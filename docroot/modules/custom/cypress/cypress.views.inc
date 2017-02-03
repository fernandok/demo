<?php

/**
 * @file
 * Contains no_views_php\no_views_php.views.inc.
 * Provide a custom views field data that isn't tied to any other module.
 */

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Render\Markup;
use Drupal\field\FieldConfigInterface;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\system\ActionConfigEntityInterface;

/**
 * Implements hook_views_data().
 */
function cypress_views_data() {

  $data['views']['table']['group'] = t('Cypress Custom Global');
  $data['views']['table']['join'] = array(
    // #global is a special flag which allows a table to appear all the time.
    '#global' => array(),
  );

  $data['views']['cypresscheckbox_field'] = array(
    'title' => t('Checkbox Field'),
    'help' => t('Enable to include checkbox and run PHP code in your modules as part of a view'),
    'field' => array(
      'id' => 'cypresscheckbox_field',
    ),
  );

  return $data;
}
