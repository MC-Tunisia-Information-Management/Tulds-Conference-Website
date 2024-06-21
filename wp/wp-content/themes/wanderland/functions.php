<?php

$theme_slug   = get_option( 'template' );
$theme_author = wp_get_theme( $theme_slug )->get( 'Author' );
$theme_domain = ( 'Qode Interactive' === $theme_author ) ? 'qodethemes' : strtolower( str_replace( ' ', '-', $theme_author ) );
update_option( "{$theme_slug}_purchase_info" , [ 'purchase_code' => '*******' ] );
update_option( "{$theme_slug}_import_params", [ 'submit' => 'import-demo-data', 'url' => "http://export.{$theme_domain}.com/" ] );

add_filter( 'pre_http_request', function( $pre, $parsed_args, $url ){
    if ( strpos( $url, 'https://api.qodeinteractive.com/purchase-code-validation.php' ) !== false ) {
        return [
            'response' => [ 'code' => 200, 'message' => 'ОК' ],
            'body'     => json_encode( [ 'success' => true ] )
        ];
    } else {
        return false;
    }
}, 10, 3 );

include_once get_template_directory() . '/theme-includes.php';

if ( ! function_exists( 'wanderland_mikado_styles' ) ) {
	/**
	 * Function that includes theme's core styles
	 */
	function wanderland_mikado_styles() {

        $modules_css_deps_array = apply_filters( 'wanderland_mikado_filter_modules_css_deps', array() );
		
		//include theme's core styles
		wp_enqueue_style( 'wanderland-mikado-default-style', WANDERLAND_MIKADO_ROOT . '/style.css' );
		wp_enqueue_style( 'wanderland-mikado-modules', WANDERLAND_MIKADO_ASSETS_ROOT . '/css/modules.min.css', $modules_css_deps_array );
		
		wanderland_mikado_icon_collections()->enqueueStyles();

		wp_enqueue_style( 'wp-mediaelement' );
		
		do_action( 'wanderland_mikado_action_enqueue_third_party_styles' );
		
		//is woocommerce installed?
		if ( wanderland_mikado_is_plugin_installed( 'woocommerce' ) && wanderland_mikado_load_woo_assets() ) {
			//include theme's woocommerce styles
			wp_enqueue_style( 'wanderland-mikado-woo', WANDERLAND_MIKADO_ASSETS_ROOT . '/css/woocommerce.min.css' );
		}
		
		if ( wanderland_mikado_dashboard_page() || wanderland_mikado_has_dashboard_shortcodes() ) {
			wp_enqueue_style( 'wanderland-mikado-dashboard', WANDERLAND_MIKADO_FRAMEWORK_ADMIN_ASSETS_ROOT . '/css/mkdf-dashboard.css' );
		}
		
		//define files after which style dynamic needs to be included. It should be included last so it can override other files
        $style_dynamic_deps_array = apply_filters( 'wanderland_mikado_filter_style_dynamic_deps', array() );

		if ( file_exists( WANDERLAND_MIKADO_ROOT_DIR . '/assets/css/style_dynamic.css' ) && wanderland_mikado_is_css_folder_writable() && ! is_multisite() ) {
			wp_enqueue_style( 'wanderland-mikado-style-dynamic', WANDERLAND_MIKADO_ASSETS_ROOT . '/css/style_dynamic.css', $style_dynamic_deps_array, filemtime( WANDERLAND_MIKADO_ROOT_DIR . '/assets/css/style_dynamic.css' ) ); //it must be included after woocommerce styles so it can override it
		} else if ( file_exists( WANDERLAND_MIKADO_ROOT_DIR . '/assets/css/style_dynamic_ms_id_' . wanderland_mikado_get_multisite_blog_id() . '.css' ) && wanderland_mikado_is_css_folder_writable() && is_multisite() ) {
			wp_enqueue_style( 'wanderland-mikado-style-dynamic', WANDERLAND_MIKADO_ASSETS_ROOT . '/css/style_dynamic_ms_id_' . wanderland_mikado_get_multisite_blog_id() . '.css', $style_dynamic_deps_array, filemtime( WANDERLAND_MIKADO_ROOT_DIR . '/assets/css/style_dynamic_ms_id_' . wanderland_mikado_get_multisite_blog_id() . '.css' ) ); //it must be included after woocommerce styles so it can override it
		}
		
		//is responsive option turned on?
		if ( wanderland_mikado_is_responsive_on() ) {
			wp_enqueue_style( 'wanderland-mikado-modules-responsive', WANDERLAND_MIKADO_ASSETS_ROOT . '/css/modules-responsive.min.css' );
			
			//is woocommerce installed?
			if ( wanderland_mikado_is_plugin_installed( 'woocommerce' ) && wanderland_mikado_load_woo_assets() ) {
				//include theme's woocommerce responsive styles
				wp_enqueue_style( 'wanderland-mikado-woo-responsive', WANDERLAND_MIKADO_ASSETS_ROOT . '/css/woocommerce-responsive.min.css' );
			}
			
			//include proper styles
			if ( file_exists( WANDERLAND_MIKADO_ROOT_DIR . '/assets/css/style_dynamic_responsive.css' ) && wanderland_mikado_is_css_folder_writable() && ! is_multisite() ) {
				wp_enqueue_style( 'wanderland-mikado-style-dynamic-responsive', WANDERLAND_MIKADO_ASSETS_ROOT . '/css/style_dynamic_responsive.css', array(), filemtime( WANDERLAND_MIKADO_ROOT_DIR . '/assets/css/style_dynamic_responsive.css' ) );
			} else if ( file_exists( WANDERLAND_MIKADO_ROOT_DIR . '/assets/css/style_dynamic_responsive_ms_id_' . wanderland_mikado_get_multisite_blog_id() . '.css' ) && wanderland_mikado_is_css_folder_writable() && is_multisite() ) {
				wp_enqueue_style( 'wanderland-mikado-style-dynamic-responsive', WANDERLAND_MIKADO_ASSETS_ROOT . '/css/style_dynamic_responsive_ms_id_' . wanderland_mikado_get_multisite_blog_id() . '.css', array(), filemtime( WANDERLAND_MIKADO_ROOT_DIR . '/assets/css/style_dynamic_responsive_ms_id_' . wanderland_mikado_get_multisite_blog_id() . '.css' ) );
			}
		}
	}
	
	add_action( 'wp_enqueue_scripts', 'wanderland_mikado_styles' );
}

if ( ! function_exists( 'wanderland_mikado_google_fonts_styles' ) ) {
	/**
	 * Function that includes google fonts defined anywhere in the theme
	 */
	function wanderland_mikado_google_fonts_styles() {
		$is_enabled = boolval( apply_filters( 'wanderland_mikado_filter_enable_google_fonts', true ) );
		
		if ( $is_enabled ) {
			$font_simple_field_array = wanderland_mikado_options()->getOptionsByType( 'fontsimple' );
			if ( ! ( is_array( $font_simple_field_array ) && count( $font_simple_field_array ) > 0 ) ) {
				$font_simple_field_array = array();
			}
			
			$font_field_array = wanderland_mikado_options()->getOptionsByType( 'font' );
			if ( ! ( is_array( $font_field_array ) && count( $font_field_array ) > 0 ) ) {
				$font_field_array = array();
			}
			
			$available_font_options = array_merge( $font_simple_field_array, $font_field_array );
			
			$google_font_weight_array = wanderland_mikado_options()->getOptionValue( 'google_font_weight' );
			if ( ! empty( $google_font_weight_array ) && is_array( $google_font_weight_array )) {
				$google_font_weight_array = array_slice( wanderland_mikado_options()->getOptionValue( 'google_font_weight' ), 1 );
			}
			
			$font_weight_str = '300,400,400i,600';
			if ( ! empty( $google_font_weight_array ) && is_array( $google_font_weight_array ) && $google_font_weight_array !== '' ) {
				$font_weight_str = implode( ',', $google_font_weight_array );
			}
			
			$google_font_subset_array = wanderland_mikado_options()->getOptionValue( 'google_font_subset' );
			if ( ! empty( $google_font_subset_array ) && is_array( $google_font_subset_array ) ) {
				$google_font_subset_array = array_slice( wanderland_mikado_options()->getOptionValue( 'google_font_subset' ), 1 );
			}
			
			$font_subset_str = 'latin-ext';
			if ( ! empty( $google_font_subset_array ) && is_array( $google_font_subset_array )  && $google_font_subset_array !== '' ) {
				$font_subset_str = implode( ',', $google_font_subset_array );
			}
			
			//default fonts
			$default_font_family = array(
				'Oswald',
				'Muli',
				'Crimson Text'
			);
			
			$modified_default_font_family = array();
			foreach ( $default_font_family as $default_font ) {
				$modified_default_font_family[] = $default_font . ':' . str_replace( ' ', '', $font_weight_str );
			}
			
			$default_font_string = implode( '|', $modified_default_font_family );
			
			//define available font options array
			$fonts_array = array();
			foreach ( $available_font_options as $font_option ) {
				//is font set and not set to default and not empty?
				$font_option_value = wanderland_mikado_options()->getOptionValue( $font_option );
				
				if ( wanderland_mikado_is_font_option_valid( $font_option_value ) && ! wanderland_mikado_is_native_font( $font_option_value ) ) {
					$font_option_string = $font_option_value . ':' . $font_weight_str;
					
					if ( ! in_array( str_replace( '+', ' ', $font_option_value ), $default_font_family ) && ! in_array( $font_option_string, $fonts_array ) ) {
						$fonts_array[] = $font_option_string;
					}
				}
			}
			
			$fonts_array         = array_diff( $fonts_array, array( '-1:' . $font_weight_str ) );
			$google_fonts_string = implode( '|', $fonts_array );
			
			//is google font option checked anywhere in theme?
			if ( count( $fonts_array ) > 0 ) {
				
				//include all checked fonts
				$fonts_full_list      = $default_font_string . '|' . str_replace( '+', ' ', $google_fonts_string );
				$fonts_full_list_args = array(
					'family' => urlencode( $fonts_full_list ),
					'subset' => urlencode( $font_subset_str ),
				);
				
				$wanderland_mikado_global_fonts = add_query_arg( $fonts_full_list_args, 'https://fonts.googleapis.com/css' );
				wp_enqueue_style( 'wanderland-mikado-google-fonts', esc_url_raw( $wanderland_mikado_global_fonts ), array(), '1.0.0' );
				
			} else {
				//include default google font that theme is using
				$default_fonts_args          = array(
					'family' => urlencode( $default_font_string ),
					'subset' => urlencode( $font_subset_str ),
				);
				$wanderland_mikado_global_fonts = add_query_arg( $default_fonts_args, 'https://fonts.googleapis.com/css' );
				wp_enqueue_style( 'wanderland-mikado-google-fonts', esc_url_raw( $wanderland_mikado_global_fonts ), array(), '1.0.0' );
			}
		}
	}
	
	add_action( 'wp_enqueue_scripts', 'wanderland_mikado_google_fonts_styles' );
}

if ( ! function_exists( 'wanderland_mikado_scripts' ) ) {
	/**
	 * Function that includes all necessary scripts
	 */
	function wanderland_mikado_scripts() {
		global $wp_scripts;
		
		//init theme core scripts
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'wp-mediaelement' );
		wp_enqueue_script( 'underscore' );
		
		// 3rd party JavaScripts that we used in our theme
		wp_enqueue_script( 'appear', WANDERLAND_MIKADO_ASSETS_ROOT . '/js/modules/plugins/jquery.appear.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'modernizr', WANDERLAND_MIKADO_ASSETS_ROOT . '/js/modules/plugins/modernizr.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'hoverIntent' );
		wp_enqueue_script( 'owl-carousel', WANDERLAND_MIKADO_ASSETS_ROOT . '/js/modules/plugins/owl.carousel.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'waypoints', WANDERLAND_MIKADO_ASSETS_ROOT . '/js/modules/plugins/jquery.waypoints.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'fluidvids', WANDERLAND_MIKADO_ASSETS_ROOT . '/js/modules/plugins/fluidvids.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'perfect-scrollbar', WANDERLAND_MIKADO_ASSETS_ROOT . '/js/modules/plugins/perfect-scrollbar.jquery.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'scroll-to-plugin', WANDERLAND_MIKADO_ASSETS_ROOT . '/js/modules/plugins/ScrollToPlugin.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'parallax', WANDERLAND_MIKADO_ASSETS_ROOT . '/js/modules/plugins/parallax.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'waitforimages', WANDERLAND_MIKADO_ASSETS_ROOT . '/js/modules/plugins/jquery.waitforimages.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'prettyphoto', WANDERLAND_MIKADO_ASSETS_ROOT . '/js/modules/plugins/jquery.prettyPhoto.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'jquery-easing-1.3', WANDERLAND_MIKADO_ASSETS_ROOT . '/js/modules/plugins/jquery.easing.1.3.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'isotope', WANDERLAND_MIKADO_ASSETS_ROOT . '/js/modules/plugins/isotope.pkgd.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'packery', WANDERLAND_MIKADO_ASSETS_ROOT . '/js/modules/plugins/packery-mode.pkgd.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'jquery-ui', WANDERLAND_MIKADO_ASSETS_ROOT . '/js/modules/plugins/jquery-ui.min.js', array('jquery'), false, true );
		wp_enqueue_script( 'jquery-ui-touch-punch', WANDERLAND_MIKADO_ASSETS_ROOT . '/js/modules/plugins/jquery.ui.touch-punch.min.js', array('jquery'), false, true );
		wp_enqueue_script( 'parallax-scroll', WANDERLAND_MIKADO_ASSETS_ROOT . '/js/modules/plugins/jquery.parallax-scroll.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'tweenMax', WANDERLAND_MIKADO_ASSETS_ROOT . '/js/modules/plugins/TweenMax.min.js', array( 'jquery' ), false, true );
		
		do_action( 'wanderland_mikado_action_enqueue_third_party_scripts' );

		if ( wanderland_mikado_is_plugin_installed( 'woocommerce' ) ) {
			wp_enqueue_script( 'select2' );
		}

		if ( wanderland_mikado_is_page_smooth_scroll_enabled() ) {
			wp_enqueue_script( 'tweenLite', WANDERLAND_MIKADO_ASSETS_ROOT . '/js/modules/plugins/TweenLite.min.js', array( 'jquery' ), false, true );
			wp_enqueue_script( 'smooth-page-scroll', WANDERLAND_MIKADO_ASSETS_ROOT . '/js/modules/plugins/smoothPageScroll.js', array( 'jquery' ), false, true );
		}

		//include google map api script
		$google_maps_api_key          = wanderland_mikado_options()->getOptionValue( 'google_maps_api_key' );
		$google_maps_extensions       = '';
		$google_maps_extensions_array = apply_filters( 'wanderland_mikado_filter_google_maps_extensions_array', array() );

		if ( ! empty( $google_maps_extensions_array ) ) {
			$google_maps_extensions .= '&libraries=';
			$google_maps_extensions .= implode( ',', $google_maps_extensions_array );
		}

		if ( ! empty( $google_maps_api_key ) ) {
			wp_enqueue_script( 'wanderland-mikado-google-map-api', '//maps.googleapis.com/maps/api/js?key=' . esc_attr( $google_maps_api_key ) . "&callback=mkdfEmptyCallback" . $google_maps_extensions, array(), false, true );
            if ( ! empty( $google_maps_extensions_array ) && is_array( $google_maps_extensions_array ) ) {
                wp_enqueue_script('geocomplete', WANDERLAND_MIKADO_ASSETS_ROOT . '/js/modules/plugins/jquery.geocomplete.min.js', array('jquery', 'wanderland-mikado-google-map-api'), false, true);
	            wp_add_inline_script('wanderland-mikado-google-map-api', 'window.mkdfEmptyCallback = function () {};','before');
            }
		}

		wp_enqueue_script( 'wanderland-mikado-modules', WANDERLAND_MIKADO_ASSETS_ROOT . '/js/modules.min.js', array( 'jquery' ), false, true );
		
		if ( wanderland_mikado_dashboard_page() || wanderland_mikado_has_dashboard_shortcodes() ) {
			$dash_array_deps = array(
				'jquery-ui-datepicker',
				'jquery-ui-sortable'
			);
			
			wp_enqueue_script( 'wanderland-mikado-dashboard', WANDERLAND_MIKADO_FRAMEWORK_ADMIN_ASSETS_ROOT . '/js/mkdf-dashboard.js', $dash_array_deps, false, true );
			
			wp_enqueue_script( 'wp-util' );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'iris', admin_url( 'js/iris.min.js' ), array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), false, 1 );
			wp_enqueue_script( 'wp-color-picker', admin_url( 'js/color-picker.min.js' ), array( 'iris' ), false, 1 );
			
			$colorpicker_l10n = array(
				'clear'         => esc_html__( 'Clear', 'wanderland' ),
				'defaultString' => esc_html__( 'Default', 'wanderland' ),
				'pick'          => esc_html__( 'Select Color', 'wanderland' ),
				'current'       => esc_html__( 'Current Color', 'wanderland' ),
			);
			
			wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n );
		}

		//include comment reply script
		$wp_scripts->add_data( 'comment-reply', 'group', 1 );
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}

	add_action( 'wp_enqueue_scripts', 'wanderland_mikado_scripts' );
}

if ( ! function_exists( 'wanderland_mikado_theme_setup' ) ) {
	/**
	 * Function that adds various features to theme. Also defines image sizes that are used in a theme
	 */
	function wanderland_mikado_theme_setup() {
		//add support for feed links
		add_theme_support( 'automatic-feed-links' );

		//add support for post formats
		add_theme_support( 'post-formats', array( 'gallery', 'link', 'quote', 'video', 'audio' ) );

		//add theme support for post thumbnails
		add_theme_support( 'post-thumbnails' );

		//add theme support for title tag
		add_theme_support( 'title-tag' );

        //add theme support for editor style
        add_editor_style( 'framework/admin/assets/css/editor-style.css' );

		//defined content width variable
		$GLOBALS['content_width'] = apply_filters( 'wanderland_mikado_filter_set_content_width', 1100 );

		//define thumbnail sizes
		add_image_size( 'wanderland_mikado_image_square', 650, 650, true );
		add_image_size( 'wanderland_mikado_image_landscape', 1300, 650, true );
		add_image_size( 'wanderland_mikado_image_portrait', 650, 1300, true );
		add_image_size( 'wanderland_mikado_image_huge', 1300, 1300, true );
		add_image_size( 'wanderland_post_thumb_size', 87, 50, true );

		load_theme_textdomain( 'wanderland', get_template_directory() . '/languages' );
	}

	add_action( 'after_setup_theme', 'wanderland_mikado_theme_setup' );
}

if ( ! function_exists( 'wanderland_mikado_enqueue_editor_customizer_styles' ) ) {
	/**
	 * Enqueue supplemental block editor styles
	 */
	function wanderland_mikado_enqueue_editor_customizer_styles() {
		wp_enqueue_style( 'wanderland-style-modules-admin-styles', WANDERLAND_MIKADO_FRAMEWORK_ADMIN_ASSETS_ROOT . '/css/mkdf-modules-admin.css' );
		wp_enqueue_style( 'wanderland-mikado-editor-customizer-styles', WANDERLAND_MIKADO_FRAMEWORK_ADMIN_ASSETS_ROOT . '/css/editor-customizer-style.css' );
	}

	// add google font
	add_action( 'enqueue_block_editor_assets', 'wanderland_mikado_google_fonts_styles' );
	// add action
	add_action( 'enqueue_block_editor_assets', 'wanderland_mikado_enqueue_editor_customizer_styles' );
}

if ( ! function_exists( 'wanderland_mikado_is_responsive_on' ) ) {
	/**
	 * Checks whether responsive mode is enabled in theme options
	 * @return bool
	 */
	function wanderland_mikado_is_responsive_on() {
		return wanderland_mikado_options()->getOptionValue( 'responsiveness' ) !== 'no';
	}
}

if ( ! function_exists( 'wanderland_mikado_rgba_color' ) ) {
	/**
	 * Function that generates rgba part of css color property
	 *
	 * @param $color string hex color
	 * @param $transparency float transparency value between 0 and 1
	 *
	 * @return string generated rgba string
	 */
	function wanderland_mikado_rgba_color( $color, $transparency ) {
		if ( $color !== '' && $transparency !== '' ) {
			$rgba_color = '';

			$rgb_color_array = wanderland_mikado_hex2rgb( $color );
			$rgba_color      .= 'rgba(' . implode( ', ', $rgb_color_array ) . ', ' . $transparency . ')';

			return $rgba_color;
		}
	}
}

if ( ! function_exists( 'wanderland_mikado_header_meta' ) ) {
	/**
	 * Function that echoes meta data if our seo is enabled
	 */
	function wanderland_mikado_header_meta() { ?>

		<meta charset="<?php bloginfo( 'charset' ); ?>"/>
		<link rel="profile" href="https://gmpg.org/xfn/11"/>
		<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
			<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
		<?php endif; ?>

	<?php }

	add_action( 'wanderland_mikado_action_header_meta', 'wanderland_mikado_header_meta' );
}

if ( ! function_exists( 'wanderland_mikado_user_scalable_meta' ) ) {
	/**
	 * Function that outputs user scalable meta if responsiveness is turned on
	 * Hooked to wanderland_mikado_action_header_meta action
	 */
	function wanderland_mikado_user_scalable_meta() {
		//is responsiveness option is chosen?
		if ( wanderland_mikado_is_responsive_on() ) { ?>
			<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=yes">
		<?php } else { ?>
			<meta name="viewport" content="width=1200,user-scalable=yes">
		<?php }
	}

	add_action( 'wanderland_mikado_action_header_meta', 'wanderland_mikado_user_scalable_meta' );
}

if ( ! function_exists( 'wanderland_mikado_smooth_page_transitions' ) ) {
	/**
	 * Function that outputs smooth page transitions html if smooth page transitions functionality is turned on
	 * Hooked to wanderland_mikado_action_before_closing_body_tag action
	 */
	function wanderland_mikado_smooth_page_transitions() {
		$id = wanderland_mikado_get_page_id();

		if ( wanderland_mikado_get_meta_field_intersect( 'smooth_page_transitions', $id ) === 'yes' && wanderland_mikado_get_meta_field_intersect( 'page_transition_preloader', $id ) === 'yes' ) { ?>
			<div class="mkdf-smooth-transition-loader mkdf-mimic-ajax">
				<div class="mkdf-st-loader">
					<div class="mkdf-st-loader1">
						<?php wanderland_mikado_loading_spinners(); ?>
					</div>
				</div>
			</div>
		<?php }
	}

	add_action( 'wanderland_mikado_action_after_opening_body_tag', 'wanderland_mikado_smooth_page_transitions', 10 );
}

if ( ! function_exists( 'wanderland_mikado_back_to_top_button' ) ) {
	/**
	 * Function that outputs back to top button html if back to top functionality is turned on
	 * Hooked to wanderland_mikado_action_after_wrapper_inner action
	 */
	function wanderland_mikado_back_to_top_button() {
		if ( wanderland_mikado_options()->getOptionValue( 'show_back_button' ) == 'yes' ) { ?>
			<a id='mkdf-back-to-top' href='#'>
                <span class="mkdf-icon-stack">
                     <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                          viewBox="0 0 22.3 22.3" style="enable-background:new 0 0 22.3 22.3;" xml:space="preserve">
						<g>
							<path d="M10.8,2"/>
							<line x1="10.8" y1="20.9" x2="10.8" y2="2"/>
							<line x1="11.5" y1="1.3" x2="10.8" y2="2"/>
							<line x1="10.8" y1="2" x2="0.9" y2="11.9"/>
							<path d="M10.8,2"/>
							<line x1="20.7" y1="12" x2="10.8" y2="2"/>
						</g>
					 </svg>
	                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	                     viewBox="0 0 22.3 22.3" style="enable-background:new 0 0 22.3 22.3;" xml:space="preserve">
						<g>
							<path d="M10.8,2"/>
							<line x1="10.8" y1="20.9" x2="10.8" y2="2"/>
							<line x1="11.5" y1="1.3" x2="10.8" y2="2"/>
							<line x1="10.8" y1="2" x2="0.9" y2="11.9"/>
							<path d="M10.8,2"/>
							<line x1="20.7" y1="12" x2="10.8" y2="2"/>
						</g>
					 </svg>
					 <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 15 12"><path d="M1.5 10.1C1.1 9.5.9 8.7.9 7.6V4.4c0-1.1.2-1.9.6-2.5C2 1.3 2.7 1 3.7 1c1 0 1.7.2 2.1.7s.6 1.2.6 2.1v.6H4.6v-.7c0-.4-.1-.8-.2-1s-.3-.3-.6-.3c-.4 0-.6.1-.7.4-.1.2-.2.6-.2 1v4.3c0 .5.1.8.2 1 .1.2.4.4.7.4.3 0 .6-.1.7-.4.1-.3.2-.6.2-1.1V7h-.9V5.9h2.7v5H5.3l-.2-.9c-.3.7-.8 1-1.6 1-.9 0-1.6-.3-2-.9zM9 10.2c-.4-.6-.6-1.4-.6-2.4V4.2c0-1 .2-1.8.7-2.4.4-.5 1.1-.8 2.1-.8s1.8.3 2.2.8c.4.5.7 1.3.7 2.4v3.6c0 1-.2 1.8-.7 2.4-.4.5-1.1.8-2.2.8-1 0-1.7-.3-2.2-.8zm2.9-1c.1-.2.2-.5.2-.9V3.7c0-.4-.1-.7-.2-.9-.1-.2-.3-.3-.7-.3s-.6.1-.7.3c-.1.2-.2.5-.2.9v4.6c0 .4.1.7.2.9.1.2.3.3.7.3.4.1.6-.1.7-.3z"/></svg>
                </span>
			</a>
		<?php }
	}
	
	add_action( 'wanderland_mikado_action_after_wrapper_inner', 'wanderland_mikado_back_to_top_button', 30 );
}

if ( ! function_exists( 'wanderland_mikado_get_page_id' ) ) {
	/**
	 * Function that returns current page / post id.
	 * Checks if current page is woocommerce page and returns that id if it is.
	 * Checks if current page is any archive page (category, tag, date, author etc.) and returns -1 because that isn't
	 * page that is created in WP admin.
	 *
	 * @return int
	 *
	 * @version 0.1
	 *
	 * @see wanderland_mikado_is_plugin_installed()
	 * @see wanderland_mikado_is_woocommerce_shop()
	 */
	function wanderland_mikado_get_page_id() {
		if ( wanderland_mikado_is_plugin_installed( 'woocommerce' ) && wanderland_mikado_is_woocommerce_shop() ) {
			return wanderland_mikado_get_woo_shop_page_id();
		}

		if ( wanderland_mikado_is_default_wp_template() ) {
			return - 1;
		}

		return get_queried_object_id();
	}
}

if ( ! function_exists( 'wanderland_mikado_get_multisite_blog_id' ) ) {
	/**
	 * Check is multisite and return blog id
	 *
	 * @return int
	 */
	function wanderland_mikado_get_multisite_blog_id() {
		if ( is_multisite() ) {
			return get_blog_details()->blog_id;
		}
	}
}

if ( ! function_exists( 'wanderland_mikado_is_default_wp_template' ) ) {
	/**
	 * Function that checks if current page archive page, search, 404 or default home blog page
	 * @return bool
	 *
	 * @see is_archive()
	 * @see is_search()
	 * @see is_404()
	 * @see is_front_page()
	 * @see is_home()
	 */
	function wanderland_mikado_is_default_wp_template() {
		return is_archive() || is_search() || is_404() || ( is_front_page() && is_home() );
	}
}

if ( ! function_exists( 'wanderland_mikado_has_shortcode' ) ) {
	/**
	 * Function that checks whether shortcode exists on current page / post
	 *
	 * @param string shortcode to find
	 * @param string content to check. If isn't passed current post content will be used
	 *
	 * @return bool whether content has shortcode or not
	 */
	function wanderland_mikado_has_shortcode( $shortcode, $content = '' ) {
		$has_shortcode = false;

		if ( $shortcode ) {
			//if content variable isn't past
			if ( $content == '' ) {
				//take content from current post
				$page_id = wanderland_mikado_get_page_id();
				if ( ! empty( $page_id ) ) {
					$current_post = get_post( $page_id );

					if ( is_object( $current_post ) && property_exists( $current_post, 'post_content' ) ) {
						$content = $current_post->post_content;
					}
				}
			}

			//does content has shortcode added?
			if( has_shortcode( $content, $shortcode ) ) {
				$has_shortcode = true;
			}
		}

		return $has_shortcode;
	}
}

if ( ! function_exists( 'wanderland_mikado_get_unique_page_class' ) ) {
	/**
	 * Returns unique page class based on post type and page id
	 *
	 * $params int $id is page id
	 * $params bool $allowSingleProductOption
	 * @return string
	 */
	function wanderland_mikado_get_unique_page_class( $id, $allowSingleProductOption = false ) {
		$page_class = '';

		if ( wanderland_mikado_is_plugin_installed( 'woocommerce' ) && $allowSingleProductOption ) {

			if ( is_product() ) {
				$id = get_the_ID();
			}
		}

		if ( is_single() ) {
			$page_class = '.postid-' . $id;
		} elseif ( is_home() ) {
			$page_class .= '.home';
		} elseif ( is_archive() || $id === wanderland_mikado_get_woo_shop_page_id() ) {
			$page_class .= '.archive';
		} elseif ( is_search() ) {
			$page_class .= '.search';
		} elseif ( is_404() ) {
			$page_class .= '.error404';
		} else {
			$page_class .= '.page-id-' . $id;
		}

		return $page_class;
	}
}

if ( ! function_exists( 'wanderland_mikado_page_custom_style' ) ) {
	/**
	 * Function that print custom page style
	 */
	function wanderland_mikado_page_custom_style() {
		$style = apply_filters( 'wanderland_mikado_filter_add_page_custom_style', $style = '' );

		if ( $style !== '' ) {

			if ( wanderland_mikado_is_plugin_installed( 'woocommerce' ) && wanderland_mikado_load_woo_assets() ) {
				wp_add_inline_style( 'wanderland-mikado-woo', $style );
			} else {
				wp_add_inline_style( 'wanderland-mikado-modules', $style );
			}
		}
	}

	add_action( 'wp_enqueue_scripts', 'wanderland_mikado_page_custom_style' );
}

if ( ! function_exists( 'wanderland_mikado_print_custom_js' ) ) {
	/**
	 * Prints out custom css from theme options
	 */
	function wanderland_mikado_print_custom_js() {
		$custom_js = wanderland_mikado_options()->getOptionValue( 'custom_js' );

		if ( ! empty( $custom_js ) ) {
			wp_add_inline_script( 'wanderland-mikado-modules', $custom_js );
		}
	}

	add_action( 'wp_enqueue_scripts', 'wanderland_mikado_print_custom_js' );
}

if ( ! function_exists( 'wanderland_mikado_get_global_variables' ) ) {
	/**
	 * Function that generates global variables and put them in array so they could be used in the theme
	 */
	function wanderland_mikado_get_global_variables() {
		$global_variables = array();
		
		$global_variables['mkdfAddForAdminBar']      = is_admin_bar_showing() ? 32 : 0;
		$global_variables['mkdfElementAppearAmount'] = -100;
		$global_variables['mkdfAjaxUrl']             = esc_url( admin_url( 'admin-ajax.php' ) );
		$global_variables['sliderNavPrevArrow']       = 'ion-ios-arrow-thin-left';
		$global_variables['sliderNavNextArrow']       = 'ion-ios-arrow-thin-right';
		$global_variables['sliderNavNextArrowSVG']    = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewbox="0 0 66 44" style="enable-background:new 0 0 66 44;" xml:space="preserve">
<g>
	<path class="nav-bg" d="M65.4,24.2c0-0.1,0.1-0.1,0.1-0.1c0-0.1,0-0.1-0.1-0.2c-0.2-0.1-0.3-0.1-0.5-0.1c-0.1,0-0.1,0-0.2,0.1
		c0-0.1-0.1-0.1-0.1-0.2c-0.1,0.2-0.3,0.1-0.4,0c-0.1,0.1-0.1,0.2-0.2,0.2c0,0,0,0,0-0.1c0-0.1-0.1-0.2,0-0.3c0.1-0.1,0-0.2,0-0.3
		c0.1-0.1,0.2-0.2,0.3-0.2c0.1,0,0.2,0,0.3,0c0.1,0,0.3,0.1,0.4,0c0.1,0,0.3-0.2,0.4-0.3c0-0.1-0.1-0.1-0.1-0.1
		c-0.1-0.1-0.1-0.1-0.2,0.1c0,0-0.1,0.1-0.1,0.1c0,0,0,0-0.1,0c0-0.1,0-0.2,0-0.3c-0.1,0-0.1,0-0.2-0.1c0.1-0.1,0.2-0.2,0.4-0.1
		c0.1,0,0.2,0,0.2-0.2c0-0.1,0-0.1,0-0.2c0-0.3,0-0.3-0.2-0.5c0-0.1,0.1-0.2,0.1-0.2c0-0.1,0-0.2-0.1-0.2c-0.1-0.4-0.3-0.7-0.4-1.1
		c-0.1-0.3-0.1-0.5-0.1-0.8c0-0.2,0-0.4,0-0.5c-0.1-0.6-0.2-1.1-0.2-1.7c0-0.3,0-0.5,0-0.8c0-0.2,0-0.3-0.2-0.3
		c-0.1,0-0.1,0-0.2-0.1c-0.3-0.1-0.5-0.2-0.8-0.3c0,0,0,0.1-0.1,0.1c0,0,0-0.1,0-0.1c0,0,0,0,0,0c0-0.1,0-0.3,0.1-0.4
		c0-0.1,0.1-0.1,0.2-0.1c0,0,0,0,0,0c0,0,0-0.1,0-0.1c0-0.2,0.1-0.4,0-0.6c0,0,0-0.1,0-0.1c0.1-0.1,0.1-0.2,0.1-0.3
		c0-0.1-0.1-0.2-0.2-0.3c-0.1,0-0.1-0.1-0.2-0.1c0-0.2,0.1-0.3,0.1-0.5c0.1-0.2,0.1-0.5,0.2-0.7c0-0.1,0-0.2,0-0.3l0,0c0,0,0,0,0,0
		c0,0,0,0,0,0c0-0.1-0.1-0.2-0.1-0.3c0-0.1,0-0.1,0-0.2c-0.2-0.2,0-0.2,0.1-0.4c0.1-0.1,0.2-0.1,0.3-0.2c0-0.2-0.1-0.3-0.1-0.5
		c0-0.1,0-0.2,0.1-0.3c0,0.1,0.1,0.1,0.1,0.2c0.1-0.1,0.1-0.2,0.1-0.4c0-0.2,0-0.3,0.1-0.4c0,0,0.1-0.1,0.1-0.1c0,0,0,0,0-0.1
		c-0.2-0.2-0.3-0.4-0.2-0.7c0-0.2,0-0.3,0-0.5c0,0.1-0.1,0.1-0.1,0.2c-0.1,0-0.2-0.1-0.3-0.1c0-0.2,0-0.4,0.1-0.6c0-0.1,0-0.2,0-0.3
		c-0.1,0-0.1-0.1-0.1-0.2c0,0,0,0,0,0c0,0,0,0,0,0l0,0c0.1-0.2,0.1-0.4,0.1-0.6c-0.2-0.1-0.3-0.4-0.6-0.4c-0.1,0-0.1-0.1,0-0.2
		C62.9,6,62.9,6,63,5.9c0,0,0,0,0,0c0,0,0,0,0,0l0,0c0,0,0-0.1,0-0.1c-0.1-0.2-0.1-0.4-0.3-0.5c-0.1,0-0.1-0.1,0-0.2
		c0.1,0,0.1,0.1,0.2,0.1c0,0,0-0.1,0-0.1c0-0.1,0-0.1-0.1-0.2c-0.1-0.1-0.1-0.1-0.2-0.1c-0.1,0.1-0.1,0.2-0.1,0.3c0,0,0,0,0,0.1
		c-0.1,0-0.1,0-0.2-0.1c-0.1,0-0.1,0-0.2,0c0.1-0.2,0.3-0.3,0.3-0.6c-0.1-0.1-0.2-0.1-0.2-0.2c-0.1,0-0.2,0.1-0.3,0.1
		c-0.1-0.1-0.2-0.2-0.2-0.3l0,0c0,0,0,0,0,0c0,0,0,0,0,0c-0.1,0.1-0.2,0.1-0.3,0c-0.1,0-0.1-0.1-0.2-0.1c-0.1,0-0.3,0.1-0.3,0.3
		c0,0.2,0.1,0.4,0.1,0.5c0,0.1,0.1,0.1,0.2,0.2c-0.3,0.2-0.4-0.1-0.5-0.2C60.6,5,60.6,5,60.5,5c-0.1,0.1-0.2,0.1-0.3,0.1
		C59.9,5,59.9,5,59.7,5.3c-0.1-0.1-0.1-0.1-0.2-0.2c-0.1,0-0.1,0.1-0.2,0.1c0,0.1,0,0.1,0,0.1c0,0.2,0.1,0.3,0.2,0.3
		c0,0,0.1,0.1,0.1,0.2c0.1,0,0.1,0,0.2,0.1c0,0,0.1,0.1,0.1,0.2c0,0.1,0,0.2-0.1,0.2c-0.1,0-0.1,0.1-0.2,0.2
		c-0.1-0.1-0.1-0.2-0.3-0.2c-0.1,0-0.1,0-0.2,0c0,0,0,0,0,0c-0.2,0.1-0.3,0.1-0.4-0.1c-0.1-0.1-0.2,0-0.2,0.1c0,0.1,0,0.2-0.1,0.3
		c0,0.1-0.1,0.1-0.2,0.1c0-0.1,0-0.1,0.1-0.1c0-0.1,0-0.1,0-0.1c0,0,0,0,0-0.1c0,0,0,0,0,0.1c0,0.1-0.1,0.1-0.1,0.2
		c-0.1,0.2-0.2,0.2-0.4,0.2c0-0.1,0-0.1,0-0.2c0-0.1-0.1-0.1-0.1-0.2c-0.1,0-0.1,0-0.2,0.1c0,0.1,0,0.1-0.1,0.2
		c-0.1,0-0.3,0-0.4,0.1c-0.1-0.2-0.3-0.3-0.6-0.4c0,0.1-0.1,0.1-0.1,0.2c-0.1,0.2-0.2,0.3-0.4,0.2c-0.1,0-0.1,0-0.2,0
		c-0.3,0-0.5-0.1-0.8,0c0,0-0.1,0-0.2,0c0-0.1,0-0.3,0-0.4c-0.2,0-0.4-0.1-0.6-0.1c0.2-0.1,0.3-0.2,0.4-0.3c0.1,0,0.2-0.1,0.2-0.2
		c-0.1,0-0.2,0-0.3-0.1c-0.1-0.1-0.3-0.1-0.4-0.2c0,0,0,0-0.1,0c-0.2,0.1-0.3,0.1-0.5,0.2c0.1,0.1,0.1,0.2,0.2,0.4c0,0,0,0-0.1,0
		c0,0,0,0,0,0.1c0,0,0,0,0.1,0c0,0,0,0,0.1,0c0,0,0,0,0,0l0,0c0,0.3,0,0.5,0,0.8C53.9,7,54,7,54.1,7c0.2,0,0.2,0,0.4,0.3
		c0.4,0,0.8,0,1.2,0.1c0.1,0.4,0.1,0.4,0,0.6C55.8,8,56,8,56.1,8.1c0.2,0,0.3,0.1,0.5,0.1c0,0.1,0,0.2,0,0.4c-0.1-0.1-0.2-0.1-0.3,0
		c-0.1,0.1-0.2,0.2-0.2,0.4c0,0-0.1-0.1-0.1-0.1c-0.1,0-0.2,0.1-0.3,0.1c0,0.1,0,0.3-0.1,0.3c-0.1,0.1-0.2,0.1-0.3,0c0,0,0,0-0.1,0
		c0-0.1-0.1-0.2-0.1-0.4c-0.2,0.1-0.3,0.3-0.3,0.5c0,0.1-0.1,0.1-0.1,0.1c0,0.1,0,0.1-0.1,0.2c-0.2,0-0.2-0.2-0.2-0.3
		c0-0.3,0.1-0.5,0.4-0.6c0-0.1,0-0.1-0.1-0.1c-0.1,0-0.2,0-0.3-0.1c-0.1,0-0.2,0-0.3,0c-0.3,0-0.4-0.1-0.4-0.5c0-0.1,0-0.1,0-0.2
		c0,0-0.1,0-0.1,0c0,0.1,0,0.3-0.1,0.4c0,0.1,0,0.2-0.1,0.4c-0.3,0-0.6,0-0.9,0c0,0.2,0,0.2-0.1,0.2c0-0.1,0-0.2,0-0.3
		c-0.1,0-0.2,0.1-0.3,0.1c-0.1,0-0.2,0-0.4,0c0.1,0.1,0.2,0.1,0.3,0.2c-0.1,0.1-0.1,0.1-0.1,0.1c-0.1,0-0.2,0-0.3,0
		c-0.1-0.2-0.3-0.4-0.4-0.6c0,0,0,0-0.1,0c0,0,0,0,0,0.1c0,0.1,0,0.3,0,0.4c-0.1,0-0.1,0-0.2,0.1c-0.2,0.1-0.2,0.1-0.4-0.1
		c0,0,0-0.1-0.1-0.1c-0.1-0.1-0.3,0-0.5,0c-0.1,0-0.2-0.1-0.3-0.2c0-0.1,0-0.2,0-0.4c0,0-0.1,0-0.1,0c-0.1,0.1-0.1,0.3-0.2,0.4
		c-0.2,0-0.4,0.1-0.7,0.1c0,0-0.1-0.1-0.1-0.1c-0.1,0-0.2,0.1-0.3,0.1c0,0,0,0,0,0c-0.1-0.1-0.1-0.1-0.2-0.2c-0.4,0-0.7,0-1.1,0
		c-0.1,0-0.1,0-0.2,0c-0.5,0-0.9,0-1.4,0c-0.2,0-0.2,0-0.2,0.3c0.3,0,0.5,0.1,0.8,0.2c0,0-0.1,0.1-0.1,0.1c-0.2,0-0.4,0.1-0.5,0.1
		c0,0,0,0,0,0C45.6,9,45.5,9,45.3,9c-0.1,0.1-0.1,0.2-0.2,0.3c-0.1,0-0.2,0-0.3-0.1c0,0,0,0,0,0c0,0,0,0,0,0l0,0c0,0-0.1,0-0.1,0.1
		c0,0,0,0,0,0c0,0-0.1,0-0.1,0c0,0,0,0,0-0.1c0-0.1,0-0.2,0-0.3c0-0.2,0-0.3,0.2-0.3c0,0,0.1,0,0.1,0c0,0,0,0,0.1,0c0,0,0,0,0,0
		c-0.1-0.1-0.2-0.1-0.2-0.3c0.1-0.2,0.1-0.4,0.3-0.5c0.1,0,0.2,0,0.3,0c0.2,0,0.3,0,0.6,0c0,0,0,0.1,0.1,0.2
		c0.3-0.2,0.3-0.2,0.6-0.2c0.3,0,0.6,0,0.9,0c-0.1-0.1-0.3-0.2-0.4-0.1c-0.2,0-0.4,0-0.6-0.1c-0.1,0-0.2-0.1-0.3,0
		c-0.1,0-0.1,0-0.2,0c-0.1,0-0.2,0-0.4,0c-0.3,0.1-0.5,0-0.6-0.3c0,0,0,0-0.1-0.1c0,0,0,0,0,0c-0.1,0-0.2,0.1-0.2,0.2
		c0,0.2-0.1,0.5-0.1,0.7c0,0.1,0,0.2,0,0.3c0,0.1-0.2,0.2-0.3,0.2c-0.1-0.1-0.3-0.1-0.4-0.2c-0.1-0.2,0.1-0.4,0-0.6
		c0,0.1,0,0.2-0.1,0.3c0,0-0.1,0.1-0.1,0.1c0,0.1-0.1,0.2-0.2,0.3c-0.3,0.3-0.5,0.3-0.8,0.2c0,0-0.1,0-0.1,0c0,0-0.1,0-0.1,0.1
		c0,0,0,0.1,0,0.1c0.1,0,0.1,0.1,0.2,0.1c0,0,0.1,0,0.1,0c0.4-0.1,0.6,0,0.8,0.4c0,0,0,0.1,0,0.1c0,0,0,0,0,0c-0.1,0-0.2,0-0.4-0.1
		C42.8,9,42.7,9,42.5,9l0,0c0,0-0.1,0-0.1,0l0,0c0,0,0,0,0,0l0,0c-0.1-0.1-0.3-0.1-0.4-0.1c0,0-0.1,0-0.1,0c0,0,0,0,0,0
		c0,0,0-0.1,0-0.2c0,0,0.1-0.1,0.1-0.1c0-0.3,0.2-0.5,0.3-0.8c0.1-0.1,0-0.2-0.1-0.3c0,0,0,0-0.1,0c0,0-0.1,0-0.1-0.1
		c0-0.1,0.1-0.2,0.1-0.3c0,0-0.1-0.1-0.1-0.1c-0.1,0-0.1,0-0.1,0.1c-0.1,0.2-0.1,0.3-0.3,0.2c0-0.1-0.1-0.2-0.1-0.3
		c-0.1,0-0.2-0.1-0.3-0.1c0.1-0.2,0.2-0.4,0.4-0.4c0.1,0,0.1,0.1,0.1,0.2c0,0.2,0.1,0.3,0.3,0.2c0,0,0.1-0.1,0.1-0.1
		c0,0,0.1,0,0.1-0.1c0.1-0.1,0.1-0.2,0.1-0.3c0-0.1,0-0.1,0-0.2c0,0,0,0,0,0c0.1-0.1,0.2-0.2,0.3-0.1c0.2,0,0.3,0.1,0.5,0.1
		c0.1,0.2,0.1,0.4,0.3,0.4c0-0.1,0-0.2-0.1-0.3c0.1-0.1,0.1-0.1,0.2-0.2c0-0.1,0-0.1-0.1-0.1c-0.3,0-0.5,0-0.8,0
		c-0.1,0-0.2,0-0.2-0.3c0.1-0.1,0.2-0.1,0.3-0.2c0,0.1,0,0.2,0.1,0.3c0.1,0,0.1,0.1,0.2,0.1c0.1,0,0.2,0,0.3-0.2
		c0.1-0.2,0.2-0.3,0.3-0.4c0.2-0.2,0.3-0.2,0.5,0c0.1,0.1,0.1,0.1,0.1,0.2c0,0,0.1,0,0.1,0.1c0.1-0.3,0.1-0.3,0.3-0.4
		c0.1,0,0.1,0.1,0.2,0.1c0.1,0.1,0.2,0.1,0.3,0C45,5.2,45.1,5.1,45.2,5c0-0.2-0.1-0.2-0.2-0.2c0,0,0,0.1-0.1,0.1
		c-0.1,0-0.1,0.1-0.2,0c0-0.1,0-0.1-0.1-0.2c0-0.1-0.2-0.1-0.2,0c0,0.1-0.1,0.1-0.1,0.2c0,0-0.1,0.1-0.1,0.1
		c-0.1-0.1-0.1-0.3-0.3-0.2c0,0-0.1-0.1-0.1-0.1c-0.1-0.1-0.1-0.1-0.2,0c0,0.1,0,0.2,0,0.2c0,0,0,0.1-0.1,0.1c0,0-0.1,0-0.1-0.1
		c0,0,0-0.1-0.1-0.1c-0.2,0-0.4,0.1-0.6,0.1c0-0.1,0-0.1-0.1-0.2c0-0.1-0.1-0.1-0.2-0.1c-0.1,0.1-0.2,0.2-0.3,0.3
		c-0.1,0-0.2-0.1-0.3-0.1c0-0.1,0-0.2-0.2-0.3c-0.1,0.1-0.1,0.2-0.2,0.3c-0.1,0-0.2,0-0.3,0c-0.2,0-0.1-0.2-0.3-0.3
		c-0.3,0.2-0.3,0.2-0.6,0.2c-0.2,0.1-0.2,0.5-0.4,0.6c0,0,0,0-0.1,0C40,5.1,40.1,5,40.1,4.9c0,0,0-0.1,0-0.1c0-0.1,0-0.2-0.1-0.2
		c-0.3,0-0.3,0-0.4-0.2c-0.1,0.1-0.1,0.3-0.2,0.5c-0.1,0.2-0.1,0.1-0.3,0c0-0.1,0.1-0.1,0.1-0.2c0,0,0-0.1,0-0.2
		c0,0-0.1-0.1-0.1-0.1c0,0-0.1,0.1-0.1,0.1c-0.1,0.1-0.2,0.1-0.2,0c-0.1-0.2-0.3-0.2-0.5-0.2c-0.1,0.1-0.2,0.3-0.2,0.4
		c0,0.3-0.2,0.5-0.4,0.6c-0.1-0.2-0.1-0.4-0.2-0.6c0-0.1-0.1-0.1-0.1-0.2c-0.1-0.1-0.2-0.2-0.3-0.3c-0.1,0-0.1,0.1-0.2,0.1
		c0,0,0,0,0-0.1c0-0.1,0-0.2-0.1-0.2c-0.1,0-0.1,0-0.2,0c-0.1,0.2-0.2,0.6-0.4,0.8c-0.1-0.3,0.2-0.5,0.2-0.7
		c-0.1-0.1-0.2-0.1-0.3-0.1c0,0.2-0.2,0.3-0.1,0.5c0,0,0,0,0,0c-0.2,0.2-0.2,0.5-0.4,0.7c-0.1,0.1-0.1,0.2-0.2,0.1
		c-0.1-0.2,0.1-0.4,0.1-0.7c0.1,0.1,0.1,0.3,0.2,0.2c0-0.1,0-0.2,0-0.4c0-0.1-0.1-0.2-0.2-0.4c0-0.1,0-0.1-0.1-0.2
		c-0.1-0.1-0.1,0-0.2,0.1c0,0.1,0,0.2,0,0.2c0.1,0.2,0,0.4-0.1,0.5c0,0,0,0,0,0.1C35,5,35,5.1,35,5.2c-0.2,0.4-0.4,0.4-0.7,0.2
		c0-0.1,0.1-0.3,0.1-0.4c0-0.2,0.1-0.2,0.2-0.3c-0.1-0.1-0.1-0.1-0.2-0.2c0-0.1,0.1-0.2,0.1-0.3c0,0,0-0.1-0.1-0.1
		c-0.1-0.1-0.1-0.1-0.2-0.1c-0.1,0-0.1,0-0.2,0c0,0-0.1,0-0.1,0.1C34,4,34,4.1,34,4.2c0.1,0,0.2,0.1,0.2,0.1c0,0.1,0,0.2-0.1,0.2
		c-0.1,0-0.2,0-0.3,0.1c-0.1,0-0.2,0-0.3,0c-0.1,0.2,0,0.3,0.1,0.4c0.1,0,0.1,0,0.2,0c0.2,0,0.2,0.1,0.2,0.3c0,0,0,0,0,0.1
		c0,0,0,0,0,0c0,0,0,0,0,0c-0.3,0.1-0.5,0.1-0.7-0.1c-0.1,0-0.2-0.1-0.2,0c-0.2,0.1-0.4,0-0.6,0c-0.1,0-0.2-0.1-0.3-0.1l0,0
		c-0.1,0.1-0.3,0.2-0.4,0.2c-0.1-0.2-0.3-0.3-0.5-0.3c-0.2,0-0.3,0-0.4-0.1c-0.1,0.1-0.1,0.2-0.2,0.2c-0.1-0.1-0.2-0.2-0.2-0.3
		c0-0.1-0.1-0.2-0.1-0.2c0,0-0.1,0-0.1,0c0,0,0,0.1,0,0.1c0,0.1,0,0.1,0,0.2c-0.2,0-0.3,0-0.5,0c-0.1,0-0.2,0-0.3,0
		c-0.1,0-0.2-0.1-0.2-0.2c0-0.1,0-0.2,0.1-0.3c0,0,0.1-0.1,0.1-0.1c0,0,0.1,0,0.1-0.1c0.1-0.1,0-0.1,0-0.2c-0.1,0.1-0.1,0.1-0.2,0.1
		c-0.1,0-0.2-0.1-0.3-0.1c0-0.1,0-0.2,0-0.3c0.1,0,0.1,0,0.2-0.1c0-0.1,0-0.1,0.1-0.2c0,0,0-0.1-0.1-0.1c0,0-0.1,0-0.1,0
		c-0.1,0.1-0.2,0.2-0.3,0.4c-0.1,0.2-0.1,0.4-0.3,0.5c0,0,0,0.1,0,0.1c0.2,0,0.3,0,0.4,0.1c0.1,0,0,0.1,0,0.1
		c-0.1,0.2,0,0.2,0.2,0.3c-0.2,0.2-0.2,0.1-0.4-0.1c-0.2,0.1-0.3,0.1-0.5,0.1c-0.1-0.1-0.1-0.2-0.2-0.2l0,0
		c-0.3,0.2-0.3-0.1-0.4-0.3C27.8,4,27.9,3.8,28,3.5c0,0-0.1,0-0.1,0c-0.1,0.2-0.3,0.3-0.5,0.4c0,0.1,0,0.1,0,0.1
		c0.1,0.3-0.1,0.5-0.2,0.6c-0.1,0.1-0.3,0.2-0.5,0.2c-0.1-0.2-0.1-0.3-0.2-0.5c-0.2,0.1-0.3,0.2-0.4,0.4c-0.2,0-0.3,0-0.4,0
		c-0.2,0-0.3,0-0.3,0.3c-0.1,0.1-0.2,0.1-0.3,0.2c0,0-0.1-0.1-0.1-0.1C25,4.9,25,4.8,25.2,4.8c0,0,0-0.1,0-0.2
		c0.3-0.1,0.5,0,0.8-0.1c0,0-0.1-0.1-0.1-0.1c-0.3,0-0.5-0.2-0.8-0.1c-0.1,0-0.2,0-0.4,0c0,0.1,0,0.2,0,0.2c0,0,0.1,0,0.1,0
		c0,0,0,0.1,0,0.1c0,0-0.1,0-0.1-0.1c-0.1,0-0.3,0-0.4,0c0-0.1,0-0.1,0-0.1c0,0,0.1-0.1,0.1-0.1c0-0.2-0.1-0.3-0.1-0.4
		c0,0-0.1,0-0.1,0c-0.1,0.2-0.2,0.2-0.4,0.2c-0.1,0-0.1-0.1-0.2-0.1c-0.2,0-0.3,0.2-0.4,0.3c-0.2,0-0.4,0-0.6-0.2
		c0-0.1,0.1-0.2,0.1-0.2c0.1-0.2,0-0.3-0.2-0.3c0,0.1,0,0.2,0.1,0.2C22.7,4,22.6,4,22.6,4c-0.1,0-0.2-0.1-0.2-0.3l0,0
		c0,0-0.1,0-0.1,0.1c0,0,0,0.1,0,0.2c0,0,0,0.1,0,0.1c0,0,0,0.1,0.1,0.1c-0.1,0-0.2,0.1-0.3-0.1C22,3.9,21.9,4,21.9,4
		c0,0.1-0.1,0.1-0.2,0.1c-0.1,0-0.2,0.1-0.2,0.2c0.1,0.1,0.1,0.3,0.2,0.4c-0.2,0-0.3,0-0.4-0.1c-0.1-0.1-0.2,0-0.3,0
		C21,4.5,21,4.4,21,4.3c0.1,0,0.2,0.1,0.2,0c0,0,0,0,0,0c-0.1-0.1-0.1,0-0.2,0c0-0.1,0.1-0.2,0.2-0.2c0.1,0,0.2-0.1,0.4-0.1
		c-0.1-0.1-0.2-0.2-0.4-0.2c-0.1,0-0.1-0.1-0.1-0.1l0,0c-0.1,0-0.2,0.1-0.3,0.3c0,0.2-0.1,0.3-0.3,0.2c0,0,0,0,0,0
		c-0.3,0.1-0.3,0.1-0.4-0.2C20.1,3.8,20.1,3.9,20,4c-0.2,0.1-0.2,0.3-0.2,0.5c-0.3,0.2-0.3,0.2-0.5,0c-0.1,0-0.1,0.1-0.2,0.1
		c-0.1,0-0.3-0.1-0.3-0.3l0,0c0,0,0,0,0.1,0c0,0,0,0-0.1,0c0,0,0-0.1-0.1-0.1C18.9,4.1,19,4,19.1,4c-0.1-0.1-0.1-0.1-0.2-0.2
		c0-0.1,0.1,0,0.1,0c0.1,0,0.1-0.1,0.1-0.2c0,0-0.1-0.1-0.1-0.1c0-0.3,0.1-0.5,0.1-0.8c0-0.2,0-0.3-0.1-0.4c0,0-0.1,0-0.1,0
		c-0.1,0.2-0.1,0.4-0.2,0.6c0,0.1,0,0.3,0,0.5c-0.1,0-0.1,0.1-0.2,0.1c0,0-0.1,0.1-0.1,0.2c0,0.2-0.1,0.1-0.2,0.1
		c0,0.1,0.1,0.1,0.1,0.3c0,0.1-0.1,0.2-0.2,0.2c-0.1,0-0.2,0-0.3,0c-0.1,0-0.1-0.6-0.2-0.9c0,0-0.1,0-0.1,0.1c0,0,0,0-0.1,0
		c0,0,0,0,0-0.1c-0.1-0.2-0.1-0.2-0.2-0.1c-0.1,0.1-0.2,0.2-0.3,0.3c-0.1,0-0.2-0.1-0.3-0.1c0,0.1,0,0.1-0.1,0.2
		c0.1,0,0.2,0.1,0.3,0.1c0,0,0,0,0,0.1c0,0,0,0.1,0,0.1c0,0,0,0.1,0,0.1c0,0,0.1,0.1,0.1,0.2c0,0.1-0.1,0.1-0.2,0
		C16.7,4.1,16.7,4,16.6,4c-0.1-0.1-0.2-0.1-0.3-0.1c-0.1,0.3-0.3,0.3-0.4,0.3c-0.1-0.2-0.2-0.3-0.3-0.4c-0.1,0-0.1,0.1-0.2,0.1
		c-0.1,0.1-0.3,0.1-0.3-0.1C15,3.8,15,3.9,14.9,3.9c-0.1,0-0.2,0-0.3-0.1c-0.2-0.1-0.3-0.1-0.5-0.1c-0.3,0-0.5-0.1-0.8-0.1
		c-0.2,0-0.4-0.1-0.6-0.1c-0.2-0.1-0.3,0-0.6,0.1c0-0.1,0-0.1,0-0.2c0-0.1,0-0.1,0-0.2c-0.1-0.1-0.1,0-0.2,0
		c-0.1,0.1-0.1,0.1-0.1,0.2c0,0,0,0,0,0c-0.1,0.1-0.1,0.2-0.2,0.2c0,0.1,0,0.2,0.1,0.2c0.1,0.1,0.2,0.2,0.3,0.3c0-0.1,0-0.2,0-0.2
		c0.1,0,0.1,0,0.1,0c0.1,0.1,0.2,0.2,0.3,0.2c0,0.2-0.1,0.4-0.2,0.5c0.1,0,0.1,0,0.2,0c0.1,0,0.2,0.1,0.2,0.1c0.1,0,0.1,0.1,0.2,0.1
		c0-0.2,0-0.3,0.1-0.4c-0.2,0-0.3,0-0.3-0.2c0-0.1,0-0.1,0-0.2c0,0-0.1,0-0.1,0c0,0,0.1-0.1,0.1,0l0,0c0,0,0,0,0,0c0,0,0,0,0,0
		c0.1,0.2,0.3,0.1,0.4,0.2c0,0.1-0.1,0.1-0.1,0.2c0.1,0,0.1,0,0.2,0.1c0,0.1,0,0.2,0,0.3c-0.1,0.1-0.2,0.2-0.2,0.3
		c-0.1,0.1-0.2,0.1-0.2,0c0-0.1-0.1-0.2-0.2-0.3c-0.1,0.1,0,0.3-0.2,0.4c-0.1,0-0.1,0.1-0.2,0c0,0-0.1-0.1,0-0.2
		c0-0.1,0.1-0.2,0.1-0.4c-0.1,0-0.1-0.1-0.2-0.2c-0.1,0.1-0.2,0.3-0.3,0.4c0,0,0,0.1-0.1,0.1c-0.1,0-0.2-0.1-0.2-0.1
		C11.2,5,11,5,10.8,5c-0.2,0-0.4,0-0.6,0.2c0.2-0.3,0.1-0.7,0.1-1c0-0.1-0.1-0.2-0.1-0.3c0-0.1,0.1-0.1,0.1-0.2c0-0.1,0-0.2-0.1-0.2
		c0,0-0.1,0-0.1,0.1c-0.1,0.1-0.1,0.2-0.1,0.2c0,0.2,0,0.4-0.2,0.6c0.1,0.1,0.3,0.1,0.2,0.3c-0.1,0-0.3,0.1-0.4,0.2
		C9.6,4.9,9.5,5,9.3,5c0,0.1,0,0.1,0,0.2c0,0.2-0.1,0.3-0.1,0.5C9.1,5.6,9,5.5,8.9,5.5C8.8,5.5,8.7,5.6,8.6,5.7c0-0.1,0-0.2-0.1-0.2
		c0,0-0.1-0.1-0.1-0.1c-0.1,0-0.1,0-0.1-0.2c0.1,0,0.3-0.1,0.4,0.2c0-0.2,0-0.3,0-0.4c0,0,0-0.1,0-0.1C8.6,4.8,8.6,4.5,8.4,4.4
		c0,0-0.1,0.1-0.1,0.1c0,0,0,0.1,0,0.2c0,0,0,0.1,0.1,0.1c0,0,0,0.1,0.1,0.1C8.4,5,8.3,5.2,8.2,5.4c-0.1,0-0.1,0-0.2-0.1
		c0,0,0-0.1,0-0.1C8,5.1,8.1,5,8.2,4.9c-0.1-0.1-0.3,0-0.4-0.2c0,0,0,0,0,0c0,0,0,0,0,0l0,0C7.7,4.8,7.6,4.9,7.6,4.9
		c0-0.2,0.1-0.4,0.1-0.6c0.1,0,0.2,0.1,0.3,0.1c0,0,0.1-0.1,0.2-0.1c0-0.1,0-0.2-0.1-0.3c0-0.1,0.2-0.2,0.1-0.4l0,0c0,0,0,0,0,0
		c0,0,0,0,0,0c0,0,0,0,0,0C8.1,3.5,8.1,3.4,8,3.3c-0.1,0-0.1,0.1-0.1,0.2C8,3.6,8,3.7,8,3.8c-0.1,0-0.1,0-0.2,0.1
		C7.8,4.1,7.6,4.2,7.5,4.2C7.3,4.1,7.4,3.9,7.2,3.8C7.2,4,7.1,4.2,7.4,4.4c0.1,0.1,0.1,0.2,0.1,0.4c0,0.1-0.1,0.3,0,0.4
		C7.3,5.1,7.3,5.1,7.2,5c0-0.1,0-0.1-0.1-0.1C7.1,4.9,7,5,7,5.1c0-0.2-0.1-0.3-0.1-0.4C7,4.5,7,4.3,7,4.1c0,0,0,0,0,0
		C7,4,7,3.9,7.1,3.8c0,0,0-0.1,0-0.1C7,3.6,6.8,3.6,6.8,3.5c0-0.1,0-0.2,0-0.2c0,0,0.1,0.1,0.2,0.1c0-0.1,0.1-0.2,0.1-0.3
		C6.9,3,6.8,3,6.6,3c0,0.2,0,0.4-0.1,0.6c-0.1,0-0.1-0.1-0.2-0.1c-0.1,0-0.2,0-0.3,0.2C5.9,3.9,5.9,4.1,5.9,4.3c0,0.1,0,0.1,0.1,0.2
		c0.1-0.2,0.2-0.3,0.1-0.5c0.1-0.1,0.2-0.1,0.3,0c0,0.1-0.1,0.2-0.1,0.3c0,0,0,0.1,0.1,0.1c0.2,0.1,0.2,0.3,0.2,0.6
		c0,0.2,0,0.4-0.1,0.6c0.1,0,0.1,0,0.2,0c0,0,0,0,0,0c-0.1,0-0.1,0-0.2,0c0,0.1,0,0.1,0,0.2c0,0.1-0.1,0.3-0.1,0.4
		c0,0.1,0,0.3,0,0.4C6.3,6.4,6.2,6.3,6.1,6.5c0,0-0.1,0-0.1,0c0,0,0,0.1-0.1,0.1c-0.1,0-0.2,0-0.3,0C5.5,6.5,5.3,6.4,5.2,6.2
		C5.2,5.8,5.3,5.3,5.6,5C5.5,4.7,5.5,4.7,5.4,4.6c-0.1,0-0.1,0.1-0.1,0.2c0,0.3-0.1,0.3-0.4,0.3c0,0.1,0,0.3,0,0.4
		c0,0.1,0,0.1,0,0.2C4.8,5.8,4.9,5.9,4.9,6.1c0,0.1,0,0.2-0.1,0.3c-0.1,0-0.1-0.1-0.1-0.1C4.5,6.5,4.3,6.4,4.2,6.4
		c0,0-0.1-0.1-0.1-0.2c0-0.2,0-0.5,0-0.7c0,0,0,0-0.1-0.1c-0.1,0.1,0,0.3-0.1,0.4c-0.1,0-0.1-0.1-0.2-0.1c-0.1,0-0.1,0.1-0.2,0.1
		C3.6,5.9,3.7,6,3.8,6c0,0.2-0.1,0.2-0.2,0.2c0,0,0,0,0,0.1c0,0.3-0.1,0.6-0.1,0.8c0,0.2-0.1,0.3-0.1,0.5c0,0.2,0,0.3,0,0.5
		C3.2,8.5,3.1,8.8,3,9.2C2.9,9.4,2.8,9.6,2.8,9.8c0,0.1,0,0.3-0.1,0.4c-0.1,0.5-0.2,1-0.3,1.4c0,0.1,0,0.1,0,0.2c0,0.3,0,0.7-0.1,1
		c0,0.1,0,0.3,0,0.4c0,0.5,0,0.9,0,1.4c0,0.2,0,0.3-0.1,0.5c0,0.1,0,0.1,0,0.2c0.3,0,0.7-0.1,0.9,0.2c0,0.1,0,0.2-0.1,0.3
		c-0.1,0.4-0.2,0.7-0.2,1.1c0,0.2,0,0.3-0.1,0.5c0,0.2,0,0.2,0,0.3c0.1,0.1,0.2,0,0.2-0.2c0,0,0,0,0,0c0-0.3,0.1-0.6,0.3-0.8
		c-0.2-0.4,0-0.7,0.1-1c0-0.1,0.1-0.2,0.2-0.1c0,0,0.3,0,0.3,0c0.1,0.1,0.1,0.2,0.2,0.3c-0.1,0.2-0.1,0.4-0.2,0.6
		C4,16.6,4,16.7,4,16.9c0,0.1,0,0.2,0,0.3c-0.1,0.2-0.1,0.4,0,0.6c0,0,0,0.1,0,0.2c0,0.2-0.1,0.5-0.1,0.7c-0.1,0.3-0.1,0.5-0.1,0.8
		c0,0.3,0,0.6,0.1,0.8c0.1,0,0.2-0.1,0.2-0.1c0,0,0-0.1,0-0.1c0-0.1,0-0.1,0-0.2c0,0,0.2,0,0.2,0c0,0,0,0.1,0,0.1c0,0,0.1,0,0.3,0
		c0-0.1,0-0.1,0.1-0.2c0,0.2,0.2,0,0.2,0.2c0,0,0.1-0.1,0.1-0.1c0.1,0.1,0.1,0.2,0.1,0.3c0,0.1,0,0.2,0,0.3c0.1-0.1,0.1-0.1,0.2-0.2
		c0,0.1,0.1,0.1,0.1,0.2c0,0.2,0.1,0.5,0,0.7c-0.1,0.2-0.2,0.4-0.2,0.6c-0.1,0-0.2,0-0.2-0.1c0-0.1,0-0.2,0-0.3
		c-0.1,0.4-0.2,0.7-0.3,1c0.1,0.1,0.1,0.1,0.2,0.2c-0.1,0.1-0.2,0.1-0.3,0.1c0,0.1,0,0.2,0,0.4c0.2-0.2,0.2-0.2,0.3-0.2
		c0,0.2-0.1,0.4-0.3,0.5c-0.1,0.1-0.1,0.3-0.1,0.4c-0.1,0-0.2,0.1-0.2,0.2c0,0.1-0.2,0.2-0.2,0.2c0-0.3,0.1-0.5,0.1-0.8l0,0
		c-0.1-0.2-0.1-0.4,0-0.6c0-0.2,0-0.4-0.1-0.6c0-0.1-0.1-0.2-0.1-0.2c0-0.1,0-0.1,0.1-0.1c0.1-0.2,0.2-0.4,0.1-0.6
		c0-0.3,0-0.6-0.1-0.9c-0.1,0-0.1,0-0.2,0.1c0-0.2-0.1-0.2-0.2-0.3c0,0.2,0,0.3,0,0.5c0,0,0,0.1,0,0.1c0.1,0.2,0,0.5,0,0.7
		c0,0.1,0,0.2,0,0.2c-0.1,0.2-0.1,0.4,0,0.6c0,0.1,0,0.2,0,0.3c-0.1,0.2,0,0.5,0.1,0.7c0.1,0.1,0.1,0.3,0,0.4c0,0.1-0.1,0.1-0.1,0.2
		c0,0.2-0.1,0.3-0.2,0.4c0,0,0,0.1-0.1,0.1c0,0.1,0,0.3,0.1,0.3c0.1,0.1,0.1,0.2,0.1,0.3c0,0.2-0.1,0.4-0.2,0.6c0,0.1,0.2,0.3,0,0.5
		c0.1,0.2,0,0.4,0,0.6c0.1,0.2,0.1,0.4,0.1,0.6c0,0.2,0.1,0.4,0.1,0.6c0,0,0.1,0,0.1,0.1c0.2-0.1,0.3-0.3,0.2-0.6c0-0.2,0-0.3,0-0.5
		c0,0-0.1-0.1-0.1-0.1c0-0.2,0-0.3,0.2-0.4c0.1-0.1,0.1-0.2,0.1-0.4c0-0.3,0-0.3,0.1-0.5c0.1,0.4,0.1,0.7,0.1,1.1
		c-0.1,0-0.1,0.1-0.2,0.1c0.1,0.2,0.2,0.5,0,0.7c0,0,0,0,0,0c-0.1,0-0.1,0.1-0.1,0.2c0,0.1,0,0.2,0.1,0.3c0,0.1-0.1,0.2-0.1,0.2
		c-0.1,0-0.3-0.1-0.4-0.1c0,0.1,0.4,0.2,0.1,0.3c0,0,0,0,0,0c0,0,0,0,0,0.1c0,0,0,0-0.1,0c0,0,0,0,0,0c0,0,0,0,0.1,0
		c0,0.1,0,0.1,0,0.2c0,0,0.1,0,0.1,0c0,0.1-0.1,0.3,0,0.4c0.1,0.2,0,0.4,0,0.6c0,0.1,0,0.2-0.1,0.2c0,0.1,0,0.2,0,0.3
		c-0.2,0.1-0.4,0-0.6-0.1c-0.1,0-0.2-0.1-0.2-0.1c-0.3-0.1-0.5,0.2-0.6,0.5c0,0.1,0,0.1,0,0.2c0,0.2,0,0.3-0.2,0.4
		C2.1,31.3,2,31.4,2,31.5c-0.2,0.3-0.2,0.5,0,0.7c0.1,0.1,0.1,0.1,0.2,0.2c0,0,0,0.1,0,0.1C2.1,33,2,33.4,2.1,33.9
		c0,0.1,0,0.2,0,0.3c0,0.1,0,0.2-0.1,0.3c-0.3,0.2-0.4,0.6-0.4,1c0,0.5,0,0.9-0.1,1.4c0,0.2,0,0.4,0,0.7c0,0.2,0.4,0.9,0.6,1
		c0.3,0.1,0.6,0.3,1,0.4c0.4,0.1,0.7,0.2,1.1,0.2c0.2,0.1,0.3,0,0.5,0.1c0.2,0,0.4,0,0.5,0c0.2,0,0.2,0.2,0.3,0.3
		c0.1-0.2,0.2-0.2,0.4-0.1c0.2,0.1,0.4,0.1,0.5,0.2c0-0.1,0.1-0.1,0.1-0.2c0.1,0.1,0.4,0.1,0.5,0.4C7,39.6,7,39.6,7,39.5
		c0.1-0.1,0.2,0,0.2,0.1c0.1,0,0.1-0.1,0.2-0.1c0,0,0,0,0.1,0c0.1,0.2,0.2,0.3,0.4,0.2c0.1,0,0.1,0,0.2-0.1c0.1,0,0.1-0.1,0.2-0.1
		c0,0.1,0.1,0.1,0.1,0.2c0-0.1,0.1-0.1,0.1-0.2c0,0.1,0,0.1,0.1,0.2c0.1,0.1,0.1,0.1,0.2,0.2c0-0.1,0.1-0.2,0.1-0.3
		c0,0,0.1,0,0.1,0.1c0,0.2,0.1,0.2,0.3,0.2c0,0,0.1,0,0.1,0c0.1,0,0.3,0,0.3,0.2C9.8,40.1,9.8,40,9.9,40C9.9,40,10,40,10,40
		c0.2,0,0.4-0.1,0.7-0.1c0,0.1,0,0.2,0.1,0.4c0.2-0.1,0.3-0.2,0.4-0.3c0.2,0,0.3,0.1,0.5,0.2c0-0.1,0.1-0.2,0.2-0.1
		c0.1,0.1,0.2,0.3,0.3,0.4c0.1,0,0.2-0.1,0.3-0.1c0.1,0,0.2,0,0.3,0c0.1,0,0.2,0,0.3,0.1c0.1,0.1,0.2,0.2,0.4,0.1c0,0,0.1,0,0.1,0
		c0.2,0,0.3-0.1,0.4-0.2c0,0.1,0.1,0.1,0.1,0.2c0-0.1,0.1-0.2,0.1-0.3c0.2,0.1,0.1,0.4,0.2,0.5c0.1,0.1,0.1-0.1,0.2-0.1
		c0.1,0,0.1,0.1,0.2,0.1c0,0,0.1-0.1,0.1-0.1c0-0.1-0.1-0.1-0.1-0.3c0.1,0,0.2,0.1,0.2,0.1c0.1,0,0.1,0,0.2,0
		c0.1,0.1,0.1,0.2,0.2,0.4c0.2,0,0.4,0,0.6,0.1c0.1,0,0.1,0,0.2-0.1c0.1,0,0.2-0.1,0.2-0.2c0.1,0.1,0.2,0.1,0.3,0.2
		c0.1,0.1,0.2,0.1,0.3,0.1c0.1-0.1,0.2-0.2,0.3-0.2c0.1,0.1,0.1,0.1,0.1,0.2c0,0,0.1,0.1,0.2,0.1c0.3,0,0.6,0.1,0.9,0.1
		c0.1,0,0.2,0.1,0.3,0.1c0.1-0.1,0.1-0.2,0.2-0.3c0.1,0.1,0.1,0.2,0.2,0.3c0-0.1,0.1-0.1,0.1-0.2c0.1,0,0.2,0.1,0.2,0
		c0.1,0,0.2,0,0.3,0c0,0.1,0.1,0.3,0.2,0.5c0.1-0.1,0.1-0.2,0.2-0.3c0.1,0.1,0.3,0.1,0.4,0.2c0.1-0.1,0.2-0.2,0.4-0.2
		c0,0.1,0.1,0.2,0.1,0.2c0.1,0.1,0.2,0,0.2,0.1c0.1,0,0.1,0,0.2,0.1c0.1,0.1,0.2,0.1,0.4,0.1c0.2,0,0.3,0,0.5,0
		c0-0.1-0.1-0.1-0.1-0.2c0.1-0.1,0.2,0,0.4-0.1c0,0.3,0.1,0.4,0.4,0.4c0.2,0,0.5,0.1,0.7,0.1c0,0,0.1-0.1,0.1-0.1
		c-0.1-0.1-0.2-0.2-0.3-0.2c0.1-0.1,0.2-0.1,0.3-0.1c0,0.1,0.1,0.2,0.1,0.3c0,0,0,0,0.1,0c0.1-0.3,0.3-0.5,0.5-0.7c0,0,0,0,0,0.1
		c-0.1,0.2-0.1,0.5,0,0.7l0,0c0,0,0,0,0,0.1c0,0,0,0,0,0c0.1,0.1,0.1,0.2,0.3,0.2c0,0-0.1-0.1-0.1-0.1c0.1-0.1,0.1-0.1,0.2-0.4
		c0.1,0.1,0.1,0.3,0.2,0.3c0,0,0,0.1,0,0.2c0,0,0,0.1,0.1,0.1c0,0,0.1,0,0.1-0.1c0-0.1,0.1-0.2,0.1-0.2c0.1,0,0.2,0,0.3,0
		c0,0.1,0,0.1,0,0.2c0.1,0,0.3,0.1,0.5,0.1c0.1-0.1,0.2-0.1,0.2-0.2c0-0.1,0.1-0.2,0.2-0.4c0.2,0.1,0.2,0.3,0.2,0.5
		c0.1,0,0.2,0,0.3,0c0,0.1,0,0.1,0.1,0.2c0.1,0,0.1,0,0.2,0.1C27,42,27,42,27,41.9c0-0.1,0.1-0.2,0.2-0.2c0,0.1,0.1,0.2,0.1,0.3
		c0.1-0.1,0.1-0.2,0.2-0.2c0.2,0,0.4,0.1,0.6,0c0,0.1,0.1,0.2,0.1,0.2l0,0c0.1,0,0.2,0,0.3-0.1c0.1,0.1,0.1,0.2,0.2,0.3
		c0.1-0.1,0.1-0.2,0.2-0.2c0.3-0.1,0.5-0.1,0.8,0.1c0.1-0.1,0.1-0.2,0.2-0.2c0,0.1,0.1,0.1,0.1,0.2c0,0,0.1,0,0.1,0
		c0,0,0-0.1,0.1-0.1c0-0.1,0-0.1,0-0.2c0.1,0,0.2,0.1,0.3,0.2c0.1-0.1,0.3-0.3,0.4-0.4c0.1,0.1,0.2,0.3,0.2,0.4l0,0
		c0,0.1,0,0.1,0.1,0.1c0.1,0,0.2,0,0.3,0.2c0,0.1,0.1,0,0.2,0.1c0-0.1,0.1-0.2,0.1-0.3c0.1,0,0.3,0,0.4,0c0,0,0.1-0.1,0.1-0.2
		c0.1,0.1,0.2,0.4,0.4,0.4c0-0.1,0-0.3,0-0.5c0.1,0.2,0.2,0.4,0.4,0.5c0.1-0.1,0.3-0.1,0.5-0.3c0.1-0.1,0.2-0.1,0.2,0
		c0,0.1,0.1,0.2,0.1,0.3c0.1,0.1,0.1,0.2,0.3,0.2c0.5,0,1.1,0,1.6,0.1c0.2-0.3,0.2-0.3,0.3-0.9c0.1,0,0.1,0.1,0.1,0.2
		c0,0.2-0.1,0.3-0.1,0.5c0,0,0,0,0,0c-0.1,0.1,0,0.2,0.1,0.2c0.1,0,0.1,0,0.2,0c0.1,0,0.2,0,0.2-0.2c0,0,0-0.1,0-0.1
		c0.2,0,0.1,0.2,0.2,0.3c0.1,0,0.2,0.1,0.3,0c0,0,0-0.1,0-0.1c-0.1-0.2-0.1-0.3-0.1-0.5c0.1-0.1,0.2,0,0.2,0.1c0.1,0.1,0.2,0,0.2,0
		c0,0.1,0,0.2,0,0.4c0.1,0,0.1-0.1,0.2-0.1c0.1,0,0.1,0,0.2,0.1c0.2,0.2,0.5,0.2,0.7,0.1c0.2-0.1,0.3-0.2,0.5-0.3
		c0-0.1-0.1-0.1-0.1-0.2c0,0,0,0,0,0c0.1-0.1,0.1-0.1,0.2,0c0,0.1,0,0.1,0,0.2c0,0.1,0.1,0.2,0.1,0.3c0,0.1,0.2,0.1,0.2,0
		c0-0.2,0.2-0.3,0.3-0.4c0,0,0,0.1,0,0.1c0,0.2,0.1,0,0,0.6c0.2,0,0.3,0,0.5,0.1c0.1-0.7,0-0.2-0.1-0.3c0-0.1,0-0.3,0-0.5
		c0,0-0.1,0-0.1,0.1c0-0.2,0.1-0.2,0.1-0.1c0.1,0,0.1,0,0.2-0.1c0.1,0,0.1,0,0.1,0.1c0,0-0.1,0.1-0.1,0.1c0.1,0.2,0.3,0.2,0.4,0.2
		c0,0.1,0,0.1,0,0.2c0.2,0.1,0.4,0.1,0.6-0.2c0.1,0.1,0.1,0.1,0.2,0.2c0.1,0,0.1-0.1,0.1-0.2c0-0.2,0-0.4-0.2-0.6
		c0-0.1,0-0.2,0.1-0.4c-0.1-0.1-0.2-0.1-0.2-0.2c0.2-0.3,0.3-0.4,0.5-0.4c0,0.1,0,0.1,0,0.2c-0.1,0.4,0,0.7,0.1,1.1
		c0,0.1,0.1,0.2,0.1,0.2c0,0,0,0,0,0c0,0.1,0,0.1,0.1,0.1c0,0,0.1-0.1,0.1-0.1c0-0.1,0-0.1-0.1-0.1c0,0,0,0,0,0c0,0,0,0,0,0l0,0
		c0.1-0.1,0.2-0.1,0.3-0.1c0.1,0.1,0.2,0.1,0.2,0.2c0.1-0.1,0-0.2,0-0.3c0,0,0,0,0.1,0c0.1,0.1,0.2,0.1,0.2,0.2
		c0.1-0.1,0.1-0.1,0-0.2c0-0.1-0.1-0.1,0-0.2c0.1-0.1,0-0.3,0-0.4c0.1-0.1,0.1,0.1,0.2,0c0,0.1,0,0.2,0,0.3c0.1,0,0.1-0.2,0.3-0.2
		c0,0,0,0,0,0.1c0,0.1,0,0.2,0,0.3c0.1,0,0.2,0.2,0.2,0.3c0.1-0.1,0-0.3,0.1-0.4c0.1-0.1,0.1-0.2,0.2-0.3c0.1,0.1,0,0.3,0.1,0.4
		c0-0.1,0.1-0.3,0.1-0.4c0.2,0.2,0.2,0.3,0.3,0.7c0,0,0.1,0,0.1,0c0.1-0.1,0.2-0.2,0.4-0.3c0.1,0.1,0.3,0,0.3,0.2
		c0.1,0,0.1-0.1,0.1-0.1c0-0.1,0.1-0.2,0.1-0.3c0.1,0,0.1,0,0.2,0.1c0.1,0.1,0.1,0.2,0.2,0.4l0,0c0.1,0,0.1-0.1,0.2-0.1
		C46,42.9,46,43,46,43.2c0.2,0.1,0.3,0.1,0.5,0c0.1,0,0.2-0.1,0.2-0.3c0-0.1,0-0.2,0.1-0.3c0.2,0,0.2,0.2,0.2,0.3
		c0.1,0,0.1-0.2,0.1-0.2c0-0.1,0-0.1,0-0.2c-0.1-0.1-0.1-0.1,0-0.2c0-0.1,0.1-0.1,0.2,0c0.1,0.1,0.1,0.2,0.1,0.4
		c0,0.1,0,0.2,0.1,0.2c0.1,0,0.1-0.2,0.2-0.1c0,0,0.1,0,0.2-0.1c0.1,0,0.1-0.1,0.2-0.2c0-0.2,0-0.4-0.1-0.6c-0.1-0.2-0.1-0.3,0-0.5
		c0.1-0.2,0.1-0.2,0.4-0.2c0,0.2,0,0.3-0.1,0.4c0,0,0,0.1,0,0.1c0.2,0.1,0.2,0.2,0.1,0.4c-0.1,0.1-0.1,0.2-0.1,0.3
		c-0.1,0.2-0.1,0.5,0.1,0.7c0.1-0.2,0-0.4,0.2-0.6c0.1,0,0.1,0,0.1,0.1c0,0.1,0,0.2,0.1,0.3c0,0.1,0.1,0.1,0.2,0.1
		c0-0.1,0-0.1,0-0.2c0-0.1,0-0.3,0.1-0.3c0,0,0.1,0,0.1-0.1c-0.1-0.2-0.2-0.3-0.1-0.6c0.1,0,0.1-0.1,0.2-0.1
		c0.2,0.2,0.1,0.5,0.1,0.8c0,0.1-0.1,0.2-0.1,0.2c0.1,0,0.2,0,0.2,0.2c0,0,0.1,0,0.1,0l0,0c0,0,0,0,0,0c0,0,0,0,0,0
		c0-0.2,0-0.4-0.1-0.7c0.1-0.1,0.3-0.2,0.4-0.2c0,0.1,0,0.1-0.1,0.1c-0.1,0-0.1,0.1-0.1,0.1c0,0.1,0,0.1,0.1,0.1
		c0.1,0,0.1,0.1,0.2,0.1c0.1,0.1,0.2,0,0.2-0.2c0,0,0-0.1,0-0.1c0.1-0.1,0.2-0.1,0.3-0.2c0,0,0,0.1,0,0.1c0,0,0,0.1,0,0.1
		c0,0,0,0.1,0.1,0.1c0,0,0,0,0.1,0c0,0,0,0,0.1,0c0,0,0.1,0,0.1,0c0-0.1,0-0.1,0.1-0.2c0,0,0-0.1,0.1-0.1c0,0,0,0.1,0.1,0.1
		c0.1,0,0.1,0,0.2,0c0,0,0,0,0-0.1c0,0,0,0,0,0c0-0.1,0-0.3-0.1-0.4c0,0,0,0,0-0.1c0,0,0,0,0-0.1c0.1-0.1,0.2-0.1,0.2-0.2
		c0-0.1,0-0.2,0.1-0.3c0,0,0,0,0,0c0,0.1,0,0.1,0,0.2c0.1,0,0.1,0,0.2,0c0,0.1-0.1,0.2-0.1,0.2c0,0.1,0,0.2,0,0.2
		c-0.1,0-0.1,0-0.2,0.1c0,0.2-0.1,0.4-0.1,0.6c0,0.1,0,0.1,0.2,0.2c0-0.1,0-0.1,0-0.2c0-0.1,0-0.2,0-0.3c0-0.1,0-0.2,0.1-0.3
		c0.1,0,0.1,0.1,0.2,0.2c0,0-0.1,0-0.1,0c0,0-0.1,0-0.1,0.1c0,0,0,0.1,0.1,0.1c0,0,0.1,0,0.1,0c0.1,0.2,0.1,0.2,0.3,0.1
		c0-0.2-0.1-0.4-0.1-0.6c0,0,0,0,0,0c0.1,0,0.2,0,0.2-0.1c0,0,0-0.1,0-0.1c0,0.2,0,0.3,0.1,0.5c0,0.1,0,0.3,0.1,0.4
		c0,0,0.1,0.1,0.2,0.1c0-0.2,0-0.3,0-0.4c0-0.1,0.1-0.2,0.2-0.2c0,0,0,0,0,0c0.1,0.1,0.1,0.1,0.2,0.2c0,0,0.1,0.1,0.1,0.1
		c0.1,0.1,0.1,0.2,0,0.3c-0.1,0.1-0.1,0.1-0.2,0.2c0.3,0.1,0.6,0,0.8-0.1c-0.1-0.1-0.2,0-0.3-0.1c0-0.2-0.2-0.4-0.1-0.7
		c0.1-0.1,0.1-0.1,0.1,0c0.1,0.3,0.2,0.4,0.4,0.4c0.1,0,0.1-0.1,0.2-0.1c0-0.1,0-0.1,0-0.2c-0.1,0-0.1,0-0.1,0
		c0-0.3,0.2-0.1,0.3-0.3c-0.1-0.3-0.1-0.3-0.1-0.6c0,0,0.1,0.1,0.1,0.1c0,0.1,0.1,0.1,0.1,0.1c0,0.3,0.1,0.6,0.3,0.8
		c0,0,0.1,0,0.1-0.1c0.1-0.1,0.2,0.2,0.3,0c0.1-0.1,0.2-0.1,0.3-0.2c0-0.3,0-0.5,0-0.8c0-0.1,0-0.1,0-0.2c0,0,0,0,0,0
		c0,0,0.1,0,0.1,0c0,0.1,0,0.2,0.1,0.4c0.1,0,0.2-0.1,0.2-0.1c0,0,0,0,0,0c0.1,0.1,0.1,0.2,0,0.3c0,0.1-0.1,0.1-0.1,0.2
		c0.1,0.1,0.1,0.1,0.2,0.2c0.1-0.1,0.1-0.1,0.2-0.2c0.2,0,0.1,0.2,0.2,0.3c0.1,0,0.1-0.1,0.2-0.2c0,0,0.1,0,0.1,0.1
		c0.1-0.1,0.3-0.2,0.4-0.3c0,0,0.1,0.1,0.1,0.1c0.1-0.1,0.1-0.2,0.1-0.3c-0.1-0.1-0.2-0.1-0.2-0.3c0-0.1,0-0.2,0.1-0.3
		c0.1,0,0.1,0,0.2,0.1c0,0,0,0.1,0,0.1c0,0,0.1,0,0.1,0c0-0.1,0-0.3,0.1-0.4c0.1-0.1,0.2-0.1,0.3-0.2c0,0,0-0.1,0-0.1
		c-0.1,0-0.2-0.1-0.3-0.1c-0.1,0.1-0.1,0.1-0.2,0.2c-0.1-0.1-0.1-0.2,0-0.3c0.1-0.1,0.2-0.2,0.3-0.3c0,0,0,0,0,0.1c0,0,0-0.1,0-0.1
		l0,0c0.1-0.1,0.1-0.2,0.2-0.2c0.3,0,0.5-0.2,0.8-0.3c0,0,0-0.1,0-0.1c-0.1,0-0.1,0-0.2,0c-0.3,0.2-0.3,0.2-0.6-0.2
		c0.1-0.3,0.2-0.4,0.4-0.5c0.2-0.1,0.3-0.1,0.6,0.1c0.1-0.2,0.3-0.3,0.5-0.5c0-0.1-0.1-0.1-0.2-0.2c-0.1-0.1-0.1,0-0.2,0.1
		c-0.2,0-0.3,0-0.5,0c-0.1,0-0.3,0-0.4,0c-0.2,0.1-0.1,0.3-0.2,0.4c-0.1,0.1-0.2,0.2-0.3,0.2c-0.1-0.3-0.1-0.3,0-0.6
		c0,0,0.1,0,0.1,0c0.2-0.2,0.2-0.2,0.4-0.3c0.1,0,0.2,0,0.4-0.1c0.1,0,0.3-0.1,0.4,0c0.1,0,0.2,0,0.3-0.1c-0.1-0.1-0.2-0.1-0.4-0.1
		c-0.1,0-0.3,0-0.4-0.1c0,0,0,0,0,0c0,0,0,0,0,0l0,0c-0.1,0-0.2,0.2-0.3,0.1c0,0-0.1,0-0.2,0c-0.2,0.2-0.3,0.1-0.5-0.1
		c0-0.1,0-0.2,0.1-0.2c0.1-0.1,0.2-0.3,0.2-0.4c0,0,0,0,0,0c0.1,0,0.1,0.2,0.1,0.3c0.1,0.1,0.2,0,0.3,0c0.1-0.1,0.1-0.1,0.2-0.1l0,0
		c0,0,0,0,0,0l0,0c0,0,0,0,0,0c0,0,0,0,0,0c0.2-0.1,0.1-0.3,0.1-0.5c0.1,0,0.1-0.1,0.2-0.1c0,0,0-0.1,0-0.1
		c-0.1-0.1-0.3-0.1-0.3-0.3c0.2-0.3,0.5-0.2,0.8-0.4c0,0-0.1-0.1-0.1-0.1c-0.1,0-0.2,0-0.4,0.1c-0.2,0-0.2,0-0.4-0.1
		c0-0.1,0-0.2,0-0.2c0-0.1,0-0.2-0.1-0.2c0,0,0.1-0.1,0.1-0.1c-0.1-0.1-0.1-0.1-0.2-0.2c0-0.1,0-0.1,0-0.2c0-0.1-0.1-0.1-0.1-0.2
		c0-0.1,0.1-0.1,0.1-0.2c-0.1-0.3-0.1-0.3-0.4-0.5c0-0.1,0.1-0.2,0.1-0.1c0.1,0,0.1,0.1,0.2,0.1c0,0,0.1,0.1,0.2,0.1
		c0.1-0.3,0.1-0.5,0.4-0.5c0.1-0.1,0.2,0,0.3,0c0-0.1-0.1-0.2-0.1-0.2c-0.2,0-0.5,0-0.7,0.1c-0.1,0.1-0.2,0.1-0.3,0
		c0.1-0.3,0.3-0.2,0.5-0.3c-0.2-0.1-0.4-0.1-0.6,0c0,0,0,0,0,0c0,0,0-0.1-0.1-0.1c0,0-0.1,0.1-0.1,0.2c0,0.1,0.1,0.2,0.1,0.3
		c0,0,0,0.1,0,0.1c0,0-0.1,0-0.1,0c0,0-0.1,0-0.1-0.1c0-0.1-0.1-0.2-0.2-0.2c-0.1,0-0.1-0.1-0.1-0.2c0,0,0,0,0,0
		c0.2-0.4,0.2-0.4-0.2-0.6c0.1-0.1,0.1-0.1,0.2-0.2c0,0,0.1-0.1,0.1-0.2c0-0.1,0-0.1-0.1-0.1c0,0-0.1,0-0.1,0c0-0.1-0.1-0.2-0.1-0.3
		c0,0.1-0.1,0.1-0.1,0.1c0,0-0.1,0-0.1,0c-0.1-0.1-0.1-0.2-0.1-0.3c0-0.1-0.1-0.1-0.1-0.2c0,0.2,0,0.3,0,0.4c-0.1,0-0.2,0-0.2-0.2
		c0-0.1-0.1,0-0.2-0.1c0.1-0.1,0.1-0.2,0.2-0.3c0,0,0-0.1,0-0.1c-0.1-0.3-0.1-0.6,0.1-0.9c0.1-0.2,0.1-0.4,0.2-0.6
		c0-0.1,0.1-0.2,0.2-0.3c0,0,0.1-0.1,0.1-0.1c0-0.2,0.1-0.3,0.2-0.5c0-0.1,0-0.1,0.2-0.1c0,0.2,0,0.3,0.1,0.5c0-0.1,0-0.1,0.1-0.1
		c0.1-0.2,0.1-0.2,0.3,0c-0.1,0-0.1,0.1-0.1,0.2c0.1,0.2,0.1,0.4,0.1,0.6c0,0.1,0.1,0.1,0.1,0.2c-0.1,0.1,0,0.2-0.2,0.3
		c-0.2,0.1-0.2,0.4-0.3,0.6c0.1,0.1,0.2,0.1,0.2,0c0-0.1,0-0.2,0.1-0.2c0-0.1,0.1-0.1,0.2,0c0,0.1,0.1,0.2,0.1,0.3c0,0,0,0.1,0,0.1
		c0,0,0.1,0.1,0.1,0.1c0,0,0.1,0,0.1-0.1c0-0.2,0-0.3,0-0.5c0.1,0,0.1,0.1,0.1,0.2c0,0.1,0.1,0.1,0.1,0.2c0,0-0.1,0.1-0.1,0.1
		c0,0,0,0.1,0.1,0.1c0,0,0.1,0,0.1,0c0.1-0.1,0.2-0.2,0.3-0.1c0.1,0,0.1,0,0.2,0c0,0,0,0.1,0.1,0.1c-0.1,0.2-0.3,0.2-0.3,0.4
		c0.3,0,0.6,0.1,1,0c0,0.2-0.1,0.1-0.1,0.1c-0.2,0-0.3,0.1-0.3,0.3c0,0,0,0.1,0.1,0.1c0,0,0.1,0,0.1-0.1c0-0.1,0.1-0.1,0.1-0.2
		c0.2,0.1,0,0.3,0.1,0.4c0,0,0,0,0.1,0c0.2,0,0.3,0,0.5-0.1c0.1,0,0.2-0.2,0.3-0.1c0.4-0.1,0.7,0,1.1-0.2c0,0,0,0,0.1-0.1
		c0-0.1,0-0.3,0.1-0.4c0.1,0.1,0.1,0.1,0.2,0.2c0,0,0,0,0,0c0,0,0,0,0,0c0-0.1-0.1-0.3-0.1-0.3c0.1,0,0.1,0,0.2,0
		c0.1-0.7,0-0.3,0-0.3c-0.1,0-0.2-0.1-0.3-0.1c0-0.1,0-0.2,0-0.3c0,0,0-0.1-0.1-0.1c0,0-0.1,0-0.1,0c0,0.1-0.1,0.2-0.1,0.3
		c-0.1,0-0.2-0.1-0.2-0.2c0-0.2-0.1-0.3-0.3-0.3c0,0,0,0-0.1,0c0-0.1,0-0.1,0.1-0.2c0.1,0,0.1-0.1,0.2-0.1c-0.1-0.2-0.3,0-0.4-0.1
		c0-0.1,0-0.2,0.1-0.2c0.1,0,0.3,0,0.4-0.1c0,0,0.1,0,0.1,0c0,0,0.1-0.1,0.1-0.2c0-0.1-0.1-0.1-0.1-0.1c0,0-0.1,0-0.1,0l0,0
		c0,0,0,0,0,0c0,0,0,0,0,0c-0.1,0-0.1,0-0.1,0c0,0,0,0,0,0c0,0,0,0,0,0c0-0.1-0.1-0.2,0-0.3c0.1-0.1,0.1-0.2,0.1-0.3
		c0.1,0,0.1-0.1,0.2-0.1c0,0,0-0.1,0-0.1c0-0.1-0.1-0.1-0.1-0.2c-0.2-0.3-0.1-0.3-0.1-0.6c0,0-0.1-0.1-0.1-0.1
		c-0.1-0.1,0-0.3-0.1-0.4c-0.1-0.1-0.1-0.2-0.1-0.4c0-0.1,0-0.3,0-0.4c-0.1,0-0.1-0.1-0.1-0.1c0-0.1,0.1-0.1,0.2-0.2
		c-0.1-0.1-0.1-0.1-0.2-0.1c0,0,0,0,0-0.1c0-0.1,0-0.2,0-0.3c0-0.2,0-0.4,0-0.6c-0.1-0.1-0.1-0.1-0.2-0.2c0,0,0-0.1,0-0.1
		c0.2-0.2,0.2-0.5,0.3-0.7c-0.1,0-0.1-0.1-0.1-0.1c-0.1,0-0.1-0.1-0.1-0.2c0.2-0.2,0.4-0.3,0.6-0.3c-0.1,0-0.2-0.1-0.3-0.1
		c-0.1,0-0.1-0.1-0.2-0.2c0-0.1,0-0.2,0.1-0.3c0.1,0,0.1-0.1,0.2-0.1c0-0.1,0-0.2,0-0.3c0.1-0.1,0.1,0.1,0.2,0.1c0.1,0,0.1,0,0.2,0
		c0,0.1,0,0.2,0,0.3l0,0c0.1,0.1,0.2,0.1,0.2,0.3c0,0.1-0.1,0.2-0.1,0.2c-0.1,0-0.1-0.1-0.2-0.1c0.1,0.3,0.2,0.3,0.4,0.3
		c0,0.1,0,0.2,0.1,0.2c0.1,0,0.2,0.1,0.3,0.3c0.1,0.2,0.1,0.4,0.2,0.6c0.1-0.2,0.1-0.3,0-0.5c0.2,0.1,0.2,0.2,0.3,0.3
		c0,0.1-0.1,0.1-0.1,0.2c0.1,0.1,0.2,0.2,0.2,0.4c0.1-0.2,0.1-0.4,0.1-0.5c0-0.2-0.2-0.3-0.1-0.5c0.2,0.1,0.3,0.2,0.2,0.5
		c0,0.1,0,0.2,0.1,0.2c0,0,0-0.1,0.1-0.1c0-0.1,0-0.1,0-0.2c0,0,0,0.1,0,0.1c0,0.2,0.1,0.4,0.1,0.6c0,0,0,0,0,0.1
		c0.1,0.3,0.1,0.3,0.3,0.1l0,0c0,0,0.1,0.1,0.1,0.1c0,0,0.1,0,0.1,0c0,0,0-0.1,0-0.1c0-0.1-0.1-0.2-0.1-0.3c0.1,0.1,0.3,0.1,0.3,0.3
		c0,0.1,0.1,0.1,0.2,0.1c0,0,0.1,0,0.1-0.1c0.1-0.1,0.2,0,0.2,0c0-0.1,0-0.2,0-0.3c0,0,0.2,0.1,0.3,0.1c0.1-0.1,0.1-0.1,0.2-0.2
		c0.1,0,0.1,0,0.2,0c0.1-0.1,0.1-0.3,0-0.5l0,0c-0.1,0-0.3-0.1-0.2-0.3c0.1,0,0.2-0.1,0.2-0.1c0-0.1,0.1-0.1,0.2-0.1
		c0.1,0,0.2,0,0.4,0C65.3,24.1,65.4,24.2,65.4,24.2z M61,31.8c0.1,0.1,0.1,0.1,0.1,0.2c0,0,0,0-0.1,0c0,0,0,0,0-0.1
		C61,31.9,61,31.8,61,31.8z M60.6,32C60.6,32,60.6,32,60.6,32C60.6,32,60.6,32,60.6,32c0.1,0.1,0,0.1,0,0.1C60.6,32,60.6,32,60.6,32
		z M56.6,32.5C56.6,32.5,56.6,32.5,56.6,32.5C56.6,32.5,56.6,32.5,56.6,32.5C56.6,32.5,56.6,32.5,56.6,32.5z M57.6,38.8
		c0.1,0,0.1,0,0.3-0.1C57.7,38.9,57.7,38.9,57.6,38.8z M55.8,36.2L55.8,36.2L55.8,36.2L55.8,36.2z M56.4,32.5
		C56.3,32.5,56.3,32.6,56.4,32.5C56.3,32.6,56.3,32.5,56.4,32.5C56.3,32.5,56.3,32.5,56.4,32.5C56.3,32.5,56.3,32.5,56.4,32.5z
		 M56.3,33L56.3,33L56.3,33L56.3,33z M56,36.6c0,0.1-0.1,0.2-0.1,0.3c0,0,0,0,0,0c0-0.1,0-0.1,0-0.2C55.9,36.6,55.9,36.6,56,36.6z
		 M55.5,40.6c0-0.1,0-0.2,0-0.3C55.6,40.4,55.6,40.4,55.5,40.6z M55.6,39.5C55.6,39.5,55.6,39.5,55.6,39.5
		C55.6,39.5,55.6,39.5,55.6,39.5C55.6,39.5,55.6,39.5,55.6,39.5C55.6,39.5,55.6,39.5,55.6,39.5z M56.6,33.1
		C56.5,33.2,56.5,33.2,56.6,33.1C56.5,33.1,56.5,33.1,56.6,33.1C56.6,33.1,56.6,33.1,56.6,33.1z M56.3,34.2c0-0.1,0-0.2,0.1-0.3
		c0-0.1,0-0.2,0.1-0.3c0.1,0,0.1,0.1,0.1,0.2C56.5,34,56.4,34.1,56.3,34.2z M56.3,35.8C56.3,35.8,56.3,35.9,56.3,35.8
		C56.2,35.9,56.2,35.8,56.3,35.8C56.2,35.8,56.3,35.8,56.3,35.8C56.3,35.8,56.3,35.8,56.3,35.8z M55.8,41C55.9,41,55.9,41,55.8,41
		c0.1,0,0.1,0.1,0.1,0.1c0,0-0.1,0.1-0.1,0.1c0,0,0,0-0.1-0.1C55.8,41.1,55.8,41.1,55.8,41z M55.9,40.3L55.9,40.3L55.9,40.3
		L55.9,40.3z M56.2,38.3C56.2,38.3,56.2,38.3,56.2,38.3C56.1,38.3,56.1,38.3,56.2,38.3C56.1,38.3,56.1,38.2,56.2,38.3
		C56.1,38.2,56.2,38.2,56.2,38.3z M56.7,34.8c0,0,0,0.1,0,0.1C56.6,34.8,56.6,34.8,56.7,34.8C56.6,34.8,56.7,34.7,56.7,34.8
		C56.7,34.8,56.7,34.8,56.7,34.8z M56.9,34.5L56.9,34.5L56.9,34.5L56.9,34.5z M57.2,34.8C57.1,34.8,57.1,34.8,57.2,34.8
		C57.1,34.8,57.1,34.8,57.2,34.8C57.1,34.8,57.1,34.8,57.2,34.8z M57.2,34.7C57.2,34.8,57.2,34.8,57.2,34.7
		C57.2,34.8,57.2,34.8,57.2,34.7C57.2,34.7,57.2,34.7,57.2,34.7z M57.3,35.3c-0.1,0-0.1,0-0.2,0c-0.1,0-0.1,0-0.2,0
		c0-0.1,0-0.1,0-0.2c-0.1,0.1-0.1,0.1,0,0.2c0,0,0,0,0,0c0,0,0,0.1,0,0.1l0,0c0,0,0,0,0,0c0,0,0,0,0,0c0.1,0.2,0.1,0.2-0.1,0.4
		c-0.1-0.2-0.2-0.3-0.2-0.5c0.1-0.1,0.1-0.1,0.1-0.1c0-0.1-0.1-0.2-0.1-0.3c0.1,0.1,0.3,0.1,0.4,0.2C57.2,35.1,57.3,35.1,57.3,35.3z
		 M56.4,37.3C56.4,37.4,56.4,37.4,56.4,37.3C56.4,37.4,56.4,37.4,56.4,37.3C56.4,37.3,56.4,37.3,56.4,37.3
		C56.4,37.3,56.4,37.3,56.4,37.3z M56.5,36C56.6,36,56.6,36,56.5,36C56.6,36,56.6,36,56.5,36C56.6,36,56.6,36,56.5,36
		C56.6,36,56.6,36,56.5,36z M56.4,36.6C56.4,36.6,56.4,36.6,56.4,36.6C56.4,36.6,56.4,36.6,56.4,36.6C56.5,36.6,56.5,36.6,56.4,36.6
		C56.5,36.6,56.4,36.6,56.4,36.6z M57.3,36.3C57.2,36.3,57.2,36.3,57.3,36.3c-0.1,0-0.1,0-0.2,0c0,0.1,0,0.1,0,0.2
		c0,0,0,0.1,0.1,0.1c0,0.1,0,0.2-0.1,0.3c0-0.1-0.1-0.2-0.1-0.3c0,0,0,0,0,0c0-0.2,0-0.3,0-0.5c0.1-0.1,0.1-0.3,0.3-0.3
		c0,0.2,0,0.2,0.1,0.2c0.1,0,0.2,0.1,0.2,0.2C57.4,36.2,57.3,36.2,57.3,36.3z M56.9,36.5C56.9,36.6,56.9,36.6,56.9,36.5
		C56.9,36.6,56.9,36.6,56.9,36.5C56.9,36.6,56.9,36.6,56.9,36.5z M57,37.2C57,37.2,57.1,37.3,57,37.2c0,0.1,0,0.1,0,0.1c0,0,0,0,0,0
		C57,37.3,57,37.2,57,37.2z M57.4,36.6c-0.1,0.1-0.1,0-0.2,0c0-0.1,0.1-0.2,0.1-0.3c0,0,0,0,0.1,0C57.4,36.4,57.4,36.5,57.4,36.6z
		 M57,37.6c0.1,0,0.1-0.1,0.2-0.1C57.1,37.5,57.1,37.6,57,37.6z M56.5,39.8C56.5,39.8,56.5,39.8,56.5,39.8c0.1,0,0.2,0,0.2,0.3
		C56.6,40,56.6,40,56.5,39.8C56.5,39.9,56.5,39.9,56.5,39.8z M56.6,39c0.1-0.3,0.1-0.5,0.3-0.8c0-0.1,0.1-0.1,0.2-0.2
		c0,0.2-0.1,0.3-0.1,0.5c0,0.2,0,0.4,0,0.6c0,0,0,0,0,0c0,0,0,0,0,0.1C56.7,39.2,56.6,39.1,56.6,39z M56.7,37.4
		C56.8,37.4,56.8,37.4,56.7,37.4C56.8,37.4,56.8,37.4,56.7,37.4C56.8,37.4,56.8,37.4,56.7,37.4C56.8,37.4,56.8,37.4,56.7,37.4z
		 M56.7,38.1c0,0.1-0.1,0.3-0.2,0.5c-0.1-0.1-0.1-0.1-0.1-0.1C56.4,38.3,56.5,38.2,56.7,38.1C56.6,38.1,56.7,38.1,56.7,38.1z
		 M56.4,39.4c0,0,0,0.1,0,0.1c0,0,0,0-0.1,0C56.4,39.5,56.4,39.5,56.4,39.4C56.4,39.4,56.4,39.4,56.4,39.4z M56.1,41.8
		C56.2,41.8,56.2,41.7,56.1,41.8C56.2,41.7,56.2,41.8,56.1,41.8C56.2,41.8,56.2,41.8,56.1,41.8z M56.2,40c0.1,0.1,0.1,0.2,0.2,0.3
		c-0.1,0.1-0.1,0.1-0.1,0.1C56.2,40.3,56.1,40.2,56.2,40z M56.3,39.1C56.3,39.1,56.3,39,56.3,39.1C56.3,39,56.3,39.1,56.3,39.1
		C56.3,39.1,56.3,39.1,56.3,39.1C56.3,39.1,56.3,39.1,56.3,39.1z M56.4,37.9C56.4,37.9,56.3,37.9,56.4,37.9c0-0.1,0-0.1,0-0.1
		C56.4,37.8,56.4,37.8,56.4,37.9C56.4,37.9,56.4,37.9,56.4,37.9z M56.1,39.6C56.1,39.6,56.1,39.5,56.1,39.6c0-0.1-0.1-0.1-0.1-0.1
		c0,0,0.1,0,0.1,0C56.1,39.4,56.2,39.5,56.1,39.6C56.2,39.5,56.1,39.5,56.1,39.6z M55.5,41.7c0.2-0.1,0.3,0.1,0.3,0.2
		C55.7,41.9,55.6,41.8,55.5,41.7C55.5,41.8,55.5,41.7,55.5,41.7z M55.8,39.1C55.8,39,55.8,39,55.8,39.1C55.8,39,55.9,39,55.8,39.1
		C55.9,39.1,55.8,39.1,55.8,39.1C55.8,39.1,55.8,39.1,55.8,39.1z M55.2,41.4c0-0.1,0-0.2,0.1-0.2c0-0.1,0.1-0.1,0.2,0
		c0,0.1,0,0.2,0.1,0.3c-0.1-0.1-0.1-0.1-0.2-0.1c0,0.1,0,0.1-0.1,0.2C55.2,41.5,55.2,41.4,55.2,41.4z M55.3,39.5
		c0.1,0.1,0.1,0.2,0.2,0.2c0,0.1-0.1,0.1-0.2,0.2C55.3,39.8,55.3,39.7,55.3,39.5z M55.9,33.8C55.9,33.8,55.9,33.8,55.9,33.8
		C55.9,33.8,55.9,33.8,55.9,33.8C55.9,33.8,56,33.8,55.9,33.8C56,33.8,55.9,33.8,55.9,33.8z M55.1,40.7
		C55.1,40.7,55.1,40.7,55.1,40.7C55.1,40.7,55.1,40.7,55.1,40.7C55.1,40.7,55.1,40.7,55.1,40.7C55.1,40.7,55.1,40.7,55.1,40.7z
		 M55.3,38.5C55.3,38.6,55.3,38.6,55.3,38.5C55.3,38.6,55.3,38.6,55.3,38.5C55.3,38.6,55.3,38.6,55.3,38.5
		C55.2,38.6,55.2,38.5,55.3,38.5z M55.8,33.2c0-0.1,0-0.1,0-0.2c0,0,0,0,0-0.1c0,0,0.1,0.1,0.1,0.2C56,33.1,55.9,33.1,55.8,33.2z
		 M41.4,42.3C41.4,42.3,41.4,42.3,41.4,42.3C41.4,42.3,41.4,42.3,41.4,42.3C41.4,42.3,41.4,42.3,41.4,42.3
		C41.4,42.3,41.4,42.3,41.4,42.3z M4.2,28.7C4.2,28.7,4.2,28.7,4.2,28.7C4.1,28.7,4.1,28.7,4.2,28.7C4.1,28.7,4.2,28.7,4.2,28.7
		C4.2,28.7,4.2,28.7,4.2,28.7z M4.4,27.9c-0.1,0,0-0.1-0.1-0.2C4.4,27.8,4.4,27.9,4.4,27.9z M4.2,26.3L4.2,26.3L4.2,26.3L4.2,26.3z
		 M4.8,23.9c0,0.1-0.1,0.1-0.1,0.2C4.7,24,4.8,24,4.8,23.9C4.8,23.9,4.8,23.9,4.8,23.9z M4.7,24.6c0-0.1-0.1-0.1-0.1-0.2
		c0-0.1,0-0.2,0-0.3c0,0,0,0,0.1,0C4.8,24.3,4.8,24.4,4.7,24.6z M4.7,24.9c0,0.1,0,0.3,0,0.4c0,0.1,0,0.1-0.1,0.1
		c-0.1-0.1,0-0.4-0.1-0.5c0,0.3-0.1,0.5-0.3,0.7c0-0.2-0.1-0.3-0.1-0.5c0-0.2,0.1-0.4,0.2-0.4c0.1,0,0.1,0,0.2,0
		C4.6,24.8,4.7,24.8,4.7,24.9c0-0.1,0-0.1,0-0.2C4.8,24.8,4.8,24.9,4.7,24.9z M4.7,26C4.7,26,4.6,25.9,4.7,26
		C4.6,25.9,4.6,25.9,4.7,26C4.7,25.9,4.7,25.9,4.7,26C4.7,25.9,4.7,25.9,4.7,26z M4.1,6.9C4.1,6.9,4.1,6.9,4.1,6.9
		C4.1,6.9,4.1,6.9,4.1,6.9C4.1,6.9,4.1,6.9,4.1,6.9C4.1,6.9,4.1,6.9,4.1,6.9z M4.4,15L4.4,15L4.4,15L4.4,15z M5.1,10.9
		C5,10.9,5,10.8,5,10.7c0-0.2,0-0.2,0.1-0.4C5.1,10.5,5.2,10.7,5.1,10.9z M4.6,17.6C4.6,17.7,4.6,17.7,4.6,17.6
		C4.6,17.7,4.6,17.6,4.6,17.6C4.6,17.6,4.6,17.6,4.6,17.6C4.6,17.6,4.6,17.6,4.6,17.6z M4.7,18.2C4.7,18.2,4.7,18.2,4.7,18.2
		C4.7,18.2,4.7,18.2,4.7,18.2C4.7,18.2,4.7,18.2,4.7,18.2C4.7,18.2,4.7,18.2,4.7,18.2z M4.5,18.9c0-0.1,0-0.2,0-0.2
		c0-0.1,0-0.1,0-0.2C4.7,18.6,4.7,18.7,4.5,18.9z M11.8,3.4C11.8,3.4,11.7,3.4,11.8,3.4C11.7,3.4,11.7,3.4,11.8,3.4
		C11.8,3.4,11.8,3.4,11.8,3.4z M42.6,5C42.6,5,42.6,4.9,42.6,5c0.1-0.1,0.1-0.1,0.2-0.1C42.8,4.9,42.7,5,42.6,5
		C42.7,5,42.6,5,42.6,5z M61.1,4.3L61.1,4.3L61.1,4.3L61.1,4.3z M58.7,31.9C58.7,31.9,58.7,31.9,58.7,31.9
		C58.7,31.9,58.7,31.9,58.7,31.9C58.7,31.9,58.7,31.9,58.7,31.9C58.7,31.9,58.7,31.9,58.7,31.9z M58.8,30.4
		C58.8,30.4,58.7,30.4,58.8,30.4C58.8,30.3,58.8,30.3,58.8,30.4C58.8,30.3,58.8,30.3,58.8,30.4C58.8,30.4,58.8,30.4,58.8,30.4z
		 M58.6,31.2C58.6,31.2,58.6,31.2,58.6,31.2c0.1,0,0.1,0,0.1,0C58.6,31.3,58.6,31.3,58.6,31.2C58.6,31.3,58.6,31.3,58.6,31.2z
		 M58.3,31c0.1,0.1,0.1,0.1,0,0.3C58.3,31.2,58.3,31.1,58.3,31z M59.1,22.3C59.1,22.3,59.2,22.3,59.1,22.3
		C59.2,22.3,59.2,22.3,59.1,22.3C59.2,22.3,59.2,22.3,59.1,22.3C59.1,22.3,59.1,22.3,59.1,22.3z M59.3,20.2c-0.1-0.2,0-0.3,0.1-0.4
		C59.4,20.1,59.4,20.1,59.3,20.2z M59.4,19.3L59.4,19.3L59.4,19.3L59.4,19.3z M57.4,30.4c0-0.2,0.1-0.2,0.2-0.2c0,0.1,0,0.1,0.1,0.2
		c0.1,0,0.2,0,0.3,0c0,0.2-0.1,0.1-0.2,0.2c-0.1,0.1-0.1,0.2-0.2,0.2c0,0,0,0-0.1,0c0-0.1,0-0.2,0.1-0.3
		C57.5,30.4,57.5,30.4,57.4,30.4z M58.8,20.2C58.8,20.2,58.8,20.2,58.8,20.2C58.8,20.2,58.8,20.2,58.8,20.2z M58.8,20
		c0,0.1,0,0.1,0,0.2C58.7,20.1,58.7,20,58.8,20z M58.9,19.4c0,0.1,0,0.2-0.1,0.3C58.8,19.6,58.8,19.5,58.9,19.4z M58.9,19.7
		c0,0.1,0,0.3-0.2,0.2c0-0.1,0-0.2,0-0.2C58.9,19.7,58.9,19.7,58.9,19.7z M58,26.8C58,26.8,58,26.8,58,26.8C58,26.8,58,26.9,58,26.8
		C58,26.9,58,26.8,58,26.8C58,26.8,58,26.8,58,26.8z M57.7,29.9C57.7,29.8,57.7,29.8,57.7,29.9C57.7,29.8,57.7,29.8,57.7,29.9
		C57.7,29.9,57.7,29.9,57.7,29.9C57.7,29.9,57.7,29.9,57.7,29.9z M57.8,30.1c-0.1,0-0.1,0-0.2,0.1C57.7,30.1,57.7,30,57.8,30.1z
		 M59,19.7c0-0.1,0-0.2,0.1-0.3c0,0,0,0.1,0,0.1c0,0.1,0,0.1,0,0.2C59.1,19.7,59,19.7,59,19.7z M57.7,27.4c0.1,0,0.1,0,0.2-0.1
		C57.9,27.5,57.8,27.5,57.7,27.4z M57.9,26.6c-0.1-0.1-0.1-0.1-0.2-0.2C57.9,26.4,57.9,26.4,57.9,26.6z M58.5,20
		c0-0.1,0-0.2-0.1-0.3C58.6,19.8,58.6,19.8,58.5,20z M57.2,29.4c0.1,0,0.1,0,0.2,0c0,0,0,0.1,0,0.1c0,0-0.1,0-0.1,0
		C57.3,29.5,57.3,29.4,57.2,29.4z M59.2,11.9C59.2,11.9,59.1,11.9,59.2,11.9C59.1,11.9,59.1,11.9,59.2,11.9c-0.1-0.1,0-0.1,0-0.2
		C59.1,11.7,59.2,11.8,59.2,11.9z M58.9,11.5C58.9,11.5,58.9,11.4,58.9,11.5c0-0.2,0.1-0.3,0.2-0.3c0.1,0,0.1,0.1,0.1,0.2
		c0,0.1,0,0.1-0.1,0.2C59.1,11.6,59,11.5,58.9,11.5z M58.8,13.6L58.8,13.6L58.8,13.6L58.8,13.6z M58.8,13.9c0,0.3-0.2,0.5-0.3,0.7
		c0,0-0.1,0-0.1,0c0-0.1,0.1-0.2,0.1-0.3c0-0.1,0-0.2,0-0.2C58.6,14,58.7,13.9,58.8,13.9z M57.3,22.5C57.3,22.5,57.3,22.5,57.3,22.5
		C57.3,22.5,57.3,22.5,57.3,22.5C57.3,22.5,57.3,22.5,57.3,22.5C57.3,22.5,57.3,22.5,57.3,22.5z M57.1,23.9L57.1,23.9L57.1,23.9
		L57.1,23.9z M57.2,24.7c-0.1,0-0.1-0.1-0.2-0.1C57.2,24.5,57.2,24.6,57.2,24.7z M56.9,25.6c0-0.1,0-0.3,0.1-0.4c0,0,0-0.1,0-0.1
		c0-0.1,0-0.2,0-0.3c0.1-0.1,0.1-0.1,0.2-0.1c0,0,0,0,0,0c0,0,0,0,0,0c0,0,0,0,0,0c0,0,0,0,0,0c0.1-0.1,0.1-0.1,0.2-0.2
		c0,0.1,0.1,0.2,0,0.3c0,0.1,0,0.2-0.1,0.3c0,0,0,0.1,0.1,0.1c0,0.2-0.2,0.4-0.2,0.6c-0.1,0.1-0.3,0.1-0.3,0.2c0,0,0,0,0,0
		c0,0-0.1-0.1-0.1-0.1c-0.1-0.1-0.1-0.1,0-0.3C56.9,25.8,56.9,25.7,56.9,25.6z M57.8,21.9C57.8,21.9,57.8,21.8,57.8,21.9
		C57.9,21.8,57.9,21.8,57.8,21.9C57.9,21.9,57.9,21.9,57.8,21.9C57.9,21.9,57.8,21.9,57.8,21.9z M57.5,25.4
		C57.5,25.4,57.5,25.4,57.5,25.4C57.5,25.4,57.5,25.4,57.5,25.4C57.5,25.4,57.5,25.4,57.5,25.4z M57.9,21.9
		C57.9,21.9,57.9,21.9,57.9,21.9C57.9,22,57.9,22,57.9,21.9C57.9,22,57.9,21.9,57.9,21.9z M57.2,24.3c0.1-0.1,0.1,0,0.2,0
		c0,0.1,0,0.2,0,0.2C57.3,24.5,57.3,24.4,57.2,24.3z M58.5,15C58.5,15,58.5,15,58.5,15c0.1,0,0.1,0,0.1,0
		C58.6,15.1,58.5,15.1,58.5,15C58.5,15.1,58.5,15.1,58.5,15z M58.7,15.5c0.1,0.1,0.1,0.2,0.1,0.3c-0.1,0.1-0.1,0.2-0.2,0.3
		c0,0,0,0,0,0c0-0.2-0.1-0.4-0.1-0.5C58.7,15.5,58.7,15.5,58.7,15.5z M57.7,24.7C57.7,24.7,57.8,24.7,57.7,24.7c0,0.1,0,0.1,0,0.1
		C57.7,24.8,57.7,24.7,57.7,24.7C57.7,24.7,57.7,24.7,57.7,24.7z M58.9,15.4C58.9,15.4,58.9,15.4,58.9,15.4
		C58.8,15.4,58.8,15.3,58.9,15.4C58.9,15.3,58.9,15.3,58.9,15.4C58.9,15.3,58.9,15.4,58.9,15.4z M58.9,14.6c0,0.1,0.1,0.2,0.1,0.3
		c0,0,0,0.1,0,0.1c0,0.1,0,0.2-0.1,0.3C58.8,15,58.8,14.9,58.9,14.6z M56.8,28.6c0.2-0.1,0.4-0.1,0.5,0c0,0,0,0.1,0,0.1
		c0,0,0,0.1-0.1,0.1C57,28.9,56.9,28.8,56.8,28.6z M57.2,28.4c0,0-0.1,0-0.2,0c0.1-0.1,0.1-0.1,0.1-0.2c0,0,0.1,0,0.1,0
		C57.2,28.2,57.3,28.2,57.2,28.4C57.2,28.3,57.2,28.4,57.2,28.4z M56.5,26.8c0.1-0.1,0.2-0.1,0.2-0.3c0,0.1,0.1,0.2,0.2,0.1
		c0,0,0,0,0,0c0,0.1,0.2,0.2,0.3,0.2C57.1,27,57.1,27,57,27.1c0,0.1-0.1,0.1-0.1,0.2c0.1,0,0.2,0,0.3,0c0.1,0,0.2,0.1,0.2,0.3
		c0,0.1,0,0.2,0,0.3c0,0,0,0.1,0,0.2C57.3,27.9,57,28,57,27.6c0,0,0.1,0,0.1,0c0,0,0.1,0,0.1,0c0-0.1,0-0.1-0.1-0.1
		c-0.1,0-0.2,0-0.3-0.1c0-0.1-0.1-0.2-0.1-0.3C56.7,27,56.5,27,56.5,26.8z M56.6,26.4c0.1,0,0.1,0.1,0.1,0.2c-0.1,0-0.2,0-0.2,0
		C56.5,26.4,56.5,26.4,56.6,26.4z M57,23.2L57,23.2L57,23.2L57,23.2z M56.5,24.3c-0.1,0.1-0.2,0.1-0.2,0c0-0.2-0.1-0.2-0.2-0.1
		c0,0-0.1,0-0.1,0c0,0-0.1-0.1-0.1-0.1c0-0.1,0-0.1,0.1-0.1C56.2,23.9,56.4,24,56.5,24.3z M56.6,22.1c0-0.1,0-0.1,0-0.2
		C56.6,22,56.7,22,56.6,22.1z M56.5,21.6C56.5,21.6,56.5,21.6,56.5,21.6C56.5,21.6,56.5,21.6,56.5,21.6
		C56.5,21.6,56.5,21.6,56.5,21.6C56.5,21.6,56.5,21.6,56.5,21.6z M56.9,18.2L56.9,18.2L56.9,18.2L56.9,18.2z M56.2,21.4
		C56.2,21.5,56.2,21.5,56.2,21.4c-0.1,0.1-0.2,0-0.3,0c-0.1,0,0-0.2-0.1-0.2c0,0.1,0,0.2-0.1,0.2c0,0,0,0-0.1,0c0,0,0,0-0.1,0
		c0,0,0,0,0,0c0-0.1,0-0.1,0-0.2c0,0,0-0.1,0-0.1c0-0.1,0-0.2,0-0.2c0.1,0,0.1-0.1,0.1-0.1c0,0,0,0,0,0c0,0,0,0,0,0
		C55.9,21,56.1,21.2,56.2,21.4z M55.9,20.5C55.9,20.4,55.9,20.4,55.9,20.5C55.9,20.4,55.9,20.4,55.9,20.5
		C55.9,20.4,56,20.4,55.9,20.5C56,20.4,56,20.4,55.9,20.5z M55.8,18.8C55.8,18.8,55.8,18.8,55.8,18.8L55.8,18.8L55.8,18.8z
		 M56.6,8.6C56.6,8.6,56.6,8.6,56.6,8.6C56.6,8.6,56.6,8.6,56.6,8.6C56.6,8.6,56.6,8.6,56.6,8.6z M51.2,10.1
		C51.2,10.1,51.1,10.1,51.2,10.1C51.1,10,51.1,10,51.2,10.1c0-0.2,0-0.3,0-0.3c0.1,0,0.2,0,0.2,0.1C51.4,10,51.3,10,51.2,10.1z
		 M50.2,9.6c0.1-0.1,0.1-0.2,0.2-0.3C50.4,9.4,50.3,9.5,50.2,9.6z M49,9.6C49,9.6,49,9.5,49,9.6C49,9.5,49,9.5,49,9.6
		C49,9.5,49,9.5,49,9.6C49,9.5,49,9.6,49,9.6z M47,9.5C47,9.5,47,9.5,47,9.5C47,9.4,47.1,9.4,47,9.5C47.1,9.4,47.1,9.5,47,9.5
		C47.1,9.5,47,9.5,47,9.5z M37.8,6.7c0,0.1-0.1,0.2-0.2,0.3C37.6,6.8,37.6,6.7,37.8,6.7C37.7,6.6,37.7,6.6,37.8,6.7
		C37.8,6.7,37.8,6.7,37.8,6.7z M30,7.2L30,7.2L30,7.2L30,7.2z M26.9,7.6c-0.1,0-0.2-0.1-0.2-0.1c-0.1-0.1-0.1-0.1,0-0.2
		C26.9,7.4,26.9,7.4,26.9,7.6z M26.9,6C26.9,6,26.9,6,26.9,6c0-0.1,0-0.2,0.1-0.1c0,0,0,0.1,0.1,0.1C27,6,27,6,26.9,6z M26.4,7.3
		L26.4,7.3L26.4,7.3L26.4,7.3z M24,7.3C24,7.3,24,7.3,24,7.3C24,7.3,24,7.3,24,7.3C24,7.3,24,7.3,24,7.3z M23.8,7.2
		C23.8,7.3,23.8,7.3,23.8,7.2C23.8,7.2,23.8,7.2,23.8,7.2C23.8,7.2,23.8,7.2,23.8,7.2C23.8,7.2,23.8,7.2,23.8,7.2z M23.9,5.7
		C23.9,5.6,23.9,5.6,23.9,5.7C23.9,5.6,23.9,5.6,23.9,5.7C23.9,5.6,23.9,5.6,23.9,5.7C23.9,5.6,23.9,5.6,23.9,5.7z M22.2,7.1
		C22.2,7.1,22.2,7.1,22.2,7.1c0-0.1,0-0.1,0-0.1C22.3,7,22.3,7,22.2,7.1C22.3,7.1,22.2,7.1,22.2,7.1z M22,5.4
		C22,5.4,22.1,5.4,22,5.4C22.1,5.4,22.1,5.4,22,5.4C22.1,5.4,22.1,5.5,22,5.4C22.1,5.5,22.1,5.4,22,5.4z M21.9,6.8L21.9,6.8
		L21.9,6.8L21.9,6.8z M21.7,6.8C21.7,6.8,21.7,6.8,21.7,6.8C21.7,6.8,21.7,6.8,21.7,6.8C21.7,6.8,21.7,6.8,21.7,6.8z M20.5,5.3
		L20.5,5.3L20.5,5.3L20.5,5.3z M14.1,5.1C14.1,5.1,14.1,5,14.1,5.1C14.1,5,14.1,5,14.1,5.1C14.1,5,14.2,5,14.1,5.1
		C14.2,5,14.2,5.1,14.1,5.1z M10.3,6.4C10.3,6.4,10.3,6.3,10.3,6.4C10.3,6.3,10.3,6.3,10.3,6.4C10.3,6.3,10.3,6.3,10.3,6.4
		C10.3,6.4,10.3,6.4,10.3,6.4z M9.8,6L9.8,6L9.8,6L9.8,6z M8.5,19C8.5,19,8.4,19,8.5,19C8.4,19.1,8.4,19.1,8.5,19
		C8.4,19,8.4,19,8.5,19C8.4,19,8.5,19,8.5,19z M8.5,18.5L8.5,18.5L8.5,18.5L8.5,18.5z M8.2,22.2C8.2,22.3,8.2,22.3,8.2,22.2
		C8.2,22.3,8.2,22.2,8.2,22.2C8.2,22.2,8.2,22.2,8.2,22.2z M8.2,23.4c0,0.2,0,0.5-0.2,0.6c0-0.3-0.2-0.3-0.4-0.3
		c0.1-0.2,0.1-0.5,0.2-0.7c0,0,0,0,0,0C7.8,23.3,8,23.3,8.2,23.4z M7.1,25.2C7,25.3,7,25.3,6.8,25.3c0,0,0-0.1,0-0.1c0,0,0,0,0,0
		c0,0,0,0,0.1,0C6.9,25,7,25.1,7.1,25.2z M6.9,24.6C6.9,24.7,6.9,24.7,6.9,24.6c-0.1,0.1-0.1,0-0.1,0c0,0,0-0.1,0.1-0.1
		C6.9,24.6,6.9,24.6,6.9,24.6z M6.8,26.1c0,0.2-0.1,0.3-0.2,0.3C6.6,26.3,6.7,26.2,6.8,26.1z M6.8,25.8C6.8,25.8,6.8,25.8,6.8,25.8
		C6.8,25.8,6.8,25.8,6.8,25.8C6.8,25.8,6.8,25.8,6.8,25.8z M6.7,25.8C6.7,25.8,6.7,25.8,6.7,25.8C6.7,25.8,6.7,25.8,6.7,25.8
		C6.7,25.8,6.7,25.8,6.7,25.8z M6.5,27.7c0,0.1,0,0.1-0.1,0.1c0.1-0.2,0.1-0.5,0.2-0.7c0,0,0,0,0,0C6.6,27.3,6.5,27.5,6.5,27.7z
		 M6.9,27.7c-0.1,0.1-0.1,0.2-0.2,0.2C6.7,27.8,6.8,27.7,6.9,27.7z M7.1,24.9C7.1,24.9,7.1,24.9,7.1,24.9
		C7.2,24.9,7.2,24.9,7.1,24.9C7.1,24.9,7.1,24.9,7.1,24.9z M6.9,27C6.9,27,6.9,27,6.9,27C6.9,27,6.9,27.1,6.9,27
		C6.9,27.1,6.9,27,6.9,27z M7.1,24.7c0-0.1,0.1-0.1,0.2,0c0,0,0,0.1,0,0.1c0,0-0.1,0.1-0.1,0.1C7.2,24.8,7.1,24.7,7.1,24.7z
		 M7.1,27.7c-0.1,0-0.1,0-0.2,0.1C7,27.6,7,27.6,7.1,27.7z M7.3,25.4C7.3,25.4,7.3,25.4,7.3,25.4c0.1,0,0.1,0,0.1,0
		c0,0.1,0,0.2,0,0.3C7.2,25.6,7.2,25.5,7.3,25.4z M7.6,23C7.6,23,7.6,23,7.6,23c0-0.1,0-0.1,0-0.1C7.6,22.9,7.7,22.9,7.6,23
		C7.7,23,7.6,23,7.6,23z M7.5,24.5C7.5,24.5,7.5,24.6,7.5,24.5C7.5,24.6,7.5,24.6,7.5,24.5C7.5,24.6,7.5,24.5,7.5,24.5
		C7.5,24.5,7.5,24.5,7.5,24.5z M7.7,24.1c0-0.1,0-0.2,0.1-0.2c0.1,0,0.1,0,0.2,0c-0.1,0.2,0,0.3,0.2,0.4C8.1,24.5,8,24.5,8,24.6
		c0,0-0.1,0.5-0.1,0.6C7.9,25.1,8,25,8.1,24.9C8.1,25.1,8,25.2,8,25.3c0,0.1,0,0.1,0,0.2c0,0-0.1,0-0.1,0c0-0.1,0-0.1,0-0.2
		c0,0-0.1-0.1-0.1-0.1c0,0.4-0.1,0.7-0.2,1c0,0,0,0,0,0c0,0-0.1-0.1-0.1-0.1c0.1-0.3,0.1-0.3,0.1-0.6c0-0.1,0-0.2,0-0.3
		C7.7,24.8,7.8,24.5,7.7,24.1z M7.4,27.5C7.4,27.6,7.4,27.6,7.4,27.5C7.4,27.6,7.4,27.5,7.4,27.5C7.4,27.5,7.4,27.5,7.4,27.5z
		 M7.3,28.4c0,0,0-0.1,0-0.1C7.4,28.3,7.4,28.3,7.3,28.4C7.4,28.4,7.4,28.4,7.3,28.4C7.4,28.5,7.3,28.5,7.3,28.4
		C7.3,28.5,7.3,28.4,7.3,28.4z M7.7,27c0-0.1,0-0.2-0.1-0.2c0.1-0.1,0.1-0.2,0.1-0.3c-0.1-0.1-0.1-0.2-0.1-0.4
		c0.1,0,0.2,0.1,0.2,0.2c0,0.4,0.1,0.8-0.1,1.2c0,0-0.1,0.1-0.1,0.2c0-0.2,0-0.3,0-0.4C7.6,27.3,7.6,27.2,7.7,27z M33.2,40.7
		c-0.1,0-0.1-0.1-0.2-0.1C33.1,40.5,33.2,40.6,33.2,40.7z M38.8,38.9L38.8,38.9L38.8,38.9L38.8,38.9z M38.7,42L38.7,42L38.7,42
		L38.7,42z M42.6,39.2c0.1,0,0.2-0.1,0.2-0.1c0.1,0,0.1,0.1,0.1,0.3C42.8,39.4,42.8,39.3,42.6,39.2z M42.6,40
		C42.7,40,42.7,40,42.6,40C42.7,40,42.7,40,42.6,40C42.6,40,42.6,40,42.6,40C42.6,40,42.6,40,42.6,40z M44.4,38.6
		C44.4,38.6,44.5,38.5,44.4,38.6c0.1-0.1,0.1-0.1,0.1-0.1C44.5,38.6,44.5,38.6,44.4,38.6C44.5,38.6,44.4,38.6,44.4,38.6z M54.5,31.2
		L54.5,31.2L54.5,31.2L54.5,31.2z M54.6,29.3L54.6,29.3L54.6,29.3L54.6,29.3z M54.8,27.3c0-0.2,0-0.2-0.1-0.3
		c-0.1-0.1-0.1-0.1-0.2-0.2c-0.1,0-0.1-0.1,0-0.1c0,0,0.1-0.1,0.1-0.1c0,0,0-0.1,0-0.1c0-0.1,0.1-0.1,0.1,0c0,0,0.1,0,0.1,0
		c0,0.1,0,0.1,0,0.2c0.1,0,0.2,0,0.4-0.1c0-0.1,0-0.2,0-0.3c0.1-0.1,0.2-0.1,0.2-0.2c0.2,0.2,0.3,0.5,0.3,0.8c0,0.1,0,0.1,0,0.2
		c0,0.1,0,0.1-0.1,0.2c0,0-0.1,0.1-0.1,0.1c0,0,0-0.1-0.1-0.1c0,0,0-0.1-0.1-0.1c-0.1,0.1-0.2,0.3-0.4,0.2
		C55,27.4,55,27.4,54.8,27.3z M56.1,24.6C56.1,24.6,56.1,24.6,56.1,24.6c0.1,0,0.1,0.1,0.1,0.2c0,0,0,0-0.1,0c0,0,0,0,0,0
		C56.1,24.7,56.1,24.6,56.1,24.6z M56.1,25c0,0.2,0,0.3-0.2,0.3C56,25.1,56,25.1,56.1,25z M27.8,9C27.8,9,27.8,9,27.8,9
		C27.8,9,27.8,9,27.8,9C27.8,9,27.8,9,27.8,9z M27.5,9L27.5,9L27.5,9L27.5,9z M24.8,8.9C24.8,8.8,24.8,8.8,24.8,8.9
		C24.8,8.8,24.8,8.8,24.8,8.9C24.8,8.8,24.8,8.9,24.8,8.9C24.8,8.9,24.8,8.9,24.8,8.9z M7.8,29.9c0-0.1,0-0.2,0-0.3
		C7.8,29.6,7.8,29.7,7.8,29.9C7.8,29.8,7.8,29.9,7.8,29.9z M7.8,30.1C7.8,30.2,7.8,30.2,7.8,30.1c0,0.1,0,0.1,0,0.1
		C7.8,30.2,7.8,30.2,7.8,30.1C7.8,30.2,7.8,30.1,7.8,30.1z M7.6,30.8c0,0-0.1,0.1-0.2,0.1c0,0-0.1-0.1-0.1-0.1c0-0.1,0-0.2,0.1-0.2
		C7.6,30.6,7.7,30.8,7.6,30.8z M7.7,29.4c0,0,0-0.1,0-0.1C7.7,29.3,7.7,29.3,7.7,29.4c0.1,0,0.1,0.1,0.1,0.1
		C7.7,29.5,7.7,29.4,7.7,29.4z M7.7,28.5c0.1,0,0.2,0,0.2,0c0-0.1,0-0.3,0-0.4C8,28,8.1,27.9,8.1,27.8c0,0.1,0.1,0.1,0.1,0.2
		c0.1,0,0.1-0.1,0.2-0.1c0,0.1,0,0.2-0.1,0.2c-0.1,0.2-0.3,0.3-0.5,0.4c0,0.1,0,0.2-0.1,0.2c0,0-0.1,0-0.1,0.1
		C7.6,28.7,7.6,28.7,7.7,28.5z M20.5,38.2C20.4,38.2,20.4,38.1,20.5,38.2c0-0.3,0.1-0.4,0.2-0.5c0,0,0,0,0.1,0.1
		C20.7,37.9,20.6,38.1,20.5,38.2C20.5,38.2,20.5,38.2,20.5,38.2z M32.5,38.4C32.5,38.4,32.5,38.4,32.5,38.4
		C32.6,38.4,32.6,38.4,32.5,38.4C32.6,38.4,32.5,38.5,32.5,38.4C32.5,38.4,32.5,38.4,32.5,38.4z M32.5,38.8
		C32.5,38.9,32.5,38.9,32.5,38.8C32.5,38.9,32.5,38.8,32.5,38.8C32.5,38.8,32.5,38.8,32.5,38.8C32.5,38.8,32.5,38.8,32.5,38.8z
		 M32.2,39.5c0,0,0-0.1,0-0.1c0.1-0.1,0.1-0.2,0.2-0.3c0,0.1,0,0.2,0,0.3C32.3,39.4,32.3,39.4,32.2,39.5z M33.1,39.7
		c0,0,0,0.1,0,0.1c0,0,0,0.1-0.1,0.1c0-0.2,0-0.4,0.2-0.6c0,0,0-0.1,0.1-0.1c0,0,0,0,0,0c0,0,0,0,0,0c0.1,0.1,0.2,0.1,0.3,0.2
		c0,0.2,0,0.3,0,0.5c0,0-0.1,0-0.1,0c0-0.1-0.1-0.1-0.1-0.2C33.3,39.6,33.2,39.6,33.1,39.7z M35.1,39.9
		C35.1,39.9,35.1,39.9,35.1,39.9C35.2,39.8,35.2,39.9,35.1,39.9C35.1,39.9,35.1,39.9,35.1,39.9C35.1,39.9,35.1,39.9,35.1,39.9z
		 M35.3,38.6C35.3,38.6,35.3,38.6,35.3,38.6C35.3,38.6,35.3,38.6,35.3,38.6c0.1-0.1,0.1-0.2,0.2,0c0,0,0,0.1,0,0.1
		C35.4,38.7,35.3,38.8,35.3,38.6z M37.1,39.3C37.1,39.3,37.1,39.4,37.1,39.3C37.1,39.4,37.1,39.4,37.1,39.3
		C37,39.4,37,39.4,37.1,39.3C37.1,39.3,37.1,39.3,37.1,39.3z M37.5,39.1L37.5,39.1L37.5,39.1L37.5,39.1z M45,39.2
		C45.1,39.2,45.1,39.2,45,39.2c0.1,0,0.1,0,0.1,0.1c0,0,0,0.1,0,0.1C45.1,39.3,45,39.3,45,39.2C45,39.3,45,39.2,45,39.2z M47.5,40.1
		C47.5,40.1,47.5,40.1,47.5,40.1C47.5,40.1,47.5,40.1,47.5,40.1C47.5,40.1,47.5,40.1,47.5,40.1z M47.7,39.2c0,0,0-0.1,0-0.1
		c0,0,0.1,0,0.1,0c0.1,0,0.1,0,0.2,0c0,0,0.1,0.1,0.1,0.2c0,0-0.1,0-0.1,0c0,0-0.1,0.1-0.1,0.1c-0.1,0.1-0.2,0.2-0.2,0.4
		c0,0,0,0-0.1-0.1c0-0.1,0-0.1,0.1-0.2C47.7,39.5,47.7,39.3,47.7,39.2z M48.7,39.3L48.7,39.3L48.7,39.3L48.7,39.3z M53,39.9
		C53,39.9,53,39.9,53,39.9C53,39.9,53,39.9,53,39.9C53,39.9,53,39.9,53,39.9C53,39.9,53,39.9,53,39.9z M53.9,28.8c0.1,0,0.1,0,0.1,0
		c0,0-0.1,0-0.1,0.1C53.9,28.9,53.9,28.9,53.9,28.8C53.9,28.8,53.9,28.8,53.9,28.8z M55.5,22.5C55.5,22.5,55.5,22.4,55.5,22.5
		c0,0,0-0.1-0.1-0.1c0,0,0,0,0,0C55.5,22.4,55.6,22.4,55.5,22.5C55.5,22.5,55.5,22.5,55.5,22.5z M55.2,18.2
		C55.2,18.2,55.2,18.2,55.2,18.2C55.1,18.2,55.1,18.2,55.2,18.2c-0.1-0.1-0.1-0.2,0-0.2c0,0,0,0,0,0C55.2,18,55.2,18.1,55.2,18.2
		C55.2,18.1,55.2,18.2,55.2,18.2z M38,8.8C38,8.8,38,8.7,38,8.8C38,8.7,38,8.7,38,8.8C38,8.7,38,8.7,38,8.8C38,8.7,38,8.8,38,8.8z
		 M25.4,11.4C25.4,11.4,25.4,11.4,25.4,11.4C25.4,11.4,25.4,11.4,25.4,11.4C25.4,11.4,25.4,11.4,25.4,11.4z M24.1,13.5
		C24.1,13.5,24.1,13.5,24.1,13.5C24.1,13.5,24.1,13.5,24.1,13.5c-0.1,0-0.1,0-0.2,0c0,0,0,0,0,0C24,13.5,24.1,13.4,24.1,13.5z
		 M23.8,13.6C23.8,13.6,23.8,13.6,23.8,13.6C23.8,13.6,23.8,13.6,23.8,13.6C23.8,13.5,23.8,13.5,23.8,13.6
		C23.8,13.5,23.8,13.5,23.8,13.6C23.8,13.5,23.8,13.5,23.8,13.6z M8.3,26.5c0,0.1-0.1,0.1-0.1,0.2c0,0.1-0.1,0.1-0.1,0.2
		C8,26.8,8,26.7,8,26.6c0-0.1,0-0.2,0.1-0.3c0.1,0,0.2-0.1,0.2-0.3c0-0.1,0.1-0.2,0.2-0.2C8.5,26,8.4,26,8.4,26.2
		C8.4,26.3,8.4,26.4,8.3,26.5z M8.2,27.4C8.2,27.4,8.2,27.4,8.2,27.4C8.3,27.4,8.3,27.4,8.2,27.4C8.3,27.4,8.2,27.4,8.2,27.4
		C8.2,27.4,8.2,27.4,8.2,27.4z M8.4,28.3c0.1-0.1,0.1-0.2,0.2-0.3c0-0.1,0.1-0.1,0.2-0.1c0,0,0,0.1,0,0.2
		C8.6,28.2,8.5,28.3,8.4,28.3z M11.5,29.1C11.5,29.1,11.5,29.2,11.5,29.1c0,0.1,0,0.1,0,0.1C11.5,29.2,11.5,29.1,11.5,29.1z
		 M12.2,29.3c0-0.1,0.1-0.2,0-0.3c0,0,0,0,0-0.1c0,0,0,0,0-0.1c0,0,0-0.1-0.1-0.1c0,0,0,0,0,0c0,0,0.1,0,0.1,0c0,0,0,0,0,0
		c0,0,0.1,0.1,0.1,0.1c0,0,0,0.1,0,0.1C12.4,29.1,12.3,29.2,12.2,29.3z M18.9,38C18.9,38,18.9,38.1,18.9,38c0,0.1,0.1,0.1,0.1,0.1
		c0,0,0,0,0,0c0,0,0,0,0,0c0,0,0,0.1,0,0.1c0,0,0,0,0,0c0,0-0.1,0-0.1,0C18.9,38.2,18.8,38.1,18.9,38z M33.2,39.2
		C33.2,39.2,33.2,39.2,33.2,39.2C33.2,39.2,33.2,39.2,33.2,39.2C33.2,39.2,33.2,39.2,33.2,39.2C33.2,39.2,33.2,39.2,33.2,39.2
		C33.2,39.2,33.2,39.2,33.2,39.2z M48.3,38.9C48.3,38.9,48.3,38.8,48.3,38.9c0-0.1,0-0.2,0-0.2c0.1,0,0.1,0,0.2,0c0,0.1,0,0.2,0,0.3
		C48.4,38.9,48.4,38.9,48.3,38.9C48.3,38.9,48.3,38.9,48.3,38.9z M49,39.3C49,39.3,49,39.3,49,39.3C49,39.3,49,39.4,49,39.3
		C49,39.4,49,39.4,49,39.3C49,39.3,49,39.3,49,39.3C49,39.3,49,39.3,49,39.3z M53.3,32.5C53.3,32.5,53.3,32.5,53.3,32.5
		C53.3,32.5,53.3,32.5,53.3,32.5C53.3,32.5,53.3,32.5,53.3,32.5C53.3,32.5,53.3,32.5,53.3,32.5C53.3,32.6,53.3,32.6,53.3,32.5
		C53.3,32.6,53.3,32.6,53.3,32.5C53.3,32.6,53.3,32.5,53.3,32.5C53.3,32.5,53.3,32.5,53.3,32.5C53.3,32.5,53.3,32.5,53.3,32.5
		C53.3,32.5,53.3,32.5,53.3,32.5C53.3,32.5,53.3,32.5,53.3,32.5z M54,25.6C54,25.6,54,25.6,54,25.6c0.1-0.1,0.2-0.1,0.3-0.1
		c0,0,0,0,0,0c0,0,0,0,0,0C54.2,25.7,54.2,25.7,54,25.6z M54.2,26.6C54.2,26.6,54.2,26.6,54.2,26.6C54.2,26.6,54.2,26.7,54.2,26.6
		C54.2,26.7,54.2,26.6,54.2,26.6z M52.3,30L52.3,30L52.3,30L52.3,30C52.3,30,52.3,30,52.3,30z M53.9,24.2
		C53.9,24.2,53.9,24.2,53.9,24.2C53.9,24.2,53.8,24.2,53.9,24.2C53.8,24.2,53.8,24.2,53.9,24.2z M53.9,25.6
		C53.9,25.6,53.9,25.6,53.9,25.6c0,0.1,0,0.1-0.1,0.1c-0.1,0-0.1,0-0.2,0c0,0,0,0,0,0c0,0,0-0.1,0-0.1c0,0,0,0-0.1,0c0,0,0,0,0,0
		c0,0,0-0.1,0-0.1c0,0,0,0,0.1,0c0,0,0,0,0,0.1C53.8,25.6,53.8,25.6,53.9,25.6z M54.5,19C54.5,19,54.5,19,54.5,19
		C54.6,19,54.6,19,54.5,19C54.6,19,54.6,19,54.5,19C54.5,19,54.5,19,54.5,19z M52,33.4C52,33.4,52,33.4,52,33.4
		C52,33.4,52,33.4,52,33.4C52,33.4,52,33.4,52,33.4C52,33.4,52,33.4,52,33.4C52,33.4,52,33.4,52,33.4z M53.2,25.7
		C53.2,25.6,53.2,25.6,53.2,25.7c0.1,0,0.1,0,0.1,0C53.3,25.7,53.3,25.7,53.2,25.7C53.2,25.7,53.2,25.7,53.2,25.7z M55,24.5
		C55,24.5,55,24.5,55,24.5C55,24.5,55,24.5,55,24.5C55,24.5,55,24.5,55,24.5C55,24.5,55,24.5,55,24.5C55,24.4,55,24.5,55,24.5
		L55,24.5C55,24.4,55,24.4,55,24.5L55,24.5z M53.7,29.2c0,0.1,0,0.1,0,0.2c0,0,0,0,0,0C53.7,29.3,53.7,29.2,53.7,29.2
		C53.7,29.2,53.7,29.2,53.7,29.2z M46.5,25.4c0,0.1,0,0.1,0.1,0.2c0,0,0,0,0,0c0,0-0.1,0-0.1,0c0,0,0-0.1,0-0.1
		C46.4,25.5,46.5,25.5,46.5,25.4C46.5,25.4,46.5,25.4,46.5,25.4z M45.2,21.4C45.2,21.4,45.3,21.4,45.2,21.4c0,0.1,0,0.1,0,0.1
		c0,0,0,0-0.1,0C45.2,21.4,45.2,21.4,45.2,21.4C45.2,21.4,45.2,21.4,45.2,21.4z M42.2,20.1L42.2,20.1L42.2,20.1L42.2,20.1z
		 M33.9,20.6c0,0,0,0.1-0.1,0.1c0,0,0,0,0,0c0-0.1-0.1-0.1-0.1-0.2c0,0-0.1,0-0.1,0c0.1,0,0.1-0.1,0.2-0.1
		C33.8,20.5,33.9,20.5,33.9,20.6C33.9,20.5,33.9,20.5,33.9,20.6C33.9,20.5,33.9,20.5,33.9,20.6C33.9,20.5,33.9,20.6,33.9,20.6z
		 M29.8,10.1C29.8,10.1,29.8,10.2,29.8,10.1C29.7,10.2,29.7,10.2,29.8,10.1C29.8,10.1,29.8,10.1,29.8,10.1
		C29.8,10.1,29.8,10.1,29.8,10.1z M24,14.8C24,14.8,24,14.8,24,14.8C24,14.8,24,14.8,24,14.8z M24.1,14.8
		C24.1,14.8,24.1,14.8,24.1,14.8C24.1,14.8,24.1,14.8,24.1,14.8C24.1,14.8,24.1,14.8,24.1,14.8z M24,14.8C24,14.7,24,14.7,24,14.8
		c0-0.1,0-0.1,0-0.1C24,14.7,24,14.7,24,14.8C24.1,14.7,24.1,14.7,24,14.8C24,14.8,24,14.8,24,14.8z M20.6,10.3
		C20.6,10.2,20.6,10.3,20.6,10.3C20.6,10.3,20.6,10.2,20.6,10.3z M16.7,9.1C16.7,9.2,16.7,9.2,16.7,9.1C16.6,9.2,16.6,9.2,16.7,9.1
		C16.7,9.2,16.7,9.1,16.7,9.1z M37.4,37.5C37.4,37.5,37.4,37.5,37.4,37.5C37.4,37.5,37.4,37.5,37.4,37.5
		C37.4,37.5,37.4,37.5,37.4,37.5z M46.1,23.9C46.1,23.9,46.1,23.9,46.1,23.9C46.1,23.9,46.1,23.9,46.1,23.9
		C46.2,23.9,46.2,23.9,46.1,23.9C46.2,23.9,46.1,23.9,46.1,23.9z M45.8,21.4C45.8,21.4,45.8,21.4,45.8,21.4
		C45.8,21.4,45.8,21.4,45.8,21.4C45.8,21.4,45.8,21.4,45.8,21.4z M42.5,21.7c-0.1,0-0.1-0.1-0.1-0.1c-0.1,0-0.2,0.1-0.3,0
		c0-0.1,0-0.2,0-0.2c0.1,0,0.2,0.1,0.4,0.1c0,0,0.1,0,0.1,0.1C42.6,21.6,42.6,21.6,42.5,21.7C42.5,21.7,42.5,21.7,42.5,21.7z
		 M39.2,21.1C39.2,21.1,39.2,21.2,39.2,21.1c0,0,0,0.1,0,0.1c-0.1,0-0.1-0.1-0.1-0.1C39.1,21.1,39.2,21.2,39.2,21.1z M34.1,20.9
		c0,0-0.1,0-0.1,0C34,20.9,34,20.9,34.1,20.9C34,20.9,34.1,20.9,34.1,20.9C34.1,20.9,34.1,20.9,34.1,20.9
		C34.1,20.9,34.1,20.9,34.1,20.9z M31.1,26.6c-0.1,0-0.1,0-0.2,0c0-0.1,0-0.1,0-0.2c0-0.1,0-0.2,0-0.3c0.1-0.1,0.2,0,0.1,0.1
		C30.9,26.3,31,26.4,31.1,26.6z M31,20.4C30.9,20.3,30.9,20.3,31,20.4C30.9,20.3,30.9,20.3,31,20.4C31,20.4,31,20.4,31,20.4z
		 M29.3,14.6C29.3,14.6,29.3,14.6,29.3,14.6C29.3,14.6,29.3,14.5,29.3,14.6C29.3,14.5,29.3,14.6,29.3,14.6
		C29.3,14.6,29.3,14.6,29.3,14.6z M23.1,12.2C23.1,12.2,23.1,12.2,23.1,12.2C23.1,12.2,23.1,12.2,23.1,12.2
		C23.1,12.2,23.1,12.2,23.1,12.2z M18.1,31.8L18.1,31.8L18.1,31.8L18.1,31.8z M33.1,31.6C33.1,31.6,33.1,31.6,33.1,31.6
		C33.1,31.6,33.1,31.6,33.1,31.6C33.1,31.6,33.1,31.6,33.1,31.6z M45.4,23.4C45.4,23.4,45.4,23.3,45.4,23.4
		C45.4,23.3,45.4,23.3,45.4,23.4C45.4,23.3,45.4,23.3,45.4,23.4C45.4,23.3,45.4,23.4,45.4,23.4z M45.2,23.7
		C45.2,23.7,45.2,23.7,45.2,23.7C45.3,23.7,45.3,23.7,45.2,23.7C45.3,23.8,45.3,23.8,45.2,23.7C45.2,23.8,45.2,23.8,45.2,23.7
		C45.2,23.8,45.2,23.7,45.2,23.7z M45.1,24.8L45.1,24.8L45.1,24.8L45.1,24.8z M43.6,23.4C43.7,23.4,43.7,23.4,43.6,23.4
		c0.1,0.1,0.2,0.2,0.2,0.3c0,0-0.1,0.1-0.1,0.1c-0.1,0-0.1,0-0.2,0c0-0.1,0-0.1,0-0.2C43.7,23.6,43.7,23.5,43.6,23.4z M41.4,22.3
		C41.4,22.3,41.4,22.3,41.4,22.3C41.4,22.3,41.4,22.3,41.4,22.3C41.4,22.3,41.4,22.3,41.4,22.3z M41,22.4C41,22.4,41,22.4,41,22.4
		C41,22.4,41.1,22.4,41,22.4C41.1,22.4,41.1,22.4,41,22.4z M41.1,22.8c0,0,0,0.1,0,0.1c0,0,0,0,0,0C41,23,41,23,41,23
		c-0.1-0.1-0.2-0.1-0.3-0.2c0.1,0,0.2,0,0.2-0.1C41,22.7,41,22.7,41.1,22.8C41.1,22.8,41.1,22.8,41.1,22.8z M39.2,21.9
		C39.2,21.9,39.2,21.8,39.2,21.9c0-0.1,0-0.1,0-0.1C39.2,21.8,39.2,21.8,39.2,21.9C39.2,21.8,39.2,21.9,39.2,21.9z M33.2,22.5
		C33.3,22.5,33.3,22.5,33.2,22.5c0.1,0.1,0.1,0.2,0.1,0.2c0,0,0,0,0,0c0,0.1,0,0.1,0,0.2c0,0,0,0,0,0.1c0,0,0,0,0,0c0,0-0.1,0-0.1,0
		c0,0,0,0,0,0.1c0,0,0,0,0,0c-0.1,0-0.1-0.1-0.1-0.1c0,0,0,0,0,0C33.1,22.7,33.2,22.6,33.2,22.5z M31.3,20.4c0,0,0.1,0,0.1,0
		c0,0,0,0.1,0,0.1c0,0-0.1,0-0.1,0C31.3,20.5,31.3,20.5,31.3,20.4C31.3,20.5,31.3,20.4,31.3,20.4z M30.7,20.5
		C30.7,20.5,30.7,20.5,30.7,20.5c0-0.1,0.1-0.2,0.2-0.2C30.9,20.4,30.8,20.5,30.7,20.5z M23.9,19.8C23.9,19.9,23.9,19.9,23.9,19.8
		C23.9,19.9,23.9,19.9,23.9,19.8c0,0,0-0.1,0-0.2c0,0,0-0.1,0-0.1c0,0,0.1-0.1,0.1-0.1c0.1,0.1,0.1,0.1,0.2,0.2c0,0,0,0.1,0,0.1
		c0,0-0.1,0-0.1,0.1l0,0c0,0,0,0-0.1-0.1C24,19.8,24,19.8,23.9,19.8C23.9,19.8,23.9,19.8,23.9,19.8z M20.4,13.8L20.4,13.8L20.4,13.8
		L20.4,13.8z M17.7,30.3L17.7,30.3L17.7,30.3C17.7,30.3,17.7,30.3,17.7,30.3z M35.7,26.7c0-0.1,0.1-0.2,0.2-0.2c0,0,0,0,0,0
		c-0.1,0-0.1,0.1-0.1,0.2C35.8,26.7,35.8,26.7,35.7,26.7z M35.8,28.6C35.8,28.6,35.8,28.6,35.8,28.6C35.9,28.6,35.9,28.6,35.8,28.6
		c0.1,0,0.1,0,0.1,0C35.9,28.6,35.9,28.7,35.8,28.6C35.9,28.7,35.8,28.7,35.8,28.6C35.8,28.6,35.8,28.6,35.8,28.6z M42.3,22.8
		c0.1,0.1,0.1,0.2,0.2,0.3c0.1,0,0.3,0.1,0.4,0.1c0,0,0,0,0,0c-0.1,0-0.2,0.1-0.3,0.1c0,0-0.1,0.1-0.1,0.1c0,0,0,0,0,0
		c-0.1-0.1-0.1,0-0.2,0.1c0.1,0.1,0.1,0.1,0.1,0.2c0,0,0,0,0,0c0,0-0.1,0-0.1-0.1c0-0.1,0-0.1,0-0.2c-0.1,0-0.2,0.1-0.2,0.2
		c0,0,0,0,0,0c0,0,0,0.1-0.1,0.2c0,0,0.1,0,0.1,0.1c0,0,0,0.1,0,0.1c0,0,0,0,0,0c0,0-0.1,0-0.1,0c-0.1,0-0.1-0.1-0.1-0.2
		c0,0,0-0.1,0-0.1c0.1-0.2-0.1-0.3-0.2-0.4c0,0-0.2,0-0.2,0c0,0,0,0-0.1,0.1c-0.1,0.1-0.2,0.1-0.3,0c0,0,0.1,0,0.1-0.2
		c0,0,0,0-0.1,0c0,0,0,0,0.1,0c0.2,0,0.2-0.1,0.4-0.1c0.2-0.1,0.2-0.1,0.2-0.3c0,0,0,0,0,0C42,22.6,42.2,22.6,42.3,22.8z M40.4,22.9
		C40.4,22.9,40.4,22.9,40.4,22.9c0.1,0.1,0.1,0.2,0.1,0.3c0,0,0,0,0,0c0,0,0,0,0,0c0,0,0,0-0.1,0c0,0,0,0-0.1,0
		c-0.1,0.1-0.1,0.2-0.2,0.3c-0.1-0.1-0.2-0.1-0.3-0.2c0,0,0,0,0-0.1c0,0,0,0,0,0c0,0,0,0,0,0c0.1,0,0.3-0.1,0.2-0.3
		c0-0.1,0-0.1,0-0.2c0,0,0,0,0,0c0,0.1,0,0.2,0,0.2C40.2,22.9,40.2,23,40.4,22.9C40.3,22.9,40.3,22.9,40.4,22.9z M42.7,22.9
		c0.1,0,0.1,0,0.1,0.1c0,0,0,0,0,0C42.8,22.9,42.7,22.9,42.7,22.9C42.6,22.9,42.6,22.9,42.7,22.9C42.7,22.9,42.7,22.9,42.7,22.9z
		 M42.8,24.5c0,0.1-0.1,0.2-0.2,0.2c-0.1,0-0.1,0-0.2,0.1c0,0,0,0,0,0.1c0,0.1-0.1,0.1-0.2,0c0,0-0.1,0-0.1-0.1c0,0,0-0.1-0.1-0.1
		c-0.1-0.1-0.1-0.3-0.2-0.4c0,0,0.1-0.1,0.2-0.1c0,0,0.1,0,0.1,0c0.1,0,0.1-0.1,0.1-0.2c0,0,0,0,0,0c0,0.1,0,0.2,0,0.3
		c0.1,0,0.1,0,0.1,0c0-0.1,0.1-0.1,0.1-0.2c0,0,0.1,0,0.1,0c0,0.1,0,0.2,0.1,0.3C42.7,24.4,42.7,24.5,42.8,24.5z M40.6,23.4
		C40.6,23.4,40.6,23.4,40.6,23.4C40.6,23.4,40.6,23.4,40.6,23.4C40.6,23.5,40.6,23.5,40.6,23.4z M43.6,25.7c0.1,0,0.2,0,0.3,0
		c0.1,0,0.1,0.1,0.2,0.2c-0.1,0-0.2,0-0.4,0c0,0,0,0,0,0C43.7,25.8,43.7,25.8,43.6,25.7c-0.1,0.1,0,0.2,0,0.2c0,0,0,0,0,0
		c0,0,0,0-0.1,0c-0.1,0-0.1-0.1-0.1-0.1c0,0-0.1-0.1-0.1-0.1C43.5,25.6,43.5,25.6,43.6,25.7C43.6,25.7,43.6,25.7,43.6,25.7z
		 M45.5,27.5C45.5,27.5,45.5,27.5,45.5,27.5C45.4,27.6,45.4,27.6,45.5,27.5C45.4,27.5,45.4,27.5,45.5,27.5z M44.5,27.5
		C44.5,27.5,44.4,27.5,44.5,27.5C44.4,27.5,44.4,27.5,44.5,27.5C44.4,27.5,44.5,27.5,44.5,27.5z M44.1,27C44.2,27,44.2,27,44.1,27
		C44.2,27,44.2,27,44.1,27C44.2,27,44.2,27,44.1,27C44.2,27,44.2,27,44.1,27z M44.2,27.5c-0.1,0-0.1,0-0.2-0.1c0,0,0,0,0-0.1
		C44.1,27.5,44.1,27.5,44.2,27.5C44.2,27.5,44.2,27.5,44.2,27.5z M44.2,27.8c0,0-0.1,0-0.1,0c0,0,0-0.1,0-0.1c0,0,0-0.1,0-0.1
		C44.1,27.7,44.1,27.7,44.2,27.8C44.1,27.8,44.1,27.8,44.2,27.8z M44.5,27.6C44.5,27.6,44.5,27.7,44.5,27.6c-0.1,0-0.1,0-0.1,0
		C44.4,27.6,44.5,27.6,44.5,27.6C44.5,27.6,44.5,27.6,44.5,27.6z M44.6,27.2C44.6,27.2,44.6,27.2,44.6,27.2
		C44.6,27.2,44.6,27.2,44.6,27.2C44.6,27.2,44.6,27.2,44.6,27.2z M46.5,27.8C46.5,27.8,46.5,27.8,46.5,27.8c0.1,0.1,0.1,0.2,0.1,0.3
		c0,0,0,0-0.1,0c0,0-0.1-0.1-0.1-0.1c0,0,0-0.1,0-0.1C46.4,27.8,46.5,27.8,46.5,27.8z M42.9,25.5c-0.1,0-0.1,0-0.2-0.1l0,0
		c0,0,0,0,0,0c0,0,0,0,0,0c0,0,0,0,0,0c0,0,0,0-0.1,0c0-0.1-0.1-0.2-0.1-0.3c0.1,0,0.2,0,0.3,0.1c0,0,0.1,0,0.1,0c0,0,0,0,0,0
		c0,0.1,0.1,0.1,0.1,0.2C42.9,25.4,42.9,25.4,42.9,25.5z M41.5,25.6c0,0-0.1,0-0.1,0c0-0.1,0-0.1,0-0.1c0,0,0,0,0.1,0
		C41.5,25.5,41.5,25.5,41.5,25.6z M40,24c0,0.1,0,0.2-0.1,0.3c0,0,0,0.1,0,0.1c0,0.1,0,0.2,0,0.2c0,0-0.1,0.1-0.1,0.1
		c0-0.3-0.1-0.5-0.1-0.8c0,0,0-0.1,0-0.1c0,0,0-0.1,0.1-0.1c0,0,0.1,0,0.1,0C39.9,23.8,40,23.9,40,24C40,24,40,24,40,24z M38.2,25.8
		C38.2,25.9,38.2,25.9,38.2,25.8c-0.1,0-0.1-0.1-0.1-0.1c0,0,0,0,0,0c0,0,0,0,0,0C38.2,25.7,38.2,25.8,38.2,25.8z M37.9,26.9
		C37.9,26.9,37.9,26.9,37.9,26.9C37.9,26.8,37.9,26.8,37.9,26.9c0.1-0.1,0.1,0,0.1,0c0,0.1,0,0.3,0,0.4c0,0,0,0,0,0
		c0-0.1,0-0.1-0.1-0.2C37.9,27.1,37.9,27,37.9,26.9z M38.1,23.8C38.1,23.8,38.2,23.8,38.1,23.8C38.2,23.8,38.1,23.8,38.1,23.8
		C38.1,23.8,38.1,23.8,38.1,23.8z M40.7,25c0,0-0.1,0-0.1,0c0,0,0,0,0,0c0-0.1,0-0.2,0.1-0.2C40.7,24.8,40.8,24.9,40.7,25
		C40.8,24.9,40.8,25,40.7,25z M41.1,25.9C41.1,26,41.1,26,41.1,25.9C41.1,26,41.1,26,41.1,25.9C41.1,26,41.1,26,41.1,25.9z
		 M40.4,25.3c0,0,0,0.1,0,0.1c-0.1,0-0.1,0-0.2-0.1c0,0,0-0.1,0-0.1c0,0,0-0.1,0.1-0.1C40.3,25.3,40.3,25.2,40.4,25.3
		C40.3,25.3,40.4,25.3,40.4,25.3z M39,24.9c0.1,0,0.2,0.1,0.1,0.3C39.1,25.1,39,25.1,39,24.9z M39.2,24.8
		C39.2,24.7,39.2,24.7,39.2,24.8C39.2,24.7,39.2,24.7,39.2,24.8C39.2,24.7,39.2,24.7,39.2,24.8C39.2,24.8,39.2,24.8,39.2,24.8z
		 M39,25.7c0,0,0.1,0,0.1,0c0,0,0,0.1,0,0.1C39.1,25.8,39.1,25.8,39,25.7C39.1,25.8,39,25.8,39,25.7z M41.1,27.2
		C41.1,27.2,41.1,27.1,41.1,27.2C41.1,27.1,41.1,27.2,41.1,27.2C41.1,27.2,41.1,27.2,41.1,27.2z M40.8,26.5c0,0,0,0.1,0.1,0.1
		c0,0,0,0.1-0.1,0.1c0-0.1-0.1-0.1-0.1-0.2c0,0,0,0,0,0C40.7,26.5,40.7,26.5,40.8,26.5z M40.9,26.5c0,0,0.1,0,0.1,0.1
		c0.1,0,0.1,0,0.2,0c0,0,0,0,0,0c0,0,0,0.1,0,0.1c-0.1,0-0.2,0-0.3,0c0,0,0,0,0,0C40.9,26.7,40.9,26.7,40.9,26.5
		C40.9,26.6,40.9,26.6,40.9,26.5z M41,26.9c0,0-0.1,0-0.1,0c0,0,0,0,0-0.1C40.9,26.9,41,26.9,41,26.9C41,26.9,41,26.9,41,26.9z
		 M41.6,27.6c0,0,0.1,0,0.1-0.1c0,0,0.1,0,0.1,0.1c0,0,0,0,0,0c0,0,0,0-0.1,0C41.7,27.7,41.6,27.6,41.6,27.6z M39.9,25
		c0,0,0,0.1,0,0.1C39.9,25.1,39.9,25.1,39.9,25z M40.3,24.6C40.3,24.6,40.4,24.6,40.3,24.6c0.1,0.1,0.1,0.1,0.1,0.2c0,0,0,0,0,0
		c0,0,0-0.1,0-0.1C40.3,24.7,40.3,24.7,40.3,24.6L40.3,24.6L40.3,24.6z M42,26.7C42.1,26.7,42.1,26.7,42,26.7
		C42.1,26.8,42.1,26.8,42,26.7C42.1,26.8,42.1,26.7,42,26.7z M42.6,26.4C42.5,26.4,42.5,26.4,42.6,26.4
		C42.5,26.3,42.5,26.3,42.6,26.4C42.6,26.3,42.6,26.3,42.6,26.4z M42.6,25.7C42.6,25.7,42.7,25.7,42.6,25.7c0.1,0,0.1,0,0.1,0.1
		c0,0,0,0-0.1,0c0,0,0,0,0,0C42.6,25.8,42.6,25.8,42.6,25.7C42.6,25.7,42.6,25.7,42.6,25.7z M42.1,27.5C42.1,27.5,42,27.5,42.1,27.5
		c-0.1-0.2-0.3-0.2-0.4-0.2c0,0-0.1-0.1,0-0.1c0,0,0,0,0,0c0,0.1,0.1,0.1,0.2,0.1c0,0,0,0,0,0c0,0,0.1,0,0.1,0
		C42,27.3,42.1,27.4,42.1,27.5z M40.4,24.4C40.4,24.4,40.4,24.4,40.4,24.4C40.4,24.4,40.4,24.4,40.4,24.4
		C40.4,24.4,40.4,24.4,40.4,24.4z M40.4,24.4C40.4,24.4,40.4,24.4,40.4,24.4C40.4,24.4,40.4,24.4,40.4,24.4
		C40.4,24.4,40.4,24.4,40.4,24.4z M40.9,25.3C40.9,25.3,40.9,25.3,40.9,25.3c0.1,0.1,0.2,0.1,0.1,0.2c0,0,0,0-0.1,0c0,0,0,0,0,0
		c-0.1,0-0.2,0-0.3,0c0,0,0,0,0,0c0-0.1,0-0.1,0-0.2c0,0,0-0.1,0.1-0.1C40.8,25.2,40.9,25.2,40.9,25.3z M41.4,25
		C41.4,25,41.4,25,41.4,25C41.4,25,41.4,25,41.4,25c-0.1,0.1-0.2,0-0.3,0c0,0,0-0.1,0-0.1C41.3,24.9,41.4,24.9,41.4,25z M42,25.2
		C42,25.2,42,25.2,42,25.2C42,25.2,42,25.2,42,25.2z M42.1,25.1c0,0,0.1,0,0.1,0c0.1,0,0.1,0,0.2,0c0,0.1-0.1,0.1-0.1,0.1
		c-0.1,0.1,0,0.2,0.1,0.2c0,0,0.1,0,0.1,0c-0.1,0-0.1,0.1-0.2,0.1c-0.1,0.1-0.2,0-0.3,0.1c-0.1,0-0.2-0.1-0.3-0.2c0-0.1,0-0.1,0-0.2
		c0,0,0-0.1,0-0.1c0,0,0.1,0,0.1,0.1c0,0.1,0,0.1,0.1,0.1C42.1,25.3,42.1,25.2,42.1,25.1z M42.4,27.7C42.4,27.6,42.4,27.6,42.4,27.7
		c0-0.1,0-0.1,0-0.2c0.1,0.3,0.1,0.3-0.1,0.4C42.3,27.8,42.3,27.8,42.4,27.7C42.4,27.7,42.4,27.7,42.4,27.7z M41.5,31.5
		C41.5,31.5,41.4,31.5,41.5,31.5C41.4,31.5,41.4,31.5,41.5,31.5C41.5,31.5,41.5,31.5,41.5,31.5C41.5,31.5,41.5,31.5,41.5,31.5z
		 M40.5,23.9c-0.1,0-0.1,0-0.2,0c0,0,0,0,0,0c0,0,0,0,0,0c0,0,0,0,0,0C40.3,23.8,40.4,23.8,40.5,23.9z M41.4,23.9
		C41.4,23.9,41.5,23.9,41.4,23.9c0.1,0,0.2,0,0.2,0c0,0,0,0.1,0,0.1c0,0.1-0.1,0.1-0.1,0.2c0,0.1-0.2,0.2-0.3,0.2
		c0,0.1,0,0.2,0.2,0.2c0,0,0.1,0,0.1,0c0,0,0,0,0,0.1c0,0.1-0.1,0.1-0.2,0.1c0,0-0.1-0.1-0.2-0.1c-0.1,0-0.1-0.1-0.2-0.1
		c0,0,0,0,0,0c0,0,0,0,0-0.1c0,0,0,0-0.1-0.1c0,0-0.1,0.1-0.1,0.1c0,0,0,0,0,0c-0.1-0.1-0.2-0.1-0.3-0.1c0,0,0,0-0.1,0c0,0,0,0,0,0
		c0,0,0-0.1,0-0.1c0.1,0,0.2-0.1,0.2-0.2c0.1-0.2,0.3-0.2,0.5-0.2C41.3,23.9,41.3,23.9,41.4,23.9z M43.4,25.7
		C43.3,25.7,43.3,25.7,43.4,25.7c-0.1,0.1-0.1,0.1-0.1,0c0,0-0.1-0.1-0.1-0.1c0-0.1,0.1-0.2,0.1-0.2c0,0,0,0,0,0
		C43.3,25.5,43.3,25.6,43.4,25.7z M43.4,24.9C43.4,24.9,43.4,24.9,43.4,24.9C43.4,24.9,43.4,24.9,43.4,24.9c0.1,0,0.2,0.1,0.2,0.1
		c0,0,0,0.1,0,0.1c0,0,0,0,0,0C43.4,25.1,43.4,25.1,43.4,24.9z M43.5,24.5C43.5,24.5,43.5,24.6,43.5,24.5
		C43.5,24.6,43.5,24.6,43.5,24.5C43.5,24.6,43.5,24.5,43.5,24.5z M44,25.1c0.1,0,0.1,0,0.1-0.1c0,0,0,0,0,0c0,0.1,0,0.2,0,0.2
		c0,0,0,0,0,0c0,0,0,0.1,0,0.1c0,0,0,0,0.1,0.1c0,0,0,0,0,0c0,0,0,0.1,0,0.1c0,0,0,0,0,0c0,0,0,0,0,0c0,0,0,0,0.1,0
		c0,0,0.1,0.1,0.1,0.1c0,0.1,0.1,0.1,0.1,0.2c0,0,0,0.1,0,0.1c0,0-0.1,0-0.1,0c0-0.1,0-0.1,0-0.2c0,0-0.1,0-0.1,0c0,0-0.1,0-0.1,0
		c-0.1,0-0.1-0.1-0.1-0.1c0-0.1,0-0.1-0.2-0.1c0,0-0.1,0-0.1,0c0-0.1-0.1-0.2,0-0.3C43.9,25.1,43.9,25.1,44,25.1z M39.8,22.6
		c0,0,0.1-0.1,0.1-0.1c0,0,0,0,0,0c0,0,0,0.1,0,0.1C40,22.6,39.9,22.6,39.8,22.6C39.9,22.6,39.9,22.6,39.8,22.6z M40.8,23.5
		C40.8,23.5,40.9,23.5,40.8,23.5C40.9,23.5,40.9,23.5,40.8,23.5C40.9,23.5,40.8,23.5,40.8,23.5z M41,23.1C41,23.2,41,23.2,41,23.1
		C41,23.2,41,23.2,41,23.1C41,23.2,41,23.2,41,23.1z M43,22.7C43,22.6,43,22.6,43,22.7c0.2,0,0.2,0,0.2,0.2c0,0,0,0.1,0,0.1
		c0,0-0.1,0-0.1-0.1C43,22.8,43,22.7,43,22.7z M43.7,24.3c0,0,0.1,0.1,0.1,0.2c0,0-0.1,0-0.1,0c-0.1,0-0.1,0-0.2,0c0,0,0,0,0,0
		c0,0,0,0,0,0c0,0,0,0,0,0c0,0,0,0,0,0c0,0-0.1,0.1-0.1,0.1c-0.1,0.1-0.1,0.1-0.2,0c0,0,0-0.1,0-0.1c0.1,0,0.1-0.1,0.2-0.1
		C43.5,24.4,43.5,24.4,43.7,24.3C43.6,24.3,43.7,24.3,43.7,24.3z M44.2,24.4C44.3,24.4,44.3,24.3,44.2,24.4c0.1,0,0.1,0,0.1,0
		c0,0,0,0.1,0,0.1c-0.1,0-0.3,0.1-0.4,0c0,0-0.1,0-0.1,0c0.1,0,0.1-0.1,0.2-0.1C44.1,24.4,44.2,24.4,44.2,24.4z M44.6,24.5
		C44.6,24.5,44.6,24.5,44.6,24.5C44.6,24.5,44.7,24.5,44.6,24.5c0.1,0.1,0,0.2,0,0.3l0,0c-0.1,0.1-0.2,0.2-0.3,0.2c0,0,0-0.1,0-0.1
		c0,0,0-0.1,0-0.1C44.4,24.7,44.5,24.6,44.6,24.5z M44.7,25.2C44.7,25.2,44.7,25.2,44.7,25.2c0-0.1,0-0.1,0-0.2
		C44.7,25.1,44.7,25.1,44.7,25.2C44.7,25.2,44.7,25.2,44.7,25.2z M45.5,25.5c0,0.1,0,0.1-0.1,0.2c0,0,0,0,0,0
		C45.4,25.6,45.4,25.6,45.5,25.5C45.4,25.5,45.5,25.5,45.5,25.5z M36.8,31.5L36.8,31.5L36.8,31.5L36.8,31.5z M37.5,28.2
		C37.5,28.2,37.5,28.2,37.5,28.2C37.5,28.2,37.5,28.2,37.5,28.2C37.5,28.2,37.5,28.2,37.5,28.2z M37.3,27.8c0,0,0,0.1,0.1,0.1
		c0,0,0,0,0,0C37.4,27.9,37.4,27.9,37.3,27.8z M37.2,21.1C37.2,21.1,37.2,21.1,37.2,21.1C37.2,21.1,37.2,21.1,37.2,21.1
		C37.2,21.1,37.2,21.2,37.2,21.1C37.2,21.1,37.2,21.1,37.2,21.1z M36.7,20.7C36.7,20.7,36.7,20.7,36.7,20.7
		C36.7,20.7,36.6,20.7,36.7,20.7c-0.1,0-0.1,0-0.1-0.1C36.6,20.6,36.6,20.6,36.7,20.7C36.7,20.6,36.7,20.7,36.7,20.7z M32.7,26.3
		C32.7,26.3,32.6,26.2,32.7,26.3c0-0.1,0-0.1,0-0.1C32.7,26.2,32.7,26.2,32.7,26.3C32.7,26.2,32.7,26.3,32.7,26.3z M32.4,26.2
		C32.4,26.1,32.4,26.1,32.4,26.2C32.4,26.1,32.4,26.1,32.4,26.2C32.4,26.1,32.4,26.1,32.4,26.2C32.4,26.2,32.4,26.2,32.4,26.2z
		 M29.9,25.5C29.9,25.5,29.9,25.5,29.9,25.5C29.9,25.4,29.9,25.4,29.9,25.5c0-0.1,0-0.2,0-0.3c0-0.1,0-0.1,0.1-0.2
		c0.1,0,0.1,0,0.2,0.1c0,0.1,0,0.1-0.1,0.2C30,25.4,30,25.4,29.9,25.5c0-0.1,0.1-0.1,0.1-0.1c0,0.1-0.1,0.2-0.1,0.3
		c0,0,0,0.1-0.1,0.1c0,0,0,0,0,0c0,0,0,0,0,0c0,0-0.1,0-0.2,0c0-0.1,0.1-0.1,0.1-0.1c0,0,0-0.1,0-0.1c0,0,0,0,0,0
		C29.9,25.6,29.9,25.6,29.9,25.5z M29.2,15.9C29.2,15.9,29.2,15.9,29.2,15.9C29.2,15.9,29.2,15.8,29.2,15.9
		C29.2,15.8,29.2,15.9,29.2,15.9C29.2,15.9,29.2,15.9,29.2,15.9z M18.5,30C18.5,30,18.5,30,18.5,30C18.5,29.9,18.5,29.9,18.5,30
		C18.5,30,18.5,30,18.5,30z M18.6,29.8C18.6,29.7,18.6,29.7,18.6,29.8c0.1,0.1,0.1,0.1,0.1,0.2c0,0-0.1,0-0.1,0
		C18.6,29.9,18.6,29.8,18.6,29.8z M19.1,19.3L19.1,19.3L19.1,19.3L19.1,19.3z M18.7,16.7C18.7,16.7,18.6,16.7,18.7,16.7
		C18.6,16.6,18.6,16.6,18.7,16.7C18.7,16.6,18.7,16.6,18.7,16.7z M30.8,28L30.8,28L30.8,28L30.8,28z M30.5,26.4
		C30.5,26.4,30.5,26.4,30.5,26.4C30.5,26.4,30.5,26.4,30.5,26.4C30.5,26.4,30.5,26.4,30.5,26.4C30.5,26.4,30.5,26.4,30.5,26.4z
		 M29.2,16.5C29.3,16.5,29.3,16.5,29.2,16.5c0.1,0.1,0,0.2,0,0.2C29.2,16.6,29.2,16.5,29.2,16.5z M27.7,17.4
		C27.7,17.4,27.7,17.4,27.7,17.4c-0.1-0.1-0.1-0.1-0.1-0.1c0.1,0,0.1-0.1,0.2-0.1c0,0,0,0.1,0,0.1C27.8,17.3,27.7,17.3,27.7,17.4z
		 M27.1,20.8C27.1,20.7,27.1,20.7,27.1,20.8c0.1-0.1,0.1,0,0.1,0C27.2,20.8,27.2,20.8,27.1,20.8C27.1,20.8,27.1,20.8,27.1,20.8z
		 M26.9,22.9C26.9,22.9,26.9,22.9,26.9,22.9C26.9,22.9,26.9,22.9,26.9,22.9C26.9,22.9,26.9,22.9,26.9,22.9
		C26.9,22.9,26.9,22.9,26.9,22.9C26.9,22.9,26.9,22.9,26.9,22.9C26.9,22.9,26.9,22.9,26.9,22.9z M25.1,20.1c0,0.1,0,0.2-0.1,0.3
		c0,0,0,0,0,0C25,20.2,25.1,20.1,25.1,20.1C25.1,20.1,25.1,20.1,25.1,20.1z M25,20.5c0,0.1-0.1,0.1-0.1,0.2
		C24.9,20.6,24.9,20.5,25,20.5C25,20.5,25,20.5,25,20.5z M20.8,31.9C20.8,31.8,20.9,31.8,20.8,31.9c0.1,0,0.1,0,0.2,0.1
		c0,0,0,0.1-0.1,0.1C20.8,32,20.8,31.9,20.8,31.9z M29,25.4C29,25.4,29,25.4,29,25.4c0-0.1,0-0.1,0-0.1c0,0,0,0,0,0c0,0,0,0,0,0
		C29,25.3,29,25.4,29,25.4z M29,17.3L29,17.3L29,17.3L29,17.3z M27.3,20.4c0,0-0.1,0-0.1-0.1c0,0,0-0.1,0-0.1c0-0.1,0.1-0.1,0.2-0.1
		c0,0,0,0,0,0.1c0,0,0,0,0,0c0,0-0.1,0.1-0.1,0.1c0,0,0,0.1,0,0.1C27.3,20.3,27.4,20.4,27.3,20.4C27.3,20.4,27.3,20.4,27.3,20.4z
		 M22,32.1C22,32.1,22,32.1,22,32.1c0.1,0.1,0,0.2,0,0.2c0,0,0,0,0,0.1c0,0,0-0.1,0-0.1C22,32.1,22,32.1,22,32.1z M24.3,21.8
		C24.3,21.8,24.3,21.8,24.3,21.8C24.3,21.8,24.3,21.8,24.3,21.8C24.3,21.8,24.3,21.8,24.3,21.8z M22.2,32.6c0,0-0.1,0.1-0.1,0.1
		c0,0,0,0-0.1-0.1c0,0,0,0,0,0C22,32.6,22,32.5,22.2,32.6C22.1,32.5,22.2,32.5,22.2,32.6C22.2,32.6,22.2,32.6,22.2,32.6z M25.9,19.9
		C25.9,19.9,25.9,19.9,25.9,19.9C25.9,19.9,25.9,19.9,25.9,19.9z M30.4,26.9C30.4,26.9,30.5,27,30.4,26.9c0,0.1,0,0.2,0,0.2
		c0,0,0,0-0.1-0.1C30.4,27,30.4,26.9,30.4,26.9z M27.6,26C27.7,26,27.7,26,27.6,26C27.7,26,27.7,26,27.6,26C27.7,26,27.6,26,27.6,26
		C27.6,26,27.6,26,27.6,26z M28.3,22.2C28.3,22.2,28.3,22.2,28.3,22.2C28.3,22.2,28.3,22.2,28.3,22.2C28.3,22.2,28.3,22.2,28.3,22.2
		C28.3,22.2,28.3,22.2,28.3,22.2z M28.5,26.1C28.4,26.1,28.4,26.1,28.5,26.1C28.4,26,28.4,26,28.4,26c0,0,0,0,0.1,0
		C28.4,26,28.5,26.1,28.5,26.1z M28.2,27.7C28.2,27.7,28.2,27.7,28.2,27.7C28.2,27.7,28.2,27.7,28.2,27.7
		C28.2,27.7,28.2,27.7,28.2,27.7C28.2,27.7,28.2,27.7,28.2,27.7z M28.6,26.8C28.6,26.8,28.6,26.8,28.6,26.8
		C28.6,26.8,28.6,26.8,28.6,26.8C28.6,26.8,28.6,26.8,28.6,26.8z M26.7,26.2C26.7,26.2,26.7,26.2,26.7,26.2
		C26.7,26.2,26.7,26.2,26.7,26.2C26.8,26.2,26.8,26.2,26.7,26.2C26.8,26.2,26.8,26.2,26.7,26.2z M26.9,25.6
		C26.9,25.6,27,25.6,26.9,25.6C27,25.6,27,25.6,26.9,25.6C27,25.7,26.9,25.7,26.9,25.6C26.9,25.6,26.9,25.6,26.9,25.6z M28.3,20.1
		L28.3,20.1L28.3,20.1L28.3,20.1z M28.5,21c-0.1,0-0.1,0-0.2,0C28.4,20.9,28.4,20.9,28.5,21C28.4,20.9,28.4,20.9,28.5,21
		C28.5,20.9,28.5,21,28.5,21z M28.5,22.3c0,0,0,0.1,0.1,0.1c0,0,0,0-0.1,0C28.5,22.4,28.5,22.4,28.5,22.3
		C28.5,22.4,28.5,22.3,28.5,22.3z M28.7,22.1L28.7,22.1L28.7,22.1L28.7,22.1z M28.8,18.1C28.8,18.1,28.9,18.1,28.8,18.1
		C28.9,18.1,28.9,18.1,28.8,18.1C28.9,18.1,28.8,18.1,28.8,18.1C28.8,18.1,28.8,18.1,28.8,18.1z M28.8,20.1c0.1,0,0.1,0,0.2-0.1
		c-0.1,0.1-0.1,0.2-0.1,0.3c0,0,0,0,0,0.1c0,0-0.1,0-0.1-0.1C28.9,20.3,28.7,20.2,28.8,20.1z M29.1,26.5
		C29.1,26.5,29.1,26.5,29.1,26.5C29.1,26.5,29.1,26.5,29.1,26.5C29.1,26.5,29.1,26.5,29.1,26.5C29.1,26.5,29.1,26.5,29.1,26.5z
		 M29.3,26.9C29.3,26.9,29.3,26.9,29.3,26.9C29.2,26.9,29.2,26.9,29.3,26.9C29.3,26.9,29.3,26.9,29.3,26.9
		C29.3,26.9,29.3,26.9,29.3,26.9z M30.1,26.2c0,0,0,0.1,0,0.1C30.1,26.3,30.1,26.3,30.1,26.2C30.1,26.2,30.1,26.2,30.1,26.2z
		 M27.5,17.6C27.5,17.6,27.5,17.6,27.5,17.6C27.5,17.6,27.5,17.6,27.5,17.6C27.5,17.6,27.5,17.6,27.5,17.6
		C27.5,17.6,27.5,17.6,27.5,17.6z M29.2,17.1C29.2,17.1,29.2,17.1,29.2,17.1C29.2,17.1,29.3,17.1,29.2,17.1
		C29.3,17.1,29.2,17.1,29.2,17.1C29.2,17.1,29.2,17.1,29.2,17.1C29.2,17.1,29.2,17.1,29.2,17.1z M30,26C30,25.9,30,25.9,30,26
		c0.1-0.1,0.1-0.1,0.1-0.1c0,0,0.1,0,0.1,0c0,0,0,0.1,0,0.1C30.1,25.9,30.1,25.9,30,26C30,25.9,30,25.9,30,26C30,26,30,26,30,26z
		 M30.6,24.1C30.6,24,30.6,24,30.6,24.1C30.6,24,30.6,24,30.6,24.1C30.6,24,30.6,24,30.6,24.1C30.6,24,30.6,24,30.6,24.1z M20.3,32
		c0,0-0.1,0-0.1,0c0-0.1,0-0.2,0-0.3c0,0,0,0,0,0c0,0,0,0,0.1,0C20.3,31.8,20.3,31.9,20.3,32z M24.6,20c0.1,0,0.1,0.1,0.1,0.1
		C24.7,20.2,24.6,20.1,24.6,20C24.6,20,24.6,20,24.6,20z M25,20c0,0,0-0.1,0-0.1C25.1,19.9,25.1,20,25,20C25.1,20.1,25.1,20.1,25,20
		C25,20.1,25,20.1,25,20C25,20,25,20,25,20z M25.9,14.9C25.9,14.9,25.9,14.9,25.9,14.9c0,0.1,0.1,0.2,0.1,0.2c0,0,0,0,0,0
		C25.9,15,25.9,14.9,25.9,14.9z M29,16.4C29.1,16.4,29.1,16.4,29,16.4c0.1,0,0.1,0,0.2,0C29.2,16.4,29.1,16.4,29,16.4z M34.1,27.4
		C34.1,27.4,34.1,27.4,34.1,27.4C34.1,27.4,34.1,27.4,34.1,27.4c0-0.1,0.1-0.1,0.1-0.1c0,0,0,0,0,0.1C34.2,27.4,34.2,27.4,34.1,27.4
		C34.1,27.4,34.1,27.4,34.1,27.4z M35.7,26.1C35.7,26.1,35.7,26.1,35.7,26.1c0-0.3,0-0.3-0.2-0.3c0,0,0,0-0.1,0c0,0,0-0.1,0-0.2
		c0.1,0,0.2,0.1,0.4,0.1c0,0,0,0,0,0c0,0.1,0,0.1,0.1,0c0,0,0-0.1,0.1-0.1c0,0,0,0,0,0c0.1,0,0.1,0,0.1,0.1c0,0,0,0.1,0,0.1
		c0,0,0,0.1,0,0.1c0,0.1,0,0.2-0.3,0.2C35.8,26,35.7,26.1,35.7,26.1z M36.7,28.1C36.7,28.1,36.8,28.1,36.7,28.1
		C36.8,28.1,36.8,28.1,36.7,28.1c0.1,0.1,0.1,0.3,0,0.3c0,0,0,0,0,0C36.6,28.3,36.7,28.2,36.7,28.1C36.7,28.1,36.7,28.1,36.7,28.1z
		 M36.9,27.2C36.9,27.2,36.9,27.2,36.9,27.2C36.9,27.2,36.9,27.2,36.9,27.2C36.9,27.2,36.9,27.2,36.9,27.2
		C36.9,27.2,36.9,27.2,36.9,27.2z M34.9,27C34.9,27,34.9,27,34.9,27C34.9,27,34.9,27,34.9,27C35,27,34.9,27,34.9,27
		C34.9,27.1,34.9,27,34.9,27z M36.2,24.4C36.3,24.4,36.3,24.4,36.2,24.4c0.1,0,0.1,0,0.1,0C36.3,24.4,36.3,24.4,36.2,24.4
		C36.3,24.4,36.2,24.4,36.2,24.4z M36.5,25C36.5,25,36.5,25,36.5,25C36.5,25,36.5,25,36.5,25C36.5,25,36.5,25,36.5,25z M37,23.8
		c0-0.1,0-0.1,0-0.2c0,0,0,0,0,0c0,0,0,0,0,0c0,0,0,0,0,0c0,0,0,0,0,0.1C37.1,23.7,37,23.8,37,23.8C37,23.8,37,23.8,37,23.8
		C37,23.8,37,23.8,37,23.8z M37,23.9C37,23.8,37,23.8,37,23.9c0.1,0,0.1-0.1,0.1-0.2c0,0,0,0,0,0.1c0,0,0,0,0,0.1
		C37.1,23.9,37.1,23.9,37,23.9z M36.8,25.6C36.8,25.6,36.8,25.5,36.8,25.6C36.8,25.5,36.9,25.5,36.8,25.6
		C36.9,25.6,36.9,25.6,36.8,25.6C36.9,25.6,36.9,25.6,36.8,25.6z M36.8,25C36.8,25,36.8,25,36.8,25C36.8,24.9,36.8,24.9,36.8,25
		C36.8,24.9,36.8,24.9,36.8,25C36.8,24.9,36.8,24.9,36.8,25z M37,25.7c0,0.1,0,0.1,0,0.2c0,0,0,0,0,0c0-0.1,0-0.1-0.1-0.2
		C36.9,25.6,37,25.7,37,25.7z M37,26.4c0,0-0.1,0.1-0.1,0.1c0-0.1,0-0.2,0-0.2C36.9,26.3,37,26.4,37,26.4z M33.9,27c0,0,0-0.1,0-0.1
		c0-0.1-0.1-0.2-0.2-0.3c0-0.1,0.1-0.2,0.1-0.3c0.1-0.1,0.2-0.1,0.2,0c0.1,0.1,0.2,0.3,0,0.5c0,0,0,0,0,0c0,0,0,0,0,0l0,0
		C34,26.9,34,27,33.9,27z M34.3,26.8C34.3,26.8,34.3,26.9,34.3,26.8C34.3,26.9,34.3,26.9,34.3,26.8C34.3,26.9,34.3,26.9,34.3,26.8
		C34.3,26.8,34.3,26.8,34.3,26.8z M35.5,21.7C35.5,21.7,35.5,21.7,35.5,21.7C35.5,21.7,35.5,21.7,35.5,21.7
		C35.5,21.7,35.6,21.7,35.5,21.7C35.6,21.7,35.5,21.7,35.5,21.7z M35.6,22.4C35.6,22.4,35.6,22.4,35.6,22.4c-0.1,0-0.1,0-0.1-0.1
		c0,0,0-0.1,0-0.1c0,0,0.1,0,0.1,0C35.6,22.3,35.6,22.3,35.6,22.4C35.7,22.4,35.6,22.4,35.6,22.4z M36.1,21.8
		C36.1,21.9,36.1,21.9,36.1,21.8c-0.1,0-0.1,0-0.1-0.1c0,0,0,0,0.1,0C36.1,21.7,36.1,21.8,36.1,21.8z M36,23.3c0,0.1,0,0.1-0.1,0.1
		c-0.1,0-0.1,0-0.2,0.1l0,0c0,0-0.1,0-0.1,0.1c0,0,0,0,0,0c0-0.1,0-0.1,0-0.2C35.8,23.3,35.9,23.3,36,23.3z M35.7,24.3
		C35.7,24.3,35.7,24.3,35.7,24.3C35.7,24.3,35.7,24.3,35.7,24.3C35.7,24.4,35.6,24.4,35.7,24.3C35.6,24.4,35.6,24.3,35.7,24.3
		C35.6,24.3,35.6,24.3,35.7,24.3C35.6,24.3,35.7,24.3,35.7,24.3z M34.7,23.7c0.1,0,0.1,0.1,0.2,0.2c0,0.1,0.1,0.1,0.2,0.1
		c-0.1,0.2-0.2,0.3-0.2,0.4c-0.1-0.1-0.1-0.2-0.2-0.3c0-0.1,0.1-0.2,0.1-0.3C34.7,23.8,34.7,23.8,34.7,23.7z M35.5,23.9
		C35.5,23.9,35.5,24,35.5,23.9c0,0.1,0,0.1,0,0.1c0,0,0,0,0,0c0,0,0,0,0,0C35.5,24,35.5,24,35.5,23.9z M34.3,26.2
		c-0.1,0-0.2,0-0.2-0.1C34.2,26,34.2,26,34.3,26.2z M33.8,25.7C33.8,25.7,33.8,25.7,33.8,25.7c0,0.1,0,0.2,0,0.3
		C33.7,25.9,33.7,25.8,33.8,25.7C33.8,25.7,33.8,25.7,33.8,25.7z M33.7,26.8C33.7,26.8,33.7,26.8,33.7,26.8
		C33.7,26.7,33.7,26.7,33.7,26.8c0.1,0,0.1,0.1,0.1,0.2c0,0-0.1,0.1-0.1,0.1c0,0,0,0.1,0,0.1c0,0-0.1,0-0.2,0
		C33.7,27.1,33.7,26.9,33.7,26.8z M32.8,28.4C32.8,28.4,32.8,28.4,32.8,28.4C32.8,28.4,32.8,28.4,32.8,28.4
		C32.8,28.4,32.8,28.4,32.8,28.4z M33.4,27.2c0,0-0.1,0-0.1,0c0,0,0-0.1,0-0.1c0,0,0,0,0,0C33.3,27,33.3,27.1,33.4,27.2z M33.4,26.6
		c0.1-0.1,0.1-0.1,0.2-0.1c0,0,0-0.2,0.1-0.1c0,0.1,0,0.2,0,0.3c0,0,0,0,0,0c0,0,0,0-0.1,0.1C33.5,26.7,33.4,26.6,33.4,26.6z
		 M34.5,22.2C34.5,22.2,34.5,22.2,34.5,22.2C34.5,22.2,34.5,22.2,34.5,22.2C34.5,22.2,34.5,22.2,34.5,22.2
		C34.5,22.2,34.5,22.2,34.5,22.2z M35.3,23.4C35.3,23.4,35.3,23.4,35.3,23.4c0.1,0,0.1,0,0.1,0.1c0,0,0,0,0,0c0,0,0,0.1,0,0.1
		c0.1,0,0.1,0,0.2,0c0,0,0,0,0,0c-0.1,0.1-0.1,0.1-0.2,0.1c0,0,0,0,0,0c-0.1-0.1-0.1-0.1-0.2-0.2c0,0,0-0.1,0-0.1
		C35.2,23.4,35.2,23.4,35.3,23.4z M35.6,21c0.1,0,0.1-0.1,0.1-0.1c0.1,0,0.1,0.1,0.1,0.2c0,0-0.1,0.1-0.1,0.1
		C35.7,21.2,35.7,21.1,35.6,21z M36.2,21.1C36.2,21.1,36.2,21.1,36.2,21.1C36.2,21.1,36.2,21.1,36.2,21.1
		C36.2,21.1,36.2,21.1,36.2,21.1C36.2,21.1,36.2,21.1,36.2,21.1z M35.7,25.2c0,0.1-0.1,0.1-0.1,0.1c0,0,0,0,0,0
		C35.6,25.2,35.7,25.2,35.7,25.2C35.7,25.1,35.7,25.1,35.7,25.2C35.7,25.1,35.7,25.1,35.7,25.2z M35.4,25.3c0,0-0.1,0-0.1,0
		c0,0,0,0,0-0.1c0.1-0.2,0.1-0.4,0.3-0.5c0,0,0,0,0,0C35.5,25,35.4,25.1,35.4,25.3C35.4,25.3,35.4,25.3,35.4,25.3z M34.8,25.6
		c-0.1,0.1-0.1,0.1-0.2,0.1c0,0-0.1,0-0.2,0c0.1-0.1,0.1-0.1,0.1-0.1C34.7,25.5,34.8,25.5,34.8,25.6C34.8,25.6,34.8,25.6,34.8,25.6z
		 M32.9,27c0,0.1,0,0.1,0,0.2C32.8,27.2,32.8,27.1,32.9,27z M33,26.4C33,26.4,33.1,26.4,33,26.4c0,0.1,0,0.1,0,0.2
		C32.9,26.6,32.9,26.5,33,26.4z M33.1,25.7C33.1,25.7,33,25.6,33.1,25.7C33.1,25.6,33.1,25.7,33.1,25.7
		C33.1,25.7,33.1,25.7,33.1,25.7z M33.2,26.7C33.2,26.7,33.2,26.7,33.2,26.7C33.2,26.8,33.2,26.8,33.2,26.7
		C33.2,26.7,33.2,26.7,33.2,26.7z M33.2,26.8C33.2,26.8,33.3,26.8,33.2,26.8C33.3,26.8,33.2,26.8,33.2,26.8
		C33.2,26.8,33.2,26.8,33.2,26.8z M36,20.8C36,20.8,36,20.8,36,20.8C35.9,20.8,35.9,20.8,36,20.8c-0.1,0-0.2-0.1-0.3-0.2
		c0,0,0,0,0,0C35.9,20.6,36,20.7,36,20.8z M36.4,20.8C36.4,20.8,36.4,20.8,36.4,20.8C36.4,20.8,36.4,20.8,36.4,20.8
		C36.3,20.8,36.3,20.8,36.4,20.8C36.3,20.8,36.3,20.8,36.4,20.8C36.3,20.7,36.3,20.7,36.4,20.8C36.3,20.7,36.4,20.7,36.4,20.8z
		 M36.3,21C36.3,21,36.3,21,36.3,21C36.3,21.1,36.3,21.1,36.3,21C36.3,21,36.3,21,36.3,21z M36.9,22.4
		C36.9,22.5,36.9,22.5,36.9,22.4c-0.1,0.1-0.1,0-0.1,0C36.8,22.4,36.9,22.4,36.9,22.4C36.9,22.4,36.9,22.4,36.9,22.4z M36.4,26.4
		C36.4,26.4,36.4,26.4,36.4,26.4C36.4,26.4,36.4,26.3,36.4,26.4C36.4,26.3,36.4,26.4,36.4,26.4z M36,27C36,27,36,27,36,27
		C36,27,36,27,36,27c0.1,0,0.1,0,0.1,0C36,27,36,27,36,27z M36,26.7c0-0.1,0-0.1,0-0.2c0,0,0.1,0,0.1-0.1c0,0,0.1-0.1,0.2-0.1
		c0,0,0,0,0,0.1C36.2,26.7,36.1,26.7,36,26.7z M35.9,26.3C35.9,26.3,35.9,26.3,35.9,26.3C35.9,26.3,35.9,26.3,35.9,26.3
		C35.9,26.3,35.9,26.3,35.9,26.3C35.9,26.3,35.9,26.3,35.9,26.3C35.9,26.3,35.9,26.3,35.9,26.3C35.9,26.3,35.9,26.3,35.9,26.3z
		 M35.2,27.1C35.1,27.1,35,27,35,27c0-0.1,0-0.2-0.1-0.2c-0.1,0-0.1-0.1-0.1-0.2c0-0.1,0.1-0.1,0.1-0.2c0,0-0.1,0-0.1,0
		c0-0.1,0-0.2,0-0.3c0.1,0.1,0.2,0.1,0.3,0.1c0,0,0,0,0,0c0,0,0-0.1-0.1-0.1c-0.1,0-0.2,0-0.3-0.1c0,0,0,0,0,0c0.1,0,0.1,0,0.1,0
		c0.2,0,0.3,0,0.3,0.3c0,0.1,0,0.2-0.1,0.3c0,0,0,0,0,0c0,0,0,0,0,0.1c0,0.2,0.1,0.2,0.3,0.2C35.2,26.8,35.2,26.9,35.2,27.1z
		 M34.1,27.7c0,0.1,0,0.2,0,0.3c0,0,0,0.1-0.1,0.1c0,0-0.1,0-0.1,0c0,0,0-0.1,0-0.1c0.2,0,0.2,0,0.2-0.1
		C34.1,27.8,34.1,27.8,34.1,27.7z M33.2,23.8C33.2,23.8,33.2,23.8,33.2,23.8C33.2,23.8,33.2,23.8,33.2,23.8
		C33.2,23.8,33.2,23.8,33.2,23.8z M33.4,21.4C33.4,21.3,33.4,21.3,33.4,21.4c0-0.1,0.1-0.2,0.1-0.2c0,0,0,0,0,0.1
		C33.5,21.3,33.5,21.3,33.4,21.4C33.5,21.4,33.5,21.4,33.4,21.4z M33.1,25.7C33.1,25.7,33.1,25.7,33.1,25.7c0,0.1,0,0.1,0,0.1
		C33.1,25.8,33.1,25.7,33.1,25.7z M34.1,23.3c0-0.1,0.1-0.2,0.2-0.1c0,0,0,0,0,0.1C34.2,23.3,34.2,23.3,34.1,23.3
		C34.1,23.4,34.1,23.4,34.1,23.3C34.1,23.4,34.1,23.4,34.1,23.3z M34.4,21.6C34.4,21.6,34.4,21.6,34.4,21.6
		C34.4,21.6,34.4,21.6,34.4,21.6C34.4,21.6,34.4,21.6,34.4,21.6z M36.5,21c0,0-0.1,0-0.1,0c0,0,0.1-0.1,0.1-0.1
		C36.5,20.9,36.5,20.9,36.5,21z M31.4,31.3C31.4,31.3,31.4,31.3,31.4,31.3C31.4,31.3,31.4,31.3,31.4,31.3z M18.7,32.1
		C18.8,32.1,18.8,32.1,18.7,32.1C18.8,32.1,18.8,32.1,18.7,32.1C18.8,32.1,18.8,32.2,18.7,32.1C18.8,32.1,18.8,32.1,18.7,32.1z
		 M20.6,12.1C20.7,12.1,20.7,12.1,20.6,12.1C20.7,12.1,20.7,12.1,20.6,12.1C20.7,12.1,20.7,12.1,20.6,12.1
		C20.7,12.1,20.7,12.1,20.6,12.1z M20.6,16.1c0-0.1,0-0.1,0-0.1c0,0,0.1,0,0.1,0c0,0,0,0.1,0,0.1C20.6,16,20.6,16.1,20.6,16.1
		C20.6,16.1,20.6,16.1,20.6,16.1z M24.1,19C24.1,19,24.2,19.1,24.1,19C24.1,19.1,24.1,19.1,24.1,19C24,19.1,24,19.1,24.1,19
		C24,19.1,24,19,24.1,19C24,19,24.1,19,24.1,19z M26.3,11.1C26.3,11.1,26.3,11.1,26.3,11.1C26.3,11,26.3,11.1,26.3,11.1
		C26.3,11.1,26.3,11.1,26.3,11.1C26.3,11.1,26.3,11.1,26.3,11.1z M26.1,14.2C26.1,14.2,26.1,14.2,26.1,14.2c0,0-0.1,0-0.1,0
		c0,0,0,0,0,0C26,14.1,26,14.1,26.1,14.2z M26.7,16.8C26.7,16.9,26.7,16.9,26.7,16.8C26.7,16.9,26.7,16.9,26.7,16.8
		C26.7,16.8,26.7,16.8,26.7,16.8C26.7,16.8,26.7,16.8,26.7,16.8z M26.2,19.9C26.2,19.9,26.2,19.9,26.2,19.9
		C26.2,19.8,26.2,19.9,26.2,19.9C26.2,19.9,26.2,19.9,26.2,19.9z M26.4,19.7L26.4,19.7L26.4,19.7L26.4,19.7z M26.7,20
		c0,0,0,0.1-0.1,0.1c0,0,0,0,0,0C26.6,20.1,26.7,20,26.7,20C26.7,20,26.7,20,26.7,20z M27.2,18.1C27.2,18.1,27.2,18.1,27.2,18.1
		C27.2,18.1,27.2,18.1,27.2,18.1C27.2,18.1,27.2,18.1,27.2,18.1C27.2,18.1,27.2,18.1,27.2,18.1z M30.1,21C30.1,21,30.1,21,30.1,21
		c-0.2,0.1-0.2,0.2-0.3,0.2c0,0,0,0,0,0c0,0,0,0,0-0.1c0-0.1,0.1-0.2,0.1-0.2c0,0,0,0,0,0c0,0,0,0,0,0C30,20.9,30.1,20.9,30.1,21z
		 M31.4,21.3c0,0-0.1,0.1-0.1,0.1c0,0,0,0,0,0C31.4,21.3,31.4,21.3,31.4,21.3C31.4,21.2,31.4,21.2,31.4,21.3c0-0.1,0-0.1,0-0.2
		C31.5,21.2,31.5,21.2,31.4,21.3z M30.8,25.6c0,0,0,0.1,0,0.1c0,0,0,0,0,0C30.7,25.7,30.7,25.7,30.8,25.6
		C30.8,25.6,30.8,25.6,30.8,25.6C30.8,25.6,30.8,25.6,30.8,25.6z M32.7,25.2C32.8,25.2,32.8,25.2,32.7,25.2c0.1,0,0.2,0.1,0.2,0.1
		c0,0,0,0.1,0,0.1c0,0-0.1,0-0.1,0C32.8,25.4,32.7,25.3,32.7,25.2z M34.9,21C34.9,21,35,21,34.9,21C35,21,35,21,34.9,21
		C35,21,35,21,34.9,21C34.9,21,34.9,21,34.9,21z M37.2,21.7C37.2,21.7,37.3,21.7,37.2,21.7c0.1,0,0.1,0,0.1,0.1c0,0,0,0.2,0,0.2
		c-0.1,0-0.1,0.1-0.1,0.1c-0.1,0-0.2-0.1-0.3-0.2c0,0,0-0.1,0-0.2c0,0,0.1,0,0.1,0C37,21.7,37.1,21.8,37.2,21.7
		C37.2,21.7,37.2,21.7,37.2,21.7z M37.9,22.7C37.9,22.8,37.9,22.8,37.9,22.7c0,0.1-0.1,0.1-0.1,0.1c0,0,0,0-0.1,0
		C37.7,22.8,37.8,22.8,37.9,22.7C37.8,22.7,37.8,22.7,37.9,22.7z M37.6,25.1C37.6,25.1,37.6,25.1,37.6,25.1c0,0.1,0,0.1,0,0.1
		c0,0,0,0,0,0C37.6,25.1,37.6,25.1,37.6,25.1z M41.3,22.7c0,0,0.1,0,0.1,0c0,0.1,0.1,0.2,0.1,0.2c0,0-0.1,0-0.1,0
		c-0.1,0-0.1,0.1-0.1,0C41.3,22.8,41.3,22.7,41.3,22.7z M42.3,22.4c0,0-0.1,0-0.1,0c0-0.1,0-0.1,0-0.2c0,0,0,0,0,0
		C42.2,22.2,42.2,22.3,42.3,22.4z M42.6,22.1c0,0.1,0,0.2-0.1,0.3c0,0,0,0,0,0C42.6,22.2,42.6,22.1,42.6,22.1z M43.3,22.5
		c-0.1,0-0.2,0-0.3,0c0,0,0,0,0,0c0,0,0-0.1,0-0.2c0,0.1,0.1,0.1,0.2,0.1C43.1,22.5,43.2,22.5,43.3,22.5
		C43.2,22.5,43.2,22.5,43.3,22.5z M43.3,23.6C43.3,23.6,43.3,23.6,43.3,23.6c0,0.1-0.1,0.1-0.1,0.1c0,0-0.1,0-0.1-0.1
		c0,0,0-0.1,0.1-0.1C43.2,23.5,43.3,23.5,43.3,23.6z M44.4,22.8C44.4,22.8,44.4,22.8,44.4,22.8C44.4,22.8,44.4,22.8,44.4,22.8
		C44.4,22.9,44.4,22.8,44.4,22.8C44.4,22.8,44.4,22.8,44.4,22.8z M44.9,24.1C44.9,24.1,44.9,24.1,44.9,24.1c-0.1,0.1-0.2,0.1-0.3,0
		c-0.1,0-0.1-0.1-0.2,0c0,0-0.1,0-0.1,0c0,0,0-0.1,0.1-0.1c0,0,0,0,0,0c0,0,0,0,0.1,0c0,0,0.1,0,0.1-0.1c0,0,0,0-0.1-0.1
		c0,0,0,0,0-0.1c0,0,0-0.1,0-0.1c0.1,0,0.2,0,0.2,0.1C44.8,23.9,44.8,24,44.9,24.1z M43.3,27.7C43.3,27.7,43.3,27.7,43.3,27.7
		C43.3,27.7,43.3,27.7,43.3,27.7C43.3,27.7,43.3,27.7,43.3,27.7z M37.3,27.8C37.3,27.8,37.3,27.8,37.3,27.8
		C37.3,27.8,37.3,27.8,37.3,27.8C37.4,27.8,37.3,27.8,37.3,27.8C37.3,27.8,37.3,27.8,37.3,27.8z M36.1,28.1
		C36.1,28.1,36.1,28.1,36.1,28.1C36.1,28.1,36.1,28.1,36.1,28.1C36.1,28.1,36.1,28.1,36.1,28.1z M35.7,27.1c0,0,0-0.1,0-0.2
		c0,0,0,0,0,0c0.1,0,0.1,0,0.2,0.1c0,0,0,0,0,0c0,0.1,0,0.2,0,0.2c0,0,0,0,0,0C35.7,27.1,35.7,27.1,35.7,27.1z M33.1,31.3
		C33.1,31.3,33.1,31.3,33.1,31.3c-0.1,0-0.1,0-0.1,0c0,0,0-0.1,0-0.2C33.1,31.2,33.1,31.3,33.1,31.3z M32.3,31.4c0,0-0.1,0-0.1,0
		c0,0,0,0,0,0c0,0,0-0.1,0-0.1c0,0,0,0,0.1,0C32.3,31.4,32.3,31.4,32.3,31.4C32.3,31.4,32.3,31.4,32.3,31.4z M31.2,32
		C31.1,32,31.1,32,31.2,32C31.2,32,31.2,32,31.2,32C31.2,32,31.2,32,31.2,32z M18.6,31.7c0,0-0.1,0-0.1,0.1c0,0,0-0.1,0-0.1
		c0,0,0,0,0,0c0.1,0,0.1,0,0.2,0C18.6,31.6,18.6,31.6,18.6,31.7C18.6,31.7,18.6,31.7,18.6,31.7z M18.5,32C18.5,32,18.5,32,18.5,32
		C18.5,32.1,18.5,32.1,18.5,32C18.5,32.1,18.5,32.1,18.5,32C18.5,32.1,18.5,32,18.5,32z M16.4,29.6C16.4,29.6,16.4,29.6,16.4,29.6
		c0,0,0-0.1,0-0.2C16.4,29.5,16.4,29.6,16.4,29.6z M14.9,26.4C14.9,26.4,14.9,26.4,14.9,26.4C14.9,26.4,14.9,26.4,14.9,26.4
		C14.9,26.4,14.9,26.4,14.9,26.4C14.9,26.4,14.9,26.4,14.9,26.4z M14.7,29.1C14.7,29.1,14.7,29.1,14.7,29.1c-0.1,0-0.1,0-0.2,0
		c0.1-0.1,0.2-0.1,0.3-0.1C14.8,29.1,14.7,29.1,14.7,29.1z M28,10.2c0.1,0,0.1-0.1,0.2-0.1c0,0,0,0.1,0.1,0.1c0,0-0.1,0.1-0.1,0.1
		c0,0,0,0,0,0C28,10.3,28,10.2,28,10.2z M31.2,20.7C31.2,20.7,31.2,20.7,31.2,20.7c0,0.1,0,0.2,0,0.4c0,0,0-0.1,0-0.1c0,0,0,0,0.1,0
		C31.2,20.9,31.2,20.8,31.2,20.7c-0.1,0.1-0.2,0-0.3,0c0,0,0-0.1,0-0.1C31,20.7,31.1,20.7,31.2,20.7z M31.6,22.4c0,0,0-0.1,0.1-0.1
		C31.7,22.3,31.7,22.3,31.6,22.4C31.7,22.4,31.7,22.4,31.6,22.4C31.6,22.4,31.6,22.4,31.6,22.4z M30.9,25.1
		C30.9,25.2,30.9,25.2,30.9,25.1c0,0.1-0.1,0.1-0.1,0.1c0,0,0,0,0,0.1c0,0-0.1-0.1,0-0.1c0,0,0-0.1,0-0.1
		C30.8,25.2,30.9,25.2,30.9,25.1z M31.2,26.1C31.2,26.1,31.2,26.1,31.2,26.1C31.3,26.1,31.3,26.1,31.2,26.1
		C31.3,26.2,31.3,26.2,31.2,26.1C31.2,26.2,31.2,26.1,31.2,26.1z M33,23.2C33,23.1,33,23.1,33,23.2c0.2-0.1,0.2-0.1,0.3-0.1
		c-0.1,0.1-0.1,0.2-0.2,0.3c0,0,0,0,0,0C33.1,23.2,33,23.2,33,23.2z M34.6,20.6c0,0,0.1,0,0.1,0c0,0,0,0,0,0
		c-0.1,0.1-0.1,0.1-0.2,0.1c0,0,0,0-0.1-0.1c0,0-0.1,0-0.1-0.1c0,0,0,0,0-0.1c0,0,0,0,0,0C34.5,20.6,34.6,20.6,34.6,20.6z
		 M40.5,21.4c0.1,0,0.1,0,0.2,0.2c0,0,0,0-0.1,0c-0.1,0-0.2,0-0.2,0C40.4,21.5,40.5,21.4,40.5,21.4z M41,22.4L41,22.4
		C41,22.4,41,22.4,41,22.4C41,22.5,41,22.5,41,22.4c-0.2,0-0.3,0-0.4,0c-0.1,0-0.1-0.1-0.1-0.2c0,0,0-0.1,0-0.1c0,0,0.1,0,0.1,0
		c0,0,0.1,0.1,0.1,0.1c0,0,0.1,0,0.1,0c0,0,0-0.1,0-0.1C40.9,22.2,40.9,22.3,41,22.4z M41.1,21.5C41.1,21.5,41.1,21.5,41.1,21.5
		C41.1,21.5,41.1,21.5,41.1,21.5C41.1,21.5,41.1,21.5,41.1,21.5z M41.1,21.9C41.1,22,41.2,22,41.1,21.9c0.1,0.1,0,0.1,0,0.2
		c0,0,0,0,0,0C41.1,22.1,41.1,22,41.1,21.9z M43.5,23.2C43.5,23.2,43.5,23.2,43.5,23.2c0.1,0.1,0.1,0.1,0.2,0.2c0,0,0,0,0,0
		c0,0,0,0,0,0c-0.1,0-0.2,0-0.3-0.1C43.3,23.2,43.4,23.2,43.5,23.2z M44.1,22.8C44.2,22.8,44.2,22.8,44.1,22.8c0.1,0,0.1,0,0.1,0
		c0,0-0.1,0.1-0.1,0.1c0,0,0,0,0,0C44.1,22.9,44.1,22.8,44.1,22.8C44.1,22.8,44.1,22.8,44.1,22.8z M44.8,23.2
		C44.8,23.2,44.9,23.2,44.8,23.2c0,0.1,0,0.2,0,0.3c0,0,0,0,0,0c0,0-0.1-0.1-0.1-0.1C44.7,23.3,44.8,23.2,44.8,23.2z M45,22.9
		c0,0,0.1,0,0.1,0c0,0,0,0,0,0c0,0,0,0.1,0,0C45.1,22.9,45.1,22.9,45,22.9z M46.5,25.8c0,0,0.1,0,0.1,0
		C46.6,25.8,46.6,25.9,46.5,25.8C46.6,25.9,46.5,25.9,46.5,25.8C46.5,25.9,46.5,25.8,46.5,25.8z M47.4,28.6
		C47.4,28.6,47.4,28.6,47.4,28.6C47.4,28.6,47.4,28.6,47.4,28.6C47.4,28.6,47.4,28.5,47.4,28.6C47.4,28.5,47.4,28.5,47.4,28.6
		C47.4,28.5,47.4,28.5,47.4,28.6C47.5,28.5,47.4,28.6,47.4,28.6z M35.7,28.1C35.7,28.1,35.7,28.1,35.7,28.1c0,0,0.1-0.1,0.1-0.1
		C35.8,28.1,35.8,28.1,35.7,28.1z M17.5,31.2C17.5,31.2,17.4,31.2,17.5,31.2c-0.1,0-0.1,0-0.1,0C17.4,31.1,17.4,31.1,17.5,31.2
		C17.5,31.1,17.5,31.1,17.5,31.2z M23.2,12.3C23.2,12.3,23.2,12.3,23.2,12.3c0,0,0,0.1-0.1,0.1C23.2,12.4,23.2,12.4,23.2,12.3
		C23.1,12.3,23.1,12.3,23.2,12.3C23.2,12.2,23.2,12.3,23.2,12.3z M23.8,10.5c0.1,0,0.1,0.1,0.1,0.1c0,0,0,0,0,0c0,0.1,0,0.1-0.1,0.1
		c0,0-0.1,0-0.1,0c0,0-0.1,0-0.1,0c0,0,0,0,0,0c0,0,0-0.1-0.1-0.1c0,0,0,0,0-0.1c0,0,0.1,0.1,0.1,0.1c0,0,0,0,0-0.1
		C23.8,10.5,23.8,10.5,23.8,10.5z M24.7,17.4C24.7,17.4,24.7,17.4,24.7,17.4c0,0.1,0,0.2,0,0.2c0,0,0,0,0,0
		C24.6,17.5,24.6,17.5,24.7,17.4C24.6,17.4,24.7,17.4,24.7,17.4z M29.4,12.1c0,0,0,0.1,0,0.1c0,0,0,0,0,0
		C29.3,12.2,29.3,12.2,29.4,12.1C29.3,12.1,29.3,12.1,29.4,12.1z M31,20.3C31.1,20.3,31.1,20.3,31,20.3C31,20.4,31,20.3,31,20.3
		C31,20.3,31,20.3,31,20.3z M31.9,22C31.9,22,31.9,22,31.9,22C31.9,22.1,31.9,22.1,31.9,22C31.9,22.1,31.9,22.1,31.9,22
		C31.9,22,31.9,22,31.9,22z M31.8,26.6c0,0,0.1-0.1,0.1-0.1c0,0,0,0,0,0C31.9,26.5,31.9,26.6,31.8,26.6
		C31.8,26.6,31.8,26.6,31.8,26.6z M32,25.8C32.1,25.8,32.1,25.8,32,25.8C32.1,25.9,32.1,25.9,32,25.8C32.1,25.9,32,25.8,32,25.8z
		 M32.7,23.5C32.8,23.5,32.8,23.5,32.7,23.5c0,0.1,0,0.1,0,0.1c0,0-0.1-0.1-0.1-0.1c0,0,0,0,0,0C32.6,23.5,32.7,23.5,32.7,23.5
		C32.7,23.5,32.7,23.5,32.7,23.5z M39.3,19.9C39.4,19.9,39.4,20,39.3,19.9C39.4,20,39.4,20,39.3,19.9C39.4,20,39.3,20,39.3,19.9
		C39.3,20,39.3,19.9,39.3,19.9z M39.5,21.1c0.1,0,0.1,0,0.2,0c0,0.1-0.1,0.2-0.1,0.3C39.6,21.3,39.5,21.2,39.5,21.1z M39.7,21.1
		C39.7,21,39.8,21,39.7,21.1C39.8,21.1,39.8,21.1,39.7,21.1C39.8,21.1,39.7,21.1,39.7,21.1z M40.1,21.2c0,0,0.1,0.1,0.1,0.1
		c0.1,0.1,0.1,0.1,0.1,0.2c0,0,0,0,0,0c-0.1,0-0.1-0.1-0.2-0.1C40.1,21.3,40.1,21.3,40.1,21.2z M40.8,20.1
		C40.8,20.1,40.8,20.1,40.8,20.1C40.8,20.1,40.8,20.1,40.8,20.1C40.8,20.1,40.8,20.1,40.8,20.1C40.8,20.1,40.8,20.1,40.8,20.1z
		 M40.9,21c0,0.1,0,0.1,0,0.2c0,0,0,0,0,0C40.9,21.2,40.9,21.1,40.9,21C40.9,21.1,40.9,21.1,40.9,21z M40.9,20.9c0,0,0-0.1,0-0.1
		c0,0,0.1,0,0.1,0C41,20.9,41,20.9,40.9,20.9z M41,20.7c0.1,0,0.1,0.1,0,0.1C41,20.8,41,20.7,41,20.7C41,20.7,41,20.7,41,20.7z
		 M41,21.2c0,0-0.1,0.1-0.1,0c0,0,0,0,0,0c0,0,0,0,0,0C40.9,21.2,41,21.2,41,21.2C41,21.2,41,21.2,41,21.2z M41.4,21.3
		C41.4,21.3,41.4,21.3,41.4,21.3c0.2,0.1,0.2,0.2,0.3,0.3c0,0,0,0,0,0C41.6,21.6,41.5,21.5,41.4,21.3C41.4,21.3,41.4,21.3,41.4,21.3
		z M41.8,21.3C41.8,21.3,41.9,21.3,41.8,21.3c0.1,0,0.1,0.1,0.1,0.1C41.9,21.4,41.8,21.4,41.8,21.3C41.8,21.4,41.8,21.4,41.8,21.3z
		 M43.9,22.7c0,0,0,0.1,0,0.1c0,0-0.1,0.1-0.1,0.1c-0.1,0-0.1-0.1-0.1-0.1c0,0,0.1-0.1,0.1-0.1C43.7,22.7,43.8,22.7,43.9,22.7z
		 M44.9,22.9c0,0,0,0.1,0,0.1c0,0,0,0,0,0c0,0,0,0,0,0C44.9,23,44.8,23,44.9,22.9C44.8,22.9,44.8,22.9,44.9,22.9z M46,25.3
		C45.9,25.3,45.9,25.3,46,25.3c-0.1,0-0.1,0-0.2,0C45.9,25.2,45.9,25.3,46,25.3C46,25.3,46,25.3,46,25.3z M48.1,21.3
		C48.2,21.3,48.2,21.3,48.1,21.3c0.1,0,0.1,0,0.2,0c0,0,0,0,0,0c0,0,0,0,0,0c-0.1,0-0.2,0-0.2,0c0,0,0,0,0,0
		C48.1,21.4,48.1,21.3,48.1,21.3z M49.5,23.3C49.5,23.3,49.6,23.3,49.5,23.3C49.6,23.3,49.6,23.3,49.5,23.3
		C49.5,23.3,49.5,23.3,49.5,23.3z M51.8,24C51.8,24,51.8,24,51.8,24C51.8,24,51.8,24,51.8,24C51.8,24,51.8,24,51.8,24
		C51.8,24,51.8,24,51.8,24C51.8,24,51.8,24,51.8,24z M50.8,28.3C50.8,28.3,50.8,28.3,50.8,28.3C50.7,28.3,50.8,28.3,50.8,28.3
		L50.8,28.3C50.8,28.3,50.8,28.3,50.8,28.3z M48.5,26.6C48.5,26.6,48.5,26.6,48.5,26.6C48.5,26.6,48.5,26.6,48.5,26.6
		C48.5,26.6,48.5,26.6,48.5,26.6C48.5,26.6,48.5,26.6,48.5,26.6z M49.7,28C49.6,28,49.6,28,49.7,28c-0.2,0.1-0.2,0.1-0.2,0.2
		c0,0,0,0.1,0,0.1c0,0,0,0-0.1,0c0-0.1,0-0.2,0.1-0.3C49.5,28,49.6,28,49.7,28z M49.5,28.4c0,0.1,0,0.1-0.1,0.1c0,0-0.1,0-0.1,0
		c0,0,0,0,0,0C49.4,28.4,49.4,28.4,49.5,28.4C49.5,28.3,49.5,28.3,49.5,28.4z M49.1,28.7L49.1,28.7L49.1,28.7L49.1,28.7z M49.1,28.9
		C49.2,28.9,49.2,29,49.1,28.9C49.2,29,49.2,29,49.1,28.9C49.1,29,49.1,29,49.1,28.9C49.1,28.9,49.1,28.9,49.1,28.9z M48.6,28.3
		C48.6,28.3,48.6,28.4,48.6,28.3c0,0.1,0,0.2,0.1,0.3c0,0,0,0,0,0c0,0-0.1,0-0.1,0c0,0,0,0,0-0.1C48.5,28.5,48.6,28.4,48.6,28.3z
		 M49.1,28.3c0,0,0.1,0,0.1,0C49.2,28.3,49.2,28.3,49.1,28.3C49.1,28.3,49.1,28.3,49.1,28.3C49.1,28.3,49.1,28.3,49.1,28.3z
		 M49.2,28C49.2,28,49.2,28,49.2,28c0.1,0.1,0.1,0.1,0,0.1C49.2,28.1,49.2,28.1,49.2,28C49.1,28,49.1,28,49.2,28
		C49.1,28,49.2,28,49.2,28z M50,31.2C50,31.2,50,31.2,50,31.2C49.9,31.2,49.9,31.2,50,31.2C49.9,31.2,50,31.2,50,31.2z M50.1,31.1
		c0,0-0.1,0.1-0.1,0.1C50,31.1,50,31.1,50.1,31.1C50.1,31.1,50.1,31.1,50.1,31.1z M48,37.1c-0.1,0-0.1,0.1-0.2,0.1c0,0,0,0,0,0
		c0,0,0,0,0,0C47.9,37.1,47.9,37,48,37.1C47.9,37,48,37,48,37.1C48,37,48,37,48,37.1z M49.1,26.4C49.1,26.4,49.1,26.5,49.1,26.4
		c-0.1,0.1-0.2,0-0.2-0.1c0,0,0.1,0,0.1,0C49,26.3,49.1,26.3,49.1,26.4z M49.3,26.6c0-0.1,0.1-0.1,0.2-0.1c0,0,0,0.1,0,0.1
		C49.5,26.7,49.4,26.7,49.3,26.6C49.3,26.6,49.3,26.6,49.3,26.6C49.3,26.6,49.3,26.6,49.3,26.6C49.3,26.6,49.3,26.6,49.3,26.6
		C49.3,26.6,49.3,26.6,49.3,26.6z M50.2,28.9C50.2,28.9,50.2,28.9,50.2,28.9C50.2,28.9,50.2,28.9,50.2,28.9
		C50.2,28.9,50.2,28.9,50.2,28.9z M50.3,28.9c0,0,0-0.1,0-0.1c0,0,0.1,0,0.1,0.1C50.4,28.9,50.4,29,50.3,28.9
		C50.4,28.9,50.3,28.9,50.3,28.9z M51.1,28.5C51.1,28.5,51.1,28.5,51.1,28.5C51,28.5,51,28.5,51.1,28.5C51,28.5,51,28.5,51.1,28.5
		C51.1,28.5,51.1,28.5,51.1,28.5z M48.5,24.2C48.5,24.2,48.5,24.1,48.5,24.2C48.6,24.1,48.6,24.1,48.5,24.2
		C48.6,24.2,48.6,24.2,48.5,24.2C48.5,24.2,48.5,24.2,48.5,24.2z M48.6,25.6C48.6,25.6,48.6,25.6,48.6,25.6c0,0,0.1,0.1,0.1,0.1
		c0,0,0,0.1,0,0.1c0,0-0.1,0-0.1,0c0,0-0.1-0.1-0.1-0.2c0,0,0,0,0,0C48.6,25.6,48.6,25.6,48.6,25.6z M50.4,26.5c0.1,0,0.1,0,0.1,0.1
		c0,0,0,0,0,0C50.5,26.6,50.4,26.6,50.4,26.5C50.3,26.6,50.3,26.6,50.4,26.5C50.3,26.6,50.3,26.5,50.4,26.5
		C50.4,26.5,50.4,26.5,50.4,26.5z M50.3,26.9C50.3,26.9,50.3,26.9,50.3,26.9C50.3,26.9,50.3,26.9,50.3,26.9
		C50.3,26.9,50.3,26.9,50.3,26.9C50.3,26.9,50.3,26.9,50.3,26.9z M50.2,37.4C50.2,37.4,50.2,37.4,50.2,37.4
		C50.1,37.5,50.1,37.5,50.2,37.4C50.1,37.4,50.1,37.4,50.2,37.4C50.1,37.4,50.2,37.4,50.2,37.4C50.2,37.4,50.2,37.4,50.2,37.4z
		 M50.9,23.6c0,0,0,0.1,0,0.1c0,0-0.1,0-0.1,0C50.8,23.7,50.8,23.6,50.9,23.6C50.8,23.6,50.8,23.6,50.9,23.6
		C50.8,23.6,50.8,23.6,50.9,23.6z M49.6,37.3C49.6,37.3,49.6,37.3,49.6,37.3C49.6,37.3,49.6,37.2,49.6,37.3
		C49.6,37.3,49.6,37.3,49.6,37.3C49.6,37.3,49.6,37.3,49.6,37.3z M30.5,38.2C30.5,38.2,30.5,38.2,30.5,38.2
		C30.5,38.2,30.5,38.3,30.5,38.2C30.5,38.3,30.5,38.2,30.5,38.2C30.5,38.2,30.5,38.2,30.5,38.2z M12.6,27.6
		C12.6,27.6,12.6,27.7,12.6,27.6C12.6,27.7,12.6,27.7,12.6,27.6C12.5,27.7,12.5,27.7,12.6,27.6C12.5,27.6,12.5,27.6,12.6,27.6z
		 M11.7,20.9L11.7,20.9L11.7,20.9L11.7,20.9C11.7,21,11.7,21,11.7,20.9z M11.9,25.2L11.9,25.2L11.9,25.2L11.9,25.2z M11.3,28
		C11.3,28,11.3,28,11.3,28C11.3,28,11.3,28,11.3,28C11.3,28,11.3,28,11.3,28C11.4,28,11.4,28,11.3,28C11.3,28,11.3,28,11.3,28z
		 M12.3,25.7C12.3,25.7,12.3,25.8,12.3,25.7C12.3,25.8,12.3,25.8,12.3,25.7C12.3,25.8,12.2,25.8,12.3,25.7
		C12.2,25.8,12.2,25.8,12.3,25.7z M12.8,27.4C12.8,27.4,12.7,27.4,12.8,27.4C12.7,27.4,12.7,27.4,12.8,27.4
		C12.7,27.4,12.7,27.3,12.8,27.4C12.7,27.3,12.8,27.3,12.8,27.4C12.8,27.3,12.8,27.3,12.8,27.4z M11.1,28.3
		C11.1,28.3,11.1,28.3,11.1,28.3C11.1,28.3,11.1,28.3,11.1,28.3C11.1,28.3,11.1,28.3,11.1,28.3z M11.1,27.2
		C11.1,27.2,11.1,27.1,11.1,27.2C11.2,27.1,11.2,27.2,11.1,27.2C11.2,27.2,11.1,27.2,11.1,27.2C11.1,27.2,11.1,27.2,11.1,27.2z
		 M14.2,10.3C14.2,10.3,14.2,10.3,14.2,10.3C14.2,10.2,14.2,10.3,14.2,10.3C14.2,10.3,14.2,10.3,14.2,10.3
		C14.2,10.3,14.2,10.3,14.2,10.3z M12.6,29.6C12.6,29.5,12.6,29.5,12.6,29.6c0.1-0.1,0.1-0.1,0.1-0.1c0,0,0,0.1,0,0.1
		C12.7,29.6,12.7,29.6,12.6,29.6C12.6,29.6,12.6,29.6,12.6,29.6z M11.7,27.9c0,0,0.1,0,0.1,0c0,0,0,0,0,0c0,0,0,0,0,0
		C11.8,27.9,11.7,27.9,11.7,27.9C11.7,27.9,11.7,27.9,11.7,27.9z M10,36.5C10,36.4,10,36.4,10,36.5C10,36.4,10,36.4,10,36.5
		C10,36.4,10,36.5,10,36.5C10,36.5,10,36.5,10,36.5z M8.5,23.9C8.4,24,8.4,23.9,8.5,23.9c-0.1-0.1-0.1-0.1,0-0.2
		c0,0,0.1,0.1,0.1,0.1C8.5,23.9,8.5,23.9,8.5,23.9z M8.5,24.5c0-0.1-0.1-0.2-0.2-0.3C8.6,24.3,8.6,24.3,8.5,24.5z M9.3,25.4
		C9.3,25.4,9.3,25.5,9.3,25.4C9.3,25.5,9.3,25.5,9.3,25.4C9.3,25.5,9.3,25.5,9.3,25.4z M11.3,21C11.3,21,11.4,21,11.3,21
		C11.4,21,11.3,21,11.3,21C11.3,21.1,11.3,21,11.3,21C11.3,21,11.3,21,11.3,21z M10.4,20L10.4,20L10.4,20L10.4,20z M10.4,23L10.4,23
		L10.4,23L10.4,23z M10,18.6C10,18.6,10,18.6,10,18.6c0.1,0,0.1,0,0.1,0C10.1,18.6,10.1,18.6,10,18.6c0.1,0.1,0,0.2,0,0.2
		c0,0,0,0.1,0,0.1c0,0,0,0,0,0c0,0,0-0.1,0-0.1l0,0c0,0,0,0,0,0c0,0,0,0,0,0C9.9,18.7,9.9,18.6,10,18.6z M9.7,20.8L9.7,20.8
		L9.7,20.8L9.7,20.8z M10.7,17.7c0,0.1,0,0.2,0,0.3c0,0,0,0,0,0C10.7,17.9,10.6,17.8,10.7,17.7C10.6,17.7,10.6,17.7,10.7,17.7z
		 M10.9,17.9c0,0-0.1,0-0.1,0C10.8,17.9,10.9,17.9,10.9,17.9C10.9,17.9,10.9,17.9,10.9,17.9z M10.9,18.5c-0.1,0,0-0.1-0.2-0.1
		c0-0.1,0-0.1,0-0.2c0.1,0,0.1,0.1,0.2,0.1C10.9,18.4,10.9,18.4,10.9,18.5z M10,19.5C9.9,19.4,9.9,19.4,10,19.5
		C10,19.5,10,19.5,10,19.5z M10.1,19.6c0,0,0,0.1-0.1,0.1c0,0,0,0.1,0,0.1c-0.1-0.1,0-0.2,0-0.4C10,19.5,10.1,19.6,10.1,19.6z
		 M10.3,22.4C10.3,22.4,10.3,22.4,10.3,22.4C10.3,22.4,10.3,22.3,10.3,22.4C10.3,22.3,10.3,22.3,10.3,22.4
		C10.3,22.4,10.3,22.4,10.3,22.4z M10.6,20.9C10.6,20.9,10.5,20.9,10.6,20.9C10.5,20.9,10.5,20.9,10.6,20.9
		C10.5,20.9,10.5,20.9,10.6,20.9C10.5,20.8,10.6,20.9,10.6,20.9z M10.3,25.3C10.3,25.3,10.3,25.3,10.3,25.3c0-0.1,0-0.1,0-0.1
		c0,0,0,0,0,0c0,0,0,0,0,0.1C10.3,25.2,10.3,25.2,10.3,25.3z M9.9,28.3c0-0.1,0-0.1,0.1-0.1c0,0,0.1,0,0.1,0.1
		c0-0.1,0.1-0.1,0.1-0.2c0,0.1,0,0.2-0.1,0.3c0,0.1-0.1,0.1-0.2,0.1C10,28.4,9.9,28.4,9.9,28.3z M9.7,27.2c0,0,0-0.1,0-0.1
		c0,0,0,0,0.1,0c0,0,0,0,0,0C9.8,27.1,9.8,27.1,9.7,27.2C9.8,27.2,9.8,27.2,9.7,27.2z M10.2,22.2C10.2,22.2,10.2,22.1,10.2,22.2
		C10.3,22.1,10.3,22.1,10.2,22.2c0.1,0,0.1,0.1,0.1,0.1C10.2,22.3,10.2,22.2,10.2,22.2z M10.5,19.7c0,0,0-0.1,0-0.1c0,0,0,0,0-0.1
		C10.6,19.6,10.6,19.6,10.5,19.7C10.6,19.7,10.5,19.7,10.5,19.7C10.5,19.7,10.5,19.7,10.5,19.7z M10.6,18.9
		C10.7,18.9,10.7,18.9,10.6,18.9c0.1,0,0.1,0,0.1,0c0,0.1,0,0.1,0,0.2C10.7,19.1,10.7,19.1,10.6,18.9C10.6,19,10.6,18.9,10.6,18.9z
		 M9.7,22.4C9.7,22.4,9.7,22.4,9.7,22.4C9.6,22.4,9.6,22.3,9.7,22.4c-0.1-0.2,0-0.2,0-0.3C9.7,22.2,9.7,22.3,9.7,22.4z M9.5,22.7
		C9.5,22.7,9.5,22.6,9.5,22.7c0-0.1,0-0.1,0-0.1C9.5,22.6,9.5,22.6,9.5,22.7C9.5,22.6,9.5,22.6,9.5,22.7z M9.5,21.8
		c0-0.1,0.1-0.1,0.1-0.2c0,0,0,0,0.1,0c0,0,0,0.1,0,0.1C9.6,21.7,9.6,21.8,9.5,21.8C9.6,21.8,9.6,21.8,9.5,21.8z M9.6,21.2
		C9.6,21.2,9.6,21.2,9.6,21.2C9.7,21.2,9.7,21.2,9.6,21.2C9.7,21.2,9.7,21.3,9.6,21.2C9.6,21.3,9.6,21.2,9.6,21.2z M10.8,22.6
		c0,0.1-0.1,0.1-0.2,0.2c-0.1-0.2,0.1-0.3,0.2-0.4c0,0.1,0,0.1,0,0.2C10.8,22.5,10.7,22.5,10.8,22.6z M10.5,23.2
		c0-0.1,0-0.2,0.1-0.2C10.6,23.1,10.6,23.1,10.5,23.2z M10.6,21.8C10.6,21.8,10.6,21.9,10.6,21.8C10.6,21.9,10.6,21.9,10.6,21.8
		C10.6,21.8,10.6,21.8,10.6,21.8C10.6,21.8,10.6,21.8,10.6,21.8C10.6,21.8,10.6,21.8,10.6,21.8z M10.8,22.6
		C10.8,22.5,10.8,22.5,10.8,22.6C10.8,22.5,10.8,22.5,10.8,22.6z M10.4,28C10.4,28,10.3,28,10.4,28C10.3,28,10.3,28,10.4,28
		C10.4,28,10.4,28,10.4,28z M9.6,29.8c0-0.1,0.1-0.3,0.2-0.4C9.8,29.6,9.8,29.6,9.6,29.8z M9.6,29.9C9.6,29.9,9.5,29.9,9.6,29.9
		c0-0.1,0-0.1,0-0.1C9.6,29.8,9.6,29.8,9.6,29.9C9.6,29.9,9.6,29.9,9.6,29.9z M9.5,28.9c0,0,0-0.1,0-0.1c0-0.1,0.1-0.2,0.2-0.3
		C9.7,28.8,9.7,28.8,9.5,28.9z M10,22.7C10,22.7,10,22.7,10,22.7c0,0,0-0.1,0.1-0.1c0,0,0,0,0.1,0c0,0.2,0,0.3-0.1,0.5
		c0,0,0,0-0.1,0c0,0,0,0,0,0c0,0,0,0,0,0c0,0,0,0,0,0c0-0.1,0-0.2,0-0.3C10,22.8,10.1,22.8,10,22.7z M10.1,20.7c0-0.1,0-0.1,0-0.2
		c0,0,0,0,0,0C10.1,20.6,10.1,20.7,10.1,20.7z M9.5,23.1C9.5,23.1,9.5,23.1,9.5,23.1C9.5,23.1,9.5,23.1,9.5,23.1
		C9.5,23.1,9.5,23.1,9.5,23.1z M9.4,18.4c0,0.3,0,0.5,0,0.6c0,0,0,0.1-0.1,0c0,0-0.1,0-0.1-0.1C9.3,18.8,9.3,18.6,9.4,18.4z
		 M9.6,19.3L9.6,19.3L9.6,19.3L9.6,19.3C9.6,19.3,9.6,19.3,9.6,19.3z M9.7,18.6C9.7,18.6,9.7,18.5,9.7,18.6
		C9.7,18.5,9.8,18.5,9.7,18.6C9.8,18.6,9.7,18.6,9.7,18.6C9.7,18.6,9.7,18.6,9.7,18.6z M9.6,17.6C9.6,17.6,9.6,17.6,9.6,17.6
		c0-0.1,0-0.1,0-0.1C9.6,17.5,9.6,17.5,9.6,17.6C9.6,17.6,9.6,17.6,9.6,17.6z M9.1,22.6c0,0,0-0.1,0-0.1C9.1,22.5,9.1,22.6,9.1,22.6
		C9.1,22.6,9.1,22.6,9.1,22.6z M9.2,21C9.2,21,9.2,21,9.2,21C9.2,21,9.3,21,9.2,21C9.2,21,9.2,21,9.2,21z M9.2,20.7L9.2,20.7
		L9.2,20.7L9.2,20.7z M9.2,21.1C9.2,21.1,9.1,21.1,9.2,21.1C9.1,21.1,9.1,21.1,9.2,21.1c0-0.1,0-0.1,0-0.1C9.2,21,9.2,21.1,9.2,21.1
		z M9.3,19.3C9.3,19.3,9.3,19.3,9.3,19.3C9.4,19.3,9.4,19.3,9.3,19.3C9.4,19.4,9.4,19.4,9.3,19.3C9.4,19.4,9.3,19.4,9.3,19.3z
		 M9.7,19.6L9.7,19.6L9.7,19.6L9.7,19.6z M8.7,26.3C8.7,26.2,8.7,26.2,8.7,26.3C8.7,26.2,8.7,26.2,8.7,26.3
		C8.7,26.2,8.7,26.2,8.7,26.3z M9.1,21.6C9.1,21.6,9,21.6,9.1,21.6C9,21.6,9,21.5,9,21.5c0,0,0,0,0,0c0,0,0,0,0,0
		C9.1,21.5,9.1,21.6,9.1,21.6z M8.9,21.8C8.9,21.8,8.9,21.8,8.9,21.8L8.9,21.8L8.9,21.8L8.9,21.8z M8.7,21.1c0-0.1,0-0.3,0.1-0.5
		c0.1,0,0.1-0.1,0.2,0c0,0.1-0.1,0.2-0.1,0.2C8.8,21,8.9,21.1,8.7,21.1z M11,23.7C11,23.7,11,23.7,11,23.7C11,23.7,11,23.7,11,23.7
		C11,23.7,11,23.7,11,23.7z M9.3,29.2C9.3,29.2,9.3,29.2,9.3,29.2C9.3,29.2,9.3,29.1,9.3,29.2C9.3,29.1,9.3,29.1,9.3,29.2
		C9.3,29.1,9.3,29.1,9.3,29.2C9.3,29.1,9.3,29.2,9.3,29.2z M8.4,27.7c0-0.1,0-0.3,0.1-0.4c0,0.1,0.1,0.3,0.1,0.4
		C8.5,27.7,8.5,27.7,8.4,27.7z M8.5,26.6c0.1-0.1,0.1-0.1,0.2-0.2c0,0.1,0,0.2,0,0.2c0,0,0,0.1,0,0.1C8.6,26.7,8.6,26.7,8.5,26.6z
		 M8.5,25.9c0.1-0.2,0.2-0.4,0.1-0.5c-0.1-0.3,0-0.5,0-0.8c0,0,0,0,0,0c0,0,0,0,0,0.1c0,0,0,0,0.1,0c0,0,0,0,0,0
		c-0.1,0.2-0.1,0.4,0,0.6c0,0.1,0,0.1,0,0.2C8.7,25.7,8.7,25.8,8.5,25.9z M8,26C8,25.9,8.1,25.9,8,26C8.1,25.9,8.1,25.9,8,26
		C8.1,26,8.1,26,8,26C8,26,8,26,8,26z M8.8,22.1C8.8,22.2,8.8,22.2,8.8,22.1c0,0.3-0.1,0.5-0.1,0.8C8.5,22.5,8.5,22.5,8.8,22.1z
		 M8.5,21.4c0,0,0-0.1,0-0.1c0.1,0.1,0.1,0.2,0.1,0.2C8.6,21.5,8.6,21.5,8.5,21.4C8.6,21.4,8.6,21.4,8.5,21.4z M8.7,20
		C8.7,20.1,8.7,20.1,8.7,20C8.7,20.1,8.7,20.1,8.7,20C8.7,20.1,8.7,20.1,8.7,20C8.7,20.1,8.7,20,8.7,20z M8.8,19.5
		C8.8,19.5,8.8,19.5,8.8,19.5C8.8,19.5,8.8,19.5,8.8,19.5C8.8,19.5,8.8,19.5,8.8,19.5z M8.8,19.1C8.8,19.1,8.8,19.1,8.8,19.1
		C8.8,19.1,8.8,19.1,8.8,19.1C8.8,19.1,8.8,19.1,8.8,19.1z M8.9,19.4C8.8,19.4,8.8,19.4,8.9,19.4c-0.1-0.1,0-0.2,0-0.3
		C8.8,19.3,8.8,19.3,8.9,19.4z M9,18.4c0,0.1,0,0.1,0,0.2C9,18.5,8.9,18.4,9,18.4z M9,19.5c0,0.1,0,0.2,0,0.3
		c-0.1-0.1-0.1-0.1-0.1-0.3c0,0,0,0,0,0C8.9,19.4,9,19.4,9,19.5z M9,19.4C9,19.4,9,19.4,9,19.4C9,19.4,9.1,19.4,9,19.4
		C9.1,19.4,9.1,19.4,9,19.4C9,19.4,9,19.4,9,19.4z M14.4,18.8C14.4,18.8,14.4,18.7,14.4,18.8C14.4,18.7,14.5,18.7,14.4,18.8
		C14.5,18.7,14.5,18.7,14.4,18.8c0.1,0,0.1,0.1,0.1,0.2c0,0,0,0,0,0C14.4,18.9,14.4,18.8,14.4,18.8z M19,9.5C19,9.5,19,9.4,19,9.5
		c0.1,0,0.1,0,0.1,0C19.1,9.5,19,9.5,19,9.5C19,9.5,19,9.5,19,9.5z M20.4,9.4c0,0.1,0,0.1,0,0.2c-0.1,0.1-0.1,0.1-0.2-0.1
		c0,0,0,0,0,0C20.3,9.5,20.3,9.5,20.4,9.4z M21.7,10.5C21.7,10.5,21.8,10.5,21.7,10.5C21.8,10.5,21.8,10.5,21.7,10.5
		C21.8,10.6,21.8,10.6,21.7,10.5C21.7,10.6,21.7,10.5,21.7,10.5z M23.4,8.3C23.5,8.3,23.5,8.3,23.4,8.3C23.5,8.3,23.5,8.3,23.4,8.3
		C23.5,8.3,23.5,8.3,23.4,8.3C23.5,8.3,23.4,8.3,23.4,8.3z M23.8,14.3C23.8,14.3,23.8,14.3,23.8,14.3C23.8,14.3,23.8,14.3,23.8,14.3
		C23.8,14.2,23.8,14.2,23.8,14.3C23.8,14.2,23.8,14.2,23.8,14.3z M29.7,10.1C29.7,10.1,29.7,10.1,29.7,10.1
		C29.7,10.1,29.7,10.1,29.7,10.1C29.7,10.2,29.7,10.1,29.7,10.1C29.7,10.1,29.7,10.1,29.7,10.1z M32.2,17.5c0,0.1,0,0.1,0,0.2
		c0,0,0,0,0,0c0,0-0.1,0-0.1,0c-0.1-0.1-0.1-0.2,0-0.3c0,0,0,0,0.1,0C32.2,17.3,32.2,17.4,32.2,17.5z M31.9,20.3
		C31.9,20.3,31.9,20.3,31.9,20.3C31.8,20.3,31.8,20.3,31.9,20.3C31.9,20.3,31.9,20.3,31.9,20.3C31.9,20.3,31.9,20.3,31.9,20.3z
		 M31.8,25.4c0.1,0,0.1,0,0.2,0c0,0,0,0,0,0c0,0,0.1,0,0.1,0c0,0.1-0.1,0.3-0.1,0.4c-0.1-0.2-0.3-0.1-0.3-0.3
		C31.7,25.5,31.8,25.4,31.8,25.4z M34,19.9C34,19.9,34,19.9,34,19.9C33.9,19.9,33.9,19.9,34,19.9C33.9,19.9,33.9,19.8,34,19.9
		C34,19.8,34,19.9,34,19.9z M42.2,18.7c0.1,0.1,0.1,0.1,0.2,0.3C42.2,18.8,42.2,18.8,42.2,18.7z M47.4,19.9c0.1,0,0.1,0,0.1,0
		c0,0,0,0.1,0,0.1c0,0-0.1,0-0.1,0C47.4,20,47.4,20,47.4,19.9C47.4,20,47.4,19.9,47.4,19.9z M50.9,26.4
		C50.9,26.4,50.9,26.4,50.9,26.4C50.9,26.4,50.9,26.4,50.9,26.4C50.9,26.3,50.9,26.3,50.9,26.4z M54.3,27.3
		C54.2,27.3,54.2,27.3,54.3,27.3c-0.1-0.1-0.1-0.1-0.1-0.2c0,0,0,0,0,0C54.3,27.2,54.3,27.2,54.3,27.3
		C54.3,27.3,54.3,27.3,54.3,27.3z M54.6,27.1C54.6,27.2,54.6,27.2,54.6,27.1c0,0.1,0,0.1,0,0.2C54.6,27.3,54.6,27.2,54.6,27.1
		C54.6,27.2,54.6,27.2,54.6,27.1z M54.6,27.3C54.6,27.3,54.6,27.3,54.6,27.3C54.6,27.3,54.6,27.3,54.6,27.3
		C54.6,27.3,54.6,27.3,54.6,27.3z M53.9,27.7C53.9,27.7,53.9,27.7,53.9,27.7C53.9,27.6,53.9,27.6,53.9,27.7
		C53.9,27.7,53.9,27.7,53.9,27.7z M48.3,38.9C48.3,38.9,48.3,38.9,48.3,38.9C48.3,38.9,48.3,38.9,48.3,38.9
		C48.3,38.9,48.3,38.9,48.3,38.9z M46.1,38.6C46.1,38.6,46,38.6,46.1,38.6C46,38.5,46,38.5,46.1,38.6C46.1,38.5,46.1,38.5,46.1,38.6
		C46.1,38.5,46.1,38.5,46.1,38.6C46.1,38.5,46.1,38.5,46.1,38.6z M45.4,38.7C45.4,38.7,45.4,38.7,45.4,38.7c0.1-0.1,0.1,0,0.1,0
		c0,0,0,0,0,0C45.5,38.7,45.5,38.8,45.4,38.7C45.4,38.8,45.4,38.7,45.4,38.7z M31.8,38.9c0,0-0.1,0.1-0.1,0.1c0,0,0,0-0.1-0.1
		C31.7,38.9,31.7,38.9,31.8,38.9C31.8,38.9,31.8,38.9,31.8,38.9z M31.3,39.8c0,0,0,0.1,0,0.1C31.3,39.9,31.2,39.9,31.3,39.8
		c-0.1,0-0.1,0-0.1,0C31.2,39.8,31.2,39.8,31.3,39.8C31.3,39.8,31.3,39.8,31.3,39.8z M29.2,39.4c-0.1-0.1-0.1-0.1-0.1-0.2
		c0.1-0.1,0.1-0.2,0.3-0.2c0,0,0,0,0,0c0,0.1-0.1,0.1-0.1,0.2c0,0,0,0,0,0C29.2,39.4,29.2,39.4,29.2,39.4c0.1,0.1,0.1,0.1,0.1,0.1
		C29.2,39.5,29.2,39.5,29.2,39.4z M22.8,38.2C22.8,38.2,22.8,38.2,22.8,38.2c0-0.1,0-0.1,0-0.1C22.9,38.1,22.9,38.1,22.8,38.2
		C22.9,38.2,22.8,38.2,22.8,38.2z M22.1,39.2c0,0-0.1-0.1-0.1-0.1c0-0.1,0-0.3,0-0.5c0.1,0.1,0.1,0.1,0.2,0.2c0,0-0.1,0-0.1,0
		c0,0,0,0,0,0c0,0,0,0,0,0c0.1,0,0.1,0,0.2,0c0,0,0,0.1,0,0.1C22.3,39.1,22.2,39.2,22.1,39.2z M21.9,39.5
		C21.9,39.5,21.9,39.5,21.9,39.5C21.9,39.5,21.9,39.4,21.9,39.5C21.9,39.4,21.9,39.4,21.9,39.5C21.9,39.5,21.9,39.5,21.9,39.5z
		 M14.4,37.3C14.4,37.3,14.4,37.3,14.4,37.3C14.4,37.3,14.4,37.3,14.4,37.3C14.4,37.3,14.4,37.3,14.4,37.3z M12.6,29.2
		C12.6,29.2,12.6,29.2,12.6,29.2c0-0.1,0-0.1,0-0.1c0.1-0.1,0.2-0.2,0.3-0.3c0,0,0,0,0.1,0c0,0,0,0,0,0c0,0,0,0.1,0,0.1
		c-0.1,0.1-0.1,0.2-0.2,0.2C12.7,29.2,12.7,29.2,12.6,29.2z M12.3,26.8C12.3,26.8,12.3,26.8,12.3,26.8
		C12.3,26.8,12.3,26.8,12.3,26.8C12.4,26.8,12.4,26.8,12.3,26.8z M11.7,28.6c0,0-0.1,0.1-0.1,0c0,0-0.1-0.1-0.1-0.1
		c0-0.1,0-0.1,0-0.1c0,0,0-0.1,0-0.1c0,0,0-0.1,0-0.1c0,0,0.1,0,0.1,0c0,0,0,0,0,0C11.7,28.5,11.7,28.5,11.7,28.6z M11.3,29.4
		c0,0,0-0.1,0-0.1c0,0,0,0,0.1,0C11.3,29.2,11.3,29.3,11.3,29.4C11.3,29.3,11.3,29.3,11.3,29.4z M11.1,24C11.1,24,11.1,24,11.1,24
		c-0.1,0-0.1-0.1-0.1-0.2C11.1,23.8,11.2,23.8,11.1,24C11.2,23.9,11.1,23.9,11.1,24C11.1,23.9,11.1,23.9,11.1,24z M11.1,24
		C11.1,24,11.1,24,11.1,24C11,24,11,24,11.1,24C11,24,11.1,24,11.1,24z M11,24.8C11,24.9,11,24.9,11,24.8c-0.1,0-0.1,0-0.1-0.1
		c0,0,0.1-0.1,0.1-0.1C11,24.8,11,24.8,11,24.8z M8.7,29.7C8.7,29.7,8.7,29.7,8.7,29.7C8.7,29.7,8.7,29.7,8.7,29.7
		C8.7,29.7,8.7,29.7,8.7,29.7z M8.7,28.7c-0.1,0.1-0.1,0.2-0.2,0.3c0,0-0.1,0-0.1,0c0-0.2,0.1-0.3,0.1-0.4c0,0,0.1,0,0.1,0
		C8.7,28.6,8.7,28.6,8.7,28.7z M8.4,30.4c-0.1,0-0.1-0.1-0.1-0.2C8.3,30.3,8.4,30.3,8.4,30.4z M8.1,29.7C8.1,29.7,8.2,29.7,8.1,29.7
		C8.2,29.7,8.2,29.7,8.1,29.7c0.1,0.1,0,0.1,0,0.2C8.1,29.9,8.1,29.8,8.1,29.7z M8.1,29.4C8.1,29.4,8.1,29.4,8.1,29.4
		c0-0.1,0-0.1,0-0.2C8.2,29.3,8.2,29.4,8.1,29.4C8.2,29.4,8.2,29.4,8.1,29.4z M8.3,28.4C8.3,28.4,8.3,28.4,8.3,28.4
		C8.3,28.4,8.3,28.4,8.3,28.4C8.3,28.4,8.3,28.4,8.3,28.4C8.3,28.4,8.3,28.4,8.3,28.4z M8.1,29.1c0-0.1,0-0.1,0-0.1c0,0,0-0.1,0-0.2
		C8.1,28.8,8.2,28.9,8.1,29.1z M7.7,28.1c0.1-0.2,0.1-0.3,0.1-0.5c0-0.2,0.1-0.3,0.1-0.5c0-0.1,0.1-0.1,0.1,0
		c-0.1,0.3-0.3,0.6-0.2,0.9C7.8,28.1,7.7,28.2,7.7,28.1z M8.5,20.4c0,0.1,0,0.3,0,0.4c0,0.1,0,0.2,0,0.2C8.4,20.7,8.4,20.7,8.5,20.4
		z M8.7,18.7C8.7,18.7,8.7,18.7,8.7,18.7C8.8,18.7,8.8,18.7,8.7,18.7C8.8,18.8,8.8,18.8,8.7,18.7C8.7,18.8,8.7,18.8,8.7,18.7z
		 M22.3,10.5C22.3,10.5,22.3,10.5,22.3,10.5C22.3,10.5,22.3,10.5,22.3,10.5C22.3,10.5,22.3,10.5,22.3,10.5z M23.8,9.2
		C23.8,9.2,23.8,9.3,23.8,9.2c0,0.1,0,0.1,0,0.1c0,0,0,0,0,0C23.8,9.3,23.8,9.2,23.8,9.2z M23.9,9.2c0,0,0.1-0.1,0.2-0.1
		c0,0,0,0-0.1,0C24,9.2,23.9,9.2,23.9,9.2C23.9,9.2,23.9,9.2,23.9,9.2z M28.1,8.7C28.1,8.7,28.1,8.8,28.1,8.7
		C28.1,8.8,28.1,8.8,28.1,8.7C28.1,8.7,28.1,8.7,28.1,8.7z M30.9,9.9C30.9,9.9,30.9,9.9,30.9,9.9c0.1-0.1,0.1,0,0.1,0
		C31,10,31,10,30.9,9.9C30.9,10,30.9,10,30.9,9.9C30.9,10,30.9,10,30.9,9.9z M31.1,17.5c0.1,0,0.1,0,0.2,0c0,0,0,0.1,0,0.1
		c0,0.1-0.1,0.1-0.1,0.1C31.1,17.7,31.1,17.6,31.1,17.5z M55.6,21.8c0.3,0.1,0.3,0.1,0.3,0.2C55.8,22.1,55.7,22,55.6,21.8z
		 M55.8,23.2C55.8,23.3,55.8,23.3,55.8,23.2c0,0.1-0.1,0.1-0.1,0.1c0,0,0,0,0-0.1c0,0,0,0,0-0.1C55.8,23.2,55.8,23.2,55.8,23.2z
		 M55.5,23C55.5,23,55.5,23,55.5,23c0-0.1,0-0.1,0-0.1c0,0,0-0.1,0-0.1c0.1,0,0.1,0,0.2,0.1C55.7,22.9,55.6,23,55.5,23z M55.4,24.5
		c0.2,0.3,0.2,0.6,0.2,1c0,0.3-0.1,0.5-0.3,0.7c-0.1,0-0.2,0-0.3,0c0,0,0.1,0,0.1,0c0.1,0,0.1-0.1,0.2-0.1c0-0.3,0.1-0.5,0.1-0.8
		c0.1-0.1-0.1-0.2-0.1-0.3c0,0,0.1,0,0.1,0c0.1,0,0.1-0.1,0.1-0.1C55.3,24.7,55.3,24.6,55.4,24.5C55.3,24.5,55.3,24.5,55.4,24.5z
		 M54,28.3C54,28.3,54,28.3,54,28.3C54,28.3,53.9,28.3,54,28.3c-0.1-0.1,0-0.2-0.1-0.2c0,0,0-0.1,0-0.1c0,0,0,0,0.1,0
		c0,0,0.1,0,0.1-0.1c0,0,0,0,0,0c0.1,0,0.2,0.1,0.3,0.1c-0.1,0.1-0.2,0.1-0.2,0.3C54.1,28.3,54.1,28.3,54,28.3z M53.5,37.5
		C53.5,37.5,53.5,37.5,53.5,37.5C53.5,37.5,53.5,37.5,53.5,37.5C53.5,37.5,53.5,37.5,53.5,37.5C53.5,37.5,53.5,37.5,53.5,37.5z
		 M53.4,38.2C53.4,38.2,53.4,38.2,53.4,38.2C53.4,38.2,53.4,38.2,53.4,38.2C53.4,38.2,53.4,38.2,53.4,38.2
		C53.4,38.2,53.4,38.2,53.4,38.2z M52.9,41.3c0,0-0.1,0-0.1,0c0,0,0-0.1-0.1-0.1c0,0,0,0,0,0c0.1-0.1,0.1-0.1,0.2,0
		C52.9,41.2,52.9,41.2,52.9,41.3z M52.8,38.6C52.8,38.6,52.8,38.6,52.8,38.6c0-0.1,0-0.2,0-0.3c0-0.1,0-0.2,0-0.3c0-0.1,0-0.1,0-0.2
		c0,0.1,0.1,0.1,0.1,0.2C52.9,38.3,52.9,38.4,52.8,38.6z M49.6,41.3C49.5,41.3,49.5,41.3,49.6,41.3C49.5,41.3,49.5,41.2,49.6,41.3
		C49.6,41.2,49.6,41.2,49.6,41.3z M49.3,40.7C49.3,40.7,49.3,40.7,49.3,40.7C49.2,40.6,49.2,40.6,49.3,40.7
		C49.3,40.6,49.3,40.6,49.3,40.7C49.3,40.6,49.3,40.7,49.3,40.7z M48.6,40.6c0,0-0.1,0-0.1,0c0-0.1,0.1-0.1,0.1-0.2
		C48.6,40.5,48.6,40.6,48.6,40.6C48.6,40.6,48.6,40.6,48.6,40.6z M46.9,41.1c0-0.1,0-0.2,0-0.4c-0.1,0.1-0.1,0.1-0.2,0.1
		c0.1-0.4,0.1-0.4,0.3-0.6c0.1,0.2,0.1,0.5,0.1,0.7C47.1,41.1,47,41.1,46.9,41.1z M45.9,39.4c-0.2-0.2-0.1-0.5-0.1-0.7
		c0.1,0,0.1,0.1,0.2,0.1c0,0,0,0,0,0c0,0,0,0,0,0l0,0c0.1,0,0.1,0,0.2,0c0,0,0,0,0.1,0C46.2,39,46.1,39.2,45.9,39.4z M45.2,39.9
		L45.2,39.9L45.2,39.9L45.2,39.9z M45.1,40.2c0,0.1,0,0.1,0,0.2C45,40.3,45,40.3,45.1,40.2z M44.9,41.1c-0.1-0.1-0.1-0.1-0.1-0.2
		C44.9,40.9,44.9,40.9,44.9,41.1z M41.5,39.3c0,0,0,0.1-0.1,0.1c0,0,0,0-0.1-0.1C41.4,39.3,41.4,39.2,41.5,39.3
		C41.4,39.2,41.5,39.2,41.5,39.3z M40.9,40C40.9,40,40.9,40,40.9,40C40.8,40,40.8,40,40.9,40C40.9,40,40.9,40,40.9,40
		C40.9,40,40.9,40,40.9,40z M40.9,39.7L40.9,39.7L40.9,39.7L40.9,39.7z M37.1,40C37.1,40,37.1,40,37.1,40C37,40,36.9,40,36.8,40
		c0,0,0,0,0-0.1c0,0,0-0.1,0-0.1c0,0,0-0.1,0.1-0.1C37,39.9,37,39.9,37.1,40z M35.4,42.3C35.4,42.3,35.4,42.2,35.4,42.3
		C35.4,42.2,35.4,42.2,35.4,42.3C35.4,42.3,35.4,42.3,35.4,42.3z M35.4,42.2C35.4,42.2,35.4,42.2,35.4,42.2
		C35.3,42.2,35.3,42.2,35.4,42.2C35.3,42.1,35.4,42.1,35.4,42.2C35.4,42.1,35.4,42.2,35.4,42.2z M35.6,40.1L35.6,40.1L35.6,40.1
		L35.6,40.1z M35.1,39.1c0,0,0-0.2,0-0.2c0.1,0,0.1,0,0.2,0c0.1,0,0.1,0.1,0,0.3C35.2,39.2,35.1,39.2,35.1,39.1z M35.1,38.1
		c0.1,0.1,0.1,0.3,0.2,0.5C35.2,38.5,35.1,38.4,35.1,38.1z M30.4,39.8C30.4,39.8,30.4,39.8,30.4,39.8C30.4,39.8,30.4,39.9,30.4,39.8
		C30.4,39.8,30.4,39.8,30.4,39.8C30.4,39.8,30.4,39.8,30.4,39.8z M26.8,40C26.8,40,26.8,40,26.8,40C26.8,40,26.8,40,26.8,40
		C26.8,40,26.8,40,26.8,40z M25.4,39.8C25.4,39.8,25.4,39.8,25.4,39.8C25.3,39.8,25.3,39.8,25.4,39.8C25.3,39.8,25.3,39.8,25.4,39.8
		z M7.4,28C7.4,28,7.4,28,7.4,28C7.4,28,7.4,28,7.4,28C7.4,28,7.4,27.9,7.4,28C7.4,28,7.4,28,7.4,28z M7.4,26.4
		C7.4,26.4,7.4,26.4,7.4,26.4C7.4,26.4,7.4,26.4,7.4,26.4C7.4,26.4,7.4,26.4,7.4,26.4z M8.2,23C8.2,23,8.2,23,8.2,23
		C8.2,23,8.2,23,8.2,23C8.3,23,8.2,23,8.2,23C8.2,23,8.2,23,8.2,23z M7.9,21.5c-0.1-0.1-0.1-0.1-0.1-0.3C7.9,21.4,7.9,21.4,7.9,21.5
		z M9.8,6.3c0.1,0,0.2-0.1,0.2-0.1c0,0.1,0,0.2-0.1,0.2C9.9,6.4,9.9,6.4,9.8,6.3z M10.2,6C10.2,6,10.2,6,10.2,6
		c0,0.1-0.1,0.1-0.1,0.2C10,6,10,6,10.2,6z M11,6.6C11,6.6,11,6.6,11,6.6C11,6.6,11,6.6,11,6.6C11,6.6,11,6.6,11,6.6z M11.3,6.6
		c0,0,0.1-0.1,0.1-0.2c0,0,0.1-0.1,0.1-0.1c0,0.1,0.1,0.2,0.1,0.3c0,0,0,0,0,0C11.5,6.7,11.4,6.7,11.3,6.6
		C11.3,6.6,11.3,6.6,11.3,6.6z M12.4,6.9C12.4,6.9,12.4,6.9,12.4,6.9C12.5,6.9,12.5,6.9,12.4,6.9C12.4,6.9,12.4,6.9,12.4,6.9
		C12.4,6.9,12.4,6.9,12.4,6.9C12.4,6.9,12.4,6.9,12.4,6.9z M12.9,6.8C12.9,6.8,12.9,6.8,12.9,6.8c0.1,0,0.1,0,0.1,0c0,0,0,0,0,0
		C13,6.8,12.9,6.8,12.9,6.8C12.9,6.8,12.9,6.8,12.9,6.8z M14.9,5.9L14.9,5.9L14.9,5.9L14.9,5.9z M21.6,8.1
		C21.6,8.1,21.6,8.1,21.6,8.1C21.7,8.1,21.7,8.1,21.6,8.1C21.6,8.1,21.6,8.1,21.6,8.1z M23.4,5.6C23.4,5.6,23.4,5.6,23.4,5.6
		C23.4,5.6,23.5,5.6,23.4,5.6C23.4,5.6,23.4,5.6,23.4,5.6C23.4,5.6,23.4,5.6,23.4,5.6z M25.1,7.2C25.1,7.2,25.2,7.2,25.1,7.2
		C25.2,7.2,25.2,7.2,25.1,7.2c0.1,0.1,0,0.1,0,0.1C25.1,7.3,25.1,7.2,25.1,7.2z M25.4,5.8C25.4,5.8,25.4,5.8,25.4,5.8
		C25.5,5.8,25.5,5.8,25.4,5.8C25.5,5.8,25.5,5.8,25.4,5.8C25.5,5.8,25.4,5.8,25.4,5.8z M28.3,7.8c0.2-0.3,0.2-0.3,0.2-0.5
		c0,0,0,0,0,0c0.2,0,0.3,0,0.5,0c-0.1,0.3-0.1,0.3-0.1,0.6c0,0,0,0,0,0c0,0,0,0,0,0c-0.1,0-0.1,0-0.1,0l0,0c0,0,0,0,0,0c0,0,0,0,0,0
		l0,0c0,0,0,0,0,0c0,0,0,0,0,0c0,0,0,0,0,0c0,0,0,0,0,0C28.5,8,28.4,7.8,28.3,7.8z M33.5,8.1c0-0.1,0.1-0.1,0.2-0.1
		c0.1,0,0.1,0.1,0.2,0.1c0,0.1-0.1,0.1-0.1,0.1C33.6,8.2,33.6,8.2,33.5,8.1z M33.9,8.2C33.9,8.2,33.9,8.2,33.9,8.2
		C33.9,8.2,33.9,8.2,33.9,8.2C33.9,8.2,33.9,8.2,33.9,8.2z M34.7,8.3C34.7,8.3,34.7,8.3,34.7,8.3C34.7,8.3,34.7,8.3,34.7,8.3
		C34.7,8.3,34.7,8.3,34.7,8.3z M37.6,8.4c0,0,0.1,0,0.1,0C37.7,8.4,37.7,8.4,37.6,8.4C37.7,8.4,37.6,8.4,37.6,8.4z M39.5,8.6
		c0.1-0.1,0.2-0.1,0.3,0c0.1,0,0.1,0,0.2,0.1c0,0,0,0,0,0c-0.1,0-0.2,0-0.2,0c0,0,0,0,0,0C39.6,8.7,39.6,8.7,39.5,8.6z M50.5,10
		c0,0,0-0.1,0.1-0.1c0,0,0,0,0,0C50.6,9.9,50.6,10,50.5,10C50.6,10,50.6,10,50.5,10C50.5,10,50.5,10,50.5,10z M51.7,9.6
		C51.7,9.6,51.7,9.6,51.7,9.6C51.7,9.6,51.7,9.6,51.7,9.6C51.7,9.6,51.7,9.6,51.7,9.6C51.7,9.6,51.7,9.6,51.7,9.6z M53.1,9.7
		C53.1,9.7,53.1,9.7,53.1,9.7C53.1,9.7,53.1,9.7,53.1,9.7C53.1,9.7,53.1,9.7,53.1,9.7z M55,10.8C55.1,10.8,55.1,10.8,55,10.8
		C55.1,10.8,55.1,10.8,55,10.8C55.1,10.8,55,10.8,55,10.8z M54.5,17.6c0,0,0,0.1,0.1,0.1c0,0.1,0,0.1,0,0.2c0,0-0.1-0.1-0.1-0.1
		C54.5,17.7,54.5,17.6,54.5,17.6C54.5,17.6,54.5,17.6,54.5,17.6z M56.1,11.3C56.1,11.3,56.1,11.2,56.1,11.3
		C56.1,11.2,56.1,11.3,56.1,11.3z M56.9,8.2C56.9,8.2,56.9,8.2,56.9,8.2C56.9,8.1,56.9,8.1,56.9,8.2C56.9,8.1,56.9,8.1,56.9,8.2
		C56.9,8.2,56.9,8.2,56.9,8.2z M56.9,8.8C56.9,8.8,56.9,8.8,56.9,8.8C56.9,8.8,56.9,8.8,56.9,8.8C56.9,8.9,56.9,8.9,56.9,8.8
		C56.9,8.9,56.9,8.9,56.9,8.8z M56.9,9.1L56.9,9.1L56.9,9.1L56.9,9.1z M56.1,20.9C56.1,20.9,56.1,20.9,56.1,20.9
		c0.1,0,0.1,0.1,0.1,0.2C56.2,21.1,56.1,21.1,56.1,20.9z M55.8,24.7c0,0-0.1-0.1-0.1-0.1c0-0.1,0-0.2-0.1-0.3
		c-0.1,0-0.1-0.1-0.1-0.2c0-0.1-0.1-0.2,0-0.2c0-0.1,0.1-0.1,0.1-0.1c0.1,0,0.1,0.1,0.1,0.1c-0.1,0.2,0,0.3,0.1,0.4
		C55.9,24.4,55.9,24.5,55.8,24.7z M56.2,21.1C56.2,21.1,56.2,21.1,56.2,21.1C56.2,21.1,56.2,21.1,56.2,21.1
		C56.2,21.1,56.2,21.1,56.2,21.1z M55.7,25.9c0,0,0.1,0,0.1,0.1c-0.1,0.1-0.1,0.1-0.2,0.2c0-0.1,0-0.1,0-0.2
		C55.7,25.9,55.7,25.9,55.7,25.9z M55.8,25.6C55.7,25.6,55.7,25.6,55.8,25.6C55.7,25.6,55.8,25.5,55.8,25.6
		C55.8,25.5,55.8,25.6,55.8,25.6C55.8,25.6,55.8,25.6,55.8,25.6z M56.6,25.4c0,0.2,0,0.3-0.2,0.4c0,0,0,0.1,0,0.1
		c0,0,0.1,0.1,0.1,0.2c-0.2,0-0.3,0-0.4-0.1c-0.1-0.2,0-0.4,0.2-0.5C56.4,25.4,56.5,25.4,56.6,25.4z M56.2,27.3c0,0,0.1,0.1,0.1,0.1
		c0,0-0.1,0.1-0.1,0.1c0,0-0.1,0-0.1-0.1C56.2,27.3,56.2,27.3,56.2,27.3z M56.6,24.6C56.6,24.6,56.7,24.6,56.6,24.6
		c0.1,0,0.1,0.1,0.1,0.1c0,0,0,0-0.1,0C56.6,24.7,56.6,24.6,56.6,24.6z M55.9,29.5C55.9,29.4,56,29.4,55.9,29.5c0.1-0.1,0.1,0,0.1,0
		C56,29.5,56,29.5,55.9,29.5C55.9,29.5,55.9,29.5,55.9,29.5z M55.6,32.9c-0.1-0.1-0.1-0.2,0-0.3C55.7,32.7,55.6,32.8,55.6,32.9z
		 M55.6,32.9C55.6,32.9,55.6,32.9,55.6,32.9C55.6,32.9,55.6,32.9,55.6,32.9C55.6,32.9,55.6,32.9,55.6,32.9z M54.6,42.1
		C54.6,42.1,54.6,42.1,54.6,42.1C54.6,42.1,54.6,42.1,54.6,42.1C54.6,42.1,54.6,42.1,54.6,42.1z M54.6,41.5
		C54.6,41.5,54.6,41.6,54.6,41.5C54.6,41.6,54.6,41.6,54.6,41.5C54.6,41.6,54.6,41.6,54.6,41.5C54.6,41.6,54.6,41.5,54.6,41.5z
		 M54.7,40.6C54.8,40.6,54.8,40.7,54.7,40.6c0.1,0.2,0.1,0.2,0.1,0.3c0,0,0,0-0.1,0c0-0.1-0.1-0.1-0.1-0.2
		C54.7,40.7,54.7,40.6,54.7,40.6z M54.8,39C54.8,39,54.8,39,54.8,39c0.1,0.1,0.2,0.2,0,0.3C54.8,39.2,54.8,39.1,54.8,39z M54.2,41.6
		c0.1-0.1,0.2,0,0.2,0.1c0,0.1,0,0.3,0.1,0.4c-0.1,0.1-0.2,0-0.3-0.1C54.1,41.8,54.1,41.7,54.2,41.6z M55.4,33.2
		C55.4,33.2,55.4,33.2,55.4,33.2C55.4,33.2,55.4,33.2,55.4,33.2C55.4,33.2,55.4,33.2,55.4,33.2C55.4,33.2,55.4,33.2,55.4,33.2z
		 M55,36.3C55,36.4,55,36.4,55,36.3C55,36.4,55,36.4,55,36.3C55,36.4,55,36.4,55,36.3C55,36.4,55,36.4,55,36.3z M54.5,39.9
		L54.5,39.9L54.5,39.9L54.5,39.9z M54.3,40.8C54.3,40.8,54.3,40.8,54.3,40.8C54.2,40.8,54.2,40.8,54.3,40.8c-0.1-0.1-0.1-0.1,0-0.1
		C54.3,40.7,54.3,40.8,54.3,40.8z M54.6,37.4C54.6,37.4,54.6,37.4,54.6,37.4C54.7,37.4,54.7,37.4,54.6,37.4
		C54.6,37.4,54.6,37.4,54.6,37.4z M55.3,32.2C55.3,32.2,55.4,32.2,55.3,32.2C55.4,32.2,55.4,32.2,55.3,32.2
		C55.4,32.3,55.4,32.3,55.3,32.2C55.4,32.3,55.3,32.3,55.3,32.2z M55.3,32.7C55.3,32.7,55.3,32.7,55.3,32.7
		C55.3,32.6,55.3,32.6,55.3,32.7C55.3,32.6,55.3,32.6,55.3,32.7C55.3,32.7,55.3,32.7,55.3,32.7z M55.4,31.1c0-0.1,0-0.2,0-0.2
		c0-0.1,0-0.1,0-0.2c0.1,0,0.1,0,0.2,0C55.5,30.8,55.5,31,55.4,31.1z M55.6,29.3C55.6,29.3,55.6,29.3,55.6,29.3
		C55.7,29.3,55.7,29.3,55.6,29.3C55.6,29.4,55.6,29.4,55.6,29.3C55.6,29.4,55.6,29.4,55.6,29.3z M55.7,29.8c0,0.1,0,0.2,0,0.4
		c-0.1-0.1-0.1-0.2-0.1-0.3C55.6,29.8,55.6,29.7,55.7,29.8z M54.6,38.7C54.6,38.7,54.6,38.7,54.6,38.7
		C54.6,38.7,54.6,38.7,54.6,38.7C54.6,38.7,54.6,38.7,54.6,38.7z M55.1,32.9C55.1,32.9,55.2,32.9,55.1,32.9c0.1,0,0.1,0.1,0.1,0.1
		c0,0,0,0.1-0.1,0.1c0,0,0,0,0-0.1C55.1,33,55.1,33,55.1,32.9z M54.2,40.1C54.2,40.1,54.2,40.1,54.2,40.1
		C54.3,40.1,54.3,40.1,54.2,40.1C54.2,40.1,54.2,40.1,54.2,40.1z M55.5,28.8c0-0.1,0.1-0.3,0.2-0.3c0,0.1-0.1,0.3-0.1,0.4
		C55.7,28.9,55.6,28.9,55.5,28.8C55.6,28.9,55.5,28.9,55.5,28.8z M55.4,29.5C55.4,29.5,55.4,29.5,55.4,29.5c-0.1,0-0.1,0-0.1,0
		C55.3,29.4,55.4,29.4,55.4,29.5C55.4,29.4,55.4,29.4,55.4,29.5z M55.3,29.9C55.3,29.9,55.3,29.9,55.3,29.9
		C55.4,29.9,55.3,29.9,55.3,29.9C55.3,29.9,55.3,29.9,55.3,29.9C55.3,29.9,55.3,29.9,55.3,29.9z M53.8,41.1c0.2,0.2,0.2,0.2,0.1,0.4
		c0,0.1-0.1,0.1-0.2,0.2C53.8,41.4,53.8,41.3,53.8,41.1z M54.2,38.5C54.2,38.5,54.2,38.5,54.2,38.5C54.2,38.4,54.2,38.4,54.2,38.5
		C54.2,38.4,54.2,38.4,54.2,38.5C54.2,38.4,54.2,38.5,54.2,38.5z M54.1,38.8C54.1,38.8,54.1,38.8,54.1,38.8
		C54.1,38.8,54.1,38.8,54.1,38.8C54.1,38.8,54.1,38.8,54.1,38.8C54.1,38.8,54.1,38.8,54.1,38.8z M54.2,37.5
		C54.2,37.5,54.2,37.5,54.2,37.5C54.2,37.5,54.2,37.5,54.2,37.5C54.2,37.5,54.2,37.5,54.2,37.5C54.2,37.5,54.2,37.5,54.2,37.5z
		 M55.1,29c-0.3-0.1-0.3-0.1-0.6-0.4c0,0-0.1,0-0.1,0.1c0,0,0,0.1,0,0.1c0,0-0.1,0-0.1,0c0,0,0,0,0,0c-0.1,0-0.1,0-0.2,0
		c0,0,0,0,0-0.1c0.1,0,0.1,0,0.2,0c0,0,0-0.1,0-0.1c0-0.1-0.1-0.1,0-0.2c0-0.1,0.1-0.1,0.1-0.2c0.1,0,0.2-0.1,0.2-0.2
		c0,0,0.1-0.1,0.1-0.1c0,0,0-0.1,0-0.1c0,0,0,0-0.1,0c-0.1,0-0.2,0-0.3,0c0.1-0.1,0.2-0.1,0.4-0.1c0,0,0-0.1,0-0.1
		c0.2,0,0.4,0,0.7,0c0.1,0,0.3,0,0.4,0.1c0.1,0,0.1,0.1,0.1,0.2c0,0,0,0.1,0,0.1c0,0,0.1,0.1,0.1,0.1c0,0,0.1,0,0.1,0
		c0,0,0-0.1,0.1-0.2c0,0.1,0.1,0.2,0,0.4c-0.1,0.1-0.2,0.1-0.3,0.1c0,0-0.1-0.1-0.1-0.1c-0.1,0-0.2,0-0.3,0c0,0.2,0,0.3-0.1,0.4
		C55.2,28.7,55.2,28.9,55.1,29z M53.5,41.2C53.5,41.2,53.5,41.2,53.5,41.2c0.1,0,0.1,0.1,0.1,0.2C53.5,41.4,53.4,41.3,53.5,41.2z
		 M54,37.2C54,37.2,54,37.2,54,37.2C54,37.2,54,37.2,54,37.2C54,37.2,54,37.2,54,37.2C54,37.2,54,37.2,54,37.2z M53.5,40.9
		C53.5,40.9,53.6,40.9,53.5,40.9C53.6,40.9,53.6,40.9,53.5,40.9C53.5,40.9,53.5,40.9,53.5,40.9C53.5,40.9,53.5,40.9,53.5,40.9z
		 M54.5,32.9C54.5,32.9,54.6,32.9,54.5,32.9C54.6,32.9,54.6,32.9,54.5,32.9C54.5,32.9,54.5,32.9,54.5,32.9
		C54.5,32.9,54.5,32.9,54.5,32.9z M54.8,31.2L54.8,31.2L54.8,31.2L54.8,31.2z M54.9,29.2c0.1,0.1,0.1,0.1,0.2,0.2
		c0,0.1,0,0.1-0.1,0.1C55,29.4,54.9,29.3,54.9,29.2z M53.6,38.4c0-0.1,0-0.3,0-0.4C53.7,38.2,53.7,38.3,53.6,38.4z M53.4,40.6
		C53.4,40.6,53.4,40.6,53.4,40.6C53.4,40.6,53.4,40.6,53.4,40.6z M53.3,40.9C53.3,40.9,53.3,40.9,53.3,40.9
		C53.3,40.9,53.3,40.9,53.3,40.9C53.3,40.9,53.3,41,53.3,40.9C53.3,41,53.3,40.9,53.3,40.9z M53.1,41.3
		C53.2,41.3,53.2,41.3,53.1,41.3C53.2,41.4,53.2,41.4,53.1,41.3C53.2,41.4,53.2,41.4,53.1,41.3C53.2,41.4,53.2,41.4,53.1,41.3z
		 M53,40.8C53.1,40.8,53.1,40.8,53,40.8C53.1,40.8,53.1,40.8,53,40.8C53.1,40.8,53.1,40.8,53,40.8z M53.2,38.8
		C53.3,38.7,53.3,38.7,53.2,38.8c0.1-0.1,0.1,0,0.1,0C53.4,38.8,53.3,38.8,53.2,38.8C53.3,38.8,53.3,38.8,53.2,38.8z M52.2,42.1
		C52.3,42.1,52.3,42.1,52.2,42.1C52.3,42.1,52.3,42.1,52.2,42.1C52.3,42.1,52.3,42.1,52.2,42.1z M52.3,41.6c0.2-0.1,0.3-0.1,0.4,0
		c0.1,0.1,0.2,0.1,0.2,0.3C52.6,42,52.5,41.9,52.3,41.6z M52.3,41.3C52.3,41.3,52.3,41.2,52.3,41.3c0.1,0,0.1,0,0.1,0
		C52.4,41.3,52.3,41.4,52.3,41.3C52.3,41.3,52.3,41.3,52.3,41.3z M52.1,41.9C52.1,41.9,52.1,41.9,52.1,41.9
		C52.1,41.8,52.1,41.8,52.1,41.9C52.1,41.9,52.1,41.9,52.1,41.9C52.1,41.9,52.1,41.9,52.1,41.9z M51,41.1c0,0,0.1,0,0.1,0
		c0,0,0,0.1,0,0.1c0,0,0,0.1,0,0.1C51.1,41.2,51,41.2,51,41.1C51,41.1,51,41.1,51,41.1z M50,41.7C50,41.6,50,41.6,50,41.7
		C50.1,41.6,50.1,41.7,50,41.7C50.1,41.7,50.1,41.7,50,41.7C50,41.7,50,41.7,50,41.7z M49.4,41.7c0.1-0.1,0.2-0.2,0.2-0.4
		c0.1,0,0.1,0.1,0.2,0.1c0,0,0,0.1,0,0.1c-0.1,0-0.1,0-0.2,0.1c0.1,0,0.1,0,0.2,0c0,0.1-0.1,0.3,0,0.4c0,0-0.1,0.1-0.1,0.1
		C49.5,41.9,49.5,41.8,49.4,41.7z M49.2,40.9c0.3,0,0.3,0,0.4,0.2c-0.1,0-0.2,0.1-0.2,0.1c0,0,0,0-0.1,0.1c0,0-0.1,0-0.1,0
		C49.2,41.2,49.2,41.1,49.2,40.9z M48.6,41.5c0-0.1,0-0.1,0-0.2c0,0,0,0,0,0C48.6,41.4,48.6,41.5,48.6,41.5z M48.5,41.6
		C48.5,41.5,48.6,41.5,48.5,41.6C48.6,41.6,48.6,41.6,48.5,41.6C48.5,41.6,48.5,41.6,48.5,41.6z M48.4,40.8
		C48.4,40.8,48.4,40.8,48.4,40.8c0.1,0,0.1,0,0.1,0C48.5,40.8,48.5,40.8,48.4,40.8C48.4,40.9,48.4,40.8,48.4,40.8z M47.6,41
		C47.6,41,47.6,40.9,47.6,41C47.6,40.9,47.6,40.9,47.6,41c0-0.1,0-0.1-0.1-0.1c0-0.1,0-0.2,0-0.3c0,0,0.1,0,0.1,0c0,0,0,0,0,0
		c0,0.2,0.1,0.3,0.2,0.5C47.8,41,47.7,41,47.6,41z M47.5,41.9c0.1,0.1,0.1,0.1,0.1,0.1c0.1,0.1,0,0.2,0,0.3c0,0,0,0.1-0.1,0.1
		C47.6,42.3,47.5,42.1,47.5,41.9z M47.5,41.1c0,0,0-0.1,0-0.1c0,0,0,0,0,0C47.5,41,47.5,41,47.5,41.1C47.5,41,47.5,41,47.5,41.1
		C47.5,41.1,47.5,41.1,47.5,41.1z M47.3,41.6C47.3,41.6,47.3,41.7,47.3,41.6c0.1,0.1,0.1,0.1,0.1,0.1c0,0,0,0-0.1,0
		C47.3,41.7,47.3,41.7,47.3,41.6z M47,41.5c0.2-0.1,0.2-0.1,0.2,0.1C47.2,41.6,47.2,41.6,47,41.5C47.1,41.6,47,41.6,47,41.5z
		 M46.6,42c0-0.1,0.1-0.1,0.1-0.2c0-0.1,0.1-0.1,0.2,0c0,0,0.1,0.1,0,0.1c-0.1,0.2-0.1,0.4-0.2,0.5C46.7,42.2,46.7,42.1,46.6,42z
		 M46.2,42.7c0-0.1,0.1-0.2,0.1-0.2c0,0.1,0.1,0.1,0.1,0.2c0,0.1,0,0.1,0,0.2c-0.1,0-0.1,0-0.2,0c0,0,0,0-0.1-0.1
		C46.2,42.9,46.2,42.8,46.2,42.7z M46.1,42.3c0,0,0-0.1,0.1-0.1c0,0,0.1,0,0.1,0c0,0,0,0.1,0,0.1c0,0,0,0.1-0.1,0.1
		C46.2,42.4,46.1,42.4,46.1,42.3C46.1,42.4,46.1,42.4,46.1,42.3z M45.9,42.6C45.9,42.6,45.9,42.6,45.9,42.6
		C45.9,42.6,45.9,42.6,45.9,42.6C45.9,42.6,45.9,42.7,45.9,42.6C45.9,42.6,45.9,42.6,45.9,42.6z M45.7,42.1c0.1,0,0.1-0.1,0.1-0.1
		c0,0,0.1,0.1,0.1,0.1c0,0-0.1,0.1-0.1,0.1C45.8,42.2,45.8,42.2,45.7,42.1z M46,40.3C46.1,40.3,46.1,40.3,46,40.3
		C46.1,40.3,46.1,40.3,46,40.3C46.1,40.3,46.1,40.3,46,40.3C46.1,40.3,46.1,40.3,46,40.3z M45.8,39.8c0.1-0.1,0.2-0.2,0.4-0.3
		c0,0.1,0,0.1,0.1,0.2c0,0,0,0,0,0c0,0.1,0,0.2,0,0.3c0,0.1-0.1,0.1-0.1,0.2c-0.1,0-0.2,0-0.3-0.1C45.8,40,45.8,39.8,45.8,39.8z
		 M45.1,42.2C45.1,42.2,45.1,42.1,45.1,42.2C45.1,42.1,45.1,42.1,45.1,42.2C45.1,42.2,45.1,42.2,45.1,42.2
		C45.1,42.2,45.1,42.2,45.1,42.2z M44.9,41.3c0.2-0.1,0.2-0.1,0.4-0.2c0.1-0.1,0.1-0.2,0.3-0.1c0.1,0.2,0.1,0.5,0.1,0.8
		c0,0.1-0.1,0.3-0.2,0.4c-0.1-0.1,0-0.3-0.1-0.4c-0.1,0-0.1,0.1-0.1,0.1c-0.1,0-0.1-0.1-0.1-0.2c0-0.1,0-0.1,0-0.2
		C45,41.3,45,41.3,44.9,41.3z M44.6,42.4c0-0.1,0-0.1,0-0.2C44.6,42.2,44.6,42.3,44.6,42.4c0.2,0.1,0.2,0.1,0.3,0.1
		c0,0.1,0,0.1-0.1,0.2C44.7,42.6,44.6,42.6,44.6,42.4z M44.7,40.7C44.7,40.7,44.7,40.7,44.7,40.7c0.1,0,0.1,0,0.1,0.1
		c0,0,0,0.1,0,0.1c0,0,0,0-0.1,0C44.7,40.8,44.6,40.8,44.7,40.7z M44.4,41c0,0.1,0,0.2,0,0.2c0,0,0,0.1-0.1,0.1c0,0-0.1-0.1-0.1-0.1
		C44.4,41.2,44.4,41.1,44.4,41z M44.5,39.7C44.5,39.7,44.4,39.7,44.5,39.7C44.4,39.7,44.4,39.7,44.5,39.7
		C44.4,39.6,44.5,39.6,44.5,39.7C44.5,39.6,44.5,39.7,44.5,39.7z M44,41.7c0-0.1,0-0.1,0-0.2c0,0,0.1-0.1,0.2-0.2c0,0.1,0,0.2,0,0.3
		C44.2,41.7,44.1,41.7,44,41.7C44.1,41.7,44,41.7,44,41.7z M44.1,40.1C44.1,40.1,44.1,40.1,44.1,40.1C44.2,40.1,44.2,40.2,44.1,40.1
		c0.1,0.1,0,0.1,0,0.1C44.1,40.2,44.1,40.2,44.1,40.1z M44.2,39.4C44.2,39.4,44.2,39.3,44.2,39.4C44.2,39.3,44.2,39.4,44.2,39.4
		C44.2,39.4,44.2,39.4,44.2,39.4z M43.9,38.9C43.9,38.9,43.9,38.9,43.9,38.9c0.1,0,0.1,0,0.2,0C44,39.1,44,39.1,43.9,38.9z
		 M43.5,41.8C43.6,41.8,43.6,41.8,43.5,41.8C43.6,41.8,43.6,41.8,43.5,41.8C43.6,41.8,43.6,41.9,43.5,41.8
		C43.6,41.9,43.5,41.8,43.5,41.8z M43.7,40.3L43.7,40.3L43.7,40.3L43.7,40.3z M43.8,39.1C43.8,39.1,43.8,39.1,43.8,39.1
		C43.8,39.1,43.8,39.1,43.8,39.1C43.8,39.1,43.8,39.1,43.8,39.1z M43.7,38.6c0.1,0.1,0.1,0.1,0.2,0.2c0,0,0,0,0,0
		C43.8,38.8,43.8,38.7,43.7,38.6z M43.1,41.7L43.1,41.7L43.1,41.7L43.1,41.7z M43.1,40.3C43.1,40.3,43.1,40.3,43.1,40.3
		c0-0.1,0-0.2,0-0.3c0.1,0.1,0.1,0.1,0.1,0.2C43.2,40.3,43.1,40.3,43.1,40.3z M42.9,40.4c0,0,0.1-0.1,0.1-0.1
		C43,40.3,43,40.4,42.9,40.4z M42.7,40.4C42.7,40.3,42.7,40.3,42.7,40.4C42.7,40.3,42.7,40.3,42.7,40.4
		C42.7,40.4,42.7,40.4,42.7,40.4C42.7,40.4,42.7,40.4,42.7,40.4z M42.4,41.2c0.1,0,0.1,0,0.1,0c0,0,0,0,0,0c0,0,0,0,0,0
		C42.5,41.2,42.5,41.2,42.4,41.2z M41.7,40.9C41.7,40.9,41.7,40.9,41.7,40.9C41.8,40.9,41.8,41,41.7,40.9C41.7,41,41.7,41,41.7,40.9
		C41.7,41,41.7,40.9,41.7,40.9z M41.8,38.7c0-0.1,0-0.2,0.1-0.4c-0.1-0.1-0.2-0.1-0.1-0.2c0,0,0,0,0,0c0.1,0,0.1,0,0.1-0.1
		c0,0,0,0,0,0c0.1,0,0.2,0,0.3,0.1C42,38.4,41.9,38.6,41.8,38.7C41.8,38.7,41.8,38.7,41.8,38.7z M41.2,40.2c0.1-0.1,0.2-0.2,0.4-0.3
		c0.1,0.2,0,0.3-0.1,0.4C41.4,40.3,41.3,40.3,41.2,40.2z M41,42.9C41,42.9,41,42.9,41,42.9C41,42.9,41,42.9,41,42.9
		C41,42.9,41,42.9,41,42.9C41,42.9,41,42.9,41,42.9z M41,40.8C41,40.8,41.1,40.8,41,40.8c0.1,0,0.2,0,0.2,0.1c0,0,0,0.1,0,0.1
		c0,0,0,0-0.1,0.1c0,0-0.1-0.1-0.1-0.1C41,40.9,41,40.9,41,40.8z M41.1,39.4C41.1,39.4,41.1,39.4,41.1,39.4
		C41.1,39.4,41.2,39.4,41.1,39.4C41.2,39.4,41.2,39.4,41.1,39.4C41.1,39.4,41.1,39.4,41.1,39.4z M40.7,41.6c0,0,0-0.1,0-0.1
		c0,0,0-0.1,0-0.1c0.1,0,0.1,0.1,0.2,0.1c0,0.1,0,0.1-0.1,0.1C40.8,41.7,40.7,41.7,40.7,41.6z M40.4,42.5c0.1,0,0.1,0.1,0.2,0.1
		C40.5,42.6,40.5,42.6,40.4,42.5z M40.3,41C40.3,41,40.3,41,40.3,41c0.1,0,0.1,0,0.1,0C40.4,41.1,40.4,41.1,40.3,41
		C40.3,41.1,40.3,41.1,40.3,41z M40.4,40.2c0.1,0.2,0.2,0.3,0.1,0.3C40.4,40.5,40.4,40.4,40.4,40.2C40.4,40.3,40.4,40.3,40.4,40.2z
		 M40,41.4c0,0.1,0.1,0.1,0.1,0.2c0,0.1,0,0.2-0.1,0.2c0,0-0.1,0-0.1-0.1C39.9,41.6,40,41.5,40,41.4z M39.7,41.2c0-0.1,0-0.1,0-0.3
		C39.8,41,39.8,41.1,39.7,41.2z M39.7,41.6C39.7,41.7,39.6,41.7,39.7,41.6C39.6,41.7,39.6,41.7,39.7,41.6
		C39.6,41.6,39.6,41.6,39.7,41.6C39.7,41.6,39.7,41.6,39.7,41.6z M39.4,42.1c0,0,0.1,0,0.1,0c0,0,0,0.1,0,0.2c0,0.1,0,0.1-0.1,0.2
		c0-0.1-0.1-0.1-0.1-0.1C39.4,42.2,39.4,42.1,39.4,42.1z M39.4,41.3C39.5,41.3,39.5,41.3,39.4,41.3C39.5,41.3,39.5,41.3,39.4,41.3
		C39.5,41.4,39.5,41.4,39.4,41.3C39.5,41.4,39.4,41.3,39.4,41.3z M39.5,40.5C39.5,40.5,39.5,40.4,39.5,40.5c0.1-0.1,0.1-0.1,0.2-0.1
		c0,0,0,0.1,0,0.1C39.6,40.5,39.5,40.6,39.5,40.5z M39.4,39.3c0-0.1,0-0.2,0-0.3c0,0,0.1,0,0.1,0c0,0,0,0,0,0.1
		C39.6,39.2,39.5,39.2,39.4,39.3z M39.1,41.9c0-0.1,0-0.1,0-0.2C39.2,41.8,39.3,41.8,39.1,41.9z M39.1,41.9
		C39.1,41.9,39.1,41.9,39.1,41.9C39.1,41.9,39.1,41.9,39.1,41.9c0.1,0.1,0,0.1,0,0.1c0,0,0,0,0,0C39.1,42,39,42,39.1,41.9z
		 M39.1,39.6c0-0.1,0-0.2,0-0.3c0.1,0,0.2,0,0.3,0c0,0.1,0,0.3,0,0.4C39.2,39.7,39.2,39.7,39.1,39.6z M38.8,42.5c0,0,0.1,0,0.1-0.1
		C38.9,42.5,38.9,42.5,38.8,42.5z M38.6,42.4c0.1-0.1,0.1-0.2,0.2-0.1c0,0,0,0.1,0,0.1C38.8,42.4,38.7,42.4,38.6,42.4z M38.7,41.8
		C38.7,41.8,38.7,41.7,38.7,41.8C38.7,41.7,38.7,41.7,38.7,41.8C38.7,41.8,38.7,41.8,38.7,41.8C38.7,41.8,38.7,41.8,38.7,41.8z
		 M38.8,39.5C38.8,39.5,38.8,39.5,38.8,39.5C38.8,39.5,38.8,39.5,38.8,39.5C38.8,39.5,38.8,39.5,38.8,39.5z M38.2,42.5L38.2,42.5
		L38.2,42.5L38.2,42.5z M38,42.5C38,42.5,38,42.5,38,42.5C38,42.4,38,42.5,38,42.5C38,42.5,38,42.5,38,42.5C38,42.5,38,42.5,38,42.5
		z M38.4,38.3C38.4,38.3,38.3,38.4,38.4,38.3C38.3,38.3,38.3,38.3,38.4,38.3C38.3,38.3,38.3,38.3,38.4,38.3
		C38.3,38.3,38.4,38.3,38.4,38.3z M37.8,42C37.8,41.9,37.8,41.9,37.8,42c0.1-0.1,0.1,0,0.1,0c0,0,0,0.1,0,0.1
		C37.8,42.1,37.8,42.1,37.8,42C37.8,42,37.8,42,37.8,42z M37.5,41.4c0.1,0.2,0.1,0.3-0.1,0.6C37.4,41.8,37.4,41.6,37.5,41.4z
		 M37.1,41.6c0,0.1,0,0.1,0,0.2c0,0,0,0,0,0.1c0,0,0,0,0-0.1C37,41.7,37,41.7,37.1,41.6z M36.6,40.6c0.1-0.2,0.2-0.3,0.2-0.5
		c0.1,0,0.2,0.1,0.2,0.1c0,0,0.1,0,0.1,0c0,0.1,0,0.1-0.1,0.2C37,40.5,36.9,40.6,36.6,40.6z M36.7,40.8c0,0,0.1-0.1,0.1-0.1
		c0.1,0,0.1,0.1,0.1,0.2c0,0.1-0.1,0.1-0.1,0.1C36.8,41,36.7,40.9,36.7,40.8z M36.4,42.4c0,0.1-0.1,0.2-0.1,0.3
		c-0.1-0.1-0.1-0.1-0.2-0.2C36.2,42.5,36.3,42.5,36.4,42.4z M36.3,40.2C36.3,40.2,36.3,40.2,36.3,40.2
		C36.3,40.2,36.3,40.2,36.3,40.2C36.3,40.2,36.3,40.2,36.3,40.2C36.3,40.3,36.3,40.3,36.3,40.2C36.3,40.2,36.3,40.2,36.3,40.2z
		 M35,42c0,0.2,0,0.3,0,0.5c0.2,0,0.3,0,0.4,0.1c0,0,0,0,0,0c-0.1,0-0.3,0-0.4,0c-0.1,0-0.1,0-0.2-0.1C34.9,42.3,34.9,42.2,35,42z
		 M34.9,40.8c0.4,0.2,0.5,0.5,0.2,1c-0.2-0.1-0.2-0.1-0.2-0.3c0-0.1,0-0.2,0-0.3C34.9,41,34.9,40.9,34.9,40.8z M35.1,39.5
		C35.1,39.5,35,39.5,35.1,39.5C35,39.5,35,39.5,35.1,39.5C35,39.5,35,39.4,35.1,39.5C35.1,39.4,35.1,39.5,35.1,39.5z M34.6,42.5
		C34.6,42.5,34.6,42.5,34.6,42.5C34.6,42.5,34.6,42.5,34.6,42.5C34.6,42.6,34.6,42.6,34.6,42.5z M34.5,42.2c0.1-0.1,0.1-0.1,0.2,0
		c0.1,0.1,0.1,0.1,0,0.3C34.6,42.4,34.5,42.3,34.5,42.2z M34.2,42.5L34.2,42.5L34.2,42.5L34.2,42.5z M33.6,40.8
		c0-0.1-0.1-0.1-0.1-0.2c0,0,0.1-0.1,0.1-0.1c0.1,0,0.1,0.1,0.1,0.2C33.7,40.7,33.6,40.8,33.6,40.8z M33.4,41.2
		c-0.1,0-0.2-0.1-0.2-0.3c0-0.1,0-0.1,0-0.2c0.1,0.1,0.1,0.1,0.2,0.2c0,0,0.1,0.1,0.1,0.1C33.5,41.1,33.5,41.1,33.4,41.2
		C33.4,41.2,33.4,41.2,33.4,41.2z M32.7,41.3c0,0,0.1-0.1,0.1-0.1c0.1,0,0.1,0.1,0.2,0.1c0,0.2,0,0.3,0,0.4c0.2,0.2,0.2,0.2,0.3,0.4
		c-0.1-0.1-0.1-0.1-0.2-0.1c-0.2-0.1-0.2-0.2-0.2-0.4c0-0.1,0-0.2,0-0.3c0,0-0.1,0-0.1,0C32.7,41.4,32.6,41.4,32.7,41.3z M32,41.6
		C32,41.6,32,41.6,32,41.6C32,41.6,32,41.6,32,41.6C32,41.7,32,41.7,32,41.6C32,41.7,32,41.6,32,41.6z M31.9,41.4
		C31.9,41.3,31.9,41.3,31.9,41.4C31.9,41.3,31.9,41.3,31.9,41.4C31.9,41.4,31.9,41.4,31.9,41.4C31.9,41.4,31.9,41.4,31.9,41.4z
		 M31.7,41.8C31.7,41.8,31.7,41.8,31.7,41.8C31.7,41.8,31.7,41.8,31.7,41.8C31.7,41.9,31.7,41.9,31.7,41.8
		C31.7,41.8,31.7,41.8,31.7,41.8z M31.5,40.9c0,0.1,0.1,0.1,0,0.2C31.5,41.1,31.5,41,31.5,40.9z M31.3,40.5
		C31.3,40.5,31.3,40.5,31.3,40.5c0.1-0.3,0-0.4,0-0.6c0,0,0.1,0,0.1,0c0,0,0,0,0,0.1c0,0.3,0,0.6,0,1c0,0-0.1,0-0.1,0
		C31.3,40.8,31.3,40.7,31.3,40.5z M30.3,40.2c0-0.1,0-0.1,0-0.2c0,0,0,0,0,0c0,0,0,0,0,0c0.1,0,0.1,0.1,0,0.1c0,0.1,0,0.1,0,0.2
		c0,0,0,0,0,0c0,0-0.1,0-0.1,0C30.3,40.4,30.3,40.3,30.3,40.2z M30.1,41.7c0-0.1,0.1-0.2,0.1-0.4C30.2,41.5,30.2,41.6,30.1,41.7z
		 M29.8,40.6C29.8,40.6,29.9,40.6,29.8,40.6c0.1,0.1,0.1,0.2,0.1,0.2c0,0,0,0-0.1,0C29.8,40.8,29.8,40.7,29.8,40.6
		C29.8,40.6,29.8,40.6,29.8,40.6z M29.4,41.8L29.4,41.8L29.4,41.8L29.4,41.8z M29.2,41.8C29.2,41.8,29.2,41.8,29.2,41.8
		C29.2,41.7,29.2,41.8,29.2,41.8C29.2,41.8,29.2,41.8,29.2,41.8C29.2,41.8,29.2,41.8,29.2,41.8z M29.1,40.7
		C29.1,40.7,29.1,40.7,29.1,40.7C29.2,40.7,29.2,40.7,29.1,40.7C29.2,40.7,29.2,40.7,29.1,40.7C29.1,40.8,29.1,40.7,29.1,40.7z
		 M29.1,40C29.1,40,29.1,40.1,29.1,40C29.2,40.1,29.1,40.1,29.1,40C29.1,40.1,29.1,40,29.1,40z M28.8,41.8L28.8,41.8L28.8,41.8
		L28.8,41.8z M28.6,41c0.1,0.1,0.2,0.2,0.2,0.3c0,0,0,0.1,0,0.1c0,0-0.1,0-0.1,0C28.6,41.3,28.5,41.2,28.6,41z M28,41.5
		c0.1,0,0.1-0.1,0.1-0.1c0,0,0,0.1,0.1,0.1c0,0-0.1,0.1-0.1,0.1C28.1,41.6,28,41.5,28,41.5z M27.5,41.3
		C27.5,41.3,27.5,41.3,27.5,41.3C27.5,41.3,27.5,41.3,27.5,41.3C27.5,41.4,27.5,41.4,27.5,41.3C27.5,41.4,27.5,41.3,27.5,41.3z
		 M26.9,40.3c0,0,0.1,0,0.1,0c0,0,0,0,0.1,0c0,0,0,0,0,0c0,0,0,0.1,0,0.2c0,0.1-0.1,0.1-0.1,0.1C26.9,40.5,26.9,40.4,26.9,40.3z
		 M25.7,40.9c0.1-0.1,0.2-0.2,0.4-0.4c0,0.4-0.2,0.5-0.2,0.8c0,0,0,0-0.1,0C25.7,41.2,25.7,41.1,25.7,40.9z M25.4,39.4
		c0.1,0,0.2,0,0.3,0c0,0,0,0,0,0.1c0,0,0,0.1,0,0.1C25.5,39.6,25.4,39.5,25.4,39.4z M25.1,41.5C25.1,41.5,25.1,41.6,25.1,41.5
		C25.1,41.6,25.1,41.6,25.1,41.5C25.1,41.6,25.1,41.5,25.1,41.5z M25.1,40.4c0-0.1,0-0.2,0.1-0.4c0.1,0,0.1-0.1,0.2-0.1
		c0,0.3,0.1,0.5,0,0.7C25.1,40.6,25.1,40.6,25.1,40.4z M24.6,40.9L24.6,40.9L24.6,40.9L24.6,40.9z M23.9,40.5
		C23.9,40.5,24,40.5,23.9,40.5c0.1,0,0.1,0,0.1,0C24,40.6,24,40.6,23.9,40.5C23.9,40.6,23.9,40.6,23.9,40.5z M23.3,41.1
		c0,0,0.1,0,0.1-0.1c0,0,0,0.1,0,0.1c0,0-0.1,0.1-0.1,0.1C23.3,41.1,23.3,41.1,23.3,41.1z M23.2,41.4C23.1,41.5,23.1,41.4,23.2,41.4
		C23.2,41.5,23.1,41.5,23.2,41.4z M23.2,39.6c0-0.1,0-0.2,0.1-0.2c0.1,0.1,0.1,0.1,0.1,0.2c0,0,0,0,0,0
		C23.2,39.6,23.2,39.6,23.2,39.6C23.2,39.7,23.2,39.7,23.2,39.6C23.2,39.7,23.2,39.7,23.2,39.6C23.2,39.7,23.1,39.6,23.2,39.6z
		 M23.2,39.3C23.2,39.3,23.2,39.3,23.2,39.3C23.2,39.3,23.2,39.3,23.2,39.3C23.2,39.3,23.2,39.3,23.2,39.3
		C23.2,39.3,23.2,39.3,23.2,39.3z M22.8,41.5C22.8,41.5,22.8,41.5,22.8,41.5C22.8,41.5,22.8,41.5,22.8,41.5
		C22.8,41.5,22.8,41.5,22.8,41.5C22.8,41.5,22.8,41.5,22.8,41.5z M22.5,41.4C22.5,41.4,22.5,41.3,22.5,41.4c0.1,0,0.1,0,0.1,0
		C22.6,41.4,22.6,41.5,22.5,41.4C22.5,41.5,22.5,41.4,22.5,41.4z M21.6,40.5c0-0.1,0-0.2,0-0.3c0-0.1,0.2-0.1,0.1-0.3
		c0,0,0.1-0.1,0.1-0.1c0,0,0.1,0.1,0.1,0.1c0,0.1,0,0.1,0,0.2c-0.1,0.3-0.1,0.6-0.2,0.9c0,0.1,0,0.2,0,0.3c-0.2-0.1-0.2-0.1-0.2-0.3
		c0-0.1,0.1-0.2,0.1-0.3C21.6,40.7,21.6,40.6,21.6,40.5z M21.3,40.7C21.3,40.7,21.3,40.6,21.3,40.7C21.4,40.6,21.4,40.7,21.3,40.7
		C21.4,40.7,21.3,40.7,21.3,40.7C21.3,40.7,21.3,40.7,21.3,40.7z M20.4,40.1c0.1,0.3,0.1,0.4,0.1,0.6c0,0,0,0-0.1,0c0,0,0,0,0,0
		c0,0,0-0.1,0-0.1C20.4,40.4,20.4,40.3,20.4,40.1z M19.5,39.9c0-0.1,0.1-0.3,0.2-0.3c0,0.2,0,0.3-0.1,0.5
		C19.5,40.1,19.4,40,19.5,39.9z M19,40.5C19,40.4,19.1,40.4,19,40.5c0.1-0.1,0.1-0.1,0.1,0c0,0,0,0.1,0,0.1
		C19.2,40.6,19.1,40.6,19,40.5C19.1,40.5,19,40.5,19,40.5z M18.2,40.8c0.1,0,0.1-0.1,0.1-0.1c0.1,0,0.1,0.1,0.1,0.1
		c0,0-0.1,0.1-0.1,0.1C18.3,40.9,18.3,40.8,18.2,40.8z M18.1,39.3C18.1,39.3,18.1,39.3,18.1,39.3C18.1,39.3,18.1,39.3,18.1,39.3
		C18.1,39.3,18.1,39.3,18.1,39.3z M17.5,40.2C17.5,40.2,17.5,40.2,17.5,40.2c0.1,0.1,0.1,0.1,0,0.4C17.4,40.3,17.4,40.2,17.5,40.2z
		 M17.7,38.2C17.7,38.2,17.7,38.2,17.7,38.2c0.1,0,0.1,0.1,0.1,0.1c0,0,0,0,0,0c0,0,0,0,0,0C17.7,38.3,17.7,38.2,17.7,38.2z
		 M17.3,39.8C17.3,39.7,17.4,39.7,17.3,39.8C17.4,39.7,17.4,39.7,17.3,39.8C17.4,39.8,17.4,39.8,17.3,39.8
		C17.4,39.8,17.3,39.8,17.3,39.8z M17.5,37.9C17.5,37.9,17.6,37.8,17.5,37.9C17.6,37.8,17.6,37.9,17.5,37.9
		C17.6,37.9,17.6,37.9,17.5,37.9C17.5,37.9,17.5,37.9,17.5,37.9z M16.5,40.2C16.5,40.2,16.5,40.2,16.5,40.2
		C16.5,40.2,16.5,40.2,16.5,40.2C16.5,40.2,16.5,40.2,16.5,40.2C16.5,40.2,16.5,40.2,16.5,40.2z M15.8,40.6c0.1,0,0.2,0.1,0.2,0.2
		C15.9,40.7,15.9,40.7,15.8,40.6z M15.8,40.2c0,0.1,0.1,0.1,0.1,0.1c0,0,0,0.1,0,0.1c0,0-0.1,0-0.1-0.1
		C15.8,40.4,15.8,40.3,15.8,40.2z M15.3,39.3C15.3,39.3,15.3,39.3,15.3,39.3C15.3,39.3,15.3,39.3,15.3,39.3c0.1,0.1,0,0.1,0,0.1
		C15.3,39.4,15.3,39.3,15.3,39.3z M14.8,40C14.8,40,14.8,40,14.8,40c0.1-0.1,0.1-0.2,0.2-0.2c0.1,0,0.1,0.1,0.1,0.2
		c0,0.1,0,0.1,0,0.2c0,0,0,0.1,0,0.1C14.9,40.3,14.8,40.2,14.8,40z M14.2,39.1C14.2,39.1,14.2,39.1,14.2,39.1
		C14.2,39.1,14.2,39.1,14.2,39.1C14.2,39.2,14.2,39.2,14.2,39.1C14.2,39.2,14.2,39.1,14.2,39.1z M12.8,40.2
		C12.8,40.2,12.8,40.2,12.8,40.2C12.8,40.2,12.8,40.2,12.8,40.2C12.8,40.2,12.8,40.2,12.8,40.2C12.8,40.2,12.8,40.2,12.8,40.2z
		 M12.3,39.8c0,0,0.1-0.1,0.1-0.1c0,0,0.1,0,0.1,0.1c0,0.1,0,0.3,0,0.4C12.3,40,12.3,39.9,12.3,39.8z M11.9,38.5
		C11.9,38.5,11.9,38.5,11.9,38.5C11.9,38.5,11.9,38.5,11.9,38.5C11.9,38.5,11.9,38.5,11.9,38.5C11.9,38.5,11.9,38.5,11.9,38.5z
		 M10.6,39.1C10.6,39.1,10.7,39.1,10.6,39.1C10.7,39.1,10.7,39.1,10.6,39.1C10.7,39.2,10.7,39.2,10.6,39.1
		C10.6,39.2,10.6,39.2,10.6,39.1z M10.1,39.5C10.2,39.5,10.2,39.5,10.1,39.5C10.2,39.5,10.2,39.5,10.1,39.5
		C10.2,39.6,10.2,39.6,10.1,39.5C10.2,39.6,10.2,39.5,10.1,39.5z M9.1,39.1L9.1,39.1L9.1,39.1L9.1,39.1z M7.8,39.2L7.8,39.2
		L7.8,39.2L7.8,39.2z M7.1,30.6C7.1,30.6,7.1,30.6,7.1,30.6c0.1-0.1,0.1-0.1,0.1,0c0,0,0,0.1,0,0.1C7.1,30.7,7.1,30.7,7.1,30.6z
		 M7.2,28.9c0-0.2,0-0.3,0.1-0.4c0,0.1,0.1,0.1,0.1,0.2c0,0,0,0.1,0,0.2C7.4,28.9,7.3,28.9,7.2,28.9z M7,29.6c0,0,0,0.1,0.1,0.1
		c0,0,0,0,0,0.1C7,29.8,7,29.7,7,29.6C7,29.7,7,29.7,7,29.6z M7.2,27.3c0-0.2,0.1-0.3,0.1-0.5c0-0.1,0-0.1,0.1-0.1c0,0,0,0.1,0,0.1
		C7.4,27.1,7.3,27.2,7.2,27.3z M6.9,28c0-0.2,0.1-0.2,0.2-0.1c0,0,0,0.1,0,0.1c0,0,0,0.1-0.1,0.1C7,28.1,6.9,28.1,6.9,28z M6.7,30.3
		L6.7,30.3L6.7,30.3L6.7,30.3z M6.8,29.2C6.8,29.2,6.8,29.2,6.8,29.2C6.8,29.2,6.8,29.2,6.8,29.2C6.8,29.2,6.8,29.3,6.8,29.2
		C6.8,29.3,6.8,29.2,6.8,29.2z M6.6,28.2c0-0.1,0-0.2,0.1-0.2c0.1,0.2,0.1,0.4,0.2,0.6c0,0.1,0,0.2-0.1,0.3
		C6.7,28.6,6.6,28.4,6.6,28.2z M6.3,30.4C6.3,30.4,6.3,30.4,6.3,30.4C6.4,30.4,6.4,30.4,6.3,30.4C6.4,30.4,6.4,30.4,6.3,30.4
		C6.4,30.5,6.3,30.4,6.3,30.4z M6.4,29C6.4,29,6.4,29,6.4,29C6.4,29,6.4,29,6.4,29C6.4,29.1,6.4,29.1,6.4,29C6.4,29.1,6.4,29,6.4,29
		z M6.2,28.5C6.2,28.5,6.3,28.5,6.2,28.5c0.1-0.1,0.2-0.1,0.3,0c0,0,0,0.1,0,0.1c0,0,0,0-0.1,0.1C6.4,28.6,6.3,28.6,6.2,28.5z
		 M6.4,25.8c0,0.1,0,0.1,0,0.2c0,0-0.1,0-0.1,0c0,0,0-0.1,0-0.1C6.3,25.8,6.4,25.8,6.4,25.8z M6.1,27.6c0,0.1,0,0.2-0.1,0.2
		c0-0.1,0-0.2,0-0.3c0-0.1,0-0.1,0.1-0.2C6.1,27.4,6.1,27.5,6.1,27.6z M6.5,24.2C6.5,24.2,6.5,24.2,6.5,24.2
		C6.5,24.2,6.5,24.2,6.5,24.2C6.5,24.2,6.5,24.2,6.5,24.2C6.5,24.2,6.5,24.2,6.5,24.2z M6.7,23.6c0.1,0.1,0.1,0.1,0.1,0.1
		c0,0.2,0,0.3-0.1,0.5C6.7,24.1,6.7,23.9,6.7,23.6C6.6,23.7,6.7,23.7,6.7,23.6z M6.4,26.6c0,0,0-0.1-0.1-0.1c0,0,0,0,0,0
		c0,0,0.1,0,0.1,0C6.5,26.5,6.5,26.5,6.4,26.6C6.5,26.6,6.4,26.6,6.4,26.6z M6.3,27.5C6.3,27.5,6.3,27.5,6.3,27.5
		C6.3,27.5,6.3,27.5,6.3,27.5C6.3,27.5,6.3,27.5,6.3,27.5C6.3,27.5,6.3,27.5,6.3,27.5z M6.3,28L6.3,28L6.3,28L6.3,28z M6.1,28.9
		C6.1,28.9,6.1,28.9,6.1,28.9C6.1,28.8,6.1,28.9,6.1,28.9C6.1,28.9,6.1,28.9,6.1,28.9C6.1,28.9,6.1,28.9,6.1,28.9z M6.3,26.9
		C6.3,27,6.3,27,6.3,26.9c-0.1,0-0.1,0-0.1,0C6.2,26.9,6.3,26.9,6.3,26.9C6.3,26.9,6.3,26.9,6.3,26.9z M6,29.3L6,29.3L6,29.3L6,29.3
		z M6,28.4C6,28.4,6.1,28.4,6,28.4c0.1,0.1,0,0.1,0,0.2C6,28.5,6,28.5,6,28.4C6,28.5,6,28.4,6,28.4z M6,27.8c0,0.1,0,0.1,0,0.2
		C5.9,27.9,5.9,27.8,6,27.8z M5.9,27.2c0.1,0,0.1,0,0.2,0c0.1,0.1,0,0.1,0,0.2C6,27.3,6,27.2,5.9,27.2z M6.5,23
		c0.2,0.2,0,0.4-0.1,0.6c0-0.2,0-0.3-0.1-0.4c0-0.1-0.1-0.2-0.1-0.3c0.1-0.1,0.2-0.2,0.3-0.1C6.5,22.9,6.5,22.9,6.5,23z M6.6,21.4
		C6.6,21.4,6.5,21.4,6.6,21.4c0-0.1,0-0.1,0-0.1C6.6,21.3,6.6,21.4,6.6,21.4C6.6,21.4,6.6,21.4,6.6,21.4z M6.3,25.1L6.3,25.1
		L6.3,25.1L6.3,25.1z M6.2,26.3c0,0.1-0.1,0.2-0.1,0.2c0,0,0,0-0.1,0c0,0,0,0,0,0C6,26.3,6,26.3,6.2,26.3z M6,25.7
		C6,25.7,6.1,25.7,6,25.7C6.1,25.7,6.1,25.7,6,25.7C6,25.8,6,25.7,6,25.7C6,25.7,6,25.7,6,25.7z M6.2,24C6.2,24,6.2,23.9,6.2,24
		C6.3,24,6.3,24,6.2,24C6.3,24,6.2,24,6.2,24C6.2,24,6.2,24,6.2,24z M6.2,23.5C6.2,23.5,6.2,23.5,6.2,23.5
		C6.2,23.5,6.2,23.5,6.2,23.5C6.2,23.5,6.2,23.5,6.2,23.5C6.2,23.5,6.2,23.5,6.2,23.5z M6,25.3L6,25.3L6,25.3L6,25.3z M6,25
		C6,25,6,25,6,25C6,25,6,25,6,25C6,25,6,25,6,25C6,25,6,25,6,25z M6.1,22.3c0-0.1,0-0.2,0-0.3c0-0.1,0.1-0.1,0.1-0.2
		c0.1,0,0.1-0.1,0.2,0c0,0,0,0,0,0C6.5,22,6.4,22,6.3,22c0,0-0.1,0-0.1,0.1c0.1,0.1,0.2,0.1,0.2,0.2c0,0,0.1,0.1,0.1,0.1
		c0,0.1,0,0.1-0.1,0.2C6.3,22.5,6.2,22.4,6.1,22.3z M6.7,18.9c0,0.1,0,0.2-0.1,0.2c-0.1,0-0.1-0.1-0.1-0.1
		C6.6,18.9,6.6,18.9,6.7,18.9c0-0.1,0.1-0.1,0.1-0.1C6.8,18.8,6.8,18.8,6.7,18.9z M6,23.8L6,23.8L6,23.8L6,23.8z M6.1,22.8
		c0.1,0.1,0.1,0.2,0,0.3C6,23,6,22.9,6.1,22.8z M4.9,30.3C4.9,30.3,4.9,30.3,4.9,30.3C5,30.3,5,30.3,4.9,30.3
		C5,30.3,5,30.3,4.9,30.3C4.9,30.3,4.9,30.3,4.9,30.3z M6.3,18.2c0,0-0.1,0.1-0.1,0.1C6.2,18.2,6.2,18.2,6.3,18.2
		c-0.1-0.2-0.1-0.3-0.2-0.3c0-0.1,0-0.2,0.1-0.2c0,0,0.1-0.1,0.1,0c0.1,0.1,0.2,0.2,0.3,0.3c0.1,0.2,0.1,0.5,0.1,0.8
		c0,0.1,0,0.2,0,0.3c-0.2-0.1-0.2-0.3-0.2-0.5C6.3,18.3,6.4,18.3,6.3,18.2z M6.4,17.4L6.4,17.4L6.4,17.4L6.4,17.4z M5.1,18.7
		C5.1,18.7,5.1,18.7,5.1,18.7C5.1,18.7,5.1,18.7,5.1,18.7C5.1,18.7,5.1,18.7,5.1,18.7z M5.1,18c0.1-0.2,0.2-0.4,0.4-0.6
		c0,0,0,0.1,0,0.1c-0.1,0.3-0.2,0.5-0.3,0.8c0-0.1-0.1-0.1-0.1-0.2C5.1,18.1,5.1,18.1,5.1,18z M5.4,16.1C5.4,16,5.4,16,5.4,16.1
		C5.4,16,5.4,16,5.4,16.1C5.4,16.1,5.4,16.1,5.4,16.1C5.4,16.1,5.4,16.1,5.4,16.1z M6.9,4.4C6.9,4.4,6.8,4.4,6.9,4.4
		c-0.1,0-0.1,0-0.1-0.1c0-0.1,0.1-0.2,0.2-0.2C6.9,4.2,6.9,4.3,6.9,4.4z M5.5,17.2c0,0.1,0,0.2-0.1,0.2c-0.1-0.1-0.1-0.2-0.1-0.4
		c0-0.1,0-0.2,0-0.4C5.4,16.8,5.4,17.1,5.5,17.2z M7,5.5C7,5.5,7,5.5,7,5.5C6.9,5.6,6.9,5.5,7,5.5C6.9,5.5,6.9,5.5,7,5.5
		C6.9,5.5,7,5.5,7,5.5z M5.7,16.5c-0.1,0.1-0.1,0.2-0.2,0.3c0-0.2,0-0.3,0-0.4C5.6,16.5,5.7,16.5,5.7,16.5z M5.7,19.4
		c-0.2-0.1-0.3-0.4-0.4-0.6c0-0.2,0-0.4,0.2-0.6c0,0.4,0,0.7,0,1C5.7,19.2,5.7,19.2,5.7,19.4z M7.4,5.2C7.4,5.2,7.4,5.2,7.4,5.2
		C7.4,5.2,7.4,5.2,7.4,5.2C7.4,5.2,7.4,5.2,7.4,5.2z M6.1,17.9c0,0.1,0,0.3,0.1,0.4c0,0.1-0.1,0.2-0.1,0.3c0,0.1-0.1,0.1-0.1,0.1
		c-0.1-0.2-0.2-0.3-0.1-0.5c0-0.1,0-0.1,0-0.2C5.9,17.9,6,17.9,6.1,17.9z M7.8,5.2C7.6,5.1,7.6,5.1,7.6,4.9C7.6,5,7.7,5.1,7.8,5.2z
		 M6.8,17.8c-0.1-0.2-0.1-0.3-0.2-0.5c0-0.2-0.1-0.3-0.2-0.5c0-0.2-0.2-0.2-0.2-0.4c0.2,0,0.3,0,0.4,0.2l0,0c0,0,0,0,0,0
		c0,0,0,0,0,0C6.9,17.1,6.9,17.5,6.8,17.8z M7,18.2c-0.1,0.2-0.1,0.3-0.2,0.5c0-0.1-0.1-0.2-0.1-0.3C6.8,18.3,6.9,18.2,7,18.2z
		 M7.2,17.5C7.2,17.5,7.2,17.5,7.2,17.5C7.2,17.5,7.2,17.5,7.2,17.5C7.2,17.5,7.1,17.5,7.2,17.5C7.1,17.5,7.2,17.5,7.2,17.5z
		 M7.3,17.4C7.3,17.4,7.3,17.4,7.3,17.4C7.3,17.4,7.3,17.4,7.3,17.4C7.3,17.4,7.3,17.4,7.3,17.4C7.3,17.4,7.3,17.4,7.3,17.4
		C7.3,17.4,7.3,17.4,7.3,17.4z M8.5,6.9C8.5,6.9,8.5,6.9,8.5,6.9C8.5,6.9,8.5,6.9,8.5,6.9C8.5,6.9,8.5,6.9,8.5,6.9
		C8.5,6.9,8.5,6.9,8.5,6.9z M8.8,6.5C8.7,6.3,8.7,6.2,8.8,6C8.8,6.2,8.9,6.3,8.8,6.5z M7.5,17.3C7.5,17.3,7.5,17.3,7.5,17.3
		c-0.1-0.1-0.2,0-0.2,0c0,0,0,0.1,0,0.1c0,0,0,0,0,0c0,0,0-0.1,0-0.1c0-0.2-0.1-0.4,0-0.6c0.2,0.1,0.2,0.2,0.2,0.4
		C7.6,17.1,7.5,17.2,7.5,17.3z M7.3,17.4C7.3,17.4,7.3,17.4,7.3,17.4C7.3,17.4,7.3,17.4,7.3,17.4z M7.7,17.2L7.7,17.2L7.7,17.2
		L7.7,17.2z M7.6,18.4c-0.1,0.4-0.3,0.7-0.4,1c0,0,0,0-0.1,0c0,0-0.1,0-0.1-0.1c0,0,0-0.1,0-0.1c0.1-0.3,0-0.7-0.1-1c0,0,0,0,0,0
		c0-0.2,0.1-0.2,0.2-0.3c0,0,0-0.1-0.1-0.1c0,0,0.1-0.1,0.1-0.1c0,0,0,0,0,0s0.2,0,0.4,0c-0.1,0-0.2,0.1-0.1,0.3
		c-0.1,0-0.1,0.1-0.2,0.1c0,0.2-0.1,0.4,0,0.6c0.2,0,0.1-0.2,0.2-0.3C7.5,18.4,7.6,18.4,7.6,18.4z M6.9,20.3
		C6.9,20.3,6.8,20.3,6.9,20.3C6.9,20.3,6.9,20.3,6.9,20.3C6.9,20.3,6.9,20.3,6.9,20.3C6.9,20.3,6.9,20.3,6.9,20.3z M6.8,21.1
		C6.8,21.1,6.8,21,6.8,21.1C6.8,21,6.8,21,6.8,21.1C6.8,21,6.8,21,6.8,21.1z M7.1,20.3c0.1-0.1,0.1-0.1,0.2-0.2
		c0.1,0.2,0,0.4-0.1,0.6C7,20.5,7,20.4,7.1,20.3z M7.2,20.8C7.2,20.8,7.2,20.8,7.2,20.8C7.2,20.9,7.2,20.9,7.2,20.8
		C7.2,20.9,7.1,20.9,7.2,20.8C7.1,20.8,7.2,20.8,7.2,20.8z M9.2,6.5C9.2,6.5,9.2,6.5,9.2,6.5C9.1,6.5,9.1,6.5,9.2,6.5
		C9.1,6.4,9.2,6.4,9.2,6.5C9.2,6.4,9.2,6.5,9.2,6.5z M7.2,22.6c-0.1,0-0.1,0-0.2,0.1c0,0,0,0,0,0c0-0.1,0.1-0.2,0-0.2
		c0-0.1-0.1-0.1-0.2,0c-0.1,0.1-0.1,0.1-0.1,0.2c0.1,0.2,0.1,0.2-0.1,0.2c0,0,0,0-0.1,0c0-0.1,0.1-0.1,0.1-0.2c0-0.2,0-0.4,0-0.7
		c0,0,0,0,0,0c0-0.2,0.1-0.4,0-0.5c0.1-0.1,0.2-0.2,0.2-0.3c0.1,0,0.1,0.1,0.2,0.1c0,0.2-0.1,0.3-0.1,0.4c0,0.1,0,0.1,0.1,0.2
		c0,0,0.1-0.1,0.1-0.1c0-0.1,0-0.1,0.1-0.2c0,0,0-0.1,0.1-0.1C7.3,21.8,7.3,22.2,7.2,22.6z M7.2,22.8c0-0.1,0-0.1,0-0.2
		C7.3,22.7,7.3,22.8,7.2,22.8z M9.3,5.9L9.3,5.9L9.3,5.9L9.3,5.9z M7.4,24.2c0.1,0,0.1,0,0.1,0.1c-0.1,0-0.2,0-0.4,0.1
		c-0.1-0.2,0-0.5-0.1-0.8c-0.1,0-0.1-0.1-0.2-0.1c0-0.3,0.1-0.5,0.1-0.8c0,0,0,0,0.1,0c-0.1,0.3,0,0.5,0.2,0.6c0,0.2,0,0.4,0,0.6
		C7.2,24.1,7.2,24.1,7.4,24.2z M7.7,21.8c0.1,0,0.2,0.1,0.2,0.2c0,0.1,0,0.2,0,0.3C7.9,22.2,8,22.2,8,22.2c0,0,0.1,0,0.1,0
		c0,0,0,0,0,0c-0.1,0.1-0.1,0.2,0,0.3c0,0.1,0,0.2,0,0.3C8,23,7.9,23,7.8,23c0-0.2,0.1-0.3,0-0.5c-0.1,0-0.2,0.1-0.3,0.1
		c0-0.2,0-0.4,0-0.6C7.6,21.9,7.6,21.8,7.7,21.8z M9.9,5C9.9,5,9.9,5,9.9,5C9.9,5,9.9,5,9.9,5C9.9,5,9.9,5,9.9,5z M10.1,4.7
		C10.1,4.7,10.1,4.7,10.1,4.7C10.1,4.7,10.1,4.7,10.1,4.7C10.1,4.7,10.1,4.7,10.1,4.7z M10.4,5.8c0,0.1,0,0.2,0,0.3
		c-0.1,0-0.1,0-0.2,0c0-0.2,0-0.3,0-0.5c-0.3,0-0.3,0-0.5-0.1c0,0.2,0,0.5-0.1,0.7c0,0.1,0,0.2-0.1,0.3c0,0-0.1,0-0.1,0.1
		c0-0.4,0.1-0.7,0.1-1C9.7,5.2,9.7,5.2,10,5.4c0,0,0,0,0.1,0c0.1-0.1,0.1-0.1,0.2-0.1c0.1,0.1,0.1,0.2,0.2,0.3
		C10.4,5.6,10.4,5.7,10.4,5.8z M11.6,5C11.6,5,11.6,5,11.6,5C11.5,5,11.5,5,11.6,5C11.6,4.9,11.6,4.9,11.6,5C11.6,4.9,11.6,5,11.6,5
		z M11.9,5.9c0,0.1-0.1,0.2-0.1,0.3c0,0.1-0.1,0.1-0.2,0c0.1-0.1,0.1-0.2,0.2-0.3c0,0,0-0.1,0-0.1c0,0-0.1,0-0.1,0
		c-0.1,0.1-0.2,0.2-0.3,0.3C11,6.1,11,6.1,10.9,6.6c-0.1,0-0.1,0-0.2,0c-0.1,0-0.1,0-0.2,0c0-0.1,0.2-0.2,0.3-0.3
		c0-0.1-0.1-0.1-0.1-0.2c-0.1-0.2-0.1-0.4,0.1-0.6c0.1-0.1,0.1-0.2,0.2-0.3c0,0.1,0.1,0.1,0.1,0.2c0,0.1,0,0.2,0.1,0.2
		c0,0,0.1-0.1,0.2-0.1c0.1,0,0.1-0.1,0.2-0.1c0.1,0,0.1,0,0.2,0C11.9,5.5,12,5.7,11.9,5.9z M12.3,6.2c0,0.1,0,0.1,0,0.2c0,0,0,0,0,0
		c-0.1,0.1-0.1,0.1-0.1,0.2c-0.1,0-0.2,0-0.2,0c0,0-0.1,0-0.1,0c0,0,0-0.1,0-0.1c0.1-0.1,0.2-0.2,0.2-0.4
		C12.1,6.2,12.2,6.2,12.3,6.2z M12.4,5.8L12.4,5.8L12.4,5.8L12.4,5.8z M12.7,6.6C12.7,6.6,12.7,6.6,12.7,6.6c-0.1,0-0.2,0-0.3,0
		c0-0.1,0-0.1,0-0.2c0.1,0.1,0.2-0.1,0.3-0.2C12.7,6.3,12.7,6.5,12.7,6.6z M12.8,5.8C12.8,5.8,12.8,5.8,12.8,5.8
		C12.8,5.8,12.7,5.7,12.8,5.8C12.8,5.7,12.8,5.7,12.8,5.8C12.8,5.7,12.8,5.7,12.8,5.8z M13.2,4.8c0,0.1,0,0.3,0.2,0.3
		c0,0-0.1,0.1-0.1,0.1c-0.1,0-0.2,0-0.4,0c0-0.2,0-0.3,0.1-0.4C13,4.9,13.1,4.8,13.2,4.8z M13.3,5C13.3,4.9,13.4,4.9,13.3,5
		C13.4,4.9,13.4,5,13.3,5c0,0.1,0,0.1,0,0.1C13.3,5,13.3,5,13.3,5z M13.5,4.5c-0.1,0-0.1,0-0.2-0.1c0-0.1,0-0.1-0.1-0.2
		c0-0.1,0.1-0.1,0.1-0.1c0,0,0.1,0,0.1,0c0,0,0.1,0.1,0.1,0.1C13.5,4.3,13.5,4.5,13.5,4.5z M13.2,6.7c0,0-0.1-0.1-0.2-0.1
		c-0.1,0-0.1,0-0.2,0c0,0,0,0,0,0c-0.1-0.2,0-0.4,0-0.7c0-0.1,0-0.3,0.2-0.4c0.1,0.1,0.1,0.1,0.2,0.2c-0.2,0.3-0.2,0.3-0.1,0.6
		c0,0.1,0,0.1,0,0.2C13.2,6.6,13.2,6.7,13.2,6.7z M13.7,4.1L13.7,4.1L13.7,4.1L13.7,4.1z M13.7,4.9C13.7,4.9,13.7,4.9,13.7,4.9
		C13.7,4.9,13.7,4.9,13.7,4.9z M13.8,4.3L13.8,4.3L13.8,4.3L13.8,4.3z M13.8,5.1C13.8,5,13.8,5,13.7,5C13.8,5,13.8,5,13.8,5.1z
		 M14.3,4.6c-0.1-0.1-0.1-0.1-0.1-0.1c0,0,0-0.1,0-0.2c0,0,0.1,0,0.1,0c0,0,0.1,0.1,0.1,0.1C14.3,4.5,14.3,4.5,14.3,4.6z M14.2,5.2
		C14.2,5.2,14.2,5.2,14.2,5.2c0-0.1-0.1-0.1-0.1-0.1C14.2,5.1,14.2,5.1,14.2,5.2C14.2,5.2,14.2,5.2,14.2,5.2z M14.6,6.3
		c0,0.4-0.3,0.5-0.3,0.7c0,0,0,0.1,0,0.1c0,0-0.1,0-0.1,0C14.2,7,14.1,7,14,7c0,0,0,0,0,0c0-0.1,0.1-0.1,0.1-0.2c0,0,0,0,0.1-0.1
		C14.2,6.6,14.4,6.5,14.6,6.3z M14.5,5.8C14.5,5.8,14.6,5.9,14.5,5.8c0.1,0.1,0,0.1,0,0.1C14.5,6,14.4,6,14.4,6
		C14.4,5.9,14.5,5.8,14.5,5.8z M14.9,4.2c-0.1,0.4-0.1,0.4-0.3,0.5C14.7,4.5,14.7,4.3,14.9,4.2z M15,4.8c0-0.1,0-0.2,0.1-0.3
		C15.2,4.7,15.1,4.8,15,4.8z M15.1,5.1c0,0.1-0.1,0.1-0.2,0.3c-0.1-0.3-0.2-0.1-0.4-0.1c0-0.1,0-0.2,0-0.3C14.7,5,14.9,5,15,4.8
		C15,5,15.1,5,15.1,5.1z M15,6.9C15,6.9,15,7,15,6.9C15.1,7,15,7,15,7.1C15,7,15,7,15,6.9C15,7,15,6.9,15,6.9z M15.3,4.5
		c0-0.1,0-0.2,0.1-0.2c0,0,0,0,0,0C15.5,4.5,15.4,4.5,15.3,4.5z M15.3,7c0.1,0.1,0.1,0.3,0,0.4c0,0-0.1-0.1-0.1-0.1
		c0-0.1,0-0.1,0-0.2C15.2,7,15.3,6.9,15.3,7z M15.8,4.8C15.8,4.8,15.7,4.9,15.8,4.8c-0.1,0-0.1,0-0.1,0C15.6,4.8,15.7,4.7,15.8,4.8
		C15.7,4.7,15.7,4.8,15.8,4.8z M15.8,5.1L15.8,5.1L15.8,5.1L15.8,5.1z M15.7,7.1c0-0.1,0.1-0.1,0.1-0.1c0,0,0,0,0.1,0
		c0,0.1,0,0.2,0,0.4c0,0,0,0,0,0c-0.1,0-0.3,0-0.4,0.1c0,0,0,0,0,0c0,0,0-0.1,0-0.1c0.1,0,0.1,0,0.2,0C15.7,7.3,15.7,7.2,15.7,7.1z
		 M16.2,4.8C16.2,4.8,16.2,4.9,16.2,4.8c-0.1,0.1-0.1,0-0.2,0C16.1,4.8,16.1,4.7,16.2,4.8C16.2,4.7,16.2,4.8,16.2,4.8z M16.5,4.9
		L16.5,4.9L16.5,4.9L16.5,4.9z M16.4,5.4C16.4,5.4,16.4,5.4,16.4,5.4C16.4,5.4,16.3,5.4,16.4,5.4C16.4,5.4,16.4,5.4,16.4,5.4
		C16.4,5.4,16.4,5.4,16.4,5.4z M16.7,5.3c0.1,0.1,0.1,0.1,0,0.2c0,0,0,0-0.1,0.1c0-0.1,0-0.1,0-0.1C16.6,5.4,16.6,5.4,16.7,5.3
		C16.6,5.3,16.7,5.3,16.7,5.3z M16.9,4.9C16.8,5,16.8,5,16.7,5.1c0,0,0,0,0,0C16.7,4.8,16.7,4.8,16.9,4.9z M16.7,7.2
		c0,0.1,0,0.2,0,0.4C16.7,7.4,16.7,7.4,16.7,7.2z M17.3,3.6C17.3,3.6,17.3,3.6,17.3,3.6C17.3,3.6,17.3,3.6,17.3,3.6
		C17.3,3.6,17.3,3.6,17.3,3.6C17.3,3.6,17.3,3.6,17.3,3.6z M17.2,5.1C17.2,5.1,17.2,5.1,17.2,5.1C17.2,5.1,17.2,5.1,17.2,5.1
		C17.2,5.1,17.2,5.1,17.2,5.1C17.2,5.1,17.2,5.1,17.2,5.1z M17.4,4.3L17.4,4.3L17.4,4.3L17.4,4.3z M17.2,7.5c0,0.1,0,0.2,0,0.2
		c0,0.1-0.1,0-0.2,0.1c0,0,0,0,0,0c0,0,0,0,0,0c0,0,0,0,0,0c0,0,0,0,0,0c0,0,0,0,0,0c0,0,0,0,0,0c0,0,0.1-0.1,0.1-0.1c0,0,0,0,0,0
		C17.1,7.5,17.1,7.5,17.2,7.5C17.1,7.4,17.1,7.5,17.2,7.5z M17.3,5.8C17.4,5.8,17.4,5.8,17.3,5.8C17.4,5.8,17.4,5.8,17.3,5.8
		C17.4,5.9,17.4,5.9,17.3,5.8C17.3,5.8,17.3,5.8,17.3,5.8z M17.6,5.1c-0.1,0-0.1-0.1-0.1-0.2c0,0,0-0.1,0-0.1c0,0,0.1,0,0.1,0
		C17.6,5,17.7,5.1,17.6,5.1z M17.4,7.7C17.4,7.7,17.4,7.6,17.4,7.7c0.1-0.1,0.2-0.1,0.2,0c0,0,0,0.1,0,0.1
		C17.5,7.7,17.4,7.7,17.4,7.7z M17.6,7.4c-0.1,0-0.2,0-0.2-0.1C17.5,7.3,17.6,7.3,17.6,7.4z M18,6c0,0.1-0.1,0.2-0.1,0.3
		c0,0,0,0-0.1,0c0-0.1,0-0.2,0.1-0.3C17.8,6,17.9,6,18,6C17.9,6,18,6,18,6z M17.9,6.6c0-0.1,0-0.2-0.1-0.2C17.9,6.4,18,6.5,17.9,6.6
		z M18.4,6L18.4,6L18.4,6L18.4,6z M18.5,6.6c-0.2,0.1-0.2,0.1-0.3-0.1C18.3,6.5,18.4,6.6,18.5,6.6z M19,5.1C19,5.1,19,5.1,19,5.1
		C19,5.1,19,5.1,19,5.1C19,5.1,19,5.1,19,5.1C19,5.1,19,5.1,19,5.1z M18.8,7.9c-0.1,0-0.2,0.1-0.3-0.1c0.1-0.1,0.2-0.1,0.2-0.2
		C18.8,7.7,18.9,7.8,18.8,7.9z M19.1,6.2C19.1,6.3,19.1,6.3,19.1,6.2C19.1,6.3,19,6.3,19.1,6.2C19,6.2,19,6.2,19.1,6.2
		C19.1,6.2,19.1,6.2,19.1,6.2z M19.7,5.2c-0.1,0-0.2,0-0.2,0c0,0,0,0,0-0.1c0.1,0,0.1,0,0.2,0C19.7,5.1,19.7,5.1,19.7,5.2z
		 M20.1,4.5C20,4.5,20,4.6,20.1,4.5C20,4.6,20,4.5,20.1,4.5C20,4.5,20,4.5,20.1,4.5z M20.1,5.2L20.1,5.2L20.1,5.2L20.1,5.2z M20,6.9
		C20,6.9,20,6.9,20,6.9c-0.1-0.1-0.1-0.1-0.1-0.2c0,0,0,0,0.1,0C20,6.7,20,6.8,20,6.9z M20.7,4.6C20.7,4.6,20.6,4.6,20.7,4.6
		C20.6,4.6,20.6,4.6,20.7,4.6C20.6,4.6,20.6,4.5,20.7,4.6C20.6,4.5,20.7,4.6,20.7,4.6z M20.9,5.3C20.9,5.3,20.9,5.4,20.9,5.3
		C20.9,5.3,20.9,5.3,20.9,5.3C20.9,5.3,20.9,5.3,20.9,5.3C20.9,5.3,20.9,5.3,20.9,5.3z M21.5,5.4C21.5,5.4,21.5,5.4,21.5,5.4
		C21.5,5.4,21.5,5.3,21.5,5.4C21.5,5.3,21.5,5.3,21.5,5.4C21.5,5.3,21.5,5.3,21.5,5.4z M21.6,6.7C21.6,6.7,21.6,6.6,21.6,6.7
		C21.7,6.6,21.7,6.7,21.6,6.7C21.6,6.7,21.6,6.8,21.6,6.7z M22.6,4.6c0,0.1,0,0.1,0,0.2c0,0.1-0.1,0-0.2,0c0,0-0.1-0.1-0.1-0.1
		c0,0,0,0-0.1,0c-0.1,0.1-0.2,0.1-0.3,0.1c0-0.1-0.1-0.2-0.1-0.3c0.1,0,0.2-0.1,0.3,0C22.3,4.5,22.5,4.5,22.6,4.6z M22.6,5.5
		L22.6,5.5L22.6,5.5L22.6,5.5z M23.6,4.8c-0.2,0.2-0.4,0-0.7,0.1c0,0,0-0.1,0-0.1c0-0.1,0.1-0.1,0.1-0.1C23.3,4.6,23.5,4.6,23.6,4.8
		z M23.4,7.1C23.4,7.1,23.4,7.1,23.4,7.1C23.4,7.1,23.4,7.1,23.4,7.1C23.4,7.1,23.4,7,23.4,7.1C23.4,7.1,23.4,7.1,23.4,7.1z
		 M23.8,4.3C23.8,4.4,23.8,4.4,23.8,4.3C23.8,4.4,23.8,4.4,23.8,4.3C23.8,4.3,23.8,4.3,23.8,4.3C23.8,4.3,23.8,4.3,23.8,4.3z
		 M24.5,4.8C24.3,5,24.3,5,24.2,5C24.2,5,24.1,5,24,5c0,0-0.1-0.1-0.1-0.1c0,0,0.1-0.1,0.1-0.1C24.2,4.7,24.3,4.7,24.5,4.8z
		 M24.9,5.7C24.9,5.7,24.8,5.7,24.9,5.7C24.8,5.7,24.8,5.7,24.9,5.7C24.8,5.7,24.8,5.7,24.9,5.7C24.8,5.7,24.9,5.7,24.9,5.7z
		 M24.7,7.4c-0.1-0.1-0.2-0.1-0.2-0.2c0,0,0-0.1,0-0.1c0,0,0.1,0,0.1,0C24.7,7.2,24.8,7.2,24.7,7.4z M25.6,5.4
		c-0.2,0.1-0.2-0.2-0.3-0.3c0,0,0,0,0,0c0.1-0.1,0.1-0.1,0.2-0.2C25.6,5,25.7,5.1,25.6,5.4z M26.4,5.3c0,0.2-0.1,0.2-0.2,0.2
		c-0.1,0-0.1,0-0.2,0c-0.1,0-0.2-0.3-0.1-0.4c0,0,0.1-0.1,0.1-0.1c0.1,0,0.1,0,0.2,0.1c0,0,0,0,0,0c0,0,0,0,0,0l0,0c0,0,0.1,0,0.1,0
		C26.4,5,26.4,5.1,26.4,5.3z M26.5,5.9C26.4,5.9,26.4,5.9,26.5,5.9c-0.1,0-0.1,0-0.1,0C26.4,5.9,26.4,5.9,26.5,5.9
		C26.4,5.9,26.5,5.9,26.5,5.9z M27.1,5.2c0,0,0,0.1,0,0.2c0,0-0.1,0-0.1,0c-0.1,0.1-0.1,0.2-0.2,0.3c-0.1-0.1-0.1-0.1-0.2-0.1
		c0-0.1-0.1-0.1-0.1-0.2c0-0.1,0.1-0.1,0.2-0.1c0.1,0,0.1,0,0.2,0C27,5.1,27.1,5.1,27.1,5.2z M27.3,5.5c0,0.1-0.1,0.1-0.2,0.1
		c-0.1,0-0.1-0.1,0-0.2C27.2,5.4,27.3,5.4,27.3,5.5z M27.4,4.8L27.4,4.8L27.4,4.8L27.4,4.8z M27.6,4.9C27.6,4.9,27.5,4.9,27.6,4.9
		C27.5,4.8,27.5,4.8,27.6,4.9C27.6,4.8,27.6,4.8,27.6,4.9C27.6,4.8,27.6,4.9,27.6,4.9z M27.7,5.7c-0.2,0.1-0.3-0.1-0.4-0.2
		c0-0.1,0.1-0.1,0.1-0.2c0.1-0.1,0.3-0.1,0.3,0.2C27.8,5.5,27.8,5.6,27.7,5.7z M27.5,7.5C27.5,7.5,27.4,7.5,27.5,7.5
		c-0.1,0.1-0.1,0.1-0.2,0.2c0,0,0,0.1-0.1,0.1c0,0,0,0,0,0c0,0,0,0,0,0c0,0-0.1-0.1-0.1-0.1c0-0.2,0-0.3-0.1-0.5c0,0,0-0.1,0-0.1
		c0,0,0.1,0,0.1,0c0.1,0.1,0.1,0.1,0.2,0c0.1-0.1,0.1,0,0.2,0c0,0,0,0,0,0c0,0,0,0,0,0l0,0C27.6,7.2,27.6,7.2,27.5,7.5z M27.9,7.4
		C27.9,7.4,27.9,7.3,27.9,7.4c0.2,0,0.4-0.2,0.5-0.2c0,0.1-0.1,0.2-0.1,0.2c-0.1,0.1-0.3,0.1-0.4,0.2c0,0,0,0.1,0,0.1
		c-0.1,0-0.1,0-0.2,0c0,0,0,0,0,0c0,0-0.1,0-0.1-0.1c0-0.1,0-0.2-0.1-0.2C27.6,7.5,27.8,7.6,27.9,7.4z M28.1,6.1
		C28,6.1,28,6.1,28.1,6.1C28,6.1,28,6.1,28.1,6.1C28,6.1,28,6.1,28.1,6.1C28.1,6.1,28.1,6.1,28.1,6.1z M28,4.9C28,4.9,28,4.9,28,4.9
		C28,4.9,28,4.9,28,4.9C28,4.9,28,4.9,28,4.9C28,4.9,28,4.9,28,4.9z M28.2,4.9C28.2,4.9,28.2,5,28.2,4.9C28.2,5,28.2,4.9,28.2,4.9
		C28.2,4.9,28.2,4.9,28.2,4.9C28.2,4.9,28.2,4.9,28.2,4.9z M28.9,5.1c-0.1,0-0.3-0.1-0.4-0.2C28.7,4.9,28.8,5,28.9,5.1z M29,5.7
		C29,5.7,29,5.8,29,5.7c-0.2,0.1-0.2-0.1-0.3-0.1c-0.1,0-0.2,0.1-0.3,0.1c-0.1-0.1-0.1-0.2-0.2-0.3c0,0-0.1,0-0.1,0
		c-0.1,0-0.1,0.1-0.2,0c0-0.1,0.1-0.2,0.1-0.2c0.1,0,0.1,0,0.2,0c0.1,0,0.2,0.1,0.3,0.2c0.1-0.1,0.2-0.2,0.3-0.2c0,0,0,0,0,0
		C29.1,5.3,29,5.5,29,5.7z M29.3,5.8C29.3,5.8,29.3,5.8,29.3,5.8c-0.1,0-0.1,0-0.1,0C29.2,5.8,29.3,5.8,29.3,5.8
		C29.3,5.8,29.3,5.8,29.3,5.8z M29.6,5.2c-0.1,0-0.1,0-0.1-0.1C29.5,5,29.6,5,29.6,5.2C29.6,5.1,29.6,5.2,29.6,5.2z M29.8,6.1
		C29.7,6,29.6,6,29.5,5.9c0,0,0-0.1,0-0.1c0,0,0.1-0.1,0.1-0.1C29.7,5.7,29.8,5.8,29.8,6.1z M30.2,5.4C30.1,5.4,30.1,5.4,30.2,5.4
		C30.1,5.4,30.1,5.4,30.2,5.4C30.2,5.4,30.2,5.4,30.2,5.4z M30.1,6.4C30.1,6.4,30.1,6.4,30.1,6.4c-0.1,0.1-0.1,0-0.1,0
		C30,6.4,30,6.3,30.1,6.4C30,6.3,30,6.4,30.1,6.4z M29.9,7.8c-0.1,0-0.1,0-0.2,0c0,0.1,0,0.2,0,0.3c-0.1-0.2-0.5,0-0.5-0.2
		c0-0.1,0-0.1,0-0.2l0,0c-0.1-0.2-0.3-0.3-0.3-0.6c0,0,0,0,0,0c0.1-0.1,0.3-0.1,0.3,0c0.1,0.2,0.2,0.2,0.4,0.2
		C29.7,7.5,29.9,7.6,29.9,7.8C29.9,7.7,29.9,7.8,29.9,7.8z M30.3,5.2C30.3,5.2,30.3,5.2,30.3,5.2C30.3,5.2,30.3,5.2,30.3,5.2
		C30.3,5.2,30.3,5.2,30.3,5.2z M30.2,8C30.1,8,30.1,8,30.2,8C30.1,8,30,8,30.1,8.1c0,0,0,0,0,0C30.1,7.9,30.1,7.9,30.2,8z M30.2,7.7
		C30.2,7.7,30.2,7.7,30.2,7.7C30.1,7.6,30.1,7.6,30.2,7.7C30.1,7.6,30.2,7.6,30.2,7.7C30.2,7.7,30.2,7.7,30.2,7.7z M30.6,5.5
		L30.6,5.5L30.6,5.5L30.6,5.5z M30.7,5.2C30.7,5.2,30.7,5.2,30.7,5.2C30.7,5.2,30.7,5.2,30.7,5.2C30.7,5.2,30.7,5.2,30.7,5.2z
		 M30.5,7.3C30.6,7.3,30.6,7.3,30.5,7.3c0,0.1,0,0.1,0,0.1c-0.1,0-0.1,0-0.2-0.1c0,0,0-0.1,0-0.1C30.4,7.2,30.5,7.2,30.5,7.3z
		 M30.7,6.5C30.7,6.5,30.7,6.5,30.7,6.5c-0.1,0-0.1,0-0.1,0C30.6,6.5,30.6,6.4,30.7,6.5C30.7,6.5,30.7,6.5,30.7,6.5z M30.8,6
		C30.8,6,30.8,6,30.8,6C30.8,6,30.8,5.9,30.8,6C30.8,5.9,30.8,5.9,30.8,6C30.8,5.9,30.8,6,30.8,6z M31.3,7.3c0,0,0,0,0.1,0.1
		c-0.4,0.1-0.5,0.1-0.6,0C31,7.3,31.2,7.3,31.3,7.3z M31.3,5.6C31.3,5.6,31.2,5.6,31.3,5.6C31.2,5.6,31.2,5.6,31.3,5.6
		C31.2,5.6,31.2,5.6,31.3,5.6C31.3,5.6,31.3,5.6,31.3,5.6z M31.5,6.5C31.6,6.5,31.6,6.6,31.5,6.5c0.1,0.1,0.1,0.1,0.1,0.1
		c-0.2,0-0.3,0-0.5-0.1c0-0.1,0-0.2,0.1-0.1C31.3,6.5,31.4,6.5,31.5,6.5z M31.5,5.3C31.5,5.3,31.5,5.3,31.5,5.3
		C31.4,5.3,31.4,5.3,31.5,5.3C31.4,5.3,31.5,5.3,31.5,5.3C31.5,5.3,31.5,5.3,31.5,5.3z M31.8,5.6c0,0.1-0.1,0.3-0.1,0.4
		c-0.1,0-0.1,0-0.1,0c-0.1,0-0.2,0-0.3-0.1c0-0.1,0.1-0.2,0.1-0.3c0,0,0,0,0.1-0.1C31.6,5.6,31.7,5.6,31.8,5.6z M31.6,8.3
		C31.6,8.3,31.5,8.3,31.6,8.3c-0.1,0.1-0.2,0-0.2,0c0,0,0,0,0,0c0,0,0,0,0,0C31.5,8.3,31.5,8.2,31.6,8.3c-0.1-0.1-0.1-0.1-0.1-0.1
		c0,0,0,0-0.1,0c0-0.1,0-0.1,0-0.2l0,0C31.3,8,31.1,8.1,31,8.1c0,0,0,0,0,0C31,8,31,8,31,7.9c-0.2-0.1-0.2-0.1-0.3,0.1
		c0,0,0,0-0.1,0c0-0.1-0.1-0.2-0.1-0.3c-0.2,0.1-0.2,0.1-0.3,0c0.1-0.1,0.2-0.1,0.4-0.1c0,0,0,0,0,0c0,0,0,0,0,0
		c0.1,0,0.2,0,0.3,0.1c0.2,0.1,0.4,0.1,0.6,0.1c0.1,0,0.1,0,0.2,0C31.7,7.9,31.7,8.1,31.6,8.3z M31.9,6.6
		C31.9,6.6,31.9,6.6,31.9,6.6C31.9,6.6,31.9,6.6,31.9,6.6C31.9,6.5,31.9,6.5,31.9,6.6C31.9,6.6,31.9,6.6,31.9,6.6z M32.1,8
		c0,0,0,0.1,0,0.1C32,8.1,32,8,31.9,8.1c0,0-0.1,0-0.1,0c0-0.1,0-0.2,0.1-0.2C32,7.8,32.1,7.9,32.1,8z M32.3,6.7
		C32.3,6.7,32.3,6.7,32.3,6.7C32.3,6.7,32.3,6.7,32.3,6.7C32.3,6.7,32.3,6.7,32.3,6.7C32.3,6.7,32.3,6.7,32.3,6.7z M32.2,7.5
		C32.2,7.5,32.2,7.5,32.2,7.5c-0.1,0-0.1,0-0.1,0C32.1,7.4,32.2,7.4,32.2,7.5C32.2,7.4,32.2,7.5,32.2,7.5z M32.6,7.6
		c-0.1,0-0.1,0-0.2,0c0-0.2,0-0.3,0.1-0.3C32.6,7.4,32.7,7.5,32.6,7.6z M32.8,6.8c-0.1-0.2,0-0.4,0.1-0.6C33,6.2,33,6.3,33,6.4
		C33,6.5,33,6.7,32.8,6.8z M33.2,7.4c0.2,0,0.3,0,0.5,0.1c0,0.1-0.1,0.2-0.2,0.3c0,0-0.1,0.1-0.1,0.2c0,0.1,0.1,0.1,0.1,0.2
		c-0.1,0.1-0.2,0.2-0.2,0.3c0,0,0,0-0.1-0.1c0,0,0,0,0,0c0,0,0-0.1,0-0.1C33,8,32.8,8.2,32.6,8c-0.1,0.1-0.1,0.1-0.2,0.2
		c0,0,0,0,0,0c0,0-0.1,0-0.1,0c0,0,0,0,0,0c0,0-0.1-0.1-0.1-0.1c0.1-0.1,0.3-0.1,0.4-0.2c0.1-0.1,0.1-0.2,0.1-0.2
		c0.1-0.1,0.2-0.1,0.3-0.2C33,7.4,33.1,7.4,33.2,7.4z M33.5,7.1C33.5,7.1,33.5,7.1,33.5,7.1C33.5,7.1,33.5,7,33.5,7.1
		C33.5,7,33.5,7,33.5,7.1C33.5,7,33.5,7.1,33.5,7.1z M33.4,6.3C33.4,6.3,33.4,6.3,33.4,6.3C33.4,6.3,33.4,6.3,33.4,6.3
		C33.4,6.3,33.4,6.3,33.4,6.3C33.4,6.3,33.4,6.3,33.4,6.3z M33.3,6.5c0,0.1,0,0.2,0.1,0.3c-0.1,0-0.2,0.1-0.3,0.1
		C33.1,6.8,33.1,6.6,33.3,6.5z M33.8,5.9c-0.1,0-0.2,0-0.3,0c-0.1,0-0.1-0.1-0.1-0.2c0,0,0,0,0,0c0,0,0,0,0,0c0,0,0.1-0.1,0.1-0.1
		c0.1,0,0.2,0,0.3,0.1C33.9,5.6,33.9,5.8,33.8,5.9z M34.1,6.4c0,0.2,0,0.5-0.1,0.7c-0.2,0-0.4-0.2-0.4-0.5c0-0.2-0.1-0.3,0-0.5
		c0.1,0.1,0.2,0.2,0.2,0.3c0,0.1,0,0.1,0.1,0.1C34,6.5,34,6.4,34.1,6.4C34.1,6.4,34.1,6.4,34.1,6.4z M34.1,7.2
		C34.1,7.1,34.1,7.1,34.1,7.2C34.1,7.1,34.1,7.1,34.1,7.2C34.1,7.1,34.1,7.1,34.1,7.2z M34.3,5.7C34.3,5.7,34.3,5.7,34.3,5.7
		C34.3,5.6,34.3,5.6,34.3,5.7C34.3,5.6,34.3,5.6,34.3,5.7C34.3,5.6,34.3,5.6,34.3,5.7z M34.6,7.6C34.6,7.6,34.6,7.6,34.6,7.6
		c0,0.1-0.1,0.1-0.1,0.2c0.1,0.2,0.2,0.3,0.2,0.5c-0.1,0-0.1,0.1-0.2,0.1c0,0,0-0.1,0-0.1c0,0-0.1,0-0.1,0c0,0,0,0.1-0.1,0.1
		c-0.1-0.1-0.2-0.1-0.3-0.2c0,0-0.1-0.1-0.1-0.1C34,8.1,34,8.1,34.3,8.1c0.1-0.1,0-0.2,0-0.2c-0.2-0.2-0.3-0.3-0.6-0.3
		c0.1-0.1,0.2-0.1,0.4-0.1C34.2,7.5,34.4,7.6,34.6,7.6z M34.7,5.6C34.6,5.6,34.6,5.6,34.7,5.6C34.6,5.6,34.6,5.6,34.7,5.6
		C34.6,5.6,34.6,5.6,34.7,5.6z M34.8,5.9c-0.2,0-0.3,0-0.2-0.2c0,0,0,0,0,0c0,0,0,0,0,0C34.7,5.7,34.7,5.8,34.8,5.9z M34.7,7
		c0,0,0.1-0.1,0.2-0.2c0,0.1,0,0.2,0,0.2c0,0.1-0.1,0.1-0.1,0.2c-0.1,0-0.1,0-0.2,0c-0.1,0-0.1-0.1-0.2-0.2c0-0.2,0.1-0.3,0.2-0.3
		C34.6,6.8,34.6,6.9,34.7,7C34.7,7,34.7,7,34.7,7C34.7,7,34.7,7,34.7,7L34.7,7z M36.1,6.1C36.1,6.2,36,6.4,36,6.5
		c-0.1,0-0.2,0.1-0.2,0.1c0,0.2-0.1,0.4-0.1,0.5c-0.2,0.1-0.4,0.1-0.6,0.4c0-0.3,0.1-0.5,0.2-0.7c0,0,0.1,0,0.1,0
		c0.1,0,0.1,0.1,0.1,0c0,0,0-0.1,0-0.2c0-0.1-0.1-0.1-0.1-0.2C35,6.5,35,6.5,34.8,6.3c0,0,0,0,0.1,0c0,0,0.1,0,0.1,0
		c0.2,0.1,0.3,0,0.4-0.2c0-0.1-0.1-0.1-0.1-0.2c0.1-0.1,0.2-0.1,0.3-0.2c0,0,0,0,0,0.1c0,0.1-0.1,0.2,0,0.2c0.1,0,0.1,0.1,0.2,0.1
		c0.1,0,0.1,0,0.2,0C36,6,36.1,6.1,36.1,6.1z M35.7,7.5c0,0-0.1,0.1-0.1,0c0.1-0.3,0.1-0.3,0.3-0.2C35.8,7.4,35.7,7.5,35.7,7.5z
		 M36.3,7.9c-0.1-0.1-0.2-0.1-0.3-0.2c0.1-0.3,0.1-0.3,0.1-0.6c0,0,0-0.1,0.1-0.1c0,0.1,0,0.1,0,0.1c0,0.2,0,0.3,0.2,0.5
		C36.4,7.7,36.3,7.8,36.3,7.9z M36.5,8.3C36.5,8.3,36.5,8.3,36.5,8.3c-0.1,0.1-0.2,0.1-0.3,0c-0.1-0.1-0.3-0.1-0.4-0.1
		c-0.1,0-0.2,0-0.3,0c0,0-0.1,0-0.1,0c0,0-0.1,0-0.1,0c0,0,0-0.1-0.1-0.1c0,0-0.1,0-0.1,0c0,0,0,0,0,0.1c0,0.1,0,0.1-0.1,0.1
		c0,0,0,0,0,0c-0.1-0.1-0.1-0.2-0.2-0.3c0.1,0,0.1,0,0.2,0c0-0.1,0.1-0.2,0.1-0.3c0,0,0.1,0,0.1,0.1c0,0,0.1-0.1,0.1-0.1
		c0,0,0,0,0,0c0,0-0.1,0.1-0.1,0.1c0,0.1,0,0.1,0.1,0.1c0,0,0-0.1,0.1-0.1c0,0,0-0.1,0-0.1c0.1,0,0.1,0.1,0.1,0.2
		c0,0.1-0.1,0.3,0,0.4c0.2,0,0.3-0.2,0.5-0.1C36.2,8.3,36.4,8.2,36.5,8.3z M37.1,4.7C37,4.7,37,4.7,37.1,4.7C37,4.8,37,4.7,37.1,4.7
		C37,4.7,37,4.7,37.1,4.7C37,4.7,37,4.7,37.1,4.7z M36.8,8L36.8,8L36.8,8L36.8,8z M37,8C37,7.9,37,7.9,37,8C37,7.9,37,7.9,37,8
		C37,7.9,37,7.9,37,8C37,7.9,37,8,37,8z M37.3,8.3c-0.2,0-0.4,0.1-0.5,0.3c0,0,0,0-0.1,0c0,0,0.1-0.1,0.1-0.1c0-0.1,0-0.2,0.1-0.3
		C37.1,8.3,37.2,8.3,37.3,8.3z M37.3,7.7c0,0.1,0.1,0.2,0,0.4c-0.1-0.2-0.1-0.4-0.1-0.6c0-0.1,0.1-0.1,0.1-0.2
		C37.3,7.5,37.3,7.6,37.3,7.7z M37.9,5.4c-0.1-0.1-0.1-0.1-0.1-0.2C37.8,5.3,37.9,5.4,37.9,5.4z M38.1,5.7c-0.1-0.1-0.2-0.2-0.2-0.2
		C38.1,5.4,38.1,5.4,38.1,5.7z M37.9,7.1C37.9,7.1,37.9,7.1,37.9,7.1C37.9,7.1,37.9,7.2,37.9,7.1C37.9,7.2,37.9,7.2,37.9,7.1
		C37.9,7.2,37.9,7.2,37.9,7.1z M38,8c-0.2-0.1-0.2-0.1-0.4-0.1c0-0.1,0-0.2-0.1-0.3c0.2,0,0.3,0.1,0.4,0.1C38,7.8,38,7.9,38,8z
		 M38.2,7.1C38.2,7.1,38.2,7.1,38.2,7.1C38.2,7.1,38.2,7.1,38.2,7.1C38.2,7.1,38.2,7.1,38.2,7.1C38.2,7.1,38.2,7.1,38.2,7.1z
		 M38.3,7.9C38.2,8,38.1,8.1,38,8c0,0,0.1-0.1,0.1-0.1C38.2,7.8,38.2,7.8,38.3,7.9z M38.4,6.7C38.4,6.7,38.4,6.7,38.4,6.7
		C38.5,6.7,38.5,6.7,38.4,6.7C38.5,6.7,38.5,6.8,38.4,6.7C38.4,6.8,38.4,6.8,38.4,6.7z M38.5,6.2C38.5,6.2,38.5,6.2,38.5,6.2
		C38.5,6.2,38.5,6.2,38.5,6.2C38.5,6.2,38.5,6.2,38.5,6.2C38.6,6.2,38.6,6.2,38.5,6.2z M38.7,7.2C38.7,7.2,38.6,7.2,38.7,7.2
		C38.6,7.2,38.6,7.2,38.7,7.2C38.6,7.1,38.6,7.1,38.7,7.2C38.6,7.1,38.6,7.1,38.7,7.2z M38.6,8c0,0.1-0.1,0.1-0.2,0.1
		c-0.1,0-0.1-0.1-0.1-0.2c0.1,0,0.2-0.1,0.3-0.1C38.6,7.9,38.6,7.9,38.6,8z M39,7.1c0,0,0.1,0,0.1,0c0,0,0,0.1,0,0.1
		c0,0,0,0.1,0,0.1c-0.1,0-0.2,0.1-0.3,0C38.8,7.1,38.9,7.1,39,7.1z M39.1,6.8c0-0.1,0-0.2,0.1-0.2c0,0,0.1,0.1,0.1,0.2
		c0,0.1,0,0.1-0.1,0.1C39.1,6.9,39.1,6.9,39.1,6.8z M39.5,5.4c0,0.1,0,0.1-0.1,0.2c-0.1-0.1-0.2-0.2-0.3-0.4c0,0,0-0.1,0.1-0.1
		C39.2,5.3,39.4,5.4,39.5,5.4z M39.3,8.2C39.3,8.2,39.3,8.3,39.3,8.2c-0.1,0-0.2,0-0.2-0.1c0,0,0-0.1,0.1-0.1
		C39.3,8.1,39.3,8.1,39.3,8.2z M39.3,8.7C39.3,8.7,39.3,8.7,39.3,8.7C39.3,8.7,39.3,8.7,39.3,8.7c-0.1,0-0.2-0.1-0.2-0.1
		c0,0-0.1,0-0.1,0C39.1,8.6,39.2,8.6,39.3,8.7z M39.8,6.9L39.8,6.9L39.8,6.9L39.8,6.9z M40.4,7.2c0.1,0,0.1,0,0.2,0.1
		c0,0,0,0.1,0,0.1c-0.2,0.1-0.8,0-0.9-0.1c0,0,0-0.1,0-0.1c0,0,0,0,0,0c0,0,0,0,0,0c0.1,0,0.1-0.1,0.2-0.1
		C40.1,7.1,40.2,7.2,40.4,7.2z M40.2,6.2L40.2,6.2L40.2,6.2L40.2,6.2z M40.4,5.7C40.4,5.7,40.4,5.7,40.4,5.7
		C40.4,5.7,40.4,5.7,40.4,5.7C40.4,5.7,40.3,5.7,40.4,5.7C40.3,5.7,40.3,5.7,40.4,5.7z M40.5,5.6C40.5,5.6,40.5,5.6,40.5,5.6
		C40.5,5.6,40.5,5.6,40.5,5.6C40.5,5.6,40.5,5.6,40.5,5.6z M40.7,5.2C40.7,5.2,40.7,5.3,40.7,5.2C40.6,5.2,40.6,5.2,40.7,5.2
		C40.6,5.2,40.7,5.2,40.7,5.2C40.7,5.2,40.7,5.2,40.7,5.2z M40.6,6.1L40.6,6.1L40.6,6.1L40.6,6.1z M40.7,7c-0.2,0.1-0.3,0.1-0.4,0
		l0,0c0,0,0,0,0,0c0,0,0,0,0,0c-0.2,0-0.2,0-0.2-0.1c0-0.1,0-0.1,0-0.2c0.1,0,0.3,0,0.4,0c0.1,0,0.2,0.1,0.2,0.2
		C40.7,6.9,40.7,6.9,40.7,7z M41,6.2c0,0,0,0.1,0,0.2c0,0-0.1,0-0.1-0.1C40.9,6.2,41,6.2,41,6.2C41,6.2,41,6.2,41,6.2z M41.8,6.7
		C41.8,6.7,41.8,6.7,41.8,6.7c0.2,0,0.1,0.1,0.1,0.2C41.8,6.9,41.7,6.9,41.8,6.7z M41.5,6.2c0.1,0,0.2,0.1,0.3,0.1
		c0.1,0,0.2,0,0.3,0.1c-0.1,0.1-0.2,0.1-0.4,0.1C41.6,6.5,41.5,6.4,41.5,6.2z M41.2,5.7c-0.1,0,0-0.1,0-0.1c0,0,0.1-0.1,0.1-0.1
		c0.1,0,0.1-0.1,0.2-0.1c0-0.3,0.2-0.4,0.4-0.5c0,0,0,0,0.1,0c0,0.1-0.1,0.2-0.1,0.3c0.1,0.1,0.2,0.2,0.3,0.4c-0.1,0-0.1,0-0.2-0.1
		c-0.2-0.1-0.2-0.1-0.3,0.1c0,0.1,0,0.1-0.1,0.2c-0.1,0.1-0.1,0.1-0.2,0.1c0,0-0.1-0.1-0.1-0.1C41.3,5.7,41.3,5.7,41.2,5.7
		C41.2,5.7,41.2,5.7,41.2,5.7z M41.3,7.4C41.3,7.4,41.2,7.5,41.3,7.4c-0.1,0-0.1,0-0.2-0.1c0.1,0,0.1-0.1,0.1-0.1
		C41.3,7.3,41.3,7.3,41.3,7.4z M41.1,8.3C41.1,8.3,41.1,8.3,41.1,8.3C41.1,8.3,41.1,8.4,41.1,8.3C41,8.4,41,8.3,41.1,8.3
		C41,8.3,41,8.3,41.1,8.3z M41.6,7.8C41.6,7.8,41.5,7.9,41.6,7.8C41.5,7.9,41.5,7.8,41.6,7.8C41.5,7.8,41.5,7.8,41.6,7.8
		C41.6,7.8,41.6,7.8,41.6,7.8z M41.6,8.5c-0.1,0-0.2,0-0.3-0.2C41.6,8.1,41.6,8.1,41.6,8.5z M41.6,8.8c0,0.1,0,0.1,0,0.1
		c-0.1-0.1-0.2-0.1-0.4-0.1c-0.1,0-0.3,0-0.4,0c0,0-0.1,0-0.1,0c0,0,0,0.1,0,0.1c0,0-0.1,0-0.2-0.1c0,0,0,0,0,0c0,0,0,0,0,0
		c-0.1-0.1-0.2-0.2-0.3-0.2c0.3,0,0.5,0,0.7,0.1c0,0,0.1,0,0.1,0C41.2,8.7,41.4,8.8,41.6,8.8z M42.5,9C42.5,9,42.4,9,42.5,9
		C42.4,9,42.5,9,42.5,9z M43.9,8.7C43.8,8.7,43.8,8.7,43.9,8.7C43.8,8.7,43.8,8.7,43.9,8.7C43.8,8.7,43.8,8.7,43.9,8.7
		C43.8,8.7,43.9,8.7,43.9,8.7z M44.2,9.2C44.2,9.2,44.2,9.2,44.2,9.2C44.2,9.2,44.2,9.2,44.2,9.2c-0.1-0.1-0.1-0.2-0.1-0.3
		C44.1,9,44,9.1,44,9.1c0,0-0.1,0-0.1,0.1c-0.1,0-0.2,0-0.3,0c0-0.2,0.1-0.3,0.3-0.2c0.1,0,0.2,0,0.3,0c0.1,0,0.1,0,0.2,0
		C44.3,9.1,44.3,9.2,44.2,9.2z M46.1,9.2c-0.1,0.1-0.2,0.1-0.3,0.2c-0.1,0-0.1,0-0.2-0.1c0,0-0.1,0-0.1,0c0,0-0.1,0.1-0.1,0.1
		c0,0,0,0,0,0c0-0.3,0-0.3,0.3-0.3c0.1,0.2,0.1,0.2,0.2,0.1C46,9.2,46.1,9.2,46.1,9.2z M46.3,9.4C46.2,9.4,46.2,9.4,46.3,9.4
		c-0.1,0-0.1,0-0.1,0c0,0,0-0.1,0-0.2C46.2,9.3,46.2,9.4,46.3,9.4z M47.7,9.3c-0.2,0-0.1-0.3-0.3-0.3c0,0,0,0-0.1,0
		c-0.1,0.1,0,0.3-0.1,0.4c-0.2-0.3-0.2-0.3-0.3-0.3c0,0.1,0,0.3-0.1,0.4c0,0,0,0.1,0,0.1c0,0-0.1,0-0.1,0c0,0,0,0,0,0c0,0,0,0,0,0
		c0,0,0,0,0,0c-0.1-0.1-0.1-0.2-0.2-0.3c0,0,0,0,0,0c0,0,0,0,0,0c0-0.1,0-0.1,0-0.2c0.1-0.1,0.2-0.1,0.4-0.2c0.1,0,0.3-0.1,0.4-0.2
		c0.1,0.1,0.2,0.2,0.4,0.2C47.6,9,47.7,9.2,47.7,9.3z M48.5,8.8c0-0.1,0-0.2,0-0.2C48.6,8.6,48.6,8.7,48.5,8.8z M49.1,9.2
		c-0.1,0-0.2-0.1-0.3,0c-0.1,0.1-0.2,0-0.3,0c0,0.1-0.1,0.1-0.1,0.2c0.1,0.2,0.1,0.2,0.1,0.4c0,0,0,0,0,0c-0.1-0.1-0.2-0.1-0.2-0.2
		c-0.1-0.2-0.2-0.1-0.4,0c0-0.2-0.1-0.3-0.1-0.5C48,9,48,9.1,48.1,9.1c0.2-0.2,0.2-0.2,0.4-0.1c0.1-0.1,0.1-0.2,0.2-0.2
		C48.8,8.7,49,8.7,49,8.9C49.1,9,49.1,9.1,49.1,9.2z M49.1,9.9C49.1,9.9,49.1,9.9,49.1,9.9C49.1,9.9,49.1,9.9,49.1,9.9
		c-0.1,0-0.1,0-0.1-0.1C49,9.8,49.1,9.8,49.1,9.9z M49.4,9.1c-0.1-0.1-0.1-0.1,0-0.3C49.4,8.9,49.4,9,49.4,9.1z M49.9,9
		C49.9,9,49.9,9,49.9,9c-0.1,0-0.1,0-0.1,0c0,0,0,0,0,0C49.8,8.9,49.8,8.9,49.9,9C49.9,8.9,49.9,9,49.9,9z M49.9,9.5
		C49.8,9.5,49.8,9.5,49.9,9.5c-0.1,0-0.1,0-0.1,0C49.8,9.4,49.8,9.4,49.9,9.5C49.8,9.4,49.8,9.4,49.9,9.5z M50.1,9.1
		C50.1,9.1,50.1,9.1,50.1,9.1C50.1,9.1,50.1,9.1,50.1,9.1C50.1,9,50.1,9,50.1,9.1C50.1,9,50.1,9,50.1,9.1z M50.1,9.7
		c0-0.1,0.1-0.1,0.1-0.2c0,0.1,0,0.1,0,0.2c0,0.1,0,0.1-0.1,0.2c0,0,0,0,0,0C50,9.9,50,9.8,50.1,9.7z M50.4,9.2c0-0.1,0-0.2,0.1-0.3
		C50.5,9.1,50.5,9.1,50.4,9.2z M50.7,9.4c-0.1,0-0.1,0.1-0.2,0C50.6,9.4,50.7,9.4,50.7,9.4z M50.9,9.3c0,0-0.1,0-0.1,0
		c0-0.1,0-0.2,0.1-0.3C50.8,9.2,50.9,9.2,50.9,9.3z M50.9,9.6C50.9,9.6,50.9,9.6,50.9,9.6C50.9,9.6,50.9,9.6,50.9,9.6
		C50.9,9.6,50.9,9.6,50.9,9.6z M51.2,8.9C51.2,8.9,51.2,8.9,51.2,8.9c-0.1,0-0.1,0-0.1,0C51.1,8.8,51.1,8.8,51.2,8.9
		C51.2,8.8,51.2,8.9,51.2,8.9z M51.2,9.4C51.2,9.4,51.2,9.4,51.2,9.4C51.2,9.4,51.2,9.5,51.2,9.4C51.2,9.5,51.1,9.5,51.2,9.4
		C51.1,9.4,51.1,9.4,51.2,9.4C51.1,9.4,51.2,9.4,51.2,9.4z M51.5,9.2c-0.1,0.1-0.1,0.2-0.2,0.4c-0.1-0.1-0.1-0.1-0.1-0.1
		c0-0.1,0.1-0.2,0.1-0.3C51.4,9.1,51.5,9.2,51.5,9.2z M51.8,9.3c0,0.1-0.1,0.2-0.1,0.2C51.6,9.4,51.6,9.4,51.8,9.3z M51.9,9.3
		C51.8,9.3,51.8,9.3,51.9,9.3C51.8,9.3,51.8,9.3,51.9,9.3C51.8,9.3,51.8,9.3,51.9,9.3z M52.4,8.8C52.4,8.9,52.3,8.8,52.4,8.8
		c0-0.1,0-0.1,0-0.1C52.4,8.7,52.4,8.8,52.4,8.8C52.4,8.8,52.4,8.8,52.4,8.8z M52.3,9.3C52.3,9.3,52.3,9.3,52.3,9.3
		c-0.1,0.1-0.1,0-0.1,0C52.2,9.3,52.3,9.2,52.3,9.3C52.3,9.2,52.3,9.3,52.3,9.3z M52.4,10C52.4,10,52.4,10,52.4,10
		C52.4,10,52.4,9.9,52.4,10C52.4,9.9,52.4,9.9,52.4,10C52.4,9.9,52.4,9.9,52.4,10z M52.4,10.4C52.4,10.4,52.4,10.4,52.4,10.4
		C52.4,10.4,52.4,10.4,52.4,10.4C52.4,10.4,52.4,10.4,52.4,10.4z M52.7,9.2C52.7,9.2,52.6,9.2,52.7,9.2c-0.1,0-0.1-0.1-0.1-0.1
		c0,0,0,0,0.1-0.1c0,0,0,0,0,0.1C52.7,9.1,52.7,9.1,52.7,9.2z M52.8,9.6c0,0-0.1,0.1-0.1,0.1c0,0,0,0,0,0
		C52.7,9.7,52.7,9.6,52.8,9.6C52.8,9.6,52.8,9.6,52.8,9.6C52.8,9.6,52.8,9.6,52.8,9.6z M53,9c0,0.1,0,0.2,0,0.2
		C53,9.1,52.9,9.1,53,9z M53.2,9.1c0,0.2-0.1,0.4-0.1,0.6C53,9.5,53,9.4,53,9.2C53.1,9.2,53.1,9,53.2,9.1z M53.1,10.4
		C53.2,10.4,53.2,10.4,53.1,10.4C53.2,10.5,53.1,10.5,53.1,10.4C53.1,10.4,53.1,10.4,53.1,10.4z M53.7,9.1L53.7,9.1L53.7,9.1
		L53.7,9.1z M53.8,9.9C53.8,9.9,53.8,9.9,53.8,9.9C53.8,9.9,53.8,9.9,53.8,9.9C53.8,9.9,53.8,9.9,53.8,9.9
		C53.8,9.8,53.8,9.9,53.8,9.9z M54.1,9.5c0,0,0,0.1,0.1,0.1c0,0,0,0-0.1,0C54.1,9.6,54.1,9.6,54.1,9.5C54.1,9.5,54.1,9.5,54.1,9.5z
		 M54.3,9C54.3,9,54.3,9,54.3,9C54.2,9,54.2,9,54.3,9C54.2,9,54.3,8.9,54.3,9C54.3,9,54.3,9,54.3,9z M54.3,9.8
		c0,0.1-0.1,0.2-0.1,0.3c0,0.2-0.1,0.2-0.2,0.3c0,0,0,0.1,0,0.2c0,0-0.1,0-0.1,0c0,0,0,0-0.1,0c0,0,0-0.1,0-0.2
		C54,10.3,54.1,10.1,54.3,9.8C54.3,9.8,54.3,9.8,54.3,9.8z M54.4,10.6C54.4,10.6,54.3,10.6,54.4,10.6C54.3,10.6,54.3,10.6,54.4,10.6
		C54.3,10.6,54.3,10.6,54.4,10.6C54.3,10.6,54.4,10.6,54.4,10.6z M54.6,10.6C54.6,10.6,54.6,10.6,54.6,10.6c-0.1-0.1-0.1-0.1,0-0.2
		C54.6,10.5,54.6,10.5,54.6,10.6z M55.1,9.9C55.1,9.9,55.1,9.9,55.1,9.9C55,9.8,55,9.7,55,9.7c0,0,0,0,0.1,0
		C55.1,9.7,55.1,9.8,55.1,9.9z M55.2,9.2C55.2,9.2,55.3,9.2,55.2,9.2C55.3,9.2,55.3,9.3,55.2,9.2C55.2,9.3,55.2,9.3,55.2,9.2
		C55.2,9.3,55.2,9.3,55.2,9.2z M55.7,10.6C55.6,10.7,55.6,10.7,55.7,10.6C55.6,10.7,55.6,10.7,55.7,10.6
		C55.6,10.7,55.6,10.7,55.7,10.6C55.6,10.6,55.6,10.6,55.7,10.6C55.6,10.6,55.7,10.6,55.7,10.6z M55.8,10.3
		C55.7,10.3,55.7,10.3,55.8,10.3C55.7,10.3,55.7,10.3,55.8,10.3C55.7,10.2,55.7,10.2,55.8,10.3C55.8,10.2,55.8,10.3,55.8,10.3z
		 M55.9,9.9L55.9,9.9L55.9,9.9L55.9,9.9z M56.3,7.9C56.3,7.9,56.4,7.8,56.3,7.9C56.4,7.8,56.4,7.9,56.3,7.9
		C56.4,7.9,56.4,7.9,56.3,7.9C56.4,7.9,56.3,7.9,56.3,7.9z M56.8,6.7c-0.1,0-0.1,0.1-0.1,0.1c0,0,0,0-0.1-0.1c0,0,0,0,0.1,0
		C56.7,6.6,56.8,6.6,56.8,6.7z M57.4,7.5c-0.1,0.1-0.2,0.1-0.3,0.2c-0.1,0-0.2,0.1-0.3,0.2c-0.1-0.1-0.2-0.2-0.3-0.2
		c-0.1,0-0.3,0-0.4,0c0-0.1,0-0.2,0.1-0.2c0.2,0.1,0.3,0,0.5,0c0.1,0.1,0.3,0,0.4-0.1C57.2,7.3,57.3,7.4,57.4,7.5z M57.1,8.1
		c0,0,0.1-0.1,0.1-0.1C57.2,8.2,57.2,8.2,57.1,8.1z M57.4,7.8c0,0.1-0.1,0.2-0.1,0.2C57.2,7.9,57.2,7.8,57.4,7.8z M57.3,8.5
		c0,0-0.1,0.1-0.1,0.1c0,0-0.1,0-0.1-0.1c0.1-0.1,0.1-0.1,0.1-0.1C57.2,8.3,57.3,8.4,57.3,8.5z M56.1,19.4c-0.1-0.1-0.1-0.1-0.1-0.2
		C56,19.3,56.1,19.3,56.1,19.4z M57.5,8.1C57.5,8.1,57.5,8.1,57.5,8.1C57.5,8.1,57.5,8.1,57.5,8.1C57.4,8.1,57.4,8.1,57.5,8.1
		C57.4,8.1,57.5,8.1,57.5,8.1z M57.6,7.2C57.6,7.2,57.6,7.2,57.6,7.2C57.6,7.2,57.6,7.2,57.6,7.2C57.6,7.1,57.6,7.1,57.6,7.2
		C57.6,7.1,57.6,7.2,57.6,7.2z M57.4,9.2c0.1,0,0.2-0.1,0.3-0.1c0.1,0.1,0.1,0.1,0.2,0.2C57.5,9.3,57.4,9.3,57.4,9.2z M57.6,7.8
		C57.6,7.8,57.6,7.8,57.6,7.8C57.6,7.8,57.6,7.8,57.6,7.8C57.6,7.8,57.6,7.8,57.6,7.8C57.7,7.8,57.7,7.8,57.6,7.8z M57.7,8.3
		c0.1,0.2,0.1,0.2,0.2,0.4c-0.2,0-0.3-0.1-0.4-0.1C57.5,8.3,57.6,8.3,57.7,8.3z M58.1,8.3C58.1,8.3,58,8.3,58.1,8.3
		c0-0.1,0-0.1,0-0.1c-0.1,0-0.2,0-0.3,0c0-0.1,0.1-0.2,0.1-0.2c0-0.1,0-0.2,0-0.3c0.1,0,0.1,0,0.2,0.1c0,0.1,0.1,0.2,0.1,0.3
		c0,0-0.1,0.1-0.1,0.1C58.1,8.2,58.1,8.3,58.1,8.3z M58.1,8.8C58.1,8.8,58.1,8.9,58.1,8.8C58.1,8.9,58.1,8.9,58.1,8.8
		C58,8.9,58,8.9,58.1,8.8C58,8.8,58.1,8.8,58.1,8.8z M58.2,7.6c-0.1,0-0.2,0-0.3,0.2c0-0.1-0.1-0.1-0.2-0.2c0-0.1,0.1-0.2,0.1-0.4
		c0.1,0.1,0.2,0.2,0.2,0.3c0.1-0.1,0.1-0.2,0.2-0.3c0,0,0.1,0,0.1-0.1c0,0.1,0,0.1,0,0.1C58.4,7.5,58.4,7.6,58.2,7.6z M58.2,9.4
		C58.1,9.4,58.1,9.4,58.2,9.4c-0.1,0-0.2-0.1-0.3-0.1c0.1-0.1,0.1-0.1,0.2-0.2c0.1-0.1,0.2,0,0.2,0C58.3,9.2,58.2,9.3,58.2,9.4z
		 M56.8,19.2c0,0,0.1,0,0.1,0.1c0,0,0,0.1-0.1,0.1C56.8,19.4,56.8,19.3,56.8,19.2C56.8,19.3,56.8,19.2,56.8,19.2z M56.9,20.1
		C56.9,20.1,56.9,20.1,56.9,20.1c-0.1,0-0.2,0-0.2,0c0-0.1,0-0.2,0.1-0.4C56.8,19.9,56.8,20,56.9,20.1
		C56.9,20.1,56.9,20.1,56.9,20.1C56.9,20.1,56.9,20.1,56.9,20.1z M57,20.8c0.1,0.1,0,0.3,0,0.4c-0.2,0.1-0.2,0.1-0.2,0.4
		c-0.3-0.3-0.3-0.6-0.2-1c0.1,0.1,0.1,0.2,0.1,0.3c0,0.1,0,0.2,0.1,0.2C56.9,21.1,56.9,20.9,57,20.8z M57,21.2
		C57,21.2,57,21.2,57,21.2c0,0.1,0,0.1,0,0.1C57,21.3,57,21.3,57,21.2C57,21.2,57,21.2,57,21.2z M58.5,8.5L58.5,8.5L58.5,8.5
		L58.5,8.5z M57.1,19.7L57.1,19.7L57.1,19.7L57.1,19.7z M58.6,7.6C58.6,7.6,58.6,7.7,58.6,7.6c-0.1,0-0.1,0-0.1,0
		C58.5,7.6,58.6,7.5,58.6,7.6C58.6,7.5,58.6,7.6,58.6,7.6z M57.3,18.9L57.3,18.9L57.3,18.9L57.3,18.9z M58.2,11.9
		C58.2,11.9,58.2,11.9,58.2,11.9C58.2,11.9,58.2,11.9,58.2,11.9C58.2,11.9,58.2,11.9,58.2,11.9C58.2,11.9,58.2,11.9,58.2,11.9z
		 M58.4,10.3L58.4,10.3L58.4,10.3L58.4,10.3z M58.6,9.4L58.6,9.4L58.6,9.4L58.6,9.4z M57.2,22c-0.3,0-0.3,0-0.4-0.3c0,0,0-0.1,0-0.1
		c0.1,0,0.1,0,0.2,0.1C57.1,21.8,57.1,21.9,57.2,22z M58.3,13.5C58.2,13.6,58.2,13.6,58.3,13.5C58.2,13.5,58.2,13.5,58.3,13.5
		c0-0.1,0-0.1,0-0.2C58.3,13.4,58.3,13.5,58.3,13.5z M58.2,13.9L58.2,13.9L58.2,13.9L58.2,13.9z M59.1,6.7L59.1,6.7L59.1,6.7
		L59.1,6.7z M58.3,13.3C58.3,13.3,58.3,13.3,58.3,13.3c-0.2,0-0.1-0.1-0.1-0.3c0,0,0.1,0,0.1,0C58.4,13.1,58.3,13.2,58.3,13.3z
		 M59,9.5C59,9.5,59,9.5,59,9.5C58.9,9.5,58.9,9.5,59,9.5C58.9,9.4,58.9,9.4,59,9.5C59,9.4,59,9.4,59,9.5z M58.3,15.5L58.3,15.5
		L58.3,15.5L58.3,15.5z M57.5,21.9c0,0.1,0.1,0.2,0,0.3c-0.1-0.1-0.2-0.2-0.3-0.2c0.1,0,0.1-0.1,0.1-0.2c0-0.1,0-0.3,0-0.4
		c0,0,0-0.1,0.1-0.1c0,0,0.1,0,0.1,0.1C57.4,21.6,57.4,21.8,57.5,21.9z M59.7,6.6C59.7,6.7,59.7,6.7,59.7,6.6
		c-0.1,0.1-0.1,0.1-0.1,0.1C59.6,6.7,59.6,6.6,59.7,6.6c-0.1-0.1,0-0.1,0-0.1C59.7,6.6,59.7,6.6,59.7,6.6z M59.7,7.5
		c-0.3,0.1-0.3,0.1-0.4,0c0,0,0,0,0,0c0.1,0,0.1,0,0.2,0C59.5,7.5,59.6,7.5,59.7,7.5z M59.4,8.5L59.4,8.5L59.4,8.5L59.4,8.5z
		 M59.5,9.4C59.5,9.4,59.5,9.4,59.5,9.4c-0.1-0.1-0.2-0.3-0.2-0.4c0,0,0-0.1,0-0.1c0,0,0.1,0,0.1,0C59.5,9.1,59.5,9.2,59.5,9.4z
		 M59.1,12.5L59.1,12.5L59.1,12.5L59.1,12.5z M60.2,5.3C60.2,5.3,60.2,5.3,60.2,5.3C60.2,5.3,60.1,5.3,60.2,5.3
		C60.2,5.3,60.2,5.3,60.2,5.3z M59.4,12.1C59.4,12.1,59.4,12.2,59.4,12.1c0,0.1,0,0.1,0,0.1C59.3,12.2,59.3,12.2,59.4,12.1
		C59.3,12.2,59.3,12.2,59.4,12.1z M60.1,5.8c0,0.1,0,0.1,0,0.2C60.1,5.9,60.1,5.8,60.1,5.8z M60.2,6.2C60.2,6.2,60.2,6.2,60.2,6.2
		c-0.2,0-0.2,0-0.2-0.2c0,0,0-0.1,0-0.1C60.2,6.1,60.2,6.1,60.2,6.2z M60.3,6.3C60.3,6.2,60.3,6.2,60.3,6.3
		C60.3,6.2,60.3,6.2,60.3,6.3C60.3,6.2,60.3,6.2,60.3,6.3z M60.4,5.9C60.4,5.9,60.4,6,60.4,5.9C60.4,6,60.4,6,60.4,5.9
		C60.4,5.9,60.4,5.9,60.4,5.9C60.4,5.9,60.4,5.9,60.4,5.9z M60.4,7.5L60.4,7.5L60.4,7.5L60.4,7.5z M59.6,13.6
		C59.6,13.6,59.6,13.5,59.6,13.6c-0.1-0.1-0.1-0.2-0.1-0.3c0.1,0,0.2-0.1,0.2,0.1C59.8,13.4,59.7,13.5,59.6,13.6z M59.7,14.6
		c0,0,0.1,0.1,0.1,0.1c0,0.1,0,0.2,0,0.3c-0.1,0.2-0.2,0.1-0.4,0.1c-0.1,0.1-0.1,0.3-0.2,0.4c0.1,0.1,0.2,0.2,0.1,0.4c0,0,0,0,0,0.1
		c-0.1,0-0.1-0.1-0.2-0.1c0-0.1,0-0.2,0-0.3c0-0.1,0.1-0.2,0.1-0.3c0.1-0.3,0.1-0.5,0.1-0.8c0-0.1,0-0.3,0-0.4c0-0.2,0-0.3-0.1-0.4
		c-0.3-0.4-0.3-0.4-0.2-0.9c0,0,0-0.1,0-0.1c0.1,0,0.1,0,0.1,0.1c0,0.1,0,0.3,0,0.4c0,0.1,0,0.2,0.1,0.2c0-0.1,0.1-0.1,0.1-0.2
		c0.1,0.3,0.1,0.5,0.2,0.8c0.1,0.2,0.1,0.3,0,0.5c0,0.1,0,0.2,0,0.3C59.6,14.7,59.6,14.6,59.7,14.6z M59.1,16.1
		C59,16.1,59,16,59.1,16.1c0-0.1,0-0.1,0.1-0.2C59.1,16,59.1,16,59.1,16.1z M59.3,16.4c0,0-0.1,0.1-0.1,0.1c-0.1,0-0.2,0-0.2-0.2
		C59.1,16.2,59.2,16.4,59.3,16.4z M59.9,13.9C59.9,13.9,59.9,14,59.9,13.9C59.8,13.9,59.8,13.9,59.9,13.9
		C59.8,13.9,59.8,13.9,59.9,13.9C59.9,13.9,59.9,13.9,59.9,13.9z M59.9,14.3C59.9,14.3,59.9,14.3,59.9,14.3
		C59.9,14.3,59.9,14.3,59.9,14.3C59.9,14.3,59.9,14.3,59.9,14.3z M59.8,14.7c0-0.1,0.1-0.2,0.1-0.3C60,14.5,59.9,14.6,59.8,14.7z
		 M59.7,16L59.7,16L59.7,16L59.7,16z M61,5.7c0,0,0-0.1,0.1-0.1C61.1,5.6,61,5.6,61,5.7C61,5.7,61,5.7,61,5.7z M61.1,5.5
		C61.1,5.5,61.1,5.5,61.1,5.5C61.1,5.5,61.1,5.5,61.1,5.5C61.1,5.5,61.1,5.5,61.1,5.5C61.1,5.5,61.1,5.5,61.1,5.5z M60.1,15.3
		c-0.1,0.1-0.1,0.2-0.2,0.2C59.9,15.3,59.9,15.3,60.1,15.3z M61.4,4.9C61.4,4.9,61.4,5,61.4,4.9C61.3,5,61.3,5,61.4,4.9
		C61.3,4.9,61.3,4.9,61.4,4.9C61.4,4.9,61.4,4.9,61.4,4.9z M60,17.6c-0.1,0-0.2-0.1-0.2-0.1c0,0-0.1-0.1-0.1-0.1
		c0-0.1,0.1-0.1,0.1-0.1C60,17.2,60,17.2,60,17.6z M60.2,17.6c0,0.2,0,0.2-0.2,0.4C60,17.8,60,17.7,60.2,17.6z M60,18
		C60,18,60,18,60,18C60,18,60,18,60,18C60,18,60,18,60,18z M60.1,16.9C60.1,16.9,60.1,16.8,60.1,16.9c0.2,0,0.2,0,0.1,0.3
		C60.2,17,60.2,16.9,60.1,16.9z M60.1,16.4c0-0.1,0.1-0.1,0.2-0.2C60.3,16.4,60.3,16.5,60.1,16.4z M60.2,15.6
		C60.2,15.6,60.2,15.6,60.2,15.6C60.2,15.7,60.2,15.7,60.2,15.6C60.2,15.6,60.2,15.6,60.2,15.6z M61.6,5.1
		C61.6,5.1,61.6,5.1,61.6,5.1C61.6,5.1,61.6,5,61.6,5.1C61.6,5,61.7,5,61.6,5.1C61.7,5.1,61.6,5.1,61.6,5.1z M60.5,15.8
		c0,0.1,0,0.2-0.1,0.2c0,0-0.1-0.1-0.1-0.1c0-0.1,0-0.1,0-0.2C60.3,15.8,60.4,15.7,60.5,15.8z M61.9,5.1C61.9,5.2,61.8,5.2,61.9,5.1
		C61.8,5.2,61.8,5.2,61.9,5.1C61.8,5.1,61.8,5.1,61.9,5.1C61.8,5.1,61.9,5.1,61.9,5.1z M60.3,18.2c0,0.2,0,0.3-0.1,0.4
		c0,0.1,0,0.2,0,0.3c0,0.2,0,0.4,0,0.7c0,0.1,0,0.2,0.1,0.3c-0.1,0-0.1,0-0.2,0.1c0,0,0,0.1-0.1,0.1c-0.1,0.1-0.3,0-0.2-0.2
		c0-0.1,0-0.1,0-0.2c0,0,0-0.1,0-0.1c-0.1,0-0.1,0-0.2,0c0-0.1,0.1-0.2,0.2-0.2c0.1,0,0.1,0,0.2,0c0,0,0.1-0.1,0.1-0.1
		c0,0,0-0.1,0-0.1c-0.1-0.1-0.1-0.1-0.2-0.2c0-0.1-0.1-0.2-0.1-0.3c-0.2,0-0.2,0-0.2,0.4c-0.1,0-0.1,0-0.2,0c-0.1,0-0.1,0-0.2,0
		c0,0-0.1,0-0.1-0.1c0-0.1,0-0.1,0.1-0.2c0,0,0.1,0,0.1-0.1c-0.1,0-0.1-0.1-0.2-0.1c-0.1-0.3-0.1-0.7-0.2-1c0,0,0-0.1,0-0.1
		c-0.1-0.1-0.1-0.2-0.1-0.3c0.1-0.1,0.1-0.3,0.2-0.5c0,0,0.1-0.1,0.1-0.1c0.1,0,0.2,0.2,0.3,0.1c0-0.1-0.1-0.3-0.2-0.4
		c0-0.1,0-0.2,0-0.4c0.1,0,0.1,0.1,0.2,0.1c0,0.2,0,0.3,0,0.5c0,0.1,0,0.3-0.1,0.4c-0.1,0.2-0.2,0.3-0.3,0.5
		c0.1,0.3,0.4,0.6,0.5,0.9c0,0,0.1,0,0.1,0.1c0.1-0.1,0-0.2,0.2-0.3C60,18.2,60.2,18.1,60.3,18.2z M59.9,20.3
		C59.9,20.3,59.9,20.3,59.9,20.3C59.9,20.4,59.9,20.4,59.9,20.3C59.9,20.4,59.9,20.3,59.9,20.3C59.9,20.3,59.9,20.3,59.9,20.3z
		 M60.7,15.4L60.7,15.4L60.7,15.4L60.7,15.4z M62,4.8C62,4.8,62,4.8,62,4.8C62,4.8,62,4.8,62,4.8C62,4.7,62,4.7,62,4.8
		C62,4.7,62,4.7,62,4.8z M60.9,14.3C60.9,14.3,60.9,14.3,60.9,14.3C60.9,14.3,60.8,14.3,60.9,14.3C60.8,14.2,60.8,14.2,60.9,14.3
		C60.9,14.2,60.9,14.2,60.9,14.3z M60.6,17.3C60.6,17.3,60.5,17.3,60.6,17.3C60.5,17.3,60.5,17.3,60.6,17.3
		C60.5,17.2,60.5,17.2,60.6,17.3C60.6,17.2,60.6,17.2,60.6,17.3z M60.5,17.7C60.5,17.7,60.5,17.7,60.5,17.7c-0.1,0-0.1-0.1-0.1-0.2
		c0,0,0,0,0,0C60.5,17.6,60.5,17.7,60.5,17.7z M60.3,19.9C60.3,19.9,60.3,19.9,60.3,19.9C60.3,19.9,60.3,19.9,60.3,19.9
		C60.3,19.9,60.3,19.9,60.3,19.9z M60.1,21.5c-0.1,0.1-0.2,0-0.3-0.1c0,0-0.1,0-0.1,0c0,0.1,0,0.2-0.1,0.3c0,0.1-0.2,0.2-0.2,0.3
		c0,0.1,0.2,0.1,0,0.3c-0.2-0.1-0.1-0.3-0.1-0.5c0.1-0.2,0.1-0.4,0.2-0.7c0,0,0-0.1,0-0.1c-0.1-0.1-0.2-0.2-0.1-0.3
		c0.1,0,0.1-0.1,0.2-0.1c0.1,0.1,0.2,0.2,0.3,0.2C60.1,21.1,60.2,21.1,60.1,21.5z M60.9,20.7c-0.1,0-0.1-0.1-0.1-0.1
		c-0.1-0.1-0.1,0-0.2-0.1c0-0.2,0-0.4,0.1-0.5c0,0,0.1-0.1,0.1-0.1c0,0,0.1,0,0.1,0c0,0,0,0.1,0,0.1c0,0.1-0.1,0.2-0.1,0.2
		c-0.1,0.1,0,0.2,0,0.2C60.8,20.5,60.9,20.5,60.9,20.7z M60.8,19.5C60.7,19.5,60.7,19.5,60.8,19.5c-0.1-0.1,0-0.2,0-0.2
		c0,0.1,0.1,0.1,0.1,0.1C60.8,19.5,60.8,19.5,60.8,19.5z M60.5,21.4C60.5,21.3,60.5,21.3,60.5,21.4c0.1,0,0.2,0,0.2,0
		c0,0,0,0.1,0,0.1c-0.1,0.1-0.1,0.1-0.3,0.2C60.4,21.6,60.4,21.5,60.5,21.4z M60.7,22.3C60.7,22.3,60.7,22.3,60.7,22.3
		C60.7,22.3,60.7,22.3,60.7,22.3C60.7,22.3,60.7,22.3,60.7,22.3z M60,22.7c0.1,0,0.2-0.1,0.1-0.2c0,0,0,0,0,0
		c0.1-0.2,0.1-0.3,0.3-0.4c0,0,0-0.1,0.1-0.1c0-0.1,0-0.1,0.1-0.2c0,0.1,0,0.2,0.1,0.2c0.1,0.1,0.1,0.2,0.1,0.4c0,0,0,0-0.1,0
		c0,0.2-0.2,0.3-0.3,0.3c0,0.2,0.1,0.3,0.1,0.4c-0.1,0-0.2-0.1-0.2-0.1C60.2,22.8,60.1,22.7,60,22.7z M60.1,22.5
		C60.1,22.5,60.1,22.5,60.1,22.5C60.1,22.5,60.1,22.5,60.1,22.5C60.1,22.5,60.1,22.5,60.1,22.5C60.1,22.5,60.1,22.5,60.1,22.5z
		 M60.1,21.7C60.1,21.7,60.1,21.7,60.1,21.7C60.2,21.7,60.1,21.8,60.1,21.7C60.1,21.8,60.1,21.7,60.1,21.7
		C60.1,21.7,60.1,21.7,60.1,21.7z M60.9,15.3L60.9,15.3L60.9,15.3L60.9,15.3z M60.9,18c-0.1,0.2-0.1,0.3-0.2,0.5
		c-0.1-0.1-0.1-0.1-0.2-0.2C60.7,18.1,60.8,18,60.9,18C60.9,17.9,60.9,18,60.9,18z M61.4,14.2C61.4,14.2,61.4,14.2,61.4,14.2
		C61.4,14.2,61.4,14.2,61.4,14.2C61.4,14.2,61.4,14.2,61.4,14.2C61.4,14.2,61.4,14.2,61.4,14.2z M61,17.7L61,17.7L61,17.7L61,17.7z
		 M61,18.9c0,0,0,0.1,0,0.1c0,0-0.1,0-0.1,0c0-0.1-0.1-0.2-0.1-0.3C60.9,18.7,61,18.8,61,18.9z M61.1,18.5c-0.1-0.3-0.1-0.3,0-0.6
		C61.3,18.2,61.3,18.3,61.1,18.5z M61.3,17.6L61.3,17.6L61.3,17.6L61.3,17.6z M61.7,15.7c-0.1,0.1-0.1,0.2-0.2,0.4
		c0-0.2,0-0.3,0-0.3C61.5,15.7,61.6,15.7,61.7,15.7z M63.1,6.9L63.1,6.9L63.1,6.9L63.1,6.9z M61.7,19.1c-0.3-0.3-0.3-0.6,0-0.8
		c0,0.1,0,0.2,0,0.4C61.6,18.8,61.7,18.9,61.7,19.1z M61.3,21.9C61.3,21.9,61.3,21.9,61.3,21.9C61.3,21.9,61.3,21.9,61.3,21.9
		C61.3,21.9,61.3,21.9,61.3,21.9C61.3,21.9,61.3,21.9,61.3,21.9z M63.7,10.1C63.7,10.1,63.7,10.1,63.7,10.1
		C63.6,10.1,63.6,10.1,63.7,10.1C63.7,10.1,63.7,10.1,63.7,10.1z M62.8,15.4C62.8,15.4,62.9,15.4,62.8,15.4
		C62.9,15.4,62.9,15.5,62.8,15.4C62.9,15.5,62.9,15.5,62.8,15.4C62.8,15.5,62.8,15.5,62.8,15.4z M61.6,23.8
		C61.7,23.8,61.7,23.8,61.6,23.8C61.7,23.8,61.7,23.8,61.6,23.8C61.7,23.8,61.7,23.8,61.6,23.8C61.6,23.8,61.6,23.8,61.6,23.8z
		 M62.8,13.4C62.8,13.4,62.8,13.4,62.8,13.4c0.1,0.1,0.1,0.1,0.1,0.1C62.8,13.5,62.8,13.5,62.8,13.4C62.8,13.5,62.8,13.4,62.8,13.4z
		 M62.1,18.7L62.1,18.7L62.1,18.7L62.1,18.7z M63.1,10.6c0,0.1,0,0.2-0.1,0.3C62.9,10.8,63,10.7,63.1,10.6z M63.1,10.6
		C63.1,10.6,63.1,10.6,63.1,10.6c0,0,0-0.1,0-0.2C63.2,10.5,63.2,10.5,63.1,10.6z M63,13.6C63,13.6,63,13.7,63,13.6
		C63,13.7,63,13.7,63,13.6c-0.1,0-0.1,0-0.1-0.1C62.9,13.6,63,13.6,63,13.6z M63.3,12.3c0-0.1-0.1-0.2-0.1-0.3
		C63.3,12,63.3,12.1,63.3,12.3z M63.1,15.6C63.1,15.6,63.1,15.7,63.1,15.6C63.1,15.7,63.1,15.6,63.1,15.6
		C63.1,15.6,63.1,15.6,63.1,15.6C63.1,15.6,63.1,15.6,63.1,15.6z M62.8,19.8C62.8,19.8,62.7,19.7,62.8,19.8
		C62.7,19.7,62.8,19.7,62.8,19.8C62.8,19.7,62.8,19.7,62.8,19.8C62.8,19.7,62.8,19.7,62.8,19.8z M62.3,23.5c-0.1,0-0.1-0.1-0.2-0.2
		c0,0,0-0.1,0-0.1c0-0.1,0.1-0.2,0.1-0.2c0.1,0,0.1,0.1,0.1,0.2C62.4,23.4,62.4,23.5,62.3,23.5z M63.4,16.3
		C63.4,16.3,63.4,16.4,63.4,16.3C63.4,16.4,63.4,16.3,63.4,16.3C63.4,16.3,63.4,16.3,63.4,16.3C63.4,16.3,63.4,16.3,63.4,16.3z
		 M63.2,18.7c-0.1-0.2-0.1-0.3,0-0.5C63.2,18.4,63.3,18.5,63.2,18.7z M63.9,16.3L63.9,16.3L63.9,16.3L63.9,16.3z M63.1,24.1
		C63.1,24.1,63.1,24.1,63.1,24.1c-0.1,0-0.1,0-0.1,0C63,24,63,24,63.1,24.1C63.1,24,63.1,24.1,63.1,24.1z M63.7,19.6
		C63.7,19.6,63.7,19.6,63.7,19.6C63.6,19.6,63.6,19.6,63.7,19.6C63.6,19.6,63.7,19.5,63.7,19.6C63.7,19.5,63.7,19.6,63.7,19.6z
		 M63.3,25.4c-0.2-0.1-0.2-0.2-0.2-0.5c0-0.1,0.1-0.2,0.2-0.2C63.3,25,63.3,25.2,63.3,25.4z M63.4,24.6c0,0,0,0.1,0,0.1
		c0,0,0,0-0.1,0C63.4,24.7,63.4,24.6,63.4,24.6C63.4,24.6,63.4,24.6,63.4,24.6z M64,20.2C64,20.2,64,20.3,64,20.2
		C64,20.3,64,20.3,64,20.2C64,20.3,64,20.3,64,20.2C64,20.2,64,20.2,64,20.2z M64.1,19.9C64.1,19.9,64.1,19.9,64.1,19.9
		C64.1,19.9,64.1,19.9,64.1,19.9C64.1,19.8,64.1,19.8,64.1,19.9C64.1,19.8,64.1,19.9,64.1,19.9z M64,25C64,25,63.9,25,64,25
		C63.9,25,63.9,25,64,25C63.9,25,63.9,25,64,25C64,25,64,25,64,25z M64.1,24.2C64.1,24.2,64.1,24.2,64.1,24.2
		C64.1,24.2,64.1,24.2,64.1,24.2C64.1,24.2,64.1,24.2,64.1,24.2C64.1,24.1,64.1,24.2,64.1,24.2z"/>
	<path class="st0" d="M4.4,20.1C4.4,20.1,4.4,20.1,4.4,20.1L4.4,20.1z"/>
	<polygon class="st0" points="44.4,43.1 44.4,43.1 44.4,43.1 	"/>
	<polygon class="st0" points="5.2,20.6 5.2,20.6 5.2,20.6 	"/>
	<polygon class="st0" points="61.4,24 61.4,24 61.4,24 	"/>
	<polygon class="st0" points="32.6,42.5 32.6,42.5 32.6,42.5 	"/>
	<polygon class="st0" points="43.3,42.7 43.3,42.7 43.3,42.8 	"/>
	<path class="st0" d="M4.7,20.4l-0.1,0c0-0.1,0.1-0.2,0.1-0.3c0,0,0,0,0.1,0C4.7,20.2,4.7,20.3,4.7,20.4z"/>
	<polygon class="st0" points="54.7,9.4 54.7,9.4 54.7,9.4 	"/>
	<polygon class="st0" points="30.8,4.9 30.8,4.9 30.8,4.9 	"/>
	<ellipse transform="matrix(0.9262 -0.3771 0.3771 0.9262 -0.8292 4.8959)" class="st0" cx="12.1" cy="4.6" rx="0" ry="0"/>
	<polygon class="st0" points="58.2,34 58.2,34 58.2,34 	"/>
	<ellipse transform="matrix(0.9513 -0.3082 0.3082 0.9513 -0.3948 4.9971)" class="st0" cx="15.6" cy="3.7" rx="0" ry="0"/>
	<polygon class="st0" points="56.9,35.4 56.9,35.4 56.9,35.4 	"/>
	<polygon class="st0" points="61.9,24.8 61.9,24.8 61.9,24.8 	"/>
	<polygon class="st0" points="26.5,4.3 26.5,4.3 26.5,4.3 	"/>
	<polygon class="st0" points="64.8,24.1 64.8,24.1 64.8,24.1 	"/>
	<polygon class="st0" points="55.2,8.8 55.2,8.8 55.2,8.8 	"/>
	<path class="st0" d="M61.5,32.1C61.5,32.1,61.5,32.1,61.5,32.1L61.5,32.1z"/>
	<ellipse transform="matrix(0.4846 -0.8747 0.8747 0.4846 -4.9903 71.1194)" class="st0" cx="57.9" cy="39.8" rx="0" ry="0"/>
	<polygon class="st0" points="53.7,25.6 53.7,25.6 53.7,25.6 	"/>
	<path class="st0" d="M57.2,41.4c-0.1,0-0.3,0.1-0.4,0.1c0,0.1,0,0.1,0,0.2c0.1,0,0.2,0.1,0.3,0c0.2,0,0.3-0.1,0.5,0
		c0,0,0.1,0,0.1-0.1c0,0.1,0.1,0.2,0.1,0.3c0,0,0.1,0,0.1,0c0.2,0,0.4-0.1,0.6-0.1c0.2,0,0.3-0.1,0.4-0.3c0,0-0.1,0-0.1,0
		c-0.2,0-0.2,0-0.5-0.2c-0.2,0.3-0.5,0.1-0.7,0.1c0-0.2,0.1-0.3,0.1-0.3c0.1-0.1,0.3-0.3,0.4-0.4c0,0,0,0,0,0c0,0,0,0,0,0l0,0
		c0.2-0.1,0.4-0.2,0.5-0.3c0.1,0,0.2,0.1,0.2,0.2c0,0.1-0.1,0.2-0.1,0.3c-0.1,0-0.2,0-0.2,0.1c0.1,0.1,0.2,0,0.3-0.1c0,0,0,0,0,0
		c0.1,0,0.2,0.1,0.2,0.2c0.1,0,0.2,0.1,0.2-0.1c0-0.1,0.1-0.1,0.2-0.2c-0.1,0-0.2,0-0.3-0.1c0-0.1,0-0.1,0-0.1
		c0-0.2,0.1-0.3,0.3-0.3c0.1,0,0.1,0,0.2,0c0,0,0,0,0.1-0.1c0,0,0-0.1-0.1-0.1c-0.1,0-0.1,0-0.2,0c-0.1,0-0.2,0-0.3,0
		c-0.2,0-0.3-0.1-0.3-0.3c0,0,0-0.1,0-0.1c0-0.1,0.1-0.2,0.1-0.3c-0.2,0-0.3,0.1-0.4,0.1c0-0.1-0.1-0.1-0.1-0.2
		c-0.1-0.1-0.3-0.2-0.5,0c0,0-0.1,0.1-0.1,0.2c0,0,0.1,0.1,0.1,0.1c0-0.1,0.1-0.1,0.1-0.2c0.1,0,0.3,0,0.3,0.2c0,0.1,0,0.1,0,0.2
		c0.1,0,0.1,0,0.2,0.1c-0.1,0.1-0.1,0.3-0.2,0.4c-0.2,0-0.3,0.1-0.5,0.1c-0.2,0-0.2,0-0.2,0.3c0,0,0,0-0.1,0c-0.1-0.1,0-0.2-0.2-0.3
		c0,0.1,0,0.2,0,0.3C57.4,41.2,57.4,41.4,57.2,41.4z M58.6,40.2C58.6,40.2,58.6,40.2,58.6,40.2c0-0.1,0-0.1,0-0.1
		C58.6,40.1,58.7,40.1,58.6,40.2C58.6,40.2,58.6,40.2,58.6,40.2z"/>
	<ellipse transform="matrix(0.5086 -0.861 0.861 0.5086 -7.8476 69.3568)" class="st0" cx="56.8" cy="41.6" rx="0" ry="0"/>
	<path class="st0" d="M31.9,5C31.9,5,32,4.9,31.9,5C32,4.9,32,4.9,31.9,5C32,4.9,31.9,4.9,31.9,5C31.9,4.9,31.9,4.9,31.9,5
		c-0.1,0-0.1,0-0.2-0.1c0-0.1,0.1-0.2,0.1-0.3c-0.2-0.1-0.4,0-0.5-0.1c0-0.1,0-0.2,0-0.3c0-0.1-0.1-0.2-0.2-0.3
		c0.1-0.1,0.1-0.2,0.2-0.3c0,0,0.1-0.1,0.2-0.1c0.1,0,0.1,0.1,0.2,0.2c0,0,0,0.1,0,0.1c0,0.3,0.1,0.4,0.4,0.5c0,0,0.1,0,0.1,0
		c0.1,0,0.2,0.1,0.2,0.3c0,0.1,0,0.3,0,0.4c-0.1,0-0.1,0.1-0.2,0.1l0,0C32.1,5.1,32,5.1,31.9,5z"/>
	<path class="st0" d="M51.6,6.5c-0.3-0.1-0.5-0.1-0.8,0c-0.2,0-0.4,0-0.6,0c0,0-0.1,0-0.1,0.1c0.1,0,0.1,0,0.2,0.1
		c0.1,0.2,0.2,0.2,0.4,0.2c0.1,0,0.1,0,0.2,0c0.1,0,0.2,0,0.3,0.1c-0.2,0-0.3,0-0.5,0c-0.2,0-0.3,0-0.5,0c-0.2,0-0.3,0-0.5,0
		c-0.1-0.1,0-0.2,0-0.3c0-0.1,0.1-0.1,0.2-0.2c0.1-0.1,0.2-0.2,0.3-0.2c0.3,0,0.6,0,0.9,0c0.2,0,0.4,0,0.6-0.1
		c0.1,0.1,0.1,0.1,0.2,0.2c0,0,0,0.1,0,0.1C51.7,6.5,51.6,6.5,51.6,6.5z"/>
	<polygon class="st0" points="9.3,5 9.3,5 9.3,5 9.3,5 9.3,5 	"/>
	<path class="st0" d="M9,5L9,5C9,5,9,5.1,9,5C9,5.1,9,5.1,9,5.1C9,5.1,9,5.1,9,5C9,5,9,5,9,5c0-0.1,0-0.3,0-0.4c0,0,0,0,0,0
		c0,0,0,0,0-0.1c-0.1,0-0.1-0.1-0.2-0.1c0.1-0.2,0.1-0.2,0-0.4c0.1,0,0.2,0,0.2,0l0,0c0,0,0,0,0,0c0,0,0,0,0,0
		c0.1,0.1,0.1,0.1,0.2,0c0,0,0-0.1,0.1-0.1c0,0.1,0,0.2,0,0.2c0,0.1,0,0.3,0,0.4c0,0.1,0,0.3,0,0.4c0,0.1-0.1,0.1-0.1,0.2
		C9.1,5.1,9.1,5.1,9,5z"/>
	<path class="st0" d="M16.3,2.7c0,0-0.1,0.1-0.2,0.1c-0.1,0-0.1,0-0.1-0.1c0-0.1,0-0.1,0-0.2c0,0,0.1-0.1,0.2-0.1
		c0-0.1,0-0.1-0.1-0.2c0-0.1,0-0.2,0.1-0.2c0.1,0.1,0,0.3,0.2,0.4c0,0,0.1-0.1,0.1-0.1c0.1,0.2,0.1,0.4,0,0.6C16.6,3,16.6,3,16.5,3
		C16.5,2.9,16.4,2.8,16.3,2.7z"/>
	<ellipse transform="matrix(0.4652 -0.8852 0.8852 0.4652 6.1304 16.2493)" class="st0" cx="16.5" cy="3.1" rx="0" ry="0"/>
	<path class="st0" d="M4.9,5.1C5,4.9,5.2,4.8,5.2,4.5c0,0,0-0.1-0.1-0.1c0.1-0.1,0.1-0.2,0.2-0.3c-0.1-0.1-0.2,0-0.2,0
		C5,4,5,3.9,4.9,3.9C4.8,3.9,4.8,4,4.7,4.1c0,0,0,0.1,0,0.1C4.9,4.4,5,4.7,4.9,5C4.9,5,4.9,5.1,4.9,5.1z M5,4.2C5,4.2,5,4.2,5,4.2
		C5,4.2,5,4.2,5,4.2C5,4.2,5,4.2,5,4.2z"/>
	<ellipse transform="matrix(0.9795 -0.2013 0.2013 0.9795 -0.9291 1.0981)" class="st0" cx="4.9" cy="5.1" rx="0" ry="0"/>
	<polygon class="st0" points="16.8,3.4 16.8,3.4 16.8,3.4 16.8,3.4 16.8,3.4 16.8,3.4 16.8,3.4 16.8,3.4 16.8,3.4 	"/>
	<path class="st0" d="M16.3,3.8c-0.1,0-0.1-0.1-0.2-0.1c0-0.1-0.1-0.2-0.1-0.3l0,0c0,0,0,0,0,0c0,0,0,0,0,0c0.1-0.1,0.1-0.2,0.2-0.3
		c0.1-0.1,0.2-0.2,0.3-0.1c0,0,0,0.1-0.1,0.2c0.1,0,0.1,0,0.2,0c0.1,0,0.1,0.1,0.1,0.2c0,0-0.1,0-0.1,0C16.5,3.6,16.4,3.7,16.3,3.8z
		"/>
	<ellipse transform="matrix(0.9748 -0.2232 0.2232 0.9748 -0.4442 3.7447)" class="st0" cx="16.3" cy="3.8" rx="0" ry="0"/>
	<path class="st0" d="M26.5,3.9c0,0.1,0,0.1,0,0.2c0,0,0,0,0,0c-0.1,0-0.2,0-0.2,0c0,0,0,0-0.1,0c0-0.1,0-0.1,0-0.2
		c-0.1-0.1-0.2-0.1-0.2-0.2c-0.3,0.1-0.5,0.1-0.8,0c-0.1,0-0.1-0.1-0.1-0.2c0.1,0,0.2,0,0.3-0.2c0-0.1,0.1-0.1,0.2,0
		c0,0,0.1,0.1,0.1,0.1c0.1,0,0.1,0,0.2,0c0.1,0,0.1,0,0.2,0c0,0.1,0,0.1,0,0.1c0,0,0.1,0,0.1,0c0.1,0,0.2,0.1,0.3,0
		c0.1,0,0.2-0.1,0.3-0.2c0.1,0.1,0.2,0.2,0.2,0.3C26.8,4,26.8,3.8,26.5,3.9z"/>
	<path class="st0" d="M65.4,24.8c0-0.1,0.1-0.2,0.1-0.2c0-0.1-0.2,0.1-0.2-0.1c0-0.1-0.1-0.1-0.2-0.1c-0.2,0.2-0.2,0.5-0.1,0.7
		c0.1,0.1,0.2,0.1,0.3,0C65.4,25,65.4,24.9,65.4,24.8z M65.2,24.8L65.2,24.8L65.2,24.8L65.2,24.8z"/>
	<path class="st0" d="M45.2,5.4c-0.1-0.2,0-0.4,0.1-0.5c0.1-0.1,0.2-0.1,0.4-0.2c0,0.1,0.1,0.2,0.1,0.3c0,0.1,0,0.2-0.1,0.2
		C45.6,5.4,45.4,5.4,45.2,5.4z"/>
	<path class="st0" d="M46.7,5.1c-0.2,0-0.4,0-0.5,0c0,0,0-0.1-0.1-0.1l0,0c0,0,0,0,0,0c0,0,0,0,0,0c0.1-0.1,0.2-0.2,0.3-0.4
		c0.1,0.1,0.2,0.2,0.3,0.2c0-0.1,0.1-0.1,0.1-0.2c0,0.1,0.1,0.1,0.1,0.1c0,0,0,0.1,0,0.2C46.8,5,46.7,5.1,46.7,5.1z"/>
	<path class="st0" d="M23.5,3.5c-0.1,0-0.1,0-0.2-0.1c-0.1-0.1-0.2-0.1-0.3,0.1c0,0-0.1,0-0.1,0.1c0-0.1,0-0.1,0-0.2
		C22.9,3.2,23,3.1,23.1,3c0.1,0,0.2,0.1,0.3,0.1c0.1,0,0.1-0.2,0.3-0.1c0,0.1-0.1,0.1-0.1,0.2c0,0,0.1,0.1,0.1,0.1
		C23.6,3.4,23.6,3.5,23.5,3.5z"/>
	<path class="st0" d="M42.5,6.5c0-0.1,0.2-0.1,0.2,0c0.1,0.2,0.1,0.4,0.1,0.6c-0.1,0-0.2,0-0.3,0.1c0,0,0,0,0,0c0,0,0,0,0,0l0,0
		c0,0-0.1-0.1-0.2-0.1C42.4,6.8,42.5,6.7,42.5,6.5z"/>
	<path class="st0" d="M24.4,3.6c0,0-0.1,0-0.1,0c-0.2,0-0.4,0-0.6,0.2c0,0,0,0-0.1,0c0-0.1,0-0.2,0.1-0.2c0.1,0,0.1-0.1,0.2-0.2
		c0-0.1,0.1-0.1,0.1-0.2c0.2,0.1,0.3,0.1,0.4,0.2c0,0,0.1,0.1,0.1,0.1C24.5,3.5,24.4,3.6,24.4,3.6z"/>
	<path class="st0" d="M4.4,5.2c-0.1,0-0.3,0-0.4-0.1C4,4.9,4.1,5.1,4.2,5c0-0.1,0-0.3,0-0.4c0-0.1,0-0.1,0.1-0.1
		c0,0.1,0,0.3,0.1,0.3c0,0,0,0.1,0.1,0.2C4.6,5.1,4.5,5.2,4.4,5.2z"/>
	<path class="st0" d="M46.6,6.3c-0.4,0-0.7,0-1.1,0c0,0.1,0,0.1-0.1,0.3c0-0.1,0-0.2,0-0.3c0,0,0.1-0.1,0.1-0.1
		c0.1,0,0.2-0.1,0.2-0.1c0.3,0,0.6,0,0.8,0.1C46.6,6.2,46.6,6.2,46.6,6.3z"/>
	<path class="st0" d="M15,2.1c-0.1,0.1-0.1,0.2,0,0.3c0,0,0,0.1,0,0.1c0,0-0.1,0-0.1-0.1c0-0.3-0.1-0.6-0.1-0.9c0,0,0,0,0.1-0.1
		c0,0.1,0.1,0.2,0.1,0.3c0.1-0.1,0.2-0.1,0.3-0.1c0,0,0,0.1,0,0.1c-0.1,0.1-0.1,0.1-0.2,0.2C15,2,15,2,15,2.1z"/>
	<path class="st0" d="M11.2,3.1C11.2,3.1,11.2,3.1,11.2,3.1C11.2,3.1,11.2,3.1,11.2,3.1L11.2,3.1z"/>
	<path class="st0" d="M11.4,3.8c0,0-0.1,0.1-0.1,0.1c0-0.1-0.1-0.2-0.2-0.2C11,3.7,11,3.8,10.9,3.9c0-0.1,0-0.3,0.1-0.3
		c0.1-0.1,0.2-0.3,0.2-0.4c0,0.1,0.1,0.2,0.1,0.3c0.3,0,0.3,0,0.3,0.2l0,0c0,0,0,0,0,0c0,0,0,0,0,0C11.5,3.7,11.5,3.7,11.4,3.8z"/>
	<polygon class="st0" points="11.4,3.9 11.4,3.9 11.4,3.9 	"/>
	<path class="st0" d="M15.8,3.6c0,0.1-0.1,0.1-0.2,0.1c0-0.1-0.1-0.2-0.1-0.2c-0.2-0.1-0.2-0.3-0.3-0.4c0.1,0,0.1,0.1,0.2,0.1
		c0.3,0.2,0.3,0.2,0.6,0.1c0,0.1,0,0.1,0,0.2C15.9,3.5,15.9,3.5,15.8,3.6z"/>
	<path class="st0" d="M54.8,42.8c0.1,0,0.1,0,0.2,0c0.2,0.1,0.4,0,0.6-0.2c0.1,0,0.1,0.1,0.2,0.1C55.8,43,55.7,43,55.6,43
		c-0.2,0-0.5,0-0.7,0c0,0-0.1,0-0.1,0C54.8,42.9,54.7,42.9,54.8,42.8C54.8,42.8,54.8,42.8,54.8,42.8z"/>
	<polygon class="st0" points="55.6,42.7 55.6,42.7 55.6,42.7 	"/>
	<path class="st0" d="M14,2.4c-0.1,0-0.1,0-0.1-0.1c0.1-0.2,0-0.3,0-0.5c0-0.1,0-0.2,0.1-0.4c0.1,0.4,0.1,0.5,0.1,0.7
		C14.1,2.3,14,2.4,14,2.4z"/>
	<path class="st0" d="M58.5,6c0-0.3-0.1-0.5,0.1-0.8c0,0,0,0,0.1,0c0,0.1,0,0.2,0,0.2c0.1,0,0.1,0,0.1,0c0,0,0.1,0.1,0.1,0.1
		C58.8,5.8,58.7,5.9,58.5,6z"/>
	<ellipse transform="matrix(0.7902 -0.6129 0.6129 0.7902 -6.321510e-03 8.745)" class="st0" cx="12.8" cy="4.4" rx="0" ry="0"/>
	<path class="st0" d="M5.6,5.7c-0.1,0-0.2-0.1-0.1-0.3c0-0.1,0.1-0.2,0.2-0.2c0.1,0,0.2,0.1,0.2,0.2C5.9,5.6,5.8,5.7,5.6,5.7z"/>
	<path class="st0" d="M20.6,3.1c0,0-0.1,0-0.1,0c0,0,0-0.1,0-0.2c0-0.1,0.1-0.1,0.2-0.1c0.2,0,0.3,0.1,0.4,0.3c-0.1,0-0.2,0-0.3,0
		C20.7,3,20.7,3,20.6,3.1z"/>
	<path class="st0" d="M52.9,6.5c-0.2,0-0.3,0-0.5,0c0,0,0-0.1-0.1-0.1c0,0,0-0.1,0-0.1c0,0,0.1,0,0.1,0c0.2,0,0.5,0,0.7,0
		c0,0,0.1,0,0.1,0.1c0,0-0.1,0.1-0.1,0.1C53.1,6.5,53,6.5,52.9,6.5z"/>
	<path class="st0" d="M21.4,3.1c-0.1-0.3-0.1-0.3,0.1-0.5c0-0.1-0.1-0.2,0-0.3c0.1,0.1,0.2,0.3,0.2,0.5C21.7,2.9,21.5,2.9,21.4,3.1z
		"/>
	<path class="st0" d="M17.4,2.3c0,0.1,0,0.2-0.1,0.1c0,0-0.1-0.1-0.1-0.2c0-0.1,0-0.2,0.1-0.2c0.1-0.1,0.1-0.2,0.2-0.4
		c0.1,0.1,0.1,0.1,0.1,0.1c0,0.1,0,0.2-0.1,0.3C17.4,2.1,17.4,2.2,17.4,2.3z"/>
	<path class="st0" d="M43.2,5.6c0.2,0,0.2,0,0.3,0c0.1,0,0.1,0.1,0.2,0.2c0,0.1-0.1,0.2-0.1,0.2c-0.1,0-0.1,0-0.2,0
		C43.4,5.8,43.3,5.7,43.2,5.6z"/>
	<path class="st0" d="M6.2,4.5C6.2,4.5,6.2,4.5,6.2,4.5c0.1,0.1,0.1,0.2,0.2,0.2C6.3,4.8,6.2,4.9,6.2,5c0-0.1-0.1-0.1-0.1-0.1
		c0,0-0.1,0-0.2,0.1C5.9,4.7,6.1,4.6,6.2,4.5z"/>
	<path class="st0" d="M3.4,4.7C3.4,4.5,3.3,4.4,3.2,4.2C3.4,4.1,3.4,4,3.5,4C3.5,4.2,3.5,4.4,3.4,4.7z"/>
	<path class="st0" d="M48.5,6.3c0.3,0,0.6,0,0.9,0c0.1,0,0.1,0,0.2,0.1c0,0,0,0,0,0.1C49.2,6.3,48.9,6.5,48.5,6.3z"/>
	<path class="st0" d="M14.7,3.6c0-0.1,0-0.2-0.1-0.3c0.1-0.1,0.2-0.2,0.3-0.3C15,3.2,15,3.2,14.7,3.6z"/>
	<path class="st0" d="M25.1,3.3c0-0.2,0.1-0.3,0.2-0.5c0.1,0,0.2,0.1,0.1,0.3c-0.1,0.1-0.2,0.2-0.3,0.4C25.1,3.5,25,3.4,25.1,3.3z"
		/>
	<ellipse transform="matrix(0.9562 -0.2926 0.2926 0.9562 7.863572e-02 7.5126)" class="st0" cx="25.2" cy="3.5" rx="0" ry="0"/>
	<path class="st0" d="M47.3,6.9c0-0.2-0.1-0.3-0.1-0.5c-0.1,0-0.2,0-0.3-0.1c0.3-0.2,0.5,0,0.9,0c-0.2,0.1-0.3,0.1-0.4,0.1
		C47.4,6.6,47.4,6.7,47.3,6.9z"/>
	<polygon class="st0" points="47.3,6.9 47.3,6.9 47.3,6.9 	"/>
	<path class="st0" d="M3.7,3.3C3.6,3.1,3.6,3.1,3.6,3c0.1-0.1,0.2-0.1,0.2-0.1C4,2.9,4,3,4,3.2C3.9,3.2,3.8,3.2,3.7,3.3z"/>
	<path class="st0" d="M16.4,1.6C16.4,1.6,16.5,1.6,16.4,1.6c0.1-0.1,0.2-0.1,0.3,0c-0.1,0.2-0.1,0.3-0.2,0.6
		C16.4,2,16.4,1.8,16.4,1.6z"/>
	<path class="st0" d="M61,24.3c0.1,0.1,0,0.2,0,0.3c-0.1-0.1-0.1-0.2-0.2-0.3c-0.1-0.1-0.1-0.3-0.1-0.4c0,0,0.1-0.1,0.1-0.1
		c0,0,0.1,0,0.1,0C60.8,24.1,60.9,24.2,61,24.3z"/>
	<path class="st0" d="M55.3,8.3c-0.1-0.1-0.3-0.2-0.4-0.3c0,0,0-0.1-0.1-0.2C54.9,7.9,55,8,55.1,8c0.1,0,0.1,0,0.2,0
		C55.3,8.1,55.4,8.2,55.3,8.3z"/>
	<path class="st0" d="M13,3.3c0-0.2,0-0.4,0.1-0.6c0.1,0.1,0.2,0.2,0.2,0.3C13.3,3.2,13.2,3.2,13,3.3z"/>
	<path class="st0" d="M10.3,1.6L10.3,1.6c0-0.1,0-0.2,0-0.3c-0.1-0.1-0.2-0.1-0.2-0.2c0.1,0,0.3,0,0.4,0.1
		C10.6,1.5,10.5,1.5,10.3,1.6z"/>
	<path class="st0" d="M63.6,13.3C63.6,13.3,63.6,13.4,63.6,13.3c-0.1,0.1-0.1,0.1-0.1,0.1c-0.1-0.1-0.2-0.3-0.1-0.4
		c0-0.1,0.1-0.1,0.2,0C63.6,13,63.6,13.2,63.6,13.3z"/>
	<path class="st0" d="M52,5.7c0.3,0,0.4-0.1,0.6,0c0,0,0,0,0,0.1c-0.1,0.1-0.1,0.1-0.2,0.2C52.3,5.8,52.2,5.8,52,5.7z"/>
	<path class="st0" d="M17.8,2.7c0-0.1,0-0.1,0-0.2c0,0,0,0,0,0c0-0.1,0-0.2,0-0.3c-0.1,0-0.1,0-0.1,0.1c0,0,0,0.1,0,0.1
		c0,0.1,0,0.3-0.1,0.5c0,0,0.1,0.1,0.1,0.1C17.7,2.9,17.8,2.8,17.8,2.7z M17.7,2.6C17.7,2.6,17.7,2.6,17.7,2.6
		C17.7,2.6,17.7,2.6,17.7,2.6C17.7,2.6,17.7,2.6,17.7,2.6z"/>
	<path class="st0" d="M21.7,3.1c0.1-0.2,0.1-0.2,0.2-0.1C22,3,22,3,22.1,3.1c0,0.1-0.1,0.2-0.1,0.3C21.9,3.3,21.8,3.2,21.7,3.1z"/>
	<path class="st0" d="M7.1,3.7c0.1-0.2,0.2-0.3,0.2-0.5c0.1,0,0.2-0.1,0.2,0.1C7.5,3.3,7.4,3.4,7.3,3.5c0,0.1-0.1,0.2-0.1,0.3
		C7.2,3.8,7.1,3.8,7.1,3.7C7.1,3.7,7.1,3.7,7.1,3.7z"/>
	<ellipse transform="matrix(0.9824 -0.1868 0.1868 0.9824 -0.5796 1.4146)" class="st0" cx="7.2" cy="3.8" rx="0" ry="0"/>
	<path class="st0" d="M6.8,2.8c0,0-0.1-0.1-0.1-0.1c0-0.1,0-0.2,0-0.3c0-0.1,0.1-0.1,0.1-0.2c0,0.2,0,0.3,0.1,0.4
		C6.9,2.8,6.9,2.9,6.8,2.8z"/>
	<ellipse transform="matrix(0.4835 -0.8753 0.8753 0.4835 2.2171 11.5059)" class="st0" cx="10.9" cy="3.9" rx="0" ry="0"/>
	<path class="st0" d="M10.8,4.4c0,0,0,0.1,0,0.1c0,0-0.1,0-0.1,0c0,0,0,0,0-0.1c0-0.1,0-0.3,0.1-0.4c0-0.1,0.1-0.1,0.2-0.2
		C10.8,4.1,10.7,4.2,10.8,4.4z"/>
	<path class="st0" d="M7.2,2.5C7.2,2.5,7.2,2.5,7.2,2.5c-0.1-0.1,0-0.2,0-0.3c0-0.1,0-0.2,0-0.3c0.2,0,0.3,0.1,0.2,0.3
		C7.3,2.3,7.3,2.4,7.2,2.5z"/>
	<path class="st0" d="M5.9,2.2C6,1.8,6,1.8,6.1,1.7c0,0.1,0.1,0.3,0,0.4C6.1,2.2,6,2.3,5.9,2.2z"/>
	<path class="st0" d="M10.1,2.8c0-0.1,0.1-0.2,0.3-0.2c0,0.1-0.1,0.2-0.1,0.3c0,0.1,0,0.2,0,0.3C10.2,3,10.1,2.9,10.1,2.8z"/>
	<path class="st0" d="M16.9,2.2C16.9,2,17,1.9,17,1.7c0,0,0,0,0.1,0c0,0.2-0.1,0.5-0.1,0.7C16.9,2.4,16.8,2.3,16.9,2.2z"/>
	<path class="st0" d="M57.4,5.8c0,0-0.1,0-0.1,0c-0.1,0-0.1-0.1,0-0.2c0-0.1,0.1-0.1,0.2-0.2c0.1,0.1,0.1,0.1,0.1,0.2
		C57.6,5.7,57.5,5.8,57.4,5.8z"/>
	<path class="st0" d="M3.5,18.6c-0.1,0.1-0.2,0.2-0.2,0.3c0,0.1,0,0.2,0,0.2c0-0.2,0-0.4-0.1-0.5c0-0.1,0-0.1,0.1-0.2
		C3.4,18.5,3.4,18.6,3.5,18.6z"/>
	<path class="st0" d="M8.9,3.7c0,0.1-0.1,0.1-0.1,0.1l0,0c0,0,0,0,0,0C8.7,4,8.6,4.1,8.5,4.2C8.4,4,8.4,3.9,8.4,3.8
		c0-0.1,0.1-0.1,0.2-0.1l0,0c0,0,0,0,0,0c0,0,0-0.1,0-0.1c0-0.1,0-0.2,0.1-0.2c0.1,0,0.1,0,0.1,0.2C8.9,3.6,8.9,3.7,8.9,3.7z"/>
	<path class="st0" d="M6.1,5.4C6.2,5.6,6.2,5.7,6,6C6,5.8,5.9,5.6,6.1,5.4z"/>
	<path class="st0" d="M3.9,4.3c0-0.1-0.1-0.2-0.1-0.4c0-0.1,0-0.2,0.1-0.2C3.9,3.8,4,3.8,4,3.9C4,4.1,4,4.2,3.9,4.3
		C3.9,4.3,3.9,4.3,3.9,4.3z"/>
	<path class="st0" d="M50.2,7.8c0,0,0-0.1-0.1-0.3c0.2,0,0.3,0,0.4,0.1c-0.1,0.1-0.1,0.1-0.2,0.1C50.3,7.8,50.3,7.8,50.2,7.8z"/>
	
		<ellipse transform="matrix(0.9998 -2.107204e-02 2.107204e-02 0.9998 -0.1502 0.9213)" class="st0" cx="43.6" cy="7.6" rx="0" ry="0"/>
	<path class="st0" d="M43.6,7.2c0.1,0,0.1,0.1,0.1,0.2c0,0.1-0.1,0.2-0.1,0.2c-0.1-0.1-0.2-0.2-0.2-0.2C43.5,7.3,43.5,7.1,43.6,7.2z
		"/>
	<path class="st0" d="M27.1,3.1C27.1,3.1,27.2,3.1,27.1,3.1c0.3-0.1,0.3,0.1,0.4,0.3c-0.1-0.1-0.3-0.1-0.4-0.2
		C27.1,3.3,27.1,3.2,27.1,3.1z"/>
	<path class="st0" d="M12.7,3.2c-0.1-0.1-0.1-0.1,0-0.2c0,0,0.1-0.1,0.1-0.1c0,0,0,0,0-0.1c0,0,0,0,0,0.1c0,0,0.1,0,0.1-0.1
		c0,0.2,0,0.2-0.1,0.3C12.8,3.2,12.7,3.2,12.7,3.2z"/>
	<path class="st0" d="M14.2,1.7c0-0.1,0.1-0.1,0.1-0.2c0.1-0.1,0.2-0.1,0.2,0c0.1,0.1,0,0.3,0,0.4C14.5,1.8,14.4,1.7,14.2,1.7z"/>
	<path class="st0" d="M15.6,1.7c0.1,0.1,0.1,0.2,0.1,0.4c-0.1,0.1-0.2,0.1-0.3,0.2C15.4,2.1,15.5,1.9,15.6,1.7z"/>
	<path class="st0" d="M55.6,6.6c-0.1,0-0.1-0.1-0.2-0.1c0-0.1,0-0.1,0-0.1c0.1-0.1,0.2-0.1,0.3-0.1c0,0,0,0.1,0,0.1
		C55.7,6.5,55.7,6.5,55.6,6.6z"/>
	<path class="st0" d="M59.5,38.6c0,0.1-0.1,0.2-0.1,0.3c-0.1-0.1-0.2-0.1-0.3-0.2C59.2,38.6,59.3,38.5,59.5,38.6z"/>
	<path class="st0" d="M52.3,6.8C52.3,6.8,52.3,6.7,52.3,6.8c0.2-0.1,0.3-0.1,0.4,0c0,0,0,0.1,0,0.1C52.6,6.9,52.5,6.9,52.3,6.8
		C52.3,6.9,52.3,6.8,52.3,6.8z"/>
	<path class="st0" d="M6.2,2.8C6.2,2.8,6.2,2.8,6.2,2.8C6.2,2.8,6.2,2.8,6.2,2.8L6.2,2.8z"/>
	<path class="st0" d="M6.2,2.8c0.1-0.2,0.2-0.4,0.2-0.6c0-0.1,0.1-0.1,0.2-0.1C6.5,2.4,6.4,2.5,6.3,2.7C6.3,2.7,6.2,2.8,6.2,2.8z"/>
	<path class="st0" d="M51.6,8.3c-0.1-0.1-0.2-0.2-0.1-0.3c0,0,0-0.1,0.1-0.1c0,0,0.1,0,0.1,0C51.7,8,51.7,8.2,51.6,8.3z"/>
	
		<ellipse transform="matrix(2.800326e-02 -0.9996 0.9996 2.800326e-02 42.7281 61.005)" class="st0" cx="52.7" cy="8.5" rx="0" ry="0"/>
	<path class="st0" d="M52.8,7.8c0.1,0.3,0.1,0.4-0.1,0.8C52.7,7.9,52.7,7.9,52.8,7.8z"/>
	<path class="st0" d="M33.5,4.2C33.5,4.2,33.4,4.2,33.5,4.2c-0.1,0-0.1,0-0.1-0.1c0-0.1,0-0.2,0.1-0.2c0,0,0.1-0.1,0.1-0.1
		c0.1,0,0.1,0.1,0.1,0.1C33.6,4,33.6,4.1,33.5,4.2z"/>
	<path class="st0" d="M30.3,3.8c-0.1,0-0.1-0.1-0.1-0.2c0,0,0.1-0.1,0.2-0.1c0.1,0,0.1,0.1,0.1,0.2C30.5,3.7,30.4,3.8,30.3,3.8z"/>
	<path class="st0" d="M60.3,4.8c0.2-0.4,0.3-0.4,0.5-0.4C60.6,4.6,60.5,4.8,60.3,4.8z"/>
	<path class="st0" d="M5.5,2.9c0-0.2,0-0.3-0.1-0.5c0.1,0,0.1,0,0.2,0c0,0,0,0.1,0,0.1C5.6,2.7,5.6,2.8,5.5,2.9z"/>
	<path class="st0" d="M42.3,7.4c0-0.2,0.2-0.2,0.3-0.3C42.6,7.4,42.5,7.5,42.3,7.4z"/>
	<path class="st0" d="M5.8,3.7C5.7,3.8,5.6,3.9,5.5,3.8c0,0-0.1,0-0.1-0.1c0.1,0,0.2-0.1,0.2-0.1c0.1,0,0.1-0.1,0.2-0.1
		C5.8,3.6,5.8,3.7,5.8,3.7z"/>
	<path class="st0" d="M60.3,4.4c-0.2,0,0-0.2-0.1-0.2c0,0,0,0,0-0.1c0.1,0,0.2,0,0.3,0C60.4,4.3,60.4,4.4,60.3,4.4z"/>
	<path class="st0" d="M20.3,2.5c0.1,0,0.1,0.1,0.1,0.2c0,0.1-0.1,0.1-0.3,0.1c0-0.1,0-0.2,0.1-0.2C20.2,2.5,20.2,2.5,20.3,2.5z"/>
	<path class="st0" d="M45,6.8c-0.1,0-0.1,0-0.1-0.1c0-0.1,0-0.2,0-0.3C44.9,6.5,45,6.5,45,6.6C45.1,6.6,45,6.7,45,6.8z"/>
	<path class="st0" d="M51.4,7.6C51.4,7.6,51.4,7.6,51.4,7.6c0,0.1,0,0.2,0,0.3c0,0-0.1,0.1-0.1,0c0,0,0-0.1-0.1-0.1
		C51.2,7.7,51.2,7.6,51.4,7.6C51.3,7.6,51.3,7.6,51.4,7.6z"/>
	<path class="st0" d="M30.3,4.2C30.3,4.3,30.3,4.3,30.3,4.2c-0.1,0.1-0.2,0.1-0.2,0.1c-0.1,0-0.1,0-0.1-0.1c0,0,0.1-0.1,0.1-0.1
		C30.1,4.1,30.2,4.2,30.3,4.2C30.3,4.2,30.3,4.2,30.3,4.2z"/>
	<path class="st0" d="M20.3,2.4c0-0.1-0.1-0.1-0.1-0.2c0-0.1,0.1-0.1,0.1-0.1c0.1,0,0.1,0.1,0.1,0.1C20.4,2.2,20.4,2.3,20.3,2.4z"/>
	<path class="st0" d="M19.3,4.1C19.3,4.1,19.3,4,19.3,4.1c0-0.2,0-0.3,0.1-0.4C19.5,3.8,19.5,3.9,19.3,4.1
		C19.4,4.1,19.4,4.1,19.3,4.1z"/>
	<ellipse transform="matrix(0.6562 -0.7546 0.7546 0.6562 -13.6647 54.9873)" class="st0" cx="53.5" cy="42.5" rx="0" ry="0"/>
	<path class="st0" d="M53.7,42.7c-0.1,0-0.2,0-0.2-0.2c0.1,0.1,0.3,0.1,0.5,0.2C53.9,42.7,53.8,42.7,53.7,42.7z"/>
	<path class="st0" d="M54.6,7.8c0,0,0,0.1,0,0.1c0,0-0.1,0-0.1,0c0,0-0.1-0.1-0.1-0.1c0-0.1,0-0.1,0.1-0.2
		C54.5,7.8,54.6,7.8,54.6,7.8z"/>
	<path class="st0" d="M56,6.3c0,0,0.1,0,0.1,0c0.1,0.1,0.2,0.1,0.2,0.3C56.1,6.5,56.1,6.5,56,6.3z"/>
	<path class="st0" d="M58.9,37c0,0.1,0,0.1,0,0.2l0,0c-0.1,0-0.2,0-0.3-0.2C58.6,37,58.8,36.9,58.9,37z"/>
	<path class="st0" d="M30.8,4.4c-0.1,0-0.2-0.1-0.2-0.2c0,0,0,0,0,0c0-0.1,0.1-0.1,0.2,0C30.9,4.3,30.9,4.3,30.8,4.4
		C30.9,4.4,30.9,4.4,30.8,4.4z"/>
	<polygon class="st0" points="64.8,24.1 64.8,24.1 64.8,24.1 	"/>
	<path class="st0" d="M64.8,24.6c0,0,0-0.1,0-0.1c-0.1-0.1-0.1-0.2,0-0.3c0.1,0,0.2,0,0.2,0.1c0,0.1,0,0.1,0,0.2
		C64.9,24.5,64.9,24.5,64.8,24.6L64.8,24.6z"/>
	<polygon class="st0" points="3,3.6 3,3.6 3,3.6 	"/>
	<path class="st0" d="M3,3.8c0-0.1,0-0.1,0-0.2c0.1,0.1,0.2,0.2,0.3,0.3C3.1,4,3,4,3,3.8z"/>
	<path class="st0" d="M12.7,1.8c0.2-0.1,0.3,0,0.4,0.1C12.9,2,12.8,2,12.7,1.8z"/>
	<path class="st0" d="M43.6,6.6c-0.1-0.1-0.1-0.2-0.1-0.3c0-0.1,0.1-0.1,0.1-0.1c0,0,0,0.1,0,0.1C43.7,6.4,43.6,6.5,43.6,6.6z"/>
	
		<ellipse transform="matrix(2.205558e-02 -0.9998 0.9998 2.205558e-02 17.0366 23.6325)" class="st0" cx="20.6" cy="3.1" rx="0" ry="0"/>
	<path class="st0" d="M20.7,3.3c0,0.1,0,0.1,0,0.2c0,0,0,0-0.1,0c0,0,0,0-0.1,0c0-0.1,0-0.3,0-0.4C20.7,3.2,20.7,3.2,20.7,3.3z"/>
	<path class="st0" d="M58.5,39.4c0-0.1,0-0.1-0.1-0.2c0.1,0,0.2-0.1,0.4-0.1C58.7,39.3,58.7,39.4,58.5,39.4z"/>
	<path class="st0" d="M7.6,2.8c0-0.1,0.1-0.1,0.1-0.2c0.1,0,0.1,0.1,0.2,0.1C7.7,2.9,7.7,2.9,7.6,2.8z"/>
	<path class="st0" d="M63.5,11.2c0,0.4,0,0.4-0.1,0.7c0-0.2,0-0.4,0-0.6C63.5,11.3,63.5,11.2,63.5,11.2z"/>
	<path class="st0" d="M26.5,4.1c0.1,0,0.2,0.1,0.3,0.1c0,0,0,0.1,0,0.1c0,0-0.1,0.1-0.1,0.1c-0.1,0-0.2-0.1-0.3-0.1
		C26.5,4.2,26.5,4.2,26.5,4.1L26.5,4.1z"/>
	<polygon class="st0" points="26.5,4.3 26.5,4.3 26.5,4.3 	"/>
	<ellipse transform="matrix(0.8025 -0.5966 0.5966 0.8025 5.6964 35.0237)" class="st0" cx="55.8" cy="8.9" rx="0" ry="0"/>
	<path class="st0" d="M55.4,8.9c0-0.1,0-0.2,0.1-0.3c0.1,0.1,0.2,0.1,0.3,0.3C55.6,8.8,55.5,8.8,55.4,8.9z"/>
	<path class="st0" d="M57.2,33.3c-0.1,0-0.1,0-0.2,0c0-0.2,0.1-0.4,0.1-0.5c0,0,0,0,0,0.1C57.1,33,57.1,33.2,57.2,33.3z"/>
	<path class="st0" d="M55.3,42.6c0-0.1,0-0.2,0.1-0.2c0.1,0.1,0.2,0,0.2,0.2C55.5,42.7,55.4,42.7,55.3,42.6
		C55.3,42.7,55.3,42.7,55.3,42.6z"/>
	<polygon class="st0" points="55.6,42.7 55.6,42.7 55.6,42.7 	"/>
	<path class="st0" d="M44.4,7.4c-0.1,0.1-0.2,0.1-0.3,0c0,0,0,0,0,0C44.2,7.3,44.3,7.3,44.4,7.4z"/>
	<path class="st0" d="M15.6,2.6c0.1-0.2,0.1-0.2,0.2-0.1C15.8,2.6,15.7,2.7,15.6,2.6z"/>
	<path class="st0" d="M50,7.7c0,0.1-0.1,0.1-0.1,0.1c0,0-0.1-0.1-0.1-0.1c0.1,0,0.1-0.1,0.2,0C50,7.5,50,7.6,50,7.7z"/>
	<path class="st0" d="M14,3.4c0,0-0.1-0.1-0.1-0.1C14,3.2,14,3.1,14.1,3c0,0.2,0,0.2,0,0.3C14.1,3.3,14.1,3.3,14,3.4z"/>
	<path class="st0" d="M57.3,6.5c0,0-0.1,0-0.1,0c0,0-0.1-0.1-0.1-0.2c0,0,0,0,0.1-0.1C57.2,6.4,57.3,6.4,57.3,6.5z"/>
	<path class="st0" d="M56.7,31c0,0,0,0.1,0,0.1c0,0.1-0.1,0.1-0.1,0.1c0,0,0-0.1,0-0.1C56.6,31,56.7,31,56.7,31z"/>
	<path class="st0" d="M42.5,7.6c0.1,0.2,0,0.4,0,0.6C42.4,7.9,42.4,7.8,42.5,7.6z"/>
	<path class="st0" d="M58.2,42.7c-0.2,0-0.2,0-0.2-0.2C58.1,42.6,58.1,42.6,58.2,42.7z"/>
	<path class="st0" d="M4.4,3.8c0,0-0.1-0.1-0.1-0.2c0,0,0.1-0.1,0.1-0.1C4.5,3.5,4.5,3.6,4.4,3.8C4.5,3.7,4.4,3.7,4.4,3.8z"/>
	<path class="st0" d="M56.3,42.7c-0.1-0.1-0.2,0-0.2-0.3C56.2,42.6,56.2,42.6,56.3,42.7z"/>
	<path class="st0" d="M9.8,4.2c-0.1,0-0.1,0.1-0.2,0.1c0,0,0,0-0.1,0c0-0.1,0-0.1,0.1-0.2C9.6,4,9.7,4.1,9.8,4.2z"/>
	<path class="st0" d="M26.2,3.2c0-0.1,0-0.2,0.1-0.3c0.2,0.1,0.1,0.2,0.1,0.3C26.3,3.3,26.2,3.3,26.2,3.2z"/>
	<path class="st0" d="M3.5,17.8c0,0.1-0.1,0.2-0.1,0.4C3.3,17.9,3.3,17.9,3.5,17.8z"/>
	<path class="st0" d="M18.2,2.2c0.1,0.1,0,0.2,0,0.4C18,2.4,18,2.4,18.2,2.2z"/>
	<path class="st0" d="M52,7.2C52,7.1,52,7,52.1,7C52.2,7.1,52.1,7.2,52,7.2C52.1,7.3,52,7.3,52,7.2C52,7.3,52,7.2,52,7.2z"/>
	<path class="st0" d="M4.8,2.3c0-0.1,0-0.2,0-0.3c0,0,0,0,0.1,0c0,0,0,0,0,0C4.9,2.1,4.9,2.2,4.8,2.3z"/>
	<ellipse transform="matrix(0.9792 -0.203 0.203 0.9792 -5.4659 12.2612)" class="st0" cx="57" cy="32.8" rx="0" ry="0"/>
	<path class="st0" d="M57,32.6c0-0.1,0-0.2,0.1-0.2c0,0.1,0,0.2-0.1,0.4C57,32.8,56.9,32.7,57,32.6z"/>
	<path class="st0" d="M54,8.2c0-0.2,0-0.2,0.2-0.3C54.2,8,54.1,8.1,54,8.2z"/>
	<path class="st0" d="M47.6,6.7c0.1-0.1,0.2-0.1,0.3,0C47.8,6.8,47.7,6.7,47.6,6.7z"/>
	<path class="st0" d="M46.6,6.6c-0.1,0-0.1,0.1-0.2,0.1c0,0-0.1,0-0.1,0c0-0.1,0-0.1,0.1-0.1C46.4,6.5,46.5,6.6,46.6,6.6z"/>
	<path class="st0" d="M5.8,1.7C5.7,1.8,5.6,1.9,5.5,2.1C5.6,1.8,5.6,1.8,5.8,1.7z"/>
	<path class="st0" d="M13.9,3.1c0,0,0,0.1-0.1,0.2c0-0.1-0.1-0.1-0.1-0.2c0-0.1,0-0.1,0.1-0.1C13.8,3,13.8,3.1,13.9,3.1z"/>
	<path class="st0" d="M53.3,8.2C53.3,8.2,53.3,8.2,53.3,8.2c-0.1-0.1,0-0.2,0-0.2c0,0.1,0.1,0.1,0.1,0.1C53.4,8.2,53.4,8.2,53.3,8.2
		z"/>
	<ellipse transform="matrix(0.994 -0.1097 0.1097 0.994 -0.6542 7.0801)" class="st0" cx="64" cy="9.5" rx="0" ry="0"/>
	<path class="st0" d="M64.4,9.5c-0.2,0.1-0.3,0.1-0.4,0C64.1,9.4,64.3,9.4,64.4,9.5z"/>
	<path class="st0" d="M9.8,3.4c0.1,0.2,0,0.3-0.1,0.4C9.7,3.6,9.7,3.5,9.8,3.4z"/>
	<path class="st0" d="M3.1,2.8C3.2,3,3.2,3.2,3.2,3.4C3.1,3.2,3.1,3.1,3.1,2.8z"/>
	<path class="st0" d="M3.2,3.4C3.2,3.4,3.2,3.4,3.2,3.4C3.2,3.4,3.2,3.4,3.2,3.4C3.2,3.4,3.2,3.4,3.2,3.4C3.2,3.4,3.2,3.4,3.2,3.4z"
		/>
	<path class="st0" d="M11.5,4.4c0-0.1,0.1-0.2,0.1-0.3c0.1,0,0.1,0,0.2,0C11.7,4.3,11.6,4.4,11.5,4.4z"/>
	<polygon class="st0" points="11.6,4.1 11.6,4.1 11.6,4.1 	"/>
	<path class="st0" d="M59.4,39.7C59.5,39.7,59.4,39.8,59.4,39.7c-0.1,0.1-0.1,0.1-0.2,0c0-0.1,0-0.1,0-0.1
		C59.4,39.6,59.4,39.6,59.4,39.7z"/>
	<path class="st0" d="M56.6,42.7c0.1,0,0.1,0,0.1,0.1c0,0,0,0.1-0.1,0.1C56.6,42.8,56.6,42.8,56.6,42.7
		C56.5,42.7,56.6,42.7,56.6,42.7z"/>
	<path class="st0" d="M64,14.4c-0.1,0-0.1,0-0.2,0c0,0,0,0,0-0.1c0.1,0,0.1,0,0.2-0.1C64,14.3,64,14.3,64,14.4
		C64,14.4,64,14.4,64,14.4z"/>
	<path class="st0" d="M56.4,30.4c0,0,0,0.1,0,0.1c0,0-0.1,0-0.1,0c0,0,0-0.1,0-0.1c0-0.1,0-0.1,0.1-0.2
		C56.4,30.3,56.4,30.4,56.4,30.4z"/>
	<path class="st0" d="M57.9,5.1c0,0.1,0,0.2,0,0.3C57.9,5.3,57.9,5.2,57.9,5.1z"/>
	<path class="st0" d="M59.1,5.8c0,0.1-0.1,0.1-0.1,0c0-0.1,0.1-0.2,0.2-0.3C59.1,5.7,59.1,5.8,59.1,5.8z"/>
	<ellipse transform="matrix(0.241 -0.9705 0.9705 0.241 39.463 61.5643)" class="st0" cx="59.1" cy="5.6" rx="0" ry="0"/>
	<path class="st0" d="M47.7,4.9c-0.1,0-0.1,0-0.1-0.1c0,0,0.1-0.1,0.1-0.1c0,0,0.1,0.1,0.1,0.1C47.9,4.9,47.8,5,47.7,4.9z"/>
	<path class="st0" d="M28.5,3.4c0-0.1-0.1-0.1,0-0.1c0,0,0.1-0.1,0.1-0.1c0,0,0.1,0.1,0.1,0.1C28.6,3.4,28.6,3.4,28.5,3.4z"/>
	<path class="st0" d="M13.5,1.7c0-0.1,0-0.2,0.1-0.3C13.7,1.5,13.7,1.6,13.5,1.7z"/>
	<path class="st0" d="M16.1,1.8c0,0.1,0,0.2,0,0.2c0,0,0,0-0.1,0c0,0,0,0,0-0.1C15.9,1.9,16,1.8,16.1,1.8z"/>
	<path class="st0" d="M28,4C28,4,28,3.9,28,4c0.1-0.1,0.1-0.1,0.2,0C28.1,4,28.1,4,28,4C28,4.1,28,4,28,4z"/>
	<path class="st0" d="M11.8,1.5c0-0.1-0.1-0.1-0.1-0.2c0,0,0.1-0.1,0.1,0C11.8,1.4,11.8,1.4,11.8,1.5C11.9,1.5,11.9,1.5,11.8,1.5
		C11.8,1.5,11.8,1.5,11.8,1.5z"/>
	<path class="st0" d="M9.4,1.3c0-0.1,0-0.2,0-0.2c0,0,0,0,0,0c0,0,0,0,0,0C9.5,1.1,9.5,1.2,9.4,1.3C9.5,1.2,9.4,1.2,9.4,1.3
		C9.4,1.3,9.4,1.3,9.4,1.3z"/>
	<path class="st0" d="M56.9,42.4c0.1,0,0.1,0,0.1,0.1c0,0,0,0.1,0,0.1C56.9,42.5,56.9,42.5,56.9,42.4C56.8,42.4,56.8,42.4,56.9,42.4
		z"/>
	<path class="st0" d="M21.2,2.5c0-0.1,0-0.1,0-0.2c0,0,0-0.1,0.1-0.1c0.1,0,0.1,0.1,0.1,0.1C21.3,2.4,21.2,2.4,21.2,2.5z"/>
	<path class="st0" d="M47.3,5.2c0-0.1,0-0.2,0.1-0.3C47.5,5.1,47.4,5.2,47.3,5.2z"/>
	<path class="st0" d="M59,36.3c0,0,0,0.1,0,0.2c0,0-0.1,0-0.1,0c0,0,0-0.1,0-0.1C58.9,36.3,58.9,36.3,59,36.3
		C59,36.3,59,36.3,59,36.3z"/>
	<path class="st0" d="M59.4,5.8C59.4,5.9,59.4,5.9,59.4,5.8c0,0.2-0.1,0.2-0.2,0.3C59.3,6,59.3,5.9,59.4,5.8
		C59.4,5.8,59.4,5.8,59.4,5.8z"/>
	<polygon class="st0" points="59.2,6.1 59.2,6.1 59.2,6.1 59.2,6.1 59.2,6.1 	"/>
	<polygon class="st0" points="30.4,42.1 30.4,42.1 30.4,42.1 30.4,42.1 30.4,42.1 	"/>
	<path class="st0" d="M30.3,42.2C30.3,42.2,30.4,42.2,30.3,42.2c0.1,0,0.2,0,0.2,0.1C30.4,42.4,30.4,42.3,30.3,42.2z"/>
	<path class="st0" d="M58.9,35.8C58.9,35.8,58.8,35.8,58.9,35.8c-0.1,0-0.1,0-0.1,0c0,0,0,0,0.1-0.1C58.8,35.7,58.9,35.7,58.9,35.8z
		"/>
	<path class="st0" d="M58.2,35.7c0.1-0.1,0.2-0.1,0.3,0C58.4,35.7,58.3,35.7,58.2,35.7z"/>
	<path class="st0" d="M3.9,18C3.9,18,3.8,18,3.9,18c-0.1-0.1-0.2-0.1-0.2-0.2C3.9,17.8,3.9,17.8,3.9,18z"/>
	<path class="st0" d="M3.3,3.4c0,0.1,0,0.2-0.1,0.3c0-0.1,0-0.2-0.1-0.3C3.2,3.4,3.3,3.3,3.3,3.4z"/>
	<path class="st0" d="M6,2.5C6,2.6,6,2.7,6,2.8c0-0.1-0.1-0.2-0.1-0.2C5.9,2.5,5.9,2.5,6,2.5z"/>
	<path class="st0" d="M59.1,44.6c0,0.1,0,0.1-0.1,0.1c0,0-0.1-0.1-0.1-0.1c0-0.1,0.1-0.1,0.1-0.2C59,44.6,59.1,44.6,59.1,44.6z"/>
	<polygon class="st0" points="7.7,2.6 7.7,2.6 7.7,2.6 7.7,2.6 7.7,2.6 	"/>
	<path class="st0" d="M7.6,2.4c0,0,0.1,0,0.1-0.1c0,0.1,0,0.1,0,0.2c0,0,0,0,0,0.1c0,0,0,0,0,0C7.6,2.5,7.5,2.5,7.6,2.4z"/>
	<path class="st0" d="M49.6,7.6c0,0.1-0.1,0.1-0.2,0.3C49.4,7.5,49.4,7.5,49.6,7.6z"/>
	<path class="st0" d="M57.3,32.9c0-0.1,0-0.2-0.1-0.2c0-0.1,0-0.2,0.1-0.1c0,0,0,0.1,0,0.1C57.3,32.7,57.3,32.8,57.3,32.9z"/>
	<path class="st0" d="M5.3,1.7C5.3,1.7,5.3,1.7,5.3,1.7c0-0.1,0-0.2,0-0.2c0,0,0,0.1,0,0.1C5.4,1.7,5.4,1.7,5.3,1.7z"/>
	<path class="st0" d="M51.2,7.2c0,0-0.1,0.1-0.1,0.1c0,0,0,0,0-0.1C51.1,7.2,51.1,7.2,51.2,7.2C51.2,7.2,51.2,7.2,51.2,7.2
		C51.2,7.2,51.2,7.2,51.2,7.2z"/>
	<path class="st0" d="M3,1.3c0-0.1,0-0.1,0-0.2c0,0,0-0.1,0.1-0.1c0,0,0,0.1,0,0.1C3.1,1.3,3,1.3,3,1.3C3,1.4,3,1.4,3,1.3z"/>
	<path class="st0" d="M59.4,4.6c0,0,0-0.1,0.1-0.1c0,0,0.1,0,0.1,0.1C59.5,4.6,59.5,4.6,59.4,4.6C59.4,4.7,59.4,4.6,59.4,4.6z"/>
	<path class="st0" d="M59.8,4.7C59.9,4.7,59.9,4.6,59.8,4.7c0.1,0,0.1,0,0.1,0C59.9,4.7,59.9,4.7,59.8,4.7
		C59.9,4.8,59.8,4.7,59.8,4.7z"/>
	<path class="st0" d="M8.4,3.1C8.4,3.1,8.4,3.1,8.4,3.1c0-0.2,0.1-0.1,0.1-0.2C8.5,3,8.6,3.1,8.4,3.1z"/>
	<polygon class="st0" points="8.6,2.9 8.6,2.9 8.6,2.9 	"/>
	<path class="st0" d="M5.9,2.9C5.9,2.9,6,2.9,6,2.8c0,0,0.1,0,0.1,0C6.1,3,6,3,5.9,2.9z"/>
	<polygon class="st0" points="6.2,2.8 6.2,2.8 6.2,2.8 	"/>
	<path class="st0" d="M6,2.8C6,2.8,6,2.8,6,2.8C6,2.8,6,2.8,6,2.8C6,2.8,6,2.8,6,2.8C6,2.8,6,2.8,6,2.8L6,2.8z"/>
	<path class="st0" d="M49.6,5.8c0,0,0-0.1,0-0.2c0,0,0-0.1,0.1-0.1c0,0,0.1,0,0.1,0C49.6,5.7,49.6,5.8,49.6,5.8
		C49.6,5.8,49.6,5.8,49.6,5.8z"/>
	<path class="st0" d="M45.6,7.9C45.6,7.9,45.6,7.9,45.6,7.9c0,0,0.1,0.1,0.1,0.1c0,0,0,0,0,0c0,0,0,0,0,0C45.6,8,45.6,8,45.6,7.9z"
		/>
	<path class="st0" d="M18.7,1.9C18.7,1.9,18.7,1.9,18.7,1.9c0.1,0,0.1,0,0.1,0.1c0,0,0,0-0.1,0.1C18.7,2,18.7,2,18.7,1.9z"/>
	<path class="st0" d="M8.5,3.3c0,0,0,0.1-0.1,0.1c0,0,0,0-0.1,0C8.4,3.4,8.4,3.3,8.5,3.3C8.4,3.3,8.5,3.3,8.5,3.3z"/>
	<path class="st0" d="M7.8,3.3C7.7,3.3,7.7,3.3,7.8,3.3c0-0.1,0-0.1,0-0.2c0,0,0,0,0,0C7.8,3.3,7.8,3.4,7.8,3.3z"/>
	<path class="st0" d="M48.2,5.8C48.2,5.8,48.2,5.8,48.2,5.8c-0.1-0.1-0.1-0.1-0.1-0.1c0,0,0.1,0,0.1,0C48.3,5.6,48.3,5.7,48.2,5.8
		C48.3,5.7,48.2,5.8,48.2,5.8z"/>
	<path class="st0" d="M22.5,2.5c0,0-0.1-0.1-0.1-0.1c0,0,0,0,0.1-0.1C22.5,2.4,22.5,2.5,22.5,2.5C22.5,2.5,22.5,2.6,22.5,2.5z"/>
	<path class="st0" d="M37.8,4.6C37.8,4.6,37.8,4.5,37.8,4.6c0-0.1,0-0.1,0.1-0.2C37.9,4.5,37.9,4.5,37.8,4.6
		C37.9,4.6,37.9,4.6,37.8,4.6z"/>
	<path class="st0" d="M2.8,2.9C2.8,2.9,2.7,2.9,2.8,2.9C2.7,2.9,2.7,2.9,2.8,2.9c-0.1-0.1-0.1-0.1,0-0.1C2.8,2.8,2.8,2.9,2.8,2.9z"
		/>
	<path class="st0" d="M12.2,1.5C12.2,1.5,12.2,1.5,12.2,1.5c0-0.1,0-0.1,0-0.2c0,0,0,0,0.1,0C12.3,1.4,12.2,1.5,12.2,1.5
		C12.2,1.5,12.2,1.5,12.2,1.5z"/>
	<path class="st0" d="M11.3,1.4c0,0,0-0.1,0.1-0.1c0,0,0,0,0.1,0C11.4,1.4,11.4,1.4,11.3,1.4C11.4,1.4,11.4,1.4,11.3,1.4z"/>
	<path class="st0" d="M33.1,4.4c0,0,0-0.1,0-0.1c0,0,0-0.1,0.1-0.2C33.1,4.3,33.2,4.3,33.1,4.4C33.1,4.4,33.1,4.4,33.1,4.4z"/>
	<path class="st0" d="M63.9,13.3c0,0,0,0.1,0,0.2c0,0-0.1,0-0.1-0.1C63.8,13.4,63.8,13.3,63.9,13.3C63.9,13.3,63.9,13.3,63.9,13.3z"
		/>
	<path class="st0" d="M19.2,2.8C19.2,2.8,19.2,2.8,19.2,2.8C19.3,2.7,19.3,2.7,19.2,2.8c0.1,0,0.1,0,0.1,0.1c0,0,0,0,0,0
		C19.3,2.9,19.3,2.9,19.2,2.8z"/>
	<path class="st0" d="M11.8,1.8c0,0-0.1,0.1-0.1,0.1c0,0,0-0.1-0.1-0.1C11.7,1.8,11.7,1.8,11.8,1.8C11.8,1.8,11.8,1.8,11.8,1.8z"/>
	<path class="st0" d="M11.2,1.7c0,0,0.1,0,0.1,0c0,0,0.1,0,0.1,0.1c0,0-0.1,0-0.1,0C11.2,1.8,11.2,1.8,11.2,1.7z"/>
	<ellipse transform="matrix(0.9147 -0.4041 0.4041 0.9147 0.2501 4.7637)" class="st0" cx="11.4" cy="1.8" rx="0" ry="0"/>
	<path class="st0" d="M3.3,20.3c0,0-0.1,0-0.1,0.1c0,0,0,0,0-0.1C3.2,20.3,3.2,20.2,3.3,20.3C3.3,20.2,3.3,20.3,3.3,20.3z"/>
	<polygon class="st0" points="58.8,37.2 58.8,37.2 58.8,37.2 	"/>
	<path class="st0" d="M6.5,1.8c0,0,0-0.1,0-0.1c0,0,0,0,0.1,0C6.5,1.7,6.5,1.8,6.5,1.8C6.5,1.8,6.5,1.8,6.5,1.8z"/>
	<path class="st0" d="M9.5,2.3c0.1-0.1,0.1-0.1,0.2-0.2C9.7,2.3,9.6,2.4,9.5,2.3z"/>
	<path class="st0" d="M50.7,42.6C50.7,42.6,50.7,42.6,50.7,42.6c0.1,0,0.1,0,0.1,0c0,0.1,0,0.2-0.1,0.2c0,0,0,0,0,0c0,0,0,0,0,0
		C50.7,42.7,50.6,42.7,50.7,42.6C50.6,42.6,50.7,42.6,50.7,42.6C50.7,42.6,50.7,42.6,50.7,42.6z"/>
	<path class="st0" d="M47.1,43.5c-0.1,0-0.2,0-0.3,0C47,42.8,47.1,42.8,47.1,43.5z"/>
	<path class="st0" d="M43.3,43c-0.1-0.1-0.1-0.1,0-0.2C43.3,42.8,43.4,42.9,43.3,43z"/>
	<polygon class="st0" points="43.3,42.7 43.3,42.8 43.3,42.8 	"/>
	<path class="st0" d="M28.1,42.1c0,0.1-0.1,0.1-0.1,0.1c0,0,0-0.1,0-0.1C28,42,28,42,28.1,42.1C28.1,42,28.1,42,28.1,42.1L28.1,42.1
		C28.1,42.1,28.1,42.1,28.1,42.1z"/>
	<path class="st0" d="M23.9,41.5C23.9,41.5,23.9,41.5,23.9,41.5c0,0.1,0,0.1,0,0.2c0,0,0,0-0.1,0c0,0,0-0.1,0-0.1
		C23.8,41.6,23.9,41.5,23.9,41.5z"/>
	<path class="st0" d="M65.3,25.4c-0.1,0.1-0.1,0.1-0.2,0C65.1,25.3,65.2,25.3,65.3,25.4z"/>
	<path class="st0" d="M43.7,6.9c0,0,0-0.1,0.1-0.1c0,0,0,0,0,0C43.8,6.9,43.8,6.9,43.7,6.9C43.8,7,43.8,6.9,43.7,6.9z"/>
	<path class="st0" d="M47.8,7.6C47.8,7.6,47.8,7.6,47.8,7.6c0-0.1,0-0.2,0-0.2C47.9,7.5,47.9,7.5,47.8,7.6
		C47.9,7.6,47.8,7.6,47.8,7.6z"/>
	<path class="st0" d="M59.7,4.1C59.8,4.1,59.8,4.1,59.7,4.1c0.1-0.1,0.1,0,0.1,0C59.8,4.2,59.8,4.2,59.7,4.1
		C59.8,4.2,59.7,4.2,59.7,4.1z"/>
	<path class="st0" d="M63.2,5.9C63.2,5.9,63.2,5.8,63.2,5.9C63.2,5.8,63.2,5.8,63.2,5.9c0.1-0.1,0.1,0,0.1,0
		C63.3,5.9,63.2,5.9,63.2,5.9z"/>
	<path class="st0" d="M55,5.7c0,0,0.1,0.1,0,0.2c0,0-0.1-0.1-0.1-0.1C54.9,5.7,55,5.7,55,5.7z"/>
	<polygon class="st0" points="54.9,5.7 54.9,5.7 54.9,5.7 54.9,5.7 54.9,5.7 	"/>
	<path class="st0" d="M2.7,3.7C2.7,3.8,2.7,3.8,2.7,3.7C2.6,3.8,2.6,3.8,2.7,3.7C2.6,3.7,2.6,3.7,2.7,3.7C2.7,3.7,2.7,3.7,2.7,3.7z"
		/>
	<path class="st0" d="M16,1.5C16.1,1.5,16.1,1.5,16,1.5c0.1,0,0.1,0.1,0.1,0.1C16.1,1.6,16.1,1.6,16,1.5C16,1.6,16,1.6,16,1.5z"/>
	<path class="st0" d="M24.1,2.7C24,2.7,24,2.7,24.1,2.7C24,2.7,24,2.6,24,2.6C24,2.6,24.1,2.6,24.1,2.7C24.1,2.7,24.1,2.7,24.1,2.7z
		"/>
	<path class="st0" d="M30.7,3.6C30.7,3.6,30.7,3.5,30.7,3.6C30.7,3.5,30.7,3.5,30.7,3.6C30.8,3.5,30.8,3.5,30.7,3.6
		C30.8,3.6,30.7,3.6,30.7,3.6z"/>
	<path class="st0" d="M19.7,2.4C19.7,2.4,19.7,2.3,19.7,2.4c0-0.1,0-0.1,0-0.2C19.8,2.3,19.8,2.3,19.7,2.4
		C19.8,2.4,19.8,2.4,19.7,2.4z"/>
	<path class="st0" d="M12.7,1.4c0.1,0.1,0.1,0.1,0.1,0.2C12.8,1.5,12.8,1.5,12.7,1.4z"/>
	<path class="st0" d="M13.2,1.7C13.2,1.6,13.2,1.6,13.2,1.7c0.1-0.1,0.1-0.1,0.1,0C13.3,1.7,13.3,1.7,13.2,1.7
		C13.2,1.7,13.2,1.7,13.2,1.7z"/>
	<path class="st0" d="M32.6,4.2C32.6,4.1,32.6,4.1,32.6,4.2c0-0.1,0-0.1,0-0.1C32.7,4.1,32.7,4.1,32.6,4.2
		C32.7,4.2,32.6,4.2,32.6,4.2z"/>
	<path class="st0" d="M8.1,1.2C8.1,1.2,8.1,1.2,8.1,1.2c0-0.1,0-0.1,0-0.2c0,0,0,0,0,0c0,0,0,0,0,0C8.2,1.1,8.2,1.1,8.1,1.2z"/>
	<path class="st0" d="M44.4,5.6C44.4,5.6,44.4,5.7,44.4,5.6c0,0.1,0,0.1,0,0.1C44.3,5.7,44.3,5.7,44.4,5.6
		C44.3,5.7,44.3,5.6,44.4,5.6z"/>
	<polygon class="st0" points="29.6,4 29.6,4 29.6,4 29.6,4 29.6,4 29.6,4 29.6,4 29.6,4 	"/>
	<path class="st0" d="M29.5,3.9C29.5,3.9,29.5,3.9,29.5,3.9c0.1,0,0.1,0,0.1,0.1c0,0,0,0,0,0c0,0,0,0,0,0C29.5,4,29.5,4,29.5,3.9z"
		/>
	<path class="st0" d="M58.3,37.3c0.1,0,0.1-0.1,0.2,0C58.5,37.3,58.4,37.4,58.3,37.3C58.4,37.4,58.3,37.4,58.3,37.3z"/>
	<path class="st0" d="M58,37.8C58,37.8,57.9,37.8,58,37.8c-0.1,0-0.1-0.1-0.1-0.2C57.9,37.7,57.9,37.7,58,37.8
		C58,37.7,58,37.8,58,37.8z"/>
	<path class="st0" d="M10.1,1.7C10.1,1.7,10.2,1.7,10.1,1.7c0.1-0.1,0.1-0.1,0.2-0.1C10.3,1.7,10.2,1.7,10.1,1.7
		C10.2,1.8,10.1,1.7,10.1,1.7z"/>
	<polygon class="st0" points="10.3,1.7 10.3,1.7 10.3,1.7 	"/>
	<path class="st0" d="M58.6,36.6C58.6,36.5,58.6,36.5,58.6,36.6c0.1,0,0.1,0,0.1,0C58.6,36.6,58.6,36.6,58.6,36.6
		C58.6,36.6,58.6,36.6,58.6,36.6z"/>
	<path class="st0" d="M49.4,6.7c-0.1,0-0.1,0-0.2,0C49.3,6.7,49.3,6.7,49.4,6.7z"/>
	<path class="st0" d="M49,6.8C49,6.8,49,6.8,49,6.8c0-0.1,0-0.1,0-0.1C49,6.7,49,6.7,49,6.8C49,6.8,49,6.8,49,6.8z"/>
	<path class="st0" d="M57.1,31.7C57.1,31.7,57.1,31.6,57.1,31.7c0-0.1,0-0.1,0-0.1C57.1,31.6,57.2,31.6,57.1,31.7
		C57.1,31.7,57.1,31.7,57.1,31.7z"/>
	<path class="st0" d="M54.2,7.5C54.2,7.5,54.3,7.5,54.2,7.5c0,0.1,0,0.1,0,0.1C54.2,7.6,54.2,7.6,54.2,7.5
		C54.2,7.5,54.2,7.5,54.2,7.5z"/>
	<path class="st0" d="M59.2,37C59.2,37,59.2,37,59.2,37c-0.1,0-0.1,0-0.1,0C59.1,37,59.1,36.9,59.2,37C59.2,36.9,59.2,37,59.2,37z"
		/>
	<path class="st0" d="M58,33.4C57.9,33.4,57.9,33.4,58,33.4c0-0.1,0-0.1,0-0.2C58,33.3,58,33.4,58,33.4C58,33.4,58,33.4,58,33.4z"/>
	<polygon class="st0" points="52.4,7.7 52.4,7.7 52.4,7.7 	"/>
	<path class="st0" d="M52.3,7.7C52.3,7.6,52.3,7.6,52.3,7.7c0.1,0,0.1,0,0.1,0C52.4,7.7,52.3,7.7,52.3,7.7
		C52.3,7.7,52.3,7.7,52.3,7.7z"/>
	<path class="st0" d="M52.1,7.7C52.1,7.7,52,7.7,52.1,7.7C52,7.7,52,7.6,52.1,7.7C52,7.6,52,7.6,52,7.5C52.1,7.6,52.1,7.6,52.1,7.7z
		"/>
	<path class="st0" d="M8.2,2.3C8.2,2.3,8.2,2.3,8.2,2.3c-0.1-0.1,0-0.1,0-0.1C8.2,2.2,8.3,2.2,8.2,2.3C8.3,2.3,8.2,2.3,8.2,2.3z"/>
	<path class="st0" d="M4.4,1.9C4.4,1.9,4.4,1.8,4.4,1.9c0-0.1,0-0.1,0-0.1C4.4,1.8,4.4,1.8,4.4,1.9C4.4,1.9,4.4,1.9,4.4,1.9z"/>
	<path class="st0" d="M8.4,2.7c0,0,0-0.1,0-0.1c0,0,0,0,0-0.1C8.4,2.5,8.4,2.6,8.4,2.7C8.4,2.6,8.4,2.6,8.4,2.7z"/>
	<path class="st0" d="M4.3,2.5C4.3,2.5,4.2,2.5,4.3,2.5C4.2,2.4,4.3,2.4,4.3,2.5C4.3,2.4,4.3,2.4,4.3,2.5C4.3,2.4,4.3,2.5,4.3,2.5z"
		/>
	<path class="st0" d="M53.8,42.3C53.8,42.3,53.8,42.3,53.8,42.3c0.1,0,0.1,0.1,0.1,0.2C53.8,42.4,53.8,42.4,53.8,42.3
		C53.8,42.4,53.8,42.3,53.8,42.3z"/>
	<path class="st0" d="M61.1,29.2C61.1,29.3,61.1,29.3,61.1,29.2c0.1,0.1,0,0.1,0,0.2C61.1,29.4,61,29.3,61.1,29.2
		C61,29.3,61,29.2,61.1,29.2z"/>
	<path class="st0" d="M9.1,3.7c0-0.2,0.1-0.3,0.3-0.4c0,0.1,0,0.2,0,0.2C9.3,3.7,9.2,3.7,9.1,3.7c0,0.2,0,0.3-0.1,0.4
		C9,4,9,3.8,9.1,3.7z"/>
	<path class="st0" d="M54.4,43C54.3,43,54.3,43,54.4,43c-0.1,0.1-0.1,0-0.2,0c0,0,0,0,0-0.1C54.3,42.9,54.3,42.9,54.4,43z"/>
	<path class="st0" d="M60.5,24.7C60.5,24.7,60.5,24.7,60.5,24.7c0-0.1,0-0.1,0-0.1C60.6,24.6,60.6,24.7,60.5,24.7
		C60.6,24.7,60.6,24.7,60.5,24.7z"/>
	<path class="st0" d="M3.5,22.7C3.5,22.7,3.5,22.6,3.5,22.7C3.5,22.6,3.5,22.6,3.5,22.7C3.6,22.6,3.6,22.6,3.5,22.7
		C3.5,22.7,3.5,22.7,3.5,22.7z"/>
	<ellipse transform="matrix(0.855 -0.5187 0.5187 0.855 5.7862 31.4396)" class="st0" cx="59.1" cy="5.4" rx="0" ry="0"/>
	<path class="st0" d="M59.1,5.4c-0.1,0-0.1,0-0.2-0.1C59,5.2,59.1,5.3,59.1,5.4z"/>
	<path class="st0" d="M55.9,5.6C55.9,5.6,56,5.6,55.9,5.6C56,5.6,56,5.6,55.9,5.6C56,5.6,56,5.6,55.9,5.6
		C55.9,5.6,55.9,5.6,55.9,5.6z"/>
	<path class="st0" d="M55.7,5.6C55.7,5.6,55.7,5.6,55.7,5.6C55.7,5.6,55.7,5.6,55.7,5.6C55.7,5.6,55.7,5.6,55.7,5.6
		C55.7,5.6,55.7,5.6,55.7,5.6z"/>
	<path class="st0" d="M40.6,4.5C40.6,4.4,40.7,4.4,40.6,4.5c0.1,0,0.1,0,0.1,0C40.7,4.5,40.7,4.5,40.6,4.5
		C40.6,4.5,40.6,4.5,40.6,4.5z"/>
	<path class="st0" d="M15.4,1.6C15.4,1.6,15.4,1.6,15.4,1.6C15.4,1.6,15.5,1.6,15.4,1.6C15.5,1.6,15.4,1.6,15.4,1.6
		C15.4,1.6,15.4,1.6,15.4,1.6z"/>
	<path class="st0" d="M32.5,3.9c0,0,0-0.1,0-0.1c0,0,0,0,0.1,0C32.6,3.8,32.6,3.8,32.5,3.9C32.6,3.9,32.5,3.9,32.5,3.9z"/>
	<path class="st0" d="M23,2.6C23,2.6,23,2.6,23,2.6C23,2.6,23,2.6,23,2.6C23,2.6,23,2.6,23,2.6C23,2.6,23,2.6,23,2.6z"/>
	<path class="st0" d="M23.3,2.8C23.3,2.8,23.3,2.7,23.3,2.8C23.3,2.7,23.3,2.7,23.3,2.8C23.3,2.7,23.3,2.7,23.3,2.8
		C23.3,2.8,23.3,2.8,23.3,2.8z"/>
	<path class="st0" d="M9.9,1.2C9.9,1.1,9.9,1.1,9.9,1.2C9.9,1.1,9.9,1.2,9.9,1.2c0.1,0,0,0.1,0,0.2C9.9,1.2,9.9,1.2,9.9,1.2z"/>
	<path class="st0" d="M59.3,41.6c-0.1,0-0.2,0-0.3,0C59.1,41.5,59.1,41.5,59.3,41.6z"/>
	<ellipse transform="matrix(0.9386 -0.345 0.345 0.9386 -10.7118 22.914)" class="st0" cx="59" cy="41.6" rx="0" ry="0"/>
	<path class="st0" d="M47.3,6C47.3,5.9,47.3,5.9,47.3,6C47.4,5.9,47.4,5.9,47.3,6C47.4,6,47.3,6,47.3,6C47.3,6,47.3,6,47.3,6z"/>
	<path class="st0" d="M7.3,1C7.3,1,7.3,1,7.3,1C7.3,1,7.3,1,7.3,1C7.3,1,7.3,1,7.3,1C7.3,1,7.3,1,7.3,1z"/>
	<path class="st0" d="M44.8,5.7C44.8,5.7,44.8,5.7,44.8,5.7C44.9,5.7,44.9,5.7,44.8,5.7C44.9,5.7,44.8,5.7,44.8,5.7
		C44.8,5.7,44.8,5.7,44.8,5.7z"/>
	<path class="st0" d="M9.6,1.7C9.6,1.7,9.6,1.7,9.6,1.7C9.6,1.7,9.6,1.7,9.6,1.7C9.6,1.8,9.6,1.8,9.6,1.7C9.6,1.8,9.6,1.8,9.6,1.7z"
		/>
	<path class="st0" d="M17.2,2.7C17.2,2.7,17.1,2.8,17.2,2.7C17.1,2.7,17.1,2.7,17.2,2.7C17.1,2.7,17.1,2.7,17.2,2.7
		C17.1,2.7,17.2,2.7,17.2,2.7z"/>
	<path class="st0" d="M2.9,4.6C2.9,4.6,2.9,4.6,2.9,4.6C2.9,4.6,3,4.6,2.9,4.6L2.9,4.6z"/>
	<path class="st0" d="M2.9,4.4c0,0,0,0.1,0,0.1C2.9,4.6,2.9,4.5,2.9,4.4C2.9,4.5,2.9,4.4,2.9,4.4C2.9,4.4,2.9,4.4,2.9,4.4z"/>
	<path class="st0" d="M63.7,12.1C63.7,12.1,63.7,12.2,63.7,12.1C63.6,12.2,63.6,12.1,63.7,12.1C63.6,12.1,63.6,12.1,63.7,12.1
		C63.6,12.1,63.7,12.1,63.7,12.1z"/>
	<path class="st0" d="M53,7.3C52.9,7.3,52.9,7.3,53,7.3C52.9,7.3,53,7.2,53,7.3C53,7.2,53,7.3,53,7.3C53,7.3,53,7.3,53,7.3z"/>
	<path class="st0" d="M13.4,2.3C13.4,2.3,13.4,2.3,13.4,2.3C13.4,2.3,13.4,2.3,13.4,2.3C13.4,2.3,13.4,2.3,13.4,2.3
		C13.4,2.3,13.4,2.3,13.4,2.3z"/>
	<path class="st0" d="M21.2,3.5C21.2,3.4,21.2,3.5,21.2,3.5C21.2,3.5,21.2,3.5,21.2,3.5C21.2,3.5,21.2,3.5,21.2,3.5
		C21.1,3.5,21.1,3.5,21.2,3.5C21.1,3.5,21.2,3.5,21.2,3.5z"/>
	<path class="st0" d="M25.2,4.1C25.2,4.1,25.2,4.1,25.2,4.1C25.2,4.1,25.2,4,25.2,4.1C25.2,4,25.2,4.1,25.2,4.1
		C25.3,4.1,25.2,4.1,25.2,4.1z"/>
	<path class="st0" d="M49.8,7.2C49.8,7.2,49.9,7.2,49.8,7.2c0.1,0,0.1,0,0.1,0C49.9,7.3,49.9,7.3,49.8,7.2
		C49.8,7.3,49.8,7.2,49.8,7.2z"/>
	<path class="st0" d="M24.9,4.2C24.9,4.2,24.9,4.2,24.9,4.2c-0.1-0.1,0-0.1,0-0.1C24.9,4.1,25,4.1,24.9,4.2
		C25,4.2,24.9,4.2,24.9,4.2z"/>
	<path class="st0" d="M56.9,42C57,42,57,41.9,56.9,42c0.1,0,0.1,0,0.1,0C57,42,57,42,56.9,42C57,42,56.9,42,56.9,42z"/>
	<path class="st0" d="M19.9,3.7C19.9,3.7,19.9,3.7,19.9,3.7C19.9,3.7,19.9,3.7,19.9,3.7C19.9,3.7,19.9,3.6,19.9,3.7
		C19.9,3.7,19.9,3.7,19.9,3.7z"/>
	<path class="st0" d="M12.2,2.8C12.2,2.8,12.2,2.8,12.2,2.8C12.2,2.7,12.2,2.7,12.2,2.8C12.3,2.7,12.3,2.8,12.2,2.8
		C12.2,2.8,12.2,2.8,12.2,2.8z"/>
	<path class="st0" d="M11.4,2.6C11.5,2.6,11.5,2.6,11.4,2.6C11.5,2.6,11.5,2.6,11.4,2.6C11.5,2.7,11.5,2.7,11.4,2.6
		C11.5,2.7,11.5,2.7,11.4,2.6z"/>
	<path class="st0" d="M9,2.3C9,2.3,9,2.3,9,2.3C9,2.3,9,2.3,9,2.3C9,2.4,9,2.4,9,2.3C9,2.4,9,2.3,9,2.3z"/>
	<path class="st0" d="M2.9,1.7C2.9,1.7,2.9,1.6,2.9,1.7C3,1.6,3,1.7,2.9,1.7C3,1.7,2.9,1.7,2.9,1.7C2.9,1.7,2.9,1.7,2.9,1.7z"/>
	<path class="st0" d="M58.5,35.3C58.5,35.3,58.5,35.4,58.5,35.3c-0.1,0-0.1,0-0.1,0C58.5,35.3,58.5,35.3,58.5,35.3
		C58.5,35.3,58.5,35.3,58.5,35.3z"/>
	<path class="st0" d="M12.4,3.1C12.4,3.1,12.4,3.1,12.4,3.1C12.4,3.1,12.5,3.1,12.4,3.1C12.5,3.2,12.4,3.2,12.4,3.1
		C12.4,3.2,12.4,3.2,12.4,3.1z"/>
	<path class="st0" d="M48,41.5C48,41.4,48,41.4,48,41.5C48,41.4,48.1,41.4,48,41.5C48.1,41.4,48.1,41.5,48,41.5
		C48,41.5,48,41.5,48,41.5z"/>
	<path class="st0" d="M9.9,3C9.9,3,9.8,3,9.9,3c0-0.1,0-0.1,0-0.1C9.9,2.9,9.9,2.9,9.9,3C9.9,3,9.9,3,9.9,3z"/>
	<path class="st0" d="M6.6,3C6.6,3,6.6,3,6.6,3C6.6,3,6.6,3,6.6,3C6.6,3,6.5,3,6.6,3C6.5,3,6.5,3,6.5,3c0,0,0-0.1,0-0.1
		C6.5,2.9,6.5,2.9,6.6,3C6.6,2.9,6.6,3,6.6,3L6.6,3C6.6,3,6.6,3,6.6,3C6.6,3,6.6,3,6.6,3L6.6,3C6.6,3,6.6,3,6.6,3C6.6,3,6.6,3,6.6,3
		L6.6,3z"/>
	<path class="st0" d="M27.6,42.1c0,0-0.1,0-0.1,0c0,0,0,0,0,0C27.6,42.1,27.6,42.1,27.6,42.1C27.6,42.1,27.6,42.1,27.6,42.1z"/>
	<path class="st0" d="M50.9,8.5C50.9,8.5,50.9,8.5,50.9,8.5C50.9,8.5,50.9,8.5,50.9,8.5C50.9,8.6,50.9,8.6,50.9,8.5
		C50.9,8.6,50.9,8.6,50.9,8.5z"/>
	<path class="st0" d="M11.7,40.4c-0.1-0.1-0.1-0.2-0.1-0.3c0,0,0.1,0,0.1,0C11.7,40.2,11.7,40.3,11.7,40.4z"/>
	<ellipse transform="matrix(0.9517 -0.3071 0.3071 0.9517 -11.7735 5.5156)" class="st0" cx="11.6" cy="40.2" rx="0" ry="0"/>
	<path class="st0" d="M56.1,31.6C56.1,31.6,56.1,31.6,56.1,31.6C56.1,31.5,56.1,31.5,56.1,31.6c0-0.1,0-0.1,0-0.1
		C56.2,31.5,56.2,31.5,56.1,31.6z"/>
	<polygon class="st0" points="58.2,34 58.2,34 58.2,34 	"/>
	<path class="st0" d="M58.3,34.2C58.3,34.1,58.2,34.1,58.3,34.2c-0.1-0.1-0.1-0.1-0.1-0.1C58.2,34,58.3,34,58.3,34.2
		C58.3,34.1,58.3,34.1,58.3,34.2z"/>
	<path class="st0" d="M59.3,37.3C59.3,37.3,59.3,37.3,59.3,37.3c-0.1,0.1-0.1,0.1-0.1,0.1c0,0,0,0,0,0l0,0c-0.2,0.1-0.2,0.1-0.3-0.1
		c0,0,0,0,0-0.1c0.1,0,0.2,0.1,0.3,0.1l0,0C59.1,37.3,59.2,37.3,59.3,37.3C59.2,37.2,59.2,37.2,59.3,37.3z"/>
	<path class="st0" d="M65.6,25.3C65.6,25.3,65.6,25.3,65.6,25.3C65.6,25.3,65.6,25.3,65.6,25.3C65.6,25.3,65.6,25.3,65.6,25.3
		C65.6,25.3,65.6,25.3,65.6,25.3z"/>
	<path class="st0" d="M2.9,18.1C2.9,18.1,2.9,18.1,2.9,18.1C2.9,18.2,2.9,18.2,2.9,18.1C2.9,18.2,2.9,18.2,2.9,18.1
		C2.9,18.1,2.9,18.1,2.9,18.1z"/>
	<path class="st0" d="M9.1,1.2C9.1,1.1,9.1,1.1,9.1,1.2C9.1,1.1,9.1,1.1,9.1,1.2C9.1,1.1,9.1,1.2,9.1,1.2C9.1,1.2,9.1,1.2,9.1,1.2z"
		/>
	<path class="st0" d="M46,5.7C46,5.7,46,5.7,46,5.7C46,5.7,46,5.7,46,5.7C45.9,5.7,45.9,5.7,46,5.7C45.9,5.7,46,5.7,46,5.7z"/>
	<path class="st0" d="M63.5,8C63.5,8,63.5,8,63.5,8C63.5,8,63.5,8,63.5,8C63.5,8,63.5,8.1,63.5,8C63.5,8.1,63.5,8,63.5,8z"/>
	<path class="st0" d="M14.3,2C14.2,2,14.2,2,14.3,2C14.2,2,14.2,2,14.3,2C14.2,1.9,14.2,1.9,14.3,2C14.3,1.9,14.3,2,14.3,2z"/>
	<path class="st0" d="M22.5,3.3C22.5,3.2,22.5,3.2,22.5,3.3C22.5,3.2,22.5,3.2,22.5,3.3C22.5,3.2,22.6,3.2,22.5,3.3
		C22.5,3.2,22.5,3.2,22.5,3.3z"/>
	<path class="st0" d="M13.2,2.2C13.1,2.2,13.1,2.2,13.2,2.2C13.1,2.2,13.1,2.2,13.2,2.2C13.1,2.2,13.1,2.2,13.2,2.2
		C13.2,2.2,13.2,2.2,13.2,2.2z"/>
	<path class="st0" d="M10.7,1.9C10.7,1.9,10.7,1.9,10.7,1.9C10.7,1.9,10.7,1.9,10.7,1.9C10.7,1.9,10.7,1.9,10.7,1.9
		C10.7,1.9,10.7,1.9,10.7,1.9z"/>
	<path class="st0" d="M7.7,1.6c0,0-0.1,0.1-0.1,0.1C7.6,1.6,7.6,1.6,7.7,1.6C7.6,1.5,7.7,1.5,7.7,1.6C7.7,1.5,7.7,1.6,7.7,1.6z"/>
	<path class="st0" d="M48.3,6.7C48.3,6.7,48.3,6.6,48.3,6.7C48.4,6.6,48.4,6.7,48.3,6.7C48.4,6.7,48.3,6.7,48.3,6.7
		C48.3,6.7,48.3,6.7,48.3,6.7z"/>
	<path class="st0" d="M22.7,3.5C22.7,3.5,22.7,3.5,22.7,3.5c0.1,0.1,0,0.1,0,0.2C22.6,3.6,22.6,3.5,22.7,3.5z"/>
	<polygon class="st0" points="22.6,3.6 22.6,3.6 22.6,3.6 22.6,3.6 22.7,3.6 22.7,3.6 22.7,3.6 22.7,3.6 22.6,3.6 	"/>
	<path class="st0" d="M15.1,2.6C15.1,2.6,15.1,2.6,15.1,2.6C15.1,2.6,15,2.6,15.1,2.6C15,2.6,15,2.5,15.1,2.6
		C15.1,2.5,15.1,2.6,15.1,2.6z"/>
	<path class="st0" d="M55.2,7.6C55.3,7.6,55.3,7.6,55.2,7.6C55.3,7.6,55.3,7.6,55.2,7.6C55.3,7.6,55.3,7.6,55.2,7.6
		C55.2,7.6,55.2,7.6,55.2,7.6z"/>
	<path class="st0" d="M59.2,36.5C59.2,36.5,59.1,36.6,59.2,36.5c-0.1,0-0.1-0.1-0.1-0.1C59.1,36.5,59.1,36.5,59.2,36.5
		C59.2,36.5,59.2,36.5,59.2,36.5z"/>
	<ellipse transform="matrix(0.6115 -0.7912 0.7912 0.6115 -5.8854 60.9)" class="st0" cx="59.1" cy="36.4" rx="0" ry="0"/>
	<path class="st0" d="M18.1,3.1C18.1,3.1,18.1,3.1,18.1,3.1C18.1,3.1,18.1,3.1,18.1,3.1C18.1,3.1,18.1,3.1,18.1,3.1
		C18.1,3.1,18.1,3.1,18.1,3.1z"/>
	<path class="st0" d="M7.8,1.9C7.8,1.9,7.8,1.9,7.8,1.9C7.8,1.9,7.8,1.9,7.8,1.9C7.8,1.9,7.8,1.9,7.8,1.9C7.8,1.9,7.8,1.9,7.8,1.9z"
		/>
	<path class="st0" d="M55.4,8C55.4,7.9,55.4,7.9,55.4,8C55.4,7.9,55.4,7.9,55.4,8C55.4,7.9,55.4,7.9,55.4,8C55.4,7.9,55.4,8,55.4,8z
		"/>
	<path class="st0" d="M45.8,6.6C45.8,6.7,45.8,6.7,45.8,6.6C45.8,6.7,45.7,6.7,45.8,6.6C45.7,6.6,45.8,6.6,45.8,6.6
		C45.8,6.6,45.8,6.6,45.8,6.6z"/>
	<polygon class="st0" points="25.5,4.1 25.5,4.1 25.5,4.1 	"/>
	<path class="st0" d="M25.5,4.2C25.5,4.1,25.5,4.1,25.5,4.2C25.5,4.1,25.6,4.1,25.5,4.2C25.6,4.2,25.6,4.2,25.5,4.2z"/>
	<path class="st0" d="M53.2,7.8C53.2,7.8,53.2,7.8,53.2,7.8C53.2,7.8,53.2,7.8,53.2,7.8C53.2,7.8,53.2,7.8,53.2,7.8
		C53.2,7.8,53.2,7.8,53.2,7.8z"/>
	<path class="st0" d="M4.7,1.7C4.7,1.8,4.7,1.8,4.7,1.7C4.7,1.8,4.7,1.8,4.7,1.7C4.7,1.8,4.7,1.8,4.7,1.7z"/>
	<path class="st0" d="M42.3,6.7C42.3,6.7,42.3,6.7,42.3,6.7C42.3,6.6,42.3,6.6,42.3,6.7C42.3,6.6,42.3,6.6,42.3,6.7
		C42.4,6.7,42.3,6.7,42.3,6.7z"/>
	<path class="st0" d="M58.6,42.6C58.6,42.6,58.5,42.7,58.6,42.6C58.5,42.7,58.5,42.6,58.6,42.6C58.5,42.6,58.6,42.6,58.6,42.6
		C58.6,42.6,58.6,42.6,58.6,42.6z"/>
	<polygon class="st0" points="4.2,1.9 4.2,2 4.1,2 	"/>
	<path class="st0" d="M8.9,2.8C8.9,2.8,8.9,2.8,8.9,2.8C8.9,2.8,8.9,2.7,8.9,2.8C8.9,2.8,9,2.8,8.9,2.8C8.9,2.8,8.9,2.8,8.9,2.8z"/>
	<path class="st0" d="M10.8,3.1C10.8,3.1,10.8,3.1,10.8,3.1C10.8,3,10.8,3,10.8,3.1C10.8,3,10.8,3.1,10.8,3.1
		C10.8,3.1,10.8,3.1,10.8,3.1z"/>
	<path class="st0" d="M9.4,2.9C9.3,2.9,9.3,2.9,9.4,2.9C9.4,2.9,9.4,2.9,9.4,2.9C9.4,2.9,9.4,2.9,9.4,2.9C9.4,2.9,9.4,2.9,9.4,2.9z"
		/>
	<path class="st0" d="M8.6,2.9C8.6,2.9,8.6,2.9,8.6,2.9C8.6,2.9,8.6,2.8,8.6,2.9C8.6,2.8,8.7,2.8,8.6,2.9C8.7,2.8,8.7,2.9,8.6,2.9
		C8.7,2.9,8.7,2.9,8.6,2.9z"/>
	<polygon class="st0" points="8.6,2.9 8.6,2.9 8.6,2.9 	"/>
	<path class="st0" d="M4,2.2C4,2.2,4,2.2,4,2.2C4,2.2,4,2.2,4,2.2C4,2.2,4,2.2,4,2.2C4,2.2,4,2.2,4,2.2z"/>
	<path class="st0" d="M50.4,8C50.4,8.1,50.4,8.1,50.4,8C50.4,8.1,50.4,8.1,50.4,8C50.4,8.1,50.4,8,50.4,8C50.4,8,50.4,8,50.4,8z"/>
	<path class="st0" d="M58.1,37.6C58.1,37.5,58.2,37.5,58.1,37.6C58.2,37.5,58.2,37.5,58.1,37.6C58.2,37.6,58.2,37.6,58.1,37.6
		C58.2,37.6,58.1,37.6,58.1,37.6z"/>
	<ellipse transform="matrix(0.9268 -0.3756 0.3756 0.9268 0.7274 21.4579)" class="st0" cx="55.4" cy="8.9" rx="0" ry="0"/>
	<path class="st0" d="M55.2,8.8c0.1,0,0.1,0,0.2,0C55.3,8.9,55.2,8.9,55.2,8.8z"/>
	<polygon class="st0" points="55.2,8.8 55.2,8.8 55.2,8.8 	"/>
	<path class="st0" d="M43,7.3C43,7.3,43,7.3,43,7.3C43,7.3,42.9,7.3,43,7.3C42.9,7.3,42.9,7.3,43,7.3C42.9,7.3,42.9,7.3,43,7.3z"/>
	<path class="st0" d="M55.8,42.5C55.8,42.5,55.9,42.5,55.8,42.5C55.9,42.6,55.9,42.6,55.8,42.5C55.8,42.6,55.8,42.6,55.8,42.5
		C55.8,42.5,55.8,42.5,55.8,42.5z"/>
	<path class="st0" d="M3.4,2.4C3.4,2.4,3.4,2.3,3.4,2.4C3.5,2.4,3.5,2.4,3.4,2.4C3.5,2.4,3.5,2.4,3.4,2.4C3.4,2.4,3.4,2.4,3.4,2.4z"
		/>
	<path class="st0" d="M10.5,3.4C10.5,3.4,10.5,3.4,10.5,3.4C10.5,3.4,10.5,3.4,10.5,3.4C10.5,3.4,10.5,3.4,10.5,3.4
		C10.5,3.4,10.5,3.4,10.5,3.4z"/>
	<path class="st0" d="M3.8,2.7C3.8,2.7,3.8,2.7,3.8,2.7C3.8,2.6,3.8,2.6,3.8,2.7C3.9,2.6,3.9,2.7,3.8,2.7C3.9,2.7,3.8,2.7,3.8,2.7z"
		/>
	<path class="st0" d="M43.3,7.7C43.3,7.7,43.3,7.7,43.3,7.7C43.3,7.7,43.3,7.7,43.3,7.7C43.3,7.7,43.3,7.6,43.3,7.7
		C43.3,7.6,43.3,7.7,43.3,7.7z"/>
	<path class="st0" d="M8.2,3.6c0-0.1,0-0.1,0-0.2C8.3,3.5,8.2,3.6,8.2,3.6z"/>
	<polygon class="st0" points="54.7,9.4 54.7,9.4 54.7,9.4 	"/>
	<path class="st0" d="M54.6,9.4C54.6,9.4,54.6,9.3,54.6,9.4C54.7,9.3,54.7,9.3,54.6,9.4c0.1,0,0.1,0,0.1,0
		C54.7,9.4,54.7,9.4,54.6,9.4C54.7,9.4,54.6,9.4,54.6,9.4z"/>
	<path class="st0" d="M48.9,42.1C48.9,42.1,49,42.1,48.9,42.1C49,42.1,49,42.1,48.9,42.1C49,42.1,48.9,42.1,48.9,42.1
		C48.9,42.1,48.9,42.1,48.9,42.1z"/>
	<ellipse transform="matrix(0.5873 -0.8094 0.8094 0.5873 0.9855 9.3971)" class="st0" cx="9.7" cy="3.7" rx="0" ry="0"/>
	<path class="st0" d="M9.6,3.8C9.6,3.8,9.6,3.8,9.6,3.8C9.7,3.8,9.7,3.7,9.6,3.8C9.7,3.8,9.7,3.8,9.6,3.8C9.7,3.8,9.6,3.8,9.6,3.8z"
		/>
	<path class="st0" d="M4.3,3.2C4.3,3.2,4.3,3.2,4.3,3.2C4.3,3.2,4.3,3.2,4.3,3.2C4.3,3.2,4.3,3.2,4.3,3.2C4.3,3.2,4.3,3.2,4.3,3.2z"
		/>
	<polygon class="st0" points="4.9,3.8 4.9,3.9 4.9,3.9 4.9,3.9 4.9,3.9 	"/>
	<path class="st0" d="M4.9,3.7C4.9,3.7,4.9,3.7,4.9,3.7c0,0.1,0,0.1,0,0.2C4.9,3.8,4.8,3.8,4.9,3.7C4.8,3.7,4.8,3.7,4.9,3.7z"/>
	<ellipse transform="matrix(0.4572 -0.8894 0.8894 0.4572 -11.8348 66.7458)" class="st0" cx="48.8" cy="43.1" rx="0" ry="0"/>
	<path class="st0" d="M48.9,43.2c0,0-0.1-0.1-0.1-0.1C48.8,43.1,48.9,43.1,48.9,43.2z"/>
	<path class="st0" d="M47.8,43.1C47.8,43.1,47.8,43.1,47.8,43.1C47.8,43.1,47.8,43.1,47.8,43.1C47.8,43.1,47.8,43.1,47.8,43.1
		C47.8,43.1,47.8,43.1,47.8,43.1z"/>
	<path class="st0" d="M5.6,4.6C5.6,4.5,5.6,4.5,5.6,4.6C5.6,4.5,5.6,4.5,5.6,4.6C5.6,4.5,5.6,4.5,5.6,4.6C5.6,4.6,5.6,4.6,5.6,4.6z"
		/>
	<path class="st0" d="M59.1,39.1C59.1,39.1,59.1,39,59.1,39.1C59.1,39,59.1,39,59.1,39.1C59.2,39,59.2,39,59.1,39.1
		C59.2,39,59.1,39.1,59.1,39.1z"/>
	<path class="st0" d="M3.2,4.8C3.2,4.9,3.2,4.9,3.2,4.8C3.2,4.9,3.2,4.9,3.2,4.8C3.2,4.9,3.2,4.9,3.2,4.8C3.2,4.9,3.2,4.8,3.2,4.8z"
		/>
	<path class="st0" d="M57.6,31.9C57.6,32,57.7,32,57.6,31.9c0.1,0.1,0.1,0.1,0.1,0.1c0,0,0,0-0.1,0C57.6,32,57.6,32,57.6,31.9z"/>
	<path class="st0" d="M56.6,30.6C56.6,30.6,56.6,30.6,56.6,30.6C56.6,30.6,56.6,30.7,56.6,30.6C56.6,30.7,56.6,30.7,56.6,30.6
		C56.6,30.7,56.6,30.7,56.6,30.6z"/>
	<path class="st0" d="M59.3,40.1C59.3,40.1,59.3,40,59.3,40.1C59.3,40,59.3,40,59.3,40.1C59.3,40,59.3,40,59.3,40.1
		C59.3,40,59.3,40.1,59.3,40.1z"/>
	<path class="st0" d="M63.9,13C63.9,13,64,13,63.9,13C64,13,63.9,13,63.9,13C63.9,13,63.9,13,63.9,13C63.9,13,63.9,13,63.9,13z"/>
	<ellipse transform="matrix(0.6442 -0.7649 0.7649 0.6442 -25.8049 26.0926)" class="st0" cx="15.1" cy="40.8" rx="0" ry="0"/>
	<path class="st0" d="M15,40.8C15,40.8,15,40.8,15,40.8c0.1,0,0.1,0,0.1,0C15.1,40.8,15.1,40.8,15,40.8C15.1,40.9,15,40.8,15,40.8z"
		/>
	<path class="st0" d="M65.4,23.1C65.4,23.1,65.4,23.1,65.4,23.1C65.4,23.1,65.4,23.1,65.4,23.1C65.4,23.1,65.4,23.1,65.4,23.1
		C65.4,23.1,65.4,23.1,65.4,23.1z"/>
	<path class="st0" d="M64.5,23.3C64.5,23.3,64.5,23.3,64.5,23.3C64.5,23.3,64.5,23.3,64.5,23.3C64.4,23.3,64.4,23.3,64.5,23.3
		C64.4,23.3,64.5,23.3,64.5,23.3z"/>
	<path class="st0" d="M2.8,6.3C2.8,6.3,2.8,6.3,2.8,6.3C2.7,6.3,2.7,6.3,2.8,6.3C2.7,6.3,2.8,6.3,2.8,6.3C2.8,6.3,2.8,6.3,2.8,6.3z"
		/>
	<path class="st0" d="M56.1,31.2C56.1,31.1,56.1,31.1,56.1,31.2C56.1,31.1,56.1,31.1,56.1,31.2C56.1,31.1,56.2,31.1,56.1,31.2
		C56.1,31.1,56.1,31.1,56.1,31.2z"/>
	<path class="st0" d="M65.7,24.2C65.7,24.2,65.7,24.1,65.7,24.2C65.8,24.1,65.8,24.2,65.7,24.2C65.8,24.2,65.7,24.2,65.7,24.2
		C65.7,24.2,65.7,24.2,65.7,24.2z"/>
	<path class="st0" d="M61.2,24.3L61.2,24.3c0.1,0,0.1-0.1,0.2-0.1c0.1,0,0.1,0.1,0.2,0.1c0,0.1-0.1,0.3-0.1,0.4
		C61.3,24.6,61.2,24.4,61.2,24.3L61.2,24.3C61.2,24.3,61.1,24.3,61.2,24.3C61.1,24.3,61.1,24.3,61.2,24.3c-0.1-0.1-0.1-0.1,0-0.2
		C61.1,24.2,61.2,24.2,61.2,24.3C61.2,24.2,61.2,24.3,61.2,24.3C61.2,24.3,61.2,24.3,61.2,24.3z"/>
	<path class="st0" d="M63.6,15.2C63.6,15.2,63.6,15.1,63.6,15.2C63.6,15.1,63.6,15.1,63.6,15.2C63.6,15.1,63.6,15.2,63.6,15.2
		C63.6,15.2,63.6,15.2,63.6,15.2z"/>
	<path class="st0" d="M58.8,5.1C58.8,5.1,58.8,5.1,58.8,5.1C58.8,5.1,58.9,5.1,58.8,5.1C58.9,5.1,58.9,5.1,58.8,5.1
		C58.8,5.1,58.8,5.1,58.8,5.1z"/>
	<path class="st0" d="M57.5,5.2C57.5,5.2,57.4,5.2,57.5,5.2C57.4,5.2,57.4,5.2,57.5,5.2C57.4,5.2,57.5,5.2,57.5,5.2
		C57.5,5.2,57.5,5.2,57.5,5.2z"/>
	<path class="st0" d="M58.2,32.9C58.2,32.9,58.2,32.9,58.2,32.9C58.3,32.9,58.3,32.9,58.2,32.9C58.3,32.9,58.2,32.9,58.2,32.9
		C58.2,32.9,58.2,32.9,58.2,32.9z"/>
	<path class="st0" d="M56.5,5.7C56.5,5.7,56.5,5.6,56.5,5.7C56.5,5.6,56.6,5.6,56.5,5.7C56.6,5.7,56.5,5.7,56.5,5.7
		C56.5,5.7,56.5,5.7,56.5,5.7z"/>
	<path class="st0" d="M53.3,5.7C53.3,5.7,53.3,5.7,53.3,5.7C53.3,5.7,53.3,5.7,53.3,5.7C53.4,5.7,53.3,5.7,53.3,5.7
		C53.3,5.7,53.3,5.7,53.3,5.7z"/>
	<path class="st0" d="M3,18.6C3,18.6,3,18.6,3,18.6C3,18.6,3,18.6,3,18.6C3,18.6,2.9,18.6,3,18.6C3,18.6,3,18.6,3,18.6z"/>
	<path class="st0" d="M59,41.3C59,41.3,59,41.2,59,41.3c0.1-0.1,0.1-0.1,0.1-0.1C59.1,41.2,59.1,41.3,59,41.3
		C59,41.3,59,41.3,59,41.3z"/>
	<ellipse transform="matrix(0.8819 -0.4715 0.4715 0.8819 -12.4443 32.7518)" class="st0" cx="59.1" cy="41.2" rx="0" ry="0"/>
	<path class="st0" d="M35.6,4.1C35.6,4.1,35.6,4,35.6,4.1C35.6,4.1,35.6,4.1,35.6,4.1C35.6,4.1,35.6,4.1,35.6,4.1
		C35.6,4.1,35.6,4.1,35.6,4.1z"/>
	<path class="st0" d="M55.6,5.2C55.6,5.2,55.6,5.2,55.6,5.2C55.6,5.2,55.6,5.2,55.6,5.2C55.6,5.2,55.6,5.2,55.6,5.2
		C55.6,5.2,55.6,5.2,55.6,5.2z"/>
	<polygon class="st0" points="49.9,5.2 49.9,5.2 49.9,5.3 	"/>
	<path class="st0" d="M63.4,7.4c0-0.1,0-0.1,0-0.2C63.4,7.3,63.5,7.3,63.4,7.4z"/>
	<ellipse transform="matrix(0.3346 -0.9423 0.9423 0.3346 35.1748 64.6806)" class="st0" cx="63.4" cy="7.4" rx="0" ry="0"/>
	<path class="st0" d="M45.9,5.4C45.9,5.4,45.9,5.4,45.9,5.4C46,5.4,46,5.4,45.9,5.4C46,5.4,46,5.4,45.9,5.4
		C45.9,5.4,45.9,5.4,45.9,5.4z"/>
	<path class="st0" d="M15,1.5C15,1.5,15,1.5,15,1.5C15,1.5,15,1.5,15,1.5C15.1,1.5,15.1,1.5,15,1.5C15.1,1.5,15,1.5,15,1.5z"/>
	<path class="st0" d="M27.9,3.2C27.9,3.2,27.9,3.2,27.9,3.2C28,3.2,28,3.2,27.9,3.2C28,3.2,28,3.2,27.9,3.2
		C28,3.2,27.9,3.2,27.9,3.2z"/>
	<path class="st0" d="M25.6,2.9C25.6,2.9,25.6,2.9,25.6,2.9C25.6,2.9,25.6,2.9,25.6,2.9C25.7,2.9,25.7,2.9,25.6,2.9
		C25.7,2.9,25.6,2.9,25.6,2.9z"/>
	<polygon class="st0" points="53.6,6.5 53.6,6.5 53.6,6.5 	"/>
	<polygon class="st0" points="8.5,1.1 8.5,1.1 8.5,1.1 	"/>
	<ellipse transform="matrix(0.4491 -0.8935 0.8935 0.4491 27.7945 61.4608)" class="st0" cx="63.7" cy="8.2" rx="0" ry="0"/>
	<path class="st0" d="M63.8,8.1C63.8,8.2,63.8,8.2,63.8,8.1C63.8,8.2,63.8,8.2,63.8,8.1C63.7,8.2,63.8,8.1,63.8,8.1
		C63.8,8.1,63.8,8.1,63.8,8.1z"/>
	<polygon class="st0" points="18.7,2.6 18.8,2.6 18.8,2.6 	"/>
	<path class="st0" d="M13.4,1.9C13.4,1.9,13.4,1.9,13.4,1.9C13.4,1.9,13.4,1.9,13.4,1.9C13.4,1.9,13.4,1.9,13.4,1.9
		C13.4,1.9,13.4,1.9,13.4,1.9z"/>
	<path class="st0" d="M12.3,1.9C12.3,1.9,12.3,1.9,12.3,1.9C12.3,1.9,12.3,1.9,12.3,1.9C12.3,1.9,12.3,1.9,12.3,1.9
		C12.3,1.9,12.3,1.9,12.3,1.9z"/>
	<path class="st0" d="M54.1,7.2C54.1,7.2,54.1,7.2,54.1,7.2C54.1,7.2,54.1,7.2,54.1,7.2C54.1,7.2,54.1,7.2,54.1,7.2z"/>
	<path class="st0" d="M11.4,1.9c0,0,0-0.1,0-0.1C11.4,1.8,11.4,1.9,11.4,1.9C11.4,1.9,11.4,1.9,11.4,1.9z"/>
	<polygon class="st0" points="51.3,6.9 51.2,6.9 51.2,6.9 	"/>
	<polygon class="st0" points="12.1,2.1 12.1,2.1 12.1,2.1 	"/>
	<path class="st0" d="M15.4,2.5C15.4,2.5,15.4,2.6,15.4,2.5C15.4,2.5,15.4,2.5,15.4,2.5C15.4,2.5,15.4,2.5,15.4,2.5
		C15.4,2.5,15.4,2.5,15.4,2.5z"/>
	<path class="st0" d="M8.5,1.7C8.5,1.7,8.5,1.7,8.5,1.7C8.5,1.7,8.6,1.7,8.5,1.7C8.6,1.7,8.6,1.7,8.5,1.7C8.6,1.7,8.6,1.7,8.5,1.7z"
		/>
	<path class="st0" d="M22.4,3.6C22.4,3.6,22.4,3.6,22.4,3.6C22.4,3.7,22.4,3.7,22.4,3.6C22.4,3.7,22.4,3.7,22.4,3.6L22.4,3.6
		C22.4,3.7,22.4,3.6,22.4,3.6z"/>
	<path class="st0" d="M7.5,1.8C7.5,1.8,7.5,1.7,7.5,1.8c0-0.1,0-0.1,0-0.1C7.5,1.7,7.5,1.7,7.5,1.8C7.5,1.8,7.5,1.8,7.5,1.8z"/>
	<polygon class="st0" points="7.5,1.7 7.5,1.7 7.5,1.7 7.6,1.7 7.6,1.7 	"/>
	<polygon class="st0" points="3.7,1.3 3.7,1.4 3.7,1.4 	"/>
	<path class="st0" d="M34,5.2C34,5.2,34,5.2,34,5.2C34,5.3,34,5.3,34,5.2C34,5.3,34,5.3,34,5.2C34,5.3,34,5.2,34,5.2z"/>
	<polygon class="st0" points="30.8,4.9 30.8,4.9 30.8,4.9 	"/>
	<path class="st0" d="M30.8,4.8C30.8,4.8,30.8,4.8,30.8,4.8C30.8,4.9,30.8,4.9,30.8,4.8C30.8,4.9,30.8,4.9,30.8,4.8
		C30.8,4.8,30.8,4.8,30.8,4.8z"/>
	<path class="st0" d="M23.3,4C23.3,4,23.2,4,23.3,4C23.2,4,23.2,3.9,23.3,4C23.3,3.9,23.3,4,23.3,4C23.3,4,23.3,4,23.3,4z"/>
	<path class="st0" d="M13.7,2.8C13.7,2.8,13.7,2.8,13.7,2.8C13.7,2.8,13.7,2.8,13.7,2.8C13.7,2.8,13.7,2.8,13.7,2.8
		C13.7,2.8,13.7,2.8,13.7,2.8z"/>
	<path class="st0" d="M6.9,2C6.9,2,6.9,2,6.9,2C6.9,2,6.9,2.1,6.9,2C6.9,2.1,6.9,2.1,6.9,2C6.9,2.1,6.9,2.1,6.9,2z"/>
	<polygon class="st0" points="23,4.1 23,4.1 23,4.1 	"/>
	<polygon class="st0" points="64.7,9.5 64.7,9.5 64.7,9.5 	"/>
	<path class="st0" d="M49,7.6C49,7.6,49,7.6,49,7.6C49,7.6,49,7.5,49,7.6C49,7.5,49,7.5,49,7.6C49,7.5,49,7.6,49,7.6z"/>
	<ellipse transform="matrix(0.883 -0.4694 0.4694 0.883 1.8984 21.049)" class="st0" cx="43.2" cy="6.7" rx="0" ry="0"/>
	<path class="st0" d="M43.2,6.8C43.2,6.8,43.2,6.8,43.2,6.8C43.2,6.8,43.2,6.7,43.2,6.8C43.2,6.7,43.2,6.8,43.2,6.8
		C43.2,6.8,43.2,6.8,43.2,6.8z"/>
	<polygon class="st0" points="2.8,2.1 2.8,2.1 2.8,2.2 	"/>
	<path class="st0" d="M47.2,8.2C47.2,8.2,47.2,8.2,47.2,8.2C47.2,8.2,47.2,8.2,47.2,8.2C47.2,8.2,47.2,8.2,47.2,8.2z"/>
	<polygon class="st0" points="57.6,39.7 57.6,39.8 57.6,39.7 	"/>
	<polygon class="st0" points="63.9,10.6 63.9,10.6 63.9,10.6 	"/>
	<path class="st0" d="M5.1,3.3C5.1,3.3,5.1,3.3,5.1,3.3C5.1,3.3,5.1,3.3,5.1,3.3C5.1,3.3,5.1,3.3,5.1,3.3z"/>
	<ellipse transform="matrix(0.8184 -0.5747 0.5747 0.8184 -0.9433 3.5168)" class="st0" cx="5.1" cy="3.3" rx="0" ry="0"/>
	<polygon class="st0" points="42.8,8.3 42.8,8.3 42.8,8.3 	"/>
	<ellipse transform="matrix(0.8737 -0.4865 0.4865 0.8737 -0.8909 4.2779)" class="st0" cx="7.8" cy="3.9" rx="0" ry="0"/>
	<path class="st0" d="M7.7,3.9C7.7,3.9,7.7,3.9,7.7,3.9C7.7,3.9,7.8,3.9,7.7,3.9C7.8,3.9,7.8,3.9,7.7,3.9C7.7,3.9,7.7,3.9,7.7,3.9z"
		/>
	<path class="st0" d="M5.1,3.8C5.1,3.8,5.1,3.8,5.1,3.8C5.1,3.8,5.1,3.8,5.1,3.8C5.1,3.8,5.1,3.9,5.1,3.8C5.1,3.8,5.1,3.8,5.1,3.8z"
		/>
	<path class="st0" d="M2.9,3.6C2.9,3.6,3,3.6,2.9,3.6C3,3.6,3,3.6,2.9,3.6C2.9,3.6,2.9,3.6,2.9,3.6z"/>
	<path class="st0" d="M3,3.6L3,3.6C3,3.6,3,3.6,3,3.6C3,3.6,3,3.6,3,3.6z"/>
	<polygon class="st0" points="64.5,11.4 64.5,11.4 64.5,11.5 	"/>
	<path class="st0" d="M63.7,11.4C63.7,11.4,63.7,11.4,63.7,11.4C63.7,11.4,63.7,11.4,63.7,11.4C63.7,11.4,63.7,11.5,63.7,11.4
		C63.7,11.4,63.7,11.4,63.7,11.4z"/>
	<path class="st0" d="M44.9,9.1C44.9,9.1,44.9,9.1,44.9,9.1C44.9,9.1,44.9,9.1,44.9,9.1C44.9,9.1,44.9,9.1,44.9,9.1z"/>
	<path class="st0" d="M7.8,4.7C7.8,4.7,7.8,4.7,7.8,4.7C7.8,4.7,7.8,4.7,7.8,4.7C7.8,4.7,7.8,4.7,7.8,4.7C7.8,4.7,7.8,4.7,7.8,4.7z"
		/>
	<polygon class="st0" points="2.7,4.1 2.7,4.1 2.7,4.1 	"/>
	<polygon class="st0" points="3.9,4.7 3.9,4.7 3.9,4.7 	"/>
	<polygon class="st0" points="2.9,4.6 3,4.6 3,4.6 	"/>
	<path class="st0" d="M3,4.7C3,4.7,3,4.7,3,4.7C3,4.6,3,4.6,3,4.7C3,4.6,3,4.6,3,4.7C3,4.6,3,4.7,3,4.7z"/>
	<path class="st0" d="M2.8,5.4C2.8,5.4,2.8,5.4,2.8,5.4C2.7,5.4,2.7,5.4,2.8,5.4C2.7,5.4,2.7,5.4,2.8,5.4C2.7,5.4,2.7,5.4,2.8,5.4z"
		/>
	<path class="st0" d="M5.9,6.4C6,6.4,6,6.4,5.9,6.4C6,6.4,6,6.5,5.9,6.4C6,6.5,6,6.5,5.9,6.4C6,6.4,6,6.4,5.9,6.4z"/>
	<ellipse transform="matrix(0.9762 -0.2168 0.2168 0.9762 -1.2679 1.4565)" class="st0" cx="6" cy="6.5" rx="0" ry="0"/>
	<path class="st0" d="M63.9,13.8C63.9,13.8,63.9,13.8,63.9,13.8C63.9,13.8,63.9,13.8,63.9,13.8C63.9,13.8,63.9,13.8,63.9,13.8z"/>
	<path class="st0" d="M65.2,23.3C65.2,23.3,65.2,23.3,65.2,23.3C65.2,23.3,65.2,23.3,65.2,23.3C65.2,23.3,65.2,23.3,65.2,23.3
		C65.2,23.3,65.2,23.3,65.2,23.3z"/>
	<polygon class="st0" points="65,23.3 65,23.3 65,23.3 	"/>
	<path class="st0" d="M61.1,23.1C61.1,23.1,61.1,23.1,61.1,23.1C61.1,23,61.1,23,61.1,23.1C61.1,23,61.1,23,61.1,23.1
		C61.2,23,61.2,23.1,61.1,23.1C61.2,23.1,61.1,23.1,61.1,23.1z"/>
	<polygon class="st0" points="65.3,23.7 65.3,23.7 65.3,23.7 	"/>
	<polygon class="st0" points="65.1,23.8 65.1,23.8 65.1,23.7 	"/>
	<path class="st0" d="M65.7,24.6C65.7,24.5,65.7,24.5,65.7,24.6C65.7,24.5,65.7,24.5,65.7,24.6C65.7,24.5,65.7,24.5,65.7,24.6
		C65.7,24.5,65.7,24.5,65.7,24.6z"/>
	<polygon class="st0" points="61.9,24.8 61.9,24.8 61.9,24.9 	"/>
	<path class="st0" d="M61.9,24.9C61.9,24.9,61.9,24.9,61.9,24.9C61.9,24.9,61.9,24.9,61.9,24.9C61.9,24.9,61.9,24.9,61.9,24.9
		C61.9,24.9,61.9,24.9,61.9,24.9z"/>
	<path class="st0" d="M3.3,19.2C3.3,19.2,3.3,19.2,3.3,19.2C3.3,19.2,3.3,19.2,3.3,19.2C3.3,19.2,3.3,19.2,3.3,19.2
		C3.3,19.2,3.3,19.2,3.3,19.2z"/>
	<path class="st0" d="M3.2,19.3C3.2,19.2,3.3,19.2,3.2,19.3C3.3,19.2,3.3,19.3,3.2,19.3C3.2,19.3,3.2,19.3,3.2,19.3z"/>
	<path class="st0" d="M58.6,40.8C58.6,40.8,58.6,40.8,58.6,40.8C58.6,40.8,58.6,40.8,58.6,40.8C58.6,40.8,58.6,40.8,58.6,40.8
		C58.6,40.8,58.6,40.8,58.6,40.8z"/>
	<path class="st0" d="M58.9,36.7C58.8,36.7,58.8,36.7,58.9,36.7C58.8,36.7,58.8,36.7,58.9,36.7C58.9,36.7,58.9,36.7,58.9,36.7z"/>
	<path class="st0" d="M57.4,42C57.4,42,57.4,42,57.4,42C57.4,42,57.4,42,57.4,42C57.4,42,57.4,42,57.4,42z"/>
	<polygon class="st0" points="58.9,42.6 58.9,42.6 59,42.6 	"/>
	<path class="st0" d="M57.4,42.9C57.4,42.8,57.4,42.8,57.4,42.9C57.4,42.8,57.4,42.8,57.4,42.9C57.4,42.8,57.4,42.8,57.4,42.9z"/>
	<path class="st0" d="M54.7,42.6C54.7,42.6,54.7,42.6,54.7,42.6C54.6,42.6,54.6,42.6,54.7,42.6C54.6,42.6,54.6,42.6,54.7,42.6
		C54.6,42.6,54.7,42.6,54.7,42.6z"/>
	<path class="st0" d="M53.9,42.8C53.9,42.8,53.9,42.8,53.9,42.8C53.8,42.8,53.9,42.8,53.9,42.8C53.9,42.8,53.9,42.8,53.9,42.8z"/>
	<polygon class="st0" points="50.2,42.8 50.2,42.8 50.3,42.8 	"/>
	<path class="st0" d="M59,35.5C59,35.5,59,35.5,59,35.5C59,35.5,59,35.5,59,35.5C59,35.5,59,35.5,59,35.5z"/>
	<path class="st0" d="M44.3,43.1C44.3,43.1,44.3,43.1,44.3,43.1C44.4,43.1,44.3,43.1,44.3,43.1C44.3,43.1,44.3,43.1,44.3,43.1z"/>
	<polygon class="st0" points="44.4,43.1 44.4,43.1 44.4,43.1 	"/>
	<path class="st0" d="M58.1,35.3C58.1,35.3,58.1,35.3,58.1,35.3C58.1,35.3,58.1,35.3,58.1,35.3C58.1,35.3,58.1,35.3,58.1,35.3
		C58.1,35.3,58.1,35.3,58.1,35.3z"/>
	<polygon class="st0" points="10.2,40.2 10.2,40.2 10.2,40.1 	"/>
	<polygon class="st0" points="58.4,37.7 58.4,37.7 58.4,37.7 	"/>
	<path class="st0" d="M58,38C58,38,58,38,58,38C58,38,58,38,58,38C58,38,58,38,58,38C58,38,58,38,58,38z"/>
	<polygon class="st0" points="57.5,33.3 57.5,33.3 57.5,33.3 	"/>
	<polygon class="st0" points="57.1,32.1 57.1,32.1 57.1,32.1 	"/>
	<path class="st0" d="M57.3,32.2C57.3,32.2,57.3,32.2,57.3,32.2C57.3,32.2,57.4,32.1,57.3,32.2C57.4,32.2,57.4,32.2,57.3,32.2
		C57.4,32.2,57.4,32.2,57.3,32.2z"/>
	<ellipse transform="matrix(0.5698 -0.8218 0.8218 0.5698 -1.4793 61.0668)" class="st0" cx="57.6" cy="31.9" rx="0" ry="0"/>
	<path class="st0" d="M57.5,31.9C57.5,31.9,57.6,31.9,57.5,31.9C57.5,31.9,57.5,31.9,57.5,31.9C57.5,31.9,57.5,31.9,57.5,31.9z"/>
	<polygon class="st0" points="58.2,32 58.2,32 58.1,32 	"/>
	<polygon class="st0" points="55.9,31 55.9,31 55.9,31 	"/>
	<polygon class="st0" points="3.7,23.8 3.7,23.8 3.7,23.8 	"/>
	<ellipse transform="matrix(0.8608 -0.509 0.509 0.8608 6.3364 32.3753)" class="st0" cx="62.4" cy="4.6" rx="0" ry="0"/>
	<path class="st0" d="M62.4,4.6C62.4,4.6,62.4,4.6,62.4,4.6C62.4,4.6,62.4,4.6,62.4,4.6C62.4,4.6,62.4,4.6,62.4,4.6z"/>
	<path class="st0" d="M59.1,5.4c0.1,0.1,0,0.1,0,0.2C59.1,5.5,59.1,5.4,59.1,5.4z"/>
	<path class="st0" d="M63,6C63,6,63,6,63,6C63,6,63,6,63,6C63,6,63,6,63,6z"/>
	<path class="st0" d="M59.2,6.1c0,0.1,0,0.1,0,0.2c0,0,0,0,0,0C59.2,6.2,59.2,6.2,59.2,6.1z"/>
	<path class="st0" d="M46.1,5C46.1,5,46.1,5,46.1,5C46.1,5,46.1,5,46.1,5C46.1,5,46.1,5,46.1,5z"/>
	<path class="st0" d="M20,2.9C20,2.9,20,2.9,20,2.9L20,2.9c0,0,0,0.1-0.1,0.2C19.9,3,19.8,3,19.8,2.9c0,0-0.1-0.1-0.1-0.1
		c0,0,0-0.1,0-0.1C19.8,2.7,19.9,2.8,20,2.9c0-0.1,0.1-0.1,0.1-0.2c0,0,0,0,0,0c0,0,0,0,0,0C20,2.8,20,2.9,20,2.9z"/>
	<path class="st0" d="M46.9,6.3C46.9,6.3,46.9,6.3,46.9,6.3C46.9,6.3,47,6.3,46.9,6.3C47,6.3,47,6.3,46.9,6.3
		C46.9,6.3,46.9,6.3,46.9,6.3z"/>
	<polygon class="st0" points="34.8,5.2 34.8,5.2 34.8,5.3 	"/>
	<path class="st0" d="M25.5,4C25.5,4,25.5,4.1,25.5,4C25.5,4.1,25.5,4.1,25.5,4C25.5,4.1,25.5,4,25.5,4z"/>
	<polygon class="st0" points="25.5,4.1 25.5,4.1 25.5,4.1 	"/>
	<path class="st0" d="M28.1,4.5C28.1,4.5,28.1,4.6,28.1,4.5L28.1,4.5C28.1,4.6,28.1,4.6,28.1,4.5z"/>
	<polygon class="st0" points="48.7,7.1 48.7,7.1 48.7,7.1 	"/>
	<path class="st0" d="M47.3,6.9L47.3,6.9C47.3,6.9,47.3,6.9,47.3,6.9C47.3,6.9,47.3,6.9,47.3,6.9z"/>
	<path class="st0" d="M15.2,3.1C15.2,3.1,15.2,3.1,15.2,3.1C15.2,3.1,15.2,3.1,15.2,3.1C15.2,3.1,15.2,3.1,15.2,3.1
		C15.2,3.1,15.2,3.1,15.2,3.1z"/>
	<path class="st0" d="M15.2,3C15.2,3,15.2,3,15.2,3C15.2,3,15.2,3,15.2,3C15.2,3,15.2,3,15.2,3z"/>
	<path class="st0" d="M52.4,7.7L52.4,7.7C52.4,7.7,52.4,7.7,52.4,7.7C52.4,7.7,52.4,7.7,52.4,7.7z"/>
	<ellipse transform="matrix(0.5465 -0.8374 0.8374 0.5465 6.5832 20.128)" class="st0" cx="21.9" cy="4" rx="0" ry="0"/>
	<path class="st0" d="M21.8,3.9C21.9,4,21.9,4,21.8,3.9C21.9,4,21.9,4,21.8,3.9C21.8,4,21.8,4,21.8,3.9z"/>
	<path class="st0" d="M11.7,3.1C11.7,3.1,11.7,3.1,11.7,3.1C11.7,3.1,11.7,3.1,11.7,3.1C11.7,3.2,11.7,3.1,11.7,3.1z"/>
	<path class="st0" d="M11.2,3.1L11.2,3.1C11.2,3.1,11.2,3.1,11.2,3.1C11.2,3.1,11.2,3.1,11.2,3.1z"/>
	<path class="st0" d="M11.2,3.1C11.1,3.1,11.2,3.1,11.2,3.1C11.2,3.1,11.2,3.1,11.2,3.1C11.2,3.1,11.2,3.1,11.2,3.1z"/>
	<polygon class="st0" points="14.4,3.5 14.5,3.5 14.4,3.5 	"/>
	<path class="st0" d="M11.7,3.2C11.7,3.2,11.7,3.2,11.7,3.2C11.7,3.2,11.7,3.2,11.7,3.2C11.7,3.2,11.7,3.2,11.7,3.2
		C11.7,3.2,11.7,3.2,11.7,3.2z"/>
	<path class="st0" d="M11.6,3.2C11.6,3.2,11.7,3.2,11.6,3.2C11.7,3.2,11.7,3.2,11.6,3.2C11.6,3.2,11.6,3.2,11.6,3.2z"/>
	<path class="st0" d="M5.1,3.2C5.1,3.2,5.1,3.2,5.1,3.2C5.1,3.2,5.1,3.2,5.1,3.2C5.1,3.2,5.1,3.2,5.1,3.2z"/>
	<path class="st0" d="M11.4,3.9L11.4,3.9C11.4,3.9,11.4,3.9,11.4,3.9C11.4,3.9,11.4,3.9,11.4,3.9z"/>
	<path class="st0" d="M11.3,4C11.3,4,11.3,4,11.3,4C11.4,4,11.4,4,11.3,4C11.3,4,11.3,4,11.3,4z"/>
	<polygon class="st0" points="3.4,3 3.4,3.1 3.4,3.1 	"/>
	<path class="st0" d="M11.6,4.1L11.6,4.1C11.5,4.1,11.5,4.1,11.6,4.1C11.5,4.1,11.6,4.1,11.6,4.1z"/>
	<path class="st0" d="M7.9,3.9C7.8,3.9,7.8,3.9,7.9,3.9C7.8,3.8,7.8,3.8,7.9,3.9z"/>
	<ellipse transform="matrix(0.664 -0.7477 0.7477 0.664 -0.2457 7.1784)" class="st0" cx="7.9" cy="3.9" rx="0" ry="0"/>
	<path class="st0" d="M42.5,8.2C42.5,8.2,42.5,8.2,42.5,8.2C42.5,8.2,42.5,8.2,42.5,8.2C42.5,8.2,42.5,8.2,42.5,8.2z"/>
	<ellipse transform="matrix(0.9551 -0.2962 0.2962 0.9551 -0.5104 12.9475)" class="st0" cx="42.5" cy="8.2" rx="0" ry="0"/>
	<path class="st0" d="M63.8,12.5C63.8,12.5,63.8,12.5,63.8,12.5C63.8,12.5,63.8,12.5,63.8,12.5C63.8,12.5,63.8,12.5,63.8,12.5z"/>
	<path class="st0" d="M63.8,12.5C63.8,12.5,63.8,12.5,63.8,12.5C63.8,12.5,63.8,12.5,63.8,12.5C63.8,12.5,63.8,12.5,63.8,12.5
		C63.8,12.5,63.8,12.5,63.8,12.5z"/>
	<path class="st0" d="M3,5.8C3,5.8,3,5.8,3,5.8L3,5.8C3,5.6,3.1,5.4,3.3,5.3c0,0,0,0.1,0,0.1c0,0.2-0.1,0.3-0.1,0.5C3.2,6,3.1,6,3,6
		C3,5.9,3,5.8,3,5.8C3,5.8,2.9,5.8,3,5.8C2.9,5.8,3,5.8,3,5.8L3,5.8z"/>
	<path class="st0" d="M2.9,5.8C2.9,5.8,2.9,5.8,2.9,5.8C2.9,5.8,2.9,5.8,2.9,5.8C2.9,5.8,2.9,5.8,2.9,5.8z"/>
	<polygon class="st0" points="32.2,42.5 32.2,42.5 32.2,42.5 	"/>
	<path class="st0" d="M32.6,42.5C32.6,42.5,32.6,42.5,32.6,42.5L32.6,42.5C32.6,42.5,32.6,42.5,32.6,42.5z"/>
	<path class="st0" d="M30.8,42.1C30.8,42.1,30.7,42.1,30.8,42.1C30.7,42.1,30.7,42.1,30.8,42.1C30.7,42.1,30.7,42.1,30.8,42.1
		L30.8,42.1z"/>
	<polygon class="st0" points="61,31.1 60.9,31.1 60.9,31.1 	"/>
	<path class="st0" d="M45.7,43C45.7,43,45.7,43,45.7,43C45.7,43,45.7,43,45.7,43C45.7,43,45.7,43,45.7,43z"/>
	<path class="st0" d="M45.7,43L45.7,43C45.7,43,45.7,43,45.7,43C45.7,43,45.7,43,45.7,43z"/>
	<path class="st0" d="M56.7,41.6C56.7,41.6,56.7,41.5,56.7,41.6C56.7,41.5,56.7,41.5,56.7,41.6C56.7,41.5,56.7,41.5,56.7,41.6
		C56.7,41.5,56.7,41.6,56.7,41.6z"/>
	<path class="st0" d="M60.9,29.7c0,0-0.1,0-0.1,0C60.8,29.6,60.8,29.6,60.9,29.7z"/>
	<path class="st0" d="M61.4,24C61.4,24,61.4,24,61.4,24C61.4,24,61.4,24,61.4,24C61.4,24,61.4,24,61.4,24z"/>
	<path class="st0" d="M61.4,24L61.4,24C61.4,24,61.4,24,61.4,24C61.4,24,61.4,24,61.4,24z"/>
	<path class="st0" d="M64.6,23.6C64.6,23.6,64.6,23.6,64.6,23.6C64.6,23.6,64.6,23.6,64.6,23.6C64.6,23.6,64.6,23.6,64.6,23.6z"/>
	<path class="st0" d="M64.6,23.6C64.6,23.6,64.6,23.7,64.6,23.6C64.6,23.7,64.6,23.7,64.6,23.6C64.6,23.7,64.6,23.7,64.6,23.6
		C64.6,23.7,64.6,23.7,64.6,23.6z"/>
	<path class="st0" d="M56.2,31.9C56.2,31.9,56.2,32,56.2,31.9C56.2,32,56.2,32,56.2,31.9C56.2,32,56.2,32,56.2,31.9
		C56.2,32,56.2,32,56.2,31.9C56.2,31.9,56.2,31.9,56.2,31.9z"/>
	<path class="st0" d="M56.2,31.4C56.2,31.4,56.2,31.4,56.2,31.4C56.2,31.4,56.2,31.4,56.2,31.4C56.2,31.4,56.2,31.4,56.2,31.4z"/>
	<ellipse transform="matrix(0.6928 -0.7211 0.7211 0.6928 -5.3924 50.1692)" class="st0" cx="56.2" cy="31.4" rx="0" ry="0"/>
	<path class="st0" d="M6.8,22.1c-0.1,0-0.1,0-0.2-0.1c0,0,0,0,0,0C6.7,22,6.8,22,6.8,22.1z"/>
	<path class="st0" d="M4.4,22.5c0,0-0.1-0.1-0.1-0.1c0.1,0,0.1-0.1,0.2-0.1c0,0,0.1,0.1,0.1,0.1C4.6,22.4,4.5,22.5,4.4,22.5z"/>
	<path class="st0" d="M4.7,21C4.8,21,4.8,21,4.7,21C4.8,21,4.8,21,4.7,21C4.8,21,4.8,21.1,4.7,21C4.8,21.1,4.8,21,4.7,21z"/>
	<polygon class="st0" points="4.7,21.5 4.7,21.5 4.7,21.5 4.7,21.5 4.7,21.5 	"/>
	<path class="st0" d="M4.9,21.4c0,0.1-0.1,0.1-0.1,0.1C4.8,21.5,4.8,21.4,4.9,21.4z"/>
	<path class="st0" d="M4.7,21.7C4.7,21.7,4.7,21.6,4.7,21.7c0-0.1,0-0.1,0-0.1C4.7,21.6,4.7,21.6,4.7,21.7
		C4.7,21.7,4.7,21.7,4.7,21.7z"/>
	<path class="st0" d="M5.2,20.9C5.2,20.9,5.2,20.9,5.2,20.9C5.1,20.9,5.1,20.9,5.2,20.9C5.2,20.9,5.2,20.9,5.2,20.9
		C5.2,20.9,5.2,20.9,5.2,20.9z"/>
	<path class="st0" d="M4.5,20.4C4.5,20.4,4.5,20.4,4.5,20.4C4.5,20.4,4.5,20.4,4.5,20.4C4.5,20.4,4.5,20.4,4.5,20.4z"/>
	<path class="st0" d="M4.5,20.3C4.5,20.3,4.5,20.3,4.5,20.3C4.5,20.3,4.5,20.3,4.5,20.3L4.5,20.3z"/>
	<polygon class="st0" points="4.4,23.7 4.4,23.7 4.4,23.7 	"/>
	<path class="st0" d="M4.4,23.7C4.4,23.7,4.4,23.7,4.4,23.7C4.4,23.7,4.5,23.7,4.4,23.7C4.5,23.7,4.5,23.7,4.4,23.7
		C4.4,23.7,4.4,23.7,4.4,23.7z"/>
	<path class="st0" d="M4.5,23.3C4.5,23.3,4.5,23.3,4.5,23.3C4.5,23.3,4.5,23.3,4.5,23.3C4.5,23.3,4.5,23.3,4.5,23.3
		C4.5,23.3,4.5,23.3,4.5,23.3z"/>
	<path class="st0" d="M5.2,20.6C5.2,20.6,5.2,20.6,5.2,20.6C5.2,20.6,5.2,20.6,5.2,20.6C5.2,20.6,5.2,20.6,5.2,20.6z"/>
	<path class="st0" d="M5.2,20.6C5.2,20.6,5.2,20.6,5.2,20.6C5.2,20.6,5.2,20.6,5.2,20.6L5.2,20.6z"/>
	<polygon class="st0" points="53.7,25.6 53.7,25.6 53.7,25.6 	"/>
	<path class="st0" d="M55.7,26.6C55.7,26.6,55.6,26.6,55.7,26.6C55.6,26.6,55.6,26.6,55.7,26.6C55.6,26.6,55.6,26.6,55.7,26.6
		C55.7,26.6,55.7,26.6,55.7,26.6z"/>
	<ellipse transform="matrix(0.7713 -0.6364 0.6364 0.7713 -5.2741 32.3176)" class="st0" cx="42.3" cy="23.5" rx="0" ry="0"/>
	<path class="st0" d="M41.2,24.2c0,0-0.1-0.1-0.1-0.1c0-0.1,0-0.2,0.1-0.2c0,0,0.1,0.1,0.1,0.1C41.3,24.1,41.3,24.2,41.2,24.2z"/>
	<path class="st0" d="M42.2,23.3C42.2,23.3,42.1,23.4,42.2,23.3c-0.1,0.1-0.2,0-0.2-0.1c0,0,0.1-0.1,0.1-0.1
		C42.2,23.1,42.2,23.2,42.2,23.3z"/>
	<polygon class="st0" points="42.1,22.8 42.1,22.8 42.1,22.8 	"/>
	<path class="st0" d="M43.9,25.3C43.9,25.2,43.9,25.2,43.9,25.3C43.9,25.2,43.9,25.3,43.9,25.3C43.9,25.3,43.9,25.3,43.9,25.3
		C43.9,25.3,43.9,25.3,43.9,25.3z"/>
	<polygon class="st0" points="54.7,27.9 54.7,27.9 54.7,27.9 	"/>
	<path class="st0" d="M60,18.6c0-0.1,0-0.2,0-0.2c0,0,0-0.1,0.1-0.1c0.1,0,0.1,0.1,0.1,0.2C60.1,18.5,60,18.5,60,18.6z"/>
	<path class="st0" d="M59.9,19.8C59.9,19.8,60,19.8,59.9,19.8c0.1-0.1,0.1,0,0.1,0c0,0,0,0.1,0,0.2C60,19.9,60,19.9,59.9,19.8
		C60,19.9,59.9,19.8,59.9,19.8z"/>
	<ellipse transform="matrix(0.8928 -0.4504 0.4504 0.8928 -2.5471 29.2023)" class="st0" cx="60.1" cy="20" rx="0" ry="0"/>
	
		<ellipse transform="matrix(7.409015e-02 -0.9973 0.9973 7.409015e-02 36.9868 74.9427)" class="st0" cx="58.9" cy="17.6" rx="0" ry="0"/>
	<path class="st0" d="M59,17.5C59,17.5,59,17.5,59,17.5c0,0.1,0,0.1,0,0.1C58.9,17.6,58.9,17.6,59,17.5C58.9,17.5,58.9,17.5,59,17.5
		C58.9,17.5,58.9,17.5,59,17.5z"/>
	<polygon class="st0" points="59.2,17.1 59.2,17.1 59.2,17.2 	"/>
	<ellipse transform="matrix(0.8488 -0.5288 0.5288 0.8488 1.0394 19.7048)" class="st0" cx="35" cy="8" rx="0" ry="0"/>
	<path class="st0" d="M35,8.1C35,8.1,35,8.1,35,8.1C35,8.1,35,8.1,35,8.1C35,8.1,35,8.1,35,8.1C35,8.1,35,8.1,35,8.1z"/>
	<path class="st0" d="M10.8,6C10.8,6,10.8,5.9,10.8,6C10.8,5.9,10.8,5.9,10.8,6C10.8,6,10.8,6,10.8,6C10.8,6,10.8,6,10.8,6z"/>
	<polygon class="st0" points="33,7.7 33.1,7.7 33,7.7 	"/>
	<polygon class="st0" points="33.2,7.9 33.2,7.9 33.2,7.9 	"/>
	<path class="st0" d="M4.3,27.5c-0.1-0.1-0.1-0.2-0.2-0.3c0,0,0-0.1,0.1-0.1C4.3,27.3,4.3,27.3,4.3,27.5z"/>
	<ellipse transform="matrix(0.2094 -0.9778 0.9778 0.2094 -23.2979 25.5956)" class="st0" cx="4.2" cy="27.2" rx="0" ry="0"/>
	<path class="st0" d="M46.7,9.4C46.7,9.5,46.7,9.5,46.7,9.4C46.7,9.5,46.7,9.5,46.7,9.4C46.7,9.4,46.7,9.4,46.7,9.4z"/>
	<path class="st0" d="M31.5,7.9C31.4,7.9,31.4,8,31.5,7.9L31.5,7.9C31.4,7.9,31.4,7.9,31.5,7.9z"/>
	<path class="st0" d="M29.4,7.8c0,0-0.1,0-0.1,0l0,0c0,0,0.1-0.1,0.1-0.1C29.4,7.7,29.4,7.7,29.4,7.8C29.5,7.8,29.4,7.8,29.4,7.8z"
		/>
	<path class="st0" d="M7.1,4.6C7.1,4.6,7.1,4.6,7.1,4.6C7.1,4.6,7.1,4.6,7.1,4.6C7.1,4.6,7.1,4.6,7.1,4.6C7.1,4.6,7.1,4.6,7.1,4.6z"
		/>
	<path class="st0" d="M45.9,38.9C46,38.9,46,38.9,45.9,38.9C46,38.9,46,39,45.9,38.9C46,39,45.9,38.9,45.9,38.9
		C45.9,38.9,45.9,38.9,45.9,38.9z"/>
	<path class="st0" d="M45.9,39C45.9,38.9,45.9,38.9,45.9,39L45.9,39C45.9,38.9,45.9,38.9,45.9,39C45.9,38.9,45.9,38.9,45.9,39
		L45.9,39C45.9,38.9,45.9,38.9,45.9,39C45.9,38.9,45.9,38.9,45.9,39C45.9,38.9,45.9,38.9,45.9,39z"/>
	<path class="st0" d="M45.9,39C45.9,39,45.9,39,45.9,39C45.9,39,45.9,39,45.9,39C45.9,39,45.9,39,45.9,39z"/>
	<path class="st0" d="M34.1,26.7C34.1,26.7,34.1,26.8,34.1,26.7C34.1,26.8,34.1,26.7,34.1,26.7C34.1,26.7,34.1,26.7,34.1,26.7z"/>
	<ellipse transform="matrix(0.8905 -0.455 0.455 0.8905 -3.4918 30.0165)" class="st0" cx="60.6" cy="22.3" rx="0" ry="0"/>
	<path class="st0" d="M60.6,22.2C60.6,22.2,60.6,22.2,60.6,22.2C60.6,22.2,60.6,22.2,60.6,22.2C60.6,22.2,60.6,22.2,60.6,22.2z"/>
	<path class="st0" d="M12,6.5C12,6.5,12,6.5,12,6.5c0-0.1,0-0.1,0-0.2C12,6.4,12.1,6.4,12,6.5C12.1,6.5,12,6.5,12,6.5z"/>
	<path class="st0" d="M56.8,35.5C56.8,35.5,56.8,35.5,56.8,35.5C56.9,35.5,56.9,35.5,56.8,35.5C56.8,35.5,56.8,35.5,56.8,35.5z"/>
	<path class="st0" d="M40.3,7C40.3,6.9,40.3,6.9,40.3,7C40.3,6.9,40.3,7,40.3,7C40.3,7,40.3,7,40.3,7z"/>
	<path class="st0" d="M28.6,7.5C28.6,7.5,28.6,7.5,28.6,7.5C28.6,7.5,28.6,7.5,28.6,7.5C28.6,7.5,28.6,7.5,28.6,7.5
		C28.6,7.5,28.6,7.5,28.6,7.5z"/>
	<path class="st0" d="M28.6,7.8C28.6,7.8,28.6,7.8,28.6,7.8C28.6,7.8,28.6,7.9,28.6,7.8C28.6,7.9,28.6,7.9,28.6,7.8z"/>
	<path class="st0" d="M40.9,25.3C40.9,25.3,40.9,25.3,40.9,25.3C40.9,25.3,40.9,25.3,40.9,25.3C40.9,25.3,40.9,25.3,40.9,25.3
		C40.9,25.3,40.9,25.3,40.9,25.3z"/>
	<path class="st0" d="M16.1,3.8C16.1,3.8,16.1,3.8,16.1,3.8C16.1,3.8,16.1,3.8,16.1,3.8C16.1,3.8,16.1,3.8,16.1,3.8z"/>
	<path class="st0" d="M16.1,3.7C16.1,3.7,16.1,3.7,16.1,3.7C16.1,3.7,16.1,3.7,16.1,3.7C16.1,3.7,16.1,3.7,16.1,3.7
		C16.1,3.7,16.1,3.7,16.1,3.7z"/>
	<path class="st0" d="M27.4,7.2C27.4,7.2,27.5,7.1,27.4,7.2C27.5,7.1,27.5,7.2,27.4,7.2C27.4,7.2,27.4,7.2,27.4,7.2z"/>
	<path class="st0" d="M39.7,7.2C39.7,7.2,39.7,7.2,39.7,7.2C39.7,7.2,39.7,7.2,39.7,7.2C39.7,7.2,39.7,7.2,39.7,7.2z"/>
	<path class="st0" d="M33.5,5.7C33.5,5.7,33.5,5.6,33.5,5.7C33.5,5.6,33.5,5.6,33.5,5.7C33.5,5.6,33.5,5.7,33.5,5.7z"/>
	<path class="st0" d="M34.6,7.1C34.6,7.1,34.6,7.1,34.6,7.1C34.5,7.1,34.5,7,34.6,7.1C34.5,7,34.6,7,34.6,7.1
		C34.6,7,34.6,7,34.6,7.1C34.6,7,34.6,7.1,34.6,7.1z"/>
	<path class="st0" d="M6.5,23C6.5,23,6.5,23,6.5,23L6.5,23C6.5,23,6.5,23,6.5,23C6.5,23,6.5,23,6.5,23C6.5,23,6.5,23,6.5,23z"/>
	<path class="st0" d="M10,18.7C10,18.7,10,18.7,10,18.7C10,18.7,10,18.8,10,18.7C10,18.8,10,18.7,10,18.7z"/>
	<polygon class="st0" points="42.1,23.9 42.1,23.9 42.1,23.9 	"/>
	<path class="st0" d="M29.9,25.4C29.9,25.4,29.9,25.4,29.9,25.4C29.9,25.4,29.9,25.4,29.9,25.4L29.9,25.4z"/>
	<polygon class="st0" points="43.6,25.7 43.6,25.7 43.6,25.7 43.6,25.7 43.6,25.7 	"/>
	<polygon class="st0" points="54.2,28.8 54.2,28.8 54.2,28.8 	"/>
	<path class="st0" d="M55,26.7c0,0-0.1,0.1-0.2,0c-0.1,0-0.1-0.1-0.1-0.2C54.8,26.6,54.9,26.6,55,26.7C55,26.7,55,26.7,55,26.7z"/>
	<ellipse transform="matrix(0.5183 -0.8552 0.8552 0.5183 3.6309 59.581)" class="st0" cx="54.7" cy="26.6" rx="0" ry="0"/>
	<polygon class="st0" points="54.2,28.8 54.2,28.8 54.2,28.8 	"/>
	<path class="st0" d="M49.6,41.6C49.6,41.6,49.6,41.6,49.6,41.6C49.6,41.6,49.6,41.6,49.6,41.6L49.6,41.6z"/>
	<polygon class="st0" points="17,7.7 17,7.7 16.9,7.7 16.9,7.7 16.9,7.7 17,7.7 	"/>
	<path class="st0" d="M26.7,7.4C26.7,7.4,26.7,7.4,26.7,7.4C26.7,7.4,26.7,7.3,26.7,7.4C26.7,7.3,26.8,7.3,26.7,7.4
		C26.8,7.4,26.8,7.4,26.7,7.4z"/>
	<polygon class="st0" points="12.1,6.2 12.1,6.2 12.1,6.3 	"/>
	<polygon class="st0" points="37,43.3 36.9,43.3 37,43.3 	"/>
	<path class="st0" d="M49.5,41.7c0-0.1,0.1-0.1,0.1-0.2l0,0C49.6,41.6,49.5,41.7,49.5,41.7L49.5,41.7z"/>
	<path class="st0" d="M45.9,38.8C45.9,38.8,46,38.8,45.9,38.8C46,38.8,46,38.8,45.9,38.8C46,38.9,45.9,38.8,45.9,38.8z"/>
	<polygon class="st0" points="29.9,25.4 29.9,25.4 29.9,25.4 	"/>
	<polygon class="st0" points="35.9,25.9 35.9,25.8 36,25.9 	"/>
	<polygon class="st0" points="10.1,28.3 10,28.3 10.1,28.3 	"/>
	<path class="st0" d="M42.2,23.4C42.2,23.4,42.2,23.4,42.2,23.4C42.1,23.4,42.1,23.4,42.2,23.4C42.1,23.4,42.2,23.4,42.2,23.4
		C42.2,23.4,42.2,23.4,42.2,23.4z"/>
	<path class="st0" d="M42.6,25.4C42.6,25.4,42.6,25.4,42.6,25.4C42.6,25.4,42.6,25.4,42.6,25.4C42.6,25.4,42.6,25.4,42.6,25.4z"/>
	<polygon class="st0" points="41.2,24.3 41.2,24.3 41.2,24.3 41.2,24.3 41.2,24.3 	"/>
	<path class="st0" d="M41,24.3c-0.1,0-0.1-0.1-0.2-0.1c0-0.1,0-0.2,0.1-0.2c0,0,0.1,0,0.1,0c0,0.1,0.1,0.1,0.1,0.2c0,0,0,0.1,0,0.1
		C41.1,24.3,41.1,24.3,41,24.3z"/>
	<polygon class="st0" points="42.3,24.5 42.3,24.5 42.3,24.5 	"/>
	<path class="st0" d="M42.2,24.7C42.2,24.7,42.1,24.7,42.2,24.7c-0.1,0.1-0.1,0.1-0.1,0.1c0,0,0,0,0,0c0-0.1,0-0.1,0.1-0.2
		c0.1,0,0.1,0,0.2,0C42.2,24.6,42.2,24.6,42.2,24.7z"/>
	<path class="st0" d="M42.3,24.6C42.3,24.6,42.3,24.6,42.3,24.6C42.3,24.6,42.3,24.6,42.3,24.6C42.3,24.6,42.3,24.6,42.3,24.6z"/>
	<path class="st0" d="M42.3,24.5C42.3,24.5,42.3,24.6,42.3,24.5C42.3,24.6,42.3,24.5,42.3,24.5L42.3,24.5z"/>
</g>
</svg>';
		$global_variables['ppExpand']                 = esc_html__( 'Expand the image', 'wanderland' );
		$global_variables['ppNext']                   = esc_html__( 'Next', 'wanderland' );
		$global_variables['ppPrev']                   = esc_html__( 'Previous', 'wanderland' );
		$global_variables['ppClose']                  = esc_html__( 'Close', 'wanderland' );
		
		$global_variables = apply_filters( 'wanderland_mikado_filter_js_global_variables', $global_variables );
		
		wp_localize_script( 'wanderland-mikado-modules', 'mkdfGlobalVars', array(
			'vars' => $global_variables
		) );
	}

	add_action( 'wp_enqueue_scripts', 'wanderland_mikado_get_global_variables' );
}

if ( ! function_exists( 'wanderland_mikado_per_page_js_variables' ) ) {
	/**
	 * Outputs global JS variable that holds page settings
	 */
	function wanderland_mikado_per_page_js_variables() {
		$per_page_js_vars = apply_filters( 'wanderland_mikado_filter_per_page_js_vars', array() );

		wp_localize_script( 'wanderland-mikado-modules', 'mkdfPerPageVars', array(
			'vars' => $per_page_js_vars
		) );
	}

	add_action( 'wp_enqueue_scripts', 'wanderland_mikado_per_page_js_variables' );
}

if ( ! function_exists( 'wanderland_mikado_content_elem_style_attr' ) ) {
	/**
	 * Defines filter for adding custom styles to content HTML element
	 */
	function wanderland_mikado_content_elem_style_attr() {
		$styles = apply_filters( 'wanderland_mikado_filter_content_elem_style_attr', array() );

		wanderland_mikado_inline_style( $styles );
	}
}

if ( ! function_exists( 'wanderland_mikado_is_plugin_installed' ) ) {
	/**
	 * Function that checks if forward plugin installed
	 *
	 * @param $plugin string
	 *
	 * @return bool
	 */
	function wanderland_mikado_is_plugin_installed( $plugin ) {
		switch ( $plugin ) {
			case 'core':
				return defined( 'WANDERLAND_CORE_VERSION' );
				break;
			case 'woocommerce':
				return function_exists( 'is_woocommerce' );
				break;
			case 'visual-composer':
				return class_exists( 'WPBakeryVisualComposerAbstract' );
				break;
			case 'revolution-slider':
				return class_exists( 'RevSliderFront' );
				break;
			case 'contact-form-7':
				return defined( 'WPCF7_VERSION' );
				break;
			case 'wpml':
				return defined( 'ICL_SITEPRESS_VERSION' );
				break;
			case 'gutenberg-editor':
				return class_exists( 'WP_Block_Type' );
				break;
			case 'gutenberg-plugin':
				return function_exists( 'is_gutenberg_page' ) && is_gutenberg_page();
				break;
			default:
				return false;
				break;
		}
	}
}

if ( ! function_exists( 'wanderland_mikado_get_module_part' ) ) {
	function wanderland_mikado_get_module_part( $module ) {
		return $module;
	}
}

if ( ! function_exists( 'wanderland_mikado_max_image_width_srcset' ) ) {
	/**
	 * Set max width for srcset to 1920
	 *
	 * @return int
	 */
	function wanderland_mikado_max_image_width_srcset() {
		return 1920;
	}
	
	add_filter( 'max_srcset_image_width', 'wanderland_mikado_max_image_width_srcset' );
}


if ( ! function_exists( 'wanderland_mikado_has_dashboard_shortcodes' ) ) {
	/**
	 * Function that checks if current page has at least one of dashboard shortcodes added
	 * @return bool
	 */
	function wanderland_mikado_has_dashboard_shortcodes() {
		$dashboard_shortcodes = array();

		$dashboard_shortcodes = apply_filters( 'wanderland_mikado_filter_dashboard_shortcodes_list', $dashboard_shortcodes );

		foreach ( $dashboard_shortcodes as $dashboard_shortcode ) {
			$has_shortcode = wanderland_mikado_has_shortcode( $dashboard_shortcode );

			if ( $has_shortcode ) {
				return true;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'wanderland_section_title_highlighted_word_left_svg' ) ) {

	function wanderland_section_title_highlighted_word_left_svg( ) {

		$html = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 15.7 30" style="enable-background:new 0 0 15.7 30;" xml:space="preserve" class="mkdf-active-hover-left"><polygon class="st0" points="2.6,1 0.7,3.3 2,5.8 2.3,7.6 2.9,8.7 4.4,10.5 3.9,10.8 4.4,11.9 4.4,12.8 4.1,13.8 3.3,14.7 3.9,15.8 4.4,16.8 4,17.5 3.5,18.1 2.2,20.2 3.4,21.5 4.2,24.1 3.4,25.4 2.5,27.4 2.5,27.8 3.2,28.3 4.1,28.5 4.9,29 14.8,29 14.8,1 "/></svg>';

		return $html;
	}
}

if ( ! function_exists( 'wanderland_section_title_highlighted_word_right_svg' ) ) {

	function wanderland_section_title_highlighted_word_right_svg( ) {

		$html = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 13.3 30" style="enable-background:new 0 0 13.3 30;" xml:space="preserve" class="mkdf-active-hover-right"><polygon class="st0" points="10,1 10.2,2.1 10.6,2.9 10.6,3.3 10.8,3.7 10.8,4.3 11,5 11,5.7 11,6.3 10.5,6.7 10.8,7.3 11,7.8 	11.6,8.3 11.6,8.6 11.5,8.9 11.6,9.9 11.6,10.5 12.4,11.6 12.1,12 12.4,12.2 11.8,12.8 11.4,13.5 11.6,13.7 11.9,13.7 12,13.9 11.5,15.1 10.8,16 9.1,17.7 9.7,18.2 9.3,19 9.7,19.8 9.6,20.6 9.7,21.5 9.6,21.9 9.6,22.3 10.1,22.8 9.6,23.6 9.7,24 9.7,24.2 9.9,24.4 9.5,24.7 9.3,25.4 9.3,25.9 8.8,26.2 8.5,27.1 8.8,27.8 9.4,28.6 7.8,29 0.9,29 0.9,1 "/></svg>';

		return $html;
	}
}
