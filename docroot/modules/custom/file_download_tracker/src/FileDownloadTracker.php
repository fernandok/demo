<?php
namespace Drupal\file_download_tracker;

use Symfony\Component\EventDispatcher\Event;

class FileDownloadTracker extends Event {
  protected $fileID;
  const SUBMIT = 'event.submit';
  public function __construct($fileID) {
    $this->fileID = $fileID;
  }
  public function getFileID() {
    return $this->fileID;
  }
}