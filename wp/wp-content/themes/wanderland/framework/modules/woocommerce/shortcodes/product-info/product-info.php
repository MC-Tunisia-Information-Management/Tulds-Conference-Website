<?php
namespace WanderlandCore\CPT\Shortcodes\ProductInfo;

use WanderlandCore\Lib;

class ProductInfo implements Lib\ShortcodeInterface {
	private $base;
	
	function __construct() {
		$this->base = 'mkdf_product_info';
		
		add_action('vc_before_init', array($this,'vcMap'));
		
		//Product id filter
		add_filter( 'vc_autocomplete_mkdf_product_info_product_id_callback', array( &$this, 'productIdAutocompleteSuggester', ), 10, 1 );
		
		//Product id render
		add_filter( 'vc_autocomplete_mkdf_product_info_product_id_render', array( &$this, 'productIdAutocompleteRender', ), 10, 1 );
	}
	
	public function getBase() {
		return $this->base;
	}
	
	public function vcMap() {
		if(function_exists('vc_map')) {
			vc_map(
				array(
					'name'                      => esc_html__( 'Product Info', 'wanderland' ),
					'base'                      => $this->getBase(),
					'category'                  => esc_html__( 'by WANDERLAND', 'wanderland' ),
					'icon'                      => 'icon-wpb-product-info extended-custom-icon',
					'allowed_container_element' => 'vc_row',
					'params'                    => array(
						array(
							'type'        => 'autocomplete',
							'param_name'  => 'product_id',
							'heading'     => esc_html__( 'Selected Product', 'wanderland' ),
							'settings'    => array(
								'sortable'      => true,
								'unique_values' => true
							),
							'admin_label' => true,
							'save_always' => true,
							'description' => esc_html__( 'If you left this field empty then product ID will be of the current page', 'wanderland' )
						),
						array(
							'type'        => 'dropdown',
							'param_name'  => 'product_info_type',
							'heading'     => esc_html__( 'Product Info Type', 'wanderland' ),
							'value'       => array(
								esc_html__( 'Title', 'wanderland' )          => 'title',
								esc_html__( 'Featured Image', 'wanderland' ) => 'featured_image',
								esc_html__( 'Category', 'wanderland' )       => 'category',
								esc_html__( 'Excerpt', 'wanderland' )        => 'excerpt',
								esc_html__( 'Price', 'wanderland' )          => 'price',
								esc_html__( 'Rating', 'wanderland' )         => 'rating',
								esc_html__( 'Add To Cart', 'wanderland' )    => 'add_to_cart',
								esc_html__( 'Tag', 'wanderland' )            => 'tag',
								esc_html__( 'Author', 'wanderland' )         => 'author',
								esc_html__( 'Date', 'wanderland' )           => 'date',
							),
							'admin_label' => true
						),
						array(
							'type'       => 'colorpicker',
							'param_name' => 'product_info_color',
							'heading'    => esc_html__( 'Product Info Color', 'wanderland' ),
							'dependency' => array( 'element' => 'product_info_type', 'value'   => array( 'title', 'category', 'excerpt', 'price', 'rating', 'tag', 'author', 'date' ) ),
							'group'      => esc_html__( 'Product Info Style', 'wanderland' )
						),
						array(
							'type'        => 'dropdown',
							'param_name'  => 'title_tag',
							'heading'     => esc_html__( 'Title Tag', 'wanderland' ),
							'value'       => array_flip( wanderland_mikado_get_title_tag( true, array( 'p' => 'p' ) ) ),
							'description' => esc_html__( 'Set title tag for product title element', 'wanderland' ),
							'dependency'  => array( 'element' => 'product_info_type', 'value' => array( 'title' ) ),
							'group'       => esc_html__( 'Product Info Style', 'wanderland' )
						),
						array(
							'type'       => 'dropdown',
							'param_name' => 'featured_image_size',
							'heading'    => esc_html__( 'Featured Image Proportions', 'wanderland' ),
							'value'      => array(
								esc_html__( 'Default', 'wanderland' )        => '',
								esc_html__( 'Original', 'wanderland' )       => 'full',
								esc_html__( 'Square', 'wanderland' )         => 'wanderland_mikado_image_square',
								esc_html__( 'Landscape', 'wanderland' )      => 'wanderland_mikado_image_landscape',
								esc_html__( 'Portrait', 'wanderland' )       => 'wanderland_mikado_image_portrait',
								esc_html__( 'Medium', 'wanderland' )         => 'medium',
								esc_html__( 'Large', 'wanderland' )          => 'large',
								esc_html__( 'Shop Single', 'wanderland' )    => 'woocommerce_single',
								esc_html__( 'Shop Thumbnail', 'wanderland' ) => 'woocommerce_thumbnail'
							),
							'dependency' => array( 'element' => 'product_info_type', 'value'   => array( 'featured_image' ) )
						),
						array(
							'type'        => 'dropdown',
							'param_name'  => 'category_tag',
							'heading'     => esc_html__( 'Category Tag', 'wanderland' ),
							'value'       => array_flip( wanderland_mikado_get_title_tag( true ) ),
							'description' => esc_html__( 'Set category tag for product category element', 'wanderland' ),
							'dependency'  => array( 'element' => 'product_info_type', 'value' => array( 'category' ) ),
							'group'       => esc_html__( 'Product Info Style', 'wanderland' )
						),
						array(
							'type'       => 'dropdown',
							'param_name' => 'add_to_cart_skin',
							'heading'    => esc_html__( 'Add To Cart Skin', 'wanderland' ),
							'value'      => array(
								esc_html__( 'Default', 'wanderland' ) => '',
								esc_html__( 'White', 'wanderland' )   => 'white',
								esc_html__( 'Dark', 'wanderland' )    => 'dark'
							),
							'dependency' => array( 'element' => 'product_info_type', 'value' => array( 'add_to_cart' ) )
						),
						array(
							'type'       => 'dropdown',
							'param_name' => 'add_to_cart_size',
							'heading'    => esc_html__( 'Add To Cart Size', 'wanderland' ),
							'value'      => array(
								esc_html__( 'Default', 'wanderland' ) => '',
								esc_html__( 'Small', 'wanderland' )   => 'small',
								esc_html__( 'Medium', 'wanderland' )  => 'medium',
								esc_html__( 'Large', 'wanderland' )   => 'large'
							),
							'dependency' => array( 'element' => 'product_info_type', 'value'   => array( 'wanderland' ) ),
							'group'      => esc_html__( 'Product Info Style', 'wanderland' )
						)
					)
				)
			);
		}
    }
	
