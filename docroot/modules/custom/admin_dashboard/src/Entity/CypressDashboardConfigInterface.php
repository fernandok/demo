<?php

namespace Drupal\admin_dashboard\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Cypress dashboard config entities.
 */
interface CypressDashboardConfigInterface extends ConfigEntityInterface {

  /**
   * Get the url path.
   */
  public function getUrlPath();

  /**
   * Get the weight.
   */
  public function getWeight();

  // Add get/set methods for your configuration properties here.
}
