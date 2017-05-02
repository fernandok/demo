<?php
namespace Drupal\cypress_custom_address;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\OrderProcessorInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_order\Adjustment;

/**
 * Provides an order processor that modifies the price of order items according to the cart rules.
 */
class CypressOrderProcessor implements OrderProcessorInterface
{
  /**
   * {@inheritdoc}
   */
  public function process(OrderInterface $order)
  {
    // Get current user and user roles.
    $current_user_id = \Drupal::currentUser()->id();
    $user_roles = \Drupal::currentUser()->getRoles();
    $cypress_roles = array(
      '0' => 'sales_rep',
      '1' => 'all_distributors',
      '2' => 'cypress_employees'
    );
    $check_roles = array_intersect($cypress_roles, $user_roles);
    // Access to CAT_B users purchasing CAT_A products.
    if (in_array('authenticated', $user_roles) && !empty($check_roles) ? TRUE : FALSE) {
      $order->setAdjustments([]);
      foreach ($order->getItems() as $order_item) {
        $product_variation = $order_item->getPurchasedEntity();
        $product_type = $product_variation->get('type')->getValue()[0]['target_id'];
        $product_id = $product_variation->get('product_id')
          ->getValue()[0]['target_id'];
        $product = Product::load($product_id);
        $quantity = $order_item->getQuantity();
        $product_title = $product->getTitle();
        if ($product_type == 'part_store') {
          $can_sample = $product->get('field_can_sample')
            ->getValue()[0]['value'];
          $product_price = $order_item->getUnitPrice();
          $product_unit_price = $product_price->getNumber();
          if ($can_sample == 1) {
            if ($product_unit_price < 20 && $quantity <= 10) {
              $adjustments = $order->getAdjustments();
              $adjustments[] = new Adjustment([
                'type' => 'cypress_cart_rules',
                'label' => 'Cart Rule Adjustment - ' . $product_title,
                'amount' => new Price('-' . $product_unit_price, 'USD'),
              ]);
              $order->setAdjustments($adjustments);
            }
            elseif ($product_unit_price < 20 && $quantity > 10) {
              $new_adjustment = $product_unit_price * 10;
              $adjustments = $order->getAdjustments();
              $adjustments[] = new Adjustment([
                'type' => 'cypress_cart_rules',
                'label' => 'Cart Rule Adjustment - ' . $product_title,
                'amount' => new Price('-' . $new_adjustment, 'USD'),
              ]);
              $order->setAdjustments($adjustments);
            }
          }
        }
      }
    }
  }
}