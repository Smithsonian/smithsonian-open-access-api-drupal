<?php

namespace Drupal\smithsonian_open_access\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\smithsonian_open_access\Api;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Configure Smithsonian Open Access settings for this site.
 */
class SmithsonianOpenAccessSettingsForm extends ConfigFormBase {

  protected Api $api;

  /**
   * Class constructor.
   *
   * @param Api $api
   *   The Smithsonian Open Access API service.
   */
  public function __construct(\Drupal\smithsonian_open_access\Api $api) {
    $this->api = $api;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('smithsonian_open_access.api')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'smithsonian_open_access_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'smithsonian_open_access.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $config = $this->config('smithsonian_open_access.settings');

    $form['api_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('API Settings'),
    ];

    $form['api_settings']['base_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base URI'),
      '#default_value' => $config->get('base_uri') ?: 'https://api.si.edu/openaccess/api/v1.0/',
      '#required' => TRUE,
      '#description' => $this->t('URI that includes the version of the API. For example: https://api.si.edu/openaccess/api/v1.0/'),
    ];

    $form['api_settings']['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#default_value' => $config->get('api_key'),
      '#required' => TRUE,
      '#description' => $this->t('An API key can be obtained from https://data.gov'),
    ];

    $form['api_endpoints'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('API Endpoints'),
    ];

