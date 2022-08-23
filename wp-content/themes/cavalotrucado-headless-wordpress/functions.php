<?php
/*
 *  Author: Johan Guse<johanguse@gmail.com>
 *  Custom functions, support, custom post types and more.
 */

add_theme_support("post-thumbnails");

function remove_menus()
{
  remove_menu_page("index.php"); //Dashboard
  remove_menu_page("jetpack"); //Jetpack*
  remove_menu_page("edit-comments.php"); //Comments
}

add_action("admin_menu", "remove_menus");


// Remove Admin bar
// function remove_admin_bar()
// {
//     return false;
// }

//include_once("functions/custom-acf-fields.php");
//include_once("functions/custom-post-types.php");
include_once("algolia/exclude_post_type.php");
include_once("algolia/push_acf.php");
include_once("algolia/settings.php");
include_once("functions/custom-admin-columns.php");

// APIS Functions
require_once("api/api-caminhao.php");

function headless_custom_menu_order($menu_ord)
{
  if (!$menu_ord)
    return true;

  return array(
    "edit.php?post_type=page", // Pages
    "edit.php", // Posts
    "edit.php?post_type=custom_posts", // Custom Post Type
    "separator1", // First separator

    "upload.php", // Media
    "themes.php", // Appearance
    "plugins.php", // Plugins
    "users.php", // Users
    "separator2", // Second separator

    "tools.php", // Tools
    "options-general.php", // Settings
    "separator-last", // Last separator
  );
}
//add_filter( "custom_menu_order", "headless_custom_menu_order", 10, 1 );
//add_filter( "menu_order", "headless_custom_menu_order", 10, 1 );

function headless_disable_feed()
{
  wp_die(__('No feed available, please visit our <a href="' . get_bloginfo("url") . '">homepage</a>!'));
}

add_action("do_feed", "headless_disable_feed", 1);
add_action("do_feed_rdf", "headless_disable_feed", 1);
add_action("do_feed_rss", "headless_disable_feed", 1);
add_action("do_feed_rss2", "headless_disable_feed", 1);
add_action("do_feed_atom", "headless_disable_feed", 1);
add_action("do_feed_rss2_comments", "headless_disable_feed", 1);
add_action("do_feed_atom_comments", "headless_disable_feed", 1);

// Return `null` if an empty value is returned from ACF.
if (!function_exists("acf_nullify_empty")) {
  function acf_nullify_empty($value, $post_id, $field)
  {
    if (empty($value)) {
      return null;
    }
    return $value;
  }
}
add_filter("acf/format_value", "acf_nullify_empty", 100, 3);

// ALGOLIA https://webdevstudios.com/2021/02/09/wp-search-with-algolia/
function wds_algolia_exclude_post($should_index, WP_Post $post)
{

  // If a page has been marked not searchable
  // by some other means, don't index the post.
  if (false === $should_index) {
    return false;
  }


  // ACF Field.
  // Check if a page is searchable.
  $excluded = get_field('exclude_from_search', $post->ID);

  // If not, don't index the post.
  if (1 === $excluded) {
    return false;
  }

  // If all else fails, index the post.
  return true;
}
add_filter('algolia_should_index_searchable_post', 'wds_algolia_exclude_post', 10, 2);
add_filter('algolia_should_index_post', 'wds_algolia_exclude_post', 10, 2);