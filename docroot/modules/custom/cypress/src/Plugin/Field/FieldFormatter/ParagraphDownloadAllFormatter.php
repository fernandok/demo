<?php

namespace Drupal\cypress\Plugin\Field\FieldFormatter;

use Drupal\file\Plugin\Field\FieldFormatter\TableFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;

/**
 * Plugin implementation of the 'file_download_all' formatter.
 *
 * @FieldFormatter(
 *   id = "paragraph_file_download_all",
 *   label = @Translation("Paragraph with download all file link"),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class ParagraphDownloadAllFormatter extends TableFormatter {

  /**
   * {@inheritdoc}
   */
  protected function needsEntityLoad(EntityReferenceItem $item) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $akamai_elements = [];

    if ($paragraphs = $this->getEntitiesToView($items, $langcode)) {
      $header = [
        t('Category'),
        t('Title'),
        t('Language'),
        t('File size'),
        t('Last updated')
      ];
      $rows = [];
      foreach ($paragraphs as $delta => $paragraph) {
        $is_akamai = $paragraph->get('field_file_type')->get(0)->getValue()['value'];
        if ($is_akamai) {
          $akamai_elements[] = [
            'akamai_uri' => $paragraph->get('field_akamai_uri')->get(0)->getValue()['value'],
            'akamai_description' =>  $paragraph->get('field_akamai_description')->get(0)->getValue()['value'],
          ];
          continue;
        }
        if(!empty($paragraph->get('field_category'))
         && !empty($paragraph->get('field_category')->get(0))) {
          $category_tid = $paragraph->get('field_category')->get(0)->getValue()['target_id'];
          $category = \Drupal\taxonomy\Entity\Term::load($category_tid)->get('name')->value;
          $file_obj =$paragraph->get('field_file')->get(0)->getValue();
          $file_id = $file_obj['target_id'];
          $file =  \Drupal\file\Entity\File::load($file_id);
          $description = $file_obj['description'];
          $language_tid = $paragraph->get('field_language')->get(0)->getValue()['target_id'];
          $language = \Drupal\taxonomy\Entity\Term::load($language_tid)->get('name')->value;
          $last_updated = $file->get('changed')->get(0)->getValue()['value'];
          $rows[] = [
            ['data' => $category],
            [
              'data' => [
                '#theme' => 'file_link',
                '#file' => $file,
                '#description' => $description,
                '#cache' => [
                  'tags' => $file->getCacheTags(),
                ],
              ],
            ],
            ['data' => $language],
            ['data' => format_size($file->getSize())],
            ['data' => date('Y/m/d', $last_updated)]
          ];
        }
      }

      $elements[0] = [];
      if (!empty($rows)) {
        $elements[0] = [
          '#theme' => 'table__file_formatter_table',
          '#header' => $header,
          '#rows' => $rows,
        ];
      }
    }
    // Download all paragraph files.
    $field_name = $items->getName();
    $parent_node_id = $items->getParent()->get('nid')->getValue()[0]['value'];
    $node_label = $items->getParent()->get('title')->getValue()[0]['value'];
    $url = Url::fromUserInput('/download_all_documents/' . $parent_node_id . '/' . $field_name);
    // $download_all_files_link = Link::fromTextAndUrl('Download All Documents', $url)->toRenderable();
    // $download_all_files_link['#attributes']['class'] = ['download-all-files'];
    if (!empty($rows)) {
      $elements[]['download_all_documents'] = [
        '#theme' => 'cypress_download_all_docs',
        '#label' => $node_label,
        '#link' => $url,
      ];
    }
    // Akamai files.
    // foreach ($akamai_elements as $akamai_element) {
    //   $elements[] = [
    //     '#theme' => 'cypress_akamai_file_download',
    //     '#uri' => $akamai_element['akamai_uri'],
    //     '#description' => $akamai_element['akamai_description'],
    //   ];
    // }

    return $elements;
  }

}
