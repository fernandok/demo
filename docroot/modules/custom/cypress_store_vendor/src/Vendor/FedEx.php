<?php

namespace Drupal\cypress_store_vendor\Vendor;

use Symfony\Component\Yaml\Yaml;

require_once(__DIR__ . "/../../fedex_wsdl/fedex-common.php5");

class FedEx extends VendorBase{

  /**
   * FedEx constructor.
   */
  public function __construct() {
    parent::__construct();

  }

  /**
   * Normal Tracking Not Tracking By Reference
   * Not Tested As tracking number not generated
   * FedEx Standard Api TrackService_v12_php
   * Track Service
   */

  public function trackService($trackingNumber = '122816215025810')
  {
    //The WSDL is not included with the sample code.
//Please include and reference in $path_to_wsdl variable.
    $path_to_wsdl = __DIR__ . "/../../fedex_wsdl/TrackService_v12.wsdl";

    ini_set("soap.wsdl_cache_enabled", "0");

    $client = new \SoapClient($path_to_wsdl, array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information

    $request['WebAuthenticationDetail'] = array(
      'ParentCredential' => array(
        'Key' => getProperty('parentkey'),
        'Password' => getProperty('parentpassword')
      ),
      'UserCredential' => array(
        'Key' => $this->config['authenticationKey'],
        'Password' => $this->config['password']
      )
    );

    $request['ClientDetail'] = array(
      'AccountNumber' => $this->config['accountNumber'],
      'MeterNumber' => $this->config['meterNumber']
    );
    $request['TransactionDetail'] = array('CustomerTransactionId' => 'Track Request');
    $request['Version'] = array(
      'ServiceId' => 'trck',
      'Major' => '12',
      'Intermediate' => '0',
      'Minor' => '0'
    );
    $request['SelectionDetails'] = array(
      'PackageIdentifier' => array(
        'Type' => 'TRACKING_NUMBER_OR_DOORTAG',
        'Value' => $trackingNumber // Replace 'XXX' with a valid tracking identifier
      )
    );


    try {
      if (setEndpoint('changeEndpoint')) {
        $newLocation = $client->__setLocation(setEndpoint('endpoint'));
      }

      $client->__setLocation($this->config['url'] . '/track'); //here we are changing the address location url which is in wsdl file and making it dynamic for productiona and Testing instance

      $response = $client->track($request);

      if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR') {
//                print'<pre>';print_r($response);print'</pre>';exit;

        if ($response->HighestSeverity == 'SUCCESS' && $response->CompletedTrackDetails->HighestSeverity == 'SUCCESS') {
          return $response->CompletedTrackDetails->TrackDetails;
        }
      }
//            else {
//                return $response;
//            }
      writeToLog($client);    // Write to log file
    } catch (SoapFault $exception) {
      return false;
    }
  }

}



