<?php

namespace Cuberis\RestAPI;

abstract class BaseController {

  public function __construct( $settings ) {
    $this->registerRoute($settings);
  }

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

  abstract function getItems( $request );

}