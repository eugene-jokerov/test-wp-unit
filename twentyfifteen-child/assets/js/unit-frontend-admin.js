jQuery(function($){
	ymaps.ready( function() {
		if ( ! $('#yandex-map').length ) {
			return false;
		}
		var map = new ymaps.Map( 'yandex-map', {
			center: [55.76, 37.64], // Москва
			zoom: 10
		}, {
			searchControlProvider: 'yandex#search'
		} );
		
		var placemark = new ymaps.Placemark( [55.684758, 37.738521], {
			balloonContent: 'Юнит'
		}, {
			preset: 'islands#icon',
			iconColor: '#0095b6',
			visible: false, // точка изначально скрыта. Как только юзер кликнет по карте, она появится в нужном месте.
		} );
		
		if ( $('#jwp-coord-lat').val() && $('#jwp-coord-lng').val() ) {
			placemark.options.set( 'visible', true );
			var point_coords = [ $('#jwp-coord-lat').val(), $('#jwp-coord-lng').val() ];
			placemark.geometry.setCoordinates( point_coords );
			map.setCenter( point_coords, 10, {
				checkZoomRange: true
			} );
		}
		
		map.geoObjects.add( placemark );
		
		map.events.add( 'click', function (e) {
			if ( map.balloon.isOpen() ) {
				map.balloon.close();
			}
			var coords = e.get('coords');
			if ( false == placemark.options.get( 'visible' ) ) {
				placemark.options.set( 'visible', true );
			}
			placemark.geometry.setCoordinates( coords );
			$('#jwp-coord-lat').val( coords[0].toPrecision(10) );
			$('#jwp-coord-lng').val( coords[1].toPrecision(10) );
			map.balloon.open( coords, {
				contentHeader:'Координаты обновлены',
			} );
			
			setTimeout( function() {
				map.balloon.close();
			}, 500 );
		});
	} );
	
	$('#jwp-unit-form').on('submit', function() {
		$('#jwp-unit-submit-btn').hide();
		var msg_block = $('.jwp-unit-form-msg');
		if ( ! msg_block.hasClass('jwp-hide') ) {
			msg_block.addClass( 'jwp-hide' );
		}
		msg_block.removeClass( 'jwp-msg-success' ).removeClass( 'jwp-msg-error' );
		msg_block.html( '' );
		$('#unit_content').val( wp.editor.getContent( 'unit_content' ) ); // иначе пустой контент будет
		$.post( $('#jwp-unit-form').attr( 'data-ajaxurl' ), $(this).serialize(), function( responce ) {
			msg_block.removeClass( 'jwp-hide' );
			$('#jwp-unit-submit-btn').show();
			if ( responce.success ) {
				msg_block.addClass( 'jwp-msg-success' );
				if ( ! parseInt( $('#jwp-unit-id').val() ) ) {
					$('#jwp-unit-id').val( responce.data.unit_id );
				}
				if ( responce.data.unit_nonce ) {
					$('#jwp-unit-nonce').val( responce.data.unit_nonce );
				}
				msg_block.text( 'Изменения сохранены.' );
			} else {
				msg_block.addClass( 'jwp-msg-error' );
				var error_list = $("<ul></ul>");
				$.each( responce.data.error_messages, function( index, value ) {
					error_list.append( '<li>'+value+'</li>' );
				} );
				msg_block.append( error_list );
			}
		});
		return false;
	} );
	
	$('.jwp-delete-unit').on( 'click', function() {
		if ( ! confirm( 'Точно удаляем?' ) ) {
			return false;
		}
		
		var self = $(this);

		var post_data = {
			'action'     : 'jwp_unit_delete',
			'unit_id'    : $(this).attr( 'data-id' ),
			'unit_nonce' : $(this).attr( 'data-nonce' ),
		}
		$.post( $('.jwp-delete-unit').closest( 'table' ).attr( 'data-ajaxurl' ), post_data, function( responce ) {
			var tr = self.closest( 'tr' );
			tr.css( 'background-color', '#e82323' );
			setTimeout( function() {
				tr.remove();
			}, 300 );
		} );
		return false;
	} );
	
});
