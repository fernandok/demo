<?php

namespace Drupal\cypress_custom_address\Plugin\Commerce\CheckoutPane;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;


/**
 * Provides the Cypress Review pane.
 *
 * @CommerceCheckoutPane(
 *   id = "cypress_review",
 *   label = @Translation("Cypress Review"),
 *   default_step = "review",
 * )
 */
class CypressReview extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form)
  {
    /** @var \Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface[] $enabled_panes */
    $enabled_panes = array_filter($this->checkoutFlow->getPanes(), function ($pane) {
      return !in_array($pane->getStepId(), ['_sidebar', '_disabled']);
    });
    foreach ($enabled_panes as $pane_id => $pane) {
      if ($summary = $pane->buildPaneSummary()) {
        // BC layer for panes which still return rendered strings.
        if ($summary && !is_array($summary)) {
          $summary = [
            '#markup' => $summary,
          ];
        }

        $label = $pane->getLabel();
        if ($pane->isVisible()) {
          $edit_link = Link::createFromRoute($this->t('Edit'), 'commerce_checkout.form', [
            'commerce_order' => $this->order->id(),
            'step' => $pane->getStepId(),
          ]);
          $label .= ' (' . $edit_link->toString() . ')';
        }
        $pane_form[$pane_id] = [
          '#type' => 'fieldset',
          '#title' => $label,
        ];
        $pane_form[$pane_id]['summary'] = $summary;
      }
    }

    // To show the Part end products on Review page.
    $order = $this->order;
    if (!empty($order)) {
      $primary_application = $order->get('field_primary_application')
        ->getValue()[0]['value'];
      // Defining markups for each field.
      $primary_application_markup = '';
      $name_of_product_system_markup = '';
      $purpose_of_order_markup = '';
      $end_customer_markup = '';
      if ($primary_application) {
        $primary_application_markup .= '<b>' . 'Primary Application for Projects/Designs:' . '</b><br>' . ucwords($primary_application) . '<br>';
      }
      $name_of_product_system = $order->get('field_name_product_system')
        ->getValue()[0]['value'];
      if ($name_of_product_system) {
        $name_of_product_system_markup .= '<b>' . 'Name of your end Product/System:' . '</b><br>' . ucwords($name_of_product_system) . '<br>';
      }
      $purpose_of_order = $order->get('field_purpose_of_order')
        ->getValue()[0]['value'];
      if ($purpose_of_order) {
        $purpose_of_order_markup .= '<b>' . 'Purpose of Order:' . '</b><br>' . ucwords($purpose_of_order) . '<br>';
      }
      $end_customer = $order->get('field_end_customer')->getValue()[0]['value'];
      if ($end_customer) {
        $end_customer_markup .= '<b>' . 'End Customer:' . '</b><br>' . ucwords($end_customer) . '<br>';
      }
    }
    $pane_form['part_end_products'] = [
      '#type' => 'fieldset',
      '#prefix' => '<div class = "part-end-products">',
      '#title' => t('Part End Products'),
      '#markup' => $primary_application_markup . $name_of_product_system_markup . $purpose_of_order_markup . $end_customer_markup,
      '#suffix' => '</div>'
    ];
    return $pane_form;
  }
}