    $form['api_endpoints']['search_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search Endpoint'),
      '#default_value' => $config->get('search_endpoint') ?: 'search',
      '#required' => TRUE,
      '#description' => $this->t('The Search endpoint allows you to search the collection. Returns a set of collection records. <a href="https://edan.si.edu/openaccess/apidocs/#api-search-search" target="_blank">See the Search endpoint documentation</a>.'),
    ];

    $form['api_endpoints']['test_search_endpoint'] = [
      '#type' => 'button',
      '#value' => $this->t('Test Search Endpoint'),
      '#ajax' => [
        'callback' => '::testSearchEndpoint',
        'wrapper' => 'search_endpoint_test_result',
      ],
    ];

    $form['api_endpoints']['search_endpoint_test_result'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'search_endpoint_test_result'],
    ];

    $form['api_endpoints']['content_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Content Endpoint'),
      '#default_value' => $config->get('content_endpoint') ?: 'content',
      '#required' => TRUE,
      '#description' => $this->t('The Content endpoint allows you to get detailed information about an object. <a href="https://edan.si.edu/openaccess/apidocs/#api-content-content" target="_blank">See the Content endpoint documentation</a>.'),
    ];

    $form['api_endpoints']['test_content_endpoint'] = [
      '#type' => 'button',
      '#value' => $this->t('Test Content Endpoint'),
      '#ajax' => [
        'callback' => '::testContentEndpoint',
        'wrapper' => 'content_endpoint_test_result',
      ],
    ];

    $form['api_endpoints']['content_endpoint_test_result'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'content_endpoint_test_result'],
    ];

    $form['api_endpoints']['stats_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Stats Endpoint'),
      '#default_value' => $config->get('stats_endpoint') ?: 'stats',
      '#required' => TRUE,
      '#description' => $this->t('The Stats endpoint allows you to get statistics on the collection. <a href="https://edan.si.edu/openaccess/apidocs/##api-metrics" target="_blank">See the metrics endpoint documentation</a>.'),
    ];

    $form['api_endpoints']['test_stats_endpoint'] = [
      '#type' => 'button',
      '#value' => $this->t('Test Stats Endpoint'),
      '#ajax' => [
        'callback' => '::testStatsEndpoint',
        'wrapper' => 'stats_endpoint_test_result',
      ],
    ];

    $form['api_endpoints']['stats_endpoint_test_result'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'stats_endpoint_test_result'],
    ];

    $form['api_endpoints']['category_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Category Search Endpoint'),
      '#default_value' => $config->get('category_endpoint') ?: 'category/:cat/search',
      '#required' => TRUE,
      '#description' => $this->t('The Category Search endpoint allows you to search the collection by category. <a href="https://edan.si.edu/openaccess/apidocs/#api-search-category_search" target="_blank">See the Category Search endpoint documentation</a>.'),
    ];

    $form['api_endpoints']['test_category_endpoint'] = [
      '#type' => 'button',
      '#value' => $this->t('Test Category Search Endpoint'),
      '#ajax' => [
        'callback' => '::testCategorySearchEndpoint',
        'wrapper' => 'category_endpoint_test_result',
      ],
    ];

    $form['api_endpoints']['category_endpoint_test_result'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'category_search_endpoint_test_result'],
    ];

    $form['api_endpoints']['terms_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Terms Endpoint'),
      '#default_value' => $config->get('terms_endpoint'),
      '#required' => TRUE,
      '#description' => $this->t('The endpoint for retrieving terms. <a href="https://edan.si.edu/openaccess/apidocs/#api-search-terms" target="_blank">See the Terms endpoint documentation</a>.'),
    ];

    $form['api_endpoints']['test_terms_endpoint'] = [
      '#type' => 'button',
      '#value' => $this->t('Test Terms Endpoint') ?: 'terms/:category',
      '#ajax' => [
        'callback' => '::testTermsEndpoint',
        'wrapper' => 'terms_endpoint_test_result',
      ],
    ];

    $form['api_endpoints']['terms_endpoint_test_result'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'terms_endpoint_test_result'],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('smithsonian_open_access.settings')
      ->set('base_uri', $form_state->getValue('base_uri'))
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('search_endpoint', $form_state->getValue('search_endpoint'))
      ->set('content_endpoint', $form_state->getValue('content_endpoint'))
      ->set('stats_endpoint', $form_state->getValue('stats_endpoint'))
      ->set('terms_endpoint', $form_state->getValue('terms_endpoint'))
      ->set('category_endpoint', $form_state->getValue('category_endpoint'))
      ->save();

  parent::submitForm($form, $form_state);
  }

  /**
   * Callback for testing the Search Endpoint.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HTTP request object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX response object.
   */
  public function testSearchEndpoint(array &$form, FormStateInterface $form_state, Request $request) {
    // Save the form values.
    $this->submitForm($form, $form_state);

    $response = new AjaxResponse();

    $api = \Drupal::service('smithsonian_open_access.api');
    try {
      $result = $api->search('smithsonian', 0, 1);
      $response->addCommand(new HtmlCommand('#search_endpoint_test_result', $this->t('Search endpoint test was successful. All API settings saved.')));
    } catch (\Exception $e) {
      $response->addCommand(new HtmlCommand('#search_endpoint_test_result', $this->t('Search endpoint test failed. Error: @error', ['@error' => $e->getMessage()])));
    }

    return $response;
  }

  /**
   * Callback for testing the Content Endpoint.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HTTP request object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX response object.
   */
  public function testContentEndpoint(array &$form, FormStateInterface $form_state, Request $request) {
    //Save the form values.
    $this->submitForm($form,$form_state);

    $response = new AjaxResponse();
    $api = \Drupal::service('smithsonian_open_access.api');

    try {
      $result = $api->getContent('ld1-1643398738600-1643398750158-0');
      $response->addCommand(new HtmlCommand('#content_endpoint_test_result', $this->t('Object Content endpoint test was successful. All API settings saved.')));
    } catch (\Exception $e) {
      $response->addCommand(new HtmlCommand('#content_endpoint_test_result', $this->t('Object Content endpoint test failed. Error: @error', ['@error' => $e->getMessage()])));
    }
    return $response;
  }

  /**
   * Callback for testing the Stats Endpoint.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HTTP request object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX response object.
   */
  public function testStatsEndpoint(array &$form, FormStateInterface $form_state, Request $request) {
    // Save the form values.
    $this->submitForm($form, $form_state);

    $response = new AjaxResponse();
    $api = \Drupal::service('smithsonian_open_access.api');

    try {
      $result = $api->getStats();
      $response->addCommand(new HtmlCommand('#stats_endpoint_test_result', $this->t('Stats endpoint test was successful. All API settings saved.')));
    } catch (\Exception $e) {
      $response->addCommand(new HtmlCommand('#stats_endpoint_test_result', $this->t('Stats endpoint test failed. Error: @error', ['@error' => $e->getMessage()])));
    }
    return $response;

  }

  /**
   * Callback for testing the Category Search Endpoint.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HTTP request object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX response object.
   */
  public function testCategorySearchEndpoint(array &$form, FormStateInterface $form_state, Request $request) {
    // Save the form values.
    $this->submitForm($form, $form_state);

    $response = new AjaxResponse();

    $api = \Drupal::service('smithsonian_open_access.api');

    try {
      $result = $api->categorySearch('smithsonian', 'art_design'); // Use 'art_design' as the hardcoded test value.
      $response->addCommand(new HtmlCommand('#category_search_endpoint_test_result', $this->t('Category search endpoint test was successful. All API settings saved.')));
    } catch (\Exception $e) {
      $response->addCommand(new HtmlCommand('#category_search_endpoint_test_result', $this->t('Category search endpoint test failed. Error: @error', ['@error' => $e->getMessage()])));
    }

    return $response;
  }

  /**
   * Callback for testing the Terms Endpoint.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HTTP request object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX response object.
   */
  public function testTermsEndpoint(array &$form, FormStateInterface $form_state, Request $request) {
    // Save the form values.
    $this->submitForm($form, $form_state);

    $response = new AjaxResponse();

    $api = \Drupal::service('smithsonian_open_access.api');

    try {
      $result = $api->termsSearch('culture'); // Use 'culture' as the hardcoded test value.
      $response->addCommand(new HtmlCommand('#terms_endpoint_test_result', $this->t('Terms search endpoint test was successful. All API settings saved.')));
    } catch (\Exception $e) {
      $response->addCommand(new HtmlCommand('#terms_endpoint_test_result', $this->t('Terms search endpoint test failed. Error: @error', ['@error' => $e->getMessage()])));
    }

    return $response;
  }
}
