<?php

namespace Drupal\cypress_store_vendor;


use Behat\Mink\Exception\Exception;

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
    try{
      $client = new \SoapClient($endPoint);
      $response = $client->QueryShipments($parameters);
      return $response;
    }catch(Exception $e){

    }

//    foreach($client->__getFunctions() as $predefinedFunctions){
//      if($predefinedFunctions == ''){
//
//      }
//      var_dump($predefinedFunctions);exit;
//    }
//    var_dump($client->__getFunctions());exit;



  }

  /**
   * Query Availability
   * @return mixed
   */
  public function QueryAvailability(){
    $endPoint = 'http://test.samplecomponents.com/webservices/wssamples/service.asmx?wsdl';
    $parameters = array('program_id' => 'cypress', 'security_id' => 'v0651sfp', 'part_number' => 'CY8CKIT-023');
    try{
      $client = new \SoapClient($endPoint);
      $response = $client->QueryAvailability($parameters);
      return $response;
    }catch(Exception $e){

    }
  }

  public function SubmitOrder(){
    $endPoint = 'http://test.samplecomponents.com/webservices/wssamples/service.asmx?wsdl';
  }

}
