<?php

namespace Drupal\Tests\commerce_promotion\Functional;

use Drupal\Core\Url;
use Drupal\Tests\commerce\Functional\CommerceBrowserTestBase;

/**
 * Tests the coupon redeem checkout pane.
 *
 * @group commerce
 * @group commerce_promotion
 */
class CouponRedemptionPaneTest extends CommerceBrowserTestBase {

  /**
   * The cart order to test against.
   *
   * @var \Drupal\commerce_order\Entity\OrderInterface
   */
  protected $cart;

  /**
   * The cart manager.
   *
   * @var \Drupal\commerce_cart\CartManagerInterface
   */
  protected $cartManager;

  /**
   * The variation to test against.
   *
   * @var \Drupal\commerce_product\Entity\ProductVariation
   */
  protected $variation;

  /**
   * The promotion for testing.
   *
   * @var \Drupal\commerce_promotion\Entity\PromotionInterface
   */
  protected $promotion;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'block',
    'commerce_cart',
    'commerce_promotion',
    'commerce_promotion_test',
    'commerce_checkout',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->cart = $this->container->get('commerce_cart.cart_provider')->createCart('default', $this->store, $this->adminUser);
    $this->cartManager = $this->container->get('commerce_cart.cart_manager');

    // Create a product variation.
    $this->variation = $this->createEntity('commerce_product_variation', [
      'type' => 'default',
      'sku' => $this->randomMachineName(),
      'price' => [
        'number' => 999,
        'currency_code' => 'USD',
      ],
    ]);
    $this->cartManager->addEntity($this->cart, $this->variation);

    // We need a product too otherwise tests complain about the missing
    // backreference.
    $this->createEntity('commerce_product', [
      'type' => 'default',
      'title' => $this->randomMachineName(),
      'stores' => [$this->store],
      'variations' => [$this->variation],
    ]);

    // Starts now, enabled. No end time.
    $this->promotion = $this->createEntity('commerce_promotion', [
      'name' => 'Promotion (with coupon)',
      'order_types' => ['default'],
      'stores' => [$this->store->id()],
      'status' => TRUE,
      'offer' => [
        'target_plugin_id' => 'commerce_promotion_order_percentage_off',
        'target_plugin_configuration' => [
          'amount' => '0.10',
        ],
      ],
      'conditions' => [],
    ]);

    $coupon = $this->createEntity('commerce_promotion_coupon', [
      'code' => $this->randomString(),
      'status' => TRUE,
    ]);
    $coupon->save();
    $this->promotion->get('coupons')->appendItem($coupon);
    $this->promotion->save();
  }

  /**
   * Tests redeeming coupon in checkout using the coupon redeem pane.
   */
  public function testCouponRedemption() {
    $this->drupalGet(Url::fromRoute('commerce_checkout.form', ['commerce_order' => $this->cart->id()]));

    /** @var \Drupal\commerce_promotion\Entity\CouponInterface $existing_coupon */
    $existing_coupon = $this->promotion->get('coupons')->first()->entity;

    $this->assertSession()->pageTextContains('Enter your coupon code to redeem a promotion.');

    // Test entering an invalid coupon.
    $this->getSession()->getPage()->fillField('Coupon code', $this->randomString());
    $this->getSession()->getPage()->pressButton('Redeem');
    $this->assertSession()->pageTextContains('Coupon is invalid');
    $this->assertSession()->pageTextContains('$999.00');

    $this->getSession()->getPage()->fillField('Coupon code', $existing_coupon->getCode());
    $this->getSession()->getPage()->pressButton('Redeem');
    $this->assertSession()->pageTextContains('Coupon applied');
    $this->assertSession()->pageTextContains('-$99.90');
    $this->assertSession()->pageTextContains('$899.10');

    $this->assertSession()->fieldNotExists('Coupon code');
    $this->assertSession()->buttonNotExists('Redeem');
    $this->getSession()->getPage()->pressButton('Remove coupon');
    $this->assertSession()->pageTextContains('$999.00');

    $this->assertSession()->fieldExists('Coupon code');
    $this->assertSession()->buttonExists('Redeem');
  }

}
