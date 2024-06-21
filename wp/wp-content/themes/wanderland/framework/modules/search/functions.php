<?php

if ( ! function_exists( 'wanderland_mikado_include_search_types_before_load' ) ) {
    /**
     * Load's all header types before load files by going through all folders that are placed directly in header types folder.
     * Functions from this files before-load are used to set all hooks and variables before global options map are init
     */
    function wanderland_mikado_include_search_types_before_load() {
        foreach ( glob( WANDERLAND_MIKADO_FRAMEWORK_SEARCH_ROOT_DIR . '/types/*/before-load.php' ) as $module_load ) {
            include_once $module_load;
        }
    }

    add_action( 'wanderland_mikado_action_options_map', 'wanderland_mikado_include_search_types_before_load', 1 ); // 1 is set to just be before header option map init
}

if ( ! function_exists( 'wanderland_mikado_load_search' ) ) {
	function wanderland_mikado_load_search() {
		$search_type_meta = wanderland_mikado_options()->getOptionValue( 'search_type' );
		$search_type      = ! empty( $search_type_meta ) ? $search_type_meta : 'fullscreen';
		
		if ( wanderland_mikado_active_widget( false, false, 'mkdf_search_opener' ) ) {
			include_once WANDERLAND_MIKADO_FRAMEWORK_MODULES_ROOT_DIR . '/search/types/' . $search_type . '/' . $search_type . '.php';
		}
	}
	
	add_action( 'init', 'wanderland_mikado_load_search' );
}

if ( ! function_exists( 'wanderland_mikado_get_holder_params_search' ) ) {
	/**
	 * Function which return holder class and holder inner class for blog pages
	 */
	function wanderland_mikado_get_holder_params_search() {
		$params_list = array();
		
		$layout = wanderland_mikado_options()->getOptionValue( 'search_page_layout' );
		if ( $layout == 'in-grid' ) {
			$params_list['holder'] = 'mkdf-container';
			$params_list['inner']  = 'mkdf-container-inner clearfix';
		} else {
			$params_list['holder'] = 'mkdf-full-width';
			$params_list['inner']  = 'mkdf-full-width-inner';
		}
		
		/**
		 * Available parameters for holder params
		 * -holder
		 * -inner
		 */
		return apply_filters( 'wanderland_mikado_filter_search_holder_params', $params_list );
	}
}

if ( ! function_exists( 'wanderland_mikado_get_search_page' ) ) {
	function wanderland_mikado_get_search_page() {
		$sidebar_layout = wanderland_mikado_sidebar_layout();
		
		$params = array(
			'sidebar_layout' => $sidebar_layout
		);
		
		wanderland_mikado_get_module_template_part( 'templates/holder', 'search', '', $params );
	}
}

if ( ! function_exists( 'wanderland_mikado_get_search_page_layout' ) ) {
	/**
	 * Function which create query for blog lists
	 */
	function wanderland_mikado_get_search_page_layout() {
		global $wp_query;
		$path   = apply_filters( 'wanderland_mikado_filter_search_page_path', 'templates/page' );
		$type   = apply_filters( 'wanderland_mikado_filter_search_page_layout', 'default' );
		$module = apply_filters( 'wanderland_mikado_filter_search_page_module', 'search' );
		$plugin = apply_filters( 'wanderland_mikado_filter_search_page_plugin_override', false );
		
		if ( get_query_var( 'paged' ) ) {
			$paged = get_query_var( 'paged' );
		} elseif ( get_query_var( 'page' ) ) {
			$paged = get_query_var( 'page' );
		} else {
			$paged = 1;
		}
		
		$params = array(
			'type'          => $type,
			'query'         => $wp_query,
			'paged'         => $paged,
			'max_num_pages' => wanderland_mikado_get_max_number_of_pages(),
		);
		
		$params = apply_filters( 'wanderland_mikado_filter_search_page_params', $params );
		
		wanderland_mikado_get_module_template_part( $path . '/' . $type, $module, '', $params, $plugin );
	}
}

if ( ! function_exists( 'wanderland_mikado_get_search_submit_icon_class' ) ) {
	/**
	 * Loads search submit icon class
	 */
	function wanderland_mikado_get_search_submit_icon_class() {
		$classes = array(
			'mkdf-search-submit'
		);
		
		$classes[] = wanderland_mikado_get_icon_sources_class( 'search', 'mkdf-search-submit' );

		return $classes;
	}
}

if ( ! function_exists( 'wanderland_mikado_get_search_close_icon_class' ) ) {
	/**
	 * Loads search close icon class
	 */
	function wanderland_mikado_get_search_close_icon_class() {
		$classes = array(
			'mkdf-search-close'
		);
		
		$classes[] = wanderland_mikado_get_icon_sources_class( 'search', 'mkdf-search-close' );

		return $classes;
	}
}

if ( ! function_exists( 'wanderland_mikado_override_search_block_templates' ) ) {
	/**
	 * Function that override `core/search` block template
	 *
	 * @see register_block_core_search()
	 */
	function wanderland_mikado_override_search_block_templates( $atts ) {
		if ( ! empty( $atts ) && isset( $atts['render_callback'] ) && 'render_block_core_search' === $atts['render_callback'] && function_exists( 'styles_for_block_core_search' ) ) {
			$atts['render_callback'] = 'wanderland_mikado_render_block_core_search';
		}
		
		return $atts;
	}
	
	add_filter( 'block_type_metadata_settings', 'wanderland_mikado_override_search_block_templates' );
}

