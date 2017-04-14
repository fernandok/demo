<?php

namespace Drupal\cypress_store_vendor\Vendor;


class Cml extends VendorBase {

  /**
   * Method to get inventory details from CML/OM.
   *
   * @param string $mpn
   *   Marketing Part number.
   *
   * @return int|string
   *   Part quantity in CML/OM.
   */
  public function getInventory($mpn) {

  }

  /**
   * Method to get shipment details from CML/OM.
   *
   * @param array $params
   *   Parameters
   *
   * @return mixed
   */
  public function getShipment($params = []) {

  }

  /**
   * Method to submit order to CML/OM.
   *
   * @param OrderInterface $order
   *   Commerce order.
   * @param array $params
   *   Additional parameters.
   *
   * @return mixed
   */
  public function setOrder($order, $params) {

  }

  /**
   * Method to get shipment details from CML/OM.
   */
  public function updateShipment() {

  }
}
