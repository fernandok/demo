<?php
/**
 * SAML 2.0 remote IdP metadata for SimpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-idp-remote
 */

/*
 * Guest IdP. allows users to sign up and register. Great for testing!
 */
if($_SERVER['SERVER_NAME'] == 'cypressextdev.prod.acquia-sites.com') {
$metadata['http://cypresscomdev.prod.acquia-sites.com/simplesaml/saml2/idp/metadata.php'] = array(
  'entityid' => 'http://cypresscomdev.prod.acquia-sites.com/simplesaml/saml2/idp/metadata.php',
  'contacts' =>
    array(
      0 =>
				array(
					'contactType' => 'technical',
					'givenName' => 'Cypress',
					'surName' => 'Webmaster',
					'emailAddress' =>
						array(
							0 => 'webmaster@cypress.com',
						),
				),
		),
	'metadata-set' => 'saml20-idp-remote',
	'SingleSignOnService' =>
		array(
			0 =>
				array(
					'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
					'Location' => 'http://cypresscomdev.prod.acquia-sites.com/simplesaml/saml2/idp/SSOService.php',
				),
		),
	'SingleLogoutService' =>
		array(
			0 =>
				array(
					'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
					'Location' => 'http://cypresscomdev.prod.acquia-sites.com/simplesaml/saml2/idp/SingleLogoutService.php',
				),
		),
	'ArtifactResolutionService' =>
		array(),
	'NameIDFormats' =>
		array(
			0 => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
		),
	'keys' =>
		array(
			0 =>
				array(
					'encryption' => FALSE,
					'signing' => TRUE,
					'type' => 'X509Certificate',
					'X509Certificate' => 'MIID/zCCAuegAwIBAgIJAMxzUU6MP0VIMA0GCSqGSIb3DQEBBQUAMIGUMQswCQYDVQQGEwJVUzETMBEGA1UECAwKQ2FsaWZvcm5pYTERMA8GA1UEBwwIU2FuIEpvc2UxEDAOBgNVBAoMB0N5cHJlc3MxEDAOBgNVBAsMB0N5cHJlc3MxEDAOBgNVBAMMB0N5cHJlc3MxJzAlBgkqhkiG9w0BCQEWGGN1c3RvbWVyY2FyZUBjeXByZXNzLmNvbTAgFw0xNTAyMjAxMzM0MjFaGA8yMTE1MDIxNjEzMzQyMVowgZQxCzAJBgNVBAYTAlVTMRMwEQYDVQQIDApDYWxpZm9ybmlhMREwDwYDVQQHDAhTYW4gSm9zZTEQMA4GA1UECgwHQ3lwcmVzczEQMA4GA1UECwwHQ3lwcmVzczEQMA4GA1UEAwwHQ3lwcmVzczEnMCUGCSqGSIb3DQEJARYYY3VzdG9tZXJjYXJlQGN5cHJlc3MuY29tMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuGEO5S2xK14vP8vcwS97Re74L8HH/P5+hbpm1gcSndf5KblLvnVySQuIdnUTZ6PyvoiINHtKtwwAeoiMgT/3N/GExf/uTekWG6/WN6s9fgxQqPArRxevD5VwExUgmYmfrMAgbu/xHI6h9SiifXOGJRq0Xs4Ok7782GnTPBO2YEcNiHo5sSnxZTP/K35vAJSuLvhTxaxcqEkAN1QYW4zqE4+ndUUjz/AX8/JoYYpfKpk5YijfvAeVDg3hJz6yI89ROtNA8TP06h0y+GhIwkjtit/TOcbUG5WPp1GnhN9C8vF+81oRKJYD8hVPORsPLTCr9O8zAsVOpPdqzO49vzah2wIDAQABo1AwTjAdBgNVHQ4EFgQUjLBpGJO0tR4XT4bhdNbAsEWpWpQwHwYDVR0jBBgwFoAUjLBpGJO0tR4XT4bhdNbAsEWpWpQwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOCAQEAN595WxvW4NFeapQ+73PLk4yLU2RUJ16wsGjE+zejYOtA7vclpEIYtQByVoWeDopPhbCrnnepBkcde4gn+rvNPK/flKCufc9wzKFAK9LsZCtecrUkTsnMLbcV+OUF+jmDbdCN90pmGZg+lb8vIjPuB2ny7jV4miRLup50kXYJHk4FSd14TD2+dD0SXMIE47MDV8NVuQr65qG//SamV0zBZpckahd5VZUItXaCd0q7oTj1y/9dpd2ZkCLtyQ2WIcxYgWfpxT3KhPWqDG5NE7b/krlv5xcUCMJJmmjlzPSzB4sPuYaYdkw+RVXghfOVlu12MuTxGfxbkQH4mXZrl6hyVg==',
				),
			1 =>
				array(
					'encryption' => TRUE,
					'signing' => FALSE,
					'type' => 'X509Certificate',
					'X509Certificate' => 'MIID/zCCAuegAwIBAgIJAMxzUU6MP0VIMA0GCSqGSIb3DQEBBQUAMIGUMQswCQYDVQQGEwJVUzETMBEGA1UECAwKQ2FsaWZvcm5pYTERMA8GA1UEBwwIU2FuIEpvc2UxEDAOBgNVBAoMB0N5cHJlc3MxEDAOBgNVBAsMB0N5cHJlc3MxEDAOBgNVBAMMB0N5cHJlc3MxJzAlBgkqhkiG9w0BCQEWGGN1c3RvbWVyY2FyZUBjeXByZXNzLmNvbTAgFw0xNTAyMjAxMzM0MjFaGA8yMTE1MDIxNjEzMzQyMVowgZQxCzAJBgNVBAYTAlVTMRMwEQYDVQQIDApDYWxpZm9ybmlhMREwDwYDVQQHDAhTYW4gSm9zZTEQMA4GA1UECgwHQ3lwcmVzczEQMA4GA1UECwwHQ3lwcmVzczEQMA4GA1UEAwwHQ3lwcmVzczEnMCUGCSqGSIb3DQEJARYYY3VzdG9tZXJjYXJlQGN5cHJlc3MuY29tMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuGEO5S2xK14vP8vcwS97Re74L8HH/P5+hbpm1gcSndf5KblLvnVySQuIdnUTZ6PyvoiINHtKtwwAeoiMgT/3N/GExf/uTekWG6/WN6s9fgxQqPArRxevD5VwExUgmYmfrMAgbu/xHI6h9SiifXOGJRq0Xs4Ok7782GnTPBO2YEcNiHo5sSnxZTP/K35vAJSuLvhTxaxcqEkAN1QYW4zqE4+ndUUjz/AX8/JoYYpfKpk5YijfvAeVDg3hJz6yI89ROtNA8TP06h0y+GhIwkjtit/TOcbUG5WPp1GnhN9C8vF+81oRKJYD8hVPORsPLTCr9O8zAsVOpPdqzO49vzah2wIDAQABo1AwTjAdBgNVHQ4EFgQUjLBpGJO0tR4XT4bhdNbAsEWpWpQwHwYDVR0jBBgwFoAUjLBpGJO0tR4XT4bhdNbAsEWpWpQwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOCAQEAN595WxvW4NFeapQ+73PLk4yLU2RUJ16wsGjE+zejYOtA7vclpEIYtQByVoWeDopPhbCrnnepBkcde4gn+rvNPK/flKCufc9wzKFAK9LsZCtecrUkTsnMLbcV+OUF+jmDbdCN90pmGZg+lb8vIjPuB2ny7jV4miRLup50kXYJHk4FSd14TD2+dD0SXMIE47MDV8NVuQr65qG//SamV0zBZpckahd5VZUItXaCd0q7oTj1y/9dpd2ZkCLtyQ2WIcxYgWfpxT3KhPWqDG5NE7b/krlv5xcUCMJJmmjlzPSzB4sPuYaYdkw+RVXghfOVlu12MuTxGfxbkQH4mXZrl6hyVg==',
				),
		),
);
}

