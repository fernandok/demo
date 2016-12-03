(function ($){
  $(document).ready(function () {
    $('#navbar .region-navigation>.container').append('<div id="subnavbar" class="hidden-xs"></div>');
    $('#block-cypressmainnavigation ul.menu.nav li.expanded>ul.dropdown-menu').each(function(index) {
      $(this).siblings('a').removeClass('dropdown-toggle').removeAttr('data-target').removeAttr('data-toggle').attr('data-index', index);
      $(this).removeClass('dropdown-menu').appendTo('#subnavbar');
    });

    // Click event.
    $('#block-cypressmainnavigation ul.menu.nav li.expanded>a').click(function(e) {
      index = $(this).data('index');
      $('#subnavbar>ul').hide();
      if (typeof index != 'undefined') {
        e.preventDefault();
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

    // Manage active sub menu.
    $('.region-sidebar-first ul.menu.nav>li.active').each(function(){
      if ($(this).find('li.active').length > 0) {
        $(this).removeClass('active');
      }
    });
  });
})(jQuery);
