<?php

namespace Drupal\wordpress_migrate\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Wordpress Migration Extension plugin plugin manager.
 */
class WordpressMigrationExtensionPluginManager extends DefaultPluginManager {

  /**
   * Constructs a new WordpressMigrationExtensionPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/WordpressMigrationExtension', $namespaces, $module_handler, 'Drupal\wordpress_migrate\Plugin\WordpressMigrationExtensionPluginInterface', 'Drupal\wordpress_migrate\Annotation\WordpressMigrationExtensionPlugin');

    $this->alterInfo('wordpress_migrate_wordpress_migration_extension_plugin_info');
    $this->setCacheBackend($cache_backend, 'wordpress_migrate_wordpress_migration_extension_plugin_plugins');
  }

}
