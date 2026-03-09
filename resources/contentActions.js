$( () => {
	$( document ).on( 'click', '#ca-pc_clear', ( e ) => {
		e.preventDefault();
		OO.ui.confirm( mw.msg( 'pagecheckout-ui-clear-confirm' ) ).done( async ( confirmed ) => {
			if ( confirmed ) {
				await ext.pageCheckout.api.clearCheckout( mw.config.get( 'wgPageName' ) );
				window.location.reload();
			}
		} );
	} );
	$( document ).on( 'click', '#ca-pc_checkout', ( e ) => {
		e.preventDefault();
		OO.ui.confirm( mw.msg( 'pagecheckout-ui-checkout-confirm' ) ).done( async ( confirmed ) => {
			if ( confirmed ) {
				await ext.pageCheckout.api.checkoutPage( mw.config.get( 'wgPageName' ) );
				window.location.reload();
			}
		} );
	} );
	$( document ).on( 'click', '#ca-pc_checkin', async ( e ) => {
		e.preventDefault();
		await mw.loader.using( 'ext.pagecheckout.checkinDialog' );
		const wm = OO.ui.getWindowManager();
		const dialog = new ext.pageCheckout.ui.CheckinDialog();
		wm.addWindows( [ dialog ] );
		wm.openWindow( dialog ).closed.then( async ( data ) => {
			if ( data && data.action === 'checkin' ) {
				window.location.reload();
			}
		} );
	} );
} );
