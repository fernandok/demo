<?php

namespace Drupal\cypress_custom_address\Plugin\Field\FieldFormatter;

use Drupal\address\Plugin\Field\FieldFormatter\AddressDefaultFormatter;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * @FieldFormatter(
 *   id = "contact_default",
 *   label = @Translation("Contact Address"),
 *   description = @Translation("Display the referenced entities’ label with their contact address."),
 *   field_types = {
 *     "contact_address_item"
 *   }
 * )
 */

class ContactDefaultFormatter extends AddressDefaultFormatter {

    /**
     * {@inheritdoc}
     */
    public function viewElements(FieldItemListInterface $items, $langcode) {
        $elements = parent::viewElements($items, $langcode);
        $values = $items->getValue();
        foreach ($elements as $delta => $entity) {
            $elements[$delta]['#suffix'] = '<br><span class="ct-addr">'.$values[$delta]['contact'].'</span>';
        }
        return $elements;
    }
}
