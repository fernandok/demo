<?php

namespace Drupal\product_rest\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_price\Price;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "product_rest_resource",
 *   label = @Translation("Product rest resource"),
 *   serialization_class = "Drupal\commerce_product\Entity\Product",
 *   uri_paths = {
 *     "canonical" = "/api/product/{commerce_product_type}",
 *     "https://www.drupal.org/link-relations/create" = "/api/product/{commerce_product_type}"
 *   }
 * )
 */
class ProductRestResource extends ResourceBase {
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
      $container->get('logger.factory')->get('product_rest'),
      $container->get('current_user')
    );
  }
  /**
   * Responds to POST requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @param $node_type
   * @param $data
   * @return \Drupal\rest\ResourceResponse Throws exception expected.
   * Throws exception expected.
   */
  public function post($commerce_product_type, $data) {
    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    //For Product Taxonomy
    $related_product = $data->related_product;
    if($related_product != '') {
      foreach($related_product as $products) {
        $term_name = $products['term_name'];
        $allparents = $products['parents'];
        $vid = 'products';
        if(is_array($allparents)) {
          $pid = 0;
          foreach($allparents as $parents) {
            $terms = $this->checkTid($parents, $vid);
            if($terms != 0) {
              $pid = $terms[0]['target_id'];
            }
            else {
              if($pid != 0) {
                $pid = $pid;
              }else {
                $pid = 0;
              }
            }
            $tags = $this->getTagIds($parents, $vid, $pid);
            $pid = $tags[0]['target_id'];
          }
        }
        else {
          $tags = $this->getTagIds($term_name, $vid, $pid = 0);
        }
        $all_tag_id = $this->checkTid($term_name, $vid);
        $product_tag_id[] = $tags[0]['target_id'];
      }
    } else {
      $product_tag_id = '';
    }

    //Fot Applications Taxonomy
    $related_applications = $data->related_applications;
    if($related_applications != '') {
      foreach($related_applications as $applications) {
        $term_name = $applications['term_name'];
        $allparents = $applications['parents'];
        $vid = 'applications';
        if(is_array($allparents)) {
          $pid = 0;
          foreach($allparents as $parents) {
            $terms = $this->checkTid($parents, $vid);
            if($terms != 0) {
              $pid = $terms[0]['target_id'];
            }
            else {
              if($pid != 0) {
                $pid = $pid;
              }else {
                $pid = 0;
              }
            }
            $tags = $this->getTagIds($parents, $vid, $pid);
            $pid = $tags[0]['target_id'];
          }
        }
        else {
          $tags = $this->getTagIds($term_name, $vid, $pid = 0);
        }
        $all_tag_id = $this->checkTid($term_name, $vid);
        $applications_tag_id[] = $tags[0]['target_id'];
      }
    } else {
      $applications_tag_id = '';
    }

    //For Trainings Taxonomy
    $related_trainings = $data->related_trainings;
    if($related_trainings != '') {
      foreach($related_trainings as $trainings) {
        $term_name = $trainings['term_name'];
        $allparents = $trainings['parents'];
        $vid = 'training';
        if(is_array($allparents)) {
          $pid = 0;
          foreach($allparents as $parents) {
            $terms = $this->checkTid($parents, $vid);
            if($terms != 0) {
              $pid = $terms[0]['target_id'];
            }
            else {
              if($pid != 0) {
                $pid = $pid;
              }else {
                $pid = 0;
              }
            }
            $tags = $this->getTagIds($parents, $vid, $pid);
            $pid = $tags[0]['target_id'];
          }
        }
        else {
          $tags = $this->getTagIds($term_name, $vid, $pid = 0);
        }
        $all_tag_id = $this->checkTid($term_name, $vid);
        $trainings_tag_id[] = $tags[0]['target_id'];
      }
    } else {
      $trainings_tag_id = '';
    }

    //For Document Type
    $document_type = $data->document_type;
    if(!empty($document_type)) {
      $tags = $this->getTagIds($document_type, 'documentation', $pid = 0);
      $document_type_id = $tags[0]['target_id'];
    }else {
      $document_type_id = '';
    }

    //For Package Family
    $package_family = $data->package_family;
    if(!empty($package_family)) {
      $tags = $this->getTagIds($package_family, 'package_family', $pid = 0);
      $package_family_id = $tags[0]['target_id'];
    }else {
      $package_family_id = '';
    }

    //For Spec Number
    $spec_number = $data->spec_number;
    if(!empty($spec_number)) {
      $tags = $this->getTagIds($spec_number, 'spec_numbers', $pid = 0);
      $spec_number_id = $tags[0]['target_id'];
    }else{
      $spec_number_id = '';
    }

    //For Related Solutions
    $related_solutions = $data->related_solutions;
    if($related_solutions != '') {
      foreach($related_solutions as $solutions) {
        $tags = $this->getTagIds($solutions, 'solutions', $pid = 0);
        $related_solutions_id[] = $tags[0]['target_id'];
      }
    } else {
      $related_solutions_id = '';
    }

    //For Related Persona
    $related_persona = $data->related_persona;
    if($related_persona != '') {
      foreach($related_persona as $persona) {
        $tags = $this->getTagIds($persona, 'persona', $pid = 0);
        $related_persona_id[] = $tags[0]['target_id'];
      }
    } else {
      $related_persona_id = '';
    }

    //For Related Content Section
    $related_content_section = $data->related_content_section;
    if($related_content_section != '') {
      foreach($related_content_section as $content_section) {
        $tags = $this->getTagIds($content_section, 'content_section', $pid = 0);
        $related_content_section_id[] = $tags[0]['target_id'];
      }
    } else {
      $related_content_section_id = '';
    }

    //For Related Content keywords
    $related_content_keywords = $data->related_content_keywords;
    if($related_content_keywords != '') {
      foreach($related_content_keywords as $content_keywords) {
        $tags = $this->getTagIds($content_keywords, 'content_keywords', $pid = 0);
        $related_content_keywords_id[] = $tags[0]['target_id'];
      }
    } else {
      $related_content_keywords_id = '';
    }

    //For Search Keywords
    $search_keywords_all = $data->search_keywords;
    if($search_keywords_all != '') {
      foreach($search_keywords_all as $search_keywords_val) {
        $search_keywords[] = $search_keywords_val;
      }
    } else {
      $search_keywords = '';
    }

    //For Price
    if($data->price != '') {
      $price = $data->price;
    } else {
      $price = 0;
    }

    $product = Product::load($data->node_id);
    if($product == '' && !isset($data->operations)) {

      //Price Variation
      $product_variation = ProductVariation::create(
        array(
          'type' => 'default',
          'price' => new Price($price, 'USD'),
        )
      );
      $product_variation->save();


      $product = Product::create(
        array(
          'type' => 'default',
          'product_id' => $data->node_id,
          'title' => $data->title,
          'body' => [
            //'summary' => '',
            'value' => $data->body->value,
            'format' => 'full_html',
          ],
          'field_version' => $data->version,
          'field_document_source' => $data->document_source,
          'field_alternative_addtocart_ur' => $data->addtocart_url,
          'field_ecn_body' => $data->ecn_body,
          'field_document_code' => $data->document_code,
          'field_document_type' => $data->document_type,
          'variations' => [$product_variation],
          'field_image' => $data->image,
          'field_related_products' => $product_tag_id,
          'field_related_applications' => $applications_tag_id,
          'field_related_trainings' => $trainings_tag_id,
          'field_document_type' => $document_type_id,
          'field_package_family' => $package_family_id,
          'field_spec_num' => $spec_number_id,
          'field_related_solutions' => $related_solutions_id,
          'field_related_persona' => $related_persona_id,
          'field_related_content_section' => $related_content_section_id,
          'field_related_content_keywords' => $related_content_keywords_id,
          'field_files_ref' => $data->related_files,
          'field_search_keywords' => $search_keywords,
          'field_node_id' => $data->node_id
        )
      );
      $product->save();
    }
    else if ($product != '' && !isset($data->operations )) {
      //Save Product Variation
      $product_variation = $product->getVariations()[0]->id();
      $product_variation = ProductVariation::load($product_variation);
      $product_variation->type = 'default';
      $product_variation->price = new Price($price, 'USD');
      $product_variation->save();
      //Save Product
      $product->title = $data->title;
      $product->body->value = $data->body->value;
      $product->body->format = 'full_html';
      $product->field_version = $data->version;
      $product->field_document_source = $data->document_source;
      $product->field_alternative_addtocart_ur = $data->addtocart_url;
      $product->field_ecn_body = $data->ecn_body;
      $product->field_document_code = $data->document_code;
      $product->variations = [$product_variation];
      $product->field_image = $data->image;
      $product->field_related_products = $product_tag_id;
      $product->field_related_applications = $applications_tag_id;
      $product->field_related_trainings = $trainings_tag_id;
      $product->field_document_type = $document_type_id;
      $product->field_package_family = $package_family_id;
      $product->field_spec_num = $spec_number_id;
      $product->field_related_solutions = $related_solutions_id;
      $product->field_related_persona = $related_persona_id;
      $product->field_related_content_section = $related_content_section_id;
      $product->field_related_content_keywords = $related_content_keywords_id;
      $product->field_files_ref = $data->related_files;
      $product->field_search_keywords = $search_keywords;
      $product->save();
    }
    elseif($data->operations == 'delete') {
      $product->type = 'default';
      $product->delete();
    }

    return new ResourceResponse($product);
  }

  /**
   * Utility: find term by name and vid.
   *
   * @param string $name
   *   Term name.
   * @param int $vid
   *   Term vid.
   * @param int $pid
   *   Parent term vid.
   *
   * @return int
   *   Term id or 0 if none.
   */
  private function getTidByName($name = NULL, $vid = NULL, $pid) {
    $properties = [];
    if (!empty($name)) {
      $properties['name'] = $name;
    }
    if (!empty($vid)) {
      $properties['vid'] = $vid;
    }
    //for single term insertion
    $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($properties);
    $term = reset($terms);

    // Create tag if not present.
    if (empty($term)) {
      Term::create([
        'name' => $name,
        'vid' => $vid,
        'parent' => [$pid],
      ])->save();

      $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($properties);
      $term = reset($terms);
    }
    return !empty($term) ? $term->id() : 0;
  }

  /**
   * Method to get tag ids.
   *
   * @param array $tags
   *   Tag names.
   * @param string $vid
   *   Vocabulary id.
   * @param int $pid
   *   Parent term vid.
   *
   * @return array
   *   Tag ids.
   */
  private function getTagIds($tags, $vid, $pid) {
    $tag_ids = [];
    $tags = array($tags);
    foreach ($tags as $tag_name) {
      $tag_id = $this->getTidByName($tag_name, $vid, $pid);
      if ($tag_id) {
        $tag_ids[] = ['target_id' => $tag_id];
      }
    }

    return $tag_ids;
  }

  /**
   * Method to check whether the term id is already there
   *
   * @param string $name
   *   Term name.
   * @param int $vid
   *   Term vid.
   * @param int $pid
   *   Parent term vid.
   *
   * @return int
   *   Term id or 0 if none.
   */
  private function checkTid($name = NULL, $vid = NULL) {

    $properties = [];
    if (!empty($name)) {
      $properties['name'] = $name;
    }
    if (!empty($vid)) {
      $properties['vid'] = $vid;
    }
    //for single term insertion
    $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($properties);
    $term = reset($terms);
    if ($term) {
      $tag_ids[] = ['target_id' => $term];
    }else
      return !empty($term) ? $term->id() : 0;
  }

}
