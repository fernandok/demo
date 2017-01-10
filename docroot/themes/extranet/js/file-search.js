/**
 * @file
 * Search for all types of files.
 */

(function ($) {
  $.urlParam = function (name) {
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results == null) {
       return null;
    }
    else {
       return results[1] || 0;
    }
  }

  $(document).ready(function () {
    // Advanced search.
    views_exposed_filter = $('body.path-file-search .region-content div.view-filters.form-group');
    tag_cloud = $('body.path-file-search .region-content section.block-dynamictagclouds ul.default_tag_clouds');
    $('#file-advanced-search').click(function () {
      views_exposed_filter.slideToggle();
      tag_cloud.slideToggle();
      if ($(this).hasClass('expanded')) {
        $(this).removeClass('expanded').html('Advanced Search');
      }
      else {
        $(this).addClass('expanded').html('Hide Advanced Search');
      }
    });

    // Highlighting tag cloud.
    selected_bu = decodeURIComponent($.urlParam('bu')).split('+')[0];
    selected_div = decodeURIComponent($.urlParam('division')).split('+')[0];
    selected_language = decodeURIComponent($.urlParam('language')).split('+')[0];
    $('body.path-file-search ul.default_tag_clouds li a').each(function () {
      if ($(this).data('tid') == selected_bu || $(this).data('tid') == selected_div || $(this).data('tid') == selected_language) {
        $(this).addClass('selected');
      }
    });

    if ($.urlParam('filename') != null && $.urlParam('product') != null) {
      $('#file-advanced-search').trigger('click');
    }
  });
})(jQuery);