if ( ! function_exists( 'wanderland_mikado_render_block_core_search' ) ) {
	/**
	 * Function that dynamically renders the `core/search` block
	 *
	 * @param array $attributes - the block attributes
	 *
	 * @return string - the search block markup
	 *
	 * @see render_block_core_search()
	 */
	function wanderland_mikado_render_block_core_search( $attributes ) {
		static $instance_id = 0;
		
		$attributes = wp_parse_args(
			$attributes,
			array(
				'label'      => esc_html__( 'Search', 'masterds-theme' ),
				'buttonText' => esc_html__( 'Search', 'masterds-theme' ),
			)
		);
		
		$input_id        = 'mkdf-search-form-' . ++ $instance_id;
		$classnames      = classnames_for_block_core_search( $attributes );
		$show_label      = ! empty( $attributes['showLabel'] );
		$use_icon_button = ! empty( $attributes['buttonUseIcon'] );
		$show_input      = ! ( ( ! empty( $attributes['buttonPosition'] ) && 'button-only' === $attributes['buttonPosition'] ) );
		$show_button     = ! ( ( ! empty( $attributes['buttonPosition'] ) && 'no-button' === $attributes['buttonPosition'] ) );
		$input_markup    = '';
		$button_markup   = '';
		$inline_styles   = styles_for_block_core_search( $attributes );
		// function get_color_classes_for_block_core_search doesn't exist in wp 5.8 and below
		$color_classes    = function_exists( 'get_color_classes_for_block_core_search' ) ? get_color_classes_for_block_core_search( $attributes ) : '';
		$is_button_inside = ! empty( $attributes['buttonPosition'] ) && 'button-inside' === $attributes['buttonPosition'];
		// border color classes need to be applied to the elements that have a border color
		// function get_border_color_classes_for_block_core_search doesn't exist in wp 5.8 and below
		$border_color_classes = function_exists( 'get_border_color_classes_for_block_core_search' ) ? get_border_color_classes_for_block_core_search( $attributes ) : '';
		
		$label_markup = sprintf(
			'<label for="%1$s" class="mkdf-search-label screen-reader-text">%2$s</label>',
			$input_id,
			empty( $attributes['label'] ) ? esc_html__( 'Search', 'masterds-theme' ) : esc_html( $attributes['label'] )
		);
		if ( $show_label && ! empty( $attributes['label'] ) ) {
			$label_markup = sprintf(
				'<label for="%1$s" class="mkdf-search-label">%2$s</label>',
				$input_id,
				esc_html( $attributes['label'] )
			);
		}
		
		if ( $show_input ) {
			$input_classes = ! $is_button_inside ? $border_color_classes : '';
			$input_markup  = sprintf(
				'<input type="search" id="%s" class="mkdf-search-field %s" name="s" value="%s" placeholder="%s" %s required />',
				$input_id,
				esc_attr( $input_classes ),
				esc_attr( get_search_query() ),
				esc_attr( $attributes['placeholder'] ),
				// key input doesn't exist in wp 5.8 and below
				array_key_exists( 'input', $inline_styles ) ? $inline_styles['input'] : ''
			);
		}
		
		if ( $show_button ) {
			$button_internal_markup = '';
			$button_classes         = $color_classes;
			$button_classes         .= ! empty( $attributes['buttonPosition'] ) ? ' qodef--' . $attributes['buttonPosition'] : '';
			
			if ( ! $is_button_inside ) {
				$button_classes .= ' ' . $border_color_classes;
			}
			if ( ! $use_icon_button ) {
				if ( ! empty( $attributes['buttonText'] ) ) {
					$button_internal_markup = esc_html( $attributes['buttonText'] );
				}
			} else {
				$button_classes         .= ' qodef--has-icon';
				$button_internal_markup = '<i class="mkdf-icon-ion-icon ion-ios-search "></i>';
			}
			
			$button_markup = sprintf(
				'<button type="submit" class="mkdf-search-submit %s" %s>%s</button>',
				esc_attr( $button_classes ),
				// key button doesn't exist in wp 5.8 and below
				array_key_exists( 'button', $inline_styles ) ? $inline_styles['button'] : '',
				$button_internal_markup
			);
		}
		
		$field_markup_classes = $is_button_inside ? $border_color_classes : '';
		$field_markup         = sprintf(
			'<div class="mkdf-form-holder %s"%s>%s</div>',
			$field_markup_classes,
			$inline_styles['wrapper'],
			$input_markup . $button_markup
		);
		$classnames           .= ' mkdf-search-form';
		$wrapper_attributes   = get_block_wrapper_attributes( array( 'class' => $classnames ) );
		
		return sprintf(
			'<form role="search" method="get" %s action="%s">%s</form>',
			$wrapper_attributes,
			esc_url( home_url( '/' ) ),
			$label_markup . $field_markup
		);
	}
}
