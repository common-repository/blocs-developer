<?php
/**
 * Plugin Name: Blocs Developer
 * Plugin URI: https://www.blocsapp.com/wordpress/blocs-developer.zip
 * Description: This plugin is intended for use with the visual web design software <a href="https://blocsapp.com/" target="_blank">Blocs</a>. It expands the Wordpress json API and adds additional endpoints for greater integration with the Blocs design environment. This plugin is <strong>only required</strong> during the development phase and is <strong>not required</strong> once a theme is exported from Blocs and installed into Wordpress. 
 * Version: 1.0.1
 * Author: Cazoobi Limited
 * Author URI: https://www.blocsapp.com
 */


// Register Additional End Points
add_action( 'rest_api_init', function () {

	// Add Blocs Preview End Point
	register_rest_route( 'wp/v2', 'blocs_preview', array(
	'methods' => 'GET',
	'callback' => 'get_blocs_preview',
	));

	// Include Custom Fields in Post Data
	register_rest_field( 'post', 'post-meta-fields', array(
		'get_callback'    => 'blocsapp_get_custom_fields',
		'schema'          => null,
	));

	// Make All Custom Post Types Available
	foreach ( $GLOBALS[ 'wp_post_types' ] as $wp_custom_post )
	{
		$wp_custom_post -> show_in_rest = true;
	} 

	// Include Custom Fields On Custom Posts
    foreach ( get_post_types( '', 'names' ) as $post_type )
    {
		register_rest_field( $post_type, 'post-meta-fields', array(
			'get_callback'    => 'blocsapp_get_custom_fields',
			'schema'          => null,
		));
    }
});


// Get Blocs Preview
function get_blocs_preview() {
	
	$previewData = array(
			'menu'		=> blocsapp_get_menu_data(),
			'max_posts'	=> get_option( 'posts_per_page' ),
			'widgets'	=> blocsapp_get_widget_zones()
	);

	return $previewData;
}

// Get Menu Data
function blocsapp_get_menu_data()
{
	$menus = wp_get_nav_menus();
	$menuLocations = get_nav_menu_locations();
	$menuID = 'Primary Menu';

	if (!empty($menus)) // Has Menus
	{
		if (has_nav_menu('primary')) // Has Primary Menu
		{
			$menuID = $menuLocations['primary'];
		}
		else // Use First Menu
		{
			$menuID = $menus[0];
		}
	}

	return wp_get_nav_menu_items($menuID);
}


// Get Widgets Zone Data
function blocsapp_get_widget_zones() {

	$widgetArray = array();

	foreach ( $GLOBALS[ 'wp_registered_sidebars' ] as $sidebar )
	{
		$widgetID = $sidebar['id'];

		ob_start();
		dynamic_sidebar($widgetID);
		$widgetContent = ob_get_contents();
		ob_end_clean();

		$widgetItem = array(
			'id'	=> $widgetID,
			'name'	=> $sidebar[ 'name' ],
			'content'	=> $widgetContent
		);

     	array_push($widgetArray,$widgetItem);
	}

	return $widgetArray;
}

// Get Post Custom Fields
function blocsapp_get_custom_fields( $object ) {
    return get_post_meta($object[ 'id' ]);
}

// Add Warning
function blocsapp_add_admin_warning(){
    echo '<div class="notice notice-warning">
    		<h4>Important!</h4>
             <p>You currently have the Blocs developer plugin active, please be aware that this plugin makes all <strong>custom fields</strong> and <strong>custom posts</strong> publicly available via the Wordpress rest API. Please disable this plugin when you are no longer developing your site with <a href="https://blocsapp.com/" target="_blank">Blocs</a>.</p>
         </div>';
}
add_action('admin_notices', 'blocsapp_add_admin_warning');