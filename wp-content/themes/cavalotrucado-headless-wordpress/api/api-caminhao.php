<?php
function get_truck_by_slug($request)
{
  $slug = sanitize_text_field($request->get_param('slug'));
  $page_object = get_page_by_path($slug, OBJECT, 'vehicle');
  $id = $page_object->ID;
  $brand = wp_get_object_terms($id, 'brand');

  $mainImage = get_field('vehicle_main_photo', $id);
  if ($mainImage):
    $size = 'large';
    $thumb = $mainImage['sizes'][$size];
    $mainImageUrl = esc_url($thumb);
  endif;

  $vehicle['data'] = array(
    'id' => $id,
    'title' => $page_object->post_title,
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

function get_random_4_trucks()
{

  $args = [
    'post_status' => 'publish',
    'numberposts' => 20,
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
    $data[$i]['vehicle_main_photo'] = $mainImageUrl;
    $data[$i]['vehicle_short_text_1'] = get_field('vehicle_short_text_1', $post->ID);
    $data[$i]['vehicle_short_text_2'] = get_field('vehicle_short_text_2', $post->ID);
    $data[$i]['vehicle_short_text_3'] = get_field('vehicle_short_text_3', $post->ID);

    $i++;
  }

  $response = new WP_REST_Response($data);
  $response->set_status(200);

  return $response;
}

add_action('rest_api_init', function () {
  register_rest_route('custom/v1', 'truck/(?P<slug>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => 'get_truck_by_slug',
    'args' => array(
      'slug' => array(
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
 * to skip cache add ?skip_cache=1 to the end of url.
 */
function wprc_add_acf_posts_endpoint($allowed_endpoints)
{
  if (!isset($allowed_endpoints['custom/v1']) || !in_array('random_4_trucks', $allowed_endpoints['custom/v1'])) {
    $allowed_endpoints['custom/v1'][] = 'random_4_trucks';
    $allowed_endpoints['custom/v1'][] = 'truck/(?P<slug>[a-zA-Z0-9-]+)';
    $allowed_endpoints['custom/v1'][] = 'truck';
  }
  return $allowed_endpoints;
}
add_filter('wp_rest_cache/allowed_endpoints', 'wprc_add_acf_posts_endpoint', 10, 1);
