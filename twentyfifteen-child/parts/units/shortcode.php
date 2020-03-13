<?php 
if ( ! $unit->get_lat() || ! $unit->get_lng() ) {
	return;
}
?>
<div id="yandex-map-front" data-lat="<?php echo esc_attr( $unit->get_lat() ); ?>" data-lng="<?php echo esc_attr( $unit->get_lng() ); ?>"></div>
