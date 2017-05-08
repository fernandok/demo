<?php

namespace Drupal\cypress_store_vendor\Vendor;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_product\Entity\Product;
use Drupal\cypress_store_vendor\CypressStoreVendor;
use SimpleSAML\Utils\XML;

class HarteHanks extends VendorBase {

  /**
   * The Api End Point
   * @var
   */
  protected $endPoint;
  /**
   * HarteHanks UserName
   * @var
   */
  protected $userName;
  /**
   * HarteHanks Password
   * @var
   */
  protected $password;
  /**
   * HarteHanks Add New Order EndPoint
   * @var
   */
  protected $addOrderEndPoint;
  /**
   * HarteHanks Get Product Availabilities EndPoint
   * @var
   */
  protected $productAvailabilitiesEndPoint;
  /**
   * HarteHanks Get Order Info EndPoint
   * @var
   */
  protected $orderInfoEndPoint;

  public function __construct() {
    parent::__construct();
    //Todo change dev2 to be dynamic based on envirnment
    $this->endPoint = $this->config['dev2']['endPoint'];
    $this->userName = $this->config['dev2']['Username'];
    $this->password = $this->config['dev2']['Password'];
    $this->addOrderEndPoint = $this->config['dev2']['addOrderEndPoint'];
    $this->productAvailabilitiesEndPoint = $this->config['dev2']['productAvailabilitiesEndPoint'];
    $this->orderInfoEndPoint = $this->config['dev2']['orderInfoEndPoint'];
  }

  public function AddNewOrder($orderId = '244') {

    $order = Order::load($orderId);
    $billingAddress = $this->getBillingAddress($order);
    $shippingAddress = $this->getShippingAddress($order);
    $createdTimeStamp = $order->get('created')->getValue();
    $orderDate = date('Y-m-d H:i:s', $createdTimeStamp[0]['value']);

    $userName = $this->userName;
    $password = $this->password;
    $shippingOption = 'UPS Ground';
    $ba_first_name = trim($billingAddress['given_name']);
    $ba_last_name = trim($billingAddress['family_name']);
    $ba_company_name = $billingAddress['organization'];
    $ba_address1 = $billingAddress['address_line1'];
    $ba_address2 = $billingAddress['address_line2'];
    $ba_city = $billingAddress['locality'];
    $ba_state = $billingAddress['administrative_area'];
    $ba_zipcode = $billingAddress['postal_code'];
    $ba_country_code = $billingAddress['country_code'];
    $ba_phone = $billingAddress['contact'];

    $sa_first_name = trim($shippingAddress['given_name']);
    $sa_last_name = trim($shippingAddress['family_name']);
    $sa_company_name = $shippingAddress['organization'];
    $sa_address1 = $shippingAddress['address_line1'];
    $sa_address2 = $shippingAddress['address_line2'];
    $sa_city = $shippingAddress['locality'];
    $sa_state = $shippingAddress['administrative_area'];
    $sa_zipcode = $shippingAddress['postal_code'];
    $sa_country_code = $shippingAddress['country_code'];
    $sa_phone = $shippingAddress['contact'];
    $email = $order->getEmail();

    $order_items = $order->getItems();

    $order_detail = '';
    foreach ($order_items as $order_item) {
      $product_quantity = (integer) $order_item->getQuantity();
      $product_mpn_id = $this->getProductMpnId($order_item);
      // Construct order detail xml.
      $order_detail .= "
        <OfferOrdered>
          <Offer>
            <Header>
              <ID>$product_mpn_id</ID>
            </Header>
          </Offer>
          <Quantity>$product_quantity</Quantity>
          <OrderShipToKey>
            <Key>0</Key>
          </OrderShipToKey>
          <Comments>HH</Comments>
        </OfferOrdered>";

    }

    $parameter = <<<XML
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
   <soap:Header>
      <AuthenticationHeader xmlns="http://sma-promail/">
         <Username>$userName</Username>
         <Password>$password</Password>
      </AuthenticationHeader>
   </soap:Header>
   <soap:Body>
      <AddOrder xmlns="http://sma-promail/">
         <order>
            <Header>
               <ID>$orderId</ID>
               <ReferenceNumber/>
               <Comments/>
            </Header>
            <Shipping>
               <ShippingOption>
                  <Description>$shippingOption</Description>
               </ShippingOption>
               <ShipComments/>
            </Shipping>
            <OrderedBy>
               <Prefix/>
               <FirstName>$ba_first_name</FirstName>
               <LastName>$ba_last_name</LastName>
               <CompanyName>$ba_company_name</CompanyName>
               <Address1>$ba_address1</Address1>
               <Address2>$ba_address2</Address2>
               <City>$ba_city</City>
               <State>$ba_state</State>
               <PostalCode>$ba_zipcode</PostalCode>
               <Phone>$ba_phone</Phone>
               <Email>$email</Email>
               <TaxExempt>false</TaxExempt>
               <TaxExemptApproved>false</TaxExemptApproved>
               <Commercial>false</Commercial>
            </OrderedBy>
            <ShipTo>
               <OrderShipTo>
                  <Comments/>
                  <FirstName>$sa_first_name</FirstName>
                  <LastName>$sa_last_name</LastName>
                  <CompanyName>$sa_company_name</CompanyName>
                  <Address1>$sa_address1</Address1>
                  <Address2>$sa_address2</Address2>
                  <City>$sa_city</City>
                  <State>$sa_state</State>
                  <PostalCode>$sa_zipcode</PostalCode>
                  <TaxExempt>false</TaxExempt>
                  <TaxExemptApproved>false</TaxExemptApproved>
                  <Commercial>false</Commercial>
                  <Flag>Other</Flag>
                  <Key>0</Key>
                  <Rush>false</Rush>
               </OrderShipTo>
            </ShipTo>
            <BillTo>
               <TaxExempt>false</TaxExempt>
               <TaxExemptApproved>false</TaxExemptApproved>
               <Commercial>false</Commercial>
               <Flag>OrderedBy</Flag>
            </BillTo>
            <Offers>
               $order_detail
            </Offers>
         </order>
      </AddOrder>
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
        "SOAPAction: \"http://sma-promail/AddOrder\""
      ); //SOAPAction: your op URL

//      $url = 'https://oms.harte-hanks.com/pmomsws/order.asmx?op=AddOrder';
      $url = $this->addOrderEndPoint;

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

