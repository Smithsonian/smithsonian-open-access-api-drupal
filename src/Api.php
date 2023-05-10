<?php


namespace Drupal\smithsonian_open_access;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;

class Api {
  protected $configFactory;
  protected $client;
  protected $logger; // Add the logger class property

  public function __construct(ConfigFactoryInterface $config_factory, Client $client, LoggerInterface $logger) {
    $this->configFactory = $config_factory;
    $this->client = $client;
    $this->logger = $logger;
  }

  /**
   * Perform an API request using the provided endpoint and parameters.
   *
   * @param string $endpoint
   *   API endpoint to call.
   * @param array $params
   *   Array of query parameters to pass to the API call.
   *
   * @return \Psr\Http\Message\ResponseInterface|null
   *   Returns the API response on success, null on failure.
   */
  private function performApiRequest($endpoint, $params = []) {
    try {
      $config = $this->configFactory->get('smithsonian_open_access.settings');
      $base_uri = $config->get('base_uri') ?: 'https://api.si.edu/openaccess/api/v1.0/';
      $api_key = $config->get('api_key');

      $params['api_key'] = $api_key;
      \Drupal::logger('smithsonian_open_access')->notice("Perform API Request URL:". $base_uri . $endpoint);

      $response = $this->client->get($base_uri . $endpoint, [
        'query' => $params,
      ]);

      if ($response->getStatusCode() == 200) {
        return $response;
      } else {
        throw new \Exception('Error while performing API request.');
      }
    } catch (RequestException $e) {
      $this->logger->error('Error while performing API request: @error', ['@error' => $e->getMessage()]);
      return null;
    }
  }

