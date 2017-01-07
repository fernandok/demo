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
    $form['#attached']['library'][] = 'cypress/akamai-styling';
    $element['value'] = $element + array(
      '#type' => 'textfield',
      '#default_value' => $values->value,
      '#states' => array(
        'invisible' => array(
          ':input[name="field_files[' . $parent_akamai_id . '][subform][field_file_type][value]"]' => array('checked' => FALSE),
        ),
      ),
      '#attributes' => ['class' => ['akamai-uri-field']],
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
   * Ajax callback to autofill akamai description field.
   */
  public function akamaiUrl(array &$form, FormStateInterface $form_state) {
    $parents = $form_state->getTriggeringElement()['#parents'];
    $parent_paragraph_id = $form_state->getTriggeringElement()['#parents'][1];
    $title = $form_state->getValues()['field_files'][$parent_paragraph_id]['subform']['field_akamai_url'][0]['value'];
    $akamai_value = explode('/', ($title));
    $akamai_descp_value = end($akamai_value);
    $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['akamai_remove']['#prefix'] = '<div class ="akamai-image"><img src = "/core/themes/classy/images/icons/x-office-spreadsheet.png" />' . $akamai_descp_value . '</div>';
    return [
      $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['akamai_remove'],
    ];
  }

  /**
   * Ajax callback to autofill akamai description field.
   */
  public function akamai_remove_url(array &$form, FormStateInterface $form_state) {
    $parents = $form_state->getTriggeringElement()['#parents'];
    return [
      $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['value'],
      $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['akamai_submit'],
    ];
  }

}


