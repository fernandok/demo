<?php
/**
 * Created by PhpStorm.
 * User: vb
 * Date: 21/3/17
 * Time: 8:39 PM
 */

namespace Drupal\cypress_store_vendor;


class DigiKeyInventory {
  public function QueryShipment($programId = 'cypress', $securityId = 'v0651sfp', $endPoint = 'http://test.samplecomponents.com/webservices/wssamples/service.asmx?wsdl'){
    $url = new \SoapClient($endPoint);

  }

}