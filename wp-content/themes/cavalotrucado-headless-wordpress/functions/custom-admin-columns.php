<?php
/* 
* Add Custom status
*/
function custom_post_status(){
  register_post_status( 'sold', array(
      'label'                     => _x( 'Vendido ', 'post status label', 'plugin-domain' ),
      'public'                    => true,
      'label_count'               => _n_noop( 'Vendidos <span class="count">(%s)</span>', 'Vendidos <span class="count">(%s)</span>', 'plugin-domain' ),
      'post_type'                 => array( 'vehicle' ), // Define one or more post types the status can be applied to.
      'show_in_admin_all_list'    => true,
      'show_in_admin_status_list' => true,
      'show_in_metabox_dropdown'  => true,
      'show_in_inline_dropdown'   => true,
      'dashicon'                  => 'dashicons-businessman',
  ) );
}
add_action( 'init', 'custom_post_status' );

add_action('admin_footer-post.php',function(){

  global $post;
  $complete = '';
  $label = '';

  if($post->post_type == 'vehicle') {

      if ( $post->post_status == 'sold' ) {
          $complete = ' selected=\"selected\"';
          $label    = 'Vendido';
      }

      $script = <<<SD

      jQuery(document).ready(function($){
          $("select#post_status").append("<option value=\"sold\" '.$complete.'>Vendido</option>");
          
          if( "{$post->post_status}" == "sold" ){
              $("span#post-status-display").html("$label");
              $("input#save-post").val("Vendido Salvo");
          }
          var jSelect = $("select#post_status");
              
          $("a.save-post-status").on("click", function(){
              
              if( jSelect.val() == "sold" ){
                  
                  $("input#save-post").val("Vendido Salvo");
              }
          });
    });


SD;

      echo '<script type="text/javascript">' . $script . '</script>';
  }

});

add_action('admin_footer-edit.php',function() {
  global $post;
  if( $post->post_type == 'vehicle') {
    
      echo "<script>
      jQuery(document).ready( function() {
          jQuery( 'select[name=\"_status\"]' ).append( '<option value=\"sold\">Vendido</option>' );
      });
      </script>";
    
  }
});


function display_sold_state( $states ) {
  global $post;
  $arg = get_query_var( 'post_status' );
  if($arg != 'sold'){
      if($post->post_status == 'sold'){
            return array('Vendido');
      }
  }
  return $states;
}
add_filter( 'display_post_states', 'display_sold_state' );

/*
* Add columns to vehicle CPT
*/

function add_acf_columns ( $columns ) {
  return array_merge ( $columns, array ( 
    'vehicle_photo' => __ ( 'Foto' ),
    'vehicle_status'   => __ ( 'Status' ),
    'vehicle_year'   => __ ( 'Ano' ),
    'vehicle_price'   => __ ( 'Valor' ),
    'vehicle_state'   => __ ( 'Localização' )
  ) );
}
add_filter ( 'manage_vehicle_posts_columns', 'add_acf_columns' );

function vehicle_custom_column ( $column, $post_id ) {
  switch ( $column ) {
    case 'vehicle_photo':
      $thumb = get_field('vehicle_main_photo');
      echo '<img src="'.$thumb.'" width="60" />';
      break;
    case 'vehicle_status':
      $status = get_post_status( $post_id );
      //echo $status;
      switch ($status) {
        case "publish":
              echo "Publicado";
              break;
        case "draft":
              echo "Rascunho";
              break;
        case "pending":
              echo "Em revisão";
              break;
        case "sold":
              echo "Vendido";
              break;
        default:
              echo "Ativo";
              break;
      }
      break;
    case 'vehicle_year':
      $year = get_post_meta ( $post_id, 'vehicle_year', true );
      $year_model = get_post_meta ( $post_id, 'vehicle_year_model', true );
      echo $year;
      echo ($year_model != null) ? " / " . $year_model : "";
      break;
    case 'vehicle_price':
      $showPrice = get_post_meta ( $post_id, 'vehicle_show_price', true );
      $price = get_post_meta ( $post_id, 'vehicle_price', true );
      echo ($showPrice == 0) ? "R$: " . $price : "Sob Consulta";
      break;
    case 'vehicle_state':
      $state = get_post_meta ( $post_id, 'vehicle_state', true );
      echo ($state == 'NO') ? "Não informado" : $state;
      break;
  }
}
add_action ( 'manage_vehicle_posts_custom_column', 'vehicle_custom_column', 10, 2 );



/*
* Add Sortable columns
*/

function vehicle_column_register_sortable( $columns ) {
	$columns['vehicle_active'] = 'vehicle_active';
	$columns['vehicle_sold'] = 'vehicle_sold';
  $columns['vehicle_year'] = 'vehicle_year';
  $columns['vehicle_price'] = 'vehicle_price';
  $columns['vehicle_state'] = 'vehicle_state';
	return $columns;
}
add_filter('manage_edit-vehicle_sortable_columns', 'vehicle_column_register_sortable' );

?>