<?php
function wds_algolia_posts_index_settings($settings)
{

  $settings['searchableAttributes'] = [
    'unordered(post_title)',
    'unordered(taxonomies)',
    'unordered(content)',
  ];

  return $settings;
}
add_filter('algolia_posts_index_settings', 'wds_algolia_posts_index_settings');
add_filter('algolia_posts_books_index_settings', 'wds_algolia_posts_index_settings');