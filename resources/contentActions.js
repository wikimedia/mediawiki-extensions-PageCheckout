$( () => {
	$( document ).on( 'click', '#ca-pc_clear', ( e ) => {
		e.preventDefault();
		ext.pageCheckout.actions.openClearCheckout( mw.config.get( 'wgPageName' ) );
	} );
	$( document ).on( 'click', '#ca-pc_checkout', ( e ) => {
		e.preventDefault();
		ext.pageCheckout.actions.openCheckoutPage( mw.config.get( 'wgPageName' ) ).done( ( checkedOut ) => {
			if ( checkedOut ) {
				window.location.reload();
			}
		} );
	} );
	$( document ).on( 'click', '#ca-pc_checkin', async ( e ) => {
		e.preventDefault();
		ext.pageCheckout.actions.openCheckinPage();
	} );
} );
