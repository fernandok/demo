<?php

namespace Drupal\file_download_tracker\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\file_download_tracker\Entity\FileDownloadEntityInterface;

/**
 * Class FileDownloadEntityController.
 *
 *  Returns responses for File download entity routes.
 *
 * @package Drupal\file_download_tracker\Controller
 */
class FileDownloadEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a File download entity  revision.
   *
   * @param int $file_download_entity_revision
   *   The File download entity  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($file_download_entity_revision) {
    $file_download_entity = $this->entityManager()->getStorage('file_download_entity')->loadRevision($file_download_entity_revision);
    $view_builder = $this->entityManager()->getViewBuilder('file_download_entity');

    return $view_builder->view($file_download_entity);
  }

  /**
   * Page title callback for a File download entity  revision.
   *
   * @param int $file_download_entity_revision
   *   The File download entity  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($file_download_entity_revision) {
    $file_download_entity = $this->entityManager()->getStorage('file_download_entity')->loadRevision($file_download_entity_revision);
    return $this->t('Revision of %title from %date', array('%title' => $file_download_entity->label(), '%date' => format_date($file_download_entity->getRevisionCreationTime())));
  }

  /**
   * Generates an overview table of older revisions of a File download entity .
   *
   * @param \Drupal\file_download_tracker\Entity\FileDownloadEntityInterface $file_download_entity
   *   A File download entity  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(FileDownloadEntityInterface $file_download_entity) {
    $account = $this->currentUser();
    $langcode = $file_download_entity->language()->getId();
    $langname = $file_download_entity->language()->getName();
    $languages = $file_download_entity->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $file_download_entity_storage = $this->entityManager()->getStorage('file_download_entity');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $file_download_entity->label()]) : $this->t('Revisions for %title', ['%title' => $file_download_entity->label()]);
    $header = array($this->t('Revision'), $this->t('Operations'));

    $revert_permission = (($account->hasPermission("revert all file download entity revisions") || $account->hasPermission('administer file download entity entities')));
    $delete_permission = (($account->hasPermission("delete all file download entity revisions") || $account->hasPermission('administer file download entity entities')));

    $rows = array();

    $vids = $file_download_entity_storage->revisionIds($file_download_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\file_download_tracker\FileDownloadEntityInterface $revision */
      $revision = $file_download_entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->revision_timestamp->value, 'short');
        if ($vid != $file_download_entity->getRevisionId()) {
          $link = $this->l($date, new Url('entity.file_download_entity.revision', ['file_download_entity' => $file_download_entity->id(), 'file_download_entity_revision' => $vid]));
        }
        else {
          $link = $file_download_entity->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->revision_log_message->value, '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('file_download_entity.revision_revert_translation_confirm', ['file_download_entity' => $file_download_entity->id(), 'file_download_entity_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('file_download_entity.revision_revert_confirm', ['file_download_entity' => $file_download_entity->id(), 'file_download_entity_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('file_download_entity.revision_delete_confirm', ['file_download_entity' => $file_download_entity->id(), 'file_download_entity_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['file_download_entity_revisions_table'] = array(
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    );

    return $build;
  }

}
