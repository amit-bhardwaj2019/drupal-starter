<?php

declare(strict_types = 1);

namespace Drupal\Tests\pluggable_entity_view_builder\Kernel;

use Drupal\KernelTests\Core\Entity\EntityLanguageTestBase;
use Drupal\Tests\EntityViewTrait;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Tests to check entity view builder plugins on translated entities.
 *
 * @group pluggable_entity_view_builder
 */
class TranslatedEntityViewBuilderTest extends EntityLanguageTestBase {

  use EntityViewTrait {
    buildEntityView as drupalBuildEntityView;
  }

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'pluggable_entity_view_builder',
    'pluggable_entity_view_builder_test',
    'user',
  ];

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Test entity 1.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $testEntity1;

  /**
   * Test entity 2.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $testEntity2;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    // Note: Parent installs entity_test module.
    parent::setUp();

    // Allow anonymous users to view test entities.
    $this->installConfig(['user']);
    $role = Role::load(RoleInterface::ANONYMOUS_ID);
    $role->grantPermission('view test entity');
    $role->trustData()->save();

    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->renderer = $this->container->get('renderer');
    // Install schema for entity_test_mul (mul: Multilingual).
    $this->installEntitySchema('entity_test_mul');
    // Create some test entities.
    $storage = $this->entityTypeManager->getStorage('entity_test_mul');
    $this->testEntity1 = $storage->create([
      'name' => 'Entity 1: Default translation',
    ]);
    $this->testEntity1->addTranslation($this->langcodes[1], [
      'name' => "Entity 1: {$this->langcodes[1]} translation",
    ]);
    $this->testEntity1->save();

    $this->testEntity2 = $storage->create([
      'name' => 'Entity 2: Default translation',
      'field_reference' => $this->testEntity1->id(),
    ]);
    $this->testEntity2->addTranslation($this->langcodes[1], [
      'name' => "Entity 2: {$this->langcodes[1]} translation",
    ]);
    $this->testEntity2->save();
  }

  /**
   * Test entity view builder plugin output is correctly translated.
   */
  public function testEntityViewBuilderPluginTranslation() {
    // Build entity 1 in default translation.
    $build = $this->drupalBuildEntityView($this->testEntity1);
    $output = (string) $this->renderer->renderRoot($build);
    $this->setRawContent($output);
    // Just the default translation name should be visible.
    $this->assertRaw('Entity 1: Default translation');
    // No references output.
    $this->assertNoRaw('<div class="entity-test-referenced-entities">');
    $this->assertNoRaw("Entity 1: {$this->langcodes[1]} translation");
    $this->assertNoRaw('Entity 2: Default translation');
    $this->assertNoRaw("Entity 2: {$this->langcodes[1]} translation");

    // Build entity 1's translation.
    $build = $this->drupalBuildEntityView($this->testEntity1, 'full', $this->langcodes[1]);
    $output = (string) $this->renderer->renderRoot($build);
    $this->setRawContent($output);
    // Only the translated title should be output.
    $this->assertRaw("Entity 1: {$this->langcodes[1]} translation");
    $this->assertNoRaw('<div class="entity-test-referenced-entities">');
    $this->assertNoRaw('Entity 1: Default translation');
    $this->assertNoRaw('Entity 2: Default translation');
    $this->assertNoRaw("Entity 2: {$this->langcodes[1]} translation");

    // Build entity 2 in default translation.
    $build = $this->drupalBuildEntityView($this->testEntity2);
    $output = (string) $this->renderer->renderRoot($build);
    $this->setRawContent($output);
    // Entity 2 references entity 1 so its title should be output in default
    // translation.
    $this->assertRaw('<div class="entity-test-referenced-entities">');
    $this->assertRaw('Entity 1: Default translation');
    $this->assertRaw('Entity 2: Default translation');
    $this->assertNoRaw("Entity 1: {$this->langcodes[1]} translation");
    $this->assertNoRaw("Entity 2: {$this->langcodes[1]} translation");

    // Build entity 2's translation.
    $build = $this->drupalBuildEntityView($this->testEntity2, 'full', $this->langcodes[1]);
    $output = (string) $this->renderer->renderRoot($build);
    $this->setRawContent($output);
    // Referenced entity 1 title should be output as translated.
    $this->assertRaw('<div class="entity-test-referenced-entities">');
    $this->assertRaw("Entity 1: {$this->langcodes[1]} translation");
    $this->assertRaw("Entity 2: {$this->langcodes[1]} translation");
    $this->assertNoRaw('Entity 1: Default translation');
    $this->assertNoRaw('Entity 2: Default translation');
  }

}
