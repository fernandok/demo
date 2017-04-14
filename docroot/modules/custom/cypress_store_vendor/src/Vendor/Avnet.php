<?php

namespace Drupal\cypress_store_vendor\Vendor;

use Drupal\commerce_order\Entity\OrderInterface;

class Avnet extends VendorBase {

  /**
   * The Api End Point
   * @var
   */
  protected $endPoint;
  /**
   * Avnet UserName
   * @var
   */
  protected $userName;
  /**
   * Avnet Password
   * @var
   */
  protected $password;
  /**
   * Avnet Partner Id
   * @var
   */
  protected $partnerId;

  /**
   * Avnet constructor.
   */
  public function __construct() {
    parent::__construct();
    //Todo change dev2 to be dynamic based on envirnment
    $this->endPoint = $this->config['dev2']['endPoint'];
    $this->userName = $this->config['dev2']['Username'];
    $this->password = $this->config['dev2']['Password'];
    $this->partnerId = $this->config['dev2']['partnerId'];
  }

  /**
   * Method to get inventory details from Avnet.
   *
   * @param string $mpn
   *   Marketing Part number.
   * @param string $region
   *   Avnet inventory region or branch.
   *
   * @return int|string
   *   Part quantity in Avnet.
   */
  public function getInventory($mpn, $region = 'SH') {
    $inventory_details = \Drupal::configFactory()->getEditable('cypress_store_vendor.avnet_inventory_entity.details')->get('details');
    $inventory = unserialize($inventory_details);
    if (isset($inventory[$region]) && isset($inventory[$region][$mpn])) {
      return ltrim($inventory[$region][$mpn]['quantity'], 0);
    }
    return 0;
  }

  /**
   * Method to get whole inventory details of Avnet.
   */
  public function updateInventory() {

    $client = \Drupal::httpClient();
    $body = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:pl="www.avnet.com/3pl">
  <soapenv:Header/>
  <soapenv:Body>
    <pl:gatewayMessage>
      <gatewayRequest>
        <encodedXmlRequest>
            &lt;inventory_request&gt;
            &lt;partlist get_all="true"/&gt;
            &lt;/inventory_request&gt;
        </encodedXmlRequest>
      </gatewayRequest>
    </pl:gatewayMessage>
  </soapenv:Body>
</soapenv:Envelope>
XML;
    try {
      $request = $client->post(
        $this->endPoint,
        [
          'auth' => [$this->userName, $this->password],
          'body' => $body
        ]
      );

      $response = $request->getBody();
      $original_content = $response->getContents();
      $content = substr($original_content, strpos($original_content, '<encodedXmlResponse>') + 20);
      $content = $this->cleanTrailingXml($content);
      $content = htmlspecialchars_decode($content);
      if (empty(trim($content))) {
        $msg = $this->getErrorMessage($original_content);
        throw new \Exception($msg, 500);
      }

      $avnet_inventory_entity = \Drupal::configFactory()->getEditable('cypress_store_vendor.avnet_inventory_entity.details');
      $avnet_inventory_entity->set('changed', REQUEST_TIME);
      $avnet_inventory_entity->set('details', $this->parseInventoryDetails($content));
      $avnet_inventory_entity->save();
    }
    catch (\Exception $e) {
      $error_message = $e->getMessage();
    }
  }

