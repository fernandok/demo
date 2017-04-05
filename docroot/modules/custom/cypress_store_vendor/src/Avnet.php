<?php
/**
 * Created by PhpStorm.
 * User: vb
 * Date: 4/4/17
 * Time: 3:17 PM
 */

namespace Drupal\cypress_store_vendor;

use Symfony\Component\Yaml\Yaml;

class Avnet{

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
    $config = \Drupal::config('cypress_store_vendor.vendor_entity.avnet')
      ->get('description');
    $parsedData = Yaml::parse($config);

    //Todo change dev2 to be dynamic based on envirnment
    $this->endPoint = $parsedData['dev2']['endPoint'];
    $this->userName = $parsedData['dev2']['Username'];
    $this->password = $parsedData['dev2']['Password'];
  }

  public function Inventory(){
    $client = \Drupal::httpClient();
    $request = $client->post('https://b2b-test.avnet.com:8543/soap/default',[
      'auth' => ['cypress','cypress123']
      ]);

    $response = $request->getBody();
    var_dump($request->getBody());exit;
  }
}