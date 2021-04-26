<?php

namespace Drupal\themoviedb_import\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Configure example settings for this site.
 */
class themoviedb_importForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'themoviedb_import.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'themoviedb_import_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['base'] = array(
      '#type' => 'details',
      '#title' => t('TheMovieDB configuration'),
      '#group' => 'advanced',
    );

    $form['base']['themoviedb_api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Url'),
      '#default_value' => $config->get('themoviedb_api_url'),
    ];
    $form['base']['themoviedb_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#default_value' => $config->get('themoviedb_api_key'),
    ];
    $form['base']['themoviedb_movie_url_part'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Movies url part (ex:"/movie/popular" )'),
      '#default_value' => $config->get('themoviedb_movie_url_part'),
    ];
    $form['base']['themoviedb_actor_url_part'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Actor url part (ex:"/movie/popular" )'),
      '#default_value' => $config->get('themoviedb_actor_url_part'),
    ];

    $form['base']['themoviedb_genre_url_part'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Genre url part (ex:"/movie/popular" )'),
      '#default_value' => $config->get('themoviedb_genre_url_part'),
    ];

    $form['base']['themoviedb_count'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Count of pages to consult'),
      '#default_value' => $config->get('themoviedb_count'),
    ];

    $form['base']['themoviedb_language'] = [
      '#type' => 'textfield',
      '#title' => $this->t('language to import data'),
      '#default_value' => $config->get('themoviedb_language'),
    ];

    $form['base']['themoviedbimage_conf'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Image Configuration path image'),
      '#default_value' => $config->get('themoviedbimage_conf'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
// Retrieve the configuration.
    $this->configFactory->getEditable(static::SETTINGS)
// Set the submitted configuration setting.
      ->set('themoviedb_api_url', $form_state->getValue('themoviedb_api_url'))
      ->set('themoviedb_api_key', $form_state->getValue('themoviedb_api_key'))
      ->set('themoviedb_movie_url_part', $form_state->getValue('themoviedb_movie_url_part'))
      ->set('themoviedb_count', $form_state->getValue('themoviedb_count'))
      ->set('themoviedb_genre_url_part', $form_state->getValue('themoviedb_genre_url_part'))
      ->set('themoviedb_language', $form_state->getValue('themoviedb_language'))
      ->set('themoviedb_actor_url_part', $form_state->getValue('themoviedb_actor_url_part'))
      ->set('themoviedbimage_conf', $form_state->getValue('themoviedbimage_conf'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
