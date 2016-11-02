<?php

namespace Drupal\admin_dashboard\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Cypress dashboard config entity.
 *
 * @ConfigEntityType(
 *   id = "cypress_dashboard_config",
 *   label = @Translation("Cypress dashboard config"),
 *   handlers = {
 *     "list_builder" =
 *   "Drupal\admin_dashboard\CypressDashboardConfigListBuilder",
 *     "form" = {
 *       "add" = "Drupal\admin_dashboard\Form\CypressDashboardConfigForm",
 *       "edit" = "Drupal\admin_dashboard\Form\CypressDashboardConfigForm",
 *       "delete" =
 *   "Drupal\admin_dashboard\Form\CypressDashboardConfigDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" =
 *   "Drupal\admin_dashboard\CypressDashboardConfigHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cypress_dashboard_config",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" =
 *   "/admin/structure/cypress_dashboard_config/{cypress_dashboard_config}",
 *     "add-form" = "/admin/structure/cypress_dashboard_config/add",
 *     "edit-form" =
 *   "/admin/structure/cypress_dashboard_config/{cypress_dashboard_config}/edit",
 *     "delete-form" =
 *   "/admin/structure/cypress_dashboard_config/{cypress_dashboard_config}/delete",
 *     "collection" = "/admin/structure/cypress_dashboard_config"
 *   }
 * )
 */
class CypressDashboardConfig extends ConfigEntityBase implements CypressDashboardConfigInterface {

  /**
   * The Cypress dashboard config ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Cypress dashboard config label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Cypress dashboard config url_path.
   *
   * @var string
   */
  protected $url_path;

  /**
   * The Cypress dashboard menu weight.
   *
   * @var string
   */
  protected $weight;

  /**
   * @return string
   */
  public function getUrlPath() {
    return $this->url_path;
  }

  /**
   * @return int
   */
  public function getWeight() {
    return $this->weight;
  }
}
