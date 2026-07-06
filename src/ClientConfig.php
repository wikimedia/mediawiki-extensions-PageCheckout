<?php

namespace MediaWiki\Extension\PageCheckout;

use MediaWiki\Config\Config;
use MediaWiki\ResourceLoader\Context;

class ClientConfig {

	/**
	 * @param Context $context
	 * @param Config $config
	 * @return array
	 */
	public static function getVEPluginConfig( Context $context, Config $config ): array {
		return [
			'checkoutEnabledNamespaces' => $config->get( 'PageCheckoutEnabledNamespaces' ),
		];
	}
}
