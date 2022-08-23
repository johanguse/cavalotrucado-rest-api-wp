<?php

add_filter('algolia_post_shared_attributes', 'vehicle_attributes', 10, 2);
add_filter('algolia_searchable_post_shared_attributes', 'vehicle_attributes', 10, 2);

/**
 * @param array   $attributes
 * @param WP_Post $post
 *
 * @return array
 */
function vehicle_attributes(array $attributes, WP_Post $post)
{

  if ('vehicle' !== $post->post_type) {
    // We only want to add an attribute for the 'speaker' post type.
    // Here the post isn't a 'speaker', so we return the attributes unaltered.
    return $attributes;
  }

  // Get the field value with the 'get_field' method and assign it to the attributes array.
  // @see https://www.advancedcustomfields.com/resources/get_field/
  $attributes['vehicle_model_name'] = get_field('vehicle_model_name', $post->ID);
  $attributes['vehicle_year'] = get_field('vehicle_year', $post->ID);
  $attributes['vehicle_year_model'] = get_field('vehicle_year_model', $post->ID);

  // Always return the value we are filtering.
  return $attributes;
}
