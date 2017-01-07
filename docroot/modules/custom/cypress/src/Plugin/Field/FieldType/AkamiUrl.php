<?php

namespace Drupal\cypress\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Component\Utility\Bytes;

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

  public function preSave() {
    parent::preSave();

//    $query =\Drupal::database()->select('paragraph__field_akamai_url', 'pt');
//    $query->fields('pt', ['field_akamai_url_value']);
//    $query->condition('entity_id', '5567');
//    $results = $query->execute()->fetchAll();
//    $current_value = $results[0]->field_akamai_url_value;
//
//    if ($current_value != $this->values['value']) {
      $get_url = $this->values['value'];
      $get_direct_link = str_replace(
        'http://dlm.cypress.com.edgesuite.net/downloadmanager',
        'http://dlm.cypress.com.edgesuite.net/akdlm/downloadmanager',
        $get_url
      );
      $url = $get_direct_link;
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_HEADER, TRUE);
      curl_setopt($ch, CURLOPT_NOBODY, TRUE);
      // Make it a HEAD request.
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      $head = curl_exec($ch);
      $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
      $this->values['file_size'] = $size;
      $this->values['last_changed'] = time();
      curl_close($ch);
  }
  
}
