<?php

namespace Drupal\cypress_custom_address\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_shipping\Entity\Shipment;
use Drupal\profile\Entity\Profile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Controller routines for page example routes.
 */
class CypressAddressController extends ControllerBase {

    public function content($profile_id, $order_id) {
      $order = Order::load($order_id);
      $shipping_profile = Profile::load($profile_id);
      $shipments = $order->get('shipments')->referencedEntities();
      if (!empty($shipments)) {
        foreach ($shipments as $shipment) {
          $shipment->setShippingProfile($shipping_profile);
          $shipment->save();
        }
      }
      else {
          // Create Package to shipments.
          $shipments = \Drupal::service('commerce_shipping.packer_manager')
              ->packToShipments($order, $shipping_profile, []);
          $shipments[0][0]->save();
          $order->shipments = $shipments[0][0];
          $order->save();
      }

      // Reroute to checkout page.
      $url = Url::fromRoute('commerce_checkout.form', ['commerce_order' => $order->id(), 'step' => 'order_information']);
      return new RedirectResponse($url->toString());
    }
}

