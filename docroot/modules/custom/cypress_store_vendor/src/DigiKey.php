<?php

namespace Drupal\cypress_store_vendor;


use Behat\Mink\Exception\Exception;
use Drupal\cypress_store_vendor\Entity\VendorEntity;
use Symfony\Component\Yaml\Yaml;

class DigiKey {
  /**
   * The Api End Point
   * @var
   */
  protected $endPoint;
  /**
   * DigiKey Program Id
   * @var
   */
  protected $program_id;
  /**
   * DigiKey Security Id
   * @var
   */
  protected $security_id;


  public function __construct() {

    $config = \Drupal::config('cypress_store_vendor.vendor_entity.digikey')->get('description');
    $parsedData = Yaml::parse($config);
    //Todo change dev2 to be dynamic based on envirnment
    $this->endPoint = $parsedData['dev2']['endPoint'];
    $this->program_id = $parsedData['dev2']['programId'];
    $this->security_id = $parsedData['dev2']['securityId'];

  }

  /**
   * Query for new shipment notifications.
   */
  public function QueryShipment(){
    $endPoint = $this->endPoint;
    $parameters = array(
      'program_id' => $this->program_id,
      'security_id' => $this->security_id
    );
    try{
      $client = new \SoapClient($endPoint);
      $response = $client->QueryShipments($parameters);
      return $response;
    }catch(Exception $e){

    }
  }

  /**
   * Retrieve shipment details for a specific order/shipment.
   * @return mixed
   */
  public function GetShipment($shipment = []){
    $endPoint = $this->endPoint;
    $parameters = array(
      'program_id' => $this->program_id,
      'security_id' => $this->security_id,
      'vid_number' => '',
      'order_id' => '',
      'shipment_id' => ''
    );
    try{
      $client = new \SoapClient($endPoint);
      $response = $client->GetShipment($parameters);
      return $response;
    }catch(Exception $e){

    }
  }

  /**
   * Query availability of sample product.
   * @return mixed
   */
  public function QueryAvailability($partNumber = 'CY8CKIT-023'){
    $endPoint = $this->endPoint;
    $parameters = array(
      'program_id' => $this->program_id,
      'security_id' => $this->security_id,
      'part_number' => $partNumber
    );
    try{
      $client = new \SoapClient($endPoint);
      $response = $client->QueryAvailability($parameters);
      return $response;
    }catch(Exception $e){

    }
  }

  /**
   * Submit a new sample request for fulfillment.
   */
  public function SubmitOrder(){
    $endPoint = $this->endPoint;
    $parameter = array(
      'program_id' => $this->program_id,
      'security_id' => $this->security_id,
      'vid_number'=>'',
      'order_date'=>'',
      'order_type'=>'',
      'first_name'=>'',
      'last_name'=>'',
      'company_name'=>'',
      'address1'=>'',
      'address2'=>'',
      'city'=>'',
      'state'=>'',
      'zipcode'=>'',
      'country'=>'',
      'email'=>'',
      'phone'=>'',
      'fax'=>'',
      'import_registration_number' =>'',
      'detail_count' => ''
    );
    $order = array(
      ''
    );

  }

}
