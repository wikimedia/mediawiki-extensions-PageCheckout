<?php

namespace MediaWiki\Extension\PageCheckout\Rest;

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\PageCheckout\CheckoutManager;
use MediaWiki\Message\Message;
use MediaWiki\Rest\HttpException;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Title\TitleFactory;
use Wikimedia\ParamValidator\ParamValidator;

class CheckinHandler extends SimpleHandler {

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
		$params = $this->getValidatedBody();
		$title = $this->titleFactory->newFromText( $params['page'] );
		if ( !$title || !$title->exists() ) {
			throw new HttpException( 'Invalid page', 400 );
		}
		$user = RequestContext::getMain()->getUser();
		$checkout = $this->checkoutManager->getCheckoutEntity( $title );
		if ( !$checkout || $checkout->getUser()->getId() !== $user->getId() ) {
			throw new HttpException(
				Message::newFromKey( 'pagecheckout-checkin-not-checked-out-by-user' )->text(), 403
			);
		}

		$pluginData = $params['pluginData'] ?? null;
		$this->checkoutManager->checkIn( $title, $params['comment'], $pluginData );
		return $this->getResponseFactory()->createJson( [ 'success' => true ] );
	}

	/**
	 * @return array[]
	 */
	public function getBodyParamSettings(): array {
		return [
			'page' => [
				static::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
			'comment' => [
				static::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_DEFAULT => '',
			],
			'pluginData' => [
				static::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'array',
				ParamValidator::PARAM_REQUIRED => false,
			]
		];
	}
}
