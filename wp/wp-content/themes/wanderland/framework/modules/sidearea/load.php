<?php

include_once WANDERLAND_MIKADO_FRAMEWORK_MODULES_ROOT_DIR . '/sidearea/functions.php';

//load global side area options
include_once WANDERLAND_MIKADO_FRAMEWORK_MODULES_ROOT_DIR . '/sidearea/admin/options-map/sidearea-map.php';

//load global side area custom styles
include_once WANDERLAND_MIKADO_FRAMEWORK_MODULES_ROOT_DIR . '/sidearea/admin/custom-styles/sidearea-custom-styles.php';

//load widgets
foreach ( glob( WANDERLAND_MIKADO_FRAMEWORK_MODULES_ROOT_DIR . '/sidearea/widgets/*/load.php' ) as $widget_load ) {
    include_once $widget_load;
}