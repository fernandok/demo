<?php

namespace Drupal\cypress_store_vendor;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class CypressStoreVendor.
 *
 * @package Drupal\cypress_store_vendor.
 */
class CypressStoreVendor extends Event {
  protected $message;
  const ERROR = 'event.error';

  /**
   * {@inheritdoc}
   */
  public function __construct($message) {
    $this->message = $message;
  }

  /**
   * {@inheritdoc}
   */
  public function getMessage() {
    return $this->message;
  }

}
