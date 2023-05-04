<?php

function namespace_get_search_args()
{
  $args = [];
  $args['s'] = [
    'description' => esc_html__('The search term.', 'namespace'),
    'type' => 'string',
  ];

  return $args;
}

function search_truck($request)
{
  $posts = [];
  $results = [];
  // check for a search term
  if (isset($request['s'])):
    // get posts
    $posts = get_posts([
      'posts_per_page' => 5,
      'post_type' => ['vehicle'],
      's' => sanitize_text_field($request['s']),
    ]);
    // set up the data I want to return
    foreach ($posts as $post):
      $results[] = [
        'title' => $post->post_title,
        'link' => get_permalink($post->ID)
      ];
    endforeach;
  endif;

  if (empty($results)):
    return new WP_Error('no_results', 'No results');
  endif;

  return rest_ensure_response($results);
}
function get_truck_by_slug($request)
{
  $slug = sanitize_text_field($request->get_param('slug'));
  $pageObject = get_page_by_path($slug, OBJECT, 'vehicle');
  $id = $pageObject->ID;
  $brand = wp_get_object_terms($id, 'brand');

  $mainImage = get_field('vehicle_main_photo', $id);
  if ($mainImage):
    $size = 'large';
    $thumb = $mainImage['sizes'][$size];
    $mainImageUrl = esc_url($thumb);
  endif;

  $vehicle['data'] = array(
    'id' => $id,
    'title' => $pageObject->post_title,
    'slug' => $slug,
    'brand' => $brand[0]->name,
    'brand_slug' => $brand[0]->slug,
    'vehicle_model_name' => get_field('vehicle_model_name', $id),
    'vehicle_year' => get_field('vehicle_year', $id),
    'vehicle_year_model' => get_field('vehicle_year_model', $id),
    'vehicle_show_price' => get_field('vehicle_show_price', $id),
    'vehicle_price' => get_field('vehicle_price', $id),
    'vehicle_km' => get_field('vehicle_km', $id),
    'vehicle_state' => get_field('vehicle_state', $id),
    'vehicle_main_photo' => $mainImageUrl,
    'vehicle_long_text' => get_field('vehicle_long_text', $id),
    'vehicle_short_text_1' => get_field('vehicle_short_text_1', $id),
    'vehicle_short_text_2' => get_field('vehicle_short_text_2', $id),
    'vehicle_short_text_3' => get_field('vehicle_short_text_3', $id),
  );

  // Short Text
  $vehicle['data']['vehicle_short_texts'] = array(
    'vehicle_short_text_1' => get_field('vehicle_short_text_1', $id),
    'vehicle_short_text_2' => get_field('vehicle_short_text_2', $id),
    'vehicle_short_text_3' => get_field('vehicle_short_text_3', $id),
  );

  // Images
  $images = get_field('vehicle_photos', $id);
  $i = 0;
  foreach ($images as $image) {
    $vehicle['data']['photos'][$i] = array(
      'url' => $image['url'],
      'alt' => $image['alt'],
      'title' => $image['title'],
      'caption' => $image['caption'],
      'description' => $image['description'],
      'sizes' => $image['sizes'],
      'width' => $image['width'],
      'height' => $image['height'],
    );
    $i++;
  }

  return rest_ensure_response($vehicle);
}

