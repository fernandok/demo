/**
 * @file
 * Js for paragraph tablesort.
 */
(function ($) {
  $(document).ready(function () {
    // Tablesort parser for data size.
    $.tablesorter.addParser({
      id: 'datasize',
      is: function(s) {
        return s.match(new RegExp( /[0-9]+(\.[0-9]+)?\ (B|K|KB|G|GB|M|MB|T|TB)/i ));
      },
      format: function(s) {
        var suf = s.match(new RegExp( /(B|K|KB|G|GB|M|MB|T|TB)/i ))[1];
        var num = parseFloat(s.match(new RegExp( /^[0-9]+(\.[0-9]+)?/ ))[0]);
        switch(suf.toLowerCase()) {
          case 'b':
            return num;
          case 'k': case 'kb':
          return num * 1024;
          case 'm': case 'mb':
          return num * 1024 * 1024;
          case 'g': case 'gb':
          return num * 1024 * 1024 * 1024;
          case 't': case 'tb':
          return num * 1024 * 1024 * 1024 * 1024;
        }
      },
      type: 'numeric'
    });
    // Tablesort parser for date in format of dd/mm/yyyy.
    $.tablesorter.addParser({
      id: "dd/mm/yyyy",
      is: function(s) {
        return false;
      },
      format: function(s) {
        s = "" + s;
        var hit = s.match(/(\d{1,2})\/(\d{1,2})\/(\d{4})/);
        if (hit && hit.length == 4) {
          return hit[3] + hit[2] + hit[1];
        } else {
          return s;
        }
      },
      type: "text"
    });
    // Initialize tablesort for paragraph table.
    $(".paragraph_table").tablesorter({
      headers: {
        // disable sorting for first column.
        0: {sorter: false},
        6: {sorter: 'datasize'},
        7: {sorter: 'dd/mm/yyyy'}
      },
      textExtraction: {
        3: function(node, table, cellIndex){ return $(node).find("a").text(); }
      }
    });
    // Initialize tablesort for akamai table.
    $(".akamai_table").tablesorter({
      headers: {
        5: {sorter: 'datasize'},
        6: {sorter: 'dd/mm/yyyy'}
      },
      textExtraction: {
        2: function(node, table, cellIndex){ return $(node).find("a").text(); }
      }
    });

  });
})(jQuery);
