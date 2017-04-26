<?php

namespace Drupal\cypress_store_vendor\Packer;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_shipping\Packer\PackerInterface;
use Drupal\commerce_shipping\ProposedShipment;
use Drupal\commerce_shipping\ShipmentItem;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\physical\Weight;
use Drupal\physical\WeightUnit;
use Drupal\profile\Entity\ProfileInterface;

/**
 * Creates a shipment per order item.
 */
class CypressPacker implements PackerInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new DefaultPacker object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
  }

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
    $order_routing_config = $this->configFactory->getEditable('cypress_store_vendor.settings')->get('order_routing_config');
    $order_routing_config = \Drupal\Core\Serialization\Yaml::decode($order_routing_config);
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

    $shipment_index = 1;
    foreach ($products as $type => $pack) {
      if (!empty($pack)) {
        $proposed_shipments[] = new ProposedShipment([
          'type' => $this->getShipmentType($order),
          'order_id' => $order->id(),
          'title' => t("Shipment #$shipment_index"),
          'items' => $pack,
          'shipping_profile' => $shipping_profile,
        ], 'commerce_shipment');
        $shipment_index++;
      }
    }

    return $proposed_shipments;
  }

  /**
   * Gets the shipment type for the current order.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   *
   * @return string
   *   The shipment type.
   */
  protected function getShipmentType(OrderInterface $order) {
    $order_type_storage = $this->entityTypeManager->getStorage('commerce_order_type');
    /** @var \Drupal\commerce_order\Entity\OrderTypeInterface $order_type */
    $order_type = $order_type_storage->load($order->bundle());

    return $order_type->getThirdPartySetting('commerce_shipping', 'shipment_type');
  }

}
