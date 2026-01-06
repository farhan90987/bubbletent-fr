var dhl_gmap_api_key = dhl.gmap_api_key;
var dhl_gmap_base_url = 'https://maps.googleapis.com/maps/api/geocode/json';

var dhl_parcel_modal;
var dhl_parcel_map = {};

(
	function( $ ) {

		locked = false;

		function decodeEntities( encodedString ) {

			var textArea = document.createElement( 'textarea' );
			textArea.innerHTML = encodedString;

			return textArea.value;
		}

		function onSearchLocationClick( event ) {
			if ( event ) {
				event.preventDefault();
			}

			var postcode = $( 'input[name="dhl-modal-postcode"]' ).val();
			var street = $( 'input[name="dhl-modal-address"]' ).val();
			var city = $( 'input[name="dhl-modal-city"]' ).val();
			var country = $( 'input[name="dhl-modal-country"]' ).val();

			if ( ( '' !== postcode ) && ( '' !== street ) && ( '' !== city ) ) {
				setGeocodedAddress( {
					postcode: postcode,
					street: street,
					city: city,
					country: country
				} );
			}
		}

		/**
		 * Attempt to get postal code from Google Geocoder JSON response.
		 *
		 * @param  {object} data [Google Geocode JSON response]
		 * @return {string|null} [Postal code]
		 */
		function getCoordinatesFromJson( data ) {
			var coords;

			try {
				coords = data.results[ 0 ].geometry.location;
			} catch ( ex ) {
			}

			return coords;
		}

		function setGeocodedAddress( address ) {
			if ( ! dhl_gmap_api_key ) {
				console.log( 'Postal code geocoder is missing Google Maps API key' );
				return;
			}

			// Load pickup points from API
			endpoint = '';
			dhl_map = $( '#dhl-parcel-modal' );
			if ( $( dhl_map ).hasClass( 'dhl_packstations' ) ) {
				endpoint = 'get_dhl_packstations_modal';
			} else {
				endpoint = 'get_dhl_parcels_modal';
			}

			$.ajax( {
				url: dhl.wc_ajax_url.toString().replace( '%%endpoint%%', endpoint ),
				dataType: 'json',
				method: 'POST',
				data: {
					action: endpoint,
					postcode: address.postcode,
					city: address.city,
					street: address.street,
					country: address.country
				},
				success: function( response ) {
					setTerminalMarkers( response );
				},
				error: function( xhr, options, error ) {
					console.error( 'DHL Parcel store: ' + error );
					console.error( xhr );
				},
				complete: function() {
					// Need delay to reattach AJAX interceptor
					setTimeout( function() {
						useAjaxInterceptor( true );
					}, 1000 );
				},
			} );

			// Build URL query
			var params = jQuery.param( {
				address: address.postcode + ' ' + address.city + ' ' + address.street,
				type: 'street_address',
				key: dhl_gmap_api_key,
			} );

			var url = dhl_gmap_base_url + '?' + params;

			// Send request to Google Geocoder
			$.getJSON( url, function( data ) {
				if ( data.status == 'OK' ) {
					var coords = getCoordinatesFromJson( data );
					if ( coords ) {
						dhl_parcel_map.my_location = coords;
						dhl_parcel_map.my_marker.setPosition( dhl_parcel_map.my_location );
						dhl_parcel_map.map.setZoom( 15 );
						dhl_parcel_map.map.panTo( dhl_parcel_map.my_location );
						setTimeout( 'dhl_parcel_map.map.setZoom(14)', 1000 );
					} else {
						console.log( 'Cannot geocode coordinates from address' );
					}
				} else {
					console.log( 'Google Maps Geocoder Error', data );
					// el.val('');
				}
			} );
		}

		function onSelectTerminalClick( event ) {

			event.preventDefault();

			var btn            = document.getElementsByClassName( 'select-terminal' )[ 0 ];
			var code             = btn.dataset.terminalCode;
			var terminal_title   = btn.dataset.terminalTitle;
			var terminal_field   = btn.dataset.method;
			var cod              = btn.dataset.cod;
			var is_backend     = $( '#order_data #dhl-parcel-modal' ).length !== 0 ? true : false;
			var order_id       = false;

			if ( is_backend ) {
				const queryString = window.location.search;
				const urlParams   = new URLSearchParams( queryString );
				order_id          = urlParams.get( 'post' );
				if ( ! order_id ) {
					// Fix for HPOS
					order_id = urlParams.get( 'id' );
				}
			}

			if ( locked ) {
				return;
			}

			$.ajax( {
				url: dhl.wc_ajax_url.toString().replace( '%%endpoint%%', 'choose_dhl_terminal' ),
				dataType: 'json',
				method: 'POST',
				data: {
					action: 'choose_dhl_terminal',
					is_backend_modal: is_backend,
					order_id:         order_id,
					terminal_field:   terminal_field,
					terminal:         code,
					terminal_details: {
						id: code,
						company: btn.dataset.terminalCompany,
						street: btn.dataset.terminalStreet,
						pcode: btn.dataset.terminalPostcode,
						city: btn.dataset.terminalCity,
					},
					cod:              cod,
					security:         dhl.ajax_nonce,
				},
				beforeSend: function() {
					locked = true;
				},
			} )
			.success( function( data ) {
				if ( is_backend ) {
					if ( undefined != typeof data.address ) {
						$( '#order_data .order_data_column:nth-of-type( 3 ) .terminal-information' ).remove();
						$( '#order_data .order_data_column:nth-of-type( 3 ) .address' ).html( data.address );
						let custom_field_container = '#postcustom';
						// Fix for HPOS
						if ( $( '#order_custom' ).length ) {
							custom_field_container = '#order_custom';
						}
						$( '#order_data .order_data_column:nth-of-type( 3 ) .address' ).html( data.address );
						let input_field = $( custom_field_container + ' input[value="' + terminal_field + '"]' ).closest( 'td' ).next().find( 'textarea' );
						let form        = $( 'form#order' ).length ? $( 'form#order' ) : $( '#order_data' ).parents( 'form' );
						let old_terminal = $( input_field ).text();
						if ( old_terminal != data.terminal_id ) {
							$( input_field ).text( data.terminal_id );
							// Remove hidden form fields if exists.
							if ( $( 'input[name="wgm_shipping_old_terminal_id"]' ).length ) {
								$( 'input[name="wgm_shipping_old_terminal_id"]' ).remove();
							}
							if ( $( 'input[name="wgm_shipping_new_terminal_id"]' ).length ) {
								$( 'input[name="wgm_shipping_new_terminal_id"]' ).remove();
							}
							if ( $( 'input[name^="wgm_shipping_new_terminal"]' ).length ) {
								$( 'input[name^="wgm_shipping_new_terminal"]' ).remove();
							}
							// Store selected terminal info into form fields.
							$( form ).append( '<input type="hidden" name="wgm_shipping_old_terminal_id" value="' + old_terminal + '">' );
							$( form ).append( '<input type="hidden" name="wgm_shipping_new_terminal_id" value="' + data.terminal_id + '">' );
							$.each( data.terminal, function( key, value ) {
								$( form ).append( '<input type="hidden" name="wgm_shipping_new_terminal[' + key + ']" value="' + value + '">' );
							});
						}
					}
				}
			} )
			.done( function() {
				locked = false;
				if ( is_backend ) {
				} else {
					// $( document.body ).trigger( "update_checkout" );
					$( '#wc_shipping_dhl_parcels_terminal' ).val( code );
					$( '#dhl-selected-parcel' ).html( terminal_title );

					// Ship to different address.
					$( '.shipping_address #shipping_company' ).val( decodeEntities( btn.dataset.terminalCompany ) );
					$( '.shipping_address #shipping_address_1' ).val( btn.dataset.terminalStreet );
					$( '.shipping_address #shipping_postcode' ).val( btn.dataset.terminalPostcode );
					$( '.shipping_address #shipping_city' ).val( btn.dataset.terminalCity );

					// Check for name fields.
					if ( '' == $( '.shipping_address #shipping_first_name' ).val() ) {
						$( '.shipping_address #shipping_first_name' ).val( $( '.woocommerce-billing-fields #billing_first_name' ).val() );
					}
					if ( '' == $( '.shipping_address #shipping_last_name' ).val() ) {
						$( '.shipping_address #shipping_last_name' ).val( $( '.woocommerce-billing-fields #billing_last_name' ).val() );
					}

					// Tick 'Ship to different address'.
					if ( ! $( '#ship-to-different-address-checkbox' ).is( ':checked' ) ) {
						$( '#ship-to-different-address label' ).trigger( 'click' );
						$( '#ship-to-different-address-checkbox' ).prop( 'checked', true );
					}

					// Fix for Atomion compatibillity.
					if ( $( '#ship-to-different-address span.atomion-checkbox-style' ).length ) {
						if ( ! $( '#ship-to-different-address span.atomion-checkbox-style' ).hasClass( 'checked' ) ) {
							$( '#ship-to-different-address span.atomion-checkbox-style' ).addClass( 'checked' );
						}
					}
				}

				$( '#dhl-close-parcel-modal' ).trigger( 'click' );
			} );
		}

		function showMarkerInfo( marker ) {
			var terminal = marker.dhl_terminal;

			dhl_parcel_map.marker_info = $( '#dhl-parcel-modal-info' );

			var pid     = dhl_parcel_map.marker_info.find( '.packstation_id' );
			var title   = dhl_parcel_map.marker_info.find( 'h3' );
			var address = dhl_parcel_map.marker_info.find( '.info-address' );
			var hours   = dhl_parcel_map.marker_info.find( '.working-hours-wrapper' );

			var mon = dhl_parcel_map.marker_info.find( '.mon' );
			var tue = dhl_parcel_map.marker_info.find( '.tue' );
			var wed = dhl_parcel_map.marker_info.find( '.wed' );
			var thu = dhl_parcel_map.marker_info.find( '.thu' );
			var fri = dhl_parcel_map.marker_info.find( '.fri' );
			var sat = dhl_parcel_map.marker_info.find( '.sat' );
			var sun = dhl_parcel_map.marker_info.find( '.sun' );

			var phone = dhl_parcel_map.marker_info.find( '.info-phone' );
			var email = dhl_parcel_map.marker_info.find( '.info-email' );
			var btn   = dhl_parcel_map.marker_info.find( '.select-terminal' );

			pid.html( terminal.parcelshop_id );
			title.html( terminal.company );
			address.html( terminal.street + ', ' + terminal.pcode + ' ' + terminal.city || '-' );

			if ( ( terminal.mon == null ) && ( terminal.tue == null ) && ( terminal.wed == null ) && ( terminal.thu == null ) && ( terminal.fri == null ) && ( terminal.sat == null ) && ( terminal.sun == null ) ) {
				hours.hide();
			}

			if ( terminal.mon != null ) {
				var monday = terminal.mon.split( '|' );

				mon.find( '.morning' ).html( monday[ 0 ] );
				mon.find( '.afternoon' ).html( monday[ 1 ] );
			} else {
				mon.hide();
			}

			if ( terminal.tue != null ) {
				var tuesday = terminal.tue.split( '|' );

				tue.find( '.morning' ).html( tuesday[ 0 ] );
				tue.find( '.afternoon' ).html( tuesday[ 1 ] );
			} else {
				tue.hide();
			}

			if ( terminal.wed != null ) {
				var wednesday = terminal.wed.split( '|' );

				wed.find( '.morning' ).html( wednesday[ 0 ] );
				wed.find( '.afternoon' ).html( wednesday[ 1 ] );
			} else {
				wed.hide();
			}

			if ( terminal.thu != null ) {
				var thursday = terminal.thu.split( '|' );

				thu.find( '.morning' ).html( thursday[ 0 ] );
				thu.find( '.afternoon' ).html( thursday[ 1 ] );
			} else {
				thu.hide();
			}

			if ( terminal.fri != null ) {
				var friday = terminal.fri.split( '|' );

				fri.find( '.morning' ).html( friday[ 0 ] );
				fri.find( '.afternoon' ).html( friday[ 1 ] );
			} else {
				fri.hide();
			}

			if ( terminal.sat != null ) {
				var saturday = terminal.sat.split( '|' );

				sat.find( '.morning' ).html( saturday[ 0 ] );
				sat.find( '.afternoon' ).html( saturday[ 1 ] );
			} else {
				sat.hide();
			}

			if ( terminal.sun != null ) {
				var sunday = terminal.sun.split( '|' );

				sun.find( '.morning' ).html( sunday[ 0 ] );
				sun.find( '.afternoon' ).html( sunday[ 1 ] );
			} else {
				sun.hide();
			}

			// if (terminal.sat == '00:00-00:00') {
			// 	sat.hide();
			// } else {
			// 	sat.append(terminal.sat || '-');
			// }

			// if (terminal.sun == '00:00-00:00') {
			// 	sun.hide();
			// } else {
			// 	sun.append(terminal.sun || '-');
			// }

			if ( terminal.phone ) {
				phone.html( '<a href="tel:' + terminal.phone + '">' + terminal.phone + '</a>' );
			} else {
				phone.html( '' );
			}

			if ( terminal.email ) {
				email.html( '<a href="mailto:' + terminal.email + '">' + terminal.email + '</a>' );
			} else {
				email.html( '' );
			}

			if ( ! terminal.phone && ! terminal.email ) {
				email.html( '-' );
			}

			// Store terminal data into data attributes at button.
			btn.attr( 'data-terminal-code', terminal.parcelshop_id );
			btn.attr( 'data-terminal-title', terminal.company + ', ' + terminal.street );
			btn.attr( 'data-terminal-company', terminal.company );
			btn.attr( 'data-terminal-street', terminal.street );
			btn.attr( 'data-terminal-postcode', terminal.pcode );
			btn.attr( 'data-terminal-city', terminal.city );
			btn.attr( 'data-terminal-country', terminal.country );
			btn.attr( 'data-cod', terminal.cod );

			$( btn ).off( 'click' ).on( 'click', onSelectTerminalClick );

			dhl_parcel_map.marker_info.show();

			$( document.body ).on( 'click', '#dhl-parcel-modal-info', function( event ) {
				if ( event ) {
					event.preventDefault();
				}
				if ( 'A' != event.target.nodeName ) {
					dhl_parcel_map.marker_info.hide();
				}
			} );

			dhl_parcel_map.map.panTo( marker.getPosition() );
		}

		/**
		 * Create marker objects and show them on map
		 *
		 * @param {array} data
		 */
		function setTerminalMarkers( data ) {
			// Remove existing markers if any
			if ( dhl_parcel_map.markers.length > 0 ) {
				for ( var i = 0; i < dhl_parcel_map.markers.length; i++ ) {
					dhl_parcel_map.markers[ i ].setMap( null );
				}

				dhl_parcel_map.markers = [];
			}

			var marker, item;
			// Create and load marker objects
			for ( var i = 0; i < data.length; i++ ) {
				item = data[ i ];
				try {
					// Create marker
					marker = new google.maps.Marker( {
						position: {
							lat: parseFloat( item.latitude ),
							lng: parseFloat( item.longitude ),
						},
						map: dhl_parcel_map.map,
						icon: dhl.theme_uri + 'point.png',
					} );

					// Store terminal properties in marker
					marker[ 'dhl_terminal' ] = $.extend( {}, item );

					google.maps.event.addListener( marker, 'click', function() {
						if ( this.hasOwnProperty( 'dhl_terminal' ) ) {
							showMarkerInfo( this );
						}
					} );

					// Add to markers array
					dhl_parcel_map.markers.push( marker );
				} catch ( e ) {
					console.log( 'Cannot create marker for terminal', item );
				}

			}

			var markerCluster = new MarkerClusterer( dhl_parcel_map.map,
				dhl_parcel_map.markers, {
					imagePath: dhl.theme_uri + 'm',
					maxZoom: 13,
					minimumClusterSize: 1,
				} );
		}

		/**
		 * Manage AJAX interceptor
		 *
		 * @param  {boolean} enabled
		 * @return {void}
		 */
		function useAjaxInterceptor( enabled ) {
			if ( enabled ) {
				init();
			} else {
				$( document ).off( 'ajaxStop' );
			}
		}

		/**
		 * Get terminals from API
		 *
		 * @return {void}
		 */
		function loadTerminalMarkers() {
			// Temporart detach AJAX interceptor
			useAjaxInterceptor( false );

			endpoint = '';
			dhl_map = $( '#dhl-parcel-modal' );
			if ( $( dhl_map ).hasClass( 'dhl_packstations' ) ) {
				endpoint = 'get_dhl_packstations';
			} else {
				endpoint = 'get_dhl_parcels';
			}

			$.ajax( {
				url: dhl.wc_ajax_url.toString().replace( '%%endpoint%%', endpoint ),
				dataType: 'json',
				method: 'POST',
				data: {
					action: 'get_dhl_parcels',
				},
				success: function( response ) {
					setTerminalMarkers( response );
				},
				error: function( xhr, options, error ) {
					console.error( 'DHL Parcel store: ' + error );
					console.error( xhr );
				},
				complete: function() {
					// Need delay to reattach AJAX interceptor
					setTimeout( function() {
						useAjaxInterceptor( true );
					}, 1000 );
				},
			} );
		}

		function initMap() {
			onSearchLocationClick( null );

			if ( navigator.geolocation ) {
				navigator.geolocation.getCurrentPosition( function( position ) {
					dhl_parcel_map.my_location = {
						lat: position.coords.latitude,
						lng: position.coords.longitude,
					};
				}, function() {
					dhl_parcel_map.my_location = {
						lat: 52.520008,
						lng: 13.404954
					};
				} );
			} else {
				// Browser doesn't support Geolocation
				dhl_parcel_map.my_location = {
					lat: 52.520008,
					lng: 13.404954
				};
			}

			dhl_parcel_map.markers = [];
			dhl_parcel_map.marker_info.hide();

			// Initialize map
			dhl_parcel_map.map = new google.maps.Map(
				document.getElementById( 'dhl-parcel-modal-map' ), {
					center: dhl_parcel_map.my_location,
					zoom: 15,
					maxZoom: 17,
					gestureHandling: 'greedy', // Disable "use ctrl + scroll to zoom the map" message
					disableDefaultUI: true,
					zoomControl: true,
					zoomControlOptions: {
						position: google.maps.ControlPosition.LEFT_CENTER,
					},
				} );

			dhl_parcel_map.my_marker = new google.maps.Marker( {
				position: dhl_parcel_map.my_location,
				map: dhl_parcel_map.map,
			} );

			loadTerminalMarkers();
		}

		/**
		 * Bind Modal Observer
		 *
		 * @return {void}
		 */
		function bindModalObserver() {

			if ( $( '#order_review' ).length ) {

				var target = jQuery( '#order_review' )[ 0 ];

				var observer = new MutationObserver( function( mutations ) {
					mutations.forEach( function( mutation ) {
						var newNodes = mutation.addedNodes;
						if ( newNodes !== null ) {
							if ( jQuery( '#dhl-show-parcel-modal' ).length ) {
								// console.log( 'DHL modal found' );
								bindModal();
							}
						}
					} );
				} );

				var config = {
					attributes: true,
					childList: true,
					characterData: true,
				};

				observer.observe( target, config );
			}
		}

		/**
		 * Setup modal window
		 *
		 * @return {void}
		 */
		function bindModal() {

			// Open the modal on button click
			//
			// this one is buggy and we gets an horrible delay
			// $('body').on('click', '#dhl-show-parcel-modal', function(event) {
			//
			$( '#dhl-show-parcel-modal' )
				.off( 'click' )
				.click( function( event ) {
					if ( event ) {
						event.preventDefault();
					}

					initMap();

					$( '#dhl-parcel-modal' ).css( 'display', 'block' );
					$( 'body' ).css( 'overflow', 'hidden' );

					// $(this).parent().find('input[name="shipping_method"]').prop('checked', true)
				} );

			// Close the modal on X click
			$( 'body' ).on( 'click', '#dhl-close-parcel-modal', function( event ) {
				// $('#dhl-close-parcel-modal').off('click').click(function(event) {
				if ( event ) {
					event.preventDefault();
				}

				$( '#dhl-parcel-modal' ).css( 'display', 'none' );
				$( 'body' ).css( 'overflow', 'auto' );
			} );

			let isMouseDown = false;
			let isDragging  = false;

			document.addEventListener( 'mousedown', function( e ) {
				isMouseDown = true;
			});

			document.addEventListener( 'mousemove', function( e ) {
				if ( isMouseDown ) {
					isDragging = true;
				}
			});

			document.addEventListener( 'mouseup', function( e ) {
				isMouseDown = false;
				setTimeout( function() {
					isDragging = false;
				}, 100 );
			});

			// When the user clicks anywhere outside of the modal, close it
			$( 'body' ).on( 'click', '#dhl-parcel-modal', function( event ) {
				if ( ! isDragging && ( event.target == $( this ).get( 0 ) ) ) {
					$( '#dhl-parcel-modal' ).css( 'display', 'none' );
					$( 'body' ).css( 'overflow', 'auto' );
				}
			} );

			dhl_parcel_map.marker_info = $( '#dhl-parcel-modal-info' );
			// dhl_parcel_map.marker_info.find('.select-terminal').off('click').click(onSelectTerminalClick);

			dhl_parcel_modal.find( '.search-location' )
				.off( 'click' )
				.click( onSearchLocationClick );

			$( document ).on( 'keyup keypress', '#dhl-parcel-modal input[type="text"]', function( event ) {
				if ( event.which == 13 ) {
					event.preventDefault();
					onSearchLocationClick();
					return false;
				}
			} );
		}

		/**
		 * Setup parcel modal
		 *
		 * @return {void}
		 */
		function init() {
			dhl_parcel_modal = $( '#dhl-parcel-modal' );

			// if(!$('#dhl-selected-parcel').html()) {
			// 	$('[name="shipping_method"]:checked').prop('checked', false)
			// }

			// Exit script if modal isn't available
			if ( dhl_parcel_modal.length === 0 ) {
				// console.log('dhl modal isnt loaded.');
				return;
			}

			bindModalObserver();
			bindModal();
		}

		// Check for shipping method changes and bind our map observer.
		$( 'form.checkout' ).on(
			'change',
			'input[name^="shipping_method"]',
			function() {
				if ( $( this ).val().includes( 'dhl_')) {
					bindModalObserver();
				}
			}
		);

		/**
		 * Initialize on jQuery ready
		 *
		 */
		$( document ).ready( function() {
			// Watch for AJAX calls to finish and bind events
			useAjaxInterceptor( true );
		} );

	}
)( jQuery );
