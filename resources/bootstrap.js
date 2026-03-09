window.ext = window.ext || {};
window.ext.pageCheckout = window.ext.pageCheckout || {};
window.ext.pageCheckout.ui = window.ext.pageCheckout.ui || {};

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
