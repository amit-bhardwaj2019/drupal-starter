<?php

namespace Drupal\intl_date\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\TimestampFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\intl_date\IntlDate;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'intl_timestamp' formatter.
 *
 * @FieldFormatter(
 *   id = "intl_timestamp",
 *   label = @Translation("Intl Default"),
 *   field_types = {
 *     "timestamp",
 *     "created",
 *     "changed",
 *   }
 * )
 */
class IntlTimestampFormatter extends TimestampFormatter {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('date.formatter'),
      $container->get('entity_type.manager')->getStorage('intl_date_format')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'date_format' => 'medium',
      'timezone' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $date_formats = [];

    foreach ($this->dateFormatStorage->loadMultiple() as $machine_name => $value) {
      $date_formats[$machine_name] = $this->t('@name format: @date', [
        '@name' => $value->label(),
        '@date' => $this->formatTimestamp(\Drupal::time()->getRequestTime(), $machine_name),
      ]);
    }

    $elements['date_format'] = [
      '#type' => 'select',
      '#title' => $this->t('Date format'),
      '#options' => $date_formats,
      '#default_value' => $this->getSetting('date_format') ?: 'medium',
    ];

    $elements['timezone'] = [
      '#type' => 'select',
      '#title' => $this->t('Time zone'),
      '#options' => ['' => $this->t('- Default site/user time zone -')] + system_time_zones(FALSE, TRUE),
      '#default_value' => $this->getSetting('timezone'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $date_format = $this->getSetting('date_format');
    $summary[] = $this->t('Date format: @date_format', ['@date_format' => $date_format]);
    if ($timezone = $this->getSetting('timezone')) {
      $summary[] = $this->t('Time zone: @timezone', ['@timezone' => $timezone]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $date_format = $this->getSetting('date_format');

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#cache' => [
          'contexts' => [
            'timezone',
          ],
        ],
        '#markup' => $this->formatTimestamp($item->value, $date_format),
      ];
    }

    return $elements;
  }

  /**
   * Formats timestamp.
   */
  protected function formatTimestamp(int $timestamp, $format_type = NULL) {
    if (empty($format_type)) {
      $format_type = $this->getSetting('date_format');
    }
    $date_format = $this->dateFormatStorage->load($format_type);
    $timezone = $this->getSetting('timezone_override') ?: '';
    if (empty($date_format)) {
      return '';
    }
    return IntlDate::format($timestamp, $date_format->get('pattern'), NULL, $timezone);
  }

}
