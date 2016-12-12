<?php

namespace Drupal\extranet_clone_content\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Path\AliasManager;
use Drupal\Core\Path\CurrentPathStack;

/**
 * Provides a 'ItExtranetPagesMenuBlock' block.
 *
 * @Block(
 *  id = "it_extranet_pages_menu_block",
 *  admin_label = @Translation("IT Extranet Pages Menu Block"),
 * )
 */
class ItExtranetPagesMenuBlock extends BlockBase implements ContainerFactoryPluginInterface {

	/**
	 * A pathAlias instance.
	 *
	 * @var \Drupal\Core\Path\AliasManager
	 */
	protected $pathAlias;

	/**
	 * A pathCurrent instance.
	 *
	 * @var \Drupal\Core\Path\CurrentPathStack
	 */
	protected $pathCurrent;

	/**
	 * Constructs a Drupal\rest\Plugin\ResourceBase object.
	 *
	 * @param array $configuration
	 *   A configuration array containing information about the plugin instance.
	 * @param string $plugin_id
	 *   The plugin_id for the plugin instance.
	 * @param mixed $plugin_definition
	 *   The plugin implementation definition.
	 * @param \Drupal\Core\Path\AliasManager $pathAlias
	 *   A pathAlias instance.
	 * @param \Drupal\Core\Path\CurrentPathStack $pathAlias
	 *   A pathCurrent instance.
	 */
	public function __construct(array $configuration, $plugin_id, $plugin_definition, AliasManager $pathAlias, CurrentPathStack $pathCurrent) {
		parent::__construct($configuration, $plugin_id, $plugin_definition);
		$this->pathAlias = $pathAlias;
		$this->pathCurrent = $pathCurrent;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
		return new static(
			$configuration,
			$plugin_id,
			$plugin_definition,
			$container->get('path.alias_manager'),
			$container->get('path.current')
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function build() {
		// Get other Extranet Pages Attactched to the current Extranet content.
		$current_path = $this->pathCurrent->getPath();
		$current_path = \Drupal::service('path.alias_manager')->getPathByAlias($current_path);
		$path_args = explode('/', $current_path);
		// Get the node id from title.
		$db = \Drupal::database();
		$query = $db->select('node', 'n');
		$query->condition('n.nid', $path_args[2]);
		$query->addField('n', 'type');
		$node_type = $query->execute()->fetchField();
		$e_links = $ep_links = array();

		if ($node_type == 'it_extranet_pages') {
			$query = $db->select('node__field_it_extranet_pages', 'fiep');
			$query->condition('fiep.field_it_extranet_pages_target_id', $path_args[2]);
			$query->addField('fiep', 'entity_id');
			$extranet_id = $query->execute()->fetchField();

			$extranet_node_id = $extranet_id;
		}
		// If it_extranet, get extranet_pages
		else {
			if ($node_type == 'it_extranet') {
				$extranet_node_id = $path_args[2];
			}
		}
		
		$e_links[$extranet_node_id] = array(
			'title' => Node::load($extranet_node_id)->getTitle(),
			'link' => $this->pathAlias->getAliasByPath('/node/' . $extranet_node_id),
		);

		// Fetch Children.
		$extranet_pages = extranet_clone_content_fetch_extranet_pages($extranet_node_id);
		foreach ($extranet_pages as $page_id => $page_title) {
			$ep_links[$page_id] = array(
				'title' => $page_title,
				'link' => $this->pathAlias->getAliasByPath('/node/' . $page_id),
			);
		}
		$build = [];
		$build['it_extranet_pages_menu_block'] = [
			'#theme' => 'extranets_pages_menu_block',
			'#e_links' => $e_links,
			'#ep_links' => $ep_links,
		];
		$build['#cache'] = ['max-age' => 0];
		return $build;
	}
}