<?php

namespace Drupal\cypress\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Component\Serialization\Json;

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
    $user_time_zone = $this->getUserTimezone();
    $date_time_zone = new \DateTimeZone($user_time_zone);
    $date_time = new \DateTime('NOW', $date_time_zone);
    $build['work_week_and_stock'] = [
      '#theme' => 'cypress_ww_cy',
      '#datetime' => $date_time->format("l d-F-Y g:i A"),
      '#ww' => date('y') . date('W'),
      '#cy_price' => $price,
      '#cy_change'  => $change,
    ];
    $build['#cache'] = ['max-age' => 0];

    return $build;
  }


  /**
   * Method to get user timezone..
   */
  private function getUserTimezone() {
    // Only do a check when the session variable is not set.
    if (!isset($_SESSION['user_time_zone'])) {
      $geoip_data = $this->getGeoipData();
      if ($geoip_data['time_zone']) {
        $_SESSION['user_time_zone'] = $geoip_data['time_zone'];
      }
      else {
        $_SESSION['user_time_zone'] = date_default_timezone_get();
      }
    }
    return $_SESSION['user_time_zone'];
  }

  /**
   * Get user geo ip data.
   */
  private function getGeoipData() {
    $user_ip = $this->getUserIp();
    $url = 'http://freegeoip.net/json/' . $user_ip;
    $request = \Drupal::httpClient()->get($url);
    $response = $request->getBody();
    $response_content = $response->getContents();

    $data = Json::decode($response_content);
    // If cannot determine the country code.
    if ($data['type'] == 'error') {
      return FALSE;
    }
    return $data;
  }

  /**
   * Get user IP.
   */
  private function getUserIp() {
    return \Drupal::request()->getClientIp();
  }

}
