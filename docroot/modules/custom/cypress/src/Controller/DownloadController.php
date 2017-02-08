<?php

namespace Drupal\cypress\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\download_all_files\Plugin\Archiver\Zip;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Drupal\file_download_tracker\FileDownloadTracker;

/**
 * Class DownloadController.
 *
 * @package Drupal\cypress\Controller
 */
class DownloadController extends ControllerBase {

  /**
   * Method archive all file associated with node and stream it for download.
   *
   * @param int $node_id
   *   Node id.
   * @param string $field_name
   *   Node file field name.
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\RedirectResponse
   *   Error Messages.
   */
  public function downloadAllDocs($node_id, $field_name) {
    $node = Node::load($node_id);
    $zip_files_directory = DRUPAL_ROOT . '/sites/default/files/daf_zips';
    $file_path = $zip_files_directory . '/' . $node->getTitle() . ' - ' . $field_name . '.zip';

    // If zip file is already present and node is not been changed since
    // Then just stream it directly.
    if (file_exists($file_path)) {
      $file_last_modified = filemtime($file_path);
      $node_changed = $node->getChangedTime();
      if ($node_changed < $file_last_modified) {
        return $this->streamZipFile($file_path);
      }
    }

    $files = [];

    // Construct zip archive and add all files, then stream it.
    $node_docs = $node->get($field_name)->getValue();
    foreach ($node_docs as $doc) {
      $paragraph = Paragraph::load($doc['target_id']);
      $is_akamai = $paragraph->get('field_file_type')->get(0)->getValue()['value'];
      if ($is_akamai) {
        continue;
      }
      $file_id = $paragraph->get('field_file')->get(0)->getValue()['target_id'];
      $file_obj = File::load($file_id);
      if ($file_obj) {
        $files[$file_obj->get('fid')->getValue()[0]['value']] = $file_obj->getFileUri();
      }
    }

    return $this->compressFiles($files, $zip_files_directory, $file_path);
  }

  /**
   * Method archive selected file associated with node and stream it for download.
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\RedirectResponse
   *   Error Messages.
   */
  public function downloadSelectedDocs($node_id) {
    if ($node_id != 0) {
      $node = Node::load($node_id);
      $title = $node->getTitle();
    }
    else {
      $title = 'All Files';
    }
    $file_ids = \Drupal::request()->get('docs');
    $file_ids = explode(',', $file_ids);
    $file_objs = File::loadMultiple($file_ids);
    $files = [];
    $zip_files_directory = DRUPAL_ROOT . '/sites/default/files/daf_zips';
    $file_path = $zip_files_directory . '/' . $title . ' - Selected.zip';
    foreach ($file_objs as $file_obj) {
      $files[$file_obj->get('fid')->getValue()[0]['value']] = $file_obj->getFileUri();
    }

    return $this->compressFiles($files, $zip_files_directory, $file_path);
  }

  /**
   * Method to compress files and stream it.
   *
   * @param array $files
   *   Files ids.
   * @param string $zip_files_directory
   *   Zip file directory.
   * @param string $file_path
   *   Full file path.
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\RedirectResponse
   *   Error Messages.
   */
  protected function compressFiles($files, $zip_files_directory, $file_path) {
    // Dispatch the FileDownloadTracker Event.
    $selected_fids = array_keys($files);
    // load the Symfony event dispatcher object through services
    $dispatcher = \Drupal::service('event_dispatcher');
    // creating our event class object.
    $event = new FileDownloadTracker($selected_fids);
    // dispatching the event through the ‘dispatch’  method,
    // passing event name and event object ‘$event’ as parameters.
    $dispatcher->dispatch(FileDownloadTracker::SUBMIT, $event);

    $redirect_on_error_to = empty($_SERVER['HTTP_REFERER']) ? '/' : $_SERVER['HTTP_REFERER'];
    $file_zip = NULL;
    if (file_prepare_directory($zip_files_directory, FILE_CREATE_DIRECTORY)) {
      foreach ($files as $file) {
        $file = \Drupal::service('file_system')->realpath($file);
        if (!$file_zip instanceof Zip) {

          $file_zip = new Zip($file_path);
        }
        $file_zip->add($file);
      }

      if ($file_zip instanceof Zip) {
        $file_zip->close();
        return $this->streamZipFile($file_path);
      }
      else {
        drupal_set_message('No files found for this node to be downloaded', 'error', TRUE);
        return new RedirectResponse($redirect_on_error_to);
      }
    }
    else {
      drupal_set_message('Zip file directory not found.', 'error', TRUE);
      return new RedirectResponse($redirect_on_error_to);
    }
  }

  /**
   * Method to stream created zip file.
   *
   * @param string $file_path
   *   File physical path.
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
   *   File Response.
   */
  protected function streamZipFile($file_path) {
    $binary_file_response = new BinaryFileResponse($file_path);
    $binary_file_response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename($file_path));

    return $binary_file_response;
  }

}
