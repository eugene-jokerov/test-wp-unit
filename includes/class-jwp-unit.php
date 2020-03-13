<?php
namespace JWP;

/**
 * Класс для работы с экземпляром Юнит
 */
class Unit {
	
	/** 
     * @var int номер записи
     */
	protected $id = 0;
	
	/** 
     * @var string название записи
     */
	protected $title = '';
	
	/** 
     * @var string контент записи
     */
	protected $content = '';
	
	/** 
     * @var string|null координата Широта
     */
	protected $lat = '';
	
	/** 
     * @var string|null координата Долгота
     */
	protected $lng = '';
	
	/** 
     * @var string проверочный ключ
     */
	protected $_nonce = '';
	
	/** 
     * @var array поля
     */
	protected $_fields = array();
	
	/**
	 * Конструктор объекта юнит
	 *
	 * @param  int $id номер записи
	 */
	public function __construct( $id = 0 ) {
		if ( $id ) {
			$this->set_id( $id );
			$this->load();
		}

		$this->_fields = array(
			'title', 'content', 'lat', 'lng',
		);
	}
	
	/**
	 * Загружает параметры юнита из базы данных
	 */
	public function load() {
		$post = \get_post( $this->id );
		
		if ( ! $post ) {
			$this->id = 0;
			return;
		} 
		
		$this->title   = $post->post_title;
		$this->content = $post->post_content;
		$this->lat     = $this->load_lat();
		$this->lng     = $this->load_lng();
	}
	
	/**
	 * Сохраняет юнит в базе данных
	 */
	public function save() {
		$is_valid = $this->is_valid();
		if ( \is_wp_error( $is_valid ) ) {
			return $is_valid;
		}
		if ( $this->id ) {
			return $this->update();
		} else {
			return $this->insert();
		}
	}
	
	/**
	 * Проверяет корректность полей
	 * 
	 * @return bool|WP_Error
	 */
	public function is_valid() {
		$errors = new \WP_Error;
		
		if ( ! $this->get_title() ) {
			$errors->add( 'no_title', 'Введите название юнита' );
		}
		
		$lat_pattern = "/^([-+]?)([\d]{1,2})((\.)[\d]{1,10})?$/i";
		$lng_pattern = "/^([-+]?)([\d]{1,3})((\.)[\d]{1,10})?$/i";
		
		// валидация самая простая, без проверки диапазонов значений lat и lng
		if ( $this->get_lng() && $this->get_lat() ) {
			if ( ! preg_match( $lat_pattern, $this->get_lat() ) ) {
				$errors->add( 'lat_error', 'Неверное значение поля Широта' );
			}
			
			if ( ! preg_match( $lng_pattern, $this->get_lng() ) ) {
				$errors->add( 'lat_error', 'Неверное значение поля Долгота' );
			}
		}
		/*
		 * Дальше нужно проверить ситуацию, когда одна координата установлена, а другая нет.
		 * В один if выглядит не понятно, поэтому жертвуем ресурсами ради большей читабельности кода
		 * if ( ( ! $this->get_lng() && $this->get_lat() ) || ( $this->get_lng() && ! $this->get_lat() ) )
		 */ 
		
		$coords_error = false;
		if ( ! $this->get_lng() && $this->get_lat() ) {
			$coords_error = true;
		}
		
		if ( $this->get_lng() && ! $this->get_lat() ) {
			$coords_error = true;
		}
		
		if ( $coords_error ) {
			$errors->add( 'coords_error', 'Необходимо задать обе координаты' );
		}
		
		if ( $errors->get_error_codes() ) {
			return $errors;
		}
		
		return true;
	}
	
	/**
	 * Обновляет юнит
	 * 
	 * @return int номер юнита
	 */
	protected function update() {
		$post_data = array(
			'ID'           => $this->id,
			'post_title'   => \wp_strip_all_tags( $this->get_title() ),
			'post_content' => $this->get_content(),
			'post_type'    => 'unit', 
		);
		
		$post_id = \wp_update_post( $post_data, true );
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}
		
		\update_post_meta( $this->get_id(), 'jwp_lat', $this->get_lat() );
		\update_post_meta( $this->get_id(), 'jwp_lng', $this->get_lng() );
		
