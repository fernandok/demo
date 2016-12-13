<?php

namespace Drupal\cypress\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\file\Plugin\Field\FieldFormatter\FileSize;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Utility\Bytes;

/**
 * Formatter that shows the file size in a human readable way.
 *
 * @FieldFormatter(
 *   id = "file_size_mb",
 *   label = @Translation("File size in MB"),
 *   field_types = {
 *     "integer"
 *   }
 * )
 */
class FileSizeInMb extends FileSize {

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return parent::isApplicable($field_definition) && $field_definition->getName() === 'filesize';
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#markup' => round(($item->value / pow(Bytes::KILOBYTE, 2)), 2) . ' MB',
      ];
    }

    return $elements;
  }

}
