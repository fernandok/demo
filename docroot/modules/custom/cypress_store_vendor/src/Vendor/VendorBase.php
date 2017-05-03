<?php

namespace Drupal\cypress_store_vendor\Vendor;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Symfony\Component\Yaml\Yaml;


class VendorBase {

  /**
   * Vendor class for Avnet SH region.
   */
  const AVNETSH  = '\Drupal\cypress_store_vendor\Vendor\AvnetSh';

  /**
   * Vendor class for Avnet SH region.
   */
  const AVNETHK  = '\Drupal\cypress_store_vendor\Vendor\AvnetHk';

  /**
   * Vendor class for Digikey.
   */
  const DIGIKEY  = '\Drupal\cypress_store_vendor\Vendor\Digikey';

  /**
   * Vendor class for CML/OM.
   */
  const CML  = '\Drupal\cypress_store_vendor\Vendor\Cml';

  /**
   * Vendor class for CML/OM.
   */
  const HH  = '\Drupal\cypress_store_vendor\Vendor\HarteHanks';

  /**
   * @var array
   *
   * Configuration for vendor.
   */
  protected $config;

  public function __construct() {
    $config_name = strtolower(substr(strrchr(get_class($this), '\\'), 1));
    $this->config = \Drupal::config('cypress_store_vendor.vendor_entity.' .$config_name)
      ->get('description');
    $this->config = Yaml::parse($this->config);
  }

  /**
   * Helper method to convert array to Xml
   *
   * @param array $data
   *   Data which need to converted to XML.
   * @param SimpleXMLElement $xml
   *   Simple XML object.
   */
  public function array_to_xml($data, \SimpleXMLElement &$xml) {
    foreach ($data as $key => $value) {
      if (is_array($value)) {
        if (!is_numeric($key)) {
          $sub_node = $xml->addChild("$key");
          $this->array_to_xml($value, $sub_node);
        }
        else {
          $this->array_to_xml($value, $xml);
        }
      }
      else {
        $xml->addChild("$key", "$value");
      }
    }
  }

  /**
   * Get Shipping Address.
   *
   * @param mixed $order
   *   Order id or object.
   * @param bool $oracle_fields_required
   *   Whether need to include oracle field data.
   *
   * @return array
   */
  public function getShippingAddress($order, $oracle_fields_required = FALSE) {
    if (is_numeric($order)) {
      $order = Order::load($order);
    }
    $shipments = $order->get('shipments')->referencedEntities();
    $first_shipment = reset($shipments);
    $shipping_address = $first_shipment->getShippingProfile()
      ->get('field_contact_address')
      ->getValue();
    if ($oracle_fields_required) {
      $oracle_fields = ['oracle_customer_site_id', 'om_customer_site_use_id'];
      foreach ($oracle_fields as $field ){
        $field_value = $first_shipment->getShippingProfile()
          ->get("field_$field")
          ->getValue()[0]['value'];
        if ($field_value == NULL) {
          $field_value = 0;
        }
        $shipping_address[0][$field] = $field_value;
      }
    }
    return $shipping_address[0];
  }

  /**
   * Get Billing Address.
   *
   * @param  mixed $order
   *   Order id or object.
   *
   * @return array
   */
  public function getBillingAddress($order) {
    if (is_numeric($order)) {
      $order = Order::load($order);
    }
    $billing_address = $order
      ->getBillingProfile()
      ->get('field_contact_address')
      ->getValue();
    return $billing_address[0];
  }

  /**
   * @param \Drupal\commerce_order\Entity\OrderItem $order_item
   *
   * @return string
   */
  public function getProductMpnId(OrderItem $order_item) {
    $product_variation = $order_item->getPurchasedEntity();
    $mpn_id = '';
    $product_type = $product_variation->get('type')->getValue()[0]['target_id'];
    if ($product_type == 'part_store') {
      $mpn_id = $product_variation->getTitle();
    }
    return $mpn_id;
  }
}
