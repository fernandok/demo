<?php
/**
 * SAML 2.0 remote SP metadata for SimpleSAMLphp.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-sp-remote
 */

/*
 * Example SimpleSAMLphp SAML 2.0 SP
 */

$metadata['https://saml2sp.example.org'] = array(
	'AssertionConsumerService' => 'https://saml2sp.example.org/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp',
	'SingleLogoutService' => 'https://saml2sp.example.org/simplesaml/module.php/saml/sp/saml2-logout.php/default-sp',
);

/*
 * This example shows an example config that works with Google Apps for education.
 * What is important is that you have an attribute in your IdP that maps to the local part of the email address
 * at Google Apps. In example, if your google account is foo.com, and you have a user that has an email john@foo.com, then you
 * must set the simplesaml.nameidattribute to be the name of an attribute that for this user has the value of 'john'.
 */

$metadata['google.com'] = array(
	'AssertionConsumerService' => 'https://www.google.com/a/g.feide.no/acs',
	'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
	'simplesaml.nameidattribute' => 'uid',
	'simplesaml.attributes' => FALSE,
);


/*
$metadata['http://cypress.local/simplesaml/module.php/saml/sp/metadata.php/default-sp'] = array(

    'name' => array(
        'en'  => 'MyWork IdP',
    ),

    'AssertionConsumerService' => 'http://cypress.local/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp',
    'SingleLogoutService'      => 'http://cypress.loca/simplesaml/module.php/saml/sp/saml2-logout.php/default-sp',
);


$metadata['http://cypress/local'] = array (
  'SingleLogoutService' => 
  array (
    0 => 
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
      'Location' => 'http://cypress.local/simplesaml/module.php/saml/sp/saml2-logout.php/default-sp',
    ),
    1 => 
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
      'Location' => 'http://cypress.local/simplesaml/module.php/saml/sp/saml2-logout.php/default-sp',
    ),
  ),
  'AssertionConsumerService' => 
  array (
    0 => 
    array (
      'index' => 0,
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
      'Location' => 'http://cypress.local/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp',
    ),
    1 => 
    array (
      'index' => 1,
      'Binding' => 'urn:oasis:names:tc:SAML:1.0:profiles:browser-post',
      'Location' => 'http://cypress.local/simplesaml/module.php/saml/sp/saml1-acs.php/default-sp',
    ),
    2 => 
    array (
      'index' => 2,
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
      'Location' => 'http://cypress.local/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp',
    ),
    3 => 
    array (
      'index' => 3,
      'Binding' => 'urn:oasis:names:tc:SAML:1.0:profiles:artifact-01',
      'Location' => 'http://cypress.local/simplesaml/module.php/saml/sp/saml1-acs.php/default-sp/artifact',
    ),
  ),
  'contacts' => 
  array (
    0 => 
    array (
      'emailAddress' => 'rajeshwari@valuebound.com',
      'contactType' => 'technical',
      'givenName' => 'Administrator',
    ),
  ),
  'certData' => 'MIID+zCCAuOgAwIBAgIJAL3PQLjyUcjmMA0GCSqGSIb3DQEBCwUAMIGTMQswCQYDVQQGEwJJTjESMBAGA1UECAwJS2FybmF0YWthMRIwEAYDVQQHDAlCYW5nYWxvcmUxEzARBgNVBAoMClZhbHVlYm91bmQxEjAQBgNVBAsMCXRlY2huaWNhbDEPMA0GA1UEAwwGc291bXlhMSIwIAYJKoZIhvcNAQkBFhNydmFyaWFyMTBAZ21haWwuY29tMB4XDTE2MTEwODEyNDgwOVoXDTI2MTEwODEyNDgwOVowgZMxCzAJBgNVBAYTAklOMRIwEAYDVQQIDAlLYXJuYXRha2ExEjAQBgNVBAcMCUJhbmdhbG9yZTETMBEGA1UECgwKVmFsdWVib3VuZDESMBAGA1UECwwJdGVjaG5pY2FsMQ8wDQYDVQQDDAZzb3VteWExIjAgBgkqhkiG9w0BCQEWE3J2YXJpYXIxMEBnbWFpbC5jb20wggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQC63DAHRVCZPALlzceYdela48SHYj0arPzQ1f/v0OFJ35QSGUR3eC/FmH1+R91/JhGYzwp3kCms/TfXG5UHEj1AWb0sOfISm92aWPvT8zczLKCynz0tXx/F+/hsH0rAS8NqPXhtuKUNS6yRlvLb7LT2l2X8edtYiwSozfMZ0xgV4slCIUlnyZh1csDLbZvb99IDqcM42xG2PcvSZqNG/VUCFhjZ2S0Fo+GRNfZvPxIjo0WoIG4M8ZBzpuoiCrVVKpgbpNLtuDRFnINLR735ygJZU5hV0t7MypH3ldFrFQH9JJSTvFXpaEgDTTfIDI7r3KubSXM/gZZRslMazGpJrg63AgMBAAGjUDBOMB0GA1UdDgQWBBSLlBeHf0VWVKdNbuP2RunYjQTnaTAfBgNVHSMEGDAWgBSLlBeHf0VWVKdNbuP2RunYjQTnaTAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBCwUAA4IBAQAcmJjx7sKumgxCLcSS87Sc2WxRaMmFATRRrlDokpbx2ME4Mfg3KKdyOGWIdaXrdfbt3Dr0EHk34UrSnH8LoSJvWf91JDh3S4xYtcO46a3Cx0etzgdxj2JiGqURQJajUCCDfdnnwb657Hs58cKd450e/98Lo4ipaH7/GozpJnMo7YU78TsmS7ZNqpogg3lmDljXeThb/v+LBUwYuXxt84jSK4SuE1dwQn2rWdVHiCmkt5D1yK2A/bdvfXsVSoLasvSaE39hxGv2bV6khprLKsebEhDvpo+uw66um3bi7xbTE8iCZGjgCj3BfSQZKXBFglqPY0TwFxnFe9hyhQ3Hq+O6',
);
