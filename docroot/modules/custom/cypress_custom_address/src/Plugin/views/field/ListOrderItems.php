<?php

namespace Drupal\cypress_custom_address\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_product\Entity\Product;


/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("field_list_order_items")
 */
class ListOrderItems extends FieldPluginBase {

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
    if(!empty($order_id)) {
      $order_obj = Order::load($order_id);
      $items = $order_obj->getItems();
      $pro_title = '<details>';
      $pro_title .= '<summary><b>Products:</b></summary><ol>';
      foreach ($items as $order_item) {
        $prod_var_id = $order_item->get('purchased_entity')->target_id;
        $product_var = ProductVariation::load($prod_var_id);
        if(!empty($product_var)) {
          $pro_title .= '<li>' . $product_var->getTitle() . '</li>';
        }
      }
      $pro_title .= '</ol></details>';
    }
    return check_markup($pro_title, 'full_html');
  }

}

