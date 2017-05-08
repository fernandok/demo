<?php

namespace Drupal\cypress_custom_address\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_price\Price;
use Drupal\commerce_price\Entity\Currency;


/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("field_cart_rules_adjustment")
 */
class CartRulesAdjustment extends FieldPluginBase {

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
   * Show the cart rule adjustment discount for the part products in cart page.
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $order_item = $values->_relationship_entities['order_items'];
    $adjustments = $order_item->getAdjustments();
    $product_variation = $order_item->getPurchasedEntity();
    $product_id = $product_variation->get('product_id')
      ->getValue()[0]['target_id'];
    $product = Product::load($product_id);
    $product_type = $product->get('type')->getValue()[0]['target_id'];
    $cart_rules_adjustment = '';
    if ($product_type == 'part') {
      foreach ($adjustments as $adjustment) {
        $adjustment_type = $adjustment->getType();
        if ($adjustment_type == 'cypress_cart_rules') {
          $adjustment_label = $adjustment->getLabel();
          $adjustment_amount = $adjustment->getAmount();
          $adjustment_price = $adjustment_amount->getNumber();
          $adjustment_price = trim($adjustment_price, "-");
          $adjustment_price = number_format($adjustment_price, '2');
          $adjustment_currency_code = $adjustment_amount->getCurrencyCode();
          $adjustment_currency = Currency::load($adjustment_currency_code);
          $currency_symbol = $adjustment_currency->getSymbol();
          $cart_rules_adjustment .= '<div class="adjustment-amount"><span class = "adjustment-label">' . $adjustment_label . '</span><span class = "adjustment-price"> - ' . $currency_symbol . '' . $adjustment_price . '</span></div>';
        }
      }
    }
    $output = check_markup($cart_rules_adjustment, 'full_html');
    return $output;
  }
}

