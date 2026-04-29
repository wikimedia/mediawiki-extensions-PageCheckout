let initialized = false;
const pageCheckoutTools = [];
let hasCheckout = null;
let checkoutStatePromise = null;

function updatePageCheckoutToolState( tool ) {
	const hasCurrentPage = !!mw.config.get( 'wgPageName' );

	tool.setDisabled( !hasCurrentPage || hasCheckout === true );
}

function setHasCheckoutState( newState ) {
	hasCheckout = !!newState;
	pageCheckoutTools.forEach( ( tool ) => {
		updatePageCheckoutToolState( tool );
	} );
}

function refreshHasCheckoutState() {
	if ( checkoutStatePromise ) {
		return checkoutStatePromise;
	}
	const articleId = mw.config.get( 'wgArticleId' );

	if ( !articleId ) {
		setHasCheckoutState( false );
		return Promise.resolve( false );
	}
	checkoutStatePromise = ext.pageCheckout.api.isCheckedOut( articleId )
		.then( ( response ) => {
			setHasCheckoutState( response.hasCheckout );
			checkoutStatePromise = null;
			return hasCheckout;
		} )
		.catch( ( e ) => {
			checkoutStatePromise = null;
			throw e;
		} );
	return checkoutStatePromise;
}

ve.ui.PageCheckoutTool = function VeUiPageCheckoutTool() {
	ve.ui.PageCheckoutTool.super.apply( this, arguments );
	pageCheckoutTools.push( this );
	updatePageCheckoutToolState( this );
	if ( hasCheckout === null ) {
		refreshHasCheckoutState().catch( () => undefined );
	}
};
OO.inheritClass( ve.ui.PageCheckoutTool, ve.ui.Tool );
ve.ui.PageCheckoutTool.static.name = 'pageCheckout';
ve.ui.PageCheckoutTool.static.group = 'utility';
ve.ui.PageCheckoutTool.static.icon = 'lock';
ve.ui.PageCheckoutTool.static.title = OO.ui.deferMsg( 'pagecheckout-action-checkout-label' );
ve.ui.PageCheckoutTool.static.autoAddToCatchall = false;
ve.ui.PageCheckoutTool.prototype.onSelect = function () {
	const currentPage = mw.config.get( 'wgPageName' );
	if ( !currentPage ) {
		return;
	}

	ext.pageCheckout.actions.openCheckoutPage( currentPage ).done( ( checkedOut ) => {
		if ( checkedOut ) {
			setHasCheckoutState( true );
			return;
		}
		refreshHasCheckoutState().catch( () => undefined );
	} );
	this.setActive( false );
};
ve.ui.PageCheckoutTool.prototype.onUpdateState = function () {
	ve.ui.PageCheckoutTool.super.prototype.onUpdateState.apply( this, arguments );
	updatePageCheckoutToolState( this );
	if ( hasCheckout === null ) {
		refreshHasCheckoutState().catch( () => undefined );
	}
};
ve.ui.toolFactory.register( ve.ui.PageCheckoutTool );

mw.hook( 've.activationComplete' ).add( async () => {
	if ( initialized ) {
		return;
	}
	initialized = true;
	const enabledNamespaces = await require( './config.json' ).checkoutPromptEnabledNamespaces || [];
	const currentNamespace = mw.config.get( 'wgNamespaceNumber' );
	const isEnabled = enabledNamespaces.indexOf( currentNamespace ) !== -1;
	if ( !isEnabled ) {
		return;
	}
	try {
		const isCheckedOut = await refreshHasCheckoutState();
		if ( isCheckedOut ) {
			return;
		}
	} catch ( e ) {
		return;
	}

	OO.ui.confirm(
		mw.msg( 'pagecheckout-ui-checkout-prompt-ve' ), {
			title: mw.msg( 'pagecheckout-action-checkout-label' ),
			size: 'large',
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
				setHasCheckoutState( true );
				mw.notify( mw.msg( 'pagecheckout-ui-checkout-success' ), { type: 'success' } );
			} catch ( e ) {
				mw.notify( mw.msg( 'pagecheckout-error-save-failed' ), { type: 'error' } );
			}
		} );
} );
