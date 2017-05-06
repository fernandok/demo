<?php

/**
 * @file
 * Provide Views data for the Address module.
 *
 * @ingroup views_module_handlers
 */

/**
 * Implements hook_field_views_data().
 *
 * Views integration for address fields.
 */
function cypress_custom_address_field_views_data() {
    $data['views']['table']['group'] = t('Contact Address');
    $data['views']['table']['join'] = array(
        // #global is a special flag which allows a table to appear all the time.
        '#global' => array(),
    );

    $data['views']['cart_product_image'] = [
        'title' => t('Cart Product Image'),
        'help' => t('Custom Product Image in Cart'),
        'field' => [
            'id' => 'field_cart_product_image',
        ],
    ];

    $data['views']['count_cart_items'] = [
      'title' => t('Cart Items'),
      'help' => t('Count of Cart Items'),
      'field' => [
        'id' => 'field_count_cart_items',
      ],
    ];

    // Fields.
    $data['profile']['contact_address_given_name'] = [
        'title' => t('Contact First Name'),
        'help' => t('Custom Contact Address First Name.'),
        'field' => [
            'id' => 'field_contact_first_name',
        ],
    ];
    $data['profile']['contact_address_family_name'] = [
        'title' => t('Contact Last Name'),
        'help' => t('Custom Contact Address Last Name.'),
        'field' => [
            'id' => 'field_contact_last_name',
        ],
    ];
    $data['profile']['contact_address_address_line1'] = [
        'title' => t('Contact Address Line1'),
        'help' => t('Custom Contact Address Line 1.'),
        'field' => [
            'id' => 'field_contact_address_line1',
        ],
    ];
    $data['profile']['contact_address_locality'] = [
        'title' => t('Contact Locality'),
        'help' => t('Custom Contact Locality.'),
        'field' => [
            'id' => 'field_contact_locality',
        ],
    ];
    $data['profile']['contact_address_postal_code'] = [
        'title' => t('Contact Postal Code'),
        'help' => t('Custom Contact Postal Code.'),
        'field' => [
            'id' => 'field_contact_postal_code',
        ],
    ];
    $data['profile']['contact_address_administrative_area'] = [
        'title' => t('Contact Administrative Area'),
        'help' => t('Custom Contact Administrative Area'),
        'field' => [
            'id' => 'field_contact_administrative_area',
        ],
    ];
    $data['profile']['contact_address_contact'] = [
        'title' => t('Contact Address Contact'),
        'help' => t('Custom Contact Address Contact'),
        'field' => [
            'id' => 'field_contact_number',
        ],
    ];
    $data['profile']['contact_address_deliver_here'] = [
        'title' => t('Deliver Here'),
        'help' => t('Deliver Here Button'),
        'field' => [
            'id' => 'field_deliver_here',
        ],
    ];

  $data['views']['table']['group'] = t('Cart Add-Ons');
  $data['views']['table']['join'] = array(
    // #global is a special flag which allows a table to appear all the time.
    '#global' => array(),
  );

  $data['views']['cart_product_variants'] = [
    'title' => t('Cart Product Variants'),
    'help' => t('Custom Product Variants in Cart'),
    'field' => [
      'id' => 'field_cart_product_variants',
    ],
  ];

  $data['views']['cart_rules_adjustment'] = [
    'title' => t('Cart Rules Adjustment Discount'),
    'help' => t('Cart Rules Adjustment Discount in Cart Page'),
    'field' => [
      'id' => 'field_cart_rules_adjustment',
    ],
  ];
    return $data;
}