/**
 * @file
 * Defines behaviors and callbacks for Commerce Shipping
 */
(function ($, Drupal) {
    'use strict';

    /**
     * Attaches the shipping recalculate behavior.
     *
     * @type {Drupal~behavior}
     *
     * @prop {Drupal~behaviorAttach} attach
     */
    Drupal.behaviors.commerceShippingRecalculate = {
        attach: function (context) {
            if (!$('[id^="edit-shipping-information-shipments-"] .form-item', context).length) {
                $('#edit-actions-next', context).prop('disabled', true);
            }
            $('[id^="edit-shipping-information-"] .form-item', context).children('input.required').filter(':not(.country)').on('change keydown paste input', function () {
                // just changed a field, clear any timer
                window.clearTimeout(recalculateTimer);
                // start the timer that will trigger recalculation after some inactivity
                recalculateTimer = window.setTimeout(
                    $.fn.commerceCheckShippingRecalculation,
                    1500
                );
            });


            $(window).load(function () {
                $.fn.commerceCheckShippingRecalculation();
            });

            // Prefer to observe #edit-shipping-information-shipments, but ajax doesn't trigger an event for it
            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.type == 'childList') { // showing and hiding the ajax indicator
                        if (mutation.addedNodes.length) {
                            $('#edit-actions-next', context).prop('disabled', true);
                        } else if (mutation.removedNodes.length) {
                            // check that shipping info is populated
                            if ($('[id^="edit-shipping-information-shipments-"] .form-item', context).length) {
                                $('#edit-actions-next', context).prop('disabled', false);
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
        var $shippingProfileSelectOptions = $('#edit-shipping-information-shipping-profile-profile-selection').find('option:selected');
        if ($shippingProfileSelectOptions.length && $shippingProfileSelectOptions.val() != 'new_address') {
            recalculate = true;
        } else {
            // validate minimum fields to calculate shipping
            var dependentLocality = $('[id^="edit-shipping-information-"] .form-item .dependent-locality.required');
            var dependentLocalityVal = null;
            if (!dependentLocality.length) {
                dependentLocalityVal = 'notrequired';
            } else {
                dependentLocalityVal = dependentLocality.val();
            }
            var locality = $('[id^="edit-shipping-information-"] .form-item .locality.required');
            var localityVal = null;
            if (!locality.length) {
                localityVal = 'notrequired';
            } else {
                localityVal = locality.val();
            }
            var admArea = $('[id^="edit-shipping-information-"] .form-item .administrative-area.required');
            var admAreaVal = null;
            if (!admArea.length) {
                admAreaVal = 'notrequired';
            } else {
                admAreaVal = admArea.val();
            }
            var postal = $('[id^="edit-shipping-information-"] .form-item .postal-code.required');
            var postalVal = null;
            if (!postal.length) {
                postalVal = 'notrequired';
            } else {
                postalVal = postal.val();
            }
            if (dependentLocalityVal && localityVal && admAreaVal && (postalVal && postalVal.length > 4)) {
                recalculate = true;
            }
        }

        // Define the callback used with setTimeout to click the recalculation button
        // if there is ongoing AJAX operation.
        var recalculateCallback = function () {
            var $recalcButton = $('[id^="edit-shipping-information-recalculate"]');
            if ($recalcButton.prop("disabled")) {
                return setTimeout($.fn.commerceCheckShippingRecalculation, 100);
            }

            // Trigger the mousedown event on the shipping recalculation button.
            $recalcButton.trigger('mousedown');
        };

        if (recalculate == true) {
            return setTimeout(recalculateCallback, 100);
        }
    }

})(jQuery, Drupal);
