<?php


/**
 * @file
 * Hooks related to intl_date module.
 */

/**
 * Alters the language - locale map.
 *
 * This way you can override existing mapping or define new ones.
 * The module ships with a limited set of mapping between language
 * codes and locales, this way it can be extended or overridden.
 */
function hook_intl_date_locale_map_alter(&$map) {
  // New language.
  $map['xe'] = 'xe_XE.UTF-16';
  // Override German, use Austrian variant.
  $map['de'] = 'de_AT.UTF-8';
}

/**
 * Alters the formatted date.
 *
 * $context provides:
 *  - language
 *  - locale
 *  - pattern
 */
function hook_intl_date_formatted_date(&$formatted_date, $context) {
  if ($context['language'] !== 'mn') {
    return;
  }
  $formatted_date = mb_strtolower($formatted_date);
}
