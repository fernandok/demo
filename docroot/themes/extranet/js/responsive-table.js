(function ($){
  $(document).ready(function () {
    // Table responsive.
    if ($(window).width() <= 800) {
      $('table').each(function() {
        headers = [];
        $(this).find('tr th').each(function() {
          if (typeof $(this).data('title') != 'undefined') {
            header = $(this).data('title').split(',');
            headers = headers.concat(header);
          }
          else {
            header = $(this).text();
            header = header.replace('Sort descending', '');
            header = header.replace('Sort ascending', '');
            headers.push(header.trim());
          }
        });
        $(this).find('tr').each(function() {
          $(this).find('td').each(function(index) {
            $(this).attr('data-title', headers[index]);
            if ($(this).text().trim() == '') {
              $(this).html('<div class="responsive-table-empty-row-data"></div>');
            }
          });
        });
      });
    }
  });
})(jQuery);
