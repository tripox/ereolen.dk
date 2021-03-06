<?php

/**
 * @file
 * Preprocess and Process Functions.
 */

/**
 * Implements hook_theme().
 *
 * Needed in order to theme user login.
 */
function wille_theme() {
  return array(
    'user_login' => array(
      'function' => 'wille_user_login',
      'render element' => 'element',
    ),
  );
}

/**
 * Implements hook_preprocess_html().
 */
function wille_preprocess_html(&$variables) {

  // Get the object loaded by the current router item.
  $node = menu_get_object();

  // Get the field_color and convert i t RGBA foropacity and add it
  // to the body background.
  if (!empty($node) && $node->type === 'breol_subject') {
    $color = _wille_alter_brightness($node->field_color[LANGUAGE_NONE][0]['rgb'], 50);

    $css = 'body {background-color: ' . $color . '} .organic-element--content .organic-svg  {fill: ' . $color . ' !important}';
    drupal_add_css($css, 'inline');
  }

  // Adding Viewport to HTML Header.
  $viewport = array(
    '#tag' => 'meta',
    '#attributes' => array(
      'name' => 'viewport',
      'content' => 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no',
    ),
  );

  drupal_add_html_head($viewport, 'viewport');
}

/**
 * Implements THEME_preprocess_TEMPLATE().
 */
function wille_preprocess_wille_site_template(&$variables, $hook) {
  $variables['organic_svg'] = file_get_contents(dirname(__FILE__) . "/svg/organic.svg");
}

/**
 * Implements THEME_preprocess_TEMPLATE().
 *
 * We preprocess and override the ctools content type here in the theme, because
 * there are theming specific files and styles that shoudl be included.
 */
function wille_preprocess_breol_news_page(&$variables, $hook) {
  $variables['organic_svg'] = file_get_contents(dirname(__FILE__) . "/svg/organic.svg");
  $variables['cover_attributes'] = array();
  if ($variables['image_file']) {
    $variables['cover_attributes']['style'] = 'background-image: url(' . file_create_url($variables['image_file']->uri) . ');';
  }
}

/**
 * Implements hook_preprocess_node().
 */
function wille_preprocess_node(&$variables, $hook) {
  // Add tpl suggestions for node view modes on node type.
  if (isset($variables['view_mode'])) {
    $variables['theme_hook_suggestions'][] = 'node__' . 'view_mode__' . $variables['view_mode'];
    $variables['theme_hook_suggestions'][] = 'node__' . $variables['node']->type . '__view_mode__' . $variables['view_mode'];
  }

  $node = $variables['node'];
  if ($node->type === 'breol_news' || $node->type === 'breol_section' || $node->type === 'breol_subject') {
    // Get file url for cover image.
    $variables['file_uri'] = NULL;
    if (!empty($node->field_breol_cover_image[LANGUAGE_NONE][0])) {
      $file_uri = file_create_url($node->field_breol_cover_image[LANGUAGE_NONE][0]['uri']);
      $variables['file_uri'] = $file_uri;
    }
  }
}

/**
 * Implements hook_ting_collection_view_alter().
 *
 * Suppress the type icon on the material in the ting_primary_object when the
 * collection contains more than one material. This removes the type-icon in
 * search results where the cover represents more than one material (likely of
 * different types).
 *
 * @deprecated by pratchett_ting_collection_view_alter() when using that as
 * base theme.
 */
function wille_ting_collection_view_alter(&$build) {
  if (isset($build['ting_primary_object'])) {
    foreach (element_children($build['ting_primary_object']) as $key) {
      $collection = $build['ting_primary_object']['#object'];
      if (count($collection->entities) > 1) {
        if (isset($build['ting_primary_object'][$key]['ting_cover'])) {
          $build['ting_primary_object'][$key]['ting_cover'][0]['#suppress_type_icon'] = TRUE;
        }
      }
    }
  }
}

/**
 * Implements hook_preprocess_ting_object_cover().
 *
 * Adds type icon to ting object covers.
 *
 * @deprecated by pratchett_ting_object_cover() when using that as
 * base theme.
 */
