<?php

namespace Drupal\cypress\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'WorkWeekAndStock' block.
 *
 * @Block(
 *  id = "work_week_and_stock",
 *  admin_label = @Translation("Work week and stock"),
 * )
 */
class WorkWeekAndStock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $yql_query = "https://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20csv%20where%20url%3D'http%3A%2F%2Fdownload.finance.yahoo.com%2Fd%2Fquotes.csv%3Fs%3DCY%26f%3Dsl1d1t1c1ohgv%26e%3D.csv'%20and%20columns%3D'symbol%2Cprice%2Cdate%2Ctime%2Cchange%2Ccol1%2Chigh%2Clow%2Ccol2'&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
    $yql_json_data = file_get_contents($yql_query);
    $yql_data = json_decode($yql_json_data);
    $price = '0.0';
    $change = '0.0';
    foreach ($yql_data as $data) {
      $price = $data->results->row->price;
      $change = $data->results->row->change;
    }
    $build = [];
    $build['work_week_and_stock'] = [
      '#theme' => 'cypress_ww_cy',
      '#ww' => date('y') . date('W'),
      '#cy_price' => $price,
      '#cy_change'  => $change,
    ];
    $build['#cache'] = ['max-age' => 0];

    return $build;
  }

}
