<?php $icon_html = wanderland_mikado_icon_collections()->renderIcon($icon, $icon_pack, $params); ?>
<div class="mkdf-icon-list-holder <?php echo esc_attr($holder_classes); ?>" <?php echo wanderland_mikado_get_inline_style($holder_styles); ?>>
	<?php if(!empty($link)) {?>
        <a href="<?php echo esc_url($link);?>" target="_blank">
    <?php }?>
    <div class="mkdf-il-icon-holder">
		<?php echo wp_kses_post($icon_html); ?>
	</div>
	<p class="mkdf-il-text" <?php echo wanderland_mikado_get_inline_style($title_styles); ?>><?php echo esc_html($title); ?></p>
	<?php if(!empty($link)) {?>
        </a>
    <?php }?>
</div>