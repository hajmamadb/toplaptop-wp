/**
 * Frontend related javascript.
 */

'use strict';

/**
 * Initialize the avatar image upload dropzone.
 */


( function( $, tgwc ) {
	Dropzone.autoDiscover = false;

	$('#tgwc-file-drop-zone').dropzone( {
		maxFilesize       : tgwc.avatarUploadSize,
		maxFiles          : 1,
		thumbnailWidth    : tgwc.avatarImageSize.width,
		thumbnailHeight   : tgwc.avatarImageSize.height,
		clickable         : '.tgwc-user-avatar-upload-icon',
		acceptedFiles     : 'image/*',
		dictDefaultMessage: '',
		timeout           : 30000,
		previewTemplate   : '',
		addedfile: function( file ) { // Removing this callback, results in console error.
		},
		thumbnail: function( file, dataURL ) {
			$( this.element ).find( 'img' ).attr( 'src', dataURL );
		},
		sending: function( file, xhr, formData ) {
			formData.append( 'previous_attach_id', tgwc.previousAttachId );
			$( this.element ).find( '.tgwc-progress' ).css( 'display', 'block' );
			$( this.element ).find( '.tgwc-remove-image' ).css( 'display', 'none' );
		},
		error: function( file, errorMessage, xhr ) {
			$( this.element ).find( '.tgwc-progress' ).html( errorMessage.data );
		},
		complete: function( file ) {
			$( this.element ).find( '.tgwc-progress' ).css( 'display', 'none' );
			this.removeFile( file );
		},
		success: function( file, xhr ) {
			tgwc.previousAttachId = xhr.data.attach_id;
			$( this.element ).find( '.tgwc-remove-image' ).css( 'display', 'block' );
		}
	} );

	/**
	 * Handle avatar image deletion.
	 */
	var $dropzone = $( '#tgwc-file-drop-zone' );
	$dropzone.on( 'click', '.tgwc-remove-image', function( e ) {
		e.preventDefault();

		var avatarUploadDropZone = Dropzone.forElement( '#' + $dropzone.attr( 'id' ) );
		avatarUploadDropZone.removeAllFiles( true );

		// Remove the previous image file.
		$.ajax({
			method: 'post',
			url: tgwc.ajaxURL,
			data: {
				action: 'tgwc_avatar_upload',
				previous_attach_id: tgwc.previousAttachId,
				tgwc_avatar_upload_nonce: $dropzone.find( 'input[name="tgwc_avatar_upload_nonce"]' ).val()
			},
			beforeSend: function( xhr ){
				$dropzone.find( '.tgwc-progress' ).css( 'display', 'block' );
			}
		} ).done( function( data ) {
			$dropzone.find( 'img' ).attr( 'src', tgwc.gravatarImage );
			$dropzone.find( 'img' ).attr( 'srcset', '' );
		} ).always( function() {
			$dropzone.find( '.tgwc-progress' ).hide();
			$dropzone.find( '.tgwc-remove-image' ).hide();
		} );

	} );
} ) ( jQuery, window.tgwc );

/**
 * Set the tgwc-woocommerce id to the wrapper.
 */
jQuery( function( $ ) {
	var $nav     = $( '.tgwc-woocommerce-MyAccount-navigation' ),
	    $wrapper = $nav.parents( '.woocommerce' ).first(),
	    $ul      = $nav.find( 'ul' );

		if ( 'tab' === window.tgwc.menuStyle && undefined === wp.customize ) {
			var navScroll = $ul.scrollTabs();
			    navScroll = Array.isArray( navScroll ) ? navScroll[0] : navScroll;
			navScroll.scrollSelectedIntoView();
		}

		// Add id to the wocommerce class for compatibility.
		$wrapper.attr( 'id', 'tgwc-woocommerce' );
} );

/**
 * Handle hover on sub menu item.
 */
jQuery( function( $ ) {
	// hover over a menu item
	$( 'li.woocommerce-MyAccount-navigation-link' ).on( 'mouseover', function() {
		var $navItem = $( this ),
			$submenuWrap = $( '> ul', $navItem );

		// grab the menu item's position relative to its positioned parent
		var navItemPos = $navItem.position();

		// place the submenu in the correct position relevant to the menu item
		$submenuWrap.css({
			left: navItemPos.left,
		} );
	} );
} );
