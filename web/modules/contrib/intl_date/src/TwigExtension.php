<?php

namespace Drupal\intl_date;

/**
 * Format dates in Twig templates using Intl.
 */
class TwigExtension extends \Twig_Extension {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'intl_date.twig_extension';
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    return [
      new \Twig_SimpleFilter('intl_format_date', [IntlDate::class, 'format']),
      new \Twig_SimpleFilter('intl_format_date_pattern', [IntlDate::class, 'formatPattern']),
    ];
  }

}
