<?php

/**
 * @file
 * Contains \Drupal\custom_solr_search\SearchSolrAll.
 */

namespace Drupal\custom_solr_search;


/**
 * Class SearchSolrAll.
 *
 * @package Drupal\custom_solr_search
 */
class SearchSolrAll {
  /**
   * Constructor.
   */
  public function __construct() {

  }

  /**
   * Searching in all Solr servers.
   *
   * @param string $keyword
   *   String to search.
   * @return array $results
   *   Array of search results.
   */
  public function seachAll($keyword){
    $servers = \Drupal::service('custom_solr_search.solr_servers')->getServers();
    $results = array();
    foreach ($servers as $server_machine => $server_display) {
      $result = \Drupal::service('custom_solr_search.search')->basicSearch($keyword, 0, 5, $server_machine);
      $results = array_merge($results, $result);
    }
    return $results;
  }
}
