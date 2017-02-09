<?php

namespace Drupal\file_download_tracker\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("fdt_entity_name_field")
 */
class FdtEntityNameField extends FieldPluginBase {

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
    $type = $values->file_download_entity_field_data_entity_type;
    $id = $values->file_download_entity_field_data_entity_id;
    $query = \Drupal::database()->select('paragraph__field_file', 'pff');
    $query->fields('pff', ['field_file_description']);
    $query->condition('field_file_target_id', $id);
    $results = $query->execute()->fetchAll();
    $file_descp = $results[0]->field_file_description;
    if(is_numeric($id)) {
      if ($type == 'file') {
        if (!empty($file_descp)) {
          $entity_name = $file_descp;
        }
        else {
          $file_load = File::load($id);
          $entity_name = $file_load->getFilename();
        }
      }
      else {
        $node_load = Node::load($id);
        $entity_name = $node_load->getTitle();
      }
    }
    else {
      $entity_name = $GLOBALS['base_url']. "/" .$id;
    }

    return $entity_name;
  }

}


