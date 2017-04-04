<?php

namespace Drupal\cypress_custom_address\Plugin\Field\FieldType;

use Drupal\address\Plugin\Field\FieldType\AddressItem;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
* @FieldType(
*   id = "contact_address_item",
*   label = @Translation("Address with Telephone"),
*   description = @Translation("This entity for extending contact field in address module."),
*   default_widget = "contact_default",
*   default_formatter = "contact_default",
* )
*/

class ContactItem extends AddressItem {

    /**
     * {@inheritdoc}
     */
    public static function schema(FieldStorageDefinitionInterface $field_definition) {
        $schema = parent::schema($field_definition);
        $schema['columns']['contact'] = [
            'type' => 'varchar',
            'length' => 255,

        ];
        return $schema;
    }

    /**
     * {@inheritdoc}
     */
    public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
        $properties = parent::propertyDefinitions($field_definition);
        $contact_definition = DataDefinition::create('string')
            ->setLabel(t('Contact'));
        $properties['contact'] = $contact_definition;
        return $properties;
    }
}
