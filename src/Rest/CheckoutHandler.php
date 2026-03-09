<?php

namespace MediaWiki\Extension\PageCheckout\Rest;

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\PageCheckout\CheckoutManager;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Rest\HttpException;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Title\TitleFactory;
use Wikimedia\ParamValidator\ParamValidator;

class CheckoutHandler extends SimpleHandler {

	/**
	 * @param CheckoutManager $checkoutManager
	 * @param PermissionManager $permissionManager
	 * @param TitleFactory $titleFactory
	 */
	public function __construct(
		private readonly CheckoutManager $checkoutManager,
		private readonly PermissionManager $permissionManager,
		private readonly TitleFactory $titleFactory
	) {
	}

	/**
	 * @return Response|mixed
	 * @throws HttpException
	 */
	public function execute() {
		$title = $this->titleFactory->newFromText( $this->getValidatedBody()['page'] );
		if ( !$title || !$title->exists() ) {
			throw new HttpException( 'Invalid page', 400 );
		}
		$user = RequestContext::getMain()->getUser();
		if ( !$this->permissionManager->userCan( 'edit', $user, $title ) ) {
			throw new HttpException( 'User cannot edit page', 403 );
		}

		$this->checkoutManager->checkout( $title, $user );
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
			]
		];
	}
}
