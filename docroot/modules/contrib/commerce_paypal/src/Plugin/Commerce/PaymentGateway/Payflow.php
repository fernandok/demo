<?php

namespace Drupal\commerce_paypal\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\CreditCard;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_payment\Entity\PaymentMethodInterface;
use Drupal\commerce_payment\Exception\HardDeclineException;
use Drupal\commerce_payment\Exception\InvalidRequestException;
use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\commerce_payment\PaymentMethodTypeManager;
use Drupal\commerce_payment\PaymentTypeManager;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OnsitePaymentGatewayBase;
use Drupal\commerce_price\Price;
use Drupal\commerce_price\RounderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\profile\Entity\ProfileInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides the PayPal Payflow payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "paypal_payflow",
 *   label = "PayPal (Payflow)",
 *   display_label = "Credit Card",
 *   payment_method_types = {"credit_card"},
 *   credit_card_types = {
 *     "amex", "discover", "mastercard", "visa",
 *   },
 * )
 */
class Payflow extends OnsitePaymentGatewayBase implements PayflowInterface {

  /**
   * Payflow test API URL.
   */
  const PAYPAL_API_TEST_URL = 'https://pilot-payflowpro.paypal.com';

  /**
   * Payflow production API URL.
   */
  const PAYPAL_API_URL = 'https://payflowpro.paypal.com';

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * The rounder.
   *
   * @var \Drupal\commerce_price\RounderInterface
   */
  protected $rounder;

  /**
   * Commerce Payflow Logger Channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, PaymentTypeManager $payment_type_manager, PaymentMethodTypeManager $payment_method_type_manager, ClientInterface $client, RounderInterface $rounder, LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $payment_type_manager, $payment_method_type_manager);
    $this->httpClient = $client;
    $this->rounder = $rounder;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.commerce_payment_type'),
      $container->get('plugin.manager.commerce_payment_method_type'),
      $container->get('http_client'),
      $container->get('commerce_price.rounder'),
      $container->get('commerce_paypal.logger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = [
      'log' => [],
      'response_verbosity' => 'MEDIUM',
      'partner' => '',
      'vendor' => '',
      'user' => '',
      'password' => '',
    ];

    return $config + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['log'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Log the following messages for debugging'),
      '#options' => [
        'request' => $this->t('API request messages'),
        'response' => $this->t('API response messages'),
      ],
      '#default_value' => $this->configuration['log'],
    ];

    $form['response_verbosity'] = [
      '#type' => 'select',
      '#title' => $this->t('Response verbosity'),
      '#description' => $this->t('The level of detail that will be returned from the Payflow API.'),
      '#options' => [
        'HIGH' => 'High',
        'MEDIUM' => 'Medium',
        'LOW' => 'Low',
      ],
      '#default_value' => $this->configuration['response_verbosity'],
    ];

    $form['partner'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Partner'),
      '#default_value' => $this->configuration['partner'],
      '#required' => TRUE,
    ];

    $form['vendor'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Vendor'),
      '#default_value' => $this->configuration['vendor'],
      '#required' => TRUE,
    ];

    $form['user'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User'),
      '#default_value' => $this->configuration['user'],
      '#required' => TRUE,
    ];

    $form['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#description' => $this->t('Only needed if you wish to change the stored value.'),
      '#default_value' => $this->configuration['password'],
      '#required' => empty($this->configuration['password']),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);

      $this->configuration['log'] = $values['log'];
      $this->configuration['response_verbosity'] = $values['response_verbosity'];
      $this->configuration['partner'] = $values['partner'];
      $this->configuration['vendor'] = $values['vendor'];
      $this->configuration['user'] = $values['user'];

      if (!empty($values['password'])) {
        $this->configuration['password'] = $values['password'];
      }
    }
  }

  /**
   * Returns the Api URL.
   */
  protected function getApiUrl() {
    return $this->getMode() == 'test' ? self::PAYPAL_API_TEST_URL : self::PAYPAL_API_URL;
  }

  /**
   * Returns the partner.
   */
  protected function getPartner() {
    return $this->configuration['partner'] ?: '';
  }

  protected function getResponseVerbosity($minimum_verbosity = NULL) {
    $verbosities = [
      'LOW',
      'MEDIUM',
      'HIGH'
    ];

    $response_verbosity = $this->configuration['response_verbosity'] ?: 'MEDIUM';
    $index = array_search($response_verbosity, $verbosities);

    // Optionally enforce a minimum, required for certain request types.
    if (!is_null($minimum_verbosity)) {
      $minimum_verbosity = strtoupper($minimum_verbosity);
      $minimum_index = array_search($minimum_verbosity, $verbosities);
      if ($minimum_index !== FALSE && ($minimum_index > $index)) {
        $response_verbosity = $minimum_verbosity;
      }
    }

    return $response_verbosity;
  }

