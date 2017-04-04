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
    var_dump($parsedData);exit;
    //Todo change dev2 to be dynamic based on envirnment
//    $this->endPoint = $parsedData['dev2']['endPoint'];
//    var_dump($this->endPoint);exit;
//    $this->program_id = $parsedData['dev2']['programId'];
//    $this->security_id = $parsedData['dev2']['securityId'];
  }

  public function AddNewOrder(){
    var_dump('hello');exit;
  }
}