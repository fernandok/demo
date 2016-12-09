<?php

namespace Drupal\admin_dashboard\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CypressDashboardConfigForm.
 *
 * @package Drupal\admin_dashboard\Form
 */
class CypressDashboardConfigForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $cypress_dashboard_config = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $cypress_dashboard_config->label(),
      '#description' => $this->t("Label for the Cypress dashboard config."),
      '#required' => TRUE,
    ];
    $form['url_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Url'),
      '#maxlength' => 255,
      '#default_value' => $cypress_dashboard_config->getUrlPath(),
      '#description' => $this->t("Enter Relative Path for the Config"),
      '#required' => TRUE,
    ];
    $form['weight'] = [
      '#type' => 'weight',
      '#title' => t('Weight'),
      '#default_value' => $cypress_dashboard_config->getWeight(),
      '#delta' => 10,
      '#description' => $this->t('Optional.
    In the menu, the heavier items will sink and the lighter items will be
    positioned nearer the top.'),
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $cypress_dashboard_config->id(),
      '#machine_name' => [
        'exists' => '\Drupal\admin_dashboard\Entity\CypressDashboardConfig::load',
      ],
      '#disabled' => !$cypress_dashboard_config->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $cypress_dashboard_config = $this->entity;
    $cypress_dashboard_config->set('url_path', $form_state->getValue('url_path'));
    $cypress_dashboard_config->set('weight', $form_state->getValue('weight'));
    $status = $cypress_dashboard_config->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Cypress dashboard config.', [
          '%label' => $cypress_dashboard_config->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Cypress dashboard config.', [
          '%label' => $cypress_dashboard_config->label(),
        ]));
    }
    $form_state->setRedirectUrl($cypress_dashboard_config->toUrl('collection'));
  }

}
