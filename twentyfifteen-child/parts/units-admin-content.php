<?php

$units   = JWP\Units::instance();
$action  = isset( $_GET['act'] ) ? $_GET['act'] : $units->get_default_action();
$unit_id = isset( $_GET['unit_id'] ) ? intval( $_GET['unit_id'] ) : 0;

if ( ! in_array( $action, $units->get_actions() ) ) {
	$action = $units->get_default_action();
}

if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
	get_template_part( 'parts/units/forbidden' );
	return;
}

// загружаем скрипты только на нужной странице
add_action( 'wp_footer', function() {
	$units   = JWP\Units::instance();
	$api_key = $units->get_yandex_map_api_key();
	wp_enqueue_script( 'yamap-api', 'https://api-maps.yandex.ru/2.1/?lang=ru_RU&amp;apikey=' . $api_key, array(), '2.1', true );
	wp_enqueue_script( 'unit-front-admin', get_stylesheet_directory_uri() . '/assets/js/unit-frontend-admin.js', array( 'yamap-api' ), '1.0.0', true );
});

$template_part = 'list-table';
$params = array();
if ( $units::ACTION_EDIT == $action || $units::ACTION_NEW == $action ) {
	$template_part = 'form';
	$params['unit'] = new JWP\Unit( $unit_id );
}

if ( $units::ACTION_LIST == $action ) {
	// тут конечно-же нужна пагинация. Но для теста не стал её делать.
	$params['units'] = $units->get_all();
}

jwp_get_template_part( "parts/units/{$template_part}", $params );


