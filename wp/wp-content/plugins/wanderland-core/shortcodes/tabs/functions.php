<?php

if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
	class WPBakeryShortCode_Mkdf_Tabs extends WPBakeryShortCodesContainer {}
	class WPBakeryShortCode_Mkdf_Tabs_Item extends WPBakeryShortCodesContainer {}
}

if ( ! function_exists( 'wanderland_core_add_tabs_shortcodes' ) ) {
	function wanderland_core_add_tabs_shortcodes( $shortcodes_class_name ) {
		$shortcodes = array(
			'WanderlandCore\CPT\Shortcodes\Tabs\Tabs',
			'WanderlandCore\CPT\Shortcodes\Tabs\TabsItem'
		);
		
		$shortcodes_class_name = array_merge( $shortcodes_class_name, $shortcodes );
		
		return $shortcodes_class_name;
	}
	
	add_filter( 'wanderland_core_filter_add_vc_shortcode', 'wanderland_core_add_tabs_shortcodes' );
}

if ( ! function_exists( 'wanderland_core_set_tabs_custom_style_for_vc_shortcodes' ) ) {
	/**
	 * Function that set custom css style for tabs shortcode
	 */
	function wanderland_core_set_tabs_custom_style_for_vc_shortcodes( $style ) {
		$current_style = '.vc_shortcodes_container.wpb_mkdf_tabs_item { 
			background-color: #f4f4f4; 
		}';
		
		$style .= $current_style;
		
		return $style;
	}
	
	add_filter( 'wanderland_core_filter_add_vc_shortcodes_custom_style', 'wanderland_core_set_tabs_custom_style_for_vc_shortcodes' );
}

if ( ! function_exists( 'wanderland_core_set_tabs_icon_class_name_for_vc_shortcodes' ) ) {
	/**
	 * Function that set custom icon class name for tabs shortcode to set our icon for Visual Composer shortcodes panel
	 */
	function wanderland_core_set_tabs_icon_class_name_for_vc_shortcodes( $shortcodes_icon_class_array ) {
		$shortcodes_icon_class_array[] = '.icon-wpb-tabs';
		$shortcodes_icon_class_array[] = '.icon-wpb-tabs-item';
		
		return $shortcodes_icon_class_array;
	}
	
	add_filter( 'wanderland_core_filter_add_vc_shortcodes_custom_icon_class', 'wanderland_core_set_tabs_icon_class_name_for_vc_shortcodes' );
}