<?php

namespace Cuberis\RestAPI;

abstract class BaseController {

  /**
   * Constructor
   *
   * @param array $settings
   */
  public function __construct( $settings ) {
    $this->registerRoute($settings);
  }

  /**
   * Register a route with WP.
   *
   * @param  array $settings
   * @return void
   */
  public function registerRoute( $settings ) {
    $ns = trailingslashit( $settings['namespace'] );
    $v = trailingslashit( (string) $settings['version'] );
    $methods = array_key_exists('methods', $settings) ? $settings['methods'] : 'GET';
    register_rest_route( $ns.'v'.$v, $settings['endpoint'], [
      [
        'methods'   => $methods,
        'callback'  => array( $this, 'getItems' ),
      ],
    ]);
  }

  /**
   * A placeholder method for extending in child classes.
   * This will be the actual response from the API.
   *
   * @abstract
   *
   * @param  WP_REST_Request           $request Full details about the request.
   * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
   */
  abstract function getItems( $request );

}