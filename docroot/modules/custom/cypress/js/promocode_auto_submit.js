(function($) {
  Drupal.behaviors.promocodeBehaviour = {
    attach: function (context, settings) {
      if (typeof $('#edit-promocode-status').val() != 'undefined' && $('#edit-submit').attr('disabled') != 'disabled') {
        $('#edit-submit').trigger('click');
      }
    }
  }
})(jQuery, Drupal);
