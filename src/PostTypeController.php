<?php

namespace Cuberis\RestAPI;

class PostTypeController extends BaseController {

  public $post_type;

  /**
   * Constructor.
   *
   * @param string $post_type
   * @param string $resource_name
   */
  public function __construct( $post_type, $settings ) {
    $this->post_type = $post_type;
    parent::__construct($settings);
  }


  /**
   * Process a partial file to be passed through the endpoint.
   *
   * @param integer $id      The post ID
   * @param string  $partial The php file to load
   */
  private function getTemplatePart( $id, $partial = null ) {

    $abspath = get_template_directory().'/templates/partials/';

    if( file_exists( $abspath . $partial . '.php' ) ) {
      $filename = $partial;
    } else {
      $filename = 'partial';
    }

    ob_start();
    include( $abspath . $filename . '.php' );
    return ob_get_clean();
  }

  /**
   * Build the query args from a WP_REST_Response object.
   *
   * @param  object $request WP_REST_Response object
   * @return array
   */
  public function buildQueryArgs( $params = null ) {

    // defaults
    $args = [
      'posts_per_page' => 100,
      'post_type' => $this->post_type
    ];

    // Loop through each of our url params and add them to our $args array.
    foreach( $params as $param => $value ) {

      // make sure taxonomies always use a tax_query
      if( taxonomy_exists( $param ) && $value !== '' ) {
        $args['tax_query'][] = [
          'taxonomy' => $param,
          'field' => 'slug',
          'terms' => explode(',', $value)
        ];

      // otherwise, just add params to $args
      } else {
        $args[$param] = htmlspecialchars($value);
      }
    }

    return $args;
  }

  /**
   * Do a DB query.
   * Use SearchWP for search queries, otherwise, use a
   * regular WP_Query.
   *
   * @param  array  $args
   * @return object
   */
  public function doQuery( $args ) {

    if( isset($args['se']) && $args['se'] ) {
      $args['s'] = $args['se'];
      return new \SWP_Query( $args );
    }

    return new \WP_Query( $args );

  }

  /**
   * Get items for our collection. This is the callback for the
   * register_rest_route function above
   *
   * @param  WP_REST_Request           $request Full details about the request.
   * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
   */
  public function getItems( $request ) {

    $type = $this->post_type;
    $request_params = $request->get_query_params();
    $partial = !is_array($type) ? 'partial-'.$type : 'partial-all';

    // get the args for the query
    $args = apply_filters( 'cuberis_rest_api_query_args', $this->buildQueryArgs($request_params), $request_params );

    // do the query
    $results = $this->doQuery( $args );
    $total_posts = $results->found_posts;
    $max_pages = ceil( $total_posts / (int) $args['posts_per_page'] );

    // prepare our results
    if( !empty($results->posts) ) {
      foreach( $results->posts as $result => $value ) {
        $filtered[] = apply_filters('sage_wp_api_result', array(
          'id' => $value->ID,
          'title' => get_the_title( $value->ID ),
          'html'  => $this->getTemplatePart( $value->ID, $partial )
        ), $value->ID);
      }
    } else {
      $filtered = [];
    }

    // Prepare the response.
    $response = rest_ensure_response( $filtered );
    $response->header( 'X-WP-Total', (int) $total_posts );
    $response->header( 'X-WP-TotalPages', (int) $max_pages );

    return $response;
  }
}