  /**
   * Performs a search using the provided query and parameters.
   *
   * @param string $query
   *   The search query.
   * @param int $start
   *   The start index for the search results. (optional)
   * @param int $rows
   *   The number of rows to return. (optional)
   * @param string $sort
   *   The sort order for the search results. (optional)
   * @param bool $online_only
   *   Set to true to return only results with online visual material. (optional)
   * @param array $additional_filters
   *   Additional filter queries for the search. (optional)
   *
   * @return array
   *   An array with the search results.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function search($query, $start = 0, $rows = 10, $sort = 'relevancy', $type = 'edanmdm', $row_group = 'objects', $images_only = TRUE, $additional_filters = []) {
    try {
      $config = $this->configFactory->get('smithsonian_open_access.settings');
      $query = urlencode($query);
      $images_only = $images_only ? ' AND online_visual_material:true' : null;

      $params = [
        'q' => $query . $images_only,
        'start' => $start,
        'rows' => $rows,
        'api_key' => $config->get('api_key'),
      ];

      $allowed_sort_values = ['relevancy','id', 'newest', 'updated', 'random'];
      if (!empty($sort) && in_array($sort, $allowed_sort_values)) {
        $params['sort'] = $sort;
      } elseif (!empty($sort)) {
        $this->logger->warning('Invalid sort value: @sort. Allowed values: ' . implode(', ', $allowed_sort_values), ['@sort' => $sort]);
      }

      $allowed_type_values = ['edanmdm', 'ead_collection', 'ead_component', 'all'];
      if (in_array($type, $allowed_type_values)) {
        $params['type'] = $type;
      } else {
        $this->logger->warning('Invalid type value: @type. Allowed values: ' . implode(', ', $allowed_type_values), ['@type' => $type]);
      }

      $allowed_row_group_values = ['objects', 'archives'];
      if (in_array($row_group, $allowed_row_group_values)) {
        $params['row_group'] = $row_group;
      } else {
        $this->logger->warning('Invalid row_group value: @row_group. Allowed values: ' . implode(', ', $allowed_row_group_values), ['@row_group' => $row_group]);
      }

      if (!empty($additional_filters)) {
        $params['fq'] = $additional_filters;
      }

      $response = $this->client->request('GET', $config->get('base_uri') . $config->get('search_endpoint'), [
        'query' => $params,
      ]);

      return json_decode($response->getBody(), TRUE);
    } catch (RequestException $e) {
      throw new RequestException("Error in search: " . $e->getMessage(), $e->getRequest());
    }
  }

  /**
   * Retrieves content by its ID or URL.
   *
   * @param string $id
   *   The content ID or URL.
   *
   * @return array
   *   An array with the content data.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function getContent($id) {
    try {
      $config = $this->configFactory->get('smithsonian_open_access.settings');
      $response = $this->client->request('GET', $config->get('base_uri') . $config->get('content_endpoint') . '/' . $id, [
        'query' => [
          'api_key' => $config->get('api_key'),
        ],
      ]);
      return json_decode($response->getBody(), TRUE);
    } catch (RequestException $e) {
      throw new RequestException("Error in getContent: " . $e->getMessage(), $e->getRequest());
    }
  }


  /**
   * Retrieves statistics from the API.
   *
   * @return array
   *   An array with the statistics data.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function getStats() {
    try {
      $config = $this->configFactory->get('smithsonian_open_access.settings');
      $response = $this->client->request('GET', $config->get('base_uri') . $config->get('stats_endpoint'), [
        'query' => [
          'api_key' => $config->get('api_key'),
        ],
      ]);
      return json_decode($response->getBody(), TRUE);
    } catch (RequestException $e) {
      throw new RequestException("Error in getStats: " . $e->getMessage(), $e->getRequest());
    }
  }


  /**
   * Performs a search within a specific category.
   *
   * @param string $query
   *   The search query.
   * @param string $category
   *   The category to search within.
   * @param int $start
   *   The start row of the query. (optional)
   * @param int $rows
   *   The size of the array to be returned. (optional)
   * @param string $sort
   *   The sort order for the search results. (optional)
   *
   * @return array
   *   An array with the search results.
   *
   * @throws \Exception
   */
  public function categorySearch($query, $category, $start = 0, $rows = 10, $sort = 'relevancy') {
    if (empty($category)) {
      throw new InvalidArgumentException("Category parameter is required for categorySearch.");
    }

    try {
      $config = $this->configFactory->get('smithsonian_open_access.settings');
      $params = [
        'q' => $query,
        'start' => $start,
        'rows' => $rows,
        'api_key' => $config->get('api_key'),
      ];

      // Validate and set the sort parameter
      $allowed_sort_values = ['relevancy', 'id', 'newest', 'updated', 'random'];
      if (in_array($sort, $allowed_sort_values)) {
        $params['sort'] = $sort;
      }
      else {
        $this->logger->warning('Invalid sort value: @sort. Allowed values: @allowed_sorts', [
          '@sort' => $sort,
          '@allowed_sorts' => implode(', ', $allowed_sort_values),
        ]);
      }

      // Validate and set the category parameter
      $allowed_category_values = ['art_design', 'history_culture', 'science_technology'];
      if (in_array($category, $allowed_category_values)) {
        $params['cat'] = $category;
      }
      else {
        $this->logger->warning('Invalid category value: @category. Allowed values: @allowed_categories', [
          '@category' => $category,
          '@allowed_categories' => implode(', ', $allowed_category_values),
        ]);
      }

      $response = $this->client->request('GET', $config->get('base_uri') . str_replace(':cat', $category, $config->get('category_endpoint')), [
        'query' => $params,
      ]);

      return json_decode($response->getBody(), TRUE);
    } catch (RequestException $e) {
      throw new RequestException("Error in categorySearch: " . $e->getMessage(), $e->getRequest());
    }
  }

  /**
   * Performs a terms search for a specific term category.
   *
   * @param string $category
   *   The term category to search for (e.g., 'culture', 'data_source', etc.).
   * @param string|null $starts_with
   *   The optional string prefix filter. (optional)
   *
   * @return array
   *   An array with the terms search results.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function termsSearch($category, $starts_with = null) {
    if (empty($category)) {
      throw new InvalidArgumentException("Category parameter is required for termsSearch.");
    }

    try {
      $config = $this->configFactory->get('smithsonian_open_access.settings');
      $params = [
        'api_key' => $config->get('api_key'),
        'category' => $category,
      ];

      if ($starts_with !== null) {
        $params['starts_with'] = $starts_with;
      }

      $response = $this->client->request('GET', $config->get('base_uri') . str_replace(':category', $category, $config->get('terms_endpoint')), [
        'query' => $params,
      ]);

      return json_decode($response->getBody(), TRUE);
    } catch (RequestException $e) {
      throw new RequestException("Error in termsSearch: " . $e->getMessage(), $e->getRequest());
    }
  }


}



