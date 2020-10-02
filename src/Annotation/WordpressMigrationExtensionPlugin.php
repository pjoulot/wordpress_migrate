<?php

namespace Drupal\wordpress_migrate\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Wordpress Migration Extension plugin item annotation object.
 *
 * @see \Drupal\wordpress_migrate\Plugin\WordpressMigrationExtensionPluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class WordpressMigrationExtensionPlugin extends Plugin {


  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The description of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

}
