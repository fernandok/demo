<?php
/**
 * @file
 * Contains \Drupal\cypress_store_vendor\Form\OrderRoutingSettingsForm.
 */

namespace Drupal\cypress_store_vendor\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;

class OrderRoutingSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'cypress_store_vendor.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'order_routing_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('cypress_store_vendor.settings');
    $form['order_routing_config'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Order Routing Configuration'),
      '#default_value' => $config->get('order_routing_config'),
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $order_routing = $form_state->getValue('order_routing_config');
    $yaml = new Parser();
    try {
      $value =  $yaml->parse($order_routing, TRUE);
      if(!is_array($value)) {
        return $form_state->setErrorByName('description', 'Vendor Configuration should be in YAML Format');
      }
    } catch (ParseException $e) {
      return $form_state->setErrorByName('description', $e->getMessage());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('cypress_store_vendor.settings')
      ->set('order_routing_config', $form_state->getValue('order_routing_config'))
      ->save();
  }
}