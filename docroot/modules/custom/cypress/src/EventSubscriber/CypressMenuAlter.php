<?php

namespace Drupal\cypress\EventSubscriber;

use Drupal\Core\Routing\RouteBuildEvent;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SamlLoginRedirect.
 *
 * @package Drupal\cypress
 */
class CypressMenuAlter implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[RoutingEvents::DYNAMIC][] = ['getroutemenulink'];
    return $events;
  }

  /**
   * {@inheritdoc}
   */
  public function getroutemenulink(RouteBuildEvent $event) {
    $get_rout = $event->getRouteCollection()->getIterator();
  }

}
