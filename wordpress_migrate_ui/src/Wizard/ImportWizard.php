<?php

namespace Drupal\wordpress_migrate_ui\Wizard;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ctools\Wizard\FormWizardBase;
use Drupal\wordpress_migrate\WordPressMigrationConfigurationTrait;

class ImportWizard extends FormWizardBase {
  
  use WordPressMigrationConfigurationTrait;

  /**
   * {@inheritdoc}
   */
  public function getOperations($cached_values) {
    $steps = [
      'source_select' => [
        'form' => 'Drupal\wordpress_migrate_ui\Form\SourceSelectForm',
        'title' => $this->t('Data source'),
      ],
      'authors' => [
        'form' => 'Drupal\wordpress_migrate_ui\Form\AuthorForm',
        'title' => $this->t('Authors'),
      ],
      'vocabulary_select' => [
        'form' => 'Drupal\wordpress_migrate_ui\Form\VocabularySelectForm',
        'title' => $this->t('Vocabularies'),
      ],
      'content_select' => [
        'form' => 'Drupal\wordpress_migrate_ui\Form\ContentSelectForm',
        'title' => $this->t('Content'),
      ],
    ];
    // Dynamically add the content migration(s) that have been configured by
    // ContentSelectForm.
    if (!empty($cached_values['post']['type'])) {
      $steps += [
        'blog_post' => [
          'form' => 'Drupal\wordpress_migrate_ui\Form\ContentTypeForm',
          'title' => $this->t('Posts'),
          'values' => ['wordpress_content_type' => 'post'],
        ],
      ];
    }
    if (!empty($cached_values['page']['type'])) {
      $steps += [
        'page' => [
          'form' => 'Drupal\wordpress_migrate_ui\Form\ContentTypeForm',
          'title' => $this->t('Pages'),
          'values' => ['wordpress_content_type' => 'page'],
        ],
      ];
    }
    $steps += [
      'review' => [
        'form' => 'Drupal\wordpress_migrate_ui\Form\ReviewForm',
        'title' => $this->t('Review'),
        'values' => ['wordpress_content_type' => ''],
      ],
    ];
    return $steps;
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteName() {
    return 'wordpress_migrate_ui.wizard.import.step';
  }

  /**
   * {@inheritdoc}
   */
  public function finish(array &$form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');
    $this->createMigrations($cached_values);
    // Go to the dashboard for this migration group.
    $form_state->setRedirect('entity.migration.list', ['migration_group' => $cached_values['group_id']]);
    parent::finish($form, $form_state);
  }

}