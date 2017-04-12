<?php

namespace Drupal\cypress_store_vendor;

use Symfony\Component\Yaml\Yaml;


class VendorBase {

  /**
   * @var array
   *
   * Configuration for vendor.
   */
  protected $config;

  public function __construct() {
    $config_name = strtolower(substr(strrchr(get_class($this), '\\'), 1));
    $this->config = \Drupal::config('cypress_store_vendor.vendor_entity.' .$config_name)
      ->get('description');
    $this->config = Yaml::parse($this->config);
  }

  /**
   * Helper method to convert array to Xml
   *
   * @param array $data
   *   Data which need to converted to XML.
   * @param SimpleXMLElement $xml
   *   Simple XML object.
   */
  public function array_to_xml($data, \SimpleXMLElement &$xml) {
    foreach ($data as $key => $value) {
      if (is_array($value)) {
        if (!is_numeric($key)) {
          $sub_node = $xml->addChild("$key");
          $this->array_to_xml($value, $sub_node);
        }
        else {
          $this->array_to_xml($value, $xml);
        }
      }
      else {
        $xml->addChild("$key", "$value");
      }
    }
  }
}
