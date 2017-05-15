<?php

namespace Drupal\cypress_store_vendor;


/**
 * Class InventoryService.
 *
 * @package Drupal\cypress_store_vendor
 */
class VendorService {
  /**
   * Constructor.
   */
  public function __construct() {

  }

  /**
   * Method to get inventory count for a product/part.
   *
   * @param string $vendor
   *   Vendor name.
   * @param string $mpn
   *   Product/Part number.
   *
   * @return mixed
   */
  public function getInventory($vendor, $mpn) {
    $vendor_class_name = constant('Drupal\cypress_store_vendor\Vendor\VendorBase::' . $vendor);
    $vendor_handler = new $vendor_class_name();
    return $vendor_handler->getInventory($mpn);
  }

  /**
   * Method to set order to vendor for fulfillment.
   *
   * @param string $vendor
   *   Vendor name.
   * @param mixed $order
   *   Commerce order.
   * @param array $shipment
   *   Shipment details.
   *
   * @return mixed
   */
  public function submitOrder($vendor, $order, $shipment) {
    $vendor_class_name = constant('Drupal\cypress_store_vendor\Vendor\VendorBase::' . $vendor);
    $vendor_handler = new $vendor_class_name();
    return $vendor_handler->submitOrder($order, $shipment);
  }

  /**
   * Method to get shipping details.
   *
   * @param string $vendor
   *   Vendor name.
   * @param array $params
   *   Additional data.
   *
   * @return mixed
   */
  public function getShipment($vendor, $params = []) {
    $vendor_handler = new $vendor();
    return $vendor_handler->getShipment($params);
  }

  /**
   * Check For Asian Countries
   *
   * @param string $search_by_country_name
   *
   * @return bool
   */
  public function isAsianCountry($country, $search_by_country_name =
  FALSE){
    $list_of_asian_country = [
      'AE' => 'United Arab Emirates',
      'AF' => 'Afghanistan',
      'AM' => 'Armenia',
      'AZ' => 'Azerbaijan',
      'BD' => 'Bangladesh',
      'BH' => 'Bahrain',
      'BN' => 'Brunei',
      'BT' => 'Bhutan',
      'CC' => 'Cocos [Keeling] Islands',
      'CN' => 'China',
      'CX' => 'Christmas Island',
      'CY' => 'Cyprus',
      'GU' => 'Guam',
      'HK' => 'Hong Kong SAR China',
      'ID' => 'Indonesia',
      'IL' => 'Israel',
      'IN' => 'India',
      'IO' => 'British Indian Ocean Territory',
      'IQ' => 'Iraq',
      'IR' => 'Iran',
      'JO' => 'Jordan',
      'JP' => 'Japan',
      'KG' => 'Kyrgyzstan',
      'KH' => 'Cambodia',
      'KP' => 'North Korea',
      'KR' => 'South Korea',
      'KW' => 'Kuwait',
      'KZ' => 'Kazakhstan',
      'LA' => 'Laos',
      'LB' => 'Lebanon',
      'LK' => 'Sri Lanka',
      'MM' => 'Myanmar [Burma]',
      'MN' => 'Mongolia',
      'MO' => 'Macau SAR China',
      'MV' => 'Maldives',
      'MY' => 'Malaysia',
      'NP' => 'Nepal',
      'OM' => 'Oman',
      'PH' => 'Philippines',
      'PK' => 'Pakistan',
      'PS' => 'Palestinian Territories',
      'QA' => 'Qatar',
      'RU' => 'Russia',
      'SA' => 'Saudi Arabia',
      'SG' => 'Singapore',
      'SY' => 'Syria',
      'TH' => 'Thailand',
      'TJ' => 'Tajikistan',
      'TM' => 'Turkmenistan',
      'TR' => 'Turkey',
      'TW' => 'Taiwan',
      'UZ' => 'Uzbekistan',
      'VN' => 'Vietnam',
      'YE' => 'Yemen',
    ];

    if ($search_by_country_name == FALSE) {
      return array_key_exists($country, $list_of_asian_country);
    }
    elseif ($search_by_country_name == TRUE ) {
      return in_array($country, $list_of_asian_country);
    }

  }

}