		return $post_id;
	}
	
	/**
	 * Добавляет новый юнит
	 * 
	 * @return int номер нового юнита
	 */
	protected function insert() {
		$post_data = array(
			'post_title'   => \wp_strip_all_tags( $this->get_title() ),
			'post_content' => $this->get_content(),
			'post_type'    => Units::POST_TYPE,
			'post_status'  => 'publish',
		);

		$post_id = \wp_insert_post( $post_data, true );
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}
		$this->id = $post_id;
		
		\update_post_meta( $this->get_id(), 'jwp_lat', $this->get_lat() );
		\update_post_meta( $this->get_id(), 'jwp_lng', $this->get_lng() );
		
		return $post_id;
	}
	
	/**
	 * Удаляет юнит из базы данных
	 * 
	 * @return void
	 */
	public function delete() {
		\wp_delete_post( $this->get_id(), true );
	}
	
	/**
	 * Устанавливает свойство юнита путём вызова соответствующего сеттера
	 * 
	 * @param  string $field поле
	 * 
	 * @param  mixed $value значение поля
	 * 
	 * @return void
	 */
	protected function set( $field = '', $value = '' ) {
		$method = 'set_' . $field;
		if ( \method_exists( $this, $method ) ) {
			$this->$method( $value );
		}
	}
	
	/**
	 * Устанавливает свойства юнита из переданного массива
	 * 
	 * @param  array $data массив со свойствами
	 * 
	 * @param  string $prefix префикс для свойств. Например: в $_POST unit_title, в объекте просто title.
	 * 
	 * @return void
	 */
	public function configure_from_array( $data = array(), $prefix = '' ) {
		// если передано поле id, то пытаемся загрузить юнит
		$id_key = $prefix . 'id';
		if ( isset( $data[ $id_key ] ) ) {
			$this->id = intval( $data[ $id_key ] );
			$this->load();
		}
		
		//устанавливаем остальные поля
		foreach ( $this->_fields as $field ) {
			$key = $prefix . $field;
			if ( isset( $data[ $key ] ) ) {
				$this->set( $field, $data[ $key ] );
			}
		}
	}
	
	/**
	 * Устанавливает свойства юнита из переданного массива
	 * 
	 * @param  WP_Post $wp_post объект wordpress поста
	 * 
	 * @param  array мета данные
	 * 
	 * @return bool
	 */
	public function configure_from_wp_post( $wp_post, $meta = array() ) {
		if ( ! $wp_post || ! ( $wp_post instanceof \WP_Post ) ) {
			return false;
		}
		$this->set_id( $wp_post->ID );
		$this->set_title( $wp_post->post_title );
		$this->set_content( $wp_post->post_content );
		
		$lat = isset( $meta['lat'] ) ? $meta['lat'] : null;
		$lng = isset( $meta['lng'] ) ? $meta['lng'] : null;
		$this->set_lat( $lat );
		$this->set_lng( $lng );
		return true;
	}
	
	/**
	 * Вовзращает проверочный ключ
	 * 
	 * @return string
	 */
	public function get_nonce() {
		if ( ! $this->_nonce ) {
			$this->create_nonce();
		}
		
		return $this->_nonce;
	}
	
	/**
	 * Создаёт проверочный ключ
	 * 
	 * @return void
	 */
	public function create_nonce() {
		$this->_nonce = \wp_create_nonce( 'unit_' . $this->get_id() );
		return $this->_nonce;
	}
	
	/**
	 * Проверяет соответствие проверочного ключа
	 * 
	 * @return bool
	 */
	public function verify_nonce( $nonce = '' ) {
		if ( \wp_verify_nonce( $nonce, 'unit_' . $this->get_id() ) ) {
			return true;
		}
		return false;
	}
	
	protected function set_id( $id = 0 ) {
		$this->id = $id;
	}
	
	public function get_id() {
		return $this->id;
	}
	
	public function get_title() {
		return $this->title;
	}
	
	public function set_title( $title = '' ) {
		$this->title = \sanitize_text_field( $title );
	}
	
	public function get_content() {
		return $this->content;
	}
	
	public function set_content( $content = '' ) {
		$this->content = \wp_kses_post( $content );
	}
	
	/**
	 * Возвращает координату Широта. Если значение null - загружает из базы данных
	 * Получается что-то типа ленивой загрузки. Экономит sql запросы.
	 * 
	 * @return void
	 */
	public function get_lat() {
		if ( is_null( $this->lat ) ) {
			$this->load_lat();
		}
		return $this->lat;
	}
	
	public function set_lat( $lat = '' ) {
		$this->lat = $lat;
	}
	
	public function get_lng() {
		if ( is_null( $this->lng ) ) {
			$this->load_lng();
		}
		return $this->lng;
	}
	
	public function set_lng( $lng = '' ) {
		$this->lng = $lng;
	}
	
	/**
	 * Загружает координату Долгота из базы данных
	 * 
	 * @return void
	 */
	protected function load_lng() {
		$this->lng = \get_post_meta( $this->get_id(), 'jwp_lng', true );
	}
	
	/**
	 * Загружает координату Широта из базы данных
	 * 
	 * @return void
	 */
	protected function load_lat() {
		$this->lat = \get_post_meta( $this->get_id(), 'jwp_lat', true );
	}
}
