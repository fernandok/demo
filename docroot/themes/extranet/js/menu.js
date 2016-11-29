(function ($){
  $(document).ready(function () {
    $('#navbar .region-navigation>.container').append('<div id="subnavbar" class="hidden-xs"></div>');
    $('#block-cypressmainnavigation ul.menu.nav li.expanded>ul.dropdown-menu').each(function() {
      $(this).removeClass('dropdown-menu').appendTo('#subnavbar');
    });

    // Click event.
    $('#block-cypressmainnavigation ul.menu.nav li.expanded>a').click(function() {
      index = $(this).data('index');
      $('#subnavbar>ul').hide();
      if (typeof index != 'undefined') {
        if ($(this).hasClass('active')) {
          $('#subnavbar>ul').eq(index).hide();
          $('#block-cypressmainnavigation ul.menu.nav li.expanded>a').removeClass('active');
        }
        else {
          $('#block-cypressmainnavigation ul.menu.nav li.expanded>a').removeClass('active');
          $(this).addClass('active');
          $('#subnavbar>ul').eq(index).show();
        }
      }
      else {
        $('#block-cypressmainnavigation ul.menu.nav li.expanded>a').removeClass('active');
      }
    });
  });
})(jQuery);
