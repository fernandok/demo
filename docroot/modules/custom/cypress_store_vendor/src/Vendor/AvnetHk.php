<?php

namespace Drupal\cypress_store_vendor\Vendor;


class AvnetHk extends Avnet {

  /**
   * Avnet HK constructor.
   */
  public function __construct() {
    parent::__construct();
    $this->region = 'HK';
  }

}
