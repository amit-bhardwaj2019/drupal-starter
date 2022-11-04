<?php

declare(strict_types = 1);

namespace Drupal\Tests\pluggable_entity_view_builder\Functional;

use Drupal\node\Entity\NodeType;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests a warning message on the Display view modes forms.
 *
 * @group pluggable_entity_view_builder
 */
class EntityViewDisplayEditTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'field_ui',
    'pluggable_entity_view_builder',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests the warning message.
   */
  public function testWarningMessage() {
    // Create article content type.
    $node_type = NodeType::create([
      'type' => 'article',
      'name' => 'Article',
    ]);
    $node_type->save();

    $user = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($user);

    $expected_message = 'Some or all view modes for this entity type and bundle are overridden by the Pluggable entity view builder module';

    // Visit display mode editing of Article content type.
    $this->drupalGet('/admin/structure/types/manage/article/display');
    $web_assert = $this->assertSession();
    $web_assert->pageTextNotContains($expected_message);

    // Enable node override, but no plugin defined yet.
    $this->drupalGet('/admin/config/system/pluggable-entity-view-builder');
    $this->submitForm(['enabled_entity_types[node]' => TRUE], 'Save configuration');
    drupal_flush_all_caches();

    $this->drupalGet('/admin/structure/types/manage/article/display');
    $web_assert = $this->assertSession();
    $web_assert->pageTextNotContains($expected_message);

    // Enable example module, which defines a plugin that overrides Article.
    $this->container->get('module_installer')->install(['pluggable_entity_view_builder_example'], TRUE);

    $this->drupalGet('/admin/structure/types/manage/article/display');
    $web_assert = $this->assertSession();
    $web_assert->pageTextContains($expected_message);
  }

}
