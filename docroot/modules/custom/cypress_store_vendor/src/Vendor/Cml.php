<?php

namespace Drupal\cypress_store_vendor\Vendor;


use Drupal\commerce_product\Entity\Product;

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
   * @param string $mpn_id
   *   Marketing Part number ID.
   *
   * @return int|string
   *   Part quantity in CML/OM.
   */
  public function getInventory($mpn) {
    $inventory = 0;
    $product_id = \Drupal::database()->select('commerce_product_field_data', 'cpfd')
      ->fields('cpfd', ['product_id'])
      ->condition('cpfd.title', $mpn)
      ->execute()->fetchCol(0);
    foreach ($product_id as $prod_id) {
      $product = Product::load($prod_id);
      $product_inventory = $product->get('field_inventory')->first();
      if (!empty($product_inventory)) {
        $inventory = $product_inventory->getValue()['value'];
      }
    }

    return $inventory;
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
   * @param array $shipment
   *   Shipment details.
   *
   * @return mixed
   */
  public function submitOrder($order, $shipment) {
    $order_id = $order->id();
    $order_date = $order->get('created')->getValue();
    $order_date = date('Y-m-d H:i:s', $order_date[0]['value']);
    $order_type = 'P';
    // TODO: Make shipping method and address dynamic.
    $ship_via = 'FEDEX Express Economy 2nd Day Air';
    $shipping_address = $this->getShippingAddress($order, TRUE);
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
    $oracle_account_site_id = $shipping_address['oracle_customer_site_id'];
    $om_customer_site_use_id = $shipping_address['om_customer_site_use_id'];
    if ($country_code == 'US') {
      $operating_unit = 125;
      $responsibility_key = 'CSC_OM_SAMPLE_CLERK';
    }
    else {
      $operating_unit = 429;
      $responsibility_key = 'CSTI_OM_SAMPLE_CLERK';
    }
    $ship_control_code = 'Single';
    // Process order shipment item.
    $shipment_items = $shipment->getItems();
    $shipment_items_count = count($shipment_items);
    $order_type_id = '';
    $order_line_items = '';
    foreach ($shipment_items as $shipment_item) {
      $mpn = $shipment_item->getTitle();
      $quantity = $shipment_item->getQuantity();
      $production_status_results = \Drupal::database()->query('SELECT fsr.field_status_raw_value
        FROM `commerce_product__field_status_raw` fsr
        join commerce_product_field_data fd
        on fsr.entity_id = fd.product_id
        where fd.title = :mpn', [':mpn' => $mpn]);
      foreach ($production_status_results as $production_status_result) {
        $status = $production_status_result->field_status_raw_value;
        if ($responsibility_key == 'CSC_OM_SAMPLE_CLERK') {
          if ($status == 'production') {
            $order_type_id .= '<ns1:ORDER_TYPE_ID>1050</ns1:ORDER_TYPE_ID>';
          }
          else {
            $order_type_id .= '<ns1:ORDER_TYPE_ID>1060</ns1:ORDER_TYPE_ID>';
          }
        }
        else if ($responsibility_key == 'CSTI_OM_SAMPLE_CLERK') {
          if ($status == 'production') {
            $order_type_id .= '<ns1:ORDER_TYPE_ID>1361</ns1:ORDER_TYPE_ID>';
          }
          else {
            $order_type_id .= '<ns1:ORDER_TYPE_ID>1359</ns1:ORDER_TYPE_ID>';
          }
        }
        $order_line_items .= "<ns1:ORDER_LINE_TBL>
          <ns1:ORDER_LINE_TBL_ITEM>
            <ns1:LINE_ID></ns1:LINE_ID>
            <ns1:LINE_NUMBER></ns1:LINE_NUMBER>
            <ns1:HEADER_ID></ns1:HEADER_ID>
            <ns1:INVENTORY_ITEM_ID></ns1:INVENTORY_ITEM_ID>
            <ns1:ORDERED_ITEM>$mpn</ns1:ORDERED_ITEM>
            <ns1:MARKETING_PART_NUM></ns1:MARKETING_PART_NUM>
            <ns1:CUSTOMER_PART_NUM></ns1:CUSTOMER_PART_NUM>
            <ns1:ATTRIBUTE1></ns1:ATTRIBUTE1>
            <ns1:ATTRIBUTE10></ns1:ATTRIBUTE10>
            <ns1:ATTRIBUTE11></ns1:ATTRIBUTE11>
            <ns1:ATTRIBUTE12></ns1:ATTRIBUTE12>
            <ns1:ATTRIBUTE13></ns1:ATTRIBUTE13>
            <ns1:ATTRIBUTE14></ns1:ATTRIBUTE14>
            <ns1:ATTRIBUTE15></ns1:ATTRIBUTE15>
            <ns1:ATTRIBUTE16></ns1:ATTRIBUTE16>
            <ns1:ATTRIBUTE17></ns1:ATTRIBUTE17>
            <ns1:ATTRIBUTE18></ns1:ATTRIBUTE18>
            <ns1:ATTRIBUTE19></ns1:ATTRIBUTE19>
            <ns1:ATTRIBUTE2></ns1:ATTRIBUTE2>
            <ns1:ATTRIBUTE20></ns1:ATTRIBUTE20>
            <ns1:ATTRIBUTE3></ns1:ATTRIBUTE3>
            <ns1:ATTRIBUTE4></ns1:ATTRIBUTE4>
            <ns1:ATTRIBUTE5></ns1:ATTRIBUTE5>
            <ns1:ATTRIBUTE6></ns1:ATTRIBUTE6>
            <ns1:ATTRIBUTE7></ns1:ATTRIBUTE7>
            <ns1:ATTRIBUTE8></ns1:ATTRIBUTE8>
            <ns1:ATTRIBUTE9></ns1:ATTRIBUTE9>
            <ns1:BOOKED_FLAG></ns1:BOOKED_FLAG>
            <ns1:CANCELLED_FLAG></ns1:CANCELLED_FLAG>
            <ns1:CUST_MODEL_SERIAL_NUMBER></ns1:CUST_MODEL_SERIAL_NUMBER>
            <ns1:CUST_PO_NUMBER></ns1:CUST_PO_NUMBER>
            <ns1:ORDERED_QUANTITY>$quantity</ns1:ORDERED_QUANTITY>
            <ns1:ORDERED_QUANTITY2></ns1:ORDERED_QUANTITY2>
            <ns1:REQUEST_DATE></ns1:REQUEST_DATE>
            <ns1:SHIP_TO_ORG_ID></ns1:SHIP_TO_ORG_ID>
            <ns1:SOLD_TO_ORG_ID></ns1:SOLD_TO_ORG_ID>
            <ns1:SOLD_FROM_ORG_ID></ns1:SOLD_FROM_ORG_ID>
            <ns1:ORDERED_ITEM_ID></ns1:ORDERED_ITEM_ID>
            <ns1:SHIP_TO_CUSTOMER_ID></ns1:SHIP_TO_CUSTOMER_ID>
            <ns1:UNIT_COST></ns1:UNIT_COST>
          </ns1:ORDER_LINE_TBL_ITEM>
        </ns1:ORDER_LINE_TBL>";
      }
    }
    $body = <<<XML
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body xmlns:ns1="http://xmlns.oracle.com/pcbpel/adapter/db/APPS/XXOESAMPLEORDER/">
    <ns1:SampleOrderInput>
      <ns1:P_API_VERSION_NUMBER>1.0</ns1:P_API_VERSION_NUMBER>
      <ns1:P_INIT_MSG_LIST>T</ns1:P_INIT_MSG_LIST>
      <ns1:P_ACTION_COMMIT>T</ns1:P_ACTION_COMMIT>
      <ns1:P_SESSION_INITIALIZE>T</ns1:P_SESSION_INITIALIZE>
      <ns1:P_ORDER_IN_REC>
        <ns1:P_ORG_ID>$operating_unit</ns1:P_ORG_ID>
        <ns1:P_USER_NAME>$this->userName</ns1:P_USER_NAME>
        <ns1:P_RESP_KEY>$responsibility_key</ns1:P_RESP_KEY>
        <ns1:P_ORIG_REF_ID>$order_id</ns1:P_ORIG_REF_ID>
        <ns1:P_CREATED_BY_MODULE>CYSTORE</ns1:P_CREATED_BY_MODULE>
        <ns1:BOOK_FLAG>T</ns1:BOOK_FLAG>
        <ns1:ADDRESS_REC>
          <ns1:CUSTOMER_SITE_ID>$oracle_account_site_id</ns1:CUSTOMER_SITE_ID>
          <ns1:CUSTOMER_SITE_USE_ID>$om_customer_site_use_id</ns1:CUSTOMER_SITE_USE_ID>
          <ns1:CONTACT_FIRST_NAME>$first_name</ns1:CONTACT_FIRST_NAME>
          <ns1:CONTACT_LAST_NAME>$last_name</ns1:CONTACT_LAST_NAME>
          <ns1:COMPANY_NAME>$company_name</ns1:COMPANY_NAME>
          <ns1:ADDRESS_USE_CODE>SHIP_TO</ns1:ADDRESS_USE_CODE>
          <ns1:ADDRESS1>$address1</ns1:ADDRESS1>
          <ns1:ADDRESS2>$address2</ns1:ADDRESS2>
          <ns1:ADDRESS3></ns1:ADDRESS3>
          <ns1:ADDRESS4></ns1:ADDRESS4>
          <ns1:CITY>$city</ns1:CITY>
          <ns1:STATE>$state</ns1:STATE>
          <ns1:POSTAL_CODE>$zipcode</ns1:POSTAL_CODE>
          <ns1:PROVINCE>$state</ns1:PROVINCE>
          <ns1:COUNTY></ns1:COUNTY>
          <ns1:COUNTRY>$country_code</ns1:COUNTRY>
          <ns1:EMAIL>$email</ns1:EMAIL>
          <ns1:PHONE_AREA_CODE></ns1:PHONE_AREA_CODE>
          <ns1:PHONE_COUNTRY_CODE></ns1:PHONE_COUNTRY_CODE>
          <ns1:PHONE_NUMBER>$phone</ns1:PHONE_NUMBER>
          <ns1:PHONE_EXTENSION></ns1:PHONE_EXTENSION>
          <ns1:PHONE_LINE_TYPE></ns1:PHONE_LINE_TYPE>
          <ns1:FAX_AREA_CODE></ns1:FAX_AREA_CODE>
          <ns1:FAX_COUNTRY_CODE></ns1:FAX_COUNTRY_CODE>
          <ns1:FAX_NUMBER></ns1:FAX_NUMBER>
          <ns1:FAX_EXTENSION></ns1:FAX_EXTENSION>
          <ns1:FAX_LINE_TYPE></ns1:FAX_LINE_TYPE>
        </ns1:ADDRESS_REC>
        <ns1:ORDER_HEADER_REC>
          <ns1:HEADER_ID></ns1:HEADER_ID>
          <ns1:ORDER_NUMBER></ns1:ORDER_NUMBER>
          <ns1:ATTRIBUTE1></ns1:ATTRIBUTE1>
          <ns1:ATTRIBUTE10></ns1:ATTRIBUTE10>
          <ns1:ATTRIBUTE11></ns1:ATTRIBUTE11>
          <ns1:ATTRIBUTE12></ns1:ATTRIBUTE12>
          <ns1:ATTRIBUTE13></ns1:ATTRIBUTE13>
          <ns1:ATTRIBUTE14></ns1:ATTRIBUTE14>
          <ns1:ATTRIBUTE15></ns1:ATTRIBUTE15>
          <ns1:ATTRIBUTE16></ns1:ATTRIBUTE16>
          <ns1:ATTRIBUTE17></ns1:ATTRIBUTE17>
          <ns1:ATTRIBUTE18></ns1:ATTRIBUTE18>
          <ns1:ATTRIBUTE19></ns1:ATTRIBUTE19>
          <ns1:ATTRIBUTE2></ns1:ATTRIBUTE2>
          <ns1:ATTRIBUTE20></ns1:ATTRIBUTE20>
          <ns1:ATTRIBUTE3></ns1:ATTRIBUTE3>
          <ns1:ATTRIBUTE4></ns1:ATTRIBUTE4>
          <ns1:ATTRIBUTE5></ns1:ATTRIBUTE5>
          <ns1:ATTRIBUTE6></ns1:ATTRIBUTE6>
          <ns1:ATTRIBUTE7></ns1:ATTRIBUTE7>
          <ns1:ATTRIBUTE8></ns1:ATTRIBUTE8>
          <ns1:ATTRIBUTE9></ns1:ATTRIBUTE9>
          <ns1:BOOKED_FLAG></ns1:BOOKED_FLAG>
          <ns1:CUST_PO_NUMBER></ns1:CUST_PO_NUMBER>
          <ns1:INVOICE_TO_CONTACT_ID></ns1:INVOICE_TO_CONTACT_ID>
          <ns1:INVOICE_TO_ORG_ID></ns1:INVOICE_TO_ORG_ID>
          <ns1:INVOICING_RULE_ID></ns1:INVOICING_RULE_ID>
          <ns1:ORDERED_DATE>$order_date</ns1:ORDERED_DATE>
          $order_type_id
          <ns1:ORIG_SYS_DOCUMENT_REF></ns1:ORIG_SYS_DOCUMENT_REF>
          <ns1:PRICING_DATE></ns1:PRICING_DATE>
          <ns1:SHIPPING_METHOD_CODE>$ship_via</ns1:SHIPPING_METHOD_CODE>
          <ns1:SHIP_FROM_ORG_ID></ns1:SHIP_FROM_ORG_ID>
          <ns1:SHIP_TO_CONTACT_ID></ns1:SHIP_TO_CONTACT_ID>
          <ns1:SHIP_TO_CUSTOMER_ID>20892</ns1:SHIP_TO_CUSTOMER_ID>
          <ns1:SHIP_TO_ORG_ID></ns1:SHIP_TO_ORG_ID>
          <ns1:BILL_TO_ACCOUNT_NUMBER></ns1:BILL_TO_ACCOUNT_NUMBER>
          <ns1:SOLD_FROM_ORG_ID></ns1:SOLD_FROM_ORG_ID>
          <ns1:SOLD_TO_CONTACT_ID></ns1:SOLD_TO_CONTACT_ID>
          <ns1:SOLD_TO_ORG_ID>20890</ns1:SOLD_TO_ORG_ID>
          <ns1:CHANGE_REASON></ns1:CHANGE_REASON>
          <ns1:CHANGE_COMMENTS></ns1:CHANGE_COMMENTS>
          <ns1:CHANGE_SEQUENCE></ns1:CHANGE_SEQUENCE>
          <ns1:SHIPPING_INSTRUCTIONS>Attn: $first_name 
          $last_name</ns1:SHIPPING_INSTRUCTIONS>
          <ns1:PACKING_INSTRUCTIONS></ns1:PACKING_INSTRUCTIONS>
          <ns1:FLOW_STATUS_CODE>ENTERED</ns1:FLOW_STATUS_CODE>
          <ns1:SOLD_TO_SITE_USE_ID></ns1:SOLD_TO_SITE_USE_ID>
          <ns1:SHIP_TO_CUSTOMER_PARTY_ID></ns1:SHIP_TO_CUSTOMER_PARTY_ID>
          <ns1:DELIVER_TO_CUSTOMER_PARTY_ID></ns1:DELIVER_TO_CUSTOMER_PARTY_ID>
          <ns1:INVOICE_TO_CUSTOMER_PARTY_ID></ns1:INVOICE_TO_CUSTOMER_PARTY_ID>
          <ns1:FOB_POINT_CODE>DDP DOCK</ns1:FOB_POINT_CODE>
        </ns1:ORDER_HEADER_REC>
        $order_line_items
        </ns1:P_ORDER_IN_REC>
      </ns1:SampleOrderInput>
  </soap:Body>
</soap:Envelope>        
XML;
    $client = \Drupal::httpClient();
    try {
      $request = $client->post(
        $this->endPoint,
        [
          'auth' => [$this->userName, $this->password],
          'body' => $body,
          'headers' => ['SOAPAction' => 'createOrder']
        ]
      );

      $response = $request->getBody();
    }
    catch (\Exception $e) {

    }
  }

  /**
   * Method to get shipment details from CML/OM.
   */
  public function updateShipment() {

  }
}
