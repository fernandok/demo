<?php

namespace Drupal\cypress_custom_address\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_product\Entity\Product;


/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("field_cart_product_image")
 */
class CartProductImage extends FieldPluginBase {

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
    // To get the product image
    $order_item_id = $values->_relationship_entities['order_items']->id();
    $order_item = OrderItem::load($order_item_id);
    $product_var_id = $order_item->get('purchased_entity')
      ->getValue()[0]['target_id'];
    $product_var = ProductVariation::load($product_var_id);
    if(!empty($product_var)) {
      $product_id = $product_var->get('product_id')->getValue()[0]['target_id'];
      $product = Product::load($product_id);
      $product_type = $product->get('type')->getValue()[0]['target_id'];
      $product_image_class = '';
      if ($product_type == 'default') {
        $product_image = $product->get('field_image')->getValue()[0]['value'];
        if (!empty($product_image)) {
          $img_src = explode(':', $product_image);
          $cart_image = $img_src[1];
        }
        else {
          $cart_image = '/themes/cypress_store/No_image_available.svg';
          $product_image_class = 'no-image-placeholder';
        }
      }
      elseif ($product_type == 'part') {
        $cart_image = '/themes/cypress_store/No_image_available.svg';
        $product_image_class = 'no-image-placeholder';
      }
    }

    $output = '<div class = "output ' . $product_image_class . '"><img src ="'.$cart_image.'" height="100" width="100"></div>';
    $img = check_markup($output, 'full_html');
    return $img;

  }

}

