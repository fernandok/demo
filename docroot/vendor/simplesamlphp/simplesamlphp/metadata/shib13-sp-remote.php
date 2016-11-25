<?php
/**
 * SAML 1.1 remote SP metadata for SimpleSAMLphp.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-sp-remote
 */

/*
 * This is just an example:
 */
/*
$metadata['https://sp.shiblab.feide.no'] = array(
	'AssertionConsumerService' => 'http://sp.shiblab.feide.no/Shibboleth.sso/SAML/POST',
	'audience'                 => 'urn:mace:feide:shiblab',
	'base64attributes'         => FALSE,
);

*/
$metadata['http://cypress.local/simplesaml/module.php/saml/sp/metadata.php/default-sp'] = array (
  'entityid' => 'http://cypress.local/simplesaml/module.php/saml/sp/metadata.php/default-sp',
  'contacts' => 
  array (
    0 => 
    array (
      'contactType' => 'technical',
      'givenName' => 'Administrator',
      'emailAddress' => 
      array (
        0 => 'rajeshwari@valuebound.com',
      ),
    ),
  ),
  'metadata-set' => 'shib13-sp-remote',
  'AssertionConsumerService' => 
  array (
    0 => 
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
      'Location' => 'http://cypress.local/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp',
      'index' => 0,
    ),
    1 => 
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:1.0:profiles:browser-post',
      'Location' => 'http://cypress.local/simplesaml/module.php/saml/sp/saml1-acs.php/default-sp',
      'index' => 1,
    ),
    2 => 
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
      'Location' => 'http://cypress.local/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp',
      'index' => 2,
    ),
    3 => 
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:1.0:profiles:artifact-01',
      'Location' => 'http://cypress.local/simplesaml/module.php/saml/sp/saml1-acs.php/default-sp/artifact',
      'index' => 3,
    ),
  ),
  'keys' => 
  array (
    0 => 
    array (
      'encryption' => false,
      'signing' => true,
      'type' => 'X509Certificate',
      'X509Certificate' => '
MIID+zCCAuOgAwIBAgIJAL3PQLjyUcjmMA0GCSqGSIb3DQEBCwUAMIGTMQswCQYD
VQQGEwJJTjESMBAGA1UECAwJS2FybmF0YWthMRIwEAYDVQQHDAlCYW5nYWxvcmUx
EzARBgNVBAoMClZhbHVlYm91bmQxEjAQBgNVBAsMCXRlY2huaWNhbDEPMA0GA1UE
AwwGc291bXlhMSIwIAYJKoZIhvcNAQkBFhNydmFyaWFyMTBAZ21haWwuY29tMB4X
DTE2MTEwODEyNDgwOVoXDTI2MTEwODEyNDgwOVowgZMxCzAJBgNVBAYTAklOMRIw
EAYDVQQIDAlLYXJuYXRha2ExEjAQBgNVBAcMCUJhbmdhbG9yZTETMBEGA1UECgwK
VmFsdWVib3VuZDESMBAGA1UECwwJdGVjaG5pY2FsMQ8wDQYDVQQDDAZzb3VteWEx
IjAgBgkqhkiG9w0BCQEWE3J2YXJpYXIxMEBnbWFpbC5jb20wggEiMA0GCSqGSIb3
DQEBAQUAA4IBDwAwggEKAoIBAQC63DAHRVCZPALlzceYdela48SHYj0arPzQ1f/v
0OFJ35QSGUR3eC/FmH1+R91/JhGYzwp3kCms/TfXG5UHEj1AWb0sOfISm92aWPvT
8zczLKCynz0tXx/F+/hsH0rAS8NqPXhtuKUNS6yRlvLb7LT2l2X8edtYiwSozfMZ
0xgV4slCIUlnyZh1csDLbZvb99IDqcM42xG2PcvSZqNG/VUCFhjZ2S0Fo+GRNfZv
PxIjo0WoIG4M8ZBzpuoiCrVVKpgbpNLtuDRFnINLR735ygJZU5hV0t7MypH3ldFr
FQH9JJSTvFXpaEgDTTfIDI7r3KubSXM/gZZRslMazGpJrg63AgMBAAGjUDBOMB0G
A1UdDgQWBBSLlBeHf0VWVKdNbuP2RunYjQTnaTAfBgNVHSMEGDAWgBSLlBeHf0VW
VKdNbuP2RunYjQTnaTAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBCwUAA4IBAQAc
mJjx7sKumgxCLcSS87Sc2WxRaMmFATRRrlDokpbx2ME4Mfg3KKdyOGWIdaXrdfbt
3Dr0EHk34UrSnH8LoSJvWf91JDh3S4xYtcO46a3Cx0etzgdxj2JiGqURQJajUCCD
fdnnwb657Hs58cKd450e/98Lo4ipaH7/GozpJnMo7YU78TsmS7ZNqpogg3lmDljX
eThb/v+LBUwYuXxt84jSK4SuE1dwQn2rWdVHiCmkt5D1yK2A/bdvfXsVSoLasvSa
E39hxGv2bV6khprLKsebEhDvpo+uw66um3bi7xbTE8iCZGjgCj3BfSQZKXBFglqP
Y0TwFxnFe9hyhQ3Hq+O6',
    ),
    1 => 
    array (
      'encryption' => true,
      'signing' => false,
      'type' => 'X509Certificate',
      'X509Certificate' => '
MIID+zCCAuOgAwIBAgIJAL3PQLjyUcjmMA0GCSqGSIb3DQEBCwUAMIGTMQswCQYD
VQQGEwJJTjESMBAGA1UECAwJS2FybmF0YWthMRIwEAYDVQQHDAlCYW5nYWxvcmUx
EzARBgNVBAoMClZhbHVlYm91bmQxEjAQBgNVBAsMCXRlY2huaWNhbDEPMA0GA1UE
AwwGc291bXlhMSIwIAYJKoZIhvcNAQkBFhNydmFyaWFyMTBAZ21haWwuY29tMB4X
DTE2MTEwODEyNDgwOVoXDTI2MTEwODEyNDgwOVowgZMxCzAJBgNVBAYTAklOMRIw
EAYDVQQIDAlLYXJuYXRha2ExEjAQBgNVBAcMCUJhbmdhbG9yZTETMBEGA1UECgwK
VmFsdWVib3VuZDESMBAGA1UECwwJdGVjaG5pY2FsMQ8wDQYDVQQDDAZzb3VteWEx
IjAgBgkqhkiG9w0BCQEWE3J2YXJpYXIxMEBnbWFpbC5jb20wggEiMA0GCSqGSIb3
DQEBAQUAA4IBDwAwggEKAoIBAQC63DAHRVCZPALlzceYdela48SHYj0arPzQ1f/v
0OFJ35QSGUR3eC/FmH1+R91/JhGYzwp3kCms/TfXG5UHEj1AWb0sOfISm92aWPvT
8zczLKCynz0tXx/F+/hsH0rAS8NqPXhtuKUNS6yRlvLb7LT2l2X8edtYiwSozfMZ
0xgV4slCIUlnyZh1csDLbZvb99IDqcM42xG2PcvSZqNG/VUCFhjZ2S0Fo+GRNfZv
PxIjo0WoIG4M8ZBzpuoiCrVVKpgbpNLtuDRFnINLR735ygJZU5hV0t7MypH3ldFr
FQH9JJSTvFXpaEgDTTfIDI7r3KubSXM/gZZRslMazGpJrg63AgMBAAGjUDBOMB0G
A1UdDgQWBBSLlBeHf0VWVKdNbuP2RunYjQTnaTAfBgNVHSMEGDAWgBSLlBeHf0VW
VKdNbuP2RunYjQTnaTAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBCwUAA4IBAQAc
mJjx7sKumgxCLcSS87Sc2WxRaMmFATRRrlDokpbx2ME4Mfg3KKdyOGWIdaXrdfbt
3Dr0EHk34UrSnH8LoSJvWf91JDh3S4xYtcO46a3Cx0etzgdxj2JiGqURQJajUCCD
fdnnwb657Hs58cKd450e/98Lo4ipaH7/GozpJnMo7YU78TsmS7ZNqpogg3lmDljX
eThb/v+LBUwYuXxt84jSK4SuE1dwQn2rWdVHiCmkt5D1yK2A/bdvfXsVSoLasvSa
E39hxGv2bV6khprLKsebEhDvpo+uw66um3bi7xbTE8iCZGjgCj3BfSQZKXBFglqP
Y0TwFxnFe9hyhQ3Hq+O6',
    ),
  ),
);

