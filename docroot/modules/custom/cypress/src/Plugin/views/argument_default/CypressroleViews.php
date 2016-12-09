<?php

namespace Drupal\cypress\Plugin\views\argument_default;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;

/**
 * Cypressrole Parameter Selector.
 *
 * @ViewsArgumentDefault(
 *   id = "cypressrole",
 *   title = @Translation("Cypress Role")
 * )
 */
class CypressroleViews extends ArgumentDefaultPluginBase implements CacheableDependencyInterface {

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    $user_role = \Drupal::currentUser()->getRoles(TRUE)[0];
    return $user_role;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['user'];
  }

}
