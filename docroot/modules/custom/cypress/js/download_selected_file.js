(function($) {
  Drupal.behaviors.downloadBehaviour = {
    attach: function (context, settings) {
      // Manage download all and selected files.
      $('.static-download-all-files-wrapper a, .download-all-files-wrapper a', context).on('click', function(e) {
        e.preventDefault();
        downloadUrl = $(this).attr('href');
        nodeId = downloadUrl.split('/')[2];
        numberOfFilesSelected = $('.download_file_selector:checked', context).length;
        if (numberOfFilesSelected > 0) {
          downloadUrl = '/download_selected_documents/' + nodeId + '?docs=';
          var checkedVals = $('.download_file_selector:checked').map(function() {
            return this.value;
          }).get();
          downloadUrl += checkedVals.join(",");
        }
        window.location = downloadUrl;
      });
      // Change download link label.
      $('.download_file_selector', context).on('click', function() {
        numberOfFilesSelected = $('.download_file_selector:checked', context).length;
        download_label = $('.download-all-files .download-label');
        if (numberOfFilesSelected > 0) {
          download_label.html('DOWNLOAD SELECTED FILE(S)');
        }
        else {
          download_label.html('DOWNLOAD ALL FILE(S)');
        }
      });
    }
  }
})(jQuery, Drupal);
