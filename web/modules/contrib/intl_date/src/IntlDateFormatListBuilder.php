<?php

namespace Drupal\intl_date;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines a class to build a listing of Intl Date Format entities.
 *
 * @see \Drupal\system\Entity\DateFormat
 */
class IntlDateFormatListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = t('Name');
    $header['pattern'] = t('Pattern');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    if (!$entity instanceof ConfigEntityInterface) {
      return [];
    }
    $row['label'] = $entity->label();
    $row['pattern'] = IntlDate::format(\Drupal::time()->getRequestTime(), $entity->get('pattern'));
    return $row + parent::buildRow($entity);
  }

}
