<?php

namespace Drupal\commerce_promotion\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
use Drupal\commerce_order\Entity\OrderTypeInterface;
use Drupal\commerce_order\OrderRefreshInterface;
use Drupal\commerce_promotion\Entity\PromotionInterface;
use Drupal\commerce_promotion\PromotionStorageInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the billing information pane.
 *
 * @CommerceCheckoutPane(
 *   id = "coupon",
 *   label = "Special offers",
 *   default_step = "order_information",
 *   wrapper_element = "fieldset",
 * )
 */
class Coupon extends CheckoutPaneBase implements CheckoutPaneInterface, ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The order refresh.
   *
   * @var \Drupal\commerce_order\OrderRefreshInterface
   */
  protected $orderRefresh;

  /**
   * The order type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $orderTypeStorage;

  /**
   * The order type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $promotionCouponStorage;

  /**
   * The order type storage.
   *
   * @var PromotionStorageInterface
   */
  protected $promotionStorage;

  /**
   * Constructs a new BillingInformation object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface $checkout_flow
   *   The parent checkout flow.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_order\OrderRefreshInterface $order_refresh
   *   The order refresh process.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CheckoutFlowInterface $checkout_flow, EntityTypeManagerInterface $entity_type_manager, OrderRefreshInterface $order_refresh) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $checkout_flow, $entity_type_manager);

    $this->entityTypeManager = $entity_type_manager;
    $this->orderRefresh = $order_refresh;
    $this->orderTypeStorage = $this->entityTypeManager->getStorage('commerce_order_type');
    $this->promotionCouponStorage = $this->entityTypeManager->getStorage('commerce_promotion_coupon');
    $this->promotionStorage = $this->entityTypeManager->getStorage('commerce_promotion');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, CheckoutFlowInterface $checkout_flow = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $checkout_flow,
      $container->get('entity_type.manager'),
      $container->get('commerce_order.order_refresh')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneSummary() {
    return $this->buildAdjustmentsTable(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $pane_form['#prefix'] = '<div id="coupon-ajax-wrapper">';
    $pane_form['#suffix'] = '</div>';
    $pane_form['code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Coupon code'),
      '#description' => $this->t('Enter your coupon code here.'),
    ];
    $pane_form['apply'] = [
      '#type' => 'button',
      '#value' => $this->t('Apply coupon'),
      '#limit_validation_errors' => [['coupon', 'code']],
      '#ajax' => [
        'callback' => [$this, 'addCouponCallback'],
      ],
    ];
    // @todo: follow up with ability to provide a custom view to display table.
    $pane_form['table'] = $this->buildAdjustmentsTable();
    return $pane_form;
  }

  /**
   * Adjustments table builder.
   *
   * @param bool $hide_actions
   *   TRUE if actions hidden.
   *
   * @return array Render array.
   *   Render array.
   */
  protected function buildAdjustmentsTable($hide_actions = FALSE) {
    $table = [
      '#type' => 'table',
      '#header' => [$this->t('Label'), $this->t('Amount')],
      '#empty' => t('There are no special offers applied.'),
    ];
    if (!$hide_actions) {
      $table['#header'][] = $this->t('Remove');
    }

    $adjustments = $this->order->getAdjustments();
    foreach ($this->order->getItems() as $orderItem) {
      if ($item_adjustments = $orderItem->getAdjustments()) {
        $adjustments = array_merge($adjustments, $item_adjustments);
      }
    }

    if (empty($adjustments)) {
      return $table;
    }
    foreach ($adjustments as $adjustment_id => $adjustment) {
      $b = $adjustment->getSourceId();
      list($entity_type, $entity_id) = explode(':', $adjustment->getSourceId());
      $label = $adjustment->getLabel();
      if ($entity_type == 'commerce_promotion_coupon') {
        // Use special format for promotion with coupon.
        $coupon = $this->promotionCouponStorage->load($entity_id);
        $label = $this->t(':title (discount code: :code)', [
          ':title' => $adjustment->getLabel(),
          ':code' => $coupon->get('code')->value
        ]);
      }
      $table[$adjustment->getSourceId()]['label'] = [
        '#type' => 'inline_template',
        '#template' => '{{ label }}',
        '#context' => [
          'label' => $label,
        ],
      ];
      $table[$adjustment->getSourceId()]['amount'] = [
        '#type' => 'inline_template',
        '#template' => '{{ price|commerce_price_format }}',
        '#context' => [
          'price' => $adjustment->getAmount(),
        ],
      ];
      if (!$hide_actions) {
        $table[$adjustment->getSourceId()]['remove'] = [
          '#type' => 'button',
          '#value' => $this->t('Remove coupon'),
          '#attributes' => [
            'class' => [
              'delete-button',
              'delete-coupon-button',
            ],
          ],
          '#limit_validation_errors' => [['coupon', 'code']],
          '#ajax' => [
            'callback' => [$this, 'removeCouponCallback'],
          ],
        ];
      }
    }

    return $table;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $coupon_parents = array_merge($pane_form['#parents'], ['code']);
    $coupon_code = $form_state->getValue($coupon_parents);
    if (empty($coupon_code)) {
      return;
    }
    $coupon = $this->promotionCouponStorage->loadByProperties(['code' => $coupon_code]);
    if (empty($coupon)) {
      $code_path = implode('][', $coupon_parents);
      $form_state->setErrorByName($code_path, $this->t('Coupon not exists'));
    }
  }

  /**
   * Ajax callback: coupon add button.
   */
  public function addCouponCallback($form, FormStateInterface &$form_state) {
    $response = new AjaxResponse();
    $coupon_parents = array_merge($form['#parents'], ['coupon', 'code']);
    $coupon_code = $form_state->getValue($coupon_parents);
    $coupons = $this->promotionCouponStorage->loadByProperties(['code' => $coupon_code]);
    if (!empty($coupons)) {
      $coupon = reset($coupons);
      /** @var OrderTypeInterface $order_type */
      $order_type = $this->orderTypeStorage->load($this->order->bundle());
      /** @var PromotionInterface $promotion */
      $promotion = $this->promotionStorage->loadByCoupon($order_type, $this->order->getStore(), $coupon);
      if ($promotion) {
        if ($promotion->applies($this->order)) {
          $this->order->get('coupons')->appendItem($coupon);
        }
        else {
          foreach ($this->order->getItems() as $orderItem) {
            if ($promotion->applies($orderItem)) {
              $this->order->get('coupons')->appendItem($coupon);
            }
          }
        }
        // @todo Coupons applies only once because values and fields
        //   are different in order entity
        $this->orderRefresh->refresh($this->order);
        $this->order->save();
      }
    }
    // @todo Need to find place for messages.
    array_unshift($form['coupon'], ['messages'=> ['#type' => 'status_messages']]);;
    $response->addCommand(new ReplaceCommand("#coupon-ajax-wrapper", $form['coupon']));
    $table = $this->buildAdjustmentsTable();
    $response->addCommand(new ReplaceCommand("#coupon-ajax-wrapper table", $table));
    $response->addCommand(new ReplaceCommand("#edit-order-summary", $form['order_summary']));
    return $response;
  }


  /**
   * Ajax callback: coupon add button.
   */
  public function removeCouponCallback($form, FormStateInterface &$form_state) {
    $response = new AjaxResponse();
    list($source_type, $source_id) = explode(':', $form_state->getTriggeringElement()['#parents'][2]);
    $position = NULL;
    foreach ($this->order->get('coupons') as $index => $item) {
      if ($item->entity->id() == $source_id && $item->entity->getEntityTypeId() == $source_type) {
        $position = $index;
      }
    }
    if (!is_null($position)) {
      $this->order->get('coupons')->removeItem($position);
    }
    $this->orderRefresh->refresh($this->order);
    $this->order->save();

    // @todo Need to find place for messages.
    array_unshift($form['coupon'], ['messages'=> ['#type' => 'status_messages']]);;
    $response->addCommand(new ReplaceCommand("#coupon-ajax-wrapper", $form['coupon']));
    $table = $this->buildAdjustmentsTable();
    $response->addCommand(new ReplaceCommand("#coupon-ajax-wrapper table", $table));
    $response->addCommand(new ReplaceCommand("#edit-order-summary", $form['order_summary']));
    return $response;
  }
  
}