<?php

namespace Drupal\pluggable_entity_view_builder;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;

/**
 * Common methods for all field-able content entities' view builders.
 *
 * @property \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
 */
trait BuildFieldTrait {

  /**
   * Get the value of a text field on an entity as a string.
   *
   * Suitable for plain text fields mostly, but can work with any fields which
   * return string values.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param string $field_name
   *   The field name to get the value of.
   *
   * @return string
   *   The value or an empty string if the field is empty.
   */
  protected function getTextFieldValue(ContentEntityInterface $entity, string $field_name): string {
    if ($entity->get($field_name)->isEmpty()) {
      return '';
    }
    return $entity->get($field_name)->getString();
  }

  /**
   * Get the value of a text list field on an entity as a string.
   *
   * Suitable for text list fields.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param string $field_name
   *   The field name to get the value of.
   *
   * @return string
   *   The value or an empty string if the field is empty.
   *   Multiple values will be concatenated with a comma (e.g. "foo, bar, baz").
   */
  protected function getTextListFieldLabelValue(ContentEntityInterface $entity, string $field_name): string {
    if ($entity->get($field_name)->isEmpty()) {
      return '';
    }
    $settings = $entity->get($field_name)->getSetting('allowed_values');
    $plain_values = $entity->get($field_name)->getValue();
    $values = [];
    foreach ($plain_values as $value) {
      $value = $value['value'];
      // In some cases, for instance when an item is removed from the allowed
      // values, the value that we seek is missing.
      // If the setting does not exist for any reason, let's just
      // act like the getTextFieldValue().
      if (empty($settings) || !isset($settings[$value])) {
        $values[] = $value;
      }
      else {
        $values[] = $settings[$value];
      }
    }
    return implode(', ', $values);
  }

  /**
   * Get the value of a boolean field on an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param string $field_name
   *   The field name to get the value of.
   *
   * @return bool
   *   Boolean value.
   */
  protected function getBooleanFieldValue(ContentEntityInterface $entity, string $field_name): bool {
    return boolval($this->getTextFieldValue($entity, $field_name));
  }

  /**
   * Get the formatted date from an entity's date field.
   *
   * Note that this is compatible with a single date field, not with date_range.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param string $field_name
   *   The name of the single date field.
   * @param string $date_format
   *   The date format for the output. Must be a PHP date format string.
   *
   * @return string
   *   The formatted date string, or an empty string if the field has no value.
   */
  protected function getDateFieldValue(ContentEntityInterface $entity, string $field_name, string $date_format): string {
    if ($entity->get($field_name)->isEmpty()) {
      return '';
    }
    /** @var \Drupal\datetime\Plugin\Field\FieldType\DateTimeFieldItemList $date_field */
    $date_field = $entity->get($field_name);
    if (!isset($date_field->date)) {
      return '';
    }
    /** @var \Drupal\Core\Datetime\DrupalDateTime $date */
    $date = $date_field->date;
    return $date->format($date_format);
  }

  /**
   * Get the URL and title of a link field on an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param string $field_name
   *   The link field name.
   *
   * @return array|null
   *   Array containing 2 items: The URL to of the link, and the link title
   *   respectively.
   */
  protected function getLinkFieldValue(ContentEntityInterface $entity, string $field_name): ?array {
    if ($entity->get($field_name)->isEmpty()) {
      return NULL;
    }
    $value = $entity->get($field_name)->getValue();
    $url = Url::fromUri($value[0]['uri']);
    if (!$url->access()) {
      return NULL;
    }
    $title = $value[0]['title'];

    return [
      'url' => $url,
      'title' => $title,
    ];
  }

