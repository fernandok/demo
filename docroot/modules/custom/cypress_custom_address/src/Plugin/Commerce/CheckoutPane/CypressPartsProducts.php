<?php

namespace Drupal\cypress_custom_address\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;

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
    $pane_form['#wrapper_id'] = 'parts-information-wrapper';
    $pane_form['#prefix'] = '<div id="' . $pane_form['#wrapper_id'] . '">';
    $pane_form['#suffix'] = '</div>';
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
    $value_dropdown_first = isset($values['cypress_parts_products']['primary_application']) ? $values['cypress_parts_products']['primary_application'] : key($primary_applications_options);

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
      '#title' => 'Second Dropdown',
      '#options' => $this->secondDropdownOptions($value_dropdown_first),
      '#default_value' => isset($values['cypress_parts_products']['dropdown_second']) ? $values['cypress_parts_products']['dropdown_second'] : '',
    );

    return $pane_form;
  }

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

  public function cypressCustomAddressAjaxCallback(&$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $parents = array_slice($triggering_element['#parents'], 0, -1);
    return NestedArray::getValue($form, $parents);
  }
  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {

  }


}