      $content = substr($response, strpos($response, '<AddOrderResult>'));
      $content = str_ireplace('<![CDATA[', '', $content);
      $content = $this->cleanTrailingXml($content);
      $content = str_ireplace(']]>', '', $content);
      $content = htmlspecialchars_decode($content);

      $shipments = new \SimpleXMLElement($content);

      return $shipments;


    } catch (\Exception $e) {
      $content = substr($response, strpos($response, '<soap:Fault>'));
      $content = $this->cleanTrailingXml($content);
      $content = htmlspecialchars_decode($content);
      $shipments = new \SimpleXMLElement($content);
      return $shipments;
    }

  }

  /**
   * @param string $mpn
   *   marketing part number
   */
  public function getInventory($mpn = 'CY8CKIT') {
//    return 1;
    $userName = $this->userName;
    $password = $this->password;

    $parameter = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
	<soap:Header>
		<AuthenticationHeader xmlns="http://sma-promail/">
			<Username>$userName</Username>
			<Password>$password</Password>
		</AuthenticationHeader>
		<DebugHeader xmlns="http://sma-promail/">
			<Debug>true</Debug>
		</DebugHeader>
	</soap:Header>
	<soap:Body>
		<GetProductAvailabilities xmlns="http://sma-promail/">
			<partNumber>$mpn</partNumber>
			<!--<owner>D46-Cypress Material Management</owner>-->
		</GetProductAvailabilities>
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
        "SOAPAction: \"http://sma-promail/GetProductAvailabilities\""
      ); //SOAPAction: your op URL

//      $url = 'https://oms.harte-hanks.com/pmomsws/order.asmx?op=GetProductAvailabilities';
      $url = $this->productAvailabilitiesEndPoint;

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

      $content = substr($response, strpos($response, '<WarehouseLevels>'));
      $content = str_ireplace('<![CDATA[', '', $content);
      $content = $this->cleanTrailingXml($content);
      $content = str_ireplace(']]>', '', $content);
      $content = htmlspecialchars_decode($content);

      $shipments = new \SimpleXMLElement($content);

      return $shipments;

    } catch (\Exception $e) {

//      $content = substr($response, strpos($response, '<soap:Fault>'));
//      $content = $this->cleanTrailingXml($content);
//      $content = htmlspecialchars_decode($content);
//      $shipments = new \SimpleXMLElement($content);

      $this->emailVendorExceptionMessage('HarteHanks Get Product Availability ',$response);


    }


  }

  /**
   * HarteHanks Get Order Info
   * @param string $HHOrderId
   */
  public function GetOrderInfo($orderId = '123456') {

    $userName = $this->userName;
    $password = $this->password;


    $parameter = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
	<soap:Header>
		<AuthenticationHeader xmlns="http://sma-promail/">
			<Username>$userName</Username>
			<Password>$password</Password>
		</AuthenticationHeader>
		<DebugHeader xmlns="http://sma-promail/">
			<Debug>true</Debug>
		</DebugHeader>
	</soap:Header>
	<soap:Body>
		<GetOrderInfo xmlns="http://sma-promail/">
			<orderId>$orderId</orderId>
		</GetOrderInfo>
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
        "SOAPAction: \"http://sma-promail/GetOrderInfo\""
      ); //SOAPAction: your op URL

//      $url = 'https://oms.harte-hanks.com/pmomsws/order.asmx?op=GetOrderInfo';
      $url = $this->orderInfoEndPoint;

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

      $content = substr($response, strpos($response, '<GetOrderInfoResult>'));
      $content = str_ireplace('<![CDATA[', '', $content);
      $content = $this->cleanTrailingXml($content);
      $content = str_ireplace(']]>', '', $content);
      $content = htmlspecialchars_decode($content);

      $shipments = new \SimpleXMLElement($content);

      return $shipments;


    } catch (\Exception $e) {
      $content = substr($response, strpos($response, '<soap:Fault>'));
      $content = $this->cleanTrailingXml($content);
      $content = htmlspecialchars_decode($content);
      $shipments = new \SimpleXMLElement($content);
      return $shipments;
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
      '</GetOrderInfoResponse>',
      '</Warehouses>',
      '</ProductAvailabilities>',
      '</GetProductAvailabilitiesResult>',
      '</GetProductAvailabilitiesResponse>',
      '</AddOrderResponse>'
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
