<?php

/**
 * @file
 * Contains file_download_entity.page.inc.
 *
 * Page callback for File download entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for File download entity templates.
 *
 * Default template: file_download_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_file_download_entity(array &$variables) {
  // Fetch FileDownloadEntity Entity Object.
  $file_download_entity = $variables['elements']['#file_download_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
