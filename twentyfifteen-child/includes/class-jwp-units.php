<?php
namespace JWP;

/**
 * Класс для работы с типом записей Юнит
 * Тут уже не буду тратить время на комментарии. Как я умею комментировать смотри в классе JWP\Unit
 */
class Units {
	
	const POST_TYPE      = 'unit';
	
	const ACTION_LIST    = 'list';
	
	const ACTION_EDIT    = 'edit';
	
	const ACTION_NEW     = 'new';
	
	const ACTION_DELETE  = 'delete';
	
	private static $_instance = null;
	
	private $actions = array();
	
	private $default_action = null;
	
	private $yandex_map_api_key = '';
	
	private function __construct() {
		$this->init();
	}
	
	protected function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Something went wrong.', 'jwp_test' ), '1.0.0' );
	}
	
	protected function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Something went wrong.', 'jwp_test' ), '1.0.0' );
	}
	
	static public function instance() {
		if( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	private function init() {
		$this->register_post_type();
		
		$this->actions = array(
			self::ACTION_LIST, 
			self::ACTION_EDIT,
			self::ACTION_NEW,
			self::ACTION_DELETE
		);
		
		$this->default_action = self::ACTION_LIST;
		
		if ( \wp_doing_ajax() && \is_user_logged_in() && \current_user_can( 'manage_options' ) ) {
			\add_action( 'wp_ajax_jwp_unit_edit', array( $this, 'ajax_edit_unit_handler' ) );
			\add_action( 'wp_ajax_jwp_unit_delete', array( $this, 'ajax_delete_unit_handler' ) );
		}
		
		// Тут конечно нужно сделать через настройки темы.
		if ( defined( 'JWP_YANDEX_MAP_API_KEY' ) ) {
			$this->yandex_map_api_key = JWP_YANDEX_MAP_API_KEY; // константу с ключём задаём у себя в wp-config.php чтобы не палить в git
		}
		
		add_shortcode( 'yandex_map', array( $this, 'map_shortcode' ) );
	}
	
	public function get_default_action() {
		return $this->default_action;
	}
	
	public function get_yandex_map_api_key() {
		return $this->yandex_map_api_key;
	}
	
	public function get_actions() {
		return $this->actions;
	}
	
	public function get_all() {
		$posts = get_posts( array(
			'post_type'   => self::POST_TYPE, 
			'numberposts' => -1
		) );
		
		$units = array();
		foreach ( $posts as $post ) {
			$unit = new Unit;
			$unit->configure_from_wp_post( $post );
			$units[] = $unit;
		}
		
		return $units;
	}
	
	public function ajax_edit_unit_handler() {
		$unit = new Unit;
		$unit->configure_from_array( $_POST, 'unit_' );
		if ( ! $unit->verify_nonce( $_POST['unit_nonce'] ) ) {
			wp_send_json_error();
		}
		$is_new = false;
		if ( ! $unit->get_id() ) {
			$is_new = true;
		}
		$result = $unit->save();
		
		if ( \is_wp_error( $result ) ) {
			\wp_send_json_error( array( 
				'error_messages' => $result->get_error_messages() 
			) );
		}
		$success_data = array( 
			'unit_id' => $unit->get_id()
		);
		// когда запись создана - нужно пересоздать и передать во фронт новый nonce
		if ( $is_new ) {
			$success_data['unit_nonce'] = $unit->create_nonce();
		}
		wp_send_json_success( $success_data );
	}
	
	public function ajax_delete_unit_handler() {
		$unit = new Unit;
		$unit->configure_from_array( $_POST, 'unit_' );
		if ( ! $unit->verify_nonce( $_POST['unit_nonce'] ) ) {
			wp_send_json_error();
		}
		$unit->delete();
		
		wp_send_json_success();
	}
	
	public function map_shortcode() {
		global $post;
		if ( ! $post ) {
			return '';
		}
		// ищем шорткод в контенте записи
		$shortcode_pos = mb_strpos( $post->post_content, '[yandex_map]' );
		if ( false === $shortcode_pos ) {
			return '';
		}
		// грузим скрипты только когда шорткод есть в записи
		add_action( 'wp_footer', function() {
			$api_key = $this->get_yandex_map_api_key();
			wp_enqueue_script( 'yamap-api', 'https://api-maps.yandex.ru/2.1/?lang=ru_RU&amp;apikey=' . $api_key, array(), '2.1', true );
			wp_enqueue_script( 'yamap-script', get_stylesheet_directory_uri() . '/assets/js/unit-shortcode.js', array( 'yamap-api' ), '1.0.0', true );
		});
		$unit = new Unit;
		$unit->configure_from_wp_post( $post );
		ob_start();
		jwp_get_template_part( 'parts/units/shortcode', array( 'unit' => $unit ) );
		return ob_get_clean();
	}
	
	private function register_post_type() {
		add_action( 'init', function() {
			$labels = array(
				'name'           => _x( 'Юниты', 'Post Type General Name', 'jwp_test' ),
				'singular_name'  => _x( 'Юнит', 'Post Type Singular Name', 'jwp_test' ),
				'menu_name'      => __( 'Юниты', 'jwp_test' ),
				'name_admin_bar' => __( 'Юнит', 'jwp_test' ),
				'all_items'      => __( 'Все юниты', 'jwp_test' ),
				'add_new_item'   => __( 'Добавить новый юнит', 'jwp_test' ),
				'add_new'        => __( 'Добавить новый', 'jwp_test' ),
				'new_item'       => __( 'Новый юнит', 'jwp_test' ),
				'edit_item'      => __( 'Редактировать юнит', 'jwp_test' ),
				'update_item'    => __( 'Обновить юнит', 'jwp_test' ),
				'view_item'      => __( 'Смотреть юнит', 'jwp_test' ),
			);
			$args = array(
				'label'               => __( 'Юнит', 'jwp_test' ),
				'description'         => __( 'Post Type Description', 'jwp_test' ),
				'labels'              => $labels,
				'supports'            => array( 'title', 'editor' ),
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'menu_position'       => 5,
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => true,
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'capability_type'     => 'page',
			);
			\register_post_type( self::POST_TYPE, $args );
		} );
	}
}