    public function render($atts, $content = null) {
        $args = array(
	        'product_id'          => '',
	        'product_info_type'   => 'title',
	        'product_info_color'  => '',
	        'title_tag'           => 'h2',
	        'featured_image_size' => 'full',
	        'category_tag'        => '',
	        'add_to_cart_skin'    => '',
	        'add_to_cart_size'    => ''
        );
		$params = shortcode_atts($args, $atts);
		extract($params);

	    $params['product_id'] = !empty($params['product_id']) ? $params['product_id'] : get_the_ID();
	    $params['title_tag'] = !empty($params['title_tag']) ? $params['title_tag'] : $args['title_tag'];
	    $params['category_tag'] = !empty($params['category_tag']) ? $params['category_tag'] : $args['category_tag'];

	    $params['product_info_style'] = $this->getProductInfoStyle($params);
		    
		$html = '';
			$html .= '<div class="mkdf-product-info">';

	            switch ($product_info_type) {
		            case 'title':
			            $html .= $this->getItemTitleHtml($params);
			            break;
		            case 'featured_image':
			            $html .= $this->getItemFeaturedImageHtml($params);
			            break;
		            case 'category':
			            $html .= $this->getItemCategoryHtml($params);
			            break;
		            case 'excerpt':
			            $html .= $this->getItemExcerptHtml($params);
			            break;
		            case 'price':
			            $html .= $this->getItemPriceHtml($params);
			            break;
		            case 'rating':
			            $html .= $this->getItemRatingHtml($params);
			            break;
		            case 'add_to_cart':
			            $html .= $this->getItemAddToCartHtml($params);
			            break;
		            case 'tag':
			            $html .= $this->getItemTagHtml($params);
			            break;
		            case 'author':
			            $html .= $this->getItemAuthorHtml($params);
			            break;
		            case 'date':
			            $html .= $this->getItemDateHtml($params);
			            break;
		            default:
			            $html .= $this->getItemTitleHtml($params);
			            break;
	            }

			$html .= '</div>';

        return $html;
	}
	
