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
  public function getInventory($mpn_id) {
    $inventory = 0;
    $product_id = \Drupal::database()->select('commerce_product__field_mpn_id', 'cpfmi')
      ->fields('cpfmi', ['entity_id'])
      ->condition('cpfmi.field_mpn_id_value', $mpn_id)
      ->execute()->fetchCol(0);
    foreach ($product_id as $prod_id) {
      $product = Product::load($prod_id);
      $inventory = $product->get('field_inventory')->first()->getValue()['value'];
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
    // TODO: Should be dynamic, get oracle customer site id.
    $oracle_account_site_id = 0;
    if ($country_code == 'US') {
      $operating_unit = 125;
      $responsibility_key = 'CSC_OM_SAMPLE_CLERK';
    }
    else {
      $operating_unit = 429;
      $responsibility_key = 'CSTI_OM_SAMPLE_CLERK';
    }
    $ship_control_code = 'Single';
    $order_items = $order->getItems();
    $order_items_count = 0;
    $order_detail = '';
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
          <ns1:CUSTOMER_SITE_USE_ID>#Trim(oracleCustSiteUseId)#</ns1:CUSTOMER_SITE_USE_ID>
          <ns1:CONTACT_FIRST_NAME>#xmlFormat(first_name)#</ns1:CONTACT_FIRST_NAME>
          <ns1:CONTACT_LAST_NAME>#xmlFormat(last_name)#</ns1:CONTACT_LAST_NAME>
          <ns1:COMPANY_NAME>#xmlFormat(company_name)#</ns1:COMPANY_NAME>
          <ns1:ADDRESS_USE_CODE>SHIP_TO</ns1:ADDRESS_USE_CODE>
          <ns1:ADDRESS1>#xmlFormat(address1)#</ns1:ADDRESS1>
          <ns1:ADDRESS2>#xmlFormat(address2)#</ns1:ADDRESS2>
          <ns1:ADDRESS3></ns1:ADDRESS3>
          <ns1:ADDRESS4></ns1:ADDRESS4>
          <ns1:CITY>#xmlFormat(city)#</ns1:CITY>
          <ns1:STATE>#xmlFormat(state)#</ns1:STATE>
          <ns1:POSTAL_CODE>#Trim(zipcode)#</ns1:POSTAL_CODE>
          <ns1:PROVINCE>#xmlFormat(state)#</ns1:PROVINCE>
          <ns1:COUNTY></ns1:COUNTY>
          <ns1:COUNTRY>#xmlFormat(country)#</ns1:COUNTRY>
          <ns1:EMAIL>#Trim(email)#</ns1:EMAIL>
          <ns1:PHONE_AREA_CODE></ns1:PHONE_AREA_CODE>
          <ns1:PHONE_COUNTRY_CODE></ns1:PHONE_COUNTRY_CODE>
          <ns1:PHONE_NUMBER>#Trim(phone)#</ns1:PHONE_NUMBER>
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
          <ns1:ORDERED_DATE></ns1:ORDERED_DATE>
XML;

  }

  /**
   * Method to get shipment details from CML/OM.
   */
  public function updateShipment() {

  }
}
