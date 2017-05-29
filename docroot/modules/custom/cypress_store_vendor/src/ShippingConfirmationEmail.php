<?php

namespace Drupal\cypress_store_vendor;

use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_shipping\Entity\Shipment;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_order\Entity\Order;
use Drupal\user\Entity\User;
use Drupal\Core\Locale\CountryManager;

/**
 * Class ShippingConfirmationEmail.
 *
 * @package Drupal\cypress_store_vendor
 */
class ShippingConfirmationEmail
{
  /**
   * Constructor.
   */
  public function __construct()
  {

  }

  /**
   * Method to send shipping confirmation email for each shipment.
   *
   * @param mixed $order
   *   Commerce order.
   * @param array $shipment
   *   Shipment details.
   *
   * @return mixed
   */
  public function shippingEmail($shipment) {
    $shipment_id = $shipment->get('shipment_id')->getValue()[0]['value'];
    $output = '';
    $shipment = Shipment::load($shipment_id);
    $order_id = $shipment->getOrderId();
    $order = Order::load($order_id);
    $order_placed_date = $order->getCreatedTime();
    $user_id = $order->getCustomerId();
    $user_object = User::load($user_id);
    $username = $user_object->field_first_name->value . ' ' . $user_object->field_last_name->value;
    if (isset($username) && !empty($username)) {
      $order_username = $username;
    }
    $order_username = $user_object->name->value;
    $shipping_address = $shipment->getShippingProfile()
      ->get('field_contact_address')
      ->getValue();
    print_r($shipping_address[0]);
    if (!empty($shipping_address[0]['given_name'])) {
      $first_name = $shipping_address[0]['given_name'];
    }
    if (!empty($shipping_address[0]['family_name'])) {
      $last_name = $shipping_address[0]['family_name'];
    }
    if (!empty($shipping_address[0]['address_line1'])) {
      $address_line1 = $shipping_address[0]['address_line1'];
    }
    if (!empty($shipping_address[0]['address_line2'])) {
      $address_line2 = '<p>' . $shipping_address[0]['address_line2'] . '</p>';
    }
    if (!empty($shipping_address[0]['postal_code'])) {
      $postal_code = $shipping_address[0]['postal_code'];
    }
    if (!empty($shipping_address[0]['dependent_locality'])) {
      $dependent_locality = '<p>' . $shipping_address[0]['dependent_locality'] . '</p>';
    }
    if (!empty($shipping_address[0]['locality'])) {
      $locality = $shipping_address[0]['locality'];
    }
    if (!empty($shipping_address[0]['administrative_area'])) {
      $administrative_area = $shipping_address[0]['administrative_area'];
    }
    if (!empty($shipping_address[0]['country_code'])) {
      $country_list = CountryManager::getStandardList();
      $country_code = array_search($shipping_address[0]['country_code'], $country_list);
      if (array_key_exists($country_code, $country_list)) {
        $country = $country_list[$country_code];
      }
    }

    if (!empty($shipping_address[0]['contact'])) {
      $telephone = $shipping_address[0]['contact'];
    }

    $shipment_items = $shipment->getItems();
    $output .= '<div class = "shipping-confirmation"><h3>Shipping Confirmation</h3>';
    $output .= '<div class = "thankyou-message"><h4>Hi ' . $order_username . '</h4><p>This is a friendly notification that the below item from your Cypress Store order has been shipped</p>';
    $output .= '<div class = "order-number-mesage">Your Order number is ' . $order_id . ', placed on ' . $order_placed_date . ' And your shipping id is ' . $shipment_id . '</div></div>';
    $number_of_items = count($shipment_items);
    $output .= '<div class ="num-items">Number of items: ' . $number_of_items . '</div>';
    foreach ($shipment_items as $shipment_item) {
      $order_item_id = $shipment_item->getOrderItemID();
      $order_item = OrderItem::load($order_item_id);
      $product_variation = $order_item->getPurchasedEntity();
      if (!empty($product_variation)) {
        $product_id = $product_variation->get('product_id')
          ->getValue()[0]['target_id'];
        if (!empty($product_id)) {
          $product = Product::load($product_id);
          $product_title = $product->getTitle();
          $quantity = $order_item->getQuantity();
          $product_price = $order_item->getUnitPrice();
          $product_unit_price = $product_price->getNumber();
          $product_type = $product->get('type')->getValue()[0]['target_id'];
          if ($product_type == 'default') {
            $product_image = $product->get('field_image')->getValue()[0]['value'];
            if (!empty($product_image)) {
              $img_src = explode(':', $product_image);
              $cart_image = $img_src[1];
            } else {
              $cart_image = '/themes/cypress_store/No_image_available.svg';
              $product_image_class = 'no-image-placeholder';
            }
          } elseif ($product_type == 'part') {
            $cart_image = '/themes/cypress_store/No_image_available.svg';
            $product_image_class = 'no-image-placeholder';
          }
          $output .= '<div class = "output ' . $product_image_class . '"><img src ="' . $cart_image . '" height="100" width="100"></div>
                   <div class = "product-title">' . $product_title . '</div><div class = "product-qty">' . $quantity . '</div>
                   <div class = "product-price">' . $product_unit_price . '</div>';
        }
      }
    }
    $output .= '<div class = "deliery-address"> Delivery Address <p>' . $first_name . ' ' . $last_name . '</p>
                <p>' . $address_line1 . '</p>'
      . $address_line2 .
      $dependent_locality .
      '<p>' . $locality . ' ' . $postal_code . '</p>
                <p>' . $country . '</p>
                <p>' . $telephone . '</p></div>';
    $output .= '<div class="col-md-12 col-sm-12">
    <div class="track-shipment col-md-6 col-sm-6 col-xs-12">
      <a href="/shipment/' . $order_id . '">
        TRACK SHIPMENT
      </a>
    </div>
    <div class="contact-us col-md-6 col-sm-6 col-xs-12">
      <a href="#">
        CONTACT US
      </a>
    </div>
  </div></div>';

    /*
     * Send shipping confirmation email.
     */
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'cypress_store_vendor';
    $key = 'shipping_confirmation_mail';
    $to = \Drupal::config('system.site')->get('mail');
    $params['message'] = $output;
    $params['title'] = t('Cypress - Shipping confirmed');
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = true;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] !== true) {
      drupal_set_message(t('There was a problem sending your message and it was not sent.'), 'error');
    }
    else {
      drupal_set_message(t('Your message has been sent.'));
    }
  }
}

