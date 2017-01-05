<?php

namespace Drupal\cypress\Plugin\Field\FieldFormatter;

use Drupal\file\Plugin\Field\FieldFormatter\TableFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;
use Drupal\taxonomy\Entity\Term;
use Drupal\file\Entity\File;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Component\Utility\Bytes;
use Drupal\Core\Link;

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
    // The first part of the akamai file, download manager.
    define('CY_AKAMAI_DOWNLOAD_MANAGER_URL', 'http://dlm.cypress.com.edgesuite.net/downloadmanager');
    // The first part of the akamai file, direct download.
    define('CY_AKAMAI_DIRECT_DOWNLOAD_URL', 'http://dlm.cypress.com.edgesuite.net/akdlm/downloadmanager');
    $elements = [];
    $akamai_elements = [];

    if ($paragraphs = $this->getEntitiesToView($items, $langcode)) {
      $header = [
        '',
        t('BU'),
        t('DIV'),
        t('Title'),
        t('Revsion'),
        t('Language'),
        t('File size'),
        t('Last updated'),
      ];
      $rows = [];
      $timestamp_to_highlight = strtotime("-2 week");
      foreach ($paragraphs as $delta => $paragraph) {
        $is_akamai = $paragraph->get('field_file_type')->get(0)->getValue()['value'];
        if ($is_akamai) {
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
          if (!empty($paragraph->get('field_spec_revision'))) {
            $revision = $paragraph->get('field_spec_revision')
              ->getValue()[0]['value'];
          }
          $get_akamai_url = $paragraph->get('field_akamai_url')->getValue()[0]['value'];
          $url = explode('/', ($get_akamai_url));
          $title = end($url);
          $get_url = Url::fromUri($get_akamai_url, array('attributes' => array('target' => '_blank')));
          $get_title_link = Link::fromTextAndUrl(t($title), $get_url)->toString();
          $get_direct_link = str_replace(CY_AKAMAI_DOWNLOAD_MANAGER_URL, CY_AKAMAI_DIRECT_DOWNLOAD_URL, $get_akamai_url);
          $get_driect_url = Url::fromUri($get_direct_link);
          $direct_link = Link::fromTextAndUrl(t('(DirectDownload)'), $get_driect_url)->toString();
          $akamai_elements[] = [
            ['data' => $bu],
            ['data' => $division],
            [
              'data' => [
                '#theme' => 'cypress_akamai_file_image',
                '#link' => $get_title_link,
                '#directlink' => $direct_link,
              ],
            ],
            ['data' => $revision],
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
        if (!empty($paragraph->get('field_spec_revision'))) {
          $revision = $paragraph->get('field_spec_revision')
            ->getValue()[0]['value'];
        }
        $rows[$delta]['data'] = [
          [
            'data' => [
              '#theme' => 'cypress_checkbox',
              '#name' => 'download_file_selector',
              '#value' => $file->id(),
              '#classes' => 'download_file_selector',
            ],
          ],
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
          ['data' => $file_size],
          ['data' => date('d/m/Y', $last_updated)],
        ];
        if ($timestamp_to_highlight <= $last_updated) {
          $rows[$delta]['class'] = ['highlight-latest'];
        }
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
        if ($akamai_elements) {
          $elements[4] = [
            '#theme' => 'table__file_formatter_table',
            '#header' => [
              'BU',
              'DIV',
              'Title',
              'Revision',
            ],
            '#prefix' => '<div class="akamai-title"><h4>' . 'Large Files' . '</h4></div>',
            '#rows' => $akamai_elements,
          ];
        }
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
