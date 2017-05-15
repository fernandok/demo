<?php

namespace Drupal\product_rest\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;
use Drupal\user\Entity\User;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "user_creation_rest_resource",
 *   label = @Translation("User creation rest"),
 *   uri_paths = {
 *     "canonical" = "/api/user-create",
 *     "https://www.drupal.org/link-relations/create" = "/api/user-create"
 *   }
 * )
 */
class UserCreationRestResource extends ResourceBase {

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
      $container->get('logger.factory')->get('cypress'),
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

    // to check whether uid exist in store or not.
    $query = \Drupal::database()->select('users_field_data','usr');
    $query->fields('usr',['mail','uid']);
    $query->condition('usr.name',$data['name']);
    $results = $query->execute()->fetchAll();

    foreach($results as $result) {
      $new_user = $result->mail;
      $new_user_id = $result->uid;
    }

    // get the data related to user from cypresscom.
    $user_id = $data['uid'];
    $user_name = $data['name'];
    $user_mail = $data['mail'];
    $status = $data['status'];
    $legacy_id = $data['legacy_id'];

    // create new user.
    if (empty($new_user)) {
      $user = User::create([
        'name' => $user_name,
        'mail' => $user_mail,
        'uid' => $user_id,
        'status' => $status,
        'field_legacy_uid' => $legacy_id
      ]);
      $user->save();
    }

    // update the data of existing user.
    if(!empty($new_user)) {
      $user = User::load($new_user_id);
      $user->name = $user_name;
      $user->mail = $user_mail;
      $user->status = $status;
      $user->field_legacy_uid = $legacy_id;
      $user->save();
    }
    
    return new ResourceResponse($user);
  }

}
