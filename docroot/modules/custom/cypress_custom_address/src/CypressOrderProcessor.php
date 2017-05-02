<?php
namespace Drupal\cypress_custom_address;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\OrderProcessorInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_order\Adjustment;

/**
 * Provides an order processor that modifies the price of order items according to the cart rules.
 */
class CypressOrderProcessor implements OrderProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(OrderInterface $order)
    {
        $order->setAdjustments([]);
        foreach ($order->getItems() as $order_item) {
            $product_variation = $order_item->getPurchasedEntity();
            $product_type = $product_variation->get('type')->getValue()[0]['target_id'];
            $product_id = $product_variation->get('product_id')
                ->getValue()[0]['target_id'];
            $product = Product::load($product_id);
            $quantity = $order_item->getQuantity();
            $product_title = $product->getTitle();
            if ($product_type == 'part_store') {
                $can_sample = $product->get('field_can_sample')
                    ->getValue()[0]['value'];
                $product_price = $order_item->getUnitPrice();
                $product_unit_price = $product_price->getNumber();
                if ($can_sample == 2) {
                    if ($product_unit_price < 20 && $quantity <= 10) {
                        $order_item->setUnitPrice(new Price("0.00", 'USD'));
                    }
                    elseif ($product_unit_price < 20 && $quantity > 10) {
                        $new_adjustment = $product_unit_price * 10;
                        $adjustments = $order->getAdjustments();
                        $adjustments[] = new Adjustment([
                            'type' => 'custom',
                            'label' => 'Cart Rule Adjustment - '.$product_title,
                            'amount' => new Price('-' . $new_adjustment, 'USD'),
                        ]);
                        $order->setAdjustments($adjustments);

                    }

                }

            }
        }
    }
}