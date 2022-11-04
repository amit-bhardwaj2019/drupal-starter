<?php

namespace Drupal\intl_date\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for editing a date format.
 *
 * @internal
 */
class DateFormatEditForm extends DateFormatFormBase {

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = t('Save format');
    unset($actions['delete']);
    return $actions;
  }

}
