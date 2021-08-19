<?php

/**
 * @file
 * Contains Views hooks implemented by the islandora_riprap.module.
 */

/**
 * Implements hook_views_data_alter().
 */
function islandora_riprap_views_data_alter(array &$data) {


  $data['media']['riprap_results'] = [
    'title' => t('Fixity auditing'),
    'help' => t('Shows results from the Riprap fixity auditing microservice.'),
    'field' => [
      'id' => 'riprap_results',
    ],
  ];

  $data['file_managed']['riprap_results'] = [
    'title' => t('Fixity auditing'),
    'help' => t('Show results from the Riprap fixity auditing microservice.'),
    'field' => [
      'id' => 'riprap_file_results',
    ],
  ];

  $data['file_managed']['riprap_success'] = [
    'title' => t('Checksum Result'),
    'help' => t('Show outcome of the checksum checking.'),
    'field' => [
      'id' => 'riprap_success_failure',
    ],
  ];
   
}