function get_random_4_trucks($request)
{

  $posts_per_page = $request['per_page'] ? $request['per_page'] : 4;

  $args = [
    'post_status' => 'publish',
    'numberposts' => $posts_per_page,
    'orderby' => 'rand',
    'post_type' => 'vehicle'
  ];

  $posts = get_posts($args);

  if (empty($posts)) {
    return new WP_Error('empty_category', 'There are no posts to display', array('status' => 404));
  }

  $data = [];
  $i = 0;

  foreach ($posts as $post) {
    $mainImage = get_field('vehicle_main_photo', $post->ID);
    if ($mainImage):
      $size = 'medium';
      $thumb = $mainImage['sizes'][$size];
      $mainImageUrl = esc_url($thumb);
    endif;

    $brand = wp_get_object_terms($post->ID, 'brand');
    $data['data'][$i]['id'] = $post->ID;
    $data['data'][$i]['title'] = $post->post_title;
    $data['data'][$i]['slug'] = $post->post_name;
    $data['data'][$i]['brand'] = $brand[0]->name;
    $data['data'][$i]['vehicle_model_name'] = get_field('vehicle_model_name', $post->ID);
    $data['data'][$i]['vehicle_year'] = get_field('vehicle_year', $post->ID);
    $data['data'][$i]['vehicle_year_model'] = get_field('vehicle_year_model', $post->ID);
    $data['data'][$i]['vehicle_show_price'] = get_field('vehicle_show_price', $post->ID);
    $data['data'][$i]['vehicle_price'] = get_field('vehicle_price', $post->ID);
    $data['data'][$i]['vehicle_km'] = get_field('vehicle_km', $post->ID);
    $data['data'][$i]['vehicle_state'] = get_field('vehicle_state', $post->ID);
    $data['data'][$i]['vehicle_main_photo'] = $mainImageUrl;
    $data['data'][$i]['vehicle_short_text_1'] = get_field('vehicle_short_text_1', $post->ID);
    $data['data'][$i]['vehicle_short_text_2'] = get_field('vehicle_short_text_2', $post->ID);
    $data['data'][$i]['vehicle_short_text_3'] = get_field('vehicle_short_text_3', $post->ID);

    $i++;
  }

  $response = new WP_REST_Response($data);
  $response->set_status(200);

  return $response;
}

function get_latest_trucks($request)
{

  $pagination_number = $request['page'] ? $request['page'] : 1;
  $posts_per_page = $request['per_page'];
  $offset = ($pagination_number - 1) * $posts_per_page;

  $args = [
    'post_type' => 'vehicle',
    //'post_status' => 'sold',
    'post_status' => 'publish',
    'posts_per_page' => $posts_per_page,
    'orderby' => 'date',
    'offset' => $offset,
  ];

  $posts = get_posts($args);

  if (empty($posts)) {
    return new WP_Error('empty_category', 'There are no posts to display', array('status' => 404));
  }

  $data = [];

  $total_pages = ceil(wp_count_posts('vehicle')->publish / $posts_per_page);
  $total_count = wp_count_posts('vehicle')->publish;

  $data['meta'] = array(
    'total_count' => (int)$total_count,
    'total_pages' => $total_pages,
    'current_page' => (int)$pagination_number,
    'per_page' => (int)$posts_per_page,
    'offset' => $offset,
  );


  $data['data'] = [];


  
  $i = 0;

  foreach ($posts as $post) {
    $mainImage = get_field('vehicle_main_photo', $post->ID);
    if ($mainImage):
      $size = 'medium';
      $thumb = $mainImage['sizes'][$size];
      $mainImageUrl = esc_url($thumb);
    endif;

    $brand = wp_get_object_terms($post->ID, 'brand');
    $data['data'][$i]['id'] = $post->ID;
    $data['data'][$i]['title'] = $post->post_title;
    $data['data'][$i]['slug'] = $post->post_name;
    $data['data'][$i]['brand'] = $brand[0]->name;
    $data['data'][$i]['vehicle_model_name'] = get_field('vehicle_model_name', $post->ID);
    $data['data'][$i]['vehicle_year'] = get_field('vehicle_year', $post->ID);
    $data['data'][$i]['vehicle_year_model'] = get_field('vehicle_year_model', $post->ID);
    $data['data'][$i]['vehicle_show_price'] = get_field('vehicle_show_price', $post->ID);
    $data['data'][$i]['vehicle_price'] = get_field('vehicle_price', $post->ID);
    $data['data'][$i]['vehicle_km'] = get_field('vehicle_km', $post->ID);
    $data['data'][$i]['vehicle_state'] = get_field('vehicle_state', $post->ID);
    $data['data'][$i]['vehicle_main_photo'] = $mainImageUrl;
    $data['data'][$i]['vehicle_short_text_1'] = get_field('vehicle_short_text_1', $post->ID);
    $data['data'][$i]['vehicle_short_text_2'] = get_field('vehicle_short_text_2', $post->ID);
    $data['data'][$i]['vehicle_short_text_3'] = get_field('vehicle_short_text_3', $post->ID);

    $i++;
  }

  $response = new WP_REST_Response($data);
  $response->set_status(200);

  return $response;
}

