<?php

namespace Drupal\wordpress_migrate\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for extending the Wordpress migrations.
 */
abstract class WordpressMigrationExtensionPluginBase extends PluginBase implements WordpressMigrationExtensionPluginInterface, ContainerFactoryPluginInterface {
  /**
   * WordpressMigrationExtensionPluginBase constructor.
   *
   * @param array $configuration
   *   ConfigurationInterface.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isActive(array $config) {
    return TRUE;
  }

}
