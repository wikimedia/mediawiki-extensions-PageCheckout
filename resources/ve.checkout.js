mw.hook( 've.activationComplete' ).add( async () => {
	let isEnabled = mw.user.options.get( 'pagecheckout-prompt-on-edit' );
	if ( isEnabled === null ) {
		isEnabled = await require( './config.json' ).checkoutPromptEnabledDefault || false;
	} else {
		isEnabled = isEnabled === '1';
	}
	if ( !isEnabled ) {
		return;
	}
	try {
		const hasCheckout = await ext.pageCheckout.api.isCheckedOut( mw.config.get( 'wgArticleId' ) );
		if ( hasCheckout.hasCheckout ) {
			return;
		}
	} catch ( e ) {
		return;
	}

	OO.ui.confirm(
		mw.msg( 'pagecheckout-ui-checkout-prompt-ve' ), {
			actions: [
				{
					label: mw.msg( 'pagecheckout-ui-cancel-button' ),
					action: 'cancel'
				},
				{
					label: mw.msg( 'pagecheckout-action-checkout-label' ),
					flags: [ 'progressive' ],
					action: 'accept'
				}
			]
		} )
		.done( async ( confirmed ) => {
			if ( !confirmed ) {
				return;
			}
			try {
				const res = await ext.pageCheckout.api.checkoutPage( mw.config.get( 'wgPageName' ) );
				if ( !res.success ) {
					throw new Error( 'API error' );
				}
			} catch ( e ) {
				OO.ui.alert( mw.msg( 'pagecheckout-error-save-failed' ), { type: 'error' } );
			}
		} );
} );