  /**
   * Returns the vendor.
   */
  protected function getVendor() {
    return $this->configuration['vendor'] ?: '';
  }

  /**
   * Returns the user.
   */
  protected function getUser() {
    return $this->configuration['user'] ?: '';
  }

  /**
   * Returns the password.
   */
  protected function getPassword() {
    return $this->configuration['password'] ?: '';
  }

  /**
   * Format the expiration date for Payflow from the provided payment details.
   *
   * @param array $payment_details
   *   The payment details array.
   *
   * @return string
   *   The expiration date string.
   */
  protected function getExpirationDate(array $payment_details) {
    $date_string = $payment_details['expiration']['month'] . '-' . $payment_details['expiration']['year'];
    $date = \DateTime::createFromFormat('m-Y', $date_string);
    return $date->format('my');
  }

  /**
   * Merge default Payflow parameters in with the provided ones.
   *
   * @param array $parameters
   *   The parameters for the transaction.
   * @param string $minimum_verbosity
   *   The minimum verbosity this request requires, such as "MEDIUM".
   *
   * @return array
   *   The new parameters.
   */
  protected function getParameters(array $parameters = [], $minimum_verbosity = NULL) {
    $default_parameters = [
      'tender' => 'C',
      'trxtype' => 'S',
      'partner' => $this->getPartner(),
      'vendor' => $this->getVendor(),
      'user' => $this->getUser(),
      'pwd' => $this->getPassword(),
      'verbosity' => $this->getResponseVerbosity($minimum_verbosity),
          ];

        return $parameters + $default_parameters;
  }

  protected function populateOrderParameters(array $parameters, OrderInterface $order) {
    $default_parameters = [
      'invnum' => $order->getOrderNumber(),
      'email' => $order->getEmail(),
      'billtoemail' => $order->getEmail(),
    ];

    $customer_id = $order->getCustomerId();
    if (!empty($customer_id)) {
      $default_parameters['custcode'] = $order->getCustomerId();
    }

    $parameters += $default_parameters;

    if ($order->hasField('shipping_profile') && !$order->get('shipping_profile')->isEmpty()) {
      $shipping_profile = $order->get('shipping_profile')->entity;
      if ($shipping_profile instanceof ProfileInterface) {
        $parameters = $this->populateShippingProfileParameters($parameters, $shipping_profile);
      }
    }

    $parameters = $this->populateBillingProfileParameters($parameters, $order->getBillingProfile());

    return $parameters;
  }

  protected function populateBillingProfileParameters(array $parameters, ProfileInterface $profile) {
    /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $address */
    $address = $profile->get('address')->first();

    $default_parameters = [
      'firstname' => $address->getGivenName(),
      'lastname' => $address->getFamilyName(),
      'street' => $address->getAddressLine1(),
      'city' => $address->getLocality(),
      'state' => $address->getAdministrativeArea(),
      'zip' => $address->getPostalCode(),
      'email' => $profile->getOwner()->getEmail(),
      'billtofirstname' => $address->getGivenName(),
      'billtolastname' => $address->getFamilyName(),
      'billtostreet' => $address->getAddressLine1(),
      'billtocity' => $address->getLocality(),
      'billtostate' => $address->getAdministrativeArea(),
      'billtozip' => $address->getPostalCode(),
      'billtocountry' => $address->getCountryCode(),
      'billtoemail' => $profile->getOwner()->getEmail(),
    ];

    return $parameters + $default_parameters;
  }

  protected function populateShippingProfileParameters(array $parameters, ProfileInterface $profile) {
    /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $address */
    $address = $profile->get('address')->first();

    $default_parameters = [
      'firstname' => $address->getGivenName(),
      'lastname' => $address->getFamilyName(),
      'street' => $address->getAddressLine1(),
      'city' => $address->getLocality(),
      'state' => $address->getAdministrativeArea(),
      'zip' => $address->getPostalCode(),
      'email' => $profile->getOwner()->getEmail(),
      'shiptofirstname' => $address->getGivenName(),
      'shiptolastname' => $address->getFamilyName(),
      'shiptostreet' => $address->getAddressLine1(),
      'shiptocity' => $address->getLocality(),
      'shiptostate' => $address->getAdministrativeArea(),
      'shiptozip' => $address->getPostalCode(),
      'shiptocountry' => $address->getCountryCode(),
    ];

    return $parameters + $default_parameters;
  }

