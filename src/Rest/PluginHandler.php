<?php

namespace MediaWiki\Extension\PageCheckout\Rest;

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\PageCheckout\PluginManager;
use MediaWiki\Rest\HttpException;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Title\TitleFactory;
use Wikimedia\ParamValidator\ParamValidator;

class PluginHandler extends SimpleHandler {

	/**
	 * @param PluginManager $pluginManager
	 * @param TitleFactory $titleFactory
	 */
	public function __construct(
		private readonly PluginManager $pluginManager,
		private readonly TitleFactory $titleFactory
	) {
	}

	/**
	 * @return Response|mixed
	 * @throws HttpException
	 */
	public function execute() {
		$params = $this->getValidatedParams();
		$title = $this->titleFactory->newFromID( $params['pageId'] );
		if ( !$title || !$title->exists() ) {
			throw new HttpException( 'Invalid page', 400 );
		}
		$user = RequestContext::getMain()->getUser();
		$plugins = $this->pluginManager->getPlugins();
		$data = [];
		foreach ( $plugins as $pluginKey => $plugin ) {
			$data[$pluginKey] = [
				'checkInLayout' => $plugin->getCheckInLayout( $title, $user )?->getSerialized(),
			];
		}

		return $this->getResponseFactory()->createJson( $data );
	}

	/**
	 * @return array[]
	 */
	public function getParamSettings(): array {
		return [
			'pageId' => [
				static::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => 'integer',
				ParamValidator::PARAM_REQUIRED => true,
			]
		];
	}
}
