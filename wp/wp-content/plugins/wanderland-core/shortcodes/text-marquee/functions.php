<?php

if ( ! function_exists( 'wanderland_core_add_text_marquee_shortcodes' ) ) {
	function wanderland_core_add_text_marquee_shortcodes( $shortcodes_class_name ) {
		$shortcodes = array(
			'WanderlandCore\CPT\Shortcodes\TextMarquee\TextMarquee'
		);
		
		$shortcodes_class_name = array_merge( $shortcodes_class_name, $shortcodes );
		
		return $shortcodes_class_name;
	}
	
	add_filter( 'wanderland_core_filter_add_vc_shortcode', 'wanderland_core_add_text_marquee_shortcodes' );
}

if ( ! function_exists( 'wanderland_core_set_text_marquee_icon_class_name_for_vc_shortcodes' ) ) {
	/**
	 * Function that set custom icon class name for text marquee shortcode to set our icon for Visual Composer shortcodes panel
	 */
	function wanderland_core_set_text_marquee_icon_class_name_for_vc_shortcodes( $shortcodes_icon_class_array ) {
		$shortcodes_icon_class_array[] = '.icon-wpb-text-marquee';
		
		return $shortcodes_icon_class_array;
	}
	
	add_filter( 'wanderland_core_filter_add_vc_shortcodes_custom_icon_class', 'wanderland_core_set_text_marquee_icon_class_name_for_vc_shortcodes' );
}