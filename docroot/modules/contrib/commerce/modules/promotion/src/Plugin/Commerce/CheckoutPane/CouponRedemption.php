<?php

namespace Drupal\commerce_promotion\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\commerce_promotion\Entity\CouponInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the billing information pane.
 *
 * @CommerceCheckoutPane(
 *   id = "coupon_redemption",
 *   label = @Translation("Coupon redemption"),
 *   default_step = "order_information",
 *   wrapper_element = "fieldset",
 * )
 */
class CouponRedemption extends CheckoutPaneBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'title' => $this->t('Coupon code'),
      'description' => $this->t('Enter your coupon code to redeem a promotion.'),
      'submit_title' => $this->t('Redeem'),
      'submit_message' => $this->t('Coupon applied'),
      'remove_title' => $this->t('Remove coupon'),
      'single_coupon_mode' => TRUE,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationSummary() {
    if (!empty($this->configuration['single_coupon_mode'])) {
      $summary = $this->t('Single coupon mode: Yes');
    }
    else {
      $summary = $this->t('Single coupon mode: No');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['single_coupon_mode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Single coupon mode'),
      '#description' => $this->t('If enabled, only one coupon can be used during checkout.'),
      '#default_value' => $this->configuration['single_coupon_mode'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['single_coupon_mode'] = $values['single_coupon_mode'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $pane_form['coupons'] = [
      '#type' => 'commerce_coupon_redemption_form',
      '#order_id' => $this->order->id(),
      '#title' => $this->configuration['title'],
      '#description' => $this->configuration['description'],
      '#submit_title' => $this->configuration['submit_title'],
      '#submit_message' => $this->configuration['submit_message'],
      '#remove_title' => $this->configuration['remove_title'],
      '#single_coupon_mode' => $this->configuration['single_coupon_mode'],
    ];

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $coupon = $form_state->getValue('coupon_redemption');
    if ($coupon instanceof CouponInterface) {
      $this->order->get('coupons')->appendItem($coupon);
      drupal_set_message($this->configuration['submit_message']);
    }
  }

}