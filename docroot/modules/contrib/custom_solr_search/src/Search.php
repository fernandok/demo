<?php

namespace Drupal\custom_solr_search;

use Solarium\QueryType\Select\Query\Query as SelectQuery;

/**
 * Class Search.
 *
 * @package Drupal\custom_solr_search
 */
class Search {

  /**
   * Constructor.
   */
  public function __construct() {

  }

  /**
   * Method for basic query and return result.
   *
   * @param string $keyword
   *   Search keyword.
   * @param int $offset
   *   Query offset.
   * @param int $limit
   *   Query limit.
   * @param string $solr_core
   *   Solarium client.
   *
   * @return array
   *   Query result.
   */
  public function basicSearch($keyword, $offset, $limit, $solr_core) {
    // Get solarium client.
    $solr_client = \Drupal::service('custom_solr_search.server')->getSolrClient($solr_core);
    // Initiate Solarium basic select query.
    $query = new SelectQuery();
    // Set search keyword.
    $query->setQuery($keyword);
    // Set offset.
    $query->setStart($offset);
    // Set limit.
    $query->setRows($limit);
    // Create a request for query.
    $request = $solr_client->createRequest($query);
    // Execute request.
    $response = $solr_client->executeRequest($request);
    // Extract result from response.
    $result = $this->extractResult($response);

    return $result;
  }

  /**
   * Method to extract result from query response.
   *
   * @param string $response
   *   Raw response.
   *
   * @return array
   *   Query result.
   */
  public function extractResult($response) {
    // If response status code is 200, return response docs.
    if ($response->getStatusCode() == 200) {
      // Get response raw body.
      $raw_body = $response->getBody();
      // Decode json string to array or object.
      $result = json_decode($raw_body);
      // Get response docs result.
      $result_docs = $result->response->docs;
      // Return.
      return $result_docs;
    }
    else {
      // Throw exception.
    }
  }

}
