# REST API Endpoints for WordPress

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cuberis/wp-rest-endpoints.svg?style=flat-square)](https://packagist.org/packages/cuberis/wp-rest-endpoints)

Add custom REST API endpoints to your WordPress site. This package provides a basic system for adding simple endpoints to the WP REST API.

## Installing

Add this to your `composer.json`:

```json
"require": {
  "cuberis/wp-rest-endpoints": "^0.1"
}
```

## Examples

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

## Filters

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