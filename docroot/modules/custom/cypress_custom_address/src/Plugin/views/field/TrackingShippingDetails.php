<?php

namespace Drupal\cypress_custom_address\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_shipping;
use Drupal\commerce_shipping\Entity\Shipment;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("field_tracking_shipping_details")
 */
class TrackingShippingDetails extends FieldPluginBase {

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
    $shipment_id = $values->_entity->id();
    $ship_items = "";
    if(!empty($shipment_id)) {
      $ship_obj = Shipment::load($shipment_id);
      $ship_items = '<details>';
      $ship_items .= '<summary>Items</summary><ol>';
      foreach ($ship_obj->getItems() as $item) {
        $ship_items .= '<li>' . $item->getTitle() . '</li>';
      }
      $ship_items .= '</ol></details>';
    }
    return check_markup($ship_items, 'full_html');
  }

}