	/**
	 * Return product info styles
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getProductInfoStyle( $params ) {
		$styles = array();
		
		if ( ! empty( $params['product_info_color'] ) ) {
			$styles[] = 'color: ' . $params['product_info_color'];
		}
		
		return $styles;
	}
	
	/**
	 * Generates product title html based on id
	 *
	 * @param $params
	 *
	 * @return html
	 */
	public function getItemTitleHtml( $params ) {
		$html               = '';
		$product_id         = $params['product_id'];
		$title              = get_the_title( $product_id );
		$title_tag          = $params['title_tag'];
		$product_info_style = $params['product_info_style'];
		
		if ( ! empty( $title ) ) {
			$html = '<' . esc_attr( $title_tag ) . ' itemprop="name" class="mkdf-pi-title entry-title" ' . wanderland_mikado_get_inline_style( $product_info_style ) . '>';
				$html .= '<a itemprop="url" href="' . esc_url( get_the_permalink( $product_id ) ) . '">' . esc_html( $title ) . '</a>';
			$html .= '</' . esc_attr( $title_tag ) . '>';
		}
		
		return $html;
	}
	
	/**
	 * Generates product featured image html based on id
	 *
	 * @param $params
	 *
	 * @return html
	 */
	public function getItemFeaturedImageHtml( $params ) {
		$html                = '';
		$product_id          = $params['product_id'];
		$featured_image_size = ! empty( $params['featured_image_size'] ) ? $params['featured_image_size'] : 'full';
		$featured_image      = get_the_post_thumbnail( $product_id, $featured_image_size );
		
		if ( ! empty( $featured_image ) ) {
			$html = '<a itemprop="url" class="mkdf-pi-image" href="' . esc_url( get_the_permalink( $product_id ) ) . '">' . $featured_image . '</a>';
		}
		
		return $html;
	}
	
	/**
	 * Generates product categories html based on id
	 *
	 * @param $params
	 *
	 * @return html
	 */
	public function getItemCategoryHtml( $params ) {
		$html               = '';
		$product_id         = $params['product_id'];
		$categories         = wp_get_post_terms( $product_id, 'product_cat' );
		$product_info_style = $params['product_info_style'];
		
		if ( ! empty( $categories ) ) {
			$html .= '<div class="mkdf-pi-category">';
				foreach ( $categories as $cat ) {
					if ( ! empty( $params['category_tag'] ) ) {
						$html .= '<' . esc_attr( $params['category_tag'] ) . ' ' . wanderland_mikado_get_inline_style( $product_info_style ) . '>';
						$html .= '<a itemprop="url" class="mkdf-pi-category-item" href="' . esc_url( get_term_link( $cat->term_id ) ) . '">' . esc_html( $cat->name ) . '</a>';
						$html .= '</' . esc_attr( $params['category_tag'] ) . '>';
					} else {
						$html .= '<a itemprop="url" class="mkdf-pi-category-item" href="' . esc_url( get_term_link( $cat->term_id ) ) . '" ' . wanderland_mikado_get_inline_style( $product_info_style ) . '>' . esc_html( $cat->name ) . '</a>';
					}
				}
			$html .= '</div>';
		}
		
		return $html;
	}
	
