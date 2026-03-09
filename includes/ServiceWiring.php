<?php

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\PageCheckout\CheckoutManager;
use MediaWiki\Extension\PageCheckout\Repo\CheckoutEventRepo;
use MediaWiki\Extension\PageCheckout\Repo\CheckoutRepo;
use MediaWiki\Extension\PageCheckout\SpecialLogLogger;

return [
	'PageCheckoutManager' => static function ( \MediaWiki\MediaWikiServices $services ) {
		return new CheckoutManager(
			RequestContext::getMain()->getUser(),
			new CheckoutRepo( $services->getConnectionProvider(), $services->getObjectCacheFactory() ),
			new CheckoutEventRepo( $services->getDBLoadBalancer() ),
			new SpecialLogLogger(),
			$services->getService( 'PageCheckout.PluginManager' )
		);
	},
	'PageCheckout.PluginManager' => static function ( \MediaWiki\MediaWikiServices $services ) {
		$attribute = ExtensionRegistry::getInstance()->getAttribute( 'PageCheckoutPlugins' );
		return new \MediaWiki\Extension\PageCheckout\PluginManager( $attribute, $services->getObjectFactory() );
	},
];
