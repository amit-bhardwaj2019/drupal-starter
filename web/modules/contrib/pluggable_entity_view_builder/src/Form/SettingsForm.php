<?php

namespace Drupal\pluggable_entity_view_builder\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Pluggable entity view builder settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $build = parent::create($container);
    $build->moduleHandler = $container->get('module_handler');
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pluggable_entity_view_builder_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['pluggable_entity_view_builder.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $default_values = $this->config('pluggable_entity_view_builder.settings')->get('enabled_entity_types');
    $form['enabled_entity_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Enabled entity types'),
      '#description' => $this->t('Override the Entity view builder of any of the following core entities. If you would like to override other entities, or have different implementation you should implement your custom hook similar to <code>pluggable_entity_view_builder_entity_type_alter</code>.'),
      '#options' => [
        'block_content' => $this->t('Block content'),
        'comment' => $this->t('Comment'),
        'media' => $this->t('Media'),
        'node' => $this->t('Node'),
        'taxonomy_term' => $this->t('Taxonomy term'),
        'user' => $this->t('User'),
      ],
      '#default_value' => $default_values ?: [],
    ];

    // Integrate with Paragraphs if it's enabled.
    if ($this->moduleHandler->moduleExists('paragraphs')) {
      $form['enabled_entity_types']['#options']['paragraph'] = $this->t('Paragraph');
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('pluggable_entity_view_builder.settings')
      ->set('enabled_entity_types', $form_state->getValue('enabled_entity_types'))
      ->save();

    $this->messenger()->addMessage($this->t('<a href="@url">Clear cache</a> for new settings to be available.', [
      '@url' => Url::fromRoute('system.performance_settings')->toString(),
    ]));

    parent::submitForm($form, $form_state);
  }

}
