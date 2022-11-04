<?php

namespace Drupal\intl_date\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Datetime\DateFormatInterface;

/**
 * Defines the Date Format configuration entity class.
 *
 * @ConfigEntityType(
 *   id = "intl_date_format",
 *   label = @Translation("International Date format"),
 *   handlers = {
 *     "access" = "Drupal\system\DateFormatAccessControlHandler",
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   admin_permission = "administer site configuration",
 *   list_cache_tags = { "rendered" },
 *   config_export = {
 *     "id",
 *     "label",
 *     "pattern",
 *   }
 * )
 */
class IntlDateFormat extends ConfigEntityBase implements DateFormatInterface {

  /**
   * The date format machine name.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the date format entity.
   *
   * @var string
   */
  protected $label;

  /**
   * The date format pattern.
   *
   * @var array
   */
  protected $pattern;

  /**
   * {@inheritdoc}
   */
  public function getPattern() {
    return $this->pattern;
  }

  /**
   * {@inheritdoc}
   */
  public function setPattern($pattern) {
    $this->pattern = $pattern;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isLocked() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function sort(ConfigEntityInterface $a, ConfigEntityInterface $b) {
    $a_label = $a->label();
    $b_label = $b->label();
    return strnatcasecmp($a_label, $b_label);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTagsToInvalidate() {
    return ['rendered'];
  }

}
