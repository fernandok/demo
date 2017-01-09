<?php

namespace Drupal\cypress\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'field_example_text' widget.
 *
 * @FieldWidget(
 *   id = "akami_url",
 *   module = "cypress",
 *   label = @Translation("Akamai Field Widget"),
 *   field_types = {
 *     "akami_url"
 *   }
 * )
 */
class AkamiUrlWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $items[$delta];
    $values = isset($items[$delta]) ? $items[$delta] : '';
    $parent_akamai_id = $element['#field_parents'][1];

    $element['value'] = $element + array(
      '#type' => 'textfield',
      '#default_value' => $values->value,
      '#states' => array(
        'invisible' => array(
          ':input[name="field_files[' . $parent_akamai_id . '][subform][field_file_type][value]"]' => array('checked' => FALSE),
        ),
      ),
      '#attributes' => ['class' => ['akamai-uri-field']],
      '#element_validate' => array(
        array($this, 'validateAkamaiUrl'),
      ),
    );

    $element['file_size'] = array(
      '#type' => 'hidden',
      '#title' => 'Akamai File Size',
      '#default_value' => $values->file_size,

    );
    $element['last_changed'] = array(
      '#type' => 'hidden',
      '#title' => 'Akamai Last changed date',
      '#default_value' => $values->last_changed,
    );
    return $element;
  }

  /**
   * Callback to validate Akamai url.
   */
  public function validateAkamaiUrl(&$element, &$form_state, $form) {
    $form_values = $form_state->getValues();
    $field_parents = $element['#field_parents'];
    if ($form_values[$field_parents[0]][$field_parents[1]][$field_parents[2]]['field_file_type']['value']) {
      if (!preg_match('/^(http|httpprivate):\/\/dlm.cypress.com.edgesuite.net/', $element['#value'])) {
        $form_state->setError($element, t('Please enter valid Akamai link.'));
      }
    }
  }

}


