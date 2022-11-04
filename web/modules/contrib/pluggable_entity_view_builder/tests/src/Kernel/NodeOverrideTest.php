<?php

declare(strict_types = 1);

namespace Drupal\Tests\pluggable_entity_view_builder\Kernel;

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\EntityViewTrait;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Tests overriding node's entity view builder.
 *
 * Copied and adapted from \Drupal\Tests\node\Kernel\SummaryLengthTest.
 *
 * @group pluggable_entity_view_builder
 */
class NodeOverrideTest extends KernelTestBase {

  use EntityViewTrait {
    buildEntityView as drupalBuildEntityView;
  }

  use ContentTypeCreationTrait {
    createContentType as drupalCreateContentType;
  }

  use NodeCreationTrait {
    createNode as drupalCreateNode;
  }

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'datetime',
    'user',
    'node',
    'system',
    'field',
    'filter',
    'text',
    'pluggable_entity_view_builder',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema('system', ['sequences']);
    $this->installEntitySchema('date_format');
    $this->installConfig('filter');
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installConfig(['node']);

    // Enable Node override, and clear cache.
    $this->container->get('config.factory')->getEditable('pluggable_entity_view_builder.settings')->set('enabled_entity_types', ['node'])->save();

    // Create article content type.
    $this->drupalCreateContentType([
      'type' => 'article',
      'name' => 'Article',
      'display_submitted' => FALSE,
    ]);

    DateFormat::create([
      'id' => 'fallback',
      'label' => 'Fallback',
      'pattern' => 'Y-m-d',
    ])->save();
  }

  /**
   * Tests the render array result of an overridden bundle.
   */
  public function testRenderArray() {
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = $this->container->get('renderer');

    $settings = [
      'title' => 'This is the default title',
      'body' => 'This is the default body',
      'type' => 'article',
    ];

    $wrapper_class_expected = 'hero-header-wrapper';
    $classes_expected = 'w-full h-64 bg-cover';

    // Assert that if no plugin is defined then it falls back to the default
    // build.
    $node = $this->drupalCreateNode($settings);
    $build = $this->drupalBuildEntityView($node);
    $this->assertTrue(in_array('#cache', array_keys($build)));

    $output = (string) $renderer->renderRoot($build);
    $this->setRawContent($output);
    $this->assertNoRaw($wrapper_class_expected);
    $this->assertNoRaw($classes_expected);

    // Enable the example module, and create a new node.
    $this->enableModules(['pluggable_entity_view_builder_example']);

    $node = $this->drupalCreateNode($settings);
    $build = $this->drupalBuildEntityView($node);
    $this->assertTrue(in_array('#cache', array_keys($build)));

    $output = (string) $renderer->renderRoot($build);
    $this->setRawContent($output);
    $this->assertRaw($wrapper_class_expected);
    $this->assertRaw($classes_expected);
  }

}
