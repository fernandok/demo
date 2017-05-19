<?php

namespace Drupal\cypress_custom_address\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_shipping;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("field_order_shipment_detail")
 */
class OrderShipmentDetail extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing -- to override the parent query.
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['hide_alter_empty'] = ['default' => FALSE];
    return $options;
  }

  /**$product_image$product_image$product_image
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {

    $order_id = $values->_entity->id();
    $order_obj = Order::load($order_id);
    if(!empty($order_obj)) {
      $shipments = $order_obj->get('shipments')->referencedEntities();
      if(!empty($shipments)) {
        $first_shipment = reset($shipments);
        if(!empty($first_shipment->getShippingProfile())) {
          $first_name = $first_shipment->getShippingProfile()
            ->get('field_contact_address')
            ->getValue()[0]['given_name'];
          $last_name = $first_shipment->getShippingProfile()
            ->get('field_contact_address')
            ->getValue()[0]['family_name'];
          $full_name = $first_name .' ' . $last_name;
        }
      }
    }
    return $full_name;
  }

}

