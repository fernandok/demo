<?php

namespace Drupal\store_mysamples\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\webform\Entity\WebformSubmission;

/**
 * Class PromocodeStatus.
 *
 * @package Drupal\store_mysampes\Controller
 */
class PromocodeStatus extends ControllerBase {

  /**
   * Display List of Promocodes with Approvers and related Status.
   *
   * @return string
   *   Return array of values.
   */
  public function content() {

    $header = [
     // t('Submission ID'),
      t('Part Number'),
      t('Approver'),
      t('Promocode'),
      t('Status'),
      t('Approver Mail'),
      t('Requester Mail'),
      t('Created'),
      t('Completed'),
      t('Changed')
    ];

    $query =  \Drupal::database()->select('webform_submission', 'ws');
    $query->fields('ws', ['sid']);
    $query->condition('webform_id' , 'promocode', '=');
    $results = $query->execute()->fetchAll();

    $options = [];
    foreach($results as $result) {
      $submission = WebformSubmission::load($result->sid);
      // To get the promocode created time.
      $created_date = $submission->getCreatedTime();
      $promocode_created_date = date('Y-m-d H:i:s', $created_date);

      // To get the promocode completed time.
      $completed_date = $submission->getCompletedTime();
      $promocode_completed_date = date('Y-m-d H:i:s', $completed_date);

      // To get the promocode changed time.
      $change_date = $submission->getChangedTime();
      $promocode_changed_date = date('Y-m-d H:i:s', $change_date);
      $options[$result->sid] = [
       // 'sid' => $result->sid,
        'part_number' => $submission->getData()['part_number'],
        'approver' => $submission->getData()['approver'],
        'promocode' => $submission->getData()['promo_code'],
        'promocode_status' => $submission->getData()['promocode_status'],
        'approver_mail' => $submission->getData()['approver_mail'],
        'requester_mail' => $submission->getData()['requester_mail'],
        'created_date' => $promocode_created_date,
        'completed_date' => $promocode_completed_date,
        'changed_date' => $promocode_changed_date,
      ];
    }
//    return [
//      '#type' => 'markup',
//      '#markup' => $this->t('Implement method: hello')
//    ];
    $form['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $options,
      '#empty' => $this->t('No Promocode found'),
    ];

    return $form;
  }

}
