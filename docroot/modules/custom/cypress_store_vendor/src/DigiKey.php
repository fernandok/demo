<?php

namespace Drupal\cypress_store_vendor;


class DigiKey {
  /**
   * Query Shipment
   * @param string $programId
   * @param string $securityId
   * @param string $endPoint
   */
  public function QueryShipment(){
    $endPoint = 'http://test.samplecomponents.com/webservices/wssamples/service.asmx?wsdl';
    $parameters = array('program_id' => 'cypress', 'security_id' => 'v0651sfp');

    $client = new \SoapClient($endPoint);
    $response = $client->QueryShipments($parameters);
    return $response;
//    foreach($client->__getFunctions() as $predefinedFunctions){
//      if($predefinedFunctions == ''){
//
//      }
//      var_dump($predefinedFunctions);exit;
//    }
//    var_dump($client->__getFunctions());exit;



  }

}
