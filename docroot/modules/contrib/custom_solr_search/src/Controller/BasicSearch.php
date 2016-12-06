<?php

/**
 * @file
 * Contains \Drupal\custom_solr_search\Controller\BasicSearch.
 */

namespace Drupal\custom_solr_search\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class BasicSearch.
 *
 * @package Drupal\custom_solr_search\Controller
 */
class BasicSearch extends ControllerBase {

  /**
   * Search.
   *
   * @return string
   *   Return Hello string.
   */
  public function search($server = NULL, $keyword = NULL) {
    // Search form.
    $render['form'] = $this->formBuilder()->getForm('Drupal\custom_solr_search\Form\SearchForm', $server, $keyword);
    // Display result if keyword is defined.
    if (!empty($keyword)) {
      // Get search results from solr core.
      if ($server == 'all') {
        $results = \Drupal::service('custom_solr_search.search_all')->seachAll($keyword);
      }
      else {
        $results = \Drupal::service('custom_solr_search.search')->basicSearch($keyword, 0, 5, $server);
      }
//      echopre($results);exit;

      // Format result to display as table.
      foreach ($results as $result) {

        if (isset($result->title)) {
          $title = $result->title;
        }
        else {
          $title = $result->label;
        }
        $render['result'][] = array(
          '#theme' => 'custom_solr_search_result',
          '#url' => $result->url[0],
          '#title' => $title,
          '#author' => $result->author_sort,
          '#publishDate' => implode(', ', $result->publishDate),
          '#publisher' => implode(', ', $result->publisher),
          '#topic' => implode(', ', $result->topic)
        );
      }
    }

    return $render;
  }

}
