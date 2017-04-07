<?php

namespace Drupal\cypress_store_vendor\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Avnet inventory entity entity.
 *
 * @ConfigEntityType(
 *   id = "avnet_inventory_entity",
 *   label = @Translation("Avnet inventory entity"),
 *   handlers = {},
 *   config_prefix = "avnet_inventory_entity",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "details" = "details",
 *     "changed" = "changed"
 *   },
 *   links = {}
 * )
 */
class AvnetInventoryEntity extends ConfigEntityBase implements AvnetInventoryEntityInterface {

  /**
   * The Avnet inventory entity ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Avnet inventory entity label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Avnet inventory details.
   *
   * @var string
   */
  protected $details;

  /**
   * The Avnet inventory last checked.
   *
   * @var string
   */
  protected $changed;

}
