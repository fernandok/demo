<?php

namespace Drupal\cypress_store_vendor\Vendor;

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
  public function getInventory($partNumber) {
    $endPoint = $this->endPoint;
    $parameters = array(
      'program_id' => $this->program_id,
      'security_id' => $this->security_id,
      'part_number' => $partNumber
    );
    try {
      $client = new \SoapClient($endPoint);
      $response = $client->QueryAvailability($parameters);
      return $response;
    } catch (\Exception $e) {
      return $e->getMessage();
    }
  }

  /**
   * Submit a new sample request for fulfillment.
   */
  public function SubmitOrder($orderId = '146') {

    $shippingAdress = $this->getShippingAddress($orderId);
    $order = Order::load($orderId);
    $createdTimeStamp = $order->get('created')->getValue();
    $orderDate = date('Y-m-d H:i:s', $createdTimeStamp[0]['value']);

    $programId = $this->program_id;
    $security_id = $this->security_id;
    $vid_number = '661865';//$order->id();
    $order_date = '2017-04-11T10:41:23.000-05:00';//$orderDate;
    $order_type = 'Test';// This can be Test or Production depending on instance
    $first_name = trim($shippingAdress['given_name']);
    $last_name = trim($shippingAdress['family_name']);
    $company_name = $shippingAdress['organization'];
    $address1 = $shippingAdress['address_line1'];
    $address2 = $shippingAdress['address_line2'];
    $city = $shippingAdress['locality'];
    $state = $shippingAdress['administrative_area'];
    $zipcode = $shippingAdress['postal_code'];
    $country_code = $shippingAdress['country_code'];
    $email = $order->getEmail();
    $phone = $shippingAdress['contact'];
    $itemCount = 0;
    $order_items = $order->getItems();
    $compliant = 'Yes';
    $backorders = 'Allow';
    $order_detail = '';
    foreach ($order_items as $order_item) {
      $product_mpn_id = $this->getProductMpnId($order_item);
      $product_quantity = (integer) $order_item->getQuantity();
      // Construct order detail xml.
      $order_detail .= "<detail>
      <manufacturer_part_number xsi:type=\"xsd:string\">$product_mpn_id</manufacturer_part_number>
      <customer_part_number xsi:type=\"xsd:string\">$product_mpn_id</customer_part_number>
      <quantity xsi:type=\"xsd:unsignedInt\">$product_quantity</quantity>
      <compliant xsi:type=\"xsd:bytes\">$compliant</compliant>
      <backorders xsi:type=\"xsd:bytes\">$backorders</backorders>
      </detail>";
      
      $itemCount++;
    }

//    var_dump($order_detail);exit;

    $application = 'Consumer Electronics (Audio/Video)';
    $end_equipment = 'Home Entertainment';
    $ship_via = 'FEDEX Express Economy 2nd Day Air';
    $ship_control_code = 'Single';
    $export_compliance_done = 'Y';
    $shipping_payment_option = 'Consignee';
    $error_mode = 'SOAP';


    $endPoint = $this->endPoint;

    $parameter = <<<XML
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
   <soap:Header/>
   <soap:Body>
      <SubmitOrder xmlns="http://www.samplecomponents.com/webservices/">
         <program_id xsi:type="xsd:string">$programId</program_id>
         <security_id xsi:type="xsd:string">$security_id</security_id>
         <order xmlns="http://www.samplecomponents.com/schemas/sample_order.xsd">
            <vid_number xsi:type="xsd:string">$vid_number</vid_number>
            <order_date xsi:type="xsd:dateTime">$order_date</order_date>
            <order_type xsi:type="xsd:byte">$order_type</order_type>
            <first_name xsi:type="xsd:string">$first_name</first_name>
            <last_name xsi:type="xsd:string">$last_name</last_name>
            <company_name xsi:type="xsd:string">$company_name</company_name>
            <address1 xsi:type="xsd:string">$address1</address1>
            <address2 xsi:type="xsd:string">$address2</address2>
            <city xsi:type="xsd:string">$city</city>
            <state xsi:type="xsd:string">$state</state>
            <zipcode xsi:type="xsd:string">$zipcode</zipcode>
            <country xsi:type="xsd:string">$country_code</country>
            <email xsi:type="xsd:string">$email</email>
            <phone xsi:type="xsd:string">$phone</phone>
            <fax xsi:type="xsd:string"/>
            <detail_count xsi:type="xsd:unsignedInt">$itemCount</detail_count>
            <details>
               <!--<detail>-->
                  <!--<manufacturer_part_number xsi:type="xsd:string">CY8C3244PVI-133</manufacturer_part_number>-->
                  <!--<customer_part_number xsi:type="xsd:string">CY8C3244PVI-133</customer_part_number>-->
                  <!--<quantity xsi:type="xsd:unsignedInt">1</quantity>-->
                  <!--<compliant xsi:type="xsd:byte">Yes</compliant>-->
                  <!--<backorders xsi:type="xsd:byte">Allow</backorders>-->
               <!--</detail>-->
               $order_detail
            </details>
            <application xsi:type="xsd:string">$application</application>
            <end_equipment xsi:type="xsd:string">$end_equipment</end_equipment>
            <po xsi:type="xsd:string"/>
            <ship_via xsi:type="xsd:byte">$ship_via</ship_via>
            <ship_control_code xsi:type="xsd:byte">$ship_control_code</ship_control_code>
            <export_compliance_done xsi:type="xsd:byte">$export_compliance_done</export_compliance_done>
            <special_handling_code xsi:type="xsd:string"/>
            <program_identifier xsi:type="xsd:string"/>
            <shipping_payment_option xsi:type="xsd:byte">$shipping_payment_option</shipping_payment_option>
            <third_party_billing>
               <tpb_account xsi:type="xsd:string"/>
               <tpb_first_name xsi:type="xsd:string"/>
               <tpb_last_name xsi:type="xsd:string"/>
               <tpb_company_name xsi:type="xsd:string"/>
               <tpb_address1 xsi:type="xsd:string"/>
               <tpb_address2 xsi:type="xsd:string"/>
               <tpb_city xsi:type="xsd:string"/>
               <tpb_state xsi:type="xsd:string"/>
               <tpb_zipcode xsi:type="xsd:string"/>
               <tpb_country xsi:type="xsd:string"/>
            </third_party_billing>
            <notes xsi:type="xsd:string"/>
            <error_mode xsi:type="xsd:byte">$error_mode</error_mode>
         </order>
      </SubmitOrder>
   </soap:Body>
</soap:Envelope>
XML;
//    print"<pre>";
//    print_r($parameter);
//    print"</pre>";exit;
    try {

      $headers = array(
//        "POST /webservices/wssamples/service.asmx HTTP/1.1",
//        "Host: test.samplecomponents.com",
        "Accept: text/xml",
        "Cache-Control: no-cache",
        "Pragma: no-cache",
        "Content-Type: text/xml; charset=utf-8",
        "Content-Length: " . strlen($parameter),
        "SOAPAction: \"http://www.samplecomponents.com/webservices/SubmitOrder\""
      ); //SOAPAction: your op URL

      $url = 'https://test.samplecomponents.com/webservices/wssamples/service.asmx?op=SubmitOrder';
//      $url = $this->endPoint;

      // PHP cURL  for https connection with auth
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
      curl_setopt($ch, CURLOPT_TIMEOUT, 10);
      curl_setopt($ch, CURLOPT_POST, TRUE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $parameter); // the SOAP request
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      // converting
      $response = curl_exec($ch);
      curl_close($ch);

      print"<pre>";
      print_r($response);
      print"</pre>";exit;



    } catch (\Exception $e) {
      print"<pre>";
      print_r($e->getMessage());
      "</pre>";
      exit;
    }


    /* // creating object of SimpleXMLElement
      $xml_stuent_info = new \SimpleXMLElement("<?xml version=\"1.0\"?><student_info></student_info>");

  // function call to convert array to xml
      $this->array_to_xml($parameter, $xml_stuent_info);
          var_dump($xml_stuent_info);exit;

      $client = new \SoapClient($endPoint);
      $response = $client->SubmitOrder($xml_stuent_info);
  //    $response = $client->SubmitOrder($xml_stuent_info);
      var_dump($response);
      exit;*/

  }
}
