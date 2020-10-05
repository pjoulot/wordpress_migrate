<?php

namespace Drupal\wordpress_migrate_ui\Form;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Simple wizard step form.
 */
class ImageSelectForm extends FormBase {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldeManager;

  /**
   * Constructs a ImageSelectForm object.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   */
  public function __construct(EntityFieldManagerInterface $entity_field_manager) {
    $this->entityFieldeManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wordpress_migrate_image_select_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Start clean in case we came here via Previous.
    $cached_values = $form_state->getTemporaryValue('wizard');
    unset($cached_values['image_field']);
    $form_state->setTemporaryValue('wizard', $cached_values);

    $form['overview'] = [
      '#markup' => $this->t('Here you may choose the Drupal image field to import Wordpress featured images into.'),
    ];

    $fields = [];
    if (!empty($cached_values['post']['type'])) {
      $fields = $this->entityFieldeManager->getFieldDefinitions('node', $cached_values['post']['type']);
    }
    $options = ['' => $this->t('Do not import')];
    foreach($fields as $field) {
      if (!empty($cached_values['image_media_type']) && $field->getType() == 'entity_reference' && $field->getSettings()['target_type'] === 'media') {
        $options[$field->getName()] = $field->getLabel();
      }
      elseif (empty($cached_values['image_media_type']) && $field->getType() == 'image') {
        $options[$field->getName()] = $field->getLabel();
      }
    }

    $form['image_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Import WordPress featured images in'),
      '#options' => $options,
    ];

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
    $cached_values['image_field'] = $form_state->getValue('image_field');
    $form_state->setTemporaryValue('wizard', $cached_values);
  }

}
