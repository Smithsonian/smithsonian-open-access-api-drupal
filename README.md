# Smithsonian Open Access Drupal Module

The Smithsonian Open Access Drupal module provides a simple and convenient way to interact with the Smithsonian Open Access API in your Drupal site. It allows you to search the API, customize the search results using various parameters, and display the JSON data on the screen.

## Features

- Easy configuration of API settings through the Drupal admin interface
- Test API key functionality to verify the validity of the API key
- A search form to test search queries using the Smithsonian Open Access API
- An API wrapper class (`Api.php`) that can be used by other developers to interact with the API

## Installation

1. Download the `smithsonian_open_access` module and place it in the `/modules/custom` directory of your Drupal site. If you cloned the repository, make sure the name of the module directory is `smithsonian_open_access`.
2. Enable the module using the Drupal admin interface or by running `drush en smithsonian_open_access` in the command line.

## Configuration

1. Navigate to the module configuration page at `/admin/config/smithsonian-open-access`.
2. Enter your Smithsonian Open Access API key and customize other settings if necessary. To obtain an API key for the Smithsonian Open Access API, visit [data.gov](https://api.data.gov/signup/).
3. Click the "Test API Key" button to verify the validity of your API key.
4. Save the configuration.

## Usage

To use the search form provided by the module, navigate to `/admin/config/content/smithsonian-open-access/search-test`. Enter a search phrase and click the "Search" button to display the JSON data returned by the API.

To use the API wrapper class in your custom code, you can follow these steps:

1. Import the `Api` class: `use Drupal\smithsonian_open_access\Api;`
2. Inject the `Api` class as a dependency in your custom class or service.
3. Call the `search()` method of the `Api` class to perform a search query.

Refer to the module's source code for more examples and details on using the API wrapper class.

## Contributing
Submit bug reports, feature requests, and other issues to the Druapl

## API Documentation

The Smithsonian Open Access API provides access to millions of digital images and data from the Smithsonian's collections. To learn more about the API, visit the [Open Access API documentation](https://edan.si.edu/openaccess/apidocs/). The documentation includes information about available fields, departments, data types, and endpoints.

## Smithsonian Open Access Program

The Smithsonian Open Access Program promotes the sharing and reusing of digital assets from the Smithsonian's collections. You can explore and access the collection data via the [Open Access GitHub repository](https://github.com/your-github-repository-link). The repository contains detailed documentation and the data formatted in JSON. Please note that the Smithsonian does not support pull requests for this repository. The data in the repository is refreshed on a weekly basis, so it's recommended to check often for the latest revisions.

## License and Content

This module is licensed under the [GNU General Public License v2.0 or later](https://www.gnu.org/licenses/gpl-2.0.html).

The content provided by the Smithsonian Open Access API is available under the [CC0 1.0 Universal (CC0 1.0) Public Domain Dedication](https://creativecommons.org/publicdomain/zero/1.0/). You are free to use, modify, and distribute the content for any purpose, including commercial use, without requiring attribution to the Smithsonian Institution.

Please note that while the API provides CC0 content, other aspects of the module, such as code and documentation, may be subject to different licensing terms.
