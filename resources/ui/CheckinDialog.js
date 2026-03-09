ext.pageCheckout.ui.CheckinDialog = function ( config ) {
	ext.pageCheckout.ui.CheckinDialog.parent.call( this, config );
	this.pluginForms = {};
};

OO.inheritClass( ext.pageCheckout.ui.CheckinDialog, OO.ui.ProcessDialog );

ext.pageCheckout.ui.CheckinDialog.static.name = 'checkin';
ext.pageCheckout.ui.CheckinDialog.static.title = mw.msg( 'pagecheckout-action-checkin-label' );
ext.pageCheckout.ui.CheckinDialog.static.actions = [
	{
		label: mw.msg( 'pagecheckout-action-checkin-label' ),
		flags: [ 'primary', 'progressive' ],
		action: 'checkin'
	},
	{
		label: mw.msg( 'pagecheckout-ui-cancel-button' ),
		flags: 'safe',
		action: 'cancel'
	}
];

ext.pageCheckout.ui.CheckinDialog.prototype.initialize = async function () {
	ext.pageCheckout.ui.CheckinDialog.parent.prototype.initialize.call( this );

	this.panel = new OO.ui.PanelLayout( {
		padded: true,
		expanded: false
	} );

	const message = mw.msg( 'pagecheckout-ui-checkin-confirm' );
	this.panel.$element.append( new OO.ui.LabelWidget( { label: message } ).$element );
	this.comment = new OO.ui.TextInputWidget();
	this.panel.$element.append( new OO.ui.FieldLayout( this.comment, {
		label: mw.msg( 'pagecheckout-ui-checkin-comment-label' ),
		align: 'top'
	} ).$element );

	const plugins = await ext.pageCheckout.api.getPlugins( mw.config.get( 'wgArticleId' ) );
	for ( const pluginKey in plugins ) {
		if ( Object.prototype.hasOwnProperty.call( plugins, pluginKey ) ) {
			const plugin = plugins[ pluginKey ];
			if ( plugin && plugin.checkInLayout ) {
				await this.appendPlugin( pluginKey, plugin.checkInLayout );
			}
		}
	}

	this.$body.append( this.panel.$element );
	this.updateSize();
};

ext.pageCheckout.ui.CheckinDialog.prototype.appendPlugin = async function ( pluginKey, layoutConfig ) {
	const form = new mw.ext.forms.standalone.Form( layoutConfig );
	this.pluginForms[ pluginKey ] = form;

	form.render();
	form.connect( this, {
		renderComplete: 'updateSize'
	} );
	this.panel.$element.append( form.$element );
};

ext.pageCheckout.ui.CheckinDialog.prototype.getActionProcess = function ( action ) {
	return ext.pageCheckout.ui.CheckinDialog.parent.prototype.getActionProcess.call( this, action ).next(
		function () {
			this.pushPending();
			if ( action === 'checkin' ) {
				const dfd = $.Deferred();
				const comment = this.comment.getValue();
				this.getPluginData().done(
					( pluginData ) => {
						ext.pageCheckout.api.checkinPage( mw.config.get( 'wgPageName' ), comment, pluginData )
							.then( () => {
								this.popPending();
								this.close( { action: 'checkin' } );
							} ).catch( ( error ) => {
								this.popPending();
								dfd.reject( error );
							} );
					}
				).fail(
					( error ) => {
						this.popPending();
						dfd.reject( error );
					}
				);
			}
			if ( action === 'cancel' ) {
				this.close();
			}
		}, this
	);

};

ext.pageCheckout.ui.CheckinDialog.prototype.getPluginData = function () {
	const promises = {};
	for ( const pluginKey in this.pluginForms ) {
		if ( Object.prototype.hasOwnProperty.call( this.pluginForms, pluginKey ) ) {
			const form = this.pluginForms[ pluginKey ];
			promises[ pluginKey ] = $.Deferred();
			form.connect( this, {
				dataSubmitted: function ( formData ) {
					promises[ pluginKey ].resolve( formData );
				},
				validationFailed: function () {
					promises[ pluginKey ].reject( new Error( 'Validation failed for plugin ' + pluginKey ) );
				}
			} );
			form.submit();
		}
	}

	const data = {};
	const promiseValues = [];
	Object.keys( promises ).forEach( ( key ) => {
		promiseValues.push( promises[ key ] );
	} );
	return $.when.apply( $, promiseValues ).then( function () {
		let i = 0;
		for ( const pluginKey in promises ) {
			if ( Object.prototype.hasOwnProperty.call( promises, pluginKey ) ) {
				data[ pluginKey ] = arguments[ i ];
				i++;
			}
		}
		return data;
	} );
};