  /**
   * Prepares the request body to name/value pairs.
   *
   * @param array $parameters
   *   The request parameters.
   *
   * @return string
   *   The request body.
   */
  protected function prepareBody(array $parameters = []) {
    $parameters = $this->getParameters($parameters);

    $values = [];
    foreach ($parameters as $key => $value) {
      $values[] = strtoupper($key) . '=' . $value;
    }

    return implode('&', $values);
  }

  /**
   * Prepares the result of a request.
   *
   * @param string $body
   *   The result.
   *
   * @return array
   *   An array of the result values.
   */
  protected function prepareResult($body) {
    $response_parts = explode('&', $body);

    $result = [];
    foreach ($response_parts as $bodyPart) {
      list($key, $value) = explode('=', $bodyPart, 2);
      $result[strtolower($key)] = $value;
    }

    return $result;
  }

  /**
   * Post a transaction to the Payflow server and return the response.
   *
   * @param array $parameters
   *   The parameters to send (will have base parameters added).
   *
   * @return array
   *   The response body data in array format.
   */
  protected function executeTransaction(array $parameters) {
    if (in_array('request', $this->configuration['log'])) {
      $this->logger->debug($this->t('Sending API request to PayFlow: @request', [
        '@request' => print_r($parameters, TRUE)
      ]));
    }

    $body = $this->prepareBody($parameters);
    $response = $this->httpClient->post($this->getApiUrl(), [
      'headers' => [
        'Content-Type' => 'text/namevalue',
        'Content-Length' => strlen($body),
      ],
      'body' => $body,
      'timeout' => 0,
    ]);
    $result = $this->prepareResult($response->getBody()->getContents());

    if (in_array('response', $this->configuration['log'])) {
      $this->logger->debug($this->t('Received API response from PayFlow: @response', [
        '@response' => print_r($result, TRUE),
      ]));
    }

    return $result;
  }

