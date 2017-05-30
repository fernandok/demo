<?php

namespace Drupal\commerce_promotion\Plugin\Commerce\PromotionOffer;

use Drupal\commerce_price\Price;

/**
 * Provides a 'Order: Fixed off' condition.
 *
 * @CommercePromotionOffer(
 *   id = "commerce_promotion_order_fixed_off",
 *   label = @Translation("Fixed off"),
 * )
 */
class OrderFixedOff extends FixedOffBase {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $this->getOrder();
    $order_total_price = $order->getTotalPrice()->getNumber();
    $currency_code = $order->getTotalPrice()->getCurrencyCode();
    // If fixed discount price is greater than order total price,
    // make discount price same to order total price.
    // So final total won't be negative.
    if ($order_total_price < $this->getAmount()) {
      $discount_price = new Price($order_total_price, $currency_code);
    }
    else {
      $discount_price = new Price($this->getAmount(), $currency_code);
    }
    $adjustment_amount = $this->rounder->round($discount_price);
    $this->applyAdjustment($order, $adjustment_amount);
  }

}
