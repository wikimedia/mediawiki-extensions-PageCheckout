<?php

namespace MediaWiki\Extension\PageCheckout\Rest;

use MediaWiki\Extension\PageCheckout\CheckoutManager;
use MediaWiki\Rest\HttpException;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Title\TitleFactory;
use Wikimedia\ParamValidator\ParamValidator;

class HasCheckoutHandler extends SimpleHandler {

	/**
	 * @param CheckoutManager $checkoutManager
	 * @param TitleFactory $titleFactory
	 */
	public function __construct(
		private readonly CheckoutManager $checkoutManager,
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
		$checkout = $this->checkoutManager->getCheckoutEntity( $title );

		return $this->getResponseFactory()->createJson( [
			'hasCheckout' => $checkout !== null
		] );
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
