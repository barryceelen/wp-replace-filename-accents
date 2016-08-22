/* global ReplaceFilenameAccentsVars, ajaxurl */

(function ($) {
	'use strict';

	var vars                = ReplaceFilenameAccentsVars,
	    $progressbar        = $( '#replace-filename-accents-bar' ),
	    $progressbarLabel   = $( '#replace-filename-accents-bar-percent' ),
	    $errorCountLabel    = $( '#replace-filename-accents-errorcount' ),
	    $renamedCountLabel  = $( '#replace-filename-accents-renamedcount' ),
	    $successList        = $( '#replace-filename-accents-successlist' ),
	    count               = 0,
	    errorCount          = 0,
	    ids                 = jQuery.parseJSON( vars.ids ),
	    run                 = true,
	    abort               = false,
	    renamedCount        = 0,
	    total               = ids.length;

	function init() {

		$progressbar.progressbar({
			value: 0,
			change: function() {
				$progressbarLabel.text( $progressbar.progressbar( 'value' ) + '%' );
			},
			complete: function() {
				if ( abort ) {
					$progressbarLabel.text( vars.labelAborted );
				} else {
					$progressbarLabel.text( vars.labelComplete );
				}

			}
		});

		$( '.replace-filename-accents-stop' ).click( function() {

			abort = true;

			if ( run ) {
				run = false;
				$( this ).val( vars.labelStopping );
			}
		});

		rename( ids.shift() );
	}

	function rename( id ) {
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'replace-filename-accents',
				id: id
			},
			success: function( response ) {

				if ( response !== Object( response ) || ( typeof response.success === 'undefined' ) ) {
					response = {};
					response.success = false;
					response.data = vars.labelErrorUnknown + ' (' + id + ')';
				}

				if ( response.success ) {
					status( true, response );
				} else {
					status( false, response );
				}

				if ( ids.length && run ) {
					rename( ids.shift() );
				} else {
					finish();
				}
			},
			error: function( response ) {
				status( false, response );

				if ( ids.length && run ) {
					rename( ids.shift() );
				}
				else {
					finish();
				}
			}
		});
	}

	function status( success, response ) {

		var value = Math.round( ( count / total ) * 1000 ) / 10;

		$progressbar.progressbar( 'value', value );
		count++;

		if ( success ) {
			if ( response.data.renamed ) {
				renamedCount++;
				$renamedCountLabel.html( renamedCount );
				$.each( response.data.messages, function( idx, message ) {
					$successList.append( '<li>' + message + '</li>' );
				});
			}
		} else {
			errorCount++;
			$errorCountLabel.html( errorCount );
			$.each( response.data.messages, function( idx, message ) {
				$successList.append( '<li>' + message + '</li>' );
			});
		}
	}

	function finish() {

		if ( abort ) {
			$progressbarLabel.text( vars.labelAborted );
			$( '.replace-filename-accents-stop' ).val( vars.labelAborted ).attr( 'disabled', 'disabled' );
			$( '.replace-filename-accents-restart' ).removeClass( 'hidden' ).attr( 'aria-hidden', 'false' );
			if ( $successList.find( 'li' ).length ) {
				$successList.append( '<li>' + vars.labelAborted + '</li>' );
			}
		} else {
			$progressbar.progressbar( 'value', 100 );
			$successList.append( '<li>' + vars.labelComplete + '</li>' );
			$( '.replace-filename-accents-stop' ).hide();
		}
	}

	init();

}(jQuery));