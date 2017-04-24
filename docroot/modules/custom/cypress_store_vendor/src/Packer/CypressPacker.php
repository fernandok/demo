<?php

namespace Drupal\cypress_store_vendor\Packer;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_shipping\Packer\PackerInterface;
use Drupal\commerce_shipping\ProposedShipment;
use Drupal\commerce_shipping\ShipmentItem;
use Drupal\physical\Weight;
use Drupal\physical\WeightUnit;
use Drupal\profile\Entity\ProfileInterface;

/**
 * Creates a shipment per order item.
 */
class CypressPacker implements PackerInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(OrderInterface $order, ProfileInterface $shipping_profile) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function pack(OrderInterface $order, ProfileInterface $shipping_profile) {
    $proposed_shipments = [];
    $weight = new Weight('0', WeightUnit::KILOGRAM);
    $products = [
      'product_kit' => [],
      'product_cat_a' => [],
      'product_cat_b' => [],
    ];
    foreach ($order->getItems() as $order_item) {
      $purchased_entity = $order_item->getPurchasedEntity();
      $product_id = $purchased_entity->get('product_id')
        ->getValue()[0]['target_id'];
      $product = Product::load($product_id);
      $product_type = $product->bundle();
      $quantity = $order_item->getQuantity();
      $shipment_item = new ShipmentItem([
        'order_item_id' => $order_item->id(),
        'title' => $order_item->getTitle(),
        'quantity' => $quantity,
        'weight' => $weight,
        'declared_value' => $order_item->getUnitPrice()->multiply($quantity),
      ]);
      switch ($product_type) {
        case 'default':
          $products['product_kit'][] = $shipment_item;
          break;

        case 'part':
          $can_sample = $product->get('field_can_sample')
            ->getValue()[0]['value'];
          if ($can_sample == 1) {
            $products['product_cat_a'][] = $shipment_item;
          }
          elseif ($can_sample == 2) {
            $products['product_cat_b'][] = $shipment_item;
          }
          break;
      }
    }

    $index = 1;
    foreach ($products as $type => $pack) {
      if (!empty($pack)) {
        $proposed_shipments[] = new ProposedShipment([
          'type' => 'default',
          'order_id' => $order->id(),
          'title' => t("Shipment #$index"),
          'items' => $pack,
          'shipping_profile' => $shipping_profile,
        ], 'commerce_shipment');
        $index++;
      }
    }

    return $proposed_shipments;
  }

}