  /**
   * Method to submit order to Avnet vendor.
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
    $order_date = date('m/d/Y H:i', $order_date[0]['value']);
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
    $order_items = $order->getItems();
    $order_items_count = 0;
    $order_detail = '';
    foreach ($order_items as $order_item) {
      $product_mpn_id = $this->getProductMpnId($order_item);
      $product_quantity = $order_item->getQuantity();
      // Construct order detail xml.
      $order_detail .= "&lt;detail&gt;
      &lt;partno&gt;$product_mpn_id&lt;/partno&gt;
      &lt;custpartno&gt;$product_mpn_id&lt;/custpartno&gt;
      &lt;qty&gt;$product_quantity&lt;/qty&gt;
      &lt;htc&gt;&lt;/htc&gt;
      &lt;eccn&gt;&lt;/eccn&gt;
      &lt;eccnall&gt;&lt;/eccnall&gt;
      &lt;/detail&gt;";
      $order_items_count++;
    }
    $ship_via = 'FEDEX Express Economy 2nd Day Air';

    $client = \Drupal::httpClient();

    $body = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:pl="www.avnet.com/3pl">
  <soapenv:Header/>
  <soapenv:Body>
    <pl:gatewayMessage>
      <gatewayRequest>
        <encodedXmlRequest>
          &lt;order&gt;
            &lt;order_id&gt;$order_id&lt;/order_id&gt;
            &lt;order_date&gt;$order_date&lt;/order_date&gt;
            &lt;order_type&gt;P&lt;/order_type&gt;
            &lt;first_name&gt;$first_name&lt;/first_name&gt;
            &lt;last_name&gt;$last_name&lt;/last_name&gt;
            &lt;company_name&gt;$company_name&lt;/company_name&gt;
            &lt;address1&gt;$address1&lt;/address1&gt;
            &lt;address2&gt;$address2&lt;/address2&gt;
            &lt;city&gt;$city&lt;/city&gt;
            &lt;state&gt;$state&lt;/state&gt;
            &lt;zipcode&gt;$zipcode&lt;/zipcode&gt;
            &lt;country&gt;$country_code&lt;/country&gt;
            &lt;email&gt;$email&lt;/email&gt;
            &lt;phone&gt;$phone&lt;/phone&gt;
            $order_detail
            &lt;detail_count&gt;$order_items_count&lt;/detail_count&gt;
              &lt;application&gt;&lt;/application&gt;
              &lt;end_equipment&gt;&lt;/end_equipment&gt;
              &lt;ship_control_code/&gt;
              &lt;ship_via&gt;$ship_via&lt;/ship_via&gt;
              &lt;tpb_account/&gt;
              &lt;tpb_type/&gt;
              &lt;tpb_first_name/&gt;
              &lt;tpb_last_name/&gt;
              &lt;tpb_company_name/&gt;
             &lt;tpb_address1/&gt;
             &lt;tpb_address2/&gt;
              &lt;tpb_city/&gt;
              &lt;tpb_state/&gt;
              &lt;tpb_zipcode/&gt;
              &lt;tpb_country/&gt;
          &lt;/order&gt;
        </encodedXmlRequest>
      </gatewayRequest>
    </pl:gatewayMessage>
  </soapenv:Body>
</soapenv:Envelope>
XML;


    try {
      $request = $client->post(
        $this->endPoint,
        [
          'auth' => [$this->userName, $this->password],
          'body' => $body
        ]
      );

      $response = $request->getBody();
      $original_content = $response->getContents();
      $content = substr($original_content, strpos($original_content, '<encodedXmlResponse>') + 20);
      $content = $this->cleanTrailingXml($content);
      $content = htmlspecialchars_decode($content);
      if (empty(trim($content))) {
        $msg = $this->getErrorMessage($original_content);
        throw new \Exception($msg, 500);
      }

      $order_ack = (array) new \SimpleXMLElement($content);
      return $order_ack['order_id'];
    }
    catch (\Exception $e) {
      // TODO: use custom logger.
      $error = $e->getMessage();
    }
    return 0;
  }

  /**
   * Method to get shipment details from Avnet.
   *
   * @param array $params
   *   Parameters
   *
   * @return mixed
   */
  public function getShipment($params = []) {
    $client = \Drupal::httpClient();
    $body = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:pl="www.avnet.com/3pl">
  <soapenv:Header/>
    <soapenv:Body>
      <pl:gatewayMessage>
        <gatewayRequest>
          <encodedXmlRequest>
            &lt;shipment_request&gt;
              &lt;partner_id&gt;$this->partnerId&lt;/partner_id&gt;
            &lt;/shipment_request&gt;
          </encodedXmlRequest>
        </gatewayRequest>
      </pl:gatewayMessage>
    </soapenv:Body>
</soapenv:Envelope>
XML;
    try {
      $request = $client->post(
        $this->endPoint,
        [
          'auth' => [$this->userName, $this->password],
          'body' => $body
        ]
      );

      $response = $request->getBody();
      $original_content = $response->getContents();
      $content = substr($original_content, strpos($original_content, '<encodedXmlResponse>') + 20);
      $content = $this->cleanTrailingXml($content);
      $content = htmlspecialchars_decode($content);
      if (empty(trim($content))) {
        $msg = $this->getErrorMessage($original_content);
        throw new \Exception($msg, 500);
      }

      $shipment = (array) new \SimpleXMLElement($content);
      return $shipment;
    }
    catch (\Exception $e) {
      // TODO: use custom logger.
      $error = $e->getMessage();
    }
    return [];
  }

  /**
   * Parse the Avnet inventory xml detail.
   *
   * @param string $inventory_xml
   *   Avnet inventory xml.
   *
   * @return array
   *   Whole Avnet inventory detail as an array.
   */
  protected function parseInventoryDetails($inventory_xml) {
    $inventory_details = simplexml_load_string($inventory_xml);
    $inventory = [];
    foreach ($inventory_details->part as $part) {
      $part = (array) $part;
      $inventory[$part['warehouse_code']][$part['partno']] = [
        'quantity' => $part['qoh'],
        'date' => $part['inventory_date'],
      ];
    }
    return serialize($inventory);
  }

  /**
   * @param $content
   *
   * @return mixed
   */
  protected function cleanTrailingXml($content) {
    $replace_trailing_xml = <<<XML
</encodedXmlResponse>
  </gatewayResponse>
</tns:gatewayMessage></SOAP-ENV:Body>
</SOAP-ENV:Envelope>
XML;
    return preg_replace("/$replace_trailing_xml/",'', $content);
  }

  /**
   * @param $content
   *
   * @return mixed
   */
  protected function getErrorMessage($content) {
    $content = substr($content, strpos($content, '</gatewayRequest>') + 16);
    $replace_trailing_xml = <<<XML
</tns:gatewayMessage></SOAP-ENV:Body>
</SOAP-ENV:Envelope>
XML;
    return preg_replace("/$replace_trailing_xml/",'', $content);
  }
}
