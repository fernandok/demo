<?php

namespace Drupal\cypress_store_vendor;
use Symfony\Component\Validator\Constraints\True;


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
   * @param string $region
   *   Optional region, which need for Avnet.
   *
   * @return mixed
   */
  public function getInventory($vendor, $mpn, $region = '') {
    $vendor_handler = new $vendor();
    return $vendor_handler->getInventory($mpn, $region);
  }

  /**
   * Method to set order to vendor for fulfillment.
   *
   * @param string $vendor
   *   Vendor name.
   * @param mixed $order
   *   Commerce order.
   * @param array $params
   *   Additional data.
   *
   * @return mixed
   */
  public function setOrder($vendor, $order, $params = []) {
    $vendor_handler = new $vendor();
    return $vendor_handler->setOrder($order, $params);
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
   * @param string $countryCode
   * @return bool
   */
  public function listOfAsianCountry($countryCode){
    $listCountry = [
      'KZ'=>'Kazakhstan',
      'KG'=>'Kyrgyzstan',
      'TJ'=>'Tajikistan',
      'TM'=>'Turkmenistan',
      'UZ'=>'Uzbekistan',
      'HK'=>'Hong Kong',
      'JP'=>'Japan',
      'MO'=>'Macao',
      'MN'=>'Mongolia',
      'KR'=>'Korea',
      'TW'=>'Taiwan',
      'BN'=>'Brunei Darussalam',
      'MM'=>'Myanmar (Burma)',
      'KH'=>'Cambodia',
      'TL'=>'East Timor(Timor-Leste)',
      'ID'=>'Indonesia',
      'IL'=>'Israel',
      'LA'=>'Laos',
      'MY'=>'Malaysia',
      'PH'=>'Philippines',
      'SG'=>'Singapore',
      'TH'=>'Thailand',
      'VN'=>'Vietnam',
      'AF'=>'Afghanistan',
      'BD'=>'Bangladesh',
      'BT'=>'Bhutan',
      'IN'=>'India',
      'MV'=>'Maldives',
      'NP'=>'Nepal',
      'PK'=>'Pakistan',
      'LK'=>'Sri Lanka',
      'PG'=>'Papua New Guinea',
      'NZ'=>'New Zealand',
      'AU'=>'Australia',
      'RU'=>'Russian Federation'
    ];

    if(array_key_exists($countryCode, $listCountry )){
      return TRUE;
    }else{
      return FALSE;
    }

  }

}
