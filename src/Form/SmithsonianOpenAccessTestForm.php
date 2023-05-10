<?php

namespace Drupal\smithsonian_open_access\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\smithsonian_open_access\Api;
use Drupal\Core\Config\ConfigFactoryInterface;

class SmithsonianOpenAccessTestForm extends FormBase {

  protected $api;
  protected $configFactory;

  public function __construct(Api $api, configFactoryInterface $config_factory) {
    $this->api = $api;
    $this->configFactory = $config_factory;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('smithsonian_open_access.api'),
      $container->get('config.factory')
    );
  }

  public function getFormId() {
    return 'smithsonian_open_access_test_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'smithsonian_open_access/form_behaviors';
    $form['#attached']['library'][] = 'core/drupal.ajax';
    $results_markup = '';

    $endpoint_config = $this->configFactory->get('smithsonian_open_access.settings');
    $endpoints = [
      'search' => $this->t('Search'),
      'content' => $this->t('Content'),
      'stats' => $this->t('Stats'),
      'terms' => $this->t('Terms'),
      'category' => $this->t('Category'),
    ];

    $form['endpoint'] = [
      '#type' => 'select',
      '#title' => $this->t('Endpoint'),
      '#description' => $this->t('Select the API endpoint you want to test.'),
      '#options' => $endpoints,
      '#required' => TRUE,
      '#default_value' => 'search',
    ];

    $form['category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category'),
      '#description' => $this->t('Select a category for the categorySearch endpoint.'),
      '#options' => [
        'art_design' => $this->t('Art & Design'),
        'history_culture' => $this->t('History & Culture'),
        'science_technology' => $this->t('Science & Technology'),
      ],
      '#default_value' => 'art_design',
      '#states' => [
        'visible' => [
          ':input[name="endpoint"]' => ['value' => 'category'],
        ],
      ],
    ];

    $form['term'] = [
      '#type' => 'select',
      '#title' => $this->t('Term'),
      '#description' => $this->t('Select a term for the termSearch endpoint.'),
      '#options' => [
        'culture' => $this->t('Culture'),
        'data_source' => $this->t('Data Source'),
        'date' => $this->t('Date'),
        'object_type' => $this->t('Object Type'),
        'online_media_type' => $this->t('Online Media Type'),
        'place' => $this->t('Place'),
        'topic' => $this->t('Topic'),
        'unit_code' => $this->t('Unit Code'),
      ],
      '#default_value' => 'topic',
      '#states' => [
        'visible' => [
          ':input[name="endpoint"]' => ['value' => 'terms'],
        ],
      ],
    ];

    $form['query'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search word or Object ID (for content endpoint)'),
      '#description' => $this->t('Enter the required parameter for the selected endpoint.'),
      '#maxlength' => 64,
      '#size' => 64,
      '#states' => [
        'visible' => [
          ':input[name="endpoint"]' => [
            ['value' => 'search'],
            ['value' => 'content'],
            ['value' => 'category'],
          ],
        ],
        'required' => [
          ':input[name="endpoint"]' => [
            ['value' => 'search'],
            ['value' => 'content'],
            ['value' => 'category'],
          ],
        ],
      ],
    ];


    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Perform Search'),
      '#ajax' => [
        'callback' => '::ajaxSearch',
        'wrapper' => 'search-results',
        'effect' => 'fade',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Searching...'),
        ],
      ],
    ];

    $form['results'] = [
      '#type' => 'textarea',
      '#rows' => 15,
      '#readonly' => TRUE,
      '#default_value' => $results_markup,
      '#prefix' => '<div id="search-results">',
      '#suffix' => '</div>',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Not used, this is an ajax form, see ajaxSearch().
    }

  public function ajaxSearch(array $form, FormStateInterface $form_state) {
    \Drupal::logger('smithsonian_open_access')->notice('ajaxSearch executed');

    $endpoint = $form_state->getValue('endpoint');
    $query = $form_state->getValue('query');

    // Set other parameters
    $start = 0;
    $rows = 25;
    $sort = null;
    $filter_query = $fq;

    switch ($endpoint) {
      case 'search':
        $results = $this->api->search($query, $start, $rows, $sort, 'edanmdm', 'objects', TRUE, [$fq]);
        break;

      case 'content':
        $results = $this->api->getContent($query);
        break;

      case 'stats':
        $results = $this->api->getStats();
        break;

      case 'category':
        $results = $this->api->categorySearch($query, $form_state->getValue('category'));
        break;

      case 'terms':
        $results = $this->api->termsSearch($form_state->getValue('term'));
        break;
    }
    \Drupal::logger('smithsonian_open_access')->notice('Search results:', ['results' => $results]);
    \Drupal::logger('smithsonian_open_access')->notice('Search results: @results', ['@results' => print_r($results, TRUE)]);


    if ($results) {
      $results_json = json_encode($results, JSON_PRETTY_PRINT);
      $form['results']['#value'] = $results_json;
    } else {
      $form['results']['#value'] = $this->t('No results found.');
    }

    $form_state->setRebuild();

    return $form['results'];
  }

}
