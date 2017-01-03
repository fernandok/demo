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
    static $static_delta = 0;
    $parent_akamai_id = $element['#field_parents'][1];
    $element['value'] = $element + array(
      '#type' => 'textfield',
      '#title' => $this->t('Akamai Test'),
      '#default_value' => $value,
      '#prefix' => '<div id = "akamai-url-widget-' . $static_delta . '"><div class = "container-inline">',
      '#states' => array(
        'invisible' => array(
          '#edit-field-files-' . $parent_akamai_id . '-subform-field-akamai-url-test-0-value' => array('filled' => TRUE),
        )
      ),
    );
    $element['akamai_submit'] = array(
      '#type' => 'button',
      '#value' => 'akamai url' . $static_delta ,
      '#ajax' => [
        'callback' => array($this, 'akamaiUrl'),
        'wrapper' => 'akamai-url-widget-' . $static_delta,
      ],
      '#suffix' => '</div>',
      '#states' => array(
        'invisible' => array(
          '#edit-field-files-' . $parent_akamai_id . '-subform-field-akamai-url-test-0-value' => array('filled' => TRUE),
        )
      ),
    );
    $element['akamai_image'] = array (
      '#type' => 'markup',
     // '#markup' => '<div class = "akamai-image"><img src = "/themes/extranet/images/Session6_Image-130px.jpg" /></div>',
    );

    $akamai_value = explode('/', ($value));
    $akamai_descp_value = end($akamai_value);

    if (!empty($value)) {
      $element['akamai_description'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Akamai Description'),
        '#default_value' => $akamai_descp_value,
        '#suffix' => '</div>',
      );
    }
    else {
      $element['akamai_description'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Akamai Description'),
        '#default_value' => '',
        '#suffix' => '</div>',
      );
    }
    $static_delta++;
    return $element;
  }

  /**
   * Ajax callback to autofill akamai description field.
   */
  public function akamaiUrl(array &$form, FormStateInterface $form_state) {
    $parents = $form_state->getTriggeringElement()['#parents'];
    $parent_paragraph_id = $form_state->getTriggeringElement()['#parents'][1];
    $title = $form_state->getValues()['field_files'][$parent_paragraph_id]['subform']['field_akamai_url_test'][0]['value'];
    $akamai_value = explode('/', ($title));
    $akamai_descp_value = end($akamai_value);
    $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['akamai_description']['#value'] = $akamai_descp_value;
    $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['value']['#access'] = TRUE;
    $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['akamai_description']['#access'] = TRUE;
    return $form[$parents[0]]['widget'][$parents[1]][$parents[2]][$parents[3]]['widget'][$parents[4]]['akamai_description'];

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
