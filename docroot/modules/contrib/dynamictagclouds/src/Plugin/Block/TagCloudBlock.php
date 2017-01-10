<?php

namespace Drupal\dynamictagclouds\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\taxonomy\TermStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Provides a 'TagCloudBlock' block.
 *
 * @Block(
 *  id = "tag_cloud_block",
 *  admin_label = @Translation("Tag cloud block"),
 * )
 */
class TagCloudBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected $termstorage;
  protected $tokenservice;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, TermStorageInterface $term_storage, $token_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->termstorage = $term_storage;
    $this->tokenservice = $token_service;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition){
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')->getStorage("taxonomy_term"),
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // Get the configurations.
    $config = $this->getConfiguration();

    $vocabularies = Vocabulary::loadMultiple();
    $vocabulary_options = [];
    foreach ($vocabularies as $vocabulary) {
      $vid = $vocabulary->get('vid');
      $name = $vocabulary->get('name');
      $vocabulary_options[$vid] = $name;
    }

    $form['vocabularies'] = [
      '#type' => 'checkboxes',
      '#options' => $vocabulary_options,
      '#title' => t('Vocabularies'),
      '#required' => TRUE,
      '#default_value' => $config['vocabularies'],
    ];

    $styles = explode(',', constant('TAG_CLOUD_STYLES'));
    $form['style'] = [
      '#type' => 'radios',
      '#title' => t('Style'),
      '#options' => $styles,
      '#default_value' => isset($config['style']) ? $config['style'] : 0,
    ];

    $form['redirect_url'] = [
      '#type' => 'textfield',
      '#title' => t('Redirect url'),
      '#required' => FALSE,
      '#default_value' => isset($config['redirect_url']) ? $config['redirect_url'] : '/taxonomy/term/',
    ];

    $token_options = [
      'global_types' => FALSE,
      'recursion_limit' => 1,
    ];

    $form['token'] = \Drupal::service('token.tree_builder')->buildAllRenderable(['term'], $token_options);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $redirect_url = $form_state->getValue('redirect_url');
    if (UrlHelper::isExternal($redirect_url)) {
      $form_state->setErrorByName('redirect_url', t('External link is not allowed for redirect url.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('vocabularies', $form_state->getValue('vocabularies'));
    $this->setConfigurationValue('style', $form_state->getValue('style'));
    $this->setConfigurationValue('redirect_url', $form_state->getValue('redirect_url'));
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $selected_bu = \Drupal::request()->get('bu');
    $selected_div = \Drupal::request()->get('division');

    $vocabularies_selected = $config['vocabularies'];
    $terms = [];
    foreach ($vocabularies_selected as $vid) {
      $vocabulary_terms = $this->termstorage->loadTree($vid);
      $connecting_string = \Drupal::request()->getPathInfo() . '?';
      switch ($vid) {
        case 'bu':
          if (!empty($selected_div) && empty($selected_bu)) {
            $connecting_string = \Drupal::request()->getUri() . '&';
          }
          break;

        case 'division':
          if (!empty($selected_bu) && empty($selected_div)) {
            $connecting_string = \Drupal::request()->getUri() . '&';
          }
          break;
      }
      $url = $connecting_string . $vid . '=';
      foreach ($vocabulary_terms as $term) {
        $term = $this->termstorage->load($term->tid);
        $tid = $term->id();
        $term_url = $url . $tid;
        $terms[$tid] = [
          'name' => $term->getName(),
          'url' => $term_url,
        ];
      }
    }

    $style = explode(',', constant('TAG_CLOUD_STYLES'))[$config['style']];

    $build = [];
    $build['#cache'] = ['max-age' => 0];
    $build['tag_cloud_block'] = [
      '#theme' => 'default_tag_clouds',
      '#tags' => $terms,
      '#attached' => array(
        'library' =>  array(
          'dynamictagclouds/' . $style . '_tag_cloud'
        ),
      ),
    ];

    return $build;
  }

}
