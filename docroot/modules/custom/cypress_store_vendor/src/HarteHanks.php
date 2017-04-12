<?php

namespace Drupal\cypress_store_vendor;

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

  public function __construct() {
    parent::__construct();
    //Todo change dev2 to be dynamic based on envirnment
    $this->endPoint = $this->config['dev2']['endPoint'];
    $this->userName = $this->config['dev2']['Username'];
    $this->password = $this->config['dev2']['Password'];
  }

  public function AddNewOrder(){
    var_dump('hello');exit;
  }
}
