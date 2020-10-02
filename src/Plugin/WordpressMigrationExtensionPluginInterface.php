<?php

namespace Drupal\wordpress_migrate\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\migrate_plus\Entity\MigrationInterface;

/**
 * Defines an interface for WordpressMigrationExtensionPluginBase.
 */
interface WordpressMigrationExtensionPluginInterface extends PluginInspectionInterface {

  /**
   * Undocumented function
   *
   * @param array $config
   *   The configuration of the migration.
   *
   * @return boolean
   *   Returns TRUE if this plugin can be used for this migration.
   */
  public function isActive(array $config);

  /**
   * Function to alter the migration.
   *
   * @param Drupal\migrate_plus\Entity\MigrationInterface $migration
   *   The migration.
   */
  public function alterMigration(MigrationInterface &$migration);

  /**
   * Gets the wordpress types where this plugin can apply.
   *
   * @return array
   *   The wordpress types where this plugin can apply.
   */
  public function getApplicableContent();

}
