(function ($){
  $(document).ready(function () {
    views_exposed_filter = $('body.path-file-search .region-content div.view-filters.form-group');
    tag_cloud = $('body.path-file-search .region-content section.block-dynamictagclouds ul.default_tag_clouds');
    $('#file-advanced-search').click(function(){
      views_exposed_filter.slideToggle();
      tag_cloud.slideToggle();
      if($(this).hasClass('expanded')) {
        $(this).removeClass('expanded').html('Advanced Search');
      }
      else {
        $(this).addClass('expanded').html('Hide Advanced Search');
      }
    });
  });
})(jQuery);