  /**
   * Get the array with the (styled) URL of an image on a media field.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param string $field_name
   *   The media field name.
   * @param string $image_style
   *   Optional; The image style to use. If none then original image URL is
   *   returned.
   *
   * @return array
   *   An array containing the keys `url`, `uri` and `alt`.
   */
  protected function getMediaImageAndAlt(ContentEntityInterface $entity, string $field_name, string $image_style = ''): array {
    if ($entity->get($field_name)->isEmpty()) {
      return [];
    }

    // Get the Media entity.
    /** @var \Drupal\media\MediaInterface $media */
    $media = $this->getReferencedEntityFromField($entity, $field_name);
    if (empty($media)) {
      // No media found, or no access to view it.
      return [];
    }

    // Get the Image entity from Media.
    /** @var \Drupal\file\FileInterface $image */
    $image = $this->getReferencedEntityFromField($media, 'field_media_image');

    if (empty($image)) {
      // No image found, or no access to view it.
      return [];
    }

    // Get image alt.
    $field = $this->getImageFieldFromMediaEntity($entity, $field_name);
    $alt = !empty($field) && !empty($field->getValue()[0]['alt']) ? $field->getValue()[0]['alt'] : '';

    // Generate image url or image style url if we have it.
    if (!empty($image_style)) {
      // Get the image style.
      /** @var \Drupal\image\ImageStyleInterface $image_style */
      $style = ImageStyle::load($image_style);
      $url = $style->buildUrl($image->getFileUri());
    }
    else {
      $url = file_create_url($image->getFileUri());
    }

    return [
      'uri' => $image->getFileUri(),
      'url' => $url,
      'alt' => $alt,
    ];
  }

  /**
   * Get the image field from a media entity on an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param string $field_name
   *   The name of the media field.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface|null
   *   The image field on the media entity.
   */
  protected function getImageFieldFromMediaEntity(ContentEntityInterface $entity, string $field_name): ?FieldItemListInterface {
    // Get the media entity.
    /** @var \Drupal\media\MediaInterface $media */
    $media = $this->getReferencedEntityFromField($entity, $field_name);
    if (empty($media) || $media->field_media_image->isEmpty() || empty($media->field_media_image->entity)) {
      return NULL;
    }
    return $media->field_media_image;
  }

  /**
   * Get a referenced entity from a field.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param string $field_name
   *   The reference field name.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The referenced entity or null if the field is empty or the entity isn't
   *   found, or the current user has no access to view the entity.
   */
  protected function getReferencedEntityFromField(ContentEntityInterface $entity, string $field_name): ?EntityInterface {
    // Get the media entity.
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $field */
    $field = $entity->get($field_name);
    if ($field->isEmpty() || empty($field->entity) || !$field->entity->access('view')) {
      // Field is empty or referenced entity has been deleted, or no access.
      return NULL;
    }
    return $field->entity;
  }

  /**
   * Build an image referenced in the given entity's given field name.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param string $field_name
   *   Optional; The field name. Defaults to "field_image".
   *
   * @return array
   *   An array containing url and alt.
   */
  protected function buildImage(ContentEntityInterface $entity, string $field_name = 'field_image'): array {
    $image_info = $this->getImageAndAlt($entity, $field_name);
    if (!$image_info) {
      return [];
    }

    return [
      '#theme' => 'image',
      '#uri' => $image_info['uri'],
      '#alt' => $image_info['alt'],
    ];
  }

  /**
   * Build a styled image referenced in the given entity's given field name.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param string $image_style
   *   The image style.
   * @param string $field_name
   *   Optional; The field name. Defaults to "field_image".
   *
   * @return array
   *   Render array.
   */
  protected function buildImageStyle(ContentEntityInterface $entity, string $image_style, string $field_name = 'field_image'): array {
    $image_info = $this->getImageAndAlt($entity, $field_name);
    if (!$image_info) {
      return [];
    }

    return [
      '#theme' => 'image_style',
      '#style_name' => $image_style,
      '#uri' => $image_info['uri'],
      '#alt' => $image_info['alt'],
    ];
  }

