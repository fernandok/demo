<?php

namespace Drupal\cypress_store_migration\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\address\Plugin\Field\FieldType\AddressItem;
use Drupal\Core\Locale\CountryManager;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use Drupal\migrate\MigrateSkipRowException;


/**
 * Provides a CypressStoreMigration migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "cypressstoremigration"
 * )
 */
class CypressStoreMigrationAddress extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Plugin logic goes here.
    $country_name = $row->getSourceProperty('COUNTRYNAME');
    $address_line1 = $row->getSourceProperty('ADDRESS');
    $address_line2 = $row->getSourceProperty('ADDRESSMORE');
    $organization = $row->getSourceProperty('COMPANYNAME');
    $administrative_area = $row->getSourceProperty('REGIONNAME');
    $locality = $row->getSourceProperty('CITYNAME');
    $given_name = $row->getSourceProperty('FIRSTNAME');
    $family_name = $row->getSourceProperty('LASTNAME');
    $tel_code = $row->getSourceProperty('TELEPHONEAREACODE');
    $tel_number = $row->getSourceProperty('TELEPHONE');
    $contact = $tel_code . '-' . $tel_number;
    $postal_code = $row->getSourceProperty('POSTALCODE');

    // to get he country code.
    $country_list = CountryManager::getStandardList();
    $country_code = array_search($country_name, $country_list);

    //to get the state value of respective country
    $subdivisionRepository = new SubdivisionRepository();
    $states = $subdivisionRepository->getAll([$country_code]);
    foreach ($states as $state) {
      $municipalities = $state->getName();
      if ($administrative_area == $municipalities) {
        $state_code = $state->getCode();
      }
      if($country_code == 'CN') {
        if($state->hasChildren) {
          $locality = $state->getChildren();
        }
      }
    }


    // return new address values from csv
    $address_new_values = array(
      "country_code" => $country_code,
      "administrative_area" => $state_code,
      "locality" => $locality,
      "postal_code" => $postal_code,
      "address_line1" => $address_line1,
      "address_line2" => $address_line2,
      "given_name" => $given_name,
      "family_name" => $family_name,
      "organization" => $organization,
      "contact" => $contact,
    );
    return $address_new_values;

  }
}