	/**
	 * Generates product excerpt html based on id
	 *
	 * @param $params
	 *
	 * @return html
	 */
	public function getItemExcerptHtml( $params ) {
		$html               = '';
		$product_id         = $params['product_id'];
		$excerpt            = get_the_excerpt( $product_id );
		$product_info_style = $params['product_info_style'];
		
		if ( ! empty( $excerpt ) ) {
			$html = '<div class="mkdf-pi-excerpt"><p itemprop="description" class="mkdf-pi-excerpt-item" ' . wanderland_mikado_get_inline_style( $product_info_style ) . '>' . esc_html( $excerpt ) . '</p></div>';
		}
		
		return $html;
	}
	
	/**
	 * Generates product price html based on id
	 *
	 * @param $params
	 *
	 * @return html
	 */
	public function getItemPriceHtml( $params ) {
		$html               = '';
		$product_id         = $params['product_id'];
		$product            = wc_get_product( $product_id );
		$product_info_style = $params['product_info_style'];
		
		if ( $price_html = $product->get_price_html() ) {
			$html = '<div class="mkdf-pi-price" ' . wanderland_mikado_get_inline_style( $product_info_style ) . '>' . $price_html . '</div>';
		}
		
		return $html;
	}
	
	/**
	 * Generates product rating html based on id
	 *
	 * @param $params
	 *
	 * @return html
	 */
	public function getItemRatingHtml( $params ) {
		$html               = '';
		$product_id         = $params['product_id'];
		$product            = wc_get_product( $product_id );
		$product_info_style = $params['product_info_style'];
		
		if ( get_option( 'woocommerce_enable_review_rating' ) !== 'no' ) {
			$average = $product->get_average_rating();
			
			$html = '<div class="mkdf-pi-rating" title="' . sprintf( esc_attr__( "Rated %s out of 5", "wanderland" ), $average ) . '" ' . wanderland_mikado_get_inline_style( $product_info_style ) . '><span style="width:' . ( ( $average / 5 ) * 100 ) . '%"></span></div>';
		}
		
		return $html;
	}
	
	/**
	 * Generates product add to cart html based on id
	 *
	 * @param $params
	 *
	 * @return html
	 */
	public function getItemAddToCartHtml( $params ) {
		$product_id = $params['product_id'];
		$product    = wc_get_product( $product_id );
		
		$button_classes = 'button add_to_cart_button ajax_add_to_cart mkdf-btn mkdf-btn-solid';
		
		if ( ! $product->is_in_stock() ) {
			$button_classes = 'button ajax_add_to_cart mkdf-btn mkdf-btn-solid mkdf-out-of-stock';
		} else if ( $product->get_type() === 'variable' ) {
			$button_classes = 'button product_type_variable add_to_cart_button mkdf-btn mkdf-btn-solid';
		} else if ( $product->get_type() === 'external' ) {
			$button_classes = 'button product_type_external mkdf-btn mkdf-btn-solid';
		}
		
		if ( ! empty( $params['add_to_cart_skin'] ) ) {
			$button_classes .= ' mkdf-' . $params['add_to_cart_skin'] . '-skin mkdf-btn-custom-hover-color mkdf-btn-custom-hover-bg mkdf-btn-custom-border-hover';
		}
		
		if ( ! empty( $params['add_to_cart_size'] ) ) {
			$button_classes .= ' mkdf-btn-' . $params['add_to_cart_size'];
		}
		
		$html = '<div class="mkdf-pi-add-to-cart">';
		$html .= apply_filters( 'wanderland_mikado_filter_product_info_add_to_cart_link',
			sprintf( '<a rel="nofollow" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="%s">%s</a>',
				esc_url( $product->add_to_cart_url() ),
				esc_attr( isset( $quantity ) ? $quantity : 1 ),
				esc_attr( $product_id ),
				esc_attr( $product->get_sku() ),
				esc_attr( $button_classes ),
				esc_html( $product->add_to_cart_text() )
			),
			$product );
		$html .= '</div>';
		
		return $html;
	}
	
