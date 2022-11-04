<?php

namespace Drupal\elasticsearch_connector\Plugin\search_api\data_type;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\search_api\DataType\DataTypePluginBase;

/**
 * Provides a full date data type.
 *
 * @SearchApiDataType(
 *   id = "full_date",
 *   label = @Translation("Full Date"),
 *   description = @Translation("Represents points in time."),
 *   default = "true"
 * )
 */
class FullDateDataType extends DataTypePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getValue($value) {
    if ((string) $value === '') {
      return NULL;
    }
    if (is_numeric($value)) {
      // It could come from a created timestamp.
      return date(DATE_ISO8601, $value);
    }
    // It could come from a date field.
    return $value;

  }

}
