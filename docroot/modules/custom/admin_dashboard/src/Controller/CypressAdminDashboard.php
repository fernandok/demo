<?php

namespace Drupal\admin_dashboard\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class CypressAdminDashboard.
 *
 * @package Drupal\admin_dashboard\Controller
 */
class CypressAdminDashboard extends ControllerBase {

	/**
	 * @var
	 */
	protected $config;

  /**
   * Showconfig.
   *
   * @return string
   *   Return Hello string.
   */
  public function showConfig() {
		$db = \Drupal::database();
		$query = $db->select('config', 'c')
			->fields('c', array('data'))
			->condition('c.name', 'admin_dashboard.cypress_dashboard_config.%', 'LIKE');
		$results = $query->execute()->fetchAll();
		$configuration = array();
		foreach ($results as $result) {
			$data = unserialize($result->data);
			$configuration[$data['id']]['label'] = $data['label'];
			$configuration[$data['id']]['url_path'] = $data['url_path'];
		}
    return [
      '#theme' => 'cypress_admin_dashboard',
      '#configuration' => $configuration,
    ];
  }

}
