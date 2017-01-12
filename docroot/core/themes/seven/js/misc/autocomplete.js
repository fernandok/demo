/**
 * @file
 * Extends autocomplete based on jQuery UI.
 *
 * @todo Remove once jQuery UI is no longer used?
 */

(function ($, Drupal) {
  'use strict';

  // Remove term id from autocomplete field on page load.
  $(document).ready(function(){
    var value = '', splitted_value = [], value_without_id = '';
    $('.form-autocomplete').each(function(){
      value = $(this).val();
      $(this).val(value.replace(/\s\(\d+\)/g,''));
    });
  });

  // Ensure the input element has a "change" event triggered. This is important
  // so that summaries in vertical tabs can be updated properly.
  // @see Drupal.behaviors.formUpdated
  $(document).on('autocompleteselect', '.form-autocomplete', function (e) {
    $(e.target).trigger('change.formUpdated');
  });

  // Extend ui.autocomplete widget so it triggers the glyphicon throbber.
  $.widget('ui.autocomplete', $.ui.autocomplete, {
    _search: function (value) {
      this.pending++;
      this.element.addClass('ui-autocomplete-loading');
      this.cancelSearch = false;
      this.source({term: value}, this._response());
    },
    _response: function () {
      var index = ++this.requestIndex;
      return $.proxy(function (content) {
        for (var i=0;i<content.length;i++) {
          content[i].value = content[i].value.replace(/\s\(\d+\)/g,'');
        }
        if (index === this.requestIndex) this.__response(content);
        this.pending--;
        this.element.removeClass('ui-autocomplete-loading');
      }, this);
    }
  });

})(jQuery, Drupal);
