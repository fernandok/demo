<?php

namespace Drupal\commerce_promotion\Plugin\Commerce\PromotionOffer;

use Drupal\commerce_price\Price;

/**
 * Provides a 'Order: Fixed off' condition.
 *
 * @CommercePromotionOffer(
 *   id = "commerce_promotion_order_fixed_off",
 *   label = @Translation("Fixed off"),
 *   target_entity_type = "commerce_order",
 * )
 */
class OrderFixedOff extends FixedOffBase {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $this->getTargetEntity();
    $currency_code = $order->getTotalPrice()->getCurrencyCode();
    $discount_price = new Price($this->getAmount(), $currency_code);
    $adjustment_amount = $this->rounder->round($discount_price);
    $this->applyAdjustment($order, $adjustment_amount);
  }

}
