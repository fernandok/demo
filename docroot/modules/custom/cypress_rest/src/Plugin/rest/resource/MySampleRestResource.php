<?php

namespace Drupal\cypress_rest\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;
use \Drupal\node\Entity\Node;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "my_sample_rest_resource",
 *   label = @Translation("My sample rest resource"),
 *   uri_paths = {
 *     "canonical" = "/api/my-sample",
 *      "https://www.drupal.org/link-relations/create" = "//api/my-sample"
 *   }
 * )
 */
class MySampleRestResource extends ResourceBase {

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
    if (empty($data)) {
      return new ResourceResponse(['error' => 'Empty My sample data cannot be processed.']);
    }

    // Process ECN data.
    $response = $this->createMySample($data);

    return new ResourceResponse($response);
  }

  /**
   * Method to create my sample.
   *
   * @param array $data
   *   Array of my samples.
   *
   * @return array
   *   Array of created my samples' id.
   */
  private function createMySample($data) {
    $response = [];
    foreach ($data as $sample) {
      if (!isset($sample['title']) || empty($sample['title'])) {
        $response[] = ['error' => 'Title is required field.'];
        continue;
      }
      $sample['type'] = 'my_samples';
      $node = Node::create($sample);
      $node->save();
      $response[] = ['node' => $node->id()];
    }

    return $response;
  }

}
