<?php

if ( ! function_exists( 'wanderland_core_get_cpt_shortcode_module_template_part' ) ) {
	/**
	 * Loads module template part.
	 *
	 * @param string $post_type name of the post type of shortcode
	 * @param string $shortcode name of the shortcode
	 * @param string $template name of the template to load
	 * @param string $slug
	 * @param array $params array of parameters to pass to template
	 * @param array $additional_params array of additional parameters to pass to template
	 *
	 * @return html
	 */
	function wanderland_core_get_cpt_shortcode_module_template_part( $post_type, $shortcode, $template, $slug = '', $params = array(), $additional_params = array() ) {
		
		//HTML Content from template
		$html          = '';
		$template_path = WANDERLAND_CORE_CPT_PATH . '/' . $post_type . '/shortcodes/' . $shortcode . '/templates';
		
		$temp = $template_path . '/' . $template;
		if ( is_array( $params ) && count( $params ) ) {
			extract( $params );
		}
		
		if ( is_array( $additional_params ) && count( $additional_params ) ) {
			extract( $additional_params );
		}
		
		$template = '';
		
		if ( ! empty( $temp ) ) {
			if ( ! empty( $slug ) ) {
				$template = "{$temp}-{$slug}.php";
				
				if ( ! file_exists( $template ) ) {
					$template = $temp . '.php';
				}
			} else {
				$template = $temp . '.php';
			}
		}
		
		if ( $template ) {
			ob_start();
			include( $template );
			$html = ob_get_clean();
		}
		
		return $html;
	}
}

if ( ! function_exists( 'wanderland_core_get_cpt_single_module_template_part' ) ) {
	/**
	 * Loads module template part.
	 *
	 * @param string $cpt_name name of the cpt folder
	 * @param string $template name of the template to load
	 * @param string $slug
	 * @param array $params array of parameters to pass to template
	 *
	 * @return html
	 */
	function wanderland_core_get_cpt_single_module_template_part( $template, $cpt_name, $slug = '', $params = array() ) {
		
		//HTML Content from template
		$html          = '';
		$template_path = WANDERLAND_CORE_CPT_PATH . '/' . $cpt_name;
		
		$temp = $template_path . '/' . $template;
		
		if ( is_array( $params ) && count( $params ) ) {
			extract( $params );
		}
		
		$template = '';
		
		if ( ! empty( $temp ) ) {
			if ( ! empty( $slug ) ) {
				$template = "{$temp}-{$slug}.php";
				
				if ( ! file_exists( $template ) ) {
					$template = $temp . '.php';
				}
			} else {
				$template = $temp . '.php';
			}
		}
		
		if ( ! empty( $template ) ) {
			ob_start();
			include( $template );
			$html = ob_get_clean();
		}

		echo wanderland_mikado_get_module_part($html);
	}
}

if ( ! function_exists( 'wanderland_core_return_cpt_single_module_template_part' ) ) {
	/**
	 * Loads module template part.
	 *
	 * @param string $cpt_name name of the cpt folder
	 * @param string $template name of the template to load
	 * @param string $slug
	 * @param array $params array of parameters to pass to template
	 *
	 * @return html
	 */
	function wanderland_core_return_cpt_single_module_template_part( $template, $cpt_name, $slug = '', $params = array() ) {
		
		//HTML Content from template
		$html          = '';
		$template_path = WANDERLAND_CORE_CPT_PATH . '/' . $cpt_name;
		
		$temp = $template_path . '/' . $template;
		
		if ( is_array( $params ) && count( $params ) ) {
			extract( $params );
		}
		
		$template = '';
		
		if ( ! empty( $temp ) ) {
			if ( ! empty( $slug ) ) {
				$template = "{$temp}-{$slug}.php";
				
				if ( ! file_exists( $template ) ) {
					$template = $temp . '.php';
				}
			} else {
				$template = $temp . '.php';
			}
		}
		
		if ( ! empty( $template ) ) {
			ob_start();
			include( $template );
			$html = ob_get_clean();
		}
		
		return $html;
	}
}

if ( ! function_exists( 'wanderland_core_get_shortcode_module_template_part' ) ) {
	/**
	 * Loads module template part.
	 *
	 * @param string $template name of the template to load
	 * @param string $shortcode name of the shortcode folder
	 * @param string $slug
	 * @param array $params array of parameters to pass to template
	 *
	 * @return html
	 */
	function wanderland_core_get_shortcode_module_template_part( $template, $shortcode, $slug = '', $params = array() ) {
		
		//HTML Content from template
		$html          = '';
		$template_path = WANDERLAND_CORE_SHORTCODES_PATH . '/' . $shortcode;
		
		$temp = $template_path . '/' . $template;
		
		if ( is_array( $params ) && count( $params ) ) {
			extract( $params );
		}
		
		$template = '';
		
		if ( ! empty( $temp ) ) {
			if ( ! empty( $slug ) ) {
				$template = "{$temp}-{$slug}.php";
				
				if ( ! file_exists( $template ) ) {
					$template = $temp . '.php';
				}
			} else {
				$template = $temp . '.php';
			}
		}
		
		if ( $template ) {
			ob_start();
			include( $template );
			$html = ob_get_clean();
		}
		
		return $html;
	}
}

