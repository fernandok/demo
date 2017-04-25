<?php

namespace Drupal\cypress_custom_address\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;

/**
 * Provides a 'CartItemsBlock' block.
 *
 * @Block(
 *  id = "cart_items_block",
 *  admin_label = @Translation("Cart items block"),
 * )
 */
class CartItemsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The cart provider.
   *
   * @var \Drupal\commerce_cart\CartProviderInterface
   */
  protected $cartProvider;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new CartBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CartProviderInterface $cart_provider, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->cartProvider = $cart_provider;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('commerce_cart.cart_provider'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

//    $cachable_metadata = new CacheableMetadata();
//    $cachable_metadata->addCacheContexts(['user', 'session']);

    /** @var \Drupal\commerce_order\Entity\OrderInterface[] $carts */
    $cart_id = $this->cartProvider->getCartIds()[0];
    $order_obj = Order::load($cart_id);
    $items = $order_obj->getItems();
    $total_items = 0;
    foreach($items as $item) {
      $total_items += $item->getQuantity();
    }
    $total_price = $order_obj->getTotalPrice()->getNumber();
    $price = number_format((float)$total_price,2,'.','');
    $build = [];
    $build['cart_items_block']['#markup'] = '<div class = "cart-items">
                                             <div class ="total-items-label"><div class = "total-items">Total Items</div><div class ="items">'. $total_items . '</div></div>
                                             <div class ="sub-total-value"><div class = "total-price">Sub Total</div><div class ="sub-total-price">$ ' .$price .'</div></div>                                            
                                             <div id ="continue-shopping"><a href="/">Continue Shopping</a></div>
                                             <div id ="checkout-dummy"><a href="">Checkout</a></div>
                                             </div>';
    $build['cart_items_block']['#attached'] = [
      'library' => array('cypress_custom_address/custom-cart-checkout'),
    ];
    $build['#cache']['max-age'] = 0;
    return $build;
  }

}
