<?php

namespace Drupal\cypress_store_vendor\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class OrderSubmitSubscriber.
 *
 * @package Drupal\cypress_store_vendor
 */
class OrderSubmitSubscriber implements EventSubscriberInterface {


  /**
   * Constructor.
   */
  public function __construct() {

  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events['commerce_order.place.post_transition'] = ['submitOrderToVendor'];

    return $events;
  }

  /**
   * This method is called whenever the commerce_order.place.post_transition event is
   * dispatched.
   *
   * @param GetResponseEvent $event
   */
  public function submitOrderToVendor(Event $event) {
    $order = $event->getEntity();

    $avnet = new \Drupal\cypress_store_vendor\Vendor\Avnet();
    $avnet->setOrder($order, []);
  }

}
