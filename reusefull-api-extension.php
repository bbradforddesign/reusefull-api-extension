<?php
/**
 * Plugin Name: ReUseFull API extension
 * Plugin URI: https://github.com/bbradforddesign/reusefull-api-extension.git
 * Description: Extends the directory to allow external apps access
 * Version: 1.0
 * Author: Blake Bradford
 * Author URI: http://www.bbradforddesign.com
 */


// all metabox fields for directory listings. any missing?
$custom_meta_arr = array(
	'_directory_id',
	'_attached_image',
	'_attached_images_order',
	'_content_field_1', // summary
	'_content_field_2', // address
	'_content_field_3', // description
	'_content_field_4', // categories
	'_content_field_5', // listing tags
	'_content_field_6', // phone
	'_content_field_7', // website
	'_content_field_8', // email
	'_content_field_9', // religious
	'_content_field_10', // pick-up service
	'_content_field_11', // resell items
	'_content_field_12', // amazon wishlist link
	'_content_field_13', // Takes most items in good conditions
	'_content_field_14', // cash donation link
	'_content_field_15', // volunteer signup link
	'_attached_image_as_logo',
	'_thumbnail_id',
	'_listing_created',
	'_order_date',
	'_listing_status',
	'_is_claimable',
	'_location_id',
	'_address_line_1',
	'_address_line_2',
	'_zip_or_postal_index',
	'_additional_info',
	'_manual_coords',
	'_map_coords_1',
	'_map_coords_2',
	'_map_icon_file',
	'_map_zoom',
	'_attached_video_id'
);

// register each meta field to show in REST
foreach ($custom_meta_arr as $custom_meta) {
	register_meta('post', $custom_meta, [
		'object_subtype' => 'w2dc_listing', // only return field on directory listings
		'show_in_rest'   => true,
	]);
};

// add REST support for directory listings
add_filter( 'register_post_type_args', 'custom_post_args_append', 10, 2 );
function custom_post_args_append( $args, $post_type ) {
	if ( $post_type === "w2dc_listing" ) {
		// expose listing to REST API
		$args['show_in_rest'] = true;
		$args['rest_base'] = 'listings';
		$args['rest_controller_class'] = 'WP_REST_Posts_Controller';
	}

	return $args;
}

// add custom field support for directory listings on API
add_action('init', 'listings_add_custom', 100);
function listings_add_custom() {
	add_post_type_support('w2dc_listing', 'custom-fields');
}



// expose taxonomies to REST API
add_filter( 'register_taxonomy_args', 'custom_tax_args_append', 10, 2 );
function custom_tax_args_append( $args, $tax_type ) {
	if ( $tax_type == "w2dc-category" ) {
		$args['show_in_rest'] = true;
		$args['rest_base'] = 'categories';
		$args['rest_controller_class'] = 'WP_REST_Terms_Controller';
	} else if ( $tax_type == "w2dc-location") {
		$args['show_in_rest'] = true;
		$args['rest_base'] = 'locations';
		$args['rest_controller_class'] = 'WP_REST_Terms_Controller';
	} else if ( $tax_type == "w2dc-tag") {
		$args['show_in_rest'] = true;
		$args['rest_base'] = 'tags';
		$args['rest_controller_class'] = 'WP_REST_Terms_Controller';
	}

	return $args;
}

// add REST fields to edit custom fields (listing address, contact, anything within the custom meta array above)
add_action('rest_api_init', 'add_address_line_1');
function add_address_line_1() {
    register_rest_field('post',
        '_address_line_1',
        array(
            'get_callback' => 'rest_get_address_line_1',
            'update_callback' => 'rest_update_address_line_1',
            'schema' => array(
                                'description' => 'Address line 1',
                                'type' => 'string',
                                'context' => array('view', 'edit')
                            )
        )
    );
}

function rest_get_address_line_1($post, $field_name, $request) {
  // Make modifications to field name if required. 
  return get_post_meta($post->ID, $field_name);
}

function rest_update_address_line_1($value, $post, $field_name) {
  // Perform Validation of input
  if (!$value || !is_string($value)) {
    return;
  }
  // Update the field
  return update_post_meta($post->ID, $field_name, $value);
}