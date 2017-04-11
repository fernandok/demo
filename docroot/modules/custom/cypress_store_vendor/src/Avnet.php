<?php

namespace Drupal\cypress_store_vendor;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\cypress_store_vendor\Entity\AvnetInventoryEntity;
use Symfony\Component\Yaml\Yaml;

class Avnet{

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
    $config = \Drupal::config('cypress_store_vendor.vendor_entity.avnet')
      ->get('description');
    $parsedData = Yaml::parse($config);

    //Todo change dev2 to be dynamic based on envirnment
    $this->endPoint = $parsedData['dev2']['endPoint'];
    $this->userName = $parsedData['dev2']['Username'];
    $this->password = $parsedData['dev2']['Password'];
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
    $replace_trailing_xml = <<<XML
<\/encodedXmlResponse>
  <\/gatewayResponse>
<\/tns:gatewayMessage><\/SOAP-ENV:Body>
<\/SOAP-ENV:Envelope>
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
      $content = $response->getContents();
      $content = substr($content, 770);
      $content = preg_replace("/$replace_trailing_xml/",'', $content);
      $content = htmlspecialchars_decode($content);

      $avnet_inventory_entity = \Drupal::configFactory()->getEditable('cypress_store_vendor.avnet_inventory_entity.details');
      $avnet_inventory_entity->set('changed', REQUEST_TIME);
      $avnet_inventory_entity->set('details', $this->parseDetails($content));
      $avnet_inventory_entity->save();
    }
    catch (\Exception $e) {
      
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

  }
}