elseif ($_SERVER['SERVER_NAME'] == 'cypressextstg.prod.acquia-sites.com') {
	$metadata['http://cypresscomstg.prod.acquia-sites.com/simplesaml/saml2/idp/metadata.php'] = array (
		'entityid' => 'http://cypresscomstg.prod.acquia-sites.com/simplesaml/saml2/idp/metadata.php',
		'contacts' =>
			array (
				0 =>
					array (
						'contactType' => 'technical',
						'givenName' => 'Cypress',
						'surName' => 'Webmaster',
						'emailAddress' =>
							array (
								0 => 'webmaster@cypress.com',
							),
					),
			),
		'metadata-set' => 'saml20-idp-remote',
		'SingleSignOnService' =>
			array (
				0 =>
					array (
						'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
						'Location' => 'http://cypresscomstg.prod.acquia-sites.com/simplesaml/saml2/idp/SSOService.php',
					),
			),
		'SingleLogoutService' =>
			array (
				0 =>
					array (
						'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
						'Location' => 'http://cypresscomstg.prod.acquia-sites.com/simplesaml/saml2/idp/SingleLogoutService.php',
					),
			),
		'ArtifactResolutionService' =>
			array (
			),
		'NameIDFormats' =>
			array (
				0 => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
			),
		'keys' =>
			array (
				0 =>
					array (
						'encryption' => false,
						'signing' => true,
						'type' => 'X509Certificate',
						'X509Certificate' => 'MIID/zCCAuegAwIBAgIJAMxzUU6MP0VIMA0GCSqGSIb3DQEBBQUAMIGUMQswCQYDVQQGEwJVUzETMBEGA1UECAwKQ2FsaWZvcm5pYTERMA8GA1UEBwwIU2FuIEpvc2UxEDAOBgNVBAoMB0N5cHJlc3MxEDAOBgNVBAsMB0N5cHJlc3MxEDAOBgNVBAMMB0N5cHJlc3MxJzAlBgkqhkiG9w0BCQEWGGN1c3RvbWVyY2FyZUBjeXByZXNzLmNvbTAgFw0xNTAyMjAxMzM0MjFaGA8yMTE1MDIxNjEzMzQyMVowgZQxCzAJBgNVBAYTAlVTMRMwEQYDVQQIDApDYWxpZm9ybmlhMREwDwYDVQQHDAhTYW4gSm9zZTEQMA4GA1UECgwHQ3lwcmVzczEQMA4GA1UECwwHQ3lwcmVzczEQMA4GA1UEAwwHQ3lwcmVzczEnMCUGCSqGSIb3DQEJARYYY3VzdG9tZXJjYXJlQGN5cHJlc3MuY29tMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuGEO5S2xK14vP8vcwS97Re74L8HH/P5+hbpm1gcSndf5KblLvnVySQuIdnUTZ6PyvoiINHtKtwwAeoiMgT/3N/GExf/uTekWG6/WN6s9fgxQqPArRxevD5VwExUgmYmfrMAgbu/xHI6h9SiifXOGJRq0Xs4Ok7782GnTPBO2YEcNiHo5sSnxZTP/K35vAJSuLvhTxaxcqEkAN1QYW4zqE4+ndUUjz/AX8/JoYYpfKpk5YijfvAeVDg3hJz6yI89ROtNA8TP06h0y+GhIwkjtit/TOcbUG5WPp1GnhN9C8vF+81oRKJYD8hVPORsPLTCr9O8zAsVOpPdqzO49vzah2wIDAQABo1AwTjAdBgNVHQ4EFgQUjLBpGJO0tR4XT4bhdNbAsEWpWpQwHwYDVR0jBBgwFoAUjLBpGJO0tR4XT4bhdNbAsEWpWpQwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOCAQEAN595WxvW4NFeapQ+73PLk4yLU2RUJ16wsGjE+zejYOtA7vclpEIYtQByVoWeDopPhbCrnnepBkcde4gn+rvNPK/flKCufc9wzKFAK9LsZCtecrUkTsnMLbcV+OUF+jmDbdCN90pmGZg+lb8vIjPuB2ny7jV4miRLup50kXYJHk4FSd14TD2+dD0SXMIE47MDV8NVuQr65qG//SamV0zBZpckahd5VZUItXaCd0q7oTj1y/9dpd2ZkCLtyQ2WIcxYgWfpxT3KhPWqDG5NE7b/krlv5xcUCMJJmmjlzPSzB4sPuYaYdkw+RVXghfOVlu12MuTxGfxbkQH4mXZrl6hyVg==',
					),
				1 =>
					array (
						'encryption' => true,
						'signing' => false,
						'type' => 'X509Certificate',
						'X509Certificate' => 'MIID/zCCAuegAwIBAgIJAMxzUU6MP0VIMA0GCSqGSIb3DQEBBQUAMIGUMQswCQYDVQQGEwJVUzETMBEGA1UECAwKQ2FsaWZvcm5pYTERMA8GA1UEBwwIU2FuIEpvc2UxEDAOBgNVBAoMB0N5cHJlc3MxEDAOBgNVBAsMB0N5cHJlc3MxEDAOBgNVBAMMB0N5cHJlc3MxJzAlBgkqhkiG9w0BCQEWGGN1c3RvbWVyY2FyZUBjeXByZXNzLmNvbTAgFw0xNTAyMjAxMzM0MjFaGA8yMTE1MDIxNjEzMzQyMVowgZQxCzAJBgNVBAYTAlVTMRMwEQYDVQQIDApDYWxpZm9ybmlhMREwDwYDVQQHDAhTYW4gSm9zZTEQMA4GA1UECgwHQ3lwcmVzczEQMA4GA1UECwwHQ3lwcmVzczEQMA4GA1UEAwwHQ3lwcmVzczEnMCUGCSqGSIb3DQEJARYYY3VzdG9tZXJjYXJlQGN5cHJlc3MuY29tMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuGEO5S2xK14vP8vcwS97Re74L8HH/P5+hbpm1gcSndf5KblLvnVySQuIdnUTZ6PyvoiINHtKtwwAeoiMgT/3N/GExf/uTekWG6/WN6s9fgxQqPArRxevD5VwExUgmYmfrMAgbu/xHI6h9SiifXOGJRq0Xs4Ok7782GnTPBO2YEcNiHo5sSnxZTP/K35vAJSuLvhTxaxcqEkAN1QYW4zqE4+ndUUjz/AX8/JoYYpfKpk5YijfvAeVDg3hJz6yI89ROtNA8TP06h0y+GhIwkjtit/TOcbUG5WPp1GnhN9C8vF+81oRKJYD8hVPORsPLTCr9O8zAsVOpPdqzO49vzah2wIDAQABo1AwTjAdBgNVHQ4EFgQUjLBpGJO0tR4XT4bhdNbAsEWpWpQwHwYDVR0jBBgwFoAUjLBpGJO0tR4XT4bhdNbAsEWpWpQwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOCAQEAN595WxvW4NFeapQ+73PLk4yLU2RUJ16wsGjE+zejYOtA7vclpEIYtQByVoWeDopPhbCrnnepBkcde4gn+rvNPK/flKCufc9wzKFAK9LsZCtecrUkTsnMLbcV+OUF+jmDbdCN90pmGZg+lb8vIjPuB2ny7jV4miRLup50kXYJHk4FSd14TD2+dD0SXMIE47MDV8NVuQr65qG//SamV0zBZpckahd5VZUItXaCd0q7oTj1y/9dpd2ZkCLtyQ2WIcxYgWfpxT3KhPWqDG5NE7b/krlv5xcUCMJJmmjlzPSzB4sPuYaYdkw+RVXghfOVlu12MuTxGfxbkQH4mXZrl6hyVg==',
					),
			),
	);
}