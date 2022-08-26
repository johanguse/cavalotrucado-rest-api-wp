<?php


function convert_chars_to_entities($str)
{
  $str = str_replace('&#215;', '', $str); // Yeah, I know.  But otherwise the gap is confusing.  --Kris


  return $str;
}

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
  $slug = basename(get_permalink($post->ID));
  $attributes['slug'] = $slug;
  $attributes['vehicle_model_name'] = get_field('vehicle_model_name', $post->ID);
  $attributes['vehicle_year'] = get_field('vehicle_year', $post->ID);
  $attributes['vehicle_year_model'] = get_field('vehicle_year_model', $post->ID);
  //Long Text
  $long_text = get_field('vehicle_long_text', $post->ID);
  $long_text_no_html = strip_tags($long_text);
  $long_text_no_html = convert_chars_to_entities($long_text_no_html);
  $attributes['vehicle_long_text'] = $long_text_no_html;
  //Brand
  $brand = wp_get_object_terms($post->ID, 'brand');
  $attributes['brand'] = $brand[0]->name;

  //Thumb
  $mainImage = get_field('vehicle_main_photo', $post->ID);
  if ($mainImage):
    $size = 'thumbnail';
    $thumb = $mainImage['sizes'][$size];
    $mainImageUrl = esc_url($thumb);
  endif;
  $attributes['vehicle_main_photo'] = $mainImageUrl;

  // Always return the value we are filtering.
  return $attributes;
}
