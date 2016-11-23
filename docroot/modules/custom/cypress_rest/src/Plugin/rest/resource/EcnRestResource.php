<?php

namespace Drupal\cypress_rest\Plugin\rest\resource;

use Drupal\Core\Entity\Entity;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileUsage\FileUsageBase;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "ecn_rest_resource",
 *   label = @Translation("Ecn rest resource"),
 *   uri_paths = {
 *     "canonical" = "/api/ecn",
*      "https://www.drupal.org/link-relations/create" = "//api/ecn"
 *   }
 * )
 */
class EcnRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('cypress_rest'),
      $container->get('current_user')
    );
  }

  /**
   * Responds to POST requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($data) {
    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    // Validate ECN data.
    $this->validateEcnData($data);

    // Process ECN data.
    return $this->processEcnData($data);
  }

  /**
   * Method to validate ECN post data.
   *
   * @param array $data
   *   ECN post data.
   *
   * @throws HttpException
   *   Bad request exception.
   */
  private function validateEcnData($data) {
    // Validate node id.
    if (!isset($data['nid']) || empty($data['nid'])) {
      throw new HttpException(400, 'Node Id required and cannot be empty.');
    }
    // Validate documents.
    if (!isset($data['documents']) || empty($data['documents'])) {
      throw new HttpException(400, 'Atleast one document data is required.');
    }
    else {
      $documents = $data['documents'];
      foreach ($documents as $doc) {
        // Validate file id for delete operation.
        if (!empty($doc['delete']) && $doc['delete']) {
          if (!isset($doc['file_id']) || empty($doc['file_id'])) {
            throw new HttpException(400, 'File id is required for delete operation.');
          }
        }
        // Validate file attributes if add/update operation.
        else {
          // Validate file name.
          if (!isset($doc['file_name']) || empty($doc['file_name'])) {
            throw new HttpException(400, 'File name is required for add/update operation.');
          }
          if (!isset($doc['file_data']) || empty($doc['file_data'])) {
            throw new HttpException(400, 'Base64 encode file binary data is required for add/update operation.');
          }
        }
      }
    }
  }

  /**
   * Method to process ECN post data.
   *
   * @param array $data
   *   ECN post data.
   *
   * @return ResourceResponse
   *   Response with list of processed files' id.
   */
  private function processEcnData($data) {
    $documents = $data['documents'];
    foreach ($documents as &$doc) {
      // Get file operation.
      if (isset($doc['delete']) && $doc['delete']) {
        $doc['op'] = 'delete';
      }
      elseif (isset($doc['file_id']) && is_numeric($doc['file_id'])) {
        $doc['op'] = 'update';
      }
      elseif (!isset($doc['file_id'])
      || (isset($doc['file_id']) && empty($doc['file_id']))) {
        $doc['op'] = 'add';
      }

      // Validate operation.
      if (!isset($doc['op'])) {
        throw new HttpException(400, 'For one of the file, not able to identify the operation.');
      }
    }

    return $this->processNodeDocuments($data['nid'], $documents);
  }

  /**
   * Method to process node documents.
   *
   * @param integer $nid
   *   Node id.
   * @param array $documents
   *   Array of documents to be processed.
   *
   * @return ResourceResponse
   *   Response with list of processed files' id.
   */
  private function processNodeDocuments($nid, $documents) {
    // Load node entity.
    $page_storage = \Drupal::entityManager()->getStorage('node');
    $page = $page_storage->load($nid);
    if (!($page instanceof \Drupal\node\Entity\Node)) {
      throw new HttpException(400, 'There is no node found for the given node id.');
    }

    // Get paragraph ids of the node.
    $paragraphs = $page->get('field_files')->getValue();
    $paragraphs_ids = [];
    foreach ($paragraphs as $paragraph) {
      $paragraphs_ids[] = $paragraph['target_id'];
    }
    $paragraphs_ids = implode(',', $paragraphs_ids);
    // Get file id of each paragraph entity.
    $query = \Drupal::database()->query('select pi.id, pff.field_file_target_id from paragraphs_item pi
      join paragraph__field_file pff
      on pi.id = pff.entity_id and pi.type = pff.bundle and pi.id in (' . $paragraphs_ids . ')');
    $results = $query->fetchAll();

    $paragraphs_file_ids = [];
    foreach ($results as $result) {
      $paragraphs_file_ids[$result->id] = $result->field_file_target_id;
    }
    // Process the paragraph entity corresponding to the file.
    $processed_files = [];
    foreach ($documents as $doc) {
      // Get file paragraph id.
      $doc_paragraph_id = array_search($doc['file_id'], $paragraphs_file_ids);
      if (empty($doc_paragraph_id)) {
        throw new HttpException(400, 'No file found associated with node for the given file id.');
      }
      // Call operation based callback.
      $process_callback = $doc['op'] . 'Paragraph';
      $processed_files[] = $this->{$process_callback}($page, $doc, $doc_paragraph_id);
    }
    // Save the page node.
    $page->save();

    return new ResourceResponse($processed_files);
  }

  /**
   * Method to add paragraph.
   *
   * @param object $page
   *   Node object.
   * @param array $doc
   *   Document details.
   */
  private function addParagraph(&$page, $doc) {

  }

  /**
   * Method to update paragraph.
   *
   * @param object $page
   *   Node object.
   * @param array $doc
   *   Document details.
   */
  private function updateParagraph(&$page, $doc, $paragraph_id) {

  }

  /**
   * Method to delete paragraph.
   *
   * @param object $page
   *   Node object.
   * @param array $doc
   *   Document details.
   */
  private function deleteParagraph(&$page, $doc, $paragraph_id) {
    // Get all paragraph list in node.
    $paragraphs = $page->get('field_files')->getValue();
    // Remove the paragraph to be delete from node.
    foreach ($paragraphs as $key => $paragraph) {
      if ($paragraph['target_id'] == $paragraph_id) {
        unset($paragraphs[$key]);
      }
    }
    // Update the node paragraph list.
    $page->field_files = $paragraphs;
    // Delete the paragraph.
    $paragraph = Paragraph::load($paragraph_id);
    $paragraph->delete();
    // Delete the file.
    $file = File::load($doc['file_id']);
    FileUsageBase::delete($file);

    return NULL;
  }

}
