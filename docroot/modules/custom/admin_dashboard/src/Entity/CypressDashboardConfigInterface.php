<?php

namespace Drupal\admin_dashboard\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Cypress dashboard config entities.
 */
interface CypressDashboardConfigInterface extends ConfigEntityInterface {
  public function getUrlPath();
  // Add get/set methods for your configuration properties here.
}
