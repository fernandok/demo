<?php

namespace Drupal\cypress_store_vendor\EventSubscriber;

use Drupal\cypress_store_vendor\Vendor\HarteHanks;
use Drupal\cypress_store_vendor\Vendor\VendorBase;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
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
  public function submitOrderToVendor(WorkflowTransitionEvent $event) {
//    $orderId = $event->getEntity()->get('order_id')->getValue()[0]['value'];
    $order = $event->getEntity();
    $shipments = $event->getEntity()->get('shipments')->referencedEntities();
    if (!empty($shipments)) {
        foreach ($shipments as $shipment) {
          if($shipment->get('field_vendor')->getValue()[0]['value'] == 'HH'){
//            $shipping_item = $shipment->get('items')->getValue();
            $vendor = new HarteHanks();
            $orderSubmit = $vendor->AddNewOrder($order,$shipment);
          }
        }
    }

  }

}
