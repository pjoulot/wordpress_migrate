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


      $media_types = [
        'image' => [
          'name' => 'Image',
          'source_plugin_id' => 'image',
          'callback' => 'updateImageExtensions',
        ],
        'audio' => [
          'name' => 'Audio',
          'source_plugin_id' => 'audio_file',
          'callback' => 'updateAudioExtensions',
        ],
        'document' => [
          'name' => 'Document',
          'source_plugin_id' => 'file',
          'callback' => 'updateDocumentExtensions',
        ],
      ];

      // @todo Supports more media types like video.

      foreach ($media_types as $media_type_key => $media_type) {
        $form['media']['container'][$media_type_key] = [
          '#type' => 'details',
          '#title' => $media_type['name'],
          '#open' => TRUE,
        ];

        $ajax_wrapper = 'media-' . $media_type_key . '-extensions-wrapper';

        $media_types_options = [
          0 => $this->t("Don't import")
        ];
        $available_media_types = $this->getMediaTypeBySourcePluginId($media_type['source_plugin_id']);
        $media_types_options = array_merge($media_types_options, $available_media_types);
        $form['media']['container'][$media_type_key][$media_type_key .'_media_type'] = [
          '#type' => 'select',
          '#title' => $this->t('Import WordPress @media_types as', ['@media_type' => strtolower($media_type['name'])]),
          '#options' => $media_types_options,
          '#ajax' => [
            'callback' => [$this, $media_type['callback']],
            'event' => 'change',
            'wrapper' => $ajax_wrapper,
            'method' => 'replace',
          ],
        ];

        $form['media']['container'][$media_type_key]['extensions_wrapper'] = [
          '#type' => 'container',
          '#attributes' => [
            'id' => $ajax_wrapper,
          ]
        ];

        $form['media']['container'][$media_type_key]['extensions_wrapper'][$media_type_key . '_media_extensions'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Extensions'),
          '#description' => $this->t('The attachments matching those extensions will be imported as @media_types. If you need to add more, edit your field first.', ['@media_type' => strtolower($media_type['name'])]),
          '#default_value' => '',
        ];
      }

    }

    return $form;
  }

  /**
   * The callback function to update the extensions based on the chosen media type.
   */
  public function updateImageExtensions(array $form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);

    if (!empty($form_state->getValue('image_media_type'))) {
      $default_media_type = !empty($form_state->getValue('image_media_type')) ? $form_state->getValue('image_media_type') : NULL;
      $form['media']['container']['image']['extensions_wrapper']['image_media_extensions']['#required'] = !empty($default_media_type);
      $form['media']['container']['image']['extensions_wrapper']['image_media_extensions']['#value'] = !empty($default_media_type) ? $this->getSourceFieldInfos($default_media_type)['supported_extensions'] : '';
    }

    return $form['media']['container']['image']['extensions_wrapper'];
  }

  /**
   * The callback function to update the extensions based on the chosen media type.
   */
  public function updateAudioExtensions(array $form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);

    if (!empty($form_state->getValue('audio_media_type'))) {
      $default_media_type = !empty($form_state->getValue('audio_media_type')) ? $form_state->getValue('audio_media_type') : NULL;
      $form['media']['container']['audio']['extensions_wrapper']['audio_media_extensions']['#required'] = !empty($default_media_type);
      $form['media']['container']['audio']['extensions_wrapper']['audio_media_extensions']['#value'] = !empty($default_media_type) ? $this->getSourceFieldInfos($default_media_type)['supported_extensions'] : '';
    }

    return $form['media']['container']['audio']['extensions_wrapper'];
  }

  /**
   * The callback function to update the extensions based on the chosen media type.
   */
  public function updateDocumentExtensions(array $form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);

    if (!empty($form_state->getValue('document_media_type'))) {
      $default_media_type = !empty($form_state->getValue('document_media_type')) ? $form_state->getValue('document_media_type') : NULL;
      $form['media']['container']['document']['extensions_wrapper']['document_media_extensions']['#required'] = !empty($default_media_type);;
      $form['media']['container']['document']['extensions_wrapper']['document_media_extensions']['#value'] = !empty($default_media_type) ? $this->getSourceFieldInfos($default_media_type)['supported_extensions'] : '';
    }

    return $form['media']['container']['document']['extensions_wrapper'];
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

  public function getSourceFieldInfos($media_type) {
    if (!empty($media_type)) {
      $media_type = $this->entityTypeManager->getStorage('media_type')->load($media_type);
      $field_definition = $media_type->getSource()->getSourceFieldDefinition($media_type);
      return [
        'name' => $field_definition->getName(),
        'supported_extensions' => $field_definition->getSetting('file_extensions'),
      ];
    }
    return [
      'name' => '',
      'supported_extensions' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');
    $cached_values['default_destination'] = $form_state->getValue('default_destination');
    $cached_values['use_media'] = $form_state->getValue('use_media');
    $cached_values['image_media_type'] = $form_state->getValue('image_media_type');
    $cached_values['image_media_type_field'] = $this->getSourceFieldInfos($cached_values['image_media_type'])['name'];
    $cached_values['image_media_extensions'] = $form_state->getValue('image_media_extensions');
    $cached_values['audio_media_type'] = $form_state->getValue('audio_media_type');
    $cached_values['audio_media_type_field'] = $this->getSourceFieldInfos($cached_values['audio_media_type'])['name'];
    $cached_values['audio_media_extensions'] = $form_state->getValue('audio_media_extensions');
    $cached_values['document_media_type'] = $form_state->getValue('document_media_type');
    $cached_values['document_media_type_field'] = $this->getSourceFieldInfos($cached_values['document_media_type'])['name'];
    $cached_values['document_media_extensions'] = $form_state->getValue('document_media_extensions');
    $form_state->setTemporaryValue('wizard', $cached_values);
  }

}
