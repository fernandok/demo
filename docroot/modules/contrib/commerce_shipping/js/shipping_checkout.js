/**
 * @file
 * Defines behaviors and callbacks for Commerce Shipping
 */
(function ($, Drupal) {
    'use strict';

    var existingValues = {};

    function setExistingShippingValues(context) {
        context = context || document;
        var changed = false;
        $('.required:not(.country)[data-drupal-selector^="edit-shipping-information-shipping-profile-"], [data-drupal-selector="edit-shipping-information-shipping-profile-profile-selection"]', context).each(function () {
            var element = $(this);
            var id = element.data('drupal-selector');
            var val = element.val();

            if (typeof existingValues[id] === 'undefined' || existingValues[id] !== val) {
                existingValues[id] = val;
                changed = true;
            }
        });

        return changed;
    }

    /**
     * Attaches the shipping recalculate behavior.
     *
     * @type {Drupal~behavior}
     *
     * @prop {Drupal~behaviorAttach} attach
     */
    Drupal.behaviors.commerceShippingRecalculate = {
        attach: function (context) {
            if (!$('[data-drupal-selector^="edit-shipping-information-shipments-"]', context).length) {
                $('[data-drupal-selector="edit-actions-next"]', context).prop('disabled', true);
            }

            $('.required:not(.country)[data-drupal-selector^="edit-shipping-information-shipping-profile-"], [data-drupal-selector="edit-shipping-information-shipping-profile-profile-selection"]', context).on('change input', function () {
                // just changed a field, clear any timer
                window.clearTimeout(recalculateTimer);
                // start the timer that will trigger recalculation after some inactivity
                recalculateTimer = window.setTimeout($.fn.commerceCheckShippingRecalculation, 1500);
            });
            $(window).load(function () {
                $.fn.commerceCheckShippingRecalculation();
            });

            // Prefer to observe #edit-shipping-information-shipments, but ajax doesn't trigger an event for it
            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.type === 'childList') { // showing and hiding the ajax indicator
                        if (mutation.addedNodes.length) {
                            $('[data-drupal-selector="edit-actions-next"]', context).prop('disabled', true);
                        }
                        else if (mutation.removedNodes.length) {
                            // check that shipping info is populated
                            if ($('[data-drupal-selector^="edit-shipping-information-shipments-"]', context).length) {
                                $('[data-drupal-selector="edit-actions-next"]', context).prop('disabled', false);
                            }
                        }
                    }
                });
            });
            var obsConfig = {childList: true, subtree: true};
            var $wrapper = $('#shipping-information-wrapper', context);
            var node = $wrapper[0];
            if (node) {
                observer.observe(node, obsConfig);
            }
        },
        detach: function (context) {
        }
    };

    var recalculateTimer = null;

    /**
     * Checks to see if we can recalculate shipping rates and dispatches the command.
     */
    $.fn.commerceCheckShippingRecalculation = function () {
        var recalculate = false;
        var $shippingProfileSelectOptions = $('[data-drupal-selector="edit-shipping-information-shipping-profile-profile-selection"]').find('option:selected');

        recalculate = true;
        if (!$shippingProfileSelectOptions.length || $shippingProfileSelectOptions.val() === 'new_address') {
            // validate minimum fields to calculate shipping
            $.each([
                'organization',
                'dependent-locality',
                'locality',
                'administrative-area',
                'postal-code'
            ], function (index, field) {
                var valid = true;
                var $field = $('.required[data-drupal-selector="edit-shipping-information-shipping-profile-address-0-address-' + field + '"]');
                if ($field.length && (!$field.val().length || ((field === 'postal-code' && $field.val().length <= 4)))) {
                    valid = false;
                    recalculate = false;
                }
                return valid;
            });
        }

        var changed = setExistingShippingValues();

        // Define the callback used with setTimeout to click the recalculation button
        // if there is ongoing AJAX operation.
        if (recalculate && changed) {
            return setTimeout(function () {
                var $recalcButton = $('[data-drupal-selector="edit-shipping-information-recalculate-shipping"]');
                if ($recalcButton.prop('disabled')) {
                    return setTimeout($.fn.commerceCheckShippingRecalculation, 100);
                }
                // Trigger the mousedown event on the shipping recalculation button.
                $recalcButton.trigger('mousedown');
            }, 100);
        }
    };

})(jQuery, Drupal);
