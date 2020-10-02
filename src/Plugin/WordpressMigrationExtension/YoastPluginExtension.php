<?php

namespace Drupal\wordpress_migrate\Plugin\WordpressMigrationExtension;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate_plus\Entity\MigrationInterface;
use Drupal\wordpress_migrate\Annotation\WordpressMigrationExtensionPlugin;
use Drupal\wordpress_migrate\Plugin\WordpressMigrationExtensionPluginBase;

/**
 * Provider plugin for storing the activity log in Drupal entities.
 *
 * @WordpressMigrationExtensionPlugin(
 *   id="wordpress_yoast_extension",
 *   label="Yoast Plugin",
 *   description="Import the metadata from the yoast wordpress plugin into the metatags field of Drupal."
 * )
 */
class YoastPluginExtension extends WordpressMigrationExtensionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function isActive(array $config) {
    $moduleHandler = \Drupal::service('module_handler');
    if ($moduleHandler->moduleExists('metatag')) {
      // Look if the wordpress XML contains yoast plugin data.
      $file_url = file_create_url($config['file_uri']);
      $content = file_get_contents($file_url);
      return strpos($content, '_yoast_wpseo_title') !== FALSE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function alterMigration(MigrationInterface &$migration) {
    // Before altering the migration, we need to find the metatags field.
    $process = $migration->get('process');
    $bundle = $process['type']['default_value'];
    $metatag_fields = $this->findFieldByFieldType('node', $bundle, 'metatag');

    foreach ($metatag_fields as $metatag_field) {
      $source = $migration->get('source');
      $source['fields'][] = [
        'name' => 'yoast_wpseo_title',
        'label' => 'Yoast SEO Title',
        'selector' => 'wp:postmeta[wp:meta_key=\'_yoast_wpseo_title\']/wp:meta_value',
      ];
      $source['fields'][] = [
        'name' => 'yoast_wpseo_metadesc',
        'label' => 'Yoast SEO Metadesc',
        'selector' => 'wp:postmeta[wp:meta_key=\'_yoast_wpseo_metadesc\']/wp:meta_value',
      ];
      $migration->set('source', $source);

      $process = $migration->get('process');
      $process['metatags/0/title'] = 'yoast_wpseo_title';
      $process['metatags/0/description'] = 'yoast_wpseo_metadesc';
      $process[$metatag_field][] = [
        'plugin' => 'callback',
        'callable' => 'serialize',
        'source' => '@metatags',
      ];
      $migration->set('process', $process);
    }

    // Add support for the yoast_seo fields.
    $moduleHandler = \Drupal::service('module_handler');
    if ($moduleHandler->moduleExists('yoast_seo')) {
      $metatag_fields = $this->findFieldByFieldType('node', $bundle, 'yoast_seo');
      foreach ($metatag_fields as $metatag_field) {
        $source = $migration->get('source');
        $source['fields'][] = [
          'name' => 'yoast_focus_keyword',
          'label' => 'Yoast Focus Keyword',
          'selector' => 'wp:postmeta[wp:meta_key=\'_yoast_wpseo_focuskw\']/wp:meta_value',
        ];
        $migration->set('source', $source);

        $process = $migration->get('process');
        $process['field_seo/0/focus_keyword'] = 'yoast_focus_keyword';
        $migration->set('process', $process);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getApplicableContent() {
    return ['post', 'page'];
  }

  /**
   * Function to find a field by type.
   *
   * @param string $entity_type
   *  The entity type.
   * @param string $bundle
   *   The entity bundle.
   * @param string $field_type
   *   The field type.
   *
   * @return array
   *   An array of the fields that match the criteria.
   */
  function findFieldByFieldType($entity_type, $bundle, $field_type) {
    $entityFieldManager = \Drupal::service('entity_field.manager');
    $fields = $entityFieldManager->getFieldDefinitions($entity_type, $bundle);

    $field_names = [];
    foreach ($fields as $field_definition) {
      if ($field_definition->getType() === $field_type) {
        $field_names[] = $field_definition->getName();
      }
    }
    return $field_names;
  }

}
