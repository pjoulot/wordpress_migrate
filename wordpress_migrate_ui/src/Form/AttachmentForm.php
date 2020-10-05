<?php

namespace Drupal\wordpress_migrate_ui\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Simple wizard step form.
 */
class AttachmentForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a AttachmentForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler) {
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wordpress_migrate_attachment_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['files'] = [
      '#type' => 'details',
      '#title' => t('Files'),
      '#open' => TRUE,
    ];

    $form['files']['default_destination'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default destination'),
      '#default_value' => 'public://attachments_files',
      '#description' => $this->t('Define the default destination when importing the files.'),
    ];

    if ($this->moduleHandler->moduleExists('media')) {
      $form['media'] = [
        '#type' => 'details',
        '#title' => t('Media'),
        '#open' => TRUE,
      ];

      $form['media']['use_media'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Do you want to import attachments as Media?'),
        '#default_value' => FALSE,
      ];

      $form['media']['container'] = [
        '#type' => 'container',
        '#states' => [
          'visible' => [
            ':input[name="use_media"]' => ['checked' => TRUE],
          ],
        ],
      ];

      $form['media']['container']['image'] = [
        '#type' => 'details',
        '#title' => t('Image'),
        '#open' => TRUE,
      ];

      $media_types[] = $this->t("Don't import");
      $media_types = array_merge($media_types, $this->getMediaTypeBySourcePluginId('image'));
      $form['media']['container']['image']['image_media_type'] = [
        '#type' => 'select',
        '#title' => $this->t('Import WordPress images as'),
        '#options' => $media_types,
      ];

      // @todo Supports more media types like document, audio or video.

    }

    return $form;
  }

  /**
   * Get the list of the media types by source plugin ID.
   *
   * @param string $id
   *   The source plugin ID.
   *
   * @return array
   *   The list of the media types.
   */
  function getMediaTypeBySourcePluginId($id) {
    $types = [];
    $media_types = $this->entityTypeManager->getStorage('media_type')->loadMultiple();
    foreach ($media_types as $media_type => $info) {
      $source_id = $info->getSource()->getPluginId();
      if ($source_id === $id) {
        $types[$media_type] = $info->get('label');
      }
    }
    return $types;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');
    $cached_values['default_destination'] = $form_state->getValue('default_destination');
    $cached_values['use_media'] = $form_state->getValue('use_media');
    $cached_values['image_media_type'] = $form_state->getValue('image_media_type');
    $form_state->setTemporaryValue('wizard', $cached_values);
  }

}
