/**
* @file
* Defines behaviors and callbacks for Commerce Shipping
*/
(function ($, Drupal) {
  'use strict';

  var existingValues = {};
  var recalculateTimer = null;
  var shippingAjaxSelectors = {
    wrapper: '#shipping-information-wrapper',
    shippingMethods: '[data-drupal-selector^="edit-shipping-information-shipments-"]',
    requiredFields: '.required[data-drupal-selector^="edit-shipping-information-shipping-profile-"]:not(.country), [data-drupal-selector="edit-shipping-information-shipping-profile-profile-selection"]',
    profileSelectField: '[data-drupal-selector="edit-shipping-information-shipping-profile-profile-selection"]',
    postalCodeField: 'input[data-drupal-selector="edit-shipping-information-shipping-profile-address-0-address-postal-code"]',
    recalculateButton: '[data-drupal-selector="edit-shipping-information-recalculate-shipping"]',
    nextButton: '[data-drupal-selector="edit-actions-next"]'
  };

  /**
   * Caches the existing shipping-related form values for comparison.
   *
   * @returns {boolean}
   */
  function setExistingShippingValues() {
    var changed = false;
    $(shippingAjaxSelectors.requiredFields).each(function () {
      var id = $(this).data('drupal-selector');
      var val = $(this).val();
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
      if (!$(shippingAjaxSelectors.shippingMethods, context).length) {
        $(shippingAjaxSelectors.nextButton, context).prop('disabled', true);
      }
      $(shippingAjaxSelectors.requiredFields, context).on('change input', function () {
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
              // check that shipping info is populated
              if (!$(shippingAjaxSelectors.shippingMethods, context).length) {
               $(shippingAjaxSelectors.nextButton, context).prop('disabled', true);
              }
            }
            else if (mutation.removedNodes.length) {
              // check that shipping info is populated
              if ($(shippingAjaxSelectors.shippingMethods, context).length) {
                $(shippingAjaxSelectors.nextButton, context).prop('disabled', false);
             }
           }
          }
        });
      });
      var obsConfig = {childList: true, subtree: true};
      var $wrapper = $(shippingAjaxSelectors.wrapper, context);
      if ($wrapper.length > 0) {
        observer.observe($wrapper[0], obsConfig);
      }
    }
  };

  /**
   * Checks to see if we can recalculate shipping rates and dispatches the command.
   */
  $.fn.commerceCheckShippingRecalculation = function () {
    var recalculate = true;
    var $selectedProfile = $(shippingAjaxSelectors.profileSelectField).find('option:selected');
    if (!$selectedProfile.length || $selectedProfile.val() === 'new_profile') {
      // validate minimum fields to calculate shipping
     var $postalCode = $(shippingAjaxSelectors.postalCodeField);
      if ($postalCode.length) {
        recalculate = ($postalCode.val().length && $postalCode.val().length > 4);
      }
      if (recalculate) {
        $(shippingAjaxSelectors.requiredFields).not($postalCode).each(function () {
          var valid = true;
          if (!$(this).val().length) {
            valid = false;
            recalculate = false;
          }
          return valid;
        });
      }
   }

    // Define the callback used with setTimeout to click the recalculation button
    // if there is ongoing AJAX operation.
    if (recalculate && setExistingShippingValues()) {
     return setTimeout(function () {
        var $recalcButton = $(shippingAjaxSelectors.recalculateButton);
        if ($recalcButton.prop('disabled')) {
          return setTimeout($.fn.commerceCheckShippingRecalculation, 100);
        }
        $recalcButton.trigger('mousedown');
      }, 100);
    }
  };

})(jQuery, Drupal);