function wille_preprocess_ting_object_cover(&$variables) {
  if (!isset($variables['elements']['#suppress_type_icon']) ||
    !$variables['elements']['#suppress_type_icon']) {
    $ting_entity = $variables['object'];
    if ($ting_entity && $ting_entity->reply && isset($ting_entity->reply->on_quota)) {
      $add_classes = _wille_type_icon_classes(reol_base_get_type_icon($ting_entity->type), $ting_entity->reply->on_quota);
      $variables['classes'] = array_merge($variables['classes'], $add_classes);
    }
  }
}

/**
 * Theme ting_object_cover.
 *
 * Wraps the cover in a link to the material.
 */
function wille_ting_object_cover($variables) {
  $attributes = array(
    'class' => implode(' ', $variables['classes']),
  );

  foreach ($variables['data'] as $name => $value) {
    $attributes['data-' . $name] = $value;
  }

  $cover = '<div ' . drupal_attributes($attributes) . '>' . $variables['image'] . '</div>';

  // Add link if the id is not to a fake material.
  $ding_entity_id = $variables['elements']['#object']->ding_entity_id;
  if (!reol_base_fake_id($ding_entity_id)) {
    $cover = l($cover, 'ting/collection/' . $ding_entity_id, array('html' => TRUE));
  }

  return $cover;
}

/**
 * Return classes for type icon.
 *
 * @return array
 *   Classes as array.
 *
 * @todo merge with _brin_type_icon_classes().
 */
function _wille_type_icon_classes($type, $quota = NULL) {
  $classes = array(
    'type-icon',
    'type-icon-' . $type,
  );
  if (is_bool($quota)) {
    $classes[] = 'type-icon-' . ($quota ? 'quota' : 'noquota');
  }
  return $classes;
}

/**
 * Implements hook_ting_view_alter().
 *
 * Fix all fieldgroups to be open.
 *
 * @todo merge with brin_ting_view_alter().
 */
