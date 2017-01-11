<?php
/**
 * Created by PhpStorm.
 * User: manoj
 * Date: 11/1/17
 * Time: 11:07 AM
 */

$soap_client = new \SoapClient('http://wwwqa.cypress.com/bjdev/promocode_webservice.cfc?wsdl');
$result = $soap_client->__soapCall('promocode', []);
echo "<pre>".print_r($result, true)."</pre>"; exit;
