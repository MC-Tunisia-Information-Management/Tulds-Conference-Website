<?php
$product = wanderland_mikado_return_woocommerce_global_variable();

if ($display_price === 'yes' && $price_html = $product->get_price_html()) { ?>
	<div class="mkdf-<?php echo esc_attr($class_name); ?>-price"><?php echo wp_kses_post($price_html); ?></div>
<?php } ?>