<?php

namespace MediaWiki\Extension\PageCheckout;

use Wikimedia\ObjectFactory\ObjectFactory;

class PluginManager {

	/** @var IPageCheckoutPlugin[] */
	private array $plugins = [];

	/**
	 * @param array $pluginRegistry
	 * @param ObjectFactory $objectFactory
	 */
	public function __construct(
		private readonly array $pluginRegistry,
		private readonly ObjectFactory $objectFactory
	) {
	}

	/**
	 * @param string $key
	 * @param IPageCheckoutPlugin $plugin
	 */
	public function registerPlugin( string $key, IPageCheckoutPlugin $plugin ): void {
		$this->plugins[$key] = $plugin;
	}

	/**
	 * @return IPageCheckoutPlugin[]
	 */
	public function getPlugins(): array {
		$this->load();
		return $this->plugins;
	}

	/**
	 * @return void
	 */
	private function load(): void {
		foreach ( $this->pluginRegistry as $key => $spec ) {
			$plugin = $this->objectFactory->createObject( $spec );
			if ( !$plugin instanceof IPageCheckoutPlugin ) {
				throw new \UnexpectedValueException( "Plugin $key does not implement IPageCheckoutPlugin" );
			}
			$this->registerPlugin( $key, $plugin );
		}
	}
}
