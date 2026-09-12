<?php

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\PageCheckout\CheckoutManager;
use MediaWiki\Extension\PageCheckout\PluginManager;
use MediaWiki\Extension\PageCheckout\Repo\CheckoutEventRepo;
use MediaWiki\Extension\PageCheckout\Repo\CheckoutRepo;
use MediaWiki\Extension\PageCheckout\SpecialLogLogger;
use MediaWiki\MediaWikiServices;
use MediaWiki\Registration\ExtensionRegistry;

/** @phpcs-require-sorted-array */
return [
	'PageCheckout.PluginManager' => static function ( MediaWikiServices $services ): PluginManager {
		$attribute = ExtensionRegistry::getInstance()->getAttribute( 'PageCheckoutPlugins' );
		return new PluginManager( $attribute, $services->getObjectFactory() );
	},
	'PageCheckoutManager' => static function ( MediaWikiServices $services ): CheckoutManager {
		return new CheckoutManager(
			RequestContext::getMain()->getUser(),
			new CheckoutRepo( $services->getConnectionProvider(), $services->getObjectCacheFactory() ),
			new CheckoutEventRepo( $services->getDBLoadBalancer(), $services->getUserFactory() ),
			new SpecialLogLogger(),
			$services->getService( 'PageCheckout.PluginManager' ),
			$services->getMainConfig()
		);
	},
];