function get_sold_trucks($request)
{

  $posts_per_page = $request['per_page'] ? $request['per_page'] : 8;

  $args = [
    'post_status' => 'sold',
    'numberposts' => $posts_per_page,
    'orderby' => 'rand',
    'post_type' => 'vehicle'
  ];

  $posts = get_posts($args);

  if (empty($posts)) {
    return new WP_Error('empty_category', 'There are no posts to display', array('status' => 404));
  }

  $data = [];
  $i = 0;

  foreach ($posts as $post) {
    $mainImage = get_field('vehicle_main_photo', $post->ID);
    if ($mainImage):
      $size = 'medium';
      $thumb = $mainImage['sizes'][$size];
      $mainImageUrl = esc_url($thumb);
    endif;

    $brand = wp_get_object_terms($post->ID, 'brand');
    $data['data'][$i]['id'] = $post->ID;
    $data['data'][$i]['title'] = $post->post_title;
    $data['data'][$i]['slug'] = $post->post_name;
    $data['data'][$i]['brand'] = $brand[0]->name;
    $data['data'][$i]['vehicle_model_name'] = get_field('vehicle_model_name', $post->ID);
    $data['data'][$i]['vehicle_year'] = get_field('vehicle_year', $post->ID);
    $data['data'][$i]['vehicle_year_model'] = get_field('vehicle_year_model', $post->ID);
    $data['data'][$i]['vehicle_show_price'] = get_field('vehicle_show_price', $post->ID);
    $data['data'][$i]['vehicle_price'] = get_field('vehicle_price', $post->ID);
    $data['data'][$i]['vehicle_km'] = get_field('vehicle_km', $post->ID);
    $data['data'][$i]['vehicle_state'] = get_field('vehicle_state', $post->ID);
    $data['data'][$i]['vehicle_main_photo'] = $mainImageUrl;
    $data['data'][$i]['vehicle_short_text_1'] = get_field('vehicle_short_text_1', $post->ID);
    $data['data'][$i]['vehicle_short_text_2'] = get_field('vehicle_short_text_2', $post->ID);
    $data['data'][$i]['vehicle_short_text_3'] = get_field('vehicle_short_text_3', $post->ID);

    $i++;
  }

  $response = new WP_REST_Response($data);
  $response->set_status(200);

  return $response;
}

add_action('rest_api_init', function () {
  register_rest_route(CUSTOM_API_NAMESPACE, 'search', [
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'search_truck',
    'args' => namespace_get_search_args()
  ]);

  register_rest_route(CUSTOM_API_NAMESPACE, 'truck/(?P<slug>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => 'get_truck_by_slug',
    'args' => array(
      'slug' => array(
        'required' => false
      )
    )
  ));

  register_rest_route(CUSTOM_API_NAMESPACE, 'random-trucks', [
    'methods' => 'GET',
    'callback' => 'get_random_4_trucks',
    'args' => array(
      'per_page' => array(
        'required' => false
      )
    ),
  ]);

  register_rest_route(CUSTOM_API_NAMESPACE, 'latest-trucks', [
    'methods' => 'GET',
    'callback' => 'get_latest_trucks',
    'args' => array(
      'per_page' => array(
        'required' => true
      ),
      'page' => array(
        'required' => false
      )
    ),
  ]);

  register_rest_route(CUSTOM_API_NAMESPACE, 'sold-trucks', [
    'methods' => 'GET',
    'callback' => 'get_sold_trucks',
    'args' => array(
      'per_page' => array(
        'required' => false
      )
    ),
  ]);
});

/**
 * Add cache to custom/v1/* endpoint.
 * to skip cache add ?skip_cache=1 to the end of url.
 */
function wprc_add_acf_posts_endpoint($allowedEndpoints)
{
  if (!isset($allowedEndpoints[CUSTOM_API_NAMESPACE]) ||
      !in_array('random_4_trucks', $allowedEndpoints[CUSTOM_API_NAMESPACE])) {
    $allowedEndpoints[CUSTOM_API_NAMESPACE][] = 'random_4_trucks';
    $allowedEndpoints[CUSTOM_API_NAMESPACE][] = 'get_last_trucks';
    $allowedEndpoints[CUSTOM_API_NAMESPACE][] = 'truck/(?P<slug>[a-zA-Z0-9-]+)';
    $allowedEndpoints[CUSTOM_API_NAMESPACE][] = 'truck';
    $allowedEndpoints[CUSTOM_API_NAMESPACE][] = 'search';
    $allowedEndpoints[CUSTOM_API_NAMESPACE][] = 'get_sold_trucks';
  }
  return $allowedEndpoints;
}
add_filter('wp_rest_cache/allowed_endpoints', 'wprc_add_acf_posts_endpoint', 10, 1);