function wille_ting_view_alter(&$build) {
  foreach ($build['#groups'] as $group) {
    if ($group->format_settings['formatter'] == 'collapsed') {
      $group->format_settings['formatter'] = 'open';
    }
  }
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function wille_form_search_block_form_alter(&$form, &$form_state, $form_id) {
  // HTML5 placeholder attribute.
  $form['search_block_form']['#attributes']['placeholder'] = t('Search among thousands of titles');

  // Hide submit button.
  $form['actions']['#attributes']['class'][] = 'element-invisible';
}

/**
 * Implements hook_preprocess_panels_pane().
 */
function wille_preprocess_panels_pane(&$variables) {

  $variables['organic_svg'] = file_get_contents(dirname(__FILE__) . "/svg/organic.svg");

  if ($variables['pane']->type === 'user_menu') {
    // Hide logout menu tab.
    foreach ($variables['content'] as $key => $item) {
      if (!empty($item['#href'])) {
        $path = $item['#href'];
        if (drupal_match_path($path, 'user/*/logout')) {
          unset($variables['content'][$key]);
        }
      }
    }

    // Load the global user and vreate welcome banner text.
    global $user;

    $user = user_load($user->uid);

    // Todo(ts) - right now there doesn't to appear to be anything here.
    $real_name = trim($user->realname);

    $variables['welcome_text_part_1'] = t('Hello @real_name', array('@real_name' => $real_name));
    $variables['welcome_text_part_2'] = t('Welcome to Ereolen');
  }
}

/**
 * Preprocess ting_relations.
 */
function wille_preprocess_ting_relation(&$vars) {
  if ($vars['relation']->getType() == 'dbcaddi:hasDescriptionFromPublisher') {
    // Replace abstract with full content of the relation.
    // I know, this is way too coupled, but it's what we have to work with.
    $path = drupal_get_path('module', 'ting_fulltext');
    include_once $path . '/includes/ting_fulltext.pages.inc';
    $fulltext = ting_fulltext_object_load($vars['relation']->object->getId());
    $build = array(
      'ting_fulltext' => array(
        '#theme' => 'ting_fulltext',
        '#fields' => ting_fulltext_parse($fulltext),
      ),
    );
    $vars['abstract'] = drupal_render($build);

    // Remove link to full text.
    unset($vars['fulltext_link']);

    // Title is ugly per default, fix it.
    $vars['title'] = t('Description from publisher');
  }
}

/**
 * Preprocess ting_search_carousel_cover.
 */
function wille_preprocess_ting_search_carousel_cover(&$vars) {
  $cover = $vars['cover']['#cover'];

  $cover_classes = '';
  if (!isset($cover->placeholder)) {
    $entity = ding_entity_load($cover->id);
    if (isset($entity->reply)) {
      $cover_classes = implode(' ', _wille_type_icon_classes(reol_base_get_type_name($entity->type), $entity->reply->on_quota));
    }
  }

  $vars['cover_classes'] = $cover_classes;
}

/**
 * Theme field.
 *
 * Removes the : on certain fields.
 */
function wille_field($variables) {
  $output = '';

  // Render the label, if it's not hidden.
  if (!$variables['label_hidden']) {
    if ($variables['element']['#field_name'] == 'field_files') {
      $output .= '<div class="field-label"' . $variables['title_attributes'] . '>' . $variables['label'] . '</div>';
    }
    else {
      $output .= '<div class="field-label"' . $variables['title_attributes'] . '>' . $variables['label'] . ':&nbsp;</div>';
    }
  }

  // Render the items.
  $output .= '<div class="field-items"' . $variables['content_attributes'] . '>';
  foreach ($variables['items'] as $delta => $item) {
    $classes = 'field-item ' . ($delta % 2 ? 'odd' : 'even');
    $output .= '<div class="' . $classes . '"' . $variables['item_attributes'][$delta] . '>' . drupal_render($item) . '</div>';
  }
  $output .= '</div>';

  // Render the top-level DIV.
  $output = '<div class="' . $variables['classes'] . '"' . $variables['attributes'] . '>' . $output . '</div>';

  return $output;
}

/**
 * Theme user login form.
 */
function wille_user_login(&$vars) {
  $vars['element']['unilogin_wrapper'] = array(
    '#type' => 'fieldset',
    '#description' => $vars['element']['unilogin']['#title'],
  );
  $vars['element']['unilogin_wrapper']['unilogin'] = $vars['element']['unilogin'];
  unset($vars['element']['unilogin']);
  $vars['element']['unilogin_wrapper']['unilogin']['#title'] = $vars['element']['actions']['submit']['#value'];
  $vars['element']['unilogin_wrapper']['unilogin']['#prefix'] = '<div class="unilogin-wrapper">';
  $vars['element']['unilogin_wrapper']['unilogin']['#suffix'] = '</div>';

  $vars['element']['login_wrapper'] = array(
    '#type' => 'fieldset',
    '#description' => t('Log in with CPR or borrower number'),
  );
  $vars['element']['login_wrapper']['name'] = $vars['element']['name'];
  $vars['element']['login_wrapper']['pass'] = $vars['element']['pass'];
  $vars['element']['login_wrapper']['retailer_id'] = $vars['element']['retailer_id'];
  $vars['element']['login_wrapper']['actions'] = $vars['element']['actions'];
  unset($vars['element']['name']);
  unset($vars['element']['pass']);
  unset($vars['element']['retailer_id']);
  unset($vars['element']['actions']);

  return drupal_render_children($vars['element']);
}

/**
 * Brighten color.
 */
function _wille_alter_brightness($colourstr, $steps) {
  $colourstr = str_replace('#', '', $colourstr);
  $rhex = substr($colourstr, 0, 2);
  $ghex = substr($colourstr, 2, 2);
  $bhex = substr($colourstr, 4, 2);

  $r = hexdec($rhex);
  $g = hexdec($ghex);
  $b = hexdec($bhex);

  $r = max(0, min(255, $r + $steps));
  $g = max(0, min(255, $g + $steps));
  $b = max(0, min(255, $b + $steps));

  return '#' . dechex($r) . dechex($g) . dechex($b);
}
