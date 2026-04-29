window.ext = window.ext || {};
window.ext.pageCheckout = window.ext.pageCheckout || {};
window.ext.pageCheckout.ui = window.ext.pageCheckout.ui || {};

window.ext.pageCheckout.actions = {
	openClearCheckout: function ( page ) {
		OO.ui.confirm( mw.msg( 'pagecheckout-ui-clear-confirm' ), {
			title: mw.msg( 'pagecheckout-action-clear-label' ),
			actions: [
				{
					label: mw.msg( 'pagecheckout-ui-cancel-button' ),
					action: 'cancel'
				},
				{
					label: mw.msg( 'pagecheckout-action-clear-label' ),
					flags: [ 'destructive' ],
					action: 'accept'
				}
			]
		} ).done( async ( confirmed ) => {
			if ( confirmed ) {
				await ext.pageCheckout.api.clearCheckout( page );
				window.location.reload();
			}
		} );
	},
	openCheckoutPage: function ( page ) {
		const dfd = $.Deferred();
		OO.ui.confirm( mw.msg( 'pagecheckout-ui-checkout-confirm' ), {
			title: mw.msg( 'pagecheckout-action-checkout-label' ),
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
		} ).done( async ( confirmed ) => {
			if ( confirmed ) {
				await ext.pageCheckout.api.checkoutPage( page );
				mw.notify( mw.msg( 'pagecheckout-ui-checkout-success' ), { type: 'success' } );
				dfd.resolve( true );
			} else {
				dfd.resolve( false );
			}
		} );
		return dfd.promise();
	},
	openCheckinPage: async function () {
		await mw.loader.using( 'ext.pagecheckout.checkinDialog' );
		const wm = OO.ui.getWindowManager();
		const dialog = new ext.pageCheckout.ui.CheckinDialog();
		wm.addWindows( [ dialog ] );
		wm.openWindow( dialog ).closed.then( async ( data ) => {
			if ( data && data.action === 'checkin' ) {
				window.location.reload();
			}
		} );
	}
};

window.ext.pageCheckout.api = {
	checkoutPage: async function ( page ) {
		return await ext.pageCheckout.api.ajax( 'checkout', { page: page }, 'POST' );
	},
	checkinPage: async function ( page, comment, pluginData ) {
		return await ext.pageCheckout.api.ajax(
			'checkin', {
				page: page, comment: comment, pluginData: pluginData
			}, 'POST'
		);
	},
	clearCheckout: async function ( page ) {
		return await ext.pageCheckout.api.ajax( 'clear', { page: page }, 'POST' );
	},
	getPlugins: async function ( pageId ) {
		return await ext.pageCheckout.api.ajax( 'plugins/' + pageId, {}, 'GET' );
	},
	isCheckedOut: async function ( pageId ) {
		return await ext.pageCheckout.api.ajax( 'has_checkout/' + pageId, {}, 'GET' );
	},
	ajax: async function ( path, params, method ) {
		const base = mw.util.wikiScript( 'rest' ) + '/pagecheckout/';
		let url = base + path;

		const options = {
			method: method.toUpperCase(),
			headers: {
				'Content-Type': 'application/json'
			}
		};

		if ( options.method === 'POST' ) {
			options.body = JSON.stringify( params );
		} else if ( Object.keys( params ).length ) {
			const query = new URLSearchParams( params ).toString();
			url += ( url.indexOf( '?' ) !== -1 ? '&' : '?' ) + query;
		}

		return fetch( url, options ).then( ( res ) => {
			if ( !res.ok ) {
				throw new Error( `REST request failed: ${ res.status }` );
			}
			return res.json();
		} );
	}
};

$( () => {
	const button = document.getElementById( 'pagecheckout-checkin-button' );

	if ( button ) {
		button.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			ext.pageCheckout.actions.openCheckinPage();
		} );
	}
} );
