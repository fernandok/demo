<?php

namespace Drupal\cypress_store_vendor\Vendor;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_product\Entity\Product;
use Drupal\cypress_store_vendor\CypressStoreVendor;
use Symfony\Component\Yaml\Yaml;

class VendorBase {

  /**
   * Vendor class for Avnet SH region.
   */
  const AVNETSH = '\Drupal\cypress_store_vendor\Vendor\AvnetSh';

  /**
   * Vendor class for Avnet SH region.
   */
  const AVNETHK = '\Drupal\cypress_store_vendor\Vendor\AvnetHk';

  /**
   * Vendor class for Digikey.
   */
  const DIGIKEY = '\Drupal\cypress_store_vendor\Vendor\DigiKey';

  /**
   * Vendor class for CML/OM.
   */
  const CML = '\Drupal\cypress_store_vendor\Vendor\Cml';

  /**
   * Vendor class for CML/OM.
   */
  const HH = '\Drupal\cypress_store_vendor\Vendor\HarteHanks';

  /**
   * Vendor shipment method mapping.
   */
  const VENDORSHIPMENTMAP = [
    'avnet' => [

    ],
    'cml' => [

    ],
    'digikey' => [

    ],
    'hartehanks' => [
      'FedEx - Express Saver',
      'FedEx - Overnight',
      'FedEx International Economy',
      'FedEx International Priority',
    ],
  ];

  /**
   * @var array
   *
   * Configuration for vendor.
   */
  protected $config;

  public function __construct() {
    $vendor = strtolower(substr(strrchr(get_class($this), '\\'), 1));
    $vendor = preg_replace('/^(avnet)(hk|sh)$/','${1}', $vendor);
    $this->vendor = $vendor;
    $config = \Drupal::config('cypress_store_vendor.vendor_entity.' . $vendor)
      ->get('description');
    $config = Yaml::parse($config);
    $environment = isset($_ENV['AH_SITE_ENVIRONMENT']) ? $_ENV['AH_SITE_ENVIRONMENT'] : 'dev2';
    $this->config = $config[$environment];
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
      foreach ($oracle_fields as $field) {
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
    if (!empty($product_variation)) {
      $product_id = $product_variation->get('product_id')
        ->getValue()[0]['target_id'];
      $product = Product::load($product_id);
      $mpn_id = '';
      $product_type = $product_variation->get('type')
        ->getValue()[0]['target_id'];
      if ($product_type == 'part_store') {
        $mpn_id = $product_variation->getTitle();
      }
      elseif ($product_type == 'default') {
        $mpn_id = $product->get('field_document_source')->getValue()[0]['value'];
      }
    }
    return $mpn_id;
  }

  /**
   * Email Admin If there is error while Placing order, check product Availability and check shipping in vendor side
   * @param $subject
   * @param $body
   */
  public function emailVendorExceptionMessage($subject, $body) {

    $message = array('subject' => $subject, 'body' => $body);
    $dispatcher = \Drupal::service('event_dispatcher');
    // Creating our CypressStoreVendor event class object.
    $event = new CypressStoreVendor($message);
    // Dispatching the event through the ‘dispatch’  method,
    // Passing event name and event object ‘$event’ as parameters.
    $dispatcher->dispatch(CypressStoreVendor::ERROR, $event);
  }

  /**
   * Method to get Shipment method identifier.
   *
   * @param object $shipment
   *  Shipment object.
   *
   * @return mixed
   */
  public function getShipmentMethodName($shipment) {
    $shipment_method = $shipment->getShippingMethod()
      ->getPlugin()
      ->getConfiguration();
    $shipment_rate_label = $shipment_method['rate_label'];
    // TODO: Need to map based on vendor $this->vendor.
    return $shipment_rate_label;
  }

}
