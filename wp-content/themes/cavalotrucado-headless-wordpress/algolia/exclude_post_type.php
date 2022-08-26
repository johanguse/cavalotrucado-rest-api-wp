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

/**
 * @param bool    $should_index
 * @param WP_Post $post
 *
 * @return bool
 */
function exclude_post_types($should_index, WP_Post $post)

{
  // Add all post types you don't want to make searchable.
  $excluded_post_types = array('page', 'post');
  if (false === $should_index) {
    return false;
  }

  return !in_array($post->post_type, $excluded_post_types, true);
}

// Hook into Algolia to manipulate the post that should be indexed.
add_filter('algolia_should_index_searchable_post', 'exclude_post_types', 10, 2);