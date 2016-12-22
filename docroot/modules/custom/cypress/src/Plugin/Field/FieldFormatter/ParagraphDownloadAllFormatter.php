<?php

namespace Drupal\cypress\Plugin\Field\FieldFormatter;

use Drupal\file\Plugin\Field\FieldFormatter\TableFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;
use Drupal\taxonomy\Entity\Term;
use Drupal\file\Entity\File;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Component\Utility\Bytes;

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
        t('BU'),
        t('DIV'),
        t('Title'),
        t('Revsion'),
        t('Language'),
        t('File size'),
        t('Last updated'),
      ];
      $rows = [];
      foreach ($paragraphs as $delta => $paragraph) {
        $is_akamai = $paragraph->get('field_file_type')->get(0)->getValue()['value'];
        if ($is_akamai) {
          $akamai_elements[] = [
            'akamai_uri' => $paragraph->get('field_akamai_uri')->get(0)->getValue()['value'],
            'akamai_description' => $paragraph->get('field_akamai_description')->get(0)->getValue()['value'],
          ];
          continue;
        }

        $bu = '';
        $division = '';
        if (!empty($paragraph->get('field_bu'))
            && !empty($paragraph->get('field_bu')->get(0))) {
          $bu_tid = $paragraph->get('field_bu')->get(0)->getValue()['target_id'];
          if (!empty(Term::load($bu_tid))) {
            $bu = Term::load($bu_tid)->get('name')->value;
          }
        }

        if (!empty($paragraph->get('field_div'))
            && !empty($paragraph->get('field_div')->get(0))) {
          $div_tid = $paragraph->get('field_div')->get(0)->getValue()['target_id'];
          if (!empty(Term::load($div_tid))) {
            $division = Term::load($div_tid)->get('name')->value;
          }
        }

        if (empty($paragraph->get('field_file'))
            || empty($paragraph->get('field_file')->get(0))) {
          continue;
        }
        $file_obj = $paragraph->get('field_file')->get(0)->getValue();
        $file_id = $file_obj['target_id'];
        $file = File::load($file_id);
        $description = $file_obj['description'];
        $language = '';
        if (!empty($paragraph->get('field_language')->get(0))) {
          $language_tid = $paragraph->get('field_language')->get(0)->getValue()['target_id'];
          if (!empty(Term::load($language_tid))) {
            $language = Term::load($language_tid)->get('name')->value;
          }
        }
        $last_updated = $file->get('changed')->get(0)->getValue()['value'];
        $file_size = $this->formatSizeInMb($file->getSize());
        if(!empty($paragraph->get('field_spec_revision'))) {
          $revision = $paragraph->get('field_spec_revision')
            ->getValue()[0]['value'];
        }
        $rows[] = [
          ['data' => $bu],
          ['data' => $division],
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
          ['data' => $revision],
          ['data' => $language],
          // ['data' => format_size($file->getSize())],
          ['data' => $file_size],
          ['data' => date('Y/m/d', $last_updated)],
        ];
      }

      // Download all paragraph files.
      $field_name = $items->getName();
      $parent_node_id = $items->getParent()->get('nid')->getValue()[0]['value'];
      $node_label = $items->getParent()->get('title')->getValue()[0]['value'];
      $url = Url::fromUserInput('/download_all_documents/' . $parent_node_id . '/' . $field_name);
      if (!empty($rows)) {
        $download_all_docs = [
          '#theme' => 'cypress_download_all_docs',
          '#label' => $node_label,
          '#link' => $url,
        ];
        $elements[0]['download_all_documents_top'] = $download_all_docs;
        $elements[1]['static_download_all_documents'] = [
          '#markup' => '<div class="static-download-all-files-wrapper">
              <a href="' . $url->toString() . '" title="Download all files">
                <div class="download-all-icon"></div>
              </a>
            </div>',
        ];
        $elements[2] = [
          '#theme' => 'table__file_formatter_table',
          '#header' => $header,
          '#rows' => $rows,
        ];
        $elements[3]['download_all_documents_bottom'] = $download_all_docs;
      }
    }
    return $elements;
  }

  /**
   * To get the file size.
   */
  private function formatSizeInMb($size) {
    return round(($size / pow(Bytes::KILOBYTE, 2)), 2) . ' MB';
  }

}
