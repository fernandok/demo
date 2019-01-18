<?php

namespace Drupal\contentimport\Form;

use Drupal\contentimport\Controller\ContentImportController;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * Configure Content Import settings for this site.
 */
class ContentImport extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'contentimport';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'contentimport.settings',
    ];
  }

  /**
   * Content Import Form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $contentTypes = ContentImportController::getAllContentTypes();
    $selected = 0;
    $form['contentimport_contenttype'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Content Type'),
      '#options' => $contentTypes,
      '#default_value' => t('Select'),
      '#required' => TRUE,
      '#ajax' => [
        'event' => 'change',
        'callback' => '::contentImportcallback',
        'wrapper' => 'content_import_fields_change_wrapper',
        'progress' => [
          'type' => 'throbber',
          'message' => NULL,
        ],
      ],
    ];

    $form['file_upload'] = [
      '#type' => 'file',
      '#title' => $this->t('Import CSV File'),
      '#size' => 40,
      '#description' => $this->t('Select the CSV file to be imported.'),
      '#required' => FALSE,
      '#autoupload' => TRUE,
      '#upload_validators' => ['file_validate_extensions' => ['csv']],
    ];

    $form['loglink'] = [
      '#type' => 'link',
      '#title' => $this->t('Check Log..'),
      '#url' => Url::fromUri('base:sites/default/files/contentimportlog.txt'),
    ];

    $form['import_ct_markup'] = [
      '#suffix' => '<div id="content_import_fields_change_wrapper"></div>',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
      '#button_type' => 'primary',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Content Import Sample CSV Creation.
   */
  public function contentImportcallback(array &$form, FormStateInterface $form_state) {
    global $base_url;
    $ajax_response = new AjaxResponse();
    $contentType = $form_state->getValue('contentimport_contenttype');
    $fields = ContentImport::getFields($contentType);
	echo "<pre>";
	print_r($fields);
	exit;
	$fieldArray = $fields['name'];
    $contentTypeFields = 'title,';
    $contentTypeFields .= 'langcode,';
    foreach ($fieldArray as $key => $val) {
      $contentTypeFields .= $val . ',';
    }
    $contentTypeFields = substr($contentTypeFields, 0, -1);
    $result .= '</tr></table>';
    $sampleFile = $contentType . '.csv';
    $handle = fopen("sites/default/files/" . $sampleFile, "w+") or die("There is no permission to create log file. Please give permission for sites/default/file!");
    fwrite($handle, $contentTypeFields);
    $result = '<a class="button button--primary" href="' . $base_url . '/sites/default/files/' . $sampleFile . '">Click here to download Sample CSV</a>';
    $ajax_response->addCommand(new HtmlCommand('#content_import_fields_change_wrapper', $result));
    return $ajax_response;
  }

  /**
   * Content Import Form Submission.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $contentType = $form_state->getValue('contentimport_contenttype');
    ContentImport::createNode($_FILES, $contentType);
  }

  /**
   * To get all Content Type Fields.
   */
  public function getFields($contentType) {
    $fields = [];
    foreach (\Drupal::entityManager()
      ->getFieldDefinitions('node', $contentType) as $field_definition) {
      if (!empty($field_definition->getTargetBundle())) {
        $fields['name'][] = $field_definition->getName();
        $fields['type'][] = $field_definition->getType();
        $fields['setting'][] = $field_definition->getSettings();
      }
    }
    return $fields;
  }

  /**
   * To get Reference field ids.
   */
  public function getTermReference($voc, $terms) {
    $vocName = strtolower($voc);
    $vid = preg_replace('@[^a-z0-9_]+@', '_', $vocName);
    $vocabularies = Vocabulary::loadMultiple();
    /* Create Vocabulary if it is not exists */
    if (!isset($vocabularies[$vid])) {
      ContentImport::createVoc($vid, $voc);
    }
    $termArray = array_map('trim', explode(',', $terms));
    $termIds = [];
    foreach ($termArray as $term) {
      $term_id = ContentImport::getTermId($term, $vid);
      if (empty($term_id)) {
        $term_id = ContentImport::createTerm($voc, $term, $vid);
      }
      $termIds[]['target_id'] = $term_id;
    }
    return $termIds;
  }

  /**
   * To Create Terms if it is not available.
   */
  public function createVoc($vid, $voc) {
    $vocabulary = Vocabulary::create([
      'vid' => $vid,
      'machine_name' => $vid,
      'name' => $voc,
    ]);
    $vocabulary->save();
  }

  /**
   * To Create Terms if it is not available.
   */
  public function createTerm($voc, $term, $vid) {
    Term::create([
      'parent' => [$voc],
      'name' => $term,
      'vid' => $vid,
    ])->save();
    $termId = ContentImport::getTermId($term, $vid);
    return $termId;
  }

  /**
   * To get Termid available.
   */
  public function getTermId($term, $vid) {
    $termRes = db_query('SELECT n.tid FROM {taxonomy_term_field_data} n WHERE n.name  = :uid AND n.vid  = :vid', [':uid' => $term, ':vid' => $vid]);
    foreach ($termRes as $val) {
      $term_id = $val->tid;
    }
    return $term_id;
  }

  /**
   * To get node available.
   */
  public function getNodeId($title) {
    $nodeReference = [];
    $db = \Drupal::database();
    foreach ($title as $key => $value) {
      $query = $db->select('node_field_data', 'n');
      $query->fields('n', ['nid']);
      $nodeId = $query
        ->condition('n.title', trim($value))
        ->execute()
        ->fetchField();
      $nodeReference[$key]['target_id'] = $nodeId;
    }
    return $nodeReference;
  }

  /**
   * To get user information based on emailIds.
   */
  public static function getUserInfo($userArray) {
    $uids = [];
    foreach ($userArray as $usermail) {
      if (filter_var($usermail, FILTER_VALIDATE_EMAIL)) {
        $users = \Drupal::entityTypeManager()->getStorage('user')
          ->loadByProperties([
            'mail' => $usermail,
          ]);
      }
      else {
        $users = \Drupal::entityTypeManager()->getStorage('user')
          ->loadByProperties([
            'name' => $usermail,
          ]);
      }
      $user = reset($users);
      if ($user) {
        $uids[] = $user->id();
      }
      else {
        $user = User::create();
        $user->uid = '';
        $user->setUsername($usermail);
        $user->setEmail($usermail);
        $user->set("init", $usermail);
        $user->enforceIsNew();
        $user->activate();
        $user->save();
        $users = \Drupal::entityTypeManager()->getStorage('user')
          ->loadByProperties(['mail' => $usermail]);
        $uids[] = $user->id();
      }
    }
    return $uids;
  }

	/**
	 * To import data as Content type nodes.
	 */
	public function updateNode($filedata, $contentType) {
		global $base_url;
		$source_domain = 'https://stg.haierductless.com';
		$fields = ContentImport::getFields($contentType);
		$fieldNames = array_flip($fields['name']);

		$logFileName = "contentimportlog.txt";
		$logFile = fopen("sites/default/files/" . $logFileName, "w") or die("There is no permission to create log file. Please give permission for sites/default/file!");

		$mimetype = 1;
		if ($mimetype) {
			$location = $filedata['files']['tmp_name']['file_upload'];
			if (($handle = fopen($location, "r")) !== FALSE) {
				$keyIndex = [];
				$index = 0;
				$logVariationFields = "***************************** Files Import Begins ************************************ \n \n ";
				$i = 0; //first row of csv doc.
				$obj = [];
				//logs
				while (($rowsData = fgetcsv($handle)) !== FALSE) {
					foreach ($rowsData as $fieldName => $fieldValue) {
						if ($i == 0) {
							$obj[$fieldName] = $fieldValue;
						} else {
							$fileData[$i][$obj[$fieldName]] = $fieldValue;
						}

					}
					$i++;
				}

				// Update Node object.
				foreach ($fileData as $data) {

					foreach ($data as $key => $value) {

						// First row used to set field name. So, skip last row iteration.
						if (empty($data['nid'])) continue;
						$node = Node::load($data['nid']);
						$uid = $node->getOwnerId();

						switch ($fields['type'][$fieldNames[$key]]) {

							case 'file':
								$imageSource = $source_domain . $value;
								$imageDest = str_replace("/sites/default/files/", "public://", $value);

								$iData = file_get_contents($imageSource);

								// If file exist.
							  if ($iData) {
									$file = file_save_data($iData, $imageDest, FILE_EXISTS_REPLACE);

									if ($file !== FALSE) {
										$logVariationFields .= 'File ' . $imageDest . " uploaded successfully. \n";
										$query = \Drupal::database()->update('file_managed');
										$query->fields([
											'uid' => $uid,
											'created' => $node->changed->value,
											'changed' => $node->changed->value
										]);
										$query->condition('fid', $file->fid->value);
										$query->execute();

										$node->get($key)->setValue([
											'target_id' => $file->fid->value,
										]);
										$logVariationFields .= 'File ' . $file->fid->value . " created sucessfully. \n";
									} else {
										$logVariationFields .= 'File unable to upload to.' . $imageDest . "\n";
									}
								} else {
									$logVariationFields .= 'File ' . $imageSource . " not found. \n";
								}

								break;
							case 'image':

								$fileSource = $source_domain . $value;
								$fileDest = str_replace("/sites/default/files/", "public://", $value);

								$fData = file_get_contents($fileSource);
								// If file exist.
								if ($fData) {
									$image = file_save_data($fData, $fileDest, FILE_EXISTS_REPLACE);

									if ($image !== FALSE) {
										$logVariationFields .= 'Image ' . $fileDest . " uploaded successfully. \n";
										// Change file's author id, created and changed date same as node.
										$query = \Drupal::database()->update('file_managed');
										$query->fields([
											'uid' => $uid,
											'created' => $node->changed->value,
											'changed' => $node->changed->value
										]);
										$query->condition('fid', $image->fid->value);
										$query->execute();
										// Set foreign key in node with file id.
										$node->get($key)->setValue([
											'target_id' => $image->fid->value,
										]);
										$logVariationFields .= "Image " . $image->fid->value . " created sucessfully. \n";
									} else {
										$logVariationFields .= 'Image ' . $fileDest . " fail to upload successfully. \n";
									}
								} else {
									$logVariationFields .= 'Image ' . $fileSource . " not found. \n";
								}
								break;
						}
				  }
					// Don't modify update timestamp.
					$node->set('changed', $node->changed->value);
					$node->save();
					$logVariationFields .= 'Node ID:' . $node->Id() . " updated sucessfully. \n";
				}
				$logVariationFields .= "********************* Files Imported successfully ********************* \n\n";
				fwrite($logFile, $logVariationFields);
				fclose($handle);
			}
		}
		$url = $base_url . "/admin/config/content/contentimport";
		header('Location:' . $url);
		exit;

	}

  /**
   * To import data as Content type nodes.
   */
  public function createNode($filedata, $contentType) {

  	$this->updateNode($filedata, $contentType);
  	exit;
    drupal_flush_all_caches();
    global $base_url;

    $logFileName = "contentimportlog.txt";
    $logFile = fopen("sites/default/files/" . $logFileName, "w") or die("There is no permission to create log file. Please give permission for sites/default/file!");
    $fields = ContentImport::getFields($contentType);
		$fieldNames = $fields['name'];
    $fieldTypes = $fields['type'];
    $fieldSettings = $fields['setting'];
    // Code for import csv file.
    $mimetype = 1;
    if ($mimetype) {
      $location = $filedata['files']['tmp_name']['file_upload'];
      if (($handle = fopen($location, "r")) !== FALSE) {
        $keyIndex = [];
        $index = 0;
        $logVariationFields = "***************************** Content Import Begins ************************************ \n \n ";
        while (($data = fgetcsv($handle)) !== FALSE) {
          $index++;
          if ($index < 2) {
            array_push($fieldNames, 'title');
            array_push($fieldTypes, 'text');
            array_push($fieldNames, 'langcode');
            array_push($fieldTypes, 'lang');
            array_push($fieldNames, 'body');
            array_push($fieldTypes, 'text');			
            if (array_search('langcode', $data) === FALSE) {
              $logVariationFields .= "Langcode missing --- Assuming EN as default langcode.. Import continues  \n \n";
              $data[count($data)] = 'langcode';
            }

            foreach ($fieldNames as $fieldValues) {
              $i = 0;
              foreach ($data as $dataValues) {
                if ($fieldValues == $dataValues) {
                  $logVariationFields .= "Data Type : " . $fieldValues . "  Matches \n";
                  $keyIndex[$fieldValues] = $i;
                }
                $i++;
              }
            }
            continue;
          }
          if (!isset($keyIndex['title']) || !isset($keyIndex['langcode'])) {
            drupal_set_message($this->t('title or langcode is missing in CSV file. Please add these fields and import again'), 'error');
            $url = $base_url . "/admin/config/content/contentimport";
            header('Location:' . $url);
            exit;
          }

          $logVariationFields .= "********************************* Importing node ****************************  \n \n";
					for ($f = 0; $f < count($fieldNames); $f++) {
					  switch ($fieldTypes[$f]) {
              case 'image':
                $logVariationFields .= "Importing Image (" . trim($data[$keyIndex[$fieldNames[$f]]]) . ") :: ";
								if (!empty($data[$keyIndex[$fieldNames[$f]]])) {
									$imgIndex = trim($data[$keyIndex[$fieldNames[$f]]]);
									$files = glob($imgIndex);
									$fileExists = file_exists($imgIndex);
                  if ($fileExists) {
                    $images = [];
                    foreach ($files as $file_name) {
                      $image = File::create(['uri' => 'public://' . $contentType . '/images/' . basename($file_name)]);
                      $image->save();
                      $images[basename($file_name)] = $image;
                      $imageId = $images[basename($file_name)]->id();
                      $imageName = basename($file_name);
                    }
										$nodeArray[$fieldNames[$f]] = [
                      [
                        'target_id' => $imageId,
                        'alt' => $nodeArray['title'],
                        'title' => $nodeArray['title'],
                      ],
                    ];
                    $logVariationFields .= "Image uploaded successfully \n ";
                  } else {
										$link = trim($data[$keyIndex[$fieldNames[$f]]]);
										$link = substr($link, 1);
										$link_array = explode('/',$link);
										$image_name = end($link_array);
										$new_lnk ="sites/default/files/uploaded_files/".$image_name;
										$newfiles = glob("sites/default/files/uploaded_files/".$image_name);
										$newfileExists = file_exists("sites/default/files/uploaded_files/".$image_name);
									  if ($newfileExists) {
										  rcopy($new_lnk, $link);
									  }
										$images = [];
										foreach ($newfiles as $file_name) {
											$image = File::create(['uri' => 'public://' . $contentType . '/images/' . basename($file_name)]);
											$image->save();
											$images[basename($file_name)] = $image;
											$imageId = $images[basename($file_name)]->id();
											$imageName = basename($file_name);
										}
										$nodeArray[$fieldNames[$f]] = [
											[
												'target_id' => $imageId,
												'alt' => $nodeArray['title'],
												'title' => $nodeArray['title'],
											],
										];
										$logVariationFields .= "Image uploaded successfully \n ";
									}
                }
								$logVariationFields .= " Success \n";
                break;

              case 'entity_reference':
                $logVariationFields .= "Importing Reference Type (" . $fieldSettings[$f]['target_type'] . ") :: ";
                if ($fieldSettings[$f]['target_type'] == 'taxonomy_term') {
                  $reference = explode(":", $data[$keyIndex[$fieldNames[$f]]]);
                  if (is_array($reference) && $reference[0] != '') {
                    $terms = ContentImport::getTermReference($reference[0], $reference[1]);
                    $nodeArray[$fieldNames[$f]] = $terms;
                  }
                }
                elseif ($fieldSettings[$f]['target_type'] == 'user') {
                  $userArray = explode(', ', $data[$keyIndex[$fieldNames[$f]]]);
                  $users = ContentImport::getUserInfo($userArray);
                  $nodeArray[$fieldNames[$f]] = $users;
                }
                elseif ($fieldSettings[$f]['target_type'] == 'node') {
                  $nodeArrays = explode(':', $data[$keyIndex[$fieldNames[$f]]]);
                  $nodeReference1 = ContentImport::getNodeId($nodeArrays);
                  $nodeArray[$fieldNames[$f]] = $nodeReference1;
                }
                $logVariationFields .= " Success \n";
                break;
                            
              case 'text_long':
              case 'text':
                $logVariationFields .= "Importing Content (" . $fieldNames[$f] . ") :: ";
                $nodeArray[$fieldNames[$f]] = [
                  'value' => $data[$keyIndex[$fieldNames[$f]]],
                  'format' => 'full_html',
                ];
                $logVariationFields .= " Success \n";
                break;

              case 'entity_reference_revisions':
              case 'text_with_summary':
                $logVariationFields .= "Importing Content (" . $fieldNames[$f] . ") :: ";
                $nodeArray[$fieldNames[$f]] = [
                  'summary' => substr(strip_tags($data[$keyIndex[$fieldNames[$f]]]), 0, 100),
                  'value' => $data[$keyIndex[$fieldNames[$f]]],
                  'format' => 'full_html',
                ];
                $logVariationFields .= " Success \n";

                break;

              case 'datetime':
                $logVariationFields .= "Importing Datetime (" . $fieldNames[$f] . ") :: ";
                $dateArray = explode(':', $data[$keyIndex[$fieldNames[$f]]]);
                if (count($dateArray) > 1) {
                  $dateTimeStamp = strtotime($data[$keyIndex[$fieldNames[$f]]]);
                  $newDateString = date('Y-m-d\TH:i:s', $dateTimeStamp);
                }
                else {
                  $dateTimeStamp = strtotime($data[$keyIndex[$fieldNames[$f]]]);
                  $newDateString = date('Y-m-d', $dateTimeStamp);
                }
                $nodeArray[$fieldNames[$f]] = ["value" => $newDateString];
                $logVariationFields .= " Success \n";
                break;

              case 'timestamp':
                $logVariationFields .= "Importing Content (" . $fieldNames[$f] . ") :: ";
                $nodeArray[$fieldNames[$f]] = ["value" => $data[$keyIndex[$fieldNames[$f]]]];
                $logVariationFields .= " Success \n";
                break;

              case 'boolean':
                $logVariationFields .= "Importing Boolean (" . $fieldNames[$f] . ") :: ";
                $nodeArray[$fieldNames[$f]] = ($data[$keyIndex[$fieldNames[$f]]] == 'On' ||
                                               $data[$keyIndex[$fieldNames[$f]]] == 'Yes' ||
                                               $data[$keyIndex[$fieldNames[$f]]] == 'on' ||
                                               $data[$keyIndex[$fieldNames[$f]]] == 'yes') ? 1 : 0;
                $logVariationFields .= " Success \n";
                break;

              case 'langcode':
                $logVariationFields .= "Importing Langcode (" . $fieldNames[$f] . ") :: ";
                $nodeArray[$fieldNames[$f]] = ($data[$keyIndex[$fieldNames[$f]]] != '') ? $data[$keyIndex[$fieldNames[$f]]] : 'en';
                $logVariationFields .= " Success \n";
                break;

              case 'geolocation':
                $logVariationFields .= "Importing Geolocation Field (" . $fieldNames[$f] . ") :: ";
                $geoArray = explode(";", $data[$keyIndex[$fieldNames[$f]]]);
                if (count($geoArray) > 0) {
                  $geoMultiArray = [];
                  for ($g = 0; $g < count($geoArray); $g++) {
                    $latlng = explode(",", $geoArray[$g]);
                    for ($l = 0; $l < count($latlng); $l++) {
                      $latlng[$l] = floatval(preg_replace("/\[^0-9,.]/", "", $latlng[$l]));
                    }
                    array_push($geoMultiArray, [
                      'lat' => $latlng[0],
                      'lng' => $latlng[1],
                    ]);
    }
                  $nodeArray[$fieldNames[$f]] = $geoMultiArray;
                }
                else {
                  $latlng = explode(",", $data[$keyIndex[$fieldNames[$f]]]);
                  for ($l = 0; $l < count($latlng); $l++) {
                    $latlng[$l] = floatval(preg_replace("/\[^0-9,.]/", "", $latlng[$l]));
                  }
                  $nodeArray[$fieldNames[$f]] = ['lat' => $latlng[0], 'lng' => $latlng[1]];
                }
                $logVariationFields .= " Success \n";
                break;
			  case 'file':
				$logVariationFields .= "Importing PDF (" . trim($data[$keyIndex[$fieldNames[$f]]]) . ") :: ";
				  $imgIndex = trim($data[$keyIndex[$fieldNames[$f]]]);
				  $fileExists = file_exists($imgIndex);
				  if ($fileExists) {
				  $files = glob('sites/default/files/' . $contentType . '/files/' . $imgIndex);
                  $files_values = [];
                    foreach ($files as $file_name) {
                      $files_values = File::create(['uri' => 'public://' . $contentType . '/files/' . basename($file_name)]);
                      $files_values->save();
                      $filesvalue[basename($file_name)] = $files_values;
                      $fileid = $filesvalue[basename($file_name)]->id();
                      $fileName = basename($file_name);
                    }
											$nodeArray[$fieldNames[$f]] = [
                      [
                        'target_id' => $fileid,
                        'alt' => $file_name,
                        'title' => $file_name,
                      ],
                    ];
					}else{
						$link = trim($data[$keyIndex[$fieldNames[$f]]]);
						$link_array = explode('/',$link);
						$files_name = end($link_array);
						$new_lnk ="sites/default/files/uploaded_files/".$files_name;
						$newfiles = glob($new_lnk);
						$newfileExists = file_exists("sites/default/files/uploaded_files/".$files_name);
						if ($newfileExists) {
							 rcopy($new_lnk, $link); 
						}
						$files_values = [];
						if($contentType=='hda_series'){
							$files_values = File::create(['uri' => 'public://downloads/series/' . basename($files_name)]);
						}else{
							$files_values = File::create(['uri' => 'public://downloads/' . basename($files_name)]);
						}	
					    $files_values->save();
					    $filesvalue[basename($files_name)] = $files_values;
					    $fileid = $filesvalue[basename($files_name)]->id();
					    $fileName = basename($files_name);
						$nodeArray[$fieldNames[$f]] = [
						  [
							'target_id' => $fileid,
							'alt' => $files_name,
							'title' => $files_name,
						  ],
						];
                    $logVariationFields .= "Image uploaded successfully \n ";
				}
					$logVariationFields .= "Files uploaded successfully \n ";
				break;
              case 'entity_reference_revisions':
                /* In Progress */
                break;

              default:
                $nodeArray[$fieldNames[$f]] = $data[$keyIndex[$fieldNames[$f]]];
                break;
            }
          }

          if (array_search('langcode', $data) === FALSE) {
            $nodeArray['langcode'] = 'en';
          }
		  $nodeArray['body'] = $nodeArray['body'];
		  $nodeArray['type'] = strtolower($contentType);
          $nodeArray['uid'] = 1;
          $nodeArray['promote'] = 0;
          $nodeArray['sticky'] = 0;
          if ($nodeArray['title']['value'] != '') {
            $node = Node::create($nodeArray);
            $node->save();
            $logVariationFields .= "********************* Node Imported successfully ********************* \n\n";
            fwrite($logFile, $logVariationFields);
          }
          $nodeArray = [];
        }
        fclose($handle);
        $url = $base_url . "/admin/content";
        header('Location:' . $url);
        exit;
      }
    } //die('test');
  }

}

 // Call function 
// Function to Copy folders and files       
function rcopy($src, $dst) {
    if (file_exists ( $dst ))
        rrmdir ( $dst );
    if (is_dir ( $src )) {
        mkdir ( $dst );
        $files = scandir ( $src );

        foreach ( $files as $file )
            if ($file != "." && $file != "..")
                rcopy ( "$src/$file", "$dst/$file" );

    } else if (file_exists ( $src ))
        copy ( $src, $dst );
			        rrmdir ( $src );
}       

// Function to remove folders and files 
function rrmdir($dir) {
	 if (is_dir($dir)) {
		 $files = scandir($dir);
        foreach ($files as $file)
            if ($file != "." && $file != "..") rrmdir("$dir/$file");
        rmdir($dir);
    }
    else if (file_exists($dir)) ;
}

// Create directory and set public permission.
function smartcopy($source,$destination) {
	$path = pathinfo($destination);
	if (!file_exists($path['dirname'])) {
		mkdir($path['dirname'], 0777, true);
		return $destination . " copy Successfull. \n";
	}
	if (!copy($source, $destination)) {
		return $destination . " copy failed. \n";
	}
}