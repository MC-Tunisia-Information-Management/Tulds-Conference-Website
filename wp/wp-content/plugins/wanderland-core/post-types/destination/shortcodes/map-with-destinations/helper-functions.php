<?php

if ( ! function_exists( 'wanderland_core_add_map_with_destinations_shortcode' ) ) {
	function wanderland_core_add_map_with_destinations_shortcode( $shortcodes_class_name ) {
		$shortcodes = array(
			'WanderlandCore\CPT\Shortcodes\MapWithDestinations\MapWithDestinations'
		);
		
		$shortcodes_class_name = array_merge( $shortcodes_class_name, $shortcodes );
		
		return $shortcodes_class_name;
	}
	
	add_filter( 'wanderland_core_filter_add_vc_shortcode', 'wanderland_core_add_map_with_destinations_shortcode' );
}

if ( ! function_exists( 'wanderland_core_set_map_with_destinations_icon_class_name_for_vc_shortcodes' ) ) {
	/**
	 * Function that set custom icon class name for this shortcode to set our icon for Visual Composer shortcodes panel
	 */
	function wanderland_core_set_map_with_destinations_icon_class_name_for_vc_shortcodes( $shortcodes_icon_class_array ) {
		$shortcodes_icon_class_array[] = '.icon-wpb-map-with-destinations';
		
		return $shortcodes_icon_class_array;
	}
	
	add_filter( 'wanderland_core_filter_add_vc_shortcodes_custom_icon_class', 'wanderland_core_set_map_with_destinations_icon_class_name_for_vc_shortcodes' );
}