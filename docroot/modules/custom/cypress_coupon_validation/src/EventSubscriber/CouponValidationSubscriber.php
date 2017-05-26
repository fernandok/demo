<?php

namespace Drupal\cypress_coupon_validation\EventSubscriber;

use Drupal\commerce_promotion\Plugin\Commerce\CheckoutPane\CouponRedemption;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Drupal\commerce_promotion\Entity\Promotion;
use Drupal\commerce_promotion\Entity\Coupon;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_order\Entity\OrderItem;

/**
 * Class CouponValidationSubscriber.
 *
 * @package Drupal\cypress_coupon_validation
 */
class CouponValidationSubscriber implements EventSubscriberInterface {


  /**
   * Constructor.
   */
  public function __construct() {

  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events['commerce_order.place.post_transition'] = ['couponOrderValidation'];

    return $events;
  }

  /**
   * This method is called whenever the commerce_order.place.post_transition event is
   * dispatched.
   *
   * @param GetResponseEvent $event
   */
  public function couponOrderValidation(Event $event) {

    $order_create = $event->getEntity();
    $order_id = $order_create->get('order_id')->getValue()[0]['value'];
    $user_id = $order_create->get('uid')->getValue()[0]['target_id'];
    $order_items = $order_create->getItems();
    foreach ($order_items as $order_item) {
      $product_var_id = $order_item->getPurchasedEntityId();
      $product_variation = ProductVariation::load($product_var_id);
      $pro_title = $product_variation->getTitle();
      $promotion_id = get_promotion_id($pro_title);
      $promotion = Promotion::load($promotion_id);
      $coupons = $promotion->getCouponIds();
      foreach ($coupons as $coupon) {
        $coupon_id = $coupon;
        $coupon_obj = Coupon::load($coupon_id);
        $promocode = $coupon_obj->getCode();
      }
    }
//    if($order_create->get('coupons')) {
//      $coupon_id = $order_create->get('coupons')->getValue()[0]['target_id'];
//      $coupon = Coupon::load($promotion_id);
//      if (!empty($coupon)) {
//        $coupon_code = $coupon->getCode();
//      }
//    }

    // Insert into custom table after order complete.
    $query =  \Drupal::database()->insert('cypress_store_coupons')
      ->fields(array(
        'order_id' => $order_id,
        'user_id' => $user_id,
        'promotion_id' => $promotion_id,
        'coupon_code' => $promocode,
      ))->execute();

    return $query;
  }

}

