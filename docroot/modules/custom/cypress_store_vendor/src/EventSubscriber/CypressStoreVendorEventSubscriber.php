<?php
/**
 * @file
 * Contains \Drupal\cypress_store_vendor\CypressStoreVendorEventSubscriber.
 */
namespace Drupal\cypress_store_vendor\EventSubscriber;

use Drupal\cypress_store_vendor\CypressStoreVendor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CypressStoreVendorEventSubscriber.
 *
 * @package Drupal\cypress_store_vendor
 */
class CypressStoreVendorEventSubscriber implements EventSubscriberInterface {
  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[CypressStoreVendor::ERROR][] = array('loggerEmail', 800);
    return $events;
  }

  /**e
   * Subscriber Callback for the event.
   * @param CypressStoreVendor $event
   */
  public function loggerEmail(CypressStoreVendor $event) {
    $message = $event->getMessage();
    \Drupal::logger('cypress_store_vendor')->error($message['subject'] . ' : ' .$message['body']);
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'cypress_store_vendor';
    $key = 'logger';
    $to = \Drupal::config('system.site')->get('mail');
    $params['message'] = $message['body'];
    $params['title'] = $message['subject'];
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = true;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] !== true) {
      drupal_set_message(t('There was a problem sending your message and it was not sent.'), 'error');
    }
    else {
      drupal_set_message(t('Your message has been sent.'));
    }

  }
}

