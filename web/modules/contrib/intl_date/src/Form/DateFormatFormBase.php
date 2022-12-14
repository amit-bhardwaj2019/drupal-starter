<?php

namespace Drupal\intl_date\Form;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityForm;

/**
 * Provides a base form for date formats.
 */
abstract class DateFormatFormBase extends EntityForm {

  /**
   * The entity being used by this form.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityInterface
   */
  protected $entity;

  /**
   * The date format storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   */
  protected $dateFormatStorage;

  /**
   * Constructs a new date format form.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $date_format_storage
   *   The date format storage.
   */
  public function __construct(ConfigEntityStorageInterface $date_format_storage) {
    $this->dateFormatStorage = $date_format_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('intl_date_format')
    );
  }

  /**
   * Checks for an existing date format.
   *
   * @param string|int $entity_id
   *   The entity ID.
   * @param array $element
   *   The form element.
   *
   * @return bool
   *   TRUE if this format already exists, FALSE otherwise.
   */
  public function exists($entity_id, array $element) {
    return (bool) $this->dateFormatStorage
      ->getQuery()
      ->condition('id', $element['#field_prefix'] . $entity_id)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => 'Name',
      '#maxlength' => 100,
      '#description' => $this->t('Name of the date format'),
      '#default_value' => $this->entity->label(),
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#description' => $this->t('A unique machine-readable name. Can only contain lowercase letters, numbers, and underscores.'),
      '#disabled' => !$this->entity->isNew(),
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => [$this, 'exists'],
        'replace_pattern' => '([^a-z0-9_]+)|(^custom$)',
        'error' => $this->t('The machine-readable name must be unique, and can only contain lowercase letters, numbers, and underscores. Additionally, it can not be the reserved word "custom".'),
      ],
    ];
    $form['pattern'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Format string'),
      '#maxlength' => 100,
      '#description' => $this->t('A user-defined date format. See the <a href="https://unicode-org.github.io/icu/userguide/format_parse/datetime/">documentation</a> for available options.'),
      '#required' => TRUE,
      '#default_value' => $this->entity->get('pattern'),
    ];

    $form['langcode'] = [
      '#type' => 'language_select',
      '#title' => $this->t('Language'),
      '#languages' => LanguageInterface::STATE_ALL,
      '#default_value' => $this->entity->language()->getId(),
    ];
    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // The machine name field should already check to see if the requested
    // machine name is available.
    $pattern = trim($form_state->getValue('date_format_pattern'));
    foreach ($this->dateFormatStorage->loadMultiple() as $format) {
      if ($format->getPattern() == $pattern && ($format->id() == $this->entity->id())) {
        $this->messenger()->addStatus($this->t('The existing format/name combination has not been altered.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = $this->entity->save();
    if ($status == SAVED_UPDATED) {
      $this->messenger()->addStatus($this->t('Custom date format updated.'));
    }
    else {
      $this->messenger()->addStatus($this->t('Custom date format added.'));
    }
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
  }

}