  /**
   * Attempt to validate payment information according to a payment state.
   *
   * @param \Drupal\commerce_payment\Entity\PaymentInterface $payment
   *   The payment to validate.
   * @param string|null $payment_state
   *   The payment state to validate the payment for.
   */
  protected function validatePayment(PaymentInterface $payment, $payment_state = 'new') {
    if ($payment->getState()->value != $payment_state) {
      throw new InvalidArgumentException('The provided payment is in an invalid state.');
    }

    $payment_method = $payment->getPaymentMethod();

    if (empty($payment_method)) {
      throw new InvalidArgumentException('The provided payment has no payment method referenced.');
    }

    switch ($payment_state) {
      case 'new':
        if (REQUEST_TIME >= $payment_method->getExpiresTime()) {
          throw new HardDeclineException('The provided payment method has expired.');
        }

        break;

      case 'authorization':
        if ($payment->getAuthorizationExpiresTime() < REQUEST_TIME) {
          throw new \InvalidArgumentException('Authorizations are guaranteed for up to 29 days.');
        }

        if (empty($payment->getRemoteId())) {
          throw new \InvalidArgumentException('Could not retrieve the transaction ID.');
        }
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createPayment(PaymentInterface $payment, $capture = TRUE) {
    $this->validatePayment($payment, 'new');

    $order = $payment->getOrder();

    try {
      $parameters = [
        'trxtype' => $capture ? 'S' : 'A',
        'amt' => $this->rounder->round($payment->getAmount())->getNumber(),
        'currencycode' => $payment->getAmount()->getCurrencyCode(),
        'origid' => $payment->getPaymentMethod()->getRemoteId(),
      ];

      $parameters = $this->populateOrderParameters($parameters, $order);
      $data = $this->executeTransaction($parameters);

      if ($data['result'] !== '0') {
        throw new HardDeclineException('Could not charge the payment method. Response: ' . $data['respmsg'], $data['result']);
      }

      $payment->state = $capture ? 'capture_completed' : 'authorization';

      if ($capture) {
        $payment->setCapturedTime(REQUEST_TIME);
      }
      else {
        $payment->setAuthorizationExpiresTime(REQUEST_TIME + (86400 * 29));
      }

      $payment
        ->setTest(($this->getMode() == 'test'))
        ->setRemoteId($data['pnref'])
        ->setRemoteState('3')
        ->setAuthorizedTime(REQUEST_TIME)
        ->save();
    }
    catch (RequestException $e) {
      throw new HardDeclineException('Could not charge the payment method.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function capturePayment(PaymentInterface $payment, Price $amount = NULL) {
    $this->validatePayment($payment, 'authorization');

    // If not specified, capture the entire amount.
    $amount = $amount ?: $payment->getAmount();

    try {
      $data = $this->executeTransaction([
        'trxtype' => 'D',
        'amt' => $this->rounder->round($amount)->getNumber(),
        'currency' => $amount->getCurrencyCode(),
        'origid' => $payment->getRemoteId(),
      ]);

      if ($data['result'] !== '0') {
        throw new PaymentGatewayException('Count not capture payment. Message: ' . $data['respmsg'], $data['result']);
      }

      $payment->state = 'capture_completed';
      $payment
        ->setAmount($amount)
        ->setCapturedTime(REQUEST_TIME)
        ->save();
    }
    catch (RequestException $e) {
      throw new PaymentGatewayException('Count not capture payment. Message: ' . $e->getMessage(), $e->getCode(), $e);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function voidPayment(PaymentInterface $payment) {
    $this->validatePayment($payment, 'authorization');

    $remote_id = $payment->getRemoteId();

    if (empty($remote_id)) {
      throw new PaymentGatewayException('Remote authorization ID could not be determined.');
    }

    try {
      $data = $this->executeTransaction([
        'trxtype' => 'V',
        'origid' => $payment->getRemoteId(),
      ]);

      if ($data['result'] !== '0') {
        throw new PaymentGatewayException('Payment could not be voided. Message: ' . $data['respmsg'], $data['result']);
      }

      $payment->state = 'authorization_voided';
      $payment->save();
    }
    catch (RequestException $e) {
      throw new InvalidArgumentException('Only payments in the "authorization" state can be voided.');
    }
  }

  /**
   * {@inheritdoc}
   *
   * TODO: Find a way to store the capture ID.
   */
  public function refundPayment(PaymentInterface $payment, Price $amount = NULL) {
    if (!in_array($payment->getState()->value, ['capture_completed', 'capture_partially_refunded'])) {
      throw new InvalidArgumentException('Only payments in the "capture_completed" and "capture_partially_refunded" states can be refunded.');
    }

    if ($payment->getCapturedTime() < strtotime('-180 days')) {
      throw new InvalidRequestException("Unable to refund a payment captured more than 180 days ago.");
    }

    // If not specified, refund the entire amount.
    $amount = $amount ?: $payment->getAmount();

    if ($amount->greaterThan($payment->getBalance())) {
      throw new InvalidRequestException(sprintf("Can't refund more than %s.", (string) $payment->getBalance()));
    }

    if (empty($payment->getRemoteId())) {
      throw new InvalidRequestException('Could not determine the remote payment details.');
    }

    try {
      $new_refunded_amount = $payment->getRefundedAmount()->add($amount);

      $data = $this->executeTransaction([
        'trxtype' => 'C',
        'origid' => $payment->getRemoteId(),
      ]);

      if ($data['result'] !== '0') {
        throw new PaymentGatewayException('Credit could not be completed. Message: ' . $data['respmsg'], $data['result']);
      }

      $payment->state = ($new_refunded_amount->lessThan($payment->getAmount()))
        ? 'capture_partially_refunded'
        : 'capture_refunded';

      $payment
        ->setRefundedAmount($new_refunded_amount)
        ->save();
    }
    catch (RequestException $e) {
      throw new InvalidRequestException("Could not refund the payment.", $e->getCode(), $e);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createPaymentMethod(PaymentMethodInterface $payment_method, array $payment_details) {
    try {
      $parameters = [
        'trxtype' => 'A',
        'amt' => 0,
        'acct' => $payment_details['number'],
        'expdate' => $this->getExpirationDate($payment_details),
        'cvv2' => $payment_details['security_code'],
        'verbosity' => $this->getResponseVerbosity('HIGH'),
      ];
      $parameters = $this->populateBillingProfileParameters($parameters, $payment_method->getBillingProfile());
      $data = $this->executeTransaction($parameters);
      if ($data['result'] !== '0') {
        throw new HardDeclineException("Unable to verify the credit card: " . $data['respmsg'], $data['result']);
      }

      $payment_method->card_type = $payment_details['type'];
      // Only the last 4 numbers are safe to store.
      $payment_method->card_number = substr($payment_details['number'], -4);
      $payment_method->card_exp_month = $payment_details['expiration']['month'];
      $payment_method->card_exp_year = $payment_details['expiration']['year'];
      $expires = CreditCard::calculateExpirationTimestamp($payment_details['expiration']['month'], $payment_details['expiration']['year']);

      // Store the remote ID returned by the request.
      $payment_method
        ->setRemoteId($data['pnref'])
        ->setExpiresTime($expires)
        ->save();
    }
    catch (RequestException $e) {
      throw new HardDeclineException("Unable to store the credit card");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deletePaymentMethod(PaymentMethodInterface $payment_method) {
    $payment_method->delete();
  }
}
