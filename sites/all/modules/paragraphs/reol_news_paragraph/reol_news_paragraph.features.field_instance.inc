<?php

/**
 * @file
 * reol_news_paragraph.features.field_instance.inc
 */

/**
 * Implements hook_field_default_field_instances().
 */
function reol_news_paragraph_field_default_field_instances() {
  $field_instances = array();

  // Exported field_instance:
  // 'paragraphs_item-article_carousel-field_carousel_count'.
  $field_instances['paragraphs_item-article_carousel-field_carousel_count'] = array(
    'bundle' => 'article_carousel',
    'default_value' => array(
      0 => array(
        'value' => 10,
      ),
    ),
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'reol_news_paragraph',
        'settings' => array(),
        'type' => 'reol_news_paragraph_carousel',
        'weight' => 0,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'field_carousel_count',
    'label' => 'Antal',
    'required' => 1,
    'settings' => array(
      'max' => 50,
      'min' => 0,
      'prefix' => '',
      'suffix' => '',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'number',
      'settings' => array(),
      'type' => 'number',
      'weight' => 1,
    ),
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('Antal');

  return $field_instances;
}