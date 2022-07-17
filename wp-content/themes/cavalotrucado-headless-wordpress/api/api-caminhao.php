<?php
function get_truck_by_slug3($request) {
  $slug = sanitize_text_field($request->get_param('slug'));
  $page_object = get_page_by_path($slug, OBJECT, 'vehicle');

  $id = $page_object->ID;

  $vehicle = array(
    'slug' => $slug,
    'id' => $id,
    'title' => $page_object->post_title,
    'vehicle_model_name' => get_field('vehicle_model_name', $id),
    'vehicle_long_text' => get_field('vehicle_long_text', $id),
  );

  $images = get_field('vehicle_photos', $id);
  foreach ($images as $image) {
    $vehicle['photos'][] = array(
      'url' => $image['url'],
    );
  }



/*
  //Image Gallery
    $images = get_field('vehicle_photos', $id);
    if($images) {
      $g = 0;
      $data[]['vehicle_photos'] = [];
        foreach($images as $image) {
          $data['vehicle_photos'][$g] = $image['url'];

          $g++;
        }
    }

  //Image Gallery
  
  $images = get_field('vehicle_photos', $id);
  if($images) {
    $i = 0;
    $gallery['vehicle_photos'] = [];
      foreach($images as $i => $image) {
        $gallery[$i] = $image['url'];
        
        $i++;
      }

  }*/

  //$final = array_push($vehicle, $data);

  return rest_ensure_response($vehicle);
}
function get_truck_by_slug($request) {
  $slug = sanitize_text_field($request->get_param('slug'));

  $args = [
    'name' => $slug,
    'post_type' => 'vehicle',
    'numberposts' => 1,
    'post_status' => 'publish'
  ];

  $posts = get_posts($args);

  if (empty($posts)) {
    return new WP_Error( 'empty_truck', 'There are no posts to display', array('status' => 404) );
  }

  $data = [];
	$i = 0;

	foreach($posts as $post) {
    $brand = wp_get_object_terms($post->ID, 'brand');
		$data[$i]['id'] = $post->ID;
		$data[$i]['title'] = $post->post_title;
    $data[$i]['slug'] = $post->post_name;
    $data[$i]['brand'] = $brand[0]->name;
    $data[$i]['vehicle_model_name'] = get_field('vehicle_model_name', $post->ID);
    $data[$i]['vehicle_year'] = get_field('vehicle_year', $post->ID);
    $data[$i]['vehicle_year_model'] = get_field('vehicle_year_model', $post->ID);
    $data[$i]['vehicle_show_price'] = get_field('vehicle_show_price', $post->ID);
    $data[$i]['vehicle_price'] = get_field('vehicle_price', $post->ID);
    $data[$i]['vehicle_km'] = get_field('vehicle_km', $post->ID);
    $data[$i]['vehicle_state'] = get_field('vehicle_state', $post->ID);
    $data[$i]['vehicle_main_photo'] = get_field('vehicle_main_photo', $post->ID);
    $data[$i]['vehicle_long_text'] = get_field('vehicle_long_text', $post->ID);
    $data[$i]['vehicle_short_text_1'] = get_field('vehicle_short_text_1', $post->ID);
    $data[$i]['vehicle_short_text_2'] = get_field('vehicle_short_text_2', $post->ID);
    $data[$i]['vehicle_short_text_3'] = get_field('vehicle_short_text_3', $post->ID);

    //Image Gallery
    $images = get_field('vehicle_photos', $post->ID);
    if($images) {
      $g = 0;
      $data[$i]['vehicle_photos'] = [];
        foreach($images as $image) {
          $data[$i]['vehicle_photos'][$g] = $image['url'];

          $g++;
        }
    }

		$i++;
	}


  /*
  $images = get_field('vehicle_photos', $posts->ID);

  if( $images ){
  $g = 0;
  $gallery = [];
  $gallery[$g]['url'] = 'teste';
    foreach($images as $image) {
      $gallery[$g]['url'] = $image['url'];
      $data[$i]['vehicle_model_name'] = get_field('vehicle_model_name', $post->ID);

      $g++;
    }
  }*/

  $response = new WP_REST_Response($data);
  $response->set_status(200);

  return $response;

}

function get_random_4_trucks() {
  
	$args = [
    'post_status' => 'publish',
		'numberposts' => 4,
    'orderby' => 'rand',
		'post_type' => 'vehicle'
	];

	$posts = get_posts($args);

  if (empty($posts)) {
    return new WP_Error( 'empty_category', 'There are no posts to display', array('status' => 404) );
  }

  $data = [];
	$i = 0;

	foreach($posts as $post) {
    $brand = wp_get_object_terms($post->ID, 'brand');
		$data[$i]['id'] = $post->ID;
		$data[$i]['title'] = $post->post_title;
    $data[$i]['slug'] = $post->post_name;
    $data[$i]['brand'] = $brand[0]->name;
    $data[$i]['vehicle_model_name'] = get_field('vehicle_model_name', $post->ID);
    $data[$i]['vehicle_year'] = get_field('vehicle_year', $post->ID);
    $data[$i]['vehicle_year_model'] = get_field('vehicle_year_model', $post->ID);
    $data[$i]['vehicle_show_price'] = get_field('vehicle_show_price', $post->ID);
    $data[$i]['vehicle_price'] = get_field('vehicle_price', $post->ID);
    $data[$i]['vehicle_km'] = get_field('vehicle_km', $post->ID);
    $data[$i]['vehicle_state'] = get_field('vehicle_state', $post->ID);
    $data[$i]['vehicle_main_photo'] = get_field('vehicle_main_photo', $post->ID);
    $data[$i]['vehicle_short_text_1'] = get_field('vehicle_short_text_1', $post->ID);
    $data[$i]['vehicle_short_text_2'] = get_field('vehicle_short_text_2', $post->ID);
    $data[$i]['vehicle_short_text_3'] = get_field('vehicle_short_text_3', $post->ID);

		$i++;
	}

  $response = new WP_REST_Response($data);
  $response->set_status(200);

  return $response;
}

add_action('rest_api_init', function() {
	register_rest_route( 'custom/v1', 'truck/(?P<slug>[a-zA-Z0-9-]+)', array(
		'methods' => 'GET',
		'callback' => 'get_truck_by_slug',
    'args' => array(
      'slug' => array (
        'required' => false
      )
    )
  ));
	
  register_rest_route('custom/v1', 'random_4_trucks', [
		'methods' => 'GET',
		'callback' => 'get_random_4_trucks',
	]);
});

/**
 * Add cache to custom/v1/* endpoint.
 * Add ?skip_cache=1 to the end of url to skip cache.
 */

function wprc_add_acf_posts_endpoint( $allowed_endpoints ) {
    if ( ! isset( $allowed_endpoints[ 'custom/v1' ] ) || ! in_array( 'random_4_trucks', $allowed_endpoints[ 'custom/v1' ] ) ) {
        $allowed_endpoints[ 'custom/v1' ][] = 'random_4_trucks';
    }
    if ( ! isset( $allowed_endpoints[ 'custom/v1' ] ) || ! in_array( 'truck', $allowed_endpoints[ 'custom/v1' ] ) ) {
        $allowed_endpoints[ 'custom/v1' ][] = 'truck';
    }
    return $allowed_endpoints;
}
add_filter( 'wp_rest_cache/allowed_endpoints', 'wprc_add_acf_posts_endpoint', 10, 1);