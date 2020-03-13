<?php

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'parent-theme-style', get_template_directory_uri() . '/style.css' );
} );

/**
* Функция загружает часть шаблона, передавая в него переменные
* 
* @param string $_template_name имя файла шаблона
* @param array $_varibles ассоциативный массив, ключи которого станут переменными в подключаемом шаблоне
* @return void
*/
if( ! function_exists( 'jwp_get_template_part' ) ) {
	function jwp_get_template_part( $_template_name, array $_varibles = array() ) {
		if( ! $_template_name ) {
			return false;
		}
		$_template_file = get_stylesheet_directory() . '/' . $_template_name . '.php';
		if ( file_exists( $_template_file ) ) {
			if ( $_varibles ) {
				extract( $_varibles, EXTR_SKIP );
			}
			require( $_template_file );
		}
	}
}


include_once get_stylesheet_directory() . '/includes/class-jwp-units.php';
include_once get_stylesheet_directory() . '/includes/class-jwp-unit.php';
\JWP\Units::instance();
