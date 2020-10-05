<?php

namespace Drupal\wordpress_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Apply the automatic paragraph filter to content
 *
 * @MigrateProcessPlugin(
 *   id = "wp_content"
 * )
 * @codingStandardsIgnoreStart
 *
 * Simple example:
 * @code
 * field_text:
 *   plugin: wp_content
 *   source: text
 *   filter_autop: true
 * @endcode
 *
 * @codingStandardsIgnoreEnd
 */
class WpContent extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $text = $value;
    if (\Drupal::moduleHandler()->moduleExists('filter') && $this->configuration['filter_autop']) {
      $text = _filter_autop($text);
    }
    return $text;
  }
}
