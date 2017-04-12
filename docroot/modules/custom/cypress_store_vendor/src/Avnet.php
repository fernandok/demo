<?php

namespace Drupal\cypress_store_vendor;

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
   * Avnet constructor.
   */
  public function __construct() {
    parent::__construct();
    //Todo change dev2 to be dynamic based on envirnment
    $this->endPoint = $this->config['dev2']['endPoint'];
    $this->program_id = $this->config['dev2']['programId'];
    $this->security_id = $this->config['dev2']['securityId'];
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
      $avnet_inventory_entity->set('details', $this->parseDetails($content));
      $avnet_inventory_entity->save();
    }
    catch (\Exception $e) {
      $error_message = $e->getMessage();
    }
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
  protected function parseDetails($inventory_xml) {
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
   * Method to submit order to Avnet vendor.
   *
   * @param OrderInterface $order
   *   Commerce order.
   * @param array $params
   *   Additional parameters.
   */
  public function setOrder($order, $params) {
    $client = \Drupal::httpClient();

    $body = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:pl="www.avnet.com/3pl">
  <soapenv:Header/>
  <soapenv:Body>
    <pl:gatewayMessage>
      <gatewayRequest>
        <encodedXmlRequest>
          &lt;order&gt;
            &lt;order_id&gt;101&lt;/order_id&gt;
            &lt;order_date&gt;04/10/2017 17:02&lt;/order_date&gt;
            &lt;order_type&gt;P&lt;/order_type&gt;
            &lt;first_name&gt;FIRST NAME&lt;/first_name&gt;
            &lt;last_name&gt;LAST NAME&lt;/last_name&gt;
            &lt;company_name&gt;COMPANY NAME&lt;/company_name&gt;
            &lt;address1&gt;address1&lt;/address1&gt;
            &lt;address2&gt;address2&lt;/address2&gt;
            &lt;city&gt;CITY&lt;/city&gt;
            &lt;state&gt;STATE&lt;/state&gt;
            &lt;zipcode&gt;695035&lt;/zipcode&gt;
            &lt;country&gt;India&lt;/country&gt;
            &lt;email&gt;manoj.k@valuebound.com&lt;/email&gt;
            &lt;phone&gt;9876543210&lt;/phone&gt;
            &lt;detail&gt;
              &lt;partno&gt;CY8C100&lt;/partno&gt;
              &lt;custpartno&gt;1000&lt;/custpartno&gt;
              &lt;qty&gt;11&lt;/qty&gt;
              &lt;htc&gt;&lt;/htc&gt;
              &lt;eccn&gt;&lt;/eccn&gt;
              &lt;eccnall&gt;&lt;/eccnall&gt;
            &lt;/detail&gt;
            &lt;detail&gt;
              &lt;partno&gt;CY8C101&lt;/partno&gt;
              &lt;custpartno&gt;1010&lt;/custpartno&gt;
              &lt;qty&gt;2&lt;/qty&gt;
              &lt;htc&gt;&lt;/htc&gt;
              &lt;eccn&gt;&lt;/eccn&gt;
              &lt;eccnall&gt;&lt;/eccnall&gt;
            &lt;/detail&gt;
            &lt;detail_count&gt;2&lt;/detail_count&gt;
              &lt;application&gt;&lt;/application&gt;
              &lt;end_equipment&gt;&lt;/end_equipment&gt;
              &lt;ship_control_code/&gt;
              &lt;ship_via&gt;FEDEX Express Economy 2nd Day Air&lt;/ship_via&gt;
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
      if (!empty(trim($content))) {
        $msg = $this->getErrorMessage($original_content);
        throw new \Exception($msg, 500);
      }

      $order_ack = (array) new \SimpleXMLElement($content);
      return $order_ack['order_id'];
    }
    catch (\Exception $e) {
      $error = $e->getMessage();
    }
    return 0;
  }

  /**
   * @param $content
   *
   * @return mixed
   */
  protected function cleanTrailingXml($content) {
    $replace_trailing_xml = <<<XML
<\/encodedXmlResponse>
  <\/gatewayResponse>
<\/tns:gatewayMessage><\/SOAP-ENV:Body>
<\/SOAP-ENV:Envelope>
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
<\/tns:gatewayMessage><\/SOAP-ENV:Body>
<\/SOAP-ENV:Envelope>
XML;
    return preg_replace("/$replace_trailing_xml/",'', $content);
  }
}
