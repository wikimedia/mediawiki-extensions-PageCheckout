ext.pageCheckout.ui.CheckoutDialog = function ( config ) {
	ext.pageCheckout.ui.CheckoutDialog.parent.call( this, config );
	this.message = ( config && config.message ) || mw.msg( 'pagecheckout-ui-checkout-confirm' );
};

OO.inheritClass( ext.pageCheckout.ui.CheckoutDialog, OO.ui.ProcessDialog );

ext.pageCheckout.ui.CheckoutDialog.static.name = 'checkout';
ext.pageCheckout.ui.CheckoutDialog.static.title = mw.msg( 'pagecheckout-action-checkout-label' );
ext.pageCheckout.ui.CheckoutDialog.static.actions = [
	{
		label: mw.msg( 'pagecheckout-action-checkout-confirm-label' ),
		flags: [ 'primary', 'destructive' ],
		action: 'checkout'
	},
	{
		icon: 'close',
		title: mw.msg( 'pagecheckout-ui-cancel-button' ),
		flags: 'safe',
		action: 'cancel'
	}
];

ext.pageCheckout.ui.CheckoutDialog.prototype.initialize = function () {
	ext.pageCheckout.ui.CheckoutDialog.parent.prototype.initialize.call( this );

	this.panel = new OO.ui.PanelLayout( {
		padded: true,
		expanded: false
	} );
	this.panel.$element.append( new OO.ui.LabelWidget( { label: this.message } ).$element );

	this.$body.append( this.panel.$element );
};

ext.pageCheckout.ui.CheckoutDialog.prototype.getActionProcess = function ( action ) {
	return ext.pageCheckout.ui.CheckoutDialog.parent.prototype.getActionProcess.call( this, action ).next(
		function () {
			this.close( { action: action } );
		}, this
	);
};
