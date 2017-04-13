<?php

namespace Drupal\cypress_store_vendor;


/**
 * Class InventoryService.
 *
 * @package Drupal\cypress_store_vendor
 */
class VendorService {
  /**
   * Constructor.
   */
  public function __construct() {

  }

  /**
   * Method to get inventory count for a product/part.
   *
   * @param string $vendor
   *   Vendor name.
   * @param string $mpn
   *   Product/Part number.
   * @param string $region
   *   Optional region, which need for Avnet.
   */
  public function getInventory($vendor, $mpn, $region = '') {
    $vendor_handler = new $vendor();
    return $vendor_handler->getInventory($mpn, $region);
  }

  /**
   * Method to set order to vendor for fulfillment.
   *
   * @param string $vendor
   *   Vendor name.
   * @param mixed $order
   *   Commerce order.
   * @param array $params
   *   Additional data.
   */
  public function setOrder($vendor, $order, $params = []) {
    $vendor_handler = new $vendor();
    return $vendor_handler->setOrder($order, $params);
  }

  /**
   * Method to get shipping details.
   *
   * @param string $vendor
   *   Vendor name.
   * @param string $mpn
   *   Product/Part number.
   * @param string $region
   *   Optional region, which need for Avnet.
   */
  public function getShipment($vendor, $params = []) {
    $vendor_handler = new $vendor();
    return $vendor_handler->getShipment($params);
  }

}
