<?php
// https://webdevstudios.com/2021/02/09/wp-search-with-algolia/
function wds_algolia_exclude_post_types($post_types)
{

  // Ignore these post types.
  unset($post_types['acf-field_group']);
  unset($post_types['testimonials']);
  unset($post_types['forms']);

  return $post_types;
}
add_filter('algolia_searchable_post_types', 'wds_algolia_exclude_post_types');
