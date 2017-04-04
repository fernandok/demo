<?php

namespace Drupal\cypress_custom_address\Plugin\Field\FieldWidget;


use Drupal\address\Plugin\Field\FieldWidget\AddressDefaultWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;


/**
 * @FieldWidget(
 *   id = "contact_default",
 *   label = @Translation("Address With Telephone"),
 *   description = @Translation("An contact text field with an associated Address."),
 *   field_types = {
 *     "contact_address_item"
 *   }
 * )
 */

class ContactDefaultWidget extends AddressDefaultWidget {

    /**
     * {@inheritdoc}
     */
    public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
        $widget = parent::formElement($items, $delta, $element, $form, $form_state);
        $widget['address']['#type'] = 'contact_address_item';
        $widget['address']['locality'] = array(
            '#weight' => 1,
        );
        $widget['address']['contact'] = array(
            '#title' => $this->t('Contact'),
            '#type' => 'textfield',
            '#default_value' => isset($items[$delta]->contact) ? $items[$delta]->contact : null,
            '#weight' => 10,
            '#maxlength' => 10,
            '#states' => [
                'invisible' => [
                    ':input[name="payment_information[billing_information][field_contact_address][0][address][country_code]"]' => ['value' => ''],
                ],
            ],
/*            '#element_validate' => array(
                array($this, 'contact_validate'),
            ),*/
        );
        return $widget;
    }

    /**
     * {@inheritdoc}
     */
/*    public function contact_validate($element, FormStateInterface $form_state, $form)
    {
        $form_values = $form_state->getValues();
        $payment_contact = $form_values['payment_information']['billing_information']['field_contact_address'][0]['address']['contact'];
        $shipping_contact = $form_values['shipping_information']['shipping_profile']['field_contact_address'][0]['address']['contact'];
        if (!is_numeric($payment_contact) || !is_numeric($shipping_contact)) {
            $form_state->setError($element, t('Contact number should be numeric.'));
        }
        if ((strlen($payment_contact) > 10) || (strlen($shipping_contact) > 10)) {
            $form_state->setError($element, t('Contact number should be 10 digit.'));
        }
    }*/
}
