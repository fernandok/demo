<?php

namespace Drupal\cypress\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("cypresscheckbox_field")
 */
class CypressCheckboxField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing -- to override the parent query.
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['hide_alter_empty'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    // To Create Checkbox inside views.
    $field_target_id = $values->_relationship_entities['field_file_target_id']->fid;
    $fid = $field_target_id->get(0)->getValue()['value'];
    $output = '<input type="checkbox" name="download_file_selector" value=' . $fid . ' class="download_file_selector" />';
    $checkbox = check_markup($output, 'full_html');
    return $checkbox;
  }

}

