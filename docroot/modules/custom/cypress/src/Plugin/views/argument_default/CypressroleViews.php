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
    public function getArgument()
    {
        $user_role = \Drupal::currentUser()->getRoles($exclude_locked_roles = true)[0];
        if ($user_role == 'all_distributors') {
          return 'all_distributors';
        }
        elseif ($user_role == 'sales_rep') {
          return 'sales_rep';
        }
        elseif ($user_role == 'cypress_employees') {
          return 'cypress_employees';
        }
        else
          return NULL;
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