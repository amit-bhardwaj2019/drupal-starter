<?php

declare(strict_types = 1);

namespace Drupal\Tests\pluggable_entity_view_builder\Functional;

use Drupal\node\Entity\NodeType;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests contextual links.
 *
 * @group pluggable_entity_view_builder
 */
class ContextualLinksTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'field_ui',
    'pluggable_entity_view_builder',
    'pluggable_entity_view_builder_example',
    'contextual',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests contextual links.
   */
  public function testContextualLinks() {
    // Create article content type.
    $node_type = NodeType::create([
      'type' => 'article',
      'name' => 'Article',
    ]);
    $node_type->save();

    $node = $this->drupalCreateNode([
      'type' => 'article',
      'title' => 'Article',
    ]);
    $node->save();

    $this->drupalGet($node->toUrl());
    $this->assertSession()->pageTextContains('Article');
    $this->assertSession()->elementNotExists('css', 'article.contextual-region');

    $user = $this->drupalCreateUser(['access contextual links'], NULL, TRUE);
    $this->drupalLogin($user);

    $this->drupalGet($node->toUrl());
    $this->assertSession()->pageTextContains('Article');
    $this->assertSession()->elementExists('css', 'article.contextual-region');
  }

}