if ( ! function_exists( 'wanderland_core_get_module_template_part' ) ) {
    /**
     * Loads module template part.
     *
     * @param string $module name of the module to load
     * @param string $template name of the template file
     * @param string $slug
     * @param array $params array of parameters to pass to template
     *
     * @return html
     */
    function wanderland_core_get_module_template_part( $module, $template, $slug = '', $params = array() ) {

        //HTML Content from template
        $html          = '';
        $template_path = WANDERLAND_CORE_ABS_PATH . '/' . $module . '/templates';

        $temp = $template_path . '/' . $template;

        if ( is_array( $params ) && count( $params ) ) {
            extract( $params );
        }

        $template = '';

        if ( ! empty( $temp ) ) {
            if ( ! empty( $slug ) ) {
                $template = "{$temp}-{$slug}.php";

                if ( ! file_exists( $template ) ) {
                    $template = $temp . '.php';
                }
            } else {
                $template = $temp . '.php';
            }
        }

        if ( $template ) {
            ob_start();
            include( $template );
            $html = ob_get_clean();
        }

        return $html;
    }
}

if ( ! function_exists( 'wanderland_core_get_module_shortcode_template_part' ) ) {
    /**
     * Loads module template part.
     *
     * @param string $module name of the module to load
     * @param string $shortcode name of the shortcode to load
     * @param string $template name of the template file
     * @param string $slug
     * @param array $params array of parameters to pass to template
     *
     * @return html
     */
    function wanderland_core_get_module_shortcode_template_part( $module, $shortcode, $template, $slug = '', $params = array() ) {

        //HTML Content from template
        $html          = '';
        $template_path = WANDERLAND_CORE_ABS_PATH . '/' . $module . '/shortcodes/' . $shortcode . '/templates';

        $temp = $template_path . '/' . $template;

        if ( is_array( $params ) && count( $params ) ) {
            extract( $params );
        }

        $template = '';

        if ( ! empty( $temp ) ) {
            if ( ! empty( $slug ) ) {
                $template = "{$temp}-{$slug}.php";

                if ( ! file_exists( $template ) ) {
                    $template = $temp . '.php';
                }
            } else {
                $template = $temp . '.php';
            }
        }

        if ( $template ) {
            ob_start();
            include( $template );
            $html = ob_get_clean();
        }

        return $html;
    }
}

if ( ! function_exists( 'wanderland_core_ajax_status' ) ) {
	/**
	 * Function that return status from ajax functions
	 */
	function wanderland_core_ajax_status( $status, $message, $data = null ) {
		$response = array(
			'status'  => $status,
			'message' => $message,
			'data'    => $data
		);
		
		$output = json_encode( $response );
		
		exit( $output );
	}
}

if ( ! function_exists( 'wanderland_core_add_user_custom_fields' ) ) {
	/**
	 * Function creates custom social fields for users
	 *
	 * return $user_contact
	 */
	function wanderland_core_add_user_custom_fields( $user_contact ) {
		/**
		 * Function that add custom user fields
		 **/
		$user_contact['facebook']   = esc_html__( 'Facebook', 'wanderland-core' );
		$user_contact['twitter']    = esc_html__( 'Twitter', 'wanderland-core' );
		$user_contact['linkedin']   = esc_html__( 'Linkedin', 'wanderland-core' );
		$user_contact['instagram']  = esc_html__( 'Instagram', 'wanderland-core' );
		$user_contact['pinterest']  = esc_html__( 'Pinterest', 'wanderland-core' );
		$user_contact['tumblr']     = esc_html__( 'Tumbrl', 'wanderland-core' );
		
		return $user_contact;
	}
	
	add_filter( 'user_contactmethods', 'wanderland_core_add_user_custom_fields' );
}

