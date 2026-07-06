<?php

namespace MediaWiki\Extension\PageCheckout\Hook;

use MediaWiki\Config\Config;
use MediaWiki\Message\Message;
use MediaWiki\Title\NamespaceInfo;

class IntegrateIntoNamespaceManager {

	/** @var Config */
	protected $config;

	/** @var NamespaceInfo */
	protected $namespaceInfo;

	/**
	 * @param Config $config
	 * @param NamespaceInfo $namespaceInfo
	 */
	public function __construct( Config $config, NamespaceInfo $namespaceInfo ) {
		$this->config = $config;
		$this->namespaceInfo = $namespaceInfo;
	}

	/**
	 * @param array &$aMetaFields
	 *
	 * @return bool
	 */
	public function onNamespaceManager__getMetaFields( &$aMetaFields ) { // phpcs:ignore MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName, Generic.Files.LineLength.TooLong
		$aMetaFields[] = [
			'name' => 'enable-pagecheckout',
			'type' => 'boolean',
			'label' => Message::newFromKey( 'pagecheckout-ns-pref-enable-pagecheckout' )->text(),
			'filter' => [
				'type' => 'boolean'
			],
		];
		return true;
	}

	/**
	 * @param array &$aResults
	 *
	 * @return bool
	 */
	public function onBSApiNamespaceStoreMakeData( &$aResults ) {
		$current = $this->config->get( 'PageCheckoutEnabledNamespaces' );
		$iResults = count( $aResults );
		for ( $i = 0; $i < $iResults; $i++ ) {
			$aResults[ $i ][ 'enable-pagecheckout' ] = [
				'value' => in_array( $aResults[ $i ][ 'id' ], $current ),
				'disabled' => $aResults[ $i ]['isTalkNS']
			];
		}
		return true;
	}

	/**
	 * @param array &$namespaceDefinitions
	 * @param int &$ns
	 * @param array $additionalSettings
	 * @param bool $useInternalDefaults
	 *
	 * @return bool
	 */
	public function onNamespaceManager__editNamespace( // phpcs:ignore MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName, Generic.Files.LineLength.TooLong
		&$namespaceDefinitions, &$ns, $additionalSettings, $useInternalDefaults = false
	) {
		if ( $this->namespaceInfo->isTalk( $ns ) ) {
			// Stabilization can not be activated for TALK namespaces!
			return true;
		}

		if ( !$useInternalDefaults && isset( $additionalSettings['enable-pagecheckout'] ) ) {
			$namespaceDefinitions[$ns][ 'enable-pagecheckout' ] = $additionalSettings['enable-pagecheckout'];
		} else {
			$namespaceDefinitions[$ns][ 'enable-pagecheckout' ] = false;
		}
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function onNamespaceManagerBeforePersistSettings(
		array &$configuration, int $id, array $definition, array $mwGlobals
	): void {
		$enabledNamespaces = $mwGlobals['wgPageCheckoutEnabledNamespaces'] ?? [];
		if ( $this->namespaceInfo->isTalk( $id ) ) {
			// Stabilization can not be activated for TALK namespaces!
			return;
		}
		$currentlyActivated = in_array( $id, $enabledNamespaces );

		$explicitlyDeactivated = false;
		if ( isset( $definition['enable-pagecheckout'] ) && $definition['enable-pagecheckout'] === false ) {
			$explicitlyDeactivated = true;
		}

		$explicitlyActivated = false;
		if ( isset( $definition['enable-pagecheckout'] ) && $definition['enable-pagecheckout'] === true ) {
			$explicitlyActivated = true;
		}

		if ( ( $currentlyActivated && !$explicitlyDeactivated ) || $explicitlyActivated ) {
			$configuration['wgPageCheckoutEnabledNamespaces'][] = $id;
		}
	}
}
