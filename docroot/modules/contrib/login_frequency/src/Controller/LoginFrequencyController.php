<?php

namespace Drupal\login_frequency\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Controller routines for Cypress login tracker routes.
 */
class LoginFrequencyController extends ControllerBase {

  /**
   * Displays a report of user logins.
   *
   * @return array
   *   A render array.
   */
  public function report() {
    $header = array(
      array('data' => t('Username'), 'field' => 'ufd.name'),
      array('data' => t('E-mail Id'), 'field' => 'ufd.mail'),
      array('data' => t('Frequency'), 'field' => 'frequency', 'sort' => 'desc'),
//      array('data' => t('Action')),
    );

    $query = db_select('login_frequency', 'lf')
        ->extend('Drupal\Core\Database\Query\TableSortExtender')
        ->extend('Drupal\Core\Database\Query\PagerSelectExtender');

    $query->join('users', 'u', 'lf.uid = u.uid');
    $query->join('users_field_data', 'ufd', 'u.uid = ufd.uid');
    $query->addExpression('count(lf.uid)', 'frequency');
    $query->groupBy('lf.uid, name, mail');

    $result = $query
        ->fields('u', array('uid'))
        ->fields('ufd', array('name', 'mail'))
        ->orderByHeader($header)
        ->limit(50)
        ->execute()
        ->fetchAll();

    return $this->generateReportTable($result, $header);
  }

  /**
   * Renders login histories as a table.
   *
   * @param array $history
   *   A list of login history objects to output.
   * @param array $header
   *   An array containing table header data.
   *
   * @return array
   *   A table render array.
   */
  function generateReportTable(array $history, array $header) {

    $rows = array();
    foreach ($history as $entry) {
//      $url = Url::fromUserInput('#');
      $rows[] = array(
        $entry->name,
        $entry->mail,
        $entry->frequency,
//        Link::fromTextAndUrl('View full history', $url),
      );
    }
    $output['history'] = array(
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => t('No login history available.'),
    );
    $output['pager'] = array(
      '#type' => 'pager',
    );

    return $output;
  }

  /**
   * Checks access for the user login report.
   */
  public function checkUserReportAccess() {
    $user_roles = \Drupal::currentUser()->getRoles();
    return AccessResult::allowedIf(in_array('administrator', $user_roles));
  }

}
