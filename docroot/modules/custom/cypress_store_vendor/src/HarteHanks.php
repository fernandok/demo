<?php
/**
 * Created by PhpStorm.
 * User: vb
 * Date: 30/3/17
 * Time: 4:32 PM
 */

namespace Drupal\cypress_store_vendor;

use Symfony\Component\Yaml\Yaml;

class HarteHanks{

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

  public function __construct() {
    $config = \Drupal::config('cypress_store_vendor.vendor_entity.hartehanks')
      ->get('description');
    $parsedData = Yaml::parse($config);

    //Todo change dev2 to be dynamic based on envirnment
    $this->endPoint = $parsedData['dev2']['endPoint'];
    $this->userName = $parsedData['dev2']['Username'];
    $this->password = $parsedData['dev2']['Password'];
  }

  public function AddNewOrder(){
    var_dump('hello');exit;
  }
}