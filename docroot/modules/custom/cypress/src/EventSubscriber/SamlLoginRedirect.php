<?php

namespace Drupal\cypress\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * Class SamlLoginRedirect.
 *
 * @package Drupal\cypress
 */
class SamlLoginRedirect implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('redirectToFrontPage', 200);
    return $events;

  }

  /**
   * Subscriber Callback for the event.
   * @param GetResponseEvent $event
   */
  public function redirectToFrontPage(GetResponseEvent $event) {
    $request = $event->getRequest();
    $request_path_info = $request->getPathInfo();
    if (\Drupal::currentUser()->isAnonymous() && $request_path_info != '/user/logout' && $request_path_info != '/saml_login' && $request_path_info != '/user/login' && $request_path_info != '/rest/session/token') {
     $event->setResponse(new RedirectResponse('/saml_login'));
    }
  }
}