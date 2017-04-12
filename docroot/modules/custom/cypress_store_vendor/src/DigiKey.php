<?php

namespace Drupal\cypress_store_vendor;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_shipping\Entity\Shipment;
use Drupal\commerce_shipping\Entity\ShippingMethod;
use Drupal\commerce_shipping\Plugin\Commerce\CheckoutPane\ShippingInformation;
use Drupal\Core\Entity\Entity;
use Drupal\cypress_store_vendor\Entity\VendorEntity;
use Symfony\Component\Yaml\Yaml;

class DigiKey extends VendorBase {
  /**
   * The Api End Point
   * @var
   */
  protected $endPoint;
  /**
   * DigiKey Program Id
   * @var
   */
  protected $program_id;
  /**
   * DigiKey Security Id
   * @var
   */
  protected $security_id;


  public function __construct() {
    parent::__construct();
    //Todo change dev2 to be dynamic based on envirnment
    $this->endPoint = $this->config['dev2']['endPoint'];
    $this->program_id = $this->config['dev2']['programId'];
    $this->security_id = $this->config['dev2']['securityId'];
  }

  /**
   * Query for new shipment notifications.
   */
  public function QueryShipment() {
    $endPoint = $this->endPoint;
    $parameters = array(
      'program_id' => $this->program_id,
      'security_id' => $this->security_id
    );
    try {
      $client = new \SoapClient($endPoint);
      $response = $client->QueryShipments($parameters);
      return $response;
    } catch (\Exception $e) {

    }
  }

  /**
   * Retrieve shipment details for a specific order/shipment.
   * @return mixed
   */
  public function GetShipment($shipment = []) {
    $endPoint = $this->endPoint;
    $parameters = array(
      'program_id' => $this->program_id,
      'security_id' => $this->security_id,
      'vid_number' => '',
      'order_id' => '',
      'shipment_id' => ''
    );
    try {
      $client = new \SoapClient($endPoint);
      $response = $client->GetShipment($parameters);
      return $response;
    } catch (\Exception $e) {

    }
  }

  /**
   * Query availability of sample product.
   * @return mixed
   */
  public function QueryAvailability($partNumber = 'CY8CKIT-023') {
    $endPoint = $this->endPoint;
    $parameters = array(
      'program_id' => $this->program_id,
      'security_id' => $this->security_id,
      'part_number' => $partNumber
    );
    try {
      $client = new \SoapClient($endPoint);
      $response = $client->QueryAvailability($parameters);
      var_dump($response);
      exit;
      return $response;
    } catch (\Exception $e) {

    }
  }

  /**
   * Submit a new sample request for fulfillment.
   */
  public function SubmitOrder($orderId = '62') {

    $shippingAdress = $this->getShippingAddress($orderId);
    $orderObj = Order::load($orderId);
    $createdTimeStamp = $orderObj->get('created')->getValue();
    $orderDate = date('Y-m-d H:i:s', $createdTimeStamp[0]['value']);

    $itemCount = count($orderObj->getItems());
//    var_dump($orderObj->getItems());exit;

    $endPoint = $this->endPoint;
    $parameter["SubmitOrder"] = array(
      "program_id" => $this->program_id,
      "security_id" => $this->security_id,
      "order" => array(
        "vid_number" => $orderId,
        "order_date" => $orderDate,
        "order_type" => 'Production',
        "first_name" => $shippingAdress['given_name'],
        "last_name" => $shippingAdress['family_name'],
        "company_name" => $shippingAdress['organization'],
        "address1" => $shippingAdress['address_line1'],
        "address2" => $shippingAdress['address_line2'],
        "city" => $shippingAdress['locality'],
        "state" => $shippingAdress['administrative_area'],
        "zipcode" => $shippingAdress['postal_code'],
        "country" => $shippingAdress['country_code'],
//        "email" => '',
//        "phone" => $shippingAdress['contact'],
        "phone" => "8553522864",
//        "fax" => '',
//        "import_registration_number" => '',
        "detail_count" => $itemCount,
        "details" => array(
          "detail" => array(
            "0" => array(
              "manufacturer_part_number" => "CY8CKIT-029A",
              "customer_part_number" => "CY8CKIT-029A",
              "quantity" => "1",
              "compliant" => "Yes",
              "backorders" => "Allow"
            ),
//          "1" => array(
//            "manufacturer_part_number" => "string",
//            "customer_part_number" => "string",
//            "quantity" => "unsignedInt",
//            "compliant" => "bytes",
//            "backorders" => "bytes"
//          )
          )

        ),
//      "application" => "string",
//      "end_equipment" => "string",
//      "po" => "string",
        "ship_via" => "FEDEX STD OVERNIGHT",
//      "ship_control_code" => "bytes",
//      "export_compliance_done" => "bytes",
//      "special_handling_code" => "string",
//      "program_identifier" => "string",
//      "shipping_payment_option" => "bytes",
//      "third_party_billing" => array(
//        "tpb_account" => "string",
//        "tpb_first_name" => "string",
//        "tpb_last_name" => "string",
//        "tpb_company_name" => "string",
//        "tpb_address1" => "string",
//        "tpb_address2" => "string",
//        "tpb_city" => "string",
//        "tpb_state" => "string",
//        "tpb_zipcode" => "string",
//        "tpb_country" => "string"
//
//      ),
//      "notes"=>"string",
//      "error_mode"=> "bytes"
      ),

    );
//    print"<pre>";
//    print_r($parameter);
//    "</pre>";

    // creating object of SimpleXMLElement
    $xml_stuent_info = new \SimpleXMLElement("<?xml version=\"1.0\"?><student_info></student_info>");

// function call to convert array to xml
    $this->array_to_xml($parameter, $xml_stuent_info);
        var_dump($xml_stuent_info);exit;

    $client = new \SoapClient($endPoint);
    $response = $client->SubmitOrder($xml_stuent_info);
//    $response = $client->SubmitOrder($xml_stuent_info);
    var_dump($response);
    exit;

  }

  /**
   * Get Shipping Address
   * @param $orderId
   * @return mixed
   */
  public function getShippingAddress($orderId) {
    $shippingId = Order::load($orderId)->get('shipments')->getValue();
    $shippingAdress = Shipment::load($shippingId[0]['target_id'])
      ->getShippingProfile()
      ->get('address')
      ->getValue();
    return $shippingAdress[0];
  }

  /**
   * Get Billing Address
   * @param $orderId
   * @return mixed
   */
  public function getBillingAddress($orderId) {
    $billingAddress = Order::load($orderId)
      ->getBillingProfile()
      ->get('address')
      ->getValue();
    return $billingAddress[0];
  }
}
