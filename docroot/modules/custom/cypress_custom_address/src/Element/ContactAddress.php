<?php

namespace Drupal\cypress_custom_address\Element;

use Drupal\address\Element\Address;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an contact_address form element.
 *
 * Usage example:
 * @code
 * $form['contact_address_item'] = [
 *   '#type' => 'contact_address_item',
 *   '#default_value' => [
 *     'contact' => '9999999999',
 *   ],
 * ];
 * @endcode
 *
 * @FormElement("contact_address_item")
 */

class ContactAddress extends Address {
    /**
     * {@inheritdoc}
     */
    public function getInfo() {
        return $info = parent::getInfo();
    }

    /**
     * {@inheritdoc}
     */
    public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
        if (is_array($input)) {
            return $input;
        }
        else {
            if (!is_array($element['#default_value'])) {
                $element['#default_value'] = [];
            }
            // Initialize properties.
            $properties = [
                'given_name', 'additional_name', 'family_name', 'organization',
                'address_line1', 'address_line2', 'postal_code', 'sorting_code',
                'dependent_locality', 'locality', 'administrative_area',
                'country_code', 'langcode', 'contact',
            ];
            foreach ($properties as $property) {
                if (!isset($element['#default_value'][$property])) {
                    $element['#default_value'][$property] = NULL;
                }
            }

            return $element['#default_value'];
        }
    }
}