	/**
	 * Generates product tags html based on id
	 *
	 * @param $params
	 *
	 * @return html
	 */
	public function getItemTagHtml( $params ) {
		$html               = '';
		$product_id         = $params['product_id'];
		$tags               = wp_get_post_terms( $product_id, 'product_tag' );
		$product_info_style = $params['product_info_style'];
		
		if ( ! empty( $tags ) ) {
			$html = '<div class="mkdf-pi-tag">';
				foreach ( $tags as $tag ) {
					$html .= '<a itemprop="url" class="mkdf-pi-tag-item" href="' . esc_url( get_term_link( $tag->term_id ) ) . '" ' . wanderland_mikado_get_inline_style( $product_info_style ) . '>' . esc_html( $tag->name ) . '</a>';
				}
			$html .= '</div>';
		}
		
		return $html;
	}
	
	/**
	 * Generates product authors html based on id
	 *
	 * @param $params
	 *
	 * @return html
	 */
	public function getItemAuthorHtml( $params ) {
		$html               = '';
		$product_id         = $params['product_id'];
		$product_post       = get_post( $product_id );
		$autor_id           = $product_post->post_author;
		$author             = get_the_author_meta( 'display_name', $autor_id );
		$product_info_style = $params['product_info_style'];
		
		if ( ! empty( $author ) ) {
			$html = '<div class="mkdf-pi-author" ' . wanderland_mikado_get_inline_style( $product_info_style ) . '>' . esc_html( $author ) . '</div>';
		}
		
		return $html;
	}
	
	/**
	 * Generates product date html based on id
	 *
	 * @param $params
	 *
	 * @return html
	 */
	public function getItemDateHtml( $params ) {
		$html               = '';
		$product_id         = $params['product_id'];
		$date               = get_the_time( get_option( 'date_format' ), $product_id );
		$product_info_style = $params['product_info_style'];
		
		if ( ! empty( $date ) ) {
			$html = '<div class="mkdf-pi-date" ' . wanderland_mikado_get_inline_style( $product_info_style ) . '>' . esc_html( $date ) . '</div>';
		}
		
		return $html;
	}

	/**
	 * Filter product by ID or Title
	 *
	 * @param $query
	 *
	 * @return array
	 */
	public function productIdAutocompleteSuggester( $query ) {
		global $wpdb;
		$product_id = (int) $query;
		$post_meta_infos = $wpdb->get_results( $wpdb->prepare( "SELECT ID AS id, post_title AS title
					FROM {$wpdb->posts} 
					WHERE post_type = 'product' AND ( ID = '%d' OR post_title LIKE '%%%s%%' )", $product_id > 0 ? $product_id : - 1, stripslashes( $query ), stripslashes( $query ) ), ARRAY_A );

		$results = array();
		if ( is_array( $post_meta_infos ) && ! empty( $post_meta_infos ) ) {
			foreach ( $post_meta_infos as $value ) {
				$data = array();
				$data['value'] = $value['id'];
				$data['label'] = esc_html__( 'Id', 'wanderland' ) . ': ' . $value['id'] . ( ( strlen( $value['title'] ) > 0 ) ? ' - ' . esc_html__( 'Title', 'wanderland' ) . ': ' . $value['title'] : '' );
				$results[] = $data;
			}
		}

		return $results;

	}

	/**
	 * Find product by id
	 * @since 4.4
	 *
	 * @param $query
	 *
	 * @return bool|array
	 */
	public function productIdAutocompleteRender( $query ) {
		$query = trim( $query['value'] ); // get value from requested
		if ( ! empty( $query ) ) {
			
			$product = get_post( (int) $query );
			if ( ! is_wp_error( $product ) ) {
				
				$product_id = $product->ID;
				$product_title = $product->post_title;
				
				$product_title_display = '';
				if ( ! empty( $product_title ) ) {
					$product_title_display = ' - ' . esc_html__( 'Title', 'wanderland' ) . ': ' . $product_title;
				}
				
				$product_id_display = esc_html__( 'Id', 'wanderland' ) . ': ' . $product_id;

				$data          = array();
				$data['value'] = $product_id;
				$data['label'] = $product_id_display . $product_title_display;

				return ! empty( $data ) ? $data : false;
			}

			return false;
		}

		return false;
	}
}