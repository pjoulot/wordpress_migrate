<?php

namespace Drupal\wordpress_migrate_ui\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Simple wizard step form.
 */
class ExtensionSelectForm extends FormBase {

  /**
   * Drupal\wordpress_migrate\Plugin\WordpressMigrationExtensionPluginManager definition.
   *
   * @var \Drupal\wordpress_migrate\Plugin\WordpressMigrationExtensionPluginManager
   */
  protected $pluginWordpressMigrationExtension;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->pluginWordpressMigrationExtension = $container->get('plugin.manager.wordpress_migration_extension_plugin');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wordpress_migrate_extension_select_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Start clean in case we came here via Previous.
    $cached_values = $form_state->getTemporaryValue('wizard');
    unset($cached_values['extensions']);
    $form_state->setTemporaryValue('wizard', $cached_values);

    $plugins = $this->pluginWordpressMigrationExtension->getDefinitions();

    $form['overview'] = [
      '#markup' => $this->t('Here you may choose to import additional data from the supported Wordpress plugins or from your custom wordpress_migrate plugins.'),
    ];

    $options = [];
    foreach ($plugins as $plugin) {
      $options[$plugin['id']] = ' <b>' . $plugin['label'] . '</b> - <em>' . $plugin['description'] . '</em>';
    }

    $form['extensions'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Activate these options to import additional data:'),
      '#options' => $options,
    ];

    if (empty($options)) {
      $form['extensions_message'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('No additional data has been detected.'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');
    $cached_values['extensions'] = $form_state->getValue('extensions');
    $form_state->setTemporaryValue('wizard', $cached_values);
  }

}
