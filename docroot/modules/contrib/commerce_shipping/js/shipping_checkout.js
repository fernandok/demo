/**
 * @file
 * Defines behaviors and callbacks for Commerce Shipping
 */
(function ($, Drupal) {
    'use strict';
    var existingValues = {};
    var recalculateTimer = null;

    /**
     * Gets the fields required for shipping calculation
     *
     * @returns {*|HTMLElement}
     */
    function getShippingFields(context) {
        context = context || document;
        return $('.required[data-drupal-selector^="edit-shipping-information-shipping-profile-"]:not(.country), [data-drupal-selector="edit-shipping-information-shipping-profile-profile-selection"]', context);
    }

    /**
     * Caches the existing shipping-related form values for comparison.
     *
     * @returns {boolean}
     */
    function setExistingShippingValues() {
        var changed = false;
        getShippingFields().each(function () {
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
            getShippingFields(context).on('change input', function () {
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
        }
    };

    /**
     * Checks to see if we can recalculate shipping rates and dispatches the command.
     */
    $.fn.commerceCheckShippingRecalculation = function () {
        var recalculate = true;
        var $shippingProfileSelectOptions = $('[data-drupal-selector="edit-shipping-information-shipping-profile-profile-selection"]').find('option:selected');

        if (!$shippingProfileSelectOptions.length || $shippingProfileSelectOptions.val() === 'new_address') {
            // validate minimum fields to calculate shipping
            var $postalCode = $('input[data-drupal-selector="edit-shipping-information-shipping-profile-address-0-address-postal-code"]');
            if ($postalCode.length) {
                recalculate = ($postalCode.val().length && $postalCode.val().length > 4);
            }
            getShippingFields().not($postalCode).each(function () {
                var valid = true;
                if (!$(this).val().length) {
                    valid = false;
                    recalculate = false;
                }
                return valid;
            });
        }

        // Define the callback used with setTimeout to click the recalculation button
        // if there is ongoing AJAX operation.
        if (recalculate && setExistingShippingValues()) {
            return setTimeout(function () {
                var $recalcButton = $('[data-drupal-selector="edit-shipping-information-recalculate-shipping"]');
                if ($recalcButton.prop('disabled')) {
                    return setTimeout($.fn.commerceCheckShippingRecalculation, 100);
                }
                $recalcButton.trigger('mousedown');
            }, 100);
        }
    };

})(jQuery, Drupal);