if ( ! function_exists( 'wanderland_core_set_open_graph_meta' ) ) {
	/*
	 * Function that echoes open graph meta tags if enabled
	 */
	function wanderland_core_set_open_graph_meta() {
		
		if ( wanderland_mikado_option_get_value( 'enable_open_graph' ) === 'yes' ) {
			
			// get the id
			$id = get_queried_object_id();
			
			// default type is article, override it with product if page is woo single product
			$type        = 'article';
			$description = '';
			
			// check if page is generic wp page w/o page id
			if ( wanderland_mikado_is_default_wp_template() ) {
				$id = 0;
			}
			
			// check if page is woocommerce shop page
			if ( wanderland_mikado_is_plugin_installed( 'woocommerce' ) && ( function_exists( 'is_shop' ) && is_shop() ) ) {
				$shop_page_id = get_option( 'woocommerce_shop_page_id' );
				
				if ( ! empty( $shop_page_id ) ) {
					$id = $shop_page_id;
					// set flag
					$description = 'woocommerce-shop';
				}
			}
			
			if ( function_exists( 'is_product' ) && is_product() ) {
				$type = 'product';
			}
			
			// if id exist use wp template tags
			if ( ! empty( $id ) && !is_front_page()  ) {
				$url   = get_permalink( $id );
				$title = get_the_title( $id );

				// apply bloginfo description to woocommerce shop page instead of first product item description
				if ( $description === 'woocommerce-shop' ) {
					$description = get_bloginfo( 'description' );
				} elseif (get_post_field( 'post_excerpt', $id ) !== '') {
					$description = strip_tags( apply_filters( 'the_excerpt', get_post_field( 'post_excerpt', $id ) ) );
				} else {
					$description = get_bloginfo( 'description' );
				}
				
				// has featured image
				if ( get_post_thumbnail_id( $id ) !== '' ) {
					$image = wp_get_attachment_url( get_post_thumbnail_id( $id ) );
				} else {
					$image = wanderland_mikado_option_get_value( 'open_graph_image' );
				}
			} else {
				global $wp;
				$url         = esc_url( home_url( add_query_arg( array(), $wp->request ) ) );
				$title       = get_bloginfo( 'name' );
				$description = get_bloginfo( 'description' );
				$image       = wanderland_mikado_option_get_value( 'open_graph_image' );
			}
			?>
			
			<meta property="og:url" content="<?php echo esc_url( $url ); ?>"/>
			<meta property="og:type" content="<?php echo esc_html( $type ); ?>"/>
			<meta property="og:title" content="<?php echo esc_html( $title ); ?>"/>
			<meta property="og:description" content="<?php echo esc_html( $description ); ?>"/>
			<meta property="og:image" content="<?php echo esc_url( $image ); ?>"/>
		
		<?php }
	}
	
	add_action( 'wanderland_mikado_action_header_meta', 'wanderland_core_set_open_graph_meta' );
}

/* Function for adding custom meta boxes hooked to default action */
if ( class_exists( 'WP_Block_Type' ) && defined( 'WANDERLAND_MIKADO_ROOT' ) ) {
	add_action( 'admin_head', 'wanderland_mikado_meta_box_add' );
} else {
	add_action( 'add_meta_boxes', 'wanderland_mikado_meta_box_add' );
}

if ( ! function_exists( 'wanderland_mikado_create_meta_box_handler' ) ) {
	function wanderland_mikado_create_meta_box_handler( $box, $key, $screen ) {
		add_meta_box(
			'mkdf-meta-box-' . $key,
			$box->title,
			'wanderland_mikado_render_meta_box',
			$screen,
			'advanced',
			'high',
			array( 'box' => $box )
		);
	}
}

if ( ! function_exists( 'wanderland_mikado_str_split_unicode' ) ) {
	function wanderland_mikado_str_split_unicode( $str ) {
		return preg_split( '~~u', $str, - 1, PREG_SPLIT_NO_EMPTY );
	}
}

if ( ! function_exists( 'wanderland_mikado_get_split_text' ) ) {
	function wanderland_mikado_get_split_text( $text ) {
		if ( ! empty( $text ) ) {
			$split_text = wanderland_mikado_str_split_unicode( $text );
			
			foreach ( $split_text as $key => $value ) {
				$split_text[ $key ] = '<span class="edgtf-s-character">' . $value . '</span>';
			}
			
			return implode( ' ', $split_text );
		}
		
		return $text;
	}
}

if ( ! function_exists( 'wanderland_core_get_cpt_items' ) ) {
	function wanderland_core_get_cpt_items( $cpt_slug, $args = array(), $first_empty = false ) {
		$items      = array();
		$query_args = array(
			'post_status'    => 'publish',
			'post_type'      => $cpt_slug,
			'posts_per_page' => '-1',
			'fields'         => 'ids'
		);
		
		if ( ! empty( $args ) ) {
			foreach ( $args as $key => $value ) {
				if ( ! empty( $value ) ) {
					$query_args[ $key ] = $value;
				}
			}
		}
		
		$cpt_items = new \WP_Query( $query_args );
		
		if ( $cpt_items->have_posts() ) {
			
			if ( $first_empty ) {
				$items[''] = esc_html__( 'Default', 'wanderland-core' );
			}
			
			foreach ( $cpt_items->posts as $id ):
				$items[ $id ] = get_the_title( $id );
			endforeach;
		}
		
		wp_reset_postdata();
		
		return $items;
	}
}