<?php

if ( ! function_exists( 'wanderland_mikado_get_header_types_options' ) ) {
	function wanderland_mikado_get_header_types_options() {
		$header_type_options = apply_filters( 'wanderland_mikado_filter_header_type_global_option', $header_type_options = array() );
		
		return $header_type_options;
	}
}

if ( ! function_exists( 'wanderland_mikado_get_header_type_default_options' ) ) {
	function wanderland_mikado_get_header_type_default_options() {
		$header_type_option = apply_filters( 'wanderland_mikado_filter_default_header_type_global_option', $header_type_option = '' );
		
		return $header_type_option;
	}
}

if ( ! function_exists( 'wanderland_mikado_get_hide_dep_for_header_behavior_options' ) ) {
	function wanderland_mikado_get_hide_dep_for_header_behavior_options() {
		$hide_dep_options = apply_filters( 'wanderland_mikado_filter_header_behavior_hide_global_option', $hide_dep_options = array() );
		
		return $hide_dep_options;
	}
}

foreach ( glob( WANDERLAND_MIKADO_FRAMEWORK_HEADER_ROOT_DIR . '/admin/options-map/*/*.php' ) as $options_load ) {
	include_once $options_load;
}

foreach ( glob( WANDERLAND_MIKADO_FRAMEWORK_HEADER_TYPES_ROOT_DIR . '/*/admin/options-map/*.php' ) as $options_load ) {
	include_once $options_load;
}

if ( ! function_exists( 'wanderland_mikado_header_options_map' ) ) {
	function wanderland_mikado_header_options_map() {
		$header_type_options              = wanderland_mikado_get_header_types_options();
		$header_type_default_option       = wanderland_mikado_get_header_type_default_options();
		$header_behavior_options_hide_dep = wanderland_mikado_get_hide_dep_for_header_behavior_options();
		
		wanderland_mikado_add_admin_page(
			array(
				'slug'  => '_header_page',
				'title' => esc_html__( 'Header', 'wanderland' ),
				'icon'  => 'fa fa-header'
			)
		);

		$panel_header_type = wanderland_mikado_add_admin_panel(
			array(
				'page'  => '_header_page',
				'name'  => 'panel_header_type',
				'title' => esc_html__( 'Header Type', 'wanderland' )
			)
		);

		wanderland_mikado_add_admin_field(
			array(
				'parent'        => $panel_header_type,
				'type'          => 'radiogroup',
				'name'          => 'header_type',
				'default_value' => $header_type_default_option,
				'label'         => esc_html__( 'Choose Header Type', 'wanderland' ),
				'description'   => esc_html__( 'Select the default header you would like to use', 'wanderland' ),
				'options'       => $header_type_options,
				'args'          => array(
					'use_images'  => true,
					'hide_labels' => true,
				)
			)
		);
		
		$panel_header = wanderland_mikado_add_admin_panel(
			array(
				'page'  => '_header_page',
				'name'  => 'panel_header',
				'title' => esc_html__( 'Header Options', 'wanderland' )
			)
		);

		wanderland_mikado_add_admin_field(
			array(
				'parent'        => $panel_header,
				'type'          => 'select',
				'name'          => 'header_options',
				'default_value' => $header_type_default_option,
				'label'         => esc_html__( 'Choose Header Type to Customize', 'wanderland' ),
				'description'   => esc_html__( 'Select the header type you would like to set styling and behavior options for. The header type you choose here will not affect your website\'s default header, which is chosen above, in the header type field.', 'wanderland' ),
				'options'       => wanderland_mikado_header_types_meta_boxes(),
			)
		);

		wanderland_mikado_add_admin_field(
			array(
				'parent'          => $panel_header,
				'type'            => 'select',
				'name'            => 'header_behaviour',
				'default_value'   => 'fixed-on-scroll',
				'label'           => esc_html__( 'Choose Header Behaviour', 'wanderland' ),
				'description'     => esc_html__( 'Select the behaviour of header when you scroll down to page', 'wanderland' ),
				'options'         => array(
					'fixed-on-scroll'                 => esc_html__( 'Fixed on scroll', 'wanderland' ),
					'no-behavior'                     => esc_html__( 'No Behavior', 'wanderland' ),
					'sticky-header-on-scroll-up'      => esc_html__( 'Sticky on scroll up', 'wanderland' ),
					'sticky-header-on-scroll-down-up' => esc_html__( 'Sticky on scroll up/down', 'wanderland' )
				),
				'dependency' => array(
					'hide' => array(
						'header_options'  => $header_behavior_options_hide_dep
					)
				)
			)
		);
		
		/***************** Header Skin Options -start ********************/
		
		wanderland_mikado_add_admin_field(
			array(
				'parent'        => $panel_header,
				'type'          => 'select',
				'name'          => 'header_style',
				'default_value' => '',
				'label'         => esc_html__( 'Header Skin', 'wanderland' ),
				'description'   => esc_html__( 'Choose a predefined header style for header elements (logo, main menu, side menu opener...)', 'wanderland' ),
				'options'       => array(
					''             => esc_html__( 'Default', 'wanderland' ),
					'light-header' => esc_html__( 'Light', 'wanderland' ),
					'dark-header'  => esc_html__( 'Dark', 'wanderland' )
				)
			)
		);
		
		/***************** Header Skin Options - end ********************/
		
		/***************** Top Header Layout - start **********************/
		
		do_action( 'wanderland_mikado_action_header_top_options_map', $panel_header );
		
		/***************** Top Header Layout - end **********************/
		
		/***************** Logo Area Layout - start **********************/
		
		do_action( 'wanderland_mikado_action_header_logo_area_options_map', $panel_header );
		
		/***************** Logo Area Layout - end **********************/
		
		/***************** Menu Area Layout - start **********************/
		
		do_action( 'wanderland_mikado_action_header_menu_area_options_map', $panel_header );
		
		/***************** Menu Area Layout - end **********************/
		
		/***************** Additional Header Menu Area Layout - start *****************/
		
		do_action( 'wanderland_mikado_action_additional_header_menu_area_options_map', $panel_header );
		
		/***************** Additional Header Menu Area Layout - end *****************/
		
		/***************** Sticky Header Layout - start *******************/
		
		do_action( 'wanderland_mikado_action_header_sticky_options_map', $panel_header );
		
		/***************** Sticky Header Layout - end *******************/
		
		/***************** Fixed Header Layout - start ********************/
		
		do_action( 'wanderland_mikado_action_header_fixed_options_map', $panel_header );
		
		/***************** Fixed Header Layout - end ********************/

		/***************** Header Additional Options - start **********************/

		do_action( 'wanderland_mikado_action_header_additional_options_map', $panel_header );

		/***************** Header Additional Options - end **********************/
		
		/******************* Main Menu Layout - start *********************/
		
		do_action( 'wanderland_mikado_action_header_main_navigation_options_map' );
		
		/******************* Main Menu Layout - end *********************/
		
		/***************** Additional Main Menu Area Layout - start *****************/
		
		do_action( 'wanderland_mikado_action_additional_header_main_navigation_options_map' );
		
		/***************** Additional Main Menu Area Layout - end *****************/
	}
	
	add_action( 'wanderland_mikado_action_options_map', 'wanderland_mikado_header_options_map', wanderland_mikado_set_options_map_position( 'header' ) );
}