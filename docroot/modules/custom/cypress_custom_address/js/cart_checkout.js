/**
 * Created by vb-rajeshwari on 21/4/17.
 */
(function ($, Drupal) {
    Drupal.behaviors.netpce_cart = {
        attach: function (context, settings) {
         // click on dummy checkout
            $('#edit-checkout').hide();
            $('#checkout-dummy').click(function () {
               // console.log('hi');
                 $('#edit-checkout').trigger("click");
                return false;
            })
        }
    };

}(jQuery, Drupal));