  /**
   * Get image and alt from a File field.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param string $field_name
   *   Optional; The field name. Defaults to "field_image".
   * @param string $image_style
   *   Optional; The image style to use. If none then original image URL is
   *   returned.
   *
   * @return array|null
   *   An array containing uri, url and alt.
   */
  protected function getImageAndAlt(ContentEntityInterface $entity, string $field_name = 'field_image', string $image_style = ''): ?array {
    if (empty($entity->{$field_name}) || $entity->get($field_name)->isEmpty()) {
      // No field, or it's empty.
      return NULL;
    }

    /** @var \Drupal\file\FileInterface $image */
    $image = $this->getReferencedEntityFromField($entity, $field_name);
    if (empty($image)) {
      // Image doesn't exist, or no access to it.
      return NULL;
    }

    $uri = $image->getFileUri();

    if (!empty($image_style)) {
      // Get the image style.
      /** @var \Drupal\image\ImageStyleInterface $image_style */
      $style = ImageStyle::load($image_style);
      $url = $style->buildUrl($uri);
    }
    else {
      $url = file_create_url($uri);
    }

    return [
      'uri' => $uri,
      'url' => $url,
      'alt' => $entity->get($field_name)[0]->alt ?: '',
    ];
  }

  /**
   * Build a responsive image render array from an entity's media field.
   *
   * Note: Responsive Image core module is required for this to work.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param string $field_name
   *   The field name.
   * @param string $responsive_image_style_id
   *   The responsive image style ID.
   *
   * @return array
   *   A render array or empty array if no value in field or no access to view
   *   the image.
   */
  protected function buildMediaResponsiveImage(ContentEntityInterface $entity, string $field_name, string $responsive_image_style_id): array {
    if ($entity->get($field_name)->isEmpty()) {
      return [];
    }
    /** @var \Drupal\media\MediaInterface $media */
    $media = $this->getReferencedEntityFromField($entity, $field_name);
    if (empty($media)) {
      // No media to display.
      return [];
    }

    /** @var \Drupal\file\FileInterface $image */
    $image = $this->getReferencedEntityFromField($media, 'field_media_image');
    if (empty($image)) {
      // Image doesn't exist, or no access to it.
      return [];
    }

    $image_and_alt = $this->getMediaImageAndAlt($entity, $field_name);
    $element = [
      '#theme' => 'responsive_image',
      '#uri' => $image->getFileUri(),
      '#responsive_image_style_id' => $responsive_image_style_id,
      '#attributes' => [
        'alt' => $image_and_alt['alt'],
      ],
    ];
    CacheableMetadata::createFromRenderArray($element)
      ->addCacheableDependency($media)
      ->addCacheableDependency($image)
      ->applyTo($element);
    return $element;
  }

  /**
   * Build entities in given view mode.
   *
   * @param \Drupal\Core\Entity\EntityInterface[] $entities
   *   An array of entities.
   * @param string $view_mode
   *   Optional; The view mode to build. Defaults to "full".
   * @param string|null $langcode
   *   Optional; For which language the entity should be rendered, defaults to
   *   the current content language.
   *
   * @return array[]
   *   Array of render arrays.
   */
  protected function buildEntities(array $entities = [], string $view_mode = 'full', string $langcode = NULL): array {
    if (!$entities) {
      return [];
    }

    $element = [];

    foreach ($entities as $entity) {
      if (!$entity->access('view')) {
        // No access to view the entity.
        continue;
      }
      $view_builder = $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId());
      $element[] = $view_builder->view($entity, $view_mode, $langcode);
    }

    return $element;
  }

  /**
   * Build entities in given view mode, from a reference field.
   *
   * @param \Drupal\Core\Field\EntityReferenceFieldItemListInterface|null $reference_field
   *   The field object where the referenced items are stored.
   * @param string $view_mode
   *   Optional; The view mode to build. Defaults to "full".
   * @param string|null $langcode
   *   Optional; The language code to render the referenced entities in.
   *   Defaults to the default language if empty.
   *
   * @return array[]
   *   Array of render arrays.
   */
  protected function buildReferencedEntities(EntityReferenceFieldItemListInterface $reference_field = NULL, string $view_mode = 'full', string $langcode = NULL): array {
    if (empty($reference_field) || $reference_field->isEmpty()) {
      // Field doesn't exist, or is empty.
      return [];
    }

    return $this->buildEntities($reference_field->referencedEntities(), $view_mode, $langcode);
  }

}
