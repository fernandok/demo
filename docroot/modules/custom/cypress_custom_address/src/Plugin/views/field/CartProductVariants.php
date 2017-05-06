<?php

namespace Drupal\cypress_custom_address\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_price\Price;
use Drupal\commerce_price\Entity\Currency;


/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("field_cart_product_variants")
 */
class CartProductVariants extends FieldPluginBase {

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
  protected function defineOptions()
  {
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
   * Show the variant prices for the part products in cart page.
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $order_item = $values->_relationship_entities['order_items'];
    $product_variation = $order_item->getPurchasedEntity();
    $product_id = $product_variation->get('product_id')
      ->getValue()[0]['target_id'];
    $product = Product::load($product_id);
    $product_type = $product->get('type')->getValue()[0]['target_id'];
    $variation_ids = $product->getVariationIds();
    $part_variant_prices = '';
    $part_range = ['1-9', '10-24', '25-99', '100-249', '250-999', '1000 +'];
    if ($product_type == 'part') {
      foreach ($variation_ids as $key => $variation_id) {
        $variation_object = ProductVariation::load($variation_id);
        $variation_price = $variation_object->getPrice();
        $variation_currency_code = $variation_price->getCurrencyCode();
        $variation_price_number = $variation_price->getNumber();
        $variation_currency = Currency::load($variation_currency_code);
        $currency_symbol = $variation_currency->getSymbol();
        $part_variant_prices .= '<div class ="part-variants">' . $part_range[$key] . ' - ' . $currency_symbol . $variation_price_number . '</div>';
      }
    }
    $output = check_markup($part_variant_prices, 'full_html');
    return $output;
  }
}

