<?php

namespace Drupal\cypress\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

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
    $element['value'] = $element + array(
      '#type' => 'textfield',
      '#default_value' => $value,
      '#prefix' => '<div id = "akamai-url-widget-' . $parent_akamai_id . '">',
    );
    $element['akamai_submit'] = array(
      '#name' => $parent_akamai_id . '_upload_button',
      '#type' => 'button',
      '#value' => 'akamai url',
      '#ajax' => [
        'callback' => array($this, 'akamaiUrl'),
        'wrapper' => 'akamai-url-widget-' . $parent_akamai_id,
      ],
      '#suffix' => '</div>',
    );
    $akamai_value = explode('/', ($value));
    $akamai_descp_value = end($akamai_value);

    if (!empty($value)) {
      $element['value']['#access'] = FALSE;
      $element['akamai_submit']['#access'] = FALSE;
      $element['akamai_description'] = array(
        '#type' => 'markup',
        '#markup' => '<div id = "akamai-remove-url-widget-' . $parent_akamai_id . '"><div class ="akamai-image"><img src = "/core/themes/classy/images/icons/x-office-spreadsheet.png" />' . $akamai_descp_value . '</div>',
      );
      $element['akamai_remove'] = array(
        '#name' => $parent_akamai_id . '_remove_button',
        '#type' => 'button',
        '#value' => 'akamai remove',
        '#ajax' => [
          'callback' => array($this, 'akamai_remove_url'),
          'wrapper' => 'akamai-remove-url-widget-' . $parent_akamai_id,
        ],
        '#suffix' => '</div>',
      );
    }
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
    $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['akamai_description']['#value'] = $akamai_descp_value;
    return [
      $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['akamai_description'],
      $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['akamai_remove'],
    ];

  }

  /**
   * Ajax callback to autofill akamai description field.
   */
  public function akamai_remove_url(array &$form, FormStateInterface $form_state) {
    $parents = $form_state->getTriggeringElement()['#parents'];
    $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['value']['#access'] = TRUE;
    $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['akamai_submit']['#access'] = TRUE;
    return [
      $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['value'],
      $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['akamai_submit'],
    ];
  }

  public function validate($element, FormStateInterface $form_state) {
  /*  $value = $element['#value'];
    if (strlen($value) == 0) {
      $form_state->setValueForElement($element, '');
      return;
    }
    if (!preg_match('/^#([a-f0-9]{6})$/iD', strtolower($value))) {
      $form_state->setError($element, t("Color must be a 6-digit hexadecimal value, suitable for CSS."));
    }*/
  }

}
