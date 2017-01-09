<?php

namespace Drupal\cypress\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'field_example_rgb' field type.
 *
 * @FieldType(
 *   id = "akami_url",
 *   label = @Translation("Akamai Url"),
 *   module = "cypress",
 *   description = @Translation("Demonstrates a field composed of an RGB color."),
 *   default_widget = "akami_url",
 *   default_formatter = "akami_url",
 * )
 */
class AkamiUrl extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'value' => array(
          'type' => 'text',
          'size' => 'tiny',
          'not null' => FALSE,
        ),
        'file_size' => array(
          'type' => 'text',
          'not null' => FALSE,
        ),
        'last_changed' => array(
          'type' => 'text',
          'not null' => FALSE,
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Akamai Url'));
    $properties['file_size'] = DataDefinition::create('string')
      ->setLabel(t('Akamai File Size'));
    $properties['last_changed'] = DataDefinition::create('string')
      ->setLabel(t('Last Changed'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    parent::preSave();

    $parent_entity = $this->getEntity();
    if ($parent_entity->isNew()) {
      // To add new Akamai Url.
      $this->setAdditionalProperties();
    }
    else {
      // To update the existing Akamai_Url if changed.
      $get_entity_id = $this->getEntity()->id();
      $query = \Drupal::database()->select('paragraph__field_akamai_url', 'pt');
      $query->fields('pt', ['field_akamai_url_value']);
      $query->condition('entity_id', $get_entity_id);
      $results = $query->execute()->fetchAll();
      $current_value = $results[0]->field_akamai_url_value;
      if ($current_value != $this->values['value']) {
        $this->setAdditionalProperties();
      }
    }
  }

  /**
   * Method to set or update akamai file size and last modified date.
   */
  private function setAdditionalProperties() {
    $file_direct_link = $this->getAkamaiDirectDownloadLink();
    $size = $this->getExternalFileSize($file_direct_link);
    $this->properties['file_size']->setValue($size, TRUE);
    $this->properties['last_changed']->setValue(time(), TRUE);
  }

  /**
   * Method to get Akamai direct download link.
   *
   * @return string
   *   Akamai file direct download link.
   */
  private function getAkamaiDirectDownloadLink() {
    $akamai_url = $this->values['value'];
    $akamai_direct_link = str_replace(
      'http://dlm.cypress.com.edgesuite.net/downloadmanager',
      'http://dlm.cypress.com.edgesuite.net/akdlm/downloadmanager',
      $akamai_url
    );

    return $akamai_direct_link;
  }

  /**
   * Private method to get external akamai file size.
   *
   * @param string $file_url
   *   External file url.
   *
   * @return int
   *   File size.
   */
  private function getExternalFileSize($file_url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_NOBODY, TRUE);
    // Make it a HEAD request.
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_URL, $file_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $head = curl_exec($ch);
    $file_size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    curl_close($ch);
    return $file_size;
  }

}
