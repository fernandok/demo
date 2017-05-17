<?php

namespace Drupal\cypress_custom_address\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_product\Entity\Product;

/**
 * Provides the completion message pane.
 *
 * @CommerceCheckoutPane(
 *   id = "cypress_parts_products",
 *   label = @Translation("Parts End Products"),
 *   default_step = "order_information",
 *   wrapper_element = "fieldset"
 * )
 */
class CypressPartsProducts extends CheckoutPaneBase {

  /**
   * {@inheritdoc}
 */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $order = $this->order;
    // Wrapper for Parts information pane.
    $pane_form['#wrapper_id'] = 'parts-information-wrapper';
    $pane_form['#prefix'] = '<div id="' . $pane_form['#wrapper_id'] . '">';
    $pane_form['#suffix'] = '</div>';
    $is_part_present = FALSE;
    // Options for purpose of order field.
    $purpose_of_order_options = [
      'university' => 'University',
      'training' => 'Training',
      'cypress internal training' => 'Cypress Internal Training',
      'one time use' => 'One Time Use',
      'other' => 'Other'
    ];
    // Get Value from the order object.
    $purpose_order_value = $this->order->get('field_purpose_of_order')->getValue()[0]['value'];
    $primary_application_order_value = $this->order->get('field_primary_application')->getValue()[0]['value'];
    $name_product_system_order_value = $this->order->get('field_name_product_system')->getValue()[0]['value'];
    $end_customer_order_value = $this->order->get('field_end_customer')->getValue()[0]['value'];
    foreach ($order->getItems() as $order_item) {
      $order_item->setAdjustments([]);
      $product_variation = $order_item->getPurchasedEntity();
      if (!empty($product_variation)) {
        $product_id = $product_variation->get('product_id')
          ->getValue()[0]['target_id'];
        if (!empty($product_id)) {
          $product = Product::load($product_id);
        }
        $product_type = $product->get('type')->getValue()[0]['target_id'];
        if ($product_type == 'part') {
          $is_part_present = TRUE;
          break;
        }
      }
    }
    // Show all fields if order has parts.
    if($is_part_present == TRUE) {
      // Options for primary applications field.
      $primary_applications_options = [
        'automotive' => 'Automotive',
        'communications systems' => 'Communications Systems',
        'computer systems or peripherals' => 'Computer Systems / Peripherals',
        'consumer electronics' => 'Consumer Electronics (Audio/Video)',
        'cypress internal usage' => 'Cypress Internal Usage',
        'medical or healthcare' => 'Medical / Healthcare',
        'military or aerospace' => 'Military / Aerospace',
        'robotics or automation' => 'Robotics / Automation',
        'university or educational use' => 'University / Educational Use'
      ];

      $values = $form_state->getValues();

      if(!empty($order)) {
        $primary_values = $order->get('field_primary_application')
          ->getValue()[0]['value'];
        $secondary_values = $order->get('field_name_product_system')
          ->getValue()[0]['value'];
        $purpose_of_order = $order->get('field_purpose_of_order')
          ->getValue()[0]['value'];
        $end_customer = $order->get('field_end_customer')->getValue()[0]['value'];
      }
      else {
        $primary_values = $values['cypress_parts_products']['primary_application'];
        $secondary_values = $values['cypress_parts_products']['dropdown_second'];
        $purpose_of_order = $values['cypress_parts_products']['purpose_order'];
        $end_customer = $values['cypress_parts_products']['end_customer'];
      }

      $value_dropdown_first = isset($primary_values) ? $primary_values : key($primary_applications_options);

      // Fields creation in pane.
      $pane_form['primary_application'] = [
        '#type' => 'select',
        '#title' => t('Primary Application for Projects/Designs'),
        '#options' => $primary_applications_options,
        '#required' => TRUE,
        '#default_value' => $value_dropdown_first,
        '#ajax' => [
          'callback' => [get_class($this), 'cypressCustomAddressAjaxCallback'],
          'wrapper' => $pane_form['#wrapper_id'],
        ],
      ];
      $pane_form['dropdown_second'] = array(
        '#type' => 'select',
        '#title' => t('Name of your end Product/system?'),
        '#required' => TRUE,
        '#options' => $this->secondDropdownOptions($value_dropdown_first),
        '#default_value' => isset($secondary_values) ? $secondary_values : $name_product_system_order_value,
      );
    }
    // Show only two fields
    $pane_form['purpose_order'] = array(
      '#type' => 'select',
      '#title' => t('Purpose of order?'),
      '#required' => TRUE,
      '#options' => $purpose_of_order_options,
      '#default_value' => isset($purpose_of_order) ? $purpose_of_order : $purpose_order_value,
    );
    $pane_form['end_customer'] = array(
      '#type' => 'textfield',
      '#title' => t('End Customer'),
      '#required' => TRUE,
      '#default_value' => isset($end_customer) ? $end_customer : $end_customer_order_value,
      '#maxlength' => 255,
    );
    return $pane_form;
  }

  /*
   * Helper function for options for Name of your end product/system field.
   */
  public function secondDropdownOptions($key = ''){
    $options = [
      'automotive' => [
        'active noise cancellation' => 'Active Noise Cancellation',
        'auto braking, suspension' => 'Auto Braking, Suspension',
        'auto power train, emission control' => 'Auto Power Train, Emission Control',
        'auto steering system' => 'Auto Steering System',
        'automotive – commercial vehicle acessory' => 'Automotive – Commercial Vehicle Acessory',
        'automotive central body controller' => 'Automotive Central body Controller',
        'automotive infotainment' => 'Automotive Infotainment',
        'automotive vision control' => 'Automotive Vision Control',
        'car audio and entertainment' => 'Car Audio and Entertainment',
        'car climate control unit' => 'Car climate Control Unit',
        'car dashboard instrument cluster' => 'Car Dashboard Instrument Cluster',
        'auto power train, emission control' => 'Auto Power Train, Emission Control',
        'digital radio' => 'Digital Radio',
        'e-Bike' => 'E-Bike',
        'global positioning satellite (gps) receiver' => 'Global Positioning Satellite (GPS) Receiver',
        'hands free kit' => 'Hands Free Kit',
        'power door' => 'Power Door'
      ],
      'communications systems' => [
        '802.11 wireless lan' => '802.11 Wireless LAN',
        'adsl modemRouter' => 'ADSL ModemRouter',
        'atca solutions' => 'ATCA Solutions',
        'atm switching equipment' => 'ATM Switching Equipment',
        'analog modem' => 'Analog Modem',
        'bluetooth headset' => 'Bluetooth Headset',
        'co line cardssystem cores' => 'CO Line CardsSystem Cores',
        'cable solutions' => 'Cable Solutions',
        'call logging' => 'Call Logging',
        'central office switching equipment' => 'Central Office Switching Equipment',
        'communication wired wireless' => 'Communication Wired Wireless',
        'digital signage' => 'Digital Signage',
        'digital wan' => 'Digital WAN',
        'ethernet controller' => 'Ethernet Controller',
        'femto base station' => 'Femto Base Station',
        'full-duplex speakerphone' => 'Full-Duplex Speakerphone',
        'global positioning satellite (gps) receiver' => 'Global Positioning Satellite (GPS) Receiver',
        'hands free kit' => 'Hands Free Kit',
        'handset: entry' => 'Handset: Entry',
        'handset: feature' => 'Handset: Feature',
        'handset: multimedia' => 'Handset: Multimedia',
        'ip phone: video' => 'IP Phone: Video',
        'iP phone: wireless' => 'IP Phone: Wireless',
        'isdn adapters' => 'ISDN Adapters',
        'integrated access device' => 'Integrated Access Device',
        'lan routers, switches' => 'LAN Routers, Switches',
        'low/hi-end dvr and dvs' => 'Low/Hi-End DVR and DVS',
        'mid-end dvr and dvs' => 'Mid-End DVR and DVS',
        'misc. public transmission' => 'Misc. Public Transmission',
        'mobile communication infrastructure' => 'Mobile Communication Infrastructure',
        'mobile internet device' => 'Mobile Internet Device',
        'network hub' => 'Network Hub',
        'ofdm power line modem' => 'OFDM Power Line MODEM',
        'optical line card' => 'Optical Line Card',
        'pabx telephony multi-processing' => 'PABX Telephony Multi-Processing',
        'pstn-ip gateway' => 'PSTN-IP Gateway',
        'point-to-point microwave backhaul' => 'Point-to-Point Microwave Backhaul',
        'power line communication modem' => 'Power Line Communication Modem',
        'power line communications' => 'Power Line Communications',
        'power over ethernet (poe)' => 'Power Over Ethernet (PoE)',
        'premises line cardsystem cores' => 'Premises Line CardSystem Cores',
        'private branch exchanges' => 'Private Branch Exchanges',
        'rasrac' => 'RASRAC',
        'smsmms phone' => 'SMSMMS Phone',
        'secure phone' => 'Secure Phone',
        'server' => 'Server',
        'shortwave modem' => 'Shortwave Modem',
        'software defined radio (sdr)' => 'Software Defined Radio (SDR)',
        'tetra base station' => 'TETRA Base Station',
        'usb phone' => 'USB Phone',
        'video analytics server' => 'Video Analytics Server',
        'video broadcasting and infrastructure: scalable platform' => 'Video Broadcasting and Infrastructure: Scalable Platform',
        'video broadcasting: ip-based multi-format decoder' => 'Video Broadcasting: IP-Based Multi-Format Decoder',
        'video broadcasting: ip-based multi-format transcoder' => 'Video Broadcasting: IP-Based Multi-Format Transcoder',
        'video conferencing: ip-based hd' => 'Video Conferencing: IP-Based HD',
        'video conferencing: ip-based sd' => 'Video Conferencing: IP-Based SD',
        'video infrastructure' => 'Video Infrastructure',
        'voip solutions' => 'VoIP Solutions',
        'voice multiplex systems' => 'Voice Multiplex Systems',
        'wiMAX and wireless infrastructure equipment' => 'WiMAX and Wireless Infrastructure Equipment',
        'wireless base station' => 'Wireless Base station',
        'wireless broadband access card' => 'Wireless Broadband Access Card',
        'wireless lan card' => 'Wireless LAN Card',
        'wireless repeater' => 'Wireless Repeater'
      ],
      'computer systems or peripherals' => [
        '802.11 Wireless LAN' => '802.11 Wireless LAN',
        'cd (rom and rw)' => 'CD (ROM and RW)',
        'cable solutions' => 'Cable Solutions',
        'copiers, fax, scanners' => 'Copiers, Fax, Scanners',
        'desktop pc' => 'Desktop PC',
        'fingerprint biometrics' => 'Fingerprint Biometrics',
        'graphicsaudio cards' => 'GraphicsAudio Cards',
        'hard disk drive' => 'Hard Disk Drive',
        'holographic data storage' => 'Holographic Data Storage',
        'lcd tv' => 'LCD TV',
        'mainframe supercomputers' => 'Mainframe Supercomputers',
        'mobile internet device' => 'Mobile Internet Device',
        'notebook pc' => 'Notebook PC',
        'pc peripheral equipment' => 'PC Peripheral Equipment',
        'pc removable storage' => 'PC Removable Storage',
        'personal digital assistant (pda)' => 'Personal Digital Assistant (PDA)',
        'printers' => 'Printers',
        'scanner' => 'Scanner',
        'server' => 'Server',
        'servers' => 'Servers',
        'usb phone' => 'USB Phone',
        'usb speakers' => 'USB Speakers',
        'wireless data access card' => 'Wireless Data Access Card',
        'wireless lan card' => 'Wireless LAN Card',
        'workstations' => 'Workstations'
      ],
      'consumer electronics' => [
        '802.11 wireless lan' => '802.11 Wireless LAN',
        'av receivers' => 'AV Receivers',
        'audio cd player' => 'Audio CD Player',
        'baby monitor' => 'Baby Monitor',
        'barcode scanner' => 'Barcode Scanner',
        'blu-ray player and home theater' => 'Blu-ray Player and Home Theater',
        'bluetooth headset' => 'Bluetooth Headset',
        'dlp front projection system' => 'DLP Front Projection System',
        'dvd player' => 'DVD Player',
        'dvd recorder' => 'DVD Recorder',
        'dvr: security with ip' => 'DVR: Security with IP',
        'desktop pc' => 'Desktop PC',
        'digital audio, mp3 player' => 'Digital Audio, MP3 Player',
        'digital hearing aids' => 'Digital Hearing Aids',
        'digital picture frame (dpf)' => 'Digital Picture Frame (DPF)',
        'digital radio' => 'Digital Radio',
        'digital set-top-box (stbpvr)' => 'Digital Set-Top-Box (STBPVR)',
        'digital speakers' => 'Digital Speakers',
        'digital still camera' => 'Digital Still Camera',
        'digital video recorder' => 'Digital Video Recorder',
        'global positioning satellite (gps) receiver' => 'Global Positioning Satellite (GPS) Receiver',
        'hdtv' => 'HDTV',
        'hvac' => 'HVAC',
        'hands free kit' => 'Hands Free Kit',
        'handset: entry' => 'Handset: Entry',
        'handset: feature' => 'Handset: Feature',
        'handset: multimedia' => 'Handset: Multimedia',
        'high-definition television (hdtv)' => 'High-Definition Television (HDTV)',
        'holographic data storage' => 'Holographic Data Storage',
        'home automation (domotics)' => 'Home Automation (Domotics)',
        'home entertainment' => 'Home Entertainment',
        'ip phone: video' => 'IP Phone: Video',
        'internet audio players' => 'Internet Audio Players',
        'mp3 player/recorder (portable audio)' => 'MP3 Player/Recorder (Portable Audio)',
        'microwave oven' => 'Microwave Oven',
        'mobile internet device' => 'Mobile Internet Device',
        'musical instruments' => 'Musical Instruments',
        'notebook pc' => 'Notebook PC',
        'oscilloscope' => 'Oscilloscope',
        'pda, palm-top computer' => 'PDA, Palm-Top Computer',
        'pagers' => 'Pagers',
        'personal digital assistant (pda)' => 'Personal Digital Assistant (PDA)',
        'portable dvd player' => 'Portable DVD Player',
        'portable media player' => 'Portable Media Player',
        'printer' => 'Printer',
        'projectors' => 'Projectors',
        'rf4ce remote control' => 'RF4CE Remote Control',
        'refrigerator' => 'Refrigerator',
        'robots' => 'Robots',
        'server' => 'Server',
        'smart cards' => 'Smart Cards',
        'streaming media' => 'Streaming Media',
        'tv lcd digital' => 'TV LCD Digital',
        'toys, games and hobbies' => 'Toys, Games and Hobbies',
        'vcrs' => 'VCRs',
        'video camcorder' => 'Video Camcorder',
        'video game devices' => 'Video Game Devices',
        'washing machine: mainstream' => 'Washing Machine: Mainstream',
        'washing machine: traditional' => 'Washing Machine: Traditional',
        'white goods' => 'White Goods',
        'wireless data access card' => 'Wireless Data Access Card'
      ],
      'medical or healthcare' => [
        'analytical instruments' => 'Analytical Instruments',
        'automated external defibrillator' => 'Automated External Defibrillator',
        'blood pressure monitor' => 'Blood Pressure Monitor',
        'blood glucose monitor' => 'Blood Glucose monitor',
        'cpap machine' => 'CPAP machine',
        'cerebellar stimulator' => 'Cerebellar Stimulator',
        'cholesterol monitor' => 'Cholesterol monitor',
        'computed tomography' => 'Computed tomography',
        'confocal microscopy' => 'Confocal Microscopy',
        'dental instruments' => 'Dental instruments',
        'dialysis machine' => 'Dialysis machine',
        'digital hearing aids' => 'Digital Hearing Aids',
        'digital x-ray' => 'Digital X-Ray',
        'digital thermometers' => 'Digital thermometers',
        'ecg electrocardiogram' => 'ECG Electrocardiogram',
        'electrocardiogram (ecg) front end' => 'Electrocardiogram (ECG) Front End',
        'electroencephalogram (eeg)' => 'Electroencephalogram (EEG)',
        'endoscope' => 'Endoscope',
        'gastric pacemaker' => 'Gastric Pacemaker',
        'hearing aid' => 'Hearing Aid',
        'heart rate monitors' => 'Heart Rate monitors',
        'home, portable and consumer medical devices' => 'Home, portable and consumer medical devices',
        'implantable devices' => 'Implantable devices',
        'infusion pump' => 'Infusion Pump',
        'infusion pump sbd' => 'Infusion Pump SBD',
        'insulin pumps' => 'Insulin Pumps',
        'internal defibrillator' => 'Internal Defibrillator',
        'laboratory equipment' => 'Laboratory equipment',
        'magnetic resonance imaging (mri)' => 'Magnetic Resonance Imaging (MRI)',
        'medical equipment' => 'Medical Equipment',
        'neuromuscular stimulator' => 'Neuromuscular Stimulator',
        'pacemaker' => 'Pacemaker',
        'patient monitoring: omap' => 'Patient Monitoring: OMAP',
        'portable blood gas analyzer' => 'Portable Blood Gas Analyzer',
        'portable medical instruments' => 'Portable Medical Instruments',
        'positron emission tomography' => 'Positron Emission Tomography',
        'pulse oximetry' => 'Pulse Oximetry',
        'spinal-cord stimulator' => 'Spinal-Cord Stimulator',
        'stethoscope: digital' => 'Stethoscope: Digital',
        'surgical instruments' => 'Surgical Instruments',
        'ultrasound system' => 'Ultrasound System',
        'ventilation respiration' => 'Ventilation respiration',
        'ventilator' => 'Ventilator',
        'x-ray: medical/dental' => 'X-ray: Medical/Dental'
      ],
      'cypress internal usage' => [
        'demo products' => 'Demo Products'
      ],
      'military or aerospace' => [
        'avionics' => 'Avionics',
        'military communications' => 'Military Communications',
        'military computers' => 'Military Computers',
        'military imaging systems' => 'Military Imaging Systems',
        'military instrumentation, sensors' => 'Military Instrumentation, Sensors',
        'military target detection and recognition' => 'Military Target Detection and Recognition',
        'military vehicle systems' => 'Military Vehicle Systems',
        'military weapon systems' => 'Military Weapon Systems',
        'missile guidance systems' => 'Missile Guidance Systems',
        'munitions' => 'Munitions',
        'other aerospace system' => 'Other Aerospace System',
        'other military systems' => 'Other Military Systems',
        'radar/sonar' => 'Radar/Sonar',
        'space instruments satellites' => 'Space Instruments Satellites',
        'target detection recognition' => 'Target Detection Recognition'
      ],
      'robotics or automation' => [
        'transportation' => 'Transportation',
        'small body orbiting' => 'Small Body Orbiting',
        'subsurface access' => 'Subsurface Access',
        'instrument placement' => 'Instrument Placement',
        'sampling' => 'Sampling',
        'construction' => 'Construction',
        'simulation' => 'Simulation',
        'user interfaces' => 'User Interfaces',
        'onboard science' => 'Onboard Science',
        'sensing and imaging' => 'Sensing and Imaging',
        'entertainment' => 'Entertainment',
        'industrial/manufacturing' => 'Industrial/Manufacturing',
        'material handling' => 'Material Handling',
        'hazard detection' => 'Hazard Detection',
        'home automation (domotics)' => 'Home Automation (Domotics)',
        'security' => 'Security'
      ],
      'university or educational use' => [
        'class/lab exercise' => 'Class/Lab Exercise',
        'commercial/government product design' => 'Commercial/Government Product Design'
      ]
    ];
    if (isset($options[$key])) {
      return $options[$key];
    }
    else {
      return array();
    }
  }

  /*
   * Ajax callback.
   */
  public function cypressCustomAddressAjaxCallback(&$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $parents = array_slice($triggering_element['#parents'], 0, -1);
    return NestedArray::getValue($form, $parents);
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValue($pane_form['#parents']);
    if (!empty($values['primary_application'])){
      $this->order->get('field_primary_application')->value = $values['primary_application'];
    }
    if (!empty($values['dropdown_second'])){
      $this->order->get('field_name_product_system')->value = $values['dropdown_second'];

    }
    if(!empty(($values['purpose_order']))){
      $this->order->get('field_purpose_of_order')->value = $values['purpose_order'];
    }
    if (!empty($values['end_customer'])){
      $this->order->get('field_end_customer')->value = $values['end_customer'];
    }
  }
}

