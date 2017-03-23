<?php

namespace Drupal\cypress_store_vendor\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Vendor entity entity.
 *
 * @ConfigEntityType(
 *   id = "vendor_entity",
 *   label = @Translation("Vendor entity"),
 *   handlers = {
 *     "list_builder" = "Drupal\cypress_store_vendor\VendorEntityListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cypress_store_vendor\Form\VendorEntityForm",
 *       "edit" = "Drupal\cypress_store_vendor\Form\VendorEntityForm",
 *       "delete" = "Drupal\cypress_store_vendor\Form\VendorEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cypress_store_vendor\VendorEntityHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "vendor_entity",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "description" = "description"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/vendor_entity/{vendor_entity}",
 *     "add-form" = "/admin/structure/vendor_entity/add",
 *     "edit-form" = "/admin/structure/vendor_entity/{vendor_entity}/edit",
 *     "delete-form" = "/admin/structure/vendor_entity/{vendor_entity}/delete",
 *     "collection" = "/admin/structure/vendor_entity"
 *   }
 * )
 */
class VendorEntity extends ConfigEntityBase {
  /**
   * The Vendor entity ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Vendor entity label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Vendor entity description.
   *
   * @var string
   */
  protected $description;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

}
