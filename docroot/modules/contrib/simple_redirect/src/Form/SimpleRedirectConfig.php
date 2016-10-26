<?php

namespace Drupal\simple_redirect\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SimpleRedirectConfig.
 *
 * @package Drupal\simple_redirect\Form
 */
class SimpleRedirectConfig extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'simple_redirect.simpleredirectconfig',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'simple_redirect_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('simple_redirect.simpleredirectconfig');
    $form['simple_redirect'] = [
      '#type' => 'fieldset',
      '#title' => t('Simple Redirect'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];
    $form['simple_redirect']['user_reg'] = [
      '#type' => 'textfield',
      '#title' => $this->t('After User Registration'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('user_reg'),
    ];
    $form['simple_redirect']['user_login'] = [
      '#type' => 'textfield',
      '#title' => $this->t('After User Login'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('user_login'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!\Drupal::pathValidator()->isValid($form_state->getValue('user_reg'))) {
      $form_state->setErrorByName('user_reg', $this->t("Enter a valid url."));
    }
    if (!\Drupal::pathValidator()->isValid($form_state->getValue('user_login'))) {
      $form_state->setErrorByName('user_login', $this->t("Enter a valid url."));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('simple_redirect.simpleredirectconfig')
      ->set('user_reg', $form_state->getValue('user_reg'))
      ->set('user_login', $form_state->getValue('user_login'))
      ->save();
  }
}
