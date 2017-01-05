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
    $value = isset($items[$delta]->value) ? $items[$delta]->value : '';
    $parent_akamai_id = $element['#field_parents'][1];
    $form['#attached']['library'][] = 'cypress/akamai-styling';
    $element['value'] = $element + array(
      '#type' => 'textfield',
      '#default_value' => $value,
//      '#prefix' => '<div id = "akamai-url-widget-' . $parent_akamai_id . '">',
      '#states' => array(
        'invisible' => array(
          ':input[name="field_files[' . $parent_akamai_id . '][subform][field_file_type][value]"]' => array('checked' => FALSE),
        ),
      ),
      '#attributes' => ['class' => ['akamai-uri-field']],
    );
//    $element['akamai_submit'] = array(
//      '#name' => $parent_akamai_id . '_upload_button',
//      '#type' => 'button',
//      '#value' => 'Add Akamai file',
//      '#ajax' => [
//        'callback' => array($this, 'akamaiUrl'),
//        'wrapper' => 'akamai-url-widget-' . $parent_akamai_id,
//      ],
//      '#states' => array(
//        'disabled' => array(
//          ':input[name="field_files[' . $parent_akamai_id . '][subform][field_akamai_url][0][value]"]' => array('filled' => FALSE),
//        ),
//        'invisible' => array(
//          ':input[name="field_files[' . $parent_akamai_id . '][subform][field_file_type][value]"]' => array('checked' => FALSE),
//        ),
//      ),
//      '#attributes' => ['class' => ['akamai-add-button']],
//    );
//    $akamai_value = explode('/', ($value));
//    $akamai_descp_value = end($akamai_value);
//    if (!empty($value)) {
//      $element['akamai_remove'] = array(
//        '#name' => $parent_akamai_id . '_remove_button',
//        '#type' => 'button',
//        '#value' => 'Remove Akamai file',
//        '#ajax' => [
//          'callback' => array($this, 'akamai_remove_url'),
//          'wrapper' => 'akamai-url-widget-' . $parent_akamai_id,
//        ],
//        '#suffix' => '</div>',
//        '#states' => array(
//          'invisible' => array(
//            ':input[name="field_files[' . $parent_akamai_id . '][subform][field_file_type][value]"]' => array('checked' => FALSE),
//          ),
//        ),
//        '#prefix' => '<div class ="akamai-image"><img src = "/core/themes/classy/images/icons/x-office-spreadsheet.png" />' . $akamai_descp_value . '</div>',
//      );
//      if ($form['field_files'][$parent_akamai_id]['subform']['field_file_type']['value']) {
//        $element['akamai_remove']['#prefix'] = '<div id = "akamai-remove-url-widget-' . $parent_akamai_id . '"><div class ="akamai-image"><img src = "/core/themes/classy/images/icons/x-office-spreadsheet.png" />' . $akamai_descp_value . '</div>';
//      }
//      elseif (!empty($form['field_files'])) {
//        $element['akamai_remove']['#prefix'] = '';
//      }
//    }
//    else {
//      $element['akamai_submit']['#suffix'] = '</div>';
//    }
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
//    $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['value']['#access'] = FALSE;
//    $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['akamai_submit']['#access'] = FALSE;
//    $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['akamai_remove']['#access'] = TRUE;
    return [
      $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['akamai_remove'],
    ];
  }

  /**
   * Ajax callback to autofill akamai description field.
   */
  public function akamai_remove_url(array &$form, FormStateInterface $form_state) {
    $parents = $form_state->getTriggeringElement()['#parents'];
    $parent_paragraph_id = $form_state->getTriggeringElement()['#parents'][1];
//    $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['value']['#value'] = '';
//    $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['value']['#access'] = TRUE;
//    $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['akamai_submit']['#access'] = TRUE;
//    $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['akamai_remove']['#access'] = FALSE;
    $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['akamai_remove']['#prefix'] = '';
    return [
      $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['value'],
      $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['akamai_submit'],
    ];
  }

}
