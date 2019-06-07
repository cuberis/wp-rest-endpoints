# REST API Endpoints for WordPress

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cuberis/wp-rest-endpoints.svg?style=flat-square)](https://packagist.org/packages/cuberis/wp-rest-endpoints)

Add custom REST API endpoints to your WordPress site. This package provides a basic system for adding simple endpoints to the WP REST API.

## Requirements

- PHP 5.6+
- WordPress 4.7+

## Installing

1. Install via composer:

```
composer require cuberis/wp-rest-endpoints
```

2. Add your endpoints
3. Create a template

## Adding Endpoints

Note: when adding new endpoints, make sure to hook into `rest_api_init`.

### Add Route for Custom Post Type

https://my-website.com/wp-json/cuberis/v1/my-post-type

```php
function my_register_rest_routes() {

  new Cuberis\REST_API\Post_Type_Controller('my_post_type_slug', [
    'namespace' => 'cuberis',
    'version'   => 1,
    'endpoint'  => 'my-post-type'
  ]);

}
add_action( 'rest_api_init', 'my_register_rest_routes' );
```

### Extend `Base_Controller` for a Custom Endpoint

https://my-website.com/wp-json/cuberis/v1/whatever

```php
class My_Custom_Controller extends Cuberis\REST_API\Base_Controller {

  public function __construct() {
    parent::__construct([
      'namespace' => 'cuberis',
      'version'   => 1,
      'endpoint'  => 'whatever'
    ]);
  }

  public function get_items( $request ) {
    return rest_ensure_response(['Testing']);
  }

}

function my_register_rest_routes() {
  new My_Custom_Controller();
}
add_action( 'rest_api_init', 'my_register_rest_routes' );
```

## Templates

### Post Type Endpoints

If you are using the default reponse from your post type endpoints, each endpoint will need a template. Templates are PHP files that live in the `templates/partials/` directory within your theme. Each template should be named: `partial-{post-type-slug}`. Example:

```shell
themes/your-theme-name/
├── templates/
│   ├── partials/
│   │   ├── partial-my_post_type.php
```

If you register multiple post types for an endpoint, the library will look for a partial named `partial-all.php`.

In your template file, the only thing you will have available from the post will be the ID in the form of an `$id` variable.

### Custom Endpoints

When defining custom endpoints, you have complete control over your API response so you can define if or how you use templates.

## About the endpoints

By default, endpoints will return the following JSON format:

```json
[
  {
    "id": 1234,
    "title": "My Post Title",
    "html": "<article>\n<h2>\nMy Post Title\n</h2>\n</article>\n"
  }
]
```

`id` is the raw post ID, `title` is your post title returned from `get_the_title()` and `html` is the raw HTML returned from your template file.

### Using URL Parameters

Endpoints are powered by URL parameters which should match up identically with WP_Query vars. So for example, you could use:

https://my-website.com/wp-json/cuberis/v1/my-post-type?posts_per_page=4,paged=2,my_category=slug1,slug2

The API would then return posts from the following WP_Query:

```
new WP_Query([
  'post_type' => 'my-post-type',
  'posts_per_page' => 4,
  'paged' => 2,
  'tax_query' => [
    [
      'taxonomy' => 'my_category',
      'field' => 'slug',
      'terms' => [
        'slug1',
        'slug2'
      ]
    ]
  ]
])
```

Note: Taxonomy queries via endpoints currently only supports the `slug` field type.

## Optional Filters

### `cuberis_rest_cpt_result`

```php
function cuberis_filter_api_result( $result, $post_id ) {
  return [
    'pageID' => $post_id,
    'testing' => 'yep!'
  ];
}
add_filter('cuberis_rest_cpt_result', 'cuberis_filter_api_result', 10, 2);
```

### `cuberis_rest_api_query_args`

```php
function cuberis_filter_api_args( $args, $params ) {
  $args['order'] = 'ASC';
  return $args;
}
add_filter('cuberis_rest_api_query_args', 'cuberis_filter_api_args', 10, 2);
```