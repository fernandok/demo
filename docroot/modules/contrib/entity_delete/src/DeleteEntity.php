<?php

namespace Drupal\entity_delete;

class DeleteEntity {

  public static function deleteEntity($delete_ids, $count, $total_count, $entity, $bundle, &$context){
    $context['message'] = t("Now processing :current_row of :highest_row", array(':current_row' => $count, ":highest_row" => $total_count));
    entity_delete_multiple($entity, $delete_ids);
    $context['results']['entity'] = $entity;
    $context['results']['bundle'] = $bundle;
    $context['results']['count'] = $total_count;
  }
  function deleteEntityFinishedCallback($success, $results, $operations) {
    if ($success) {
      if ($results['bundle'] == 'all') {
        $message = t('Successfully deleted @num @entity(s).', array('@num' => $results['count'], '@entity' => $results['entity']));
      }
      else{
        $message = t('Successfully deleted @num @entity(s) with @bundle.', array('@num' => $results['count'], 
          '@entity' => $results['entity'], '@bundle' => $results['bundle']));
      }
    }
    else {
      $message = t('Finished with an error.');
    }
    drupal_set_message($message);
  }
}
