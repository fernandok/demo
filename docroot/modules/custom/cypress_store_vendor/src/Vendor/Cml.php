<?php

namespace Drupal\cypress_store_vendor\Vendor;


class Cml extends VendorBase {

  /**
   * The Api End Point
   * @var
   */
  protected $endPoint;
  /**
   * CML Oracle UserName
   * @var
   */
  protected $userName;

  /**
   * Avnet constructor.
   */
  public function __construct() {
    parent::__construct();
    //Todo change dev2 to be dynamic based on envirnment
    $this->endPoint = $this->config['dev2']['end_point'];
    $this->userName = $this->config['dev2']['username'];
  }

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
    $order_id = $order->id();
    $order_date = $order->get('created')->getValue();
    $order_date = date('Y-m-d H:i:s', $order_date[0]['value']);
    $order_type = 'P';
    // TODO: Make shipping method and address dynamic.
    $ship_via = 'FEDEX Express Economy 2nd Day Air';
    $shipping_address = $this->getShippingAddress($order);
    $first_name = trim($shipping_address['given_name']);
    $last_name = trim($shipping_address['family_name']);
    $company_name = $shipping_address['organization'];
    $address1 = $shipping_address['address_line1'];
    $address2 = $shipping_address['address_line2'];
    $city = $shipping_address['locality'];
    $state = $shipping_address['administrative_area'];
    $zipcode = $shipping_address['postal_code'];
    $country_code = $shipping_address['country_code'];
    $email = $order->getEmail();
    $phone = $shipping_address['contact'];
    $oracle_account_site_id = 0;
    $operating_unit = 125; // OR 429
    $responsibility_key = 'CSC_OM_SAMPLE_CLERK'; // OR CSTI_OM_SAMPLE_CLERK
    $ship_control_code = 'Single';
    $order_items = $order->getItems();
    $order_items_count = 0;
    $order_detail = '';
    $body = <<<XML

XML;

  }

  /**
   * Method to get shipment details from CML/OM.
   */
  public function updateShipment() {

  }
}
