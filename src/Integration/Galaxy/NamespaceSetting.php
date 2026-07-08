<?php

namespace MediaWiki\Extension\PageCheckout\Integration\Galaxy;

use BlueSpice\GalaxyDistributionConnector\NamespaceSettings\INamespaceSetting;
use MediaWiki\Message\Message;

class NamespaceSetting implements INamespaceSetting {

	/**
	 * @return Message
	 */
	public function getLabel(): Message {
		return Message::newFromKey( 'pagecheckout-ns-pref-enable-pagecheckout' );
	}

	/**
	 * @return Message
	 */
	public function getDescription(): Message {
		return Message::newFromKey( 'pagecheckout-ns-pref-enable-pagecheckout-help' );
	}

	/**
	 * @param int $namespace
	 * @param mixed $value
	 * @return void
	 */
	public function apply( int $namespace, mixed $value ): void {
		$GLOBALS['wgPageCheckoutEnabledNamespaces'] = $GLOBALS['wgPageCheckoutEnabledNamespaces'] ?? [];
		if ( !$value && in_array( $namespace, $GLOBALS['wgPageCheckoutEnabledNamespaces'] ) ) {
			$GLOBALS['wgPageCheckoutEnabledNamespaces'] = array_diff(
				$GLOBALS['wgPageCheckoutEnabledNamespaces'],
				[ $namespace ]
			);
		} elseif ( $value && !in_array( $namespace, $GLOBALS['wgPageCheckoutEnabledNamespaces'] ) ) {
			$GLOBALS['wgPageCheckoutEnabledNamespaces'][] = $namespace;
		}
	}
}
