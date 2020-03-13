jQuery(function($){
	if ( ! $('#yandex-map-front').length ) {
		return false;
	}
	var lat = $('#yandex-map-front').attr('data-lat');
	var lng = $('#yandex-map-front').attr('data-lng');
	if ( ! lat || ! lng ) {
		return false;
	}
	ymaps.ready( function() {
		var map = new ymaps.Map('yandex-map-front', {
			center: [lat, lng], // Москва
			zoom: 10
		}, {
			searchControlProvider: 'yandex#search'
		});
		
		var placemark = new ymaps.Placemark([lat, lng], {
			balloonContent: 'Юнит'
		}, {
			preset: 'islands#icon',
			iconColor: '#0095b6',
		});
		
		map.geoObjects.add( placemark );
	});
});
