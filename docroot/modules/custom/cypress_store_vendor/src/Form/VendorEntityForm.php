<?php

namespace Drupal\cypress_store_vendor\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Class VendorEntityForm.
 *
 * @package Drupal\cypress_store_vendor\Form
 */
class VendorEntityForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $vendor_entity = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Vendor'),
      '#maxlength' => 255,
      '#default_value' => $vendor_entity->label(),
      '#description' => $this->t("Label for the Vendor entity."),
      '#required' => TRUE,
    );

    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Vendor Configuration'),
      '#default_value' => $vendor_entity->getDescription(),
      '#description' => $this->t("Description should be in YAML format."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $vendor_entity->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\cypress_store_vendor\Entity\VendorEntity::load',
      ),
      '#disabled' => !$vendor_entity->isNew(),
    );

    /* You will need additional form elements for your custom properties. */

    return $form;
  }



  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $content = $form_state->getValue('description');
    $yaml = new Parser();
    try {
      $value =  $yaml->parse($content, TRUE);
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
  public function save(array $form, FormStateInterface $form_state) {
    $vendor_entity = $this->entity;
    $vendor_entity->set('description', $form_state->getValue('description'));
    $status = $vendor_entity->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Vendor entity.', [
          '%label' => $vendor_entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Vendor entity.', [
          '%label' => $vendor_entity->label(),
        ]));
    }
    $form_state->setRedirectUrl($vendor_entity->urlInfo('collection'));
  }

}
