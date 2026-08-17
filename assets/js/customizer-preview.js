/**
 * SD-ON Theme — живой предпросмотр настроек в Customizer.
 *
 * Настройки из sdon_postmessage_map() применяются мгновенно, подменяя
 * CSS-переменные на элементе <html>.
 */
( function ( api ) {
	'use strict';

	var map = window.sdonPreview || {};

	if ( ! api ) {
		return;
	}

	Object.keys( map ).forEach( function ( id ) {
		var item = map[ id ];

		api( id, function ( setting ) {
			setting.bind( function ( value ) {
				if ( '' === value || null === value || undefined === value ) {
					return;
				}

				document.documentElement.style.setProperty( item.var, String( value ) + ( item.unit || '' ) );
			} );
		} );
	} );
}( window.wp && window.wp.customize ) );
