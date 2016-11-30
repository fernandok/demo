(function ($){
  $(document).ready(function () {
    views_exposed_filter = $('body.path-file-search .region-content div.view-filters.form-group');
    $('#file-advanced-search').click(function(){
      views_exposed_filter.slideToggle();
      if($(this).hasClass('expanded')) {
        $(this).removeClass('expanded').html('Advanced Search');
      }
      else {
        $(this).addClass('expanded').html('Hide Advanced Search');
      }
    });
  });
})(jQuery);
