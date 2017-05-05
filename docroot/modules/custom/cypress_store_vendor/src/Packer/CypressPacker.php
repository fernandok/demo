<?php

namespace Drupal\cypress_store_vendor\Packer;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_shipping\Packer\PackerInterface;
use Drupal\commerce_shipping\ProposedShipment;
use Drupal\commerce_shipping\ShipmentItem;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\cypress_store_vendor\VendorService;
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
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory, VendorService $vendor_service) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->vendorService = $vendor_service;
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
    $order_routing_config = Yaml::decode($order_routing_config);
    $proposed_shipments = [];
    $weight = new Weight('0', WeightUnit::KILOGRAM);
    $vendors_package = [
      'AVNETSH' => [],
      'AVNETHK' => [],
      'DIGIKEY' => [],
      'CML' => [],
      'HH' => [],
    ];
    // Get shipping region: China or Asia or Non-Asia.
    $country_code = $shipping_profile->get('field_contact_address')
      ->first()
      ->getValue()['country_code'];
    if ($country_code == 'CN') {
      $region = 'China';
    }
    else {
      $is_asian_region = $this->vendorService->isAsianCountry($country_code);
      if ($is_asian_region) {
        $region = 'Asia';
      }
      else {
        $region = 'Non Asia';
      }
    }
    foreach ($order->getItems() as $order_item) {
      $purchased_entity = $order_item->getPurchasedEntity();
      $product_id = $purchased_entity->get('product_id')
        ->getValue()[0]['target_id'];
      $product = Product::load($product_id);
      $product_type = $product->bundle();
      $order_item_quantity = (int) $order_item->getQuantity();
      $shipment_item = new ShipmentItem([
        'order_item_id' => $order_item->id(),
        'title' => $order_item->getTitle(),
        'quantity' => $order_item_quantity,
        'weight' => $weight,
        'declared_value' => $order_item->getUnitPrice()->multiply($order_item_quantity),
      ]);
      $mpn = $product->getTitle();
      // Get order total.
      $order_item_total_price = (float) $order_item->getTotalPrice()->getNumber();
      switch ($product_type) {
        // Product type - KIT
        case 'default':
          $rules = $order_routing_config['kit'];
          break;
        // Product type - Part
        case 'part':
          $can_sample = $product->get('field_can_sample')
            ->getValue()[0]['value'];
          // Part category - Cat A
          if ($can_sample == 1) {
            $rules = $order_routing_config['cat_a'];
          }
          // Part category - Cat B
          elseif ($can_sample == 2) {
            $rules = $order_routing_config['cat_b'];
          }
          break;
      }
      // Execute order routing rule and get list of vendors.
      $vendors = [];
      foreach ($rules as $rule) {
        $condition = $rule['condition'];
        if (eval("return $condition;")) {
          $vendors = $rule['vendors'];
          break;
        }
      }
      // Choose vendor based on inventory.
      $last_vendor = end($vendors);
      foreach ($vendors as $vendor) {
        $inventory = $this->vendorService->getInventory($vendor, $mpn);
        if ($inventory > 0) {
          $vendors_package[$vendor][] = $shipment_item;
          break;
        }
        // If no vendor is having inventory, need to be shipped via last vendor.
        if ($vendor == $last_vendor) {
          $vendors_package[$vendor][] = $shipment_item;
        }
      }
    }

    $shipment_index = 1;
    foreach ($vendors_package as $type => $pack) {
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
