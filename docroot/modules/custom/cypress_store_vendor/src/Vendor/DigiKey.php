<?php

namespace Drupal\cypress_store_vendor\Vendor;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_shipping\Entity\Shipment;
use Drupal\commerce_shipping\Entity\ShippingMethod;
use Drupal\commerce_shipping\Plugin\Commerce\CheckoutPane\ShippingInformation;
use Drupal\Core\Entity\Entity;
use Drupal\cypress_store_vendor\Entity\VendorEntity;
use Symfony\Component\Yaml\Yaml;

class DigiKey extends VendorBase {

  /**
   * DigiKey constructor.
   */
  public function __construct() {
    parent::__construct();
  }

  /**
   * Query for new shipment notifications.
   */
  public function QueryShipment() {
    $endPoint = $this->config['endPoint'];
    $parameters = array(
      'program_id' => $this->config['programId'],
      'security_id' => $this->config['securityId']
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
    $endPoint = $this->config['endPoint'];
    $parameters = array(
      'program_id' => $this->config['programId'],
      'security_id' => $this->config['securityId'],
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
    $endPoint = $this->config['endPoint'];
    $parameters = array(
      'program_id' => $this->config['programId'],
      'security_id' => $this->config['securityId'],
      'part_number' => $partNumber
    );
    try {
      $client = new \SoapClient($endPoint);
      $response = $client->QueryAvailability($parameters);
      if ($response->QueryAvailabilityResult->item_count > 0) {
        return $response->QueryAvailabilityResult->items->item->quantity_available;
      }
      else {
        $body = 'Environment : ' . $_ENV['AH_SITE_ENVIRONMENT'] . '<br/>' . 'Vendor : DigiKey' . '<br/>' . 'Request Body :' . htmlentities($parameters) . '<br/>' . 'Response Body : ' . htmlentities($response);
        $this->emailVendorExceptionMessage('DigiKey Submit Order ', $body);

        return $response->QueryAvailabilityResult->item_count;
      }
    } catch (\Exception $e) {
      $body = 'Environment : ' . $_ENV['AH_SITE_ENVIRONMENT'] . '<br/>' . 'Vendor : DigiKey' . '<br/>' . 'Request Body :' . htmlentities($parameters) . '<br/>' . 'Response Body : ' . htmlentities($response);
      $this->emailVendorExceptionMessage('DigiKey Submit Order ', $body);

      return 0;
    }
  }

  /**
   * Submit a new sample request for fulfillment.
   */
  public function submitOrder($order, $shipment) {
    //Const vid for placing order in digikey
    $const_vid = '661901';
    $shipment_id = $shipment->get('shipment_id')->getValue()[0]['value'];
    $shippingAdress = $this->getShippingAddress($order);
//    $order = Order::load($orderId);
    $createdTimeStamp = $order->get('created')->getValue();
    $orderDate = date('Y-m-d H:i:s', $createdTimeStamp[0]['value']);

    $programId = $this->config['programId'];
    $security_id = $this->config['securityId'];
    $vid_number = $const_vid + $shipment_id;
//    $vid_number = $shipment_id;
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
//    $order_items = $order->getItems();
    $shipment_items = $shipment->get('items')->getValue();
    $compliant = 'Yes';
    $backorders = 'Allow';
    $order_detail = '';
    // Parts fields for Parts.
    $primary_application = $order->get('field_primary_application')
      ->getValue()[0]['value'];
    $name_product_system = $order->get('field_name_product_system')
      ->getValue()[0]['value'];
    // Parts fields for parts and kit.
    $purpose_of_order = $order->get('field_purpose_of_order')
      ->getValue()[0]['value'];
    $end_customer = $order->get('field_end_customer')->getValue()[0]['value'];

    foreach ($shipment_items as $shipment_item) {
      $orderItem = OrderItem::load($shipment_item['value']->getOrderItemId());
      $product_mpn_id = $this->getProductMpnId($orderItem);
      $product_quantity = (integer) $shipment_item['value']->getQuantity();
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

    $application = $primary_application;
    $end_equipment = $name_product_system;
    $ship_via = $this->getShipmentMethodName($shipment);
    $ship_control_code = 'Single';
    $export_compliance_done = 'Y';
    $shipping_payment_option = 'Consignee';
    $error_mode = 'SOAP';


    $endPoint = $this->config['endPoint'];

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

    try {

      $headers = array(
        "Accept: text/xml",
        "Cache-Control: no-cache",
        "Pragma: no-cache",
        "Content-Type: text/xml; charset=utf-8",
        "Content-Length: " . strlen($parameter),
        "SOAPAction: \"http://www.samplecomponents.com/webservices/SubmitOrder\""
      ); //SOAPAction: your op URL

//      $url = 'https://test.samplecomponents.com/webservices/wssamples/service.asmx?op=SubmitOrder';
      $url = $this->config['submitOrderEndPoint'];

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

      $content = substr($response, strpos($response, '<SubmitOrderResult'));
      $content = str_ireplace('<![CDATA[', '', $content);
      $content = $this->cleanTrailingXml($content);
      $content = str_ireplace(']]>', '', $content);
      $content = htmlspecialchars_decode($content);

      $shipments = new \SimpleXMLElement($content);

      $shipmentsArray = json_decode(json_encode((array) $shipments), TRUE);

      $shipment->setData('DigiKey', $shipmentsArray);
      $shipment->save();

      return TRUE;


    } catch (\Exception $e) {
      $body = 'Environment : ' . $_ENV['AH_SITE_ENVIRONMENT'] . '<br/>' . 'Vendor : DigiKey' . '<br/>' . 'Request Body :' . htmlentities($parameter) . '<br/>' . 'Response Body : ' . htmlentities($response);

      $this->emailVendorExceptionMessage('DigiKey Submit Order ', $body);

      return FALSE;
    }

  }

  /**
   * @param $content
   *
   * @return mixed
   */
  protected function cleanTrailingXml($content) {
    $trailing_xml_tags = [
      '</soap:Envelope>',
      '</soap:Body>',
      '</SubmitOrderResponse>',
    ];

    return $this->replaceTrailingXmlTags($trailing_xml_tags, $content);
  }


  protected function replaceTrailingXmlTags($trailing_xml_tags, $content) {
    foreach ($trailing_xml_tags as $xml_tag) {
      $content = str_ireplace($xml_tag, '', $content);
    }

    return trim($content);
  }
}
