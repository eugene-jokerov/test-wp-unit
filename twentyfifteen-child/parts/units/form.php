<form id="jwp-unit-form" data-ajaxurl="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
	<label>Название</label>
	<input type="text" value="<?php echo esc_attr( $unit->get_title() ); ?>" name="unit_title">
	
	<label>Контент</label>
	<?php wp_editor( $unit->get_content(), 'unit_content', array(
		'textarea_rows' => 7
	) ); ?>
	
	<label>Юнит на карте</label>
	<div id="yandex-map"></div>
	<div class="jwp-row">
		<div class="jwp-column">
			<label>Широта</label>
			<input type="text" id="jwp-coord-lat" name="unit_lat" value="<?php echo esc_attr( $unit->get_lat() ); ?>">
		</div>
		<div class="jwp-column">
			<label>Долгота</label>
			<input type="text" id="jwp-coord-lng" name="unit_lng" value="<?php echo esc_attr( $unit->get_lng() ); ?>">
		</div>
	</div>
	
	<input type="hidden" name="unit_id" id="jwp-unit-id" value="<?php echo esc_attr( $unit->get_id() ); ?>">
	<input type="hidden" name="unit_nonce" id="jwp-unit-nonce" value="<?php echo esc_attr( $unit->get_nonce() ); ?>">
	<input type="hidden" name="action" value="jwp_unit_edit">
	<button type="submit" id="jwp-unit-submit-btn">Сохранить</button>
	<p class="jwp-unit-form-msg jwp-hide"></p>
</form>

