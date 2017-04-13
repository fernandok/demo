<?php

namespace Drupal\cypress_store_vendor\Vendor;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Symfony\Component\Yaml\Yaml;


class VendorBase {

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
   * @param  mixed $order
   *   Order id or object.
   *
   * @return array
   */
  public function getShippingAddress($order) {
    if (is_numeric($order)) {
      $order = Order::load($order);
    }
    $shipments = $order->get('shipments')->referencedEntities();
    $first_shipment = reset($shipments);
    $shipping_address = $first_shipment->getShippingProfile()
      ->get('field_contact_address')
      ->getValue();
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
      ->get('address')
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
