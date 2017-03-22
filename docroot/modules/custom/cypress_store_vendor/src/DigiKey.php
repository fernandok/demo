<?php

namespace Drupal\cypress_store_vendor;


class DigiKey {
  public function QueryShipment($programId = 'cypress', $securityId = 'v0651sfp', $endPoint = 'http://test.samplecomponents.com/webservices/wssamples/service.asmx?wsdl'){
    $url = new \SoapClient($endPoint);

  }

}
