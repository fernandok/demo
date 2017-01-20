<?php

namespace Drupal\cypress_rest\Plugin\rest\resource;

use Drupal\node\Entity\Node;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileUsage\FileUsageBase;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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
   * List of paragraphs group by node id.
   *
   * @var array
   */
  protected $paragraphs;

  /**
   * File id associated with paragraphs.
   *
   * @var array
   */
  protected $paragraphsFileIds;

  /**
   * Paragraph id for the documents.
   *
   * @var array
   */
  protected $docParagraphId;

  protected $fileField;

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
    if (empty($data)) {
      return new ResourceResponse(['error' => 'Empty ECN data cannot be processed.']);
    }

    // Process ECN data.
    return $this->processEcnData($data);
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
    $response = [];
    foreach ($data as $node) {
      if (empty($node)) {
        $response[] = ['error' => 'Empty node data cannot be processed.'];
      }
      elseif (!isset($node['nid']) || empty($node['nid'])) {
        $response[] = ['error' => 'Node Id required and cannot be empty.'];
      }
      else {
        $page_storage = \Drupal::entityManager()->getStorage('node');
        $page = $page_storage->load($node['nid']);
        if (!($page instanceof Node) || ($page->getType() != 'cy_page' &&
          $page->getType() != 'dwr')) {
          $response[] = ['error' => 'There is no node found for the given node id ' . $node['nid'] . '.'];
          continue;
        }
        else {
          if ($page->getType() == 'cy_page') {
            $this->fileField = 'field_files';
          }
          else if ($page->getType() == 'dwr') {
            $this->fileField = 'field_dwr_files';
          }
        }
        $this->setConditionCheckingProperties($page);
        $documents = $node['documents'];
        foreach ($documents as $doc) {
          if (!isset($doc['spec_number']) || empty($doc['spec_number'])) {
            $response[] = ['error' => 'Spec number is required to process the file.'];
            continue;
          }
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
            $response[] = [
              'spec_number' => $doc['spec_number'],
              'error' => 'Not able to identify the file operation.',
            ];
            continue;
          }
          $error = $this->validateNodeDocument($page, $doc);
          if (empty($error)) {
            $response[] = $this->processNodeDocument($page, $doc);
          }
          else {
            $response[] = [
              'spec_number' => $doc['spec_number'],
              'error' => $error,
            ];
          }
        }
        $page->save();
      }
    }

    return new ResourceResponse($response);
  }

  /**
   * Method to set basic condition checking properties for ECN.
   *
   * @param object $node
   *   Currently processing node.
   */
  private function setConditionCheckingProperties($node) {
    $nid = $node->id();
    $this->paragraphs[$nid] = $node->get($this->fileField)->getValue();
    $paragraphs_ids = [];
    foreach ($this->paragraphs[$nid] as $paragraph) {
      $paragraphs_ids[] = $paragraph['target_id'];
    }
    $this->paragraphs_file_ids[$nid] = [];
    if (!empty($paragraphs_ids)) {
      $paragraphs_ids = implode(',', $paragraphs_ids);
      // Get file id of each paragraph entity.
      $query = \Drupal::database()->query('select pi.id, pff.field_file_target_id from paragraphs_item pi
        join paragraph__field_file pff
        on pi.id = pff.entity_id and pi.type = pff.bundle and pi.id in (' . $paragraphs_ids . ')');
      $results = $query->fetchAll();
      foreach ($results as $result) {
        $this->paragraphs_file_ids[$nid][$result->id] = $result->field_file_target_id;
      }
    }
  }

  /**
   * Method to validate node documents.
   *
   * @param int $node
   *   Node id.
   * @param array $doc
   *   Document to be validated.
   *
   * @return string
   *   Error string if any.
   */
  private function validateNodeDocument($node, $doc) {
    $error = '';
    $fields_to_validate = [];
    switch ($doc['op']) {
      case 'add':
        $fields_to_validate = [
          'file_name',
          'file_data',
          'file_description',
          'business_unit',
          'doc_type',
          'family',
          'language',
          'spec_revision',
        ];
        break;

      case 'update':
        $fields_to_validate = [
          'file_id',
          'file_name',
          'file_data',
          'file_description',
          'business_unit',
          'doc_type',
          'family',
          'language',
          'spec_revision',
        ];
        break;

      case 'delete':
        $fields_to_validate = [
          'file_id',
        ];
        break;
    }

    foreach ($fields_to_validate as $field) {
      $field_value = $doc[$field];
      $field = str_replace('_', '', ucwords($field, '_'));
      $field_valid_callback = 'validate' . $field;
      $error = $this->{$field_valid_callback}($node, $field_value);
      if (!empty($error)) {
        break;
      }
    }

    return $error;
  }

  /**
   * Method to validate file id.
   *
   * @param object $node
   *   Node object.
   * @param int $value
   *   File id.
   *
   * @return string
   *   Error message.
   */
  private function validateFileId($node, $value) {
    $error = '';
    $nid = $node->id();
    if (empty($value)) {
      $error = 'File id is required for update/delete operation.';
    }
    else {
      $this->doc_paragraph_id[$value] = array_search($value, $this->paragraphs_file_ids[$nid]);
      if (empty($this->doc_paragraph_id[$value])) {
        $error = 'No file found associated with node for the given file id ' . $value . '.';
      }
    }

    return $error;
  }

  /**
   * Method to validate file name.
   *
   * @param object $node
   *   Node object.
   * @param int $value
   *   File id.
   *
   * @return string
   *   Error message.
   */
  private function validateFileName($node, $value) {
    $error = '';

    if (empty($value)) {
      $error = 'File name is required for add/update operation.';
    }

    return $error;
  }

  /**
   * Method to validate file data.
   *
   * @param object $node
   *   Node object.
   * @param int $value
   *   File id.
   *
   * @return string
   *   Error message.
   */
  private function validateFileData($node, $value) {
    $error = '';

    if (empty($value)) {
      $error = 'Base64 encode file binary data is required for add/update operation.';
    }

    return $error;
  }

  /**
   * Method to validate file description.
   *
   * @param object $node
   *   Node object.
   * @param int $value
   *   File id.
   *
   * @return string
   *   Error message.
   */
  private function validateFileDescription($node, $value) {
    $error = '';

    if (empty($value)) {
      $error = 'File description is required for add/update operation.';
    }

    return $error;
  }

  /**
   * Method to validate file business unit.
   *
   * @param object $node
   *   Node object.
   * @param int $value
   *   File id.
   *
   * @return string
   *   Error message.
   */
  private function validateBusinessUnit($node, $value) {
    $error = '';

    if (empty($value)) {
      $error = 'File business unit is required for add/update operation.';
    }

    return $error;
  }

  /**
   * Method to validate file doc type.
   *
   * @param object $node
   *   Node object.
   * @param int $value
   *   File id.
   *
   * @return string
   *   Error message.
   */
  private function validateDocType($node, $value) {
    $error = '';

    if (empty($value)) {
      $error = 'File doc type is required for add/update operation.';
    }

    return $error;
  }

  /**
   * Method to validate file family.
   *
   * @param object $node
   *   Node object.
   * @param int $value
   *   File id.
   *
   * @return string
   *   Error message.
   */
  private function validateFamily($node, $value) {
    $error = '';

    if (empty($value)) {
      $error = 'File family is required for add/update operation.';
    }

    return $error;
  }

  /**
   * Method to validate file language.
   *
   * @param object $node
   *   Node object.
   * @param int $value
   *   File id.
   *
   * @return string
   *   Error message.
   */
  private function validateLanguage($node, $value) {
    $error = '';

    if (empty($value)) {
      $error = 'File language is required for add/update operation.';
    }

    return $error;
  }

  /**
   * Method to validate file spec revision.
   *
   * @param object $node
   *   Node object.
   * @param int $value
   *   File id.
   *
   * @return string
   *   Error message.
   */
  private function validateSpecRevision($node, $value) {
    $error = '';

    if (empty($value)) {
      $error = 'File spec revision is required for add/update operation.';
    }

    return $error;
  }

  /**
   * Method to process node documents.
   *
   * @param int $page
   *   Node id.
   * @param array $doc
   *   Document to be processed.
   *
   * @return ResourceResponse
   *   Processed file id.
   */
  private function processNodeDocument($page, $doc) {
    $process_callback = $doc['op'] . 'Paragraph';
    $response = $this->{$process_callback}($page, $doc);

    return $response;
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
    $nid = $page->id();
    if (!isset($this->paragraphs[$nid])) {
      $this->paragraphs[$nid] = $node->get($this->fileField)->getValue();
    }
    // Save file.
    $file = file_save_data(base64_decode($doc['file_data']), 'public://' . $doc['file_name'], FILE_EXISTS_REPLACE);
    $tags = $this->getAllTags($doc);
    // Create new paragraph.
    $paragraph = Paragraph::create([
      'type' => 'documents',
      'field_file' => [
        "target_id" => $file->id(),
        "description" => $doc['file_description'],
      ],
      'field_application_tags' => $tags['application_tags'],
      'field_category' => $tags['category'],
      'field_cyu_training_url' => $doc['cyu_training_url'],
      'field_doc_type' => $doc['doc_type'][0],
      'field_family' => $tags['family'],
      'field_language' => $tags['language'],
      'field_product' => $tags['product'],
      'field_product_page_url' => $doc['product_page_url'],
      'field_product_tags' => $tags['product_tags'],
      'field_spec_number' => $doc['spec_number'],
      'field_spec_revision' => $doc['spec_revision'],
    ]);
    $paragraph->save();
    // Append new paragraph to node.
    $this->paragraphs[$nid][] = [
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ];
    $page->{$this->fileField} = $this->paragraphs[$nid];
    // Return new file id.
    return [
      'spec_number' => $doc['spec_number'],
      'file_id' => $file->id(),
    ];
  }

  /**
   * Method to update paragraph.
   *
   * @param object $page
   *   Node object.
   * @param array $doc
   *   Document details.
   */
  private function updateParagraph(&$page, $doc) {
    // Get old file id.
    $old_file_id = $doc['file_id'];
    // Load paragraph.
    $paragraph = Paragraph::load($this->doc_paragraph_id[$old_file_id]);
    // Save new file.
    $file = file_save_data(base64_decode($doc['file_data']), 'public://' . $doc['file_name'], FILE_EXISTS_REPLACE);
    // Get all tags.
    $tags = $this->getAllTags($doc);
    // Update paragraph with new file and details.
    $paragraph->field_file = [
      "target_id" => $file->id(),
      "description" => $doc['file_description'],
    ];
    $paragraph->field_application_tags = $tags['application_tags'];
    $paragraph->field_category = $tags['category'];
    $paragraph->field_cyu_training_url = $doc['cyu_training_url'];
    $paragraph->field_doc_type = $doc['doc_type'][0];
    $paragraph->field_family = $tags['family'];
    $paragraph->field_language = $tags['language'];
    $paragraph->field_product = $tags['product'];
    $paragraph->field_product_page_url = $doc['product_page_url'];
    $paragraph->field_product_tags = $tags['product_tags'];
    $paragraph->field_spec_number = $doc['spec_number'];
    $paragraph->field_spec_revision = $doc['spec_revision'];
    $paragraph->save();
    // Delete old file.
    $this->deleteFile($old_file_id);

    // Return new file id.
    return [
      'spec_number' => $doc['spec_number'],
      'file_id' => $file->id(),
    ];
  }

  /**
   * Method to delete paragraph.
   *
   * @param object $page
   *   Node object.
   * @param array $doc
   *   Document details.
   */
  private function deleteParagraph(&$page, $doc) {
    $nid = $page->id();
    $fid = $doc['file_id'];
    // Delete the paragraph.
    $paragraph = Paragraph::load($this->doc_paragraph_id[$fid]);
    if (empty($this->paragraphs[$nid])) {
      return [
        'spec_number' => $doc['spec_number'],
        'error' => 'No file found associated with node for the given file id ' . $fid . '.',
      ];
    }
    $paragraph->delete();
    // Remove the deleted paragraph from node.
    foreach ($this->paragraphs[$nid] as $key => $paragraph) {
      if ($paragraph['target_id'] == $this->doc_paragraph_id[$fid]) {
        unset($this->paragraphs[$nid][$key]);
      }
    }
    // Update the node paragraph list.
    $page->{$this->fileField} = $this->paragraphs[$nid];
    // Delete the file.
    $this->deleteFile($doc['file_id']);

    // Return new file id.
    return [
      'spec_number' => $doc['spec_number'],
      'file_id' => '',
    ];
  }

  /**
   * Utility: find term by name and vid.
   *
   * @param string $name
   *   Term name.
   * @param int $vid
   *   Term vid.
   *
   * @return int
   *   Term id or 0 if none.
   */
  private function getTidByName($name = NULL, $vid = NULL) {
    $properties = [];
    if (!empty($name)) {
      $properties['name'] = $name;
    }
    if (!empty($vid)) {
      $properties['vid'] = $vid;
    }
    $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($properties);
    $term = reset($terms);

    return !empty($term) ? $term->id() : 0;
  }

  /**
   * Method to get tag ids.
   *
   * @param array $tags
   *   Tag names.
   *
   * @return array
   *   Tag ids.
   */
  private function getTagIds($tags) {
    $tag_ids = [];
    foreach ($tags as $tag_name) {
      $tag_id = $this->getTidByName($tag_name);
      if ($tag_id) {
        $tag_ids[] = ['target_id' => $tag_id];
      }
    }

    return $tag_ids;
  }

  /**
   * Method to delete file.
   *
   * @param int $fid
   *   File id.
   */
  private function deleteFile($fid) {
    $file = File::load($fid);
    FileUsageBase::delete($file);
  }

  /**
   * Method to get all tags associated with paragraph.
   *
   * @param array $doc
   *   Document detail.
   *
   * @return array
   *   Paragraph tags.
   */
  private function getAllTags($doc) {
    $tags['application_tags'] = $this->getTagIds($doc['application_tags']);
    $category = $doc['business_unit'];
    if (isset($doc['division']) && !empty($doc['division'])) {
      $category .= ' - ' . $doc['division'];
    }
    $tags['category'] = $this->getTagIds([$category]);
    $tags['family'] = $this->getTagIds($doc['family']);
    $tags['language'] = $this->getTagIds([$doc['language']]);
    $tags['product'] = $this->getTagIds($doc['product']);
    $tags['product_tags'] = $this->getTagIds($doc['product_tags']);

    return $tags;
  }

}
