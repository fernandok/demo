<?php
namespace Drupal\cypress_custom_address;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_order\OrderProcessorInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_order\Adjustment;
use Drupal\commerce_product\Entity\ProductVariation;

/**
 * Provides an order processor that modifies the cart according to the business logic for Parts.
 */
class CypressOrderProcessor implements OrderProcessorInterface {
  /**
   * {@inheritdoc}
   */
  public function process(OrderInterface $order) {
    // Get current user and user roles.
    $current_user_id = \Drupal::currentUser()->id();
    $user_roles = \Drupal::currentUser()->getRoles();
    $cypress_roles = array(
      '0' => 'sales_rep',
      '1' => 'all_distributors',
      '2' => 'cypress_employees'
    );
    $check_roles = array_intersect($cypress_roles, $user_roles);
   // $order->setAdjustments([]);
    foreach ($order->getItems() as $order_item) {
      $order_item->setAdjustments([]);
      $product_variation = $order_item->getPurchasedEntity();
      $default_product_variation_id = $order_item->getPurchasedEntityId();
      $product_variation_type = $product_variation->get('type')->getValue()[0]['target_id'];
      $product_id = $product_variation->get('product_id')
        ->getValue()[0]['target_id'];
      $product = Product::load($product_id);
      $product_type = $product->get('type')->getValue()[0]['target_id'];
      $variation_ids = $product->getVariationIds();
      $quantity = $order_item->getQuantity();
      $product_title = $product->getTitle();
      foreach ($variation_ids as $variation_id) {
          $variation_object = ProductVariation::load($variation_id);
        $get_part_quantity = $variation_object->get('weight')->getValue()[0]['number'];
        $part_quantity = round($get_part_quantity);
        // The Part Quantity for Variant.
        $part_quantity = intval($part_quantity);
        $product_qty = round($quantity);
        // The Quantity to purchase the Part.
        $product_qty = intval($quantity);
        if ($part_quantity >= $product_qty && (!isset($prev_variation_quantity) || $prev_variation_quantity > $part_quantity)) {
          $prev_variation_quantity = $part_quantity;
          // Set new variation id.
          $current_variation_id = $variation_id;
        }
        // If product quantity is more than any part quantity.
        elseif ($part_quantity < $product_qty) {
          $current_variation_id = $variation_id;
        }
      }
      unset($prev_variation_quantity);
      // Show the new variation according to the product quantity.
      if ($current_variation_id != $default_product_variation_id) {
        $variation_object = ProductVariation::load($current_variation_id);
        $variation_price = $variation_object->getPrice();
        $order_item->get('purchased_entity')->setValue(['target_id' => $current_variation_id], TRUE);
        $order_item->setUnitPrice($variation_price);
        $order_item->save();
      }
      /*
       * Cart Rules.
       */
      // Access to CAT_B users purchasing CAT_A products.
      if ($product_type == 'part' && (in_array('authenticated', $user_roles) && !empty($check_roles) ? TRUE : FALSE)) {
        $can_sample = $product->get('field_can_sample')
          ->getValue()[0]['value'];
        $product_price = $order_item->getUnitPrice();
        $product_unit_price = $product_price->getNumber();
        // CAT_A products.
        if ($can_sample == 1) {
          if ($product_unit_price < 20 && $quantity <= 10) {
            $new_adjustment = $product_unit_price;
          }
          elseif ($product_unit_price < 20 && $quantity > 10) {
            $new_adjustment = ($product_unit_price * 10) / $quantity;
          }
          else {
            continue;
          }
          $adjustments = $order_item->getAdjustments();
          $adjustments[] = new Adjustment([
            'type' => 'cypress_cart_rules',
            'label' => 'CRA - ' . $product_title,
            'amount' => new Price('-' . $new_adjustment, 'USD'),
          ]);
          $order_item->setAdjustments($adjustments);
        }
      }
    }
  }
}

