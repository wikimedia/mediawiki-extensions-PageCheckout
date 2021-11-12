<?php

namespace MediaWiki\Extension\PageCheckout\AlertProvider;

use MWStake\MediaWiki\Component\AlertBanners\IAlertProvider;
use MediaWiki\Extension\PageCheckout\CheckoutManager;
use RequestContext;

class PageCheckout implements IAlertProvider {

	/** @var CheckoutManager */
	private $manager;

	/**
	 * ApplyCheckout constructor.
	 * @param CheckoutManager $manager
	 */
	public function __construct( CheckoutManager $manager ) {
		$this->manager = $manager;
	}

	/**
	 * @return string
	 */
	public function getHTML(): string {
		$context = RequestContext::getMain();
		if ( !$context->getTitle() || !$context->getTitle()->exists() ) {
			return '';
		}
		if ( !$this->manager->isCheckedOut( $context->getTitle() ) ) {
			return '';
		}
		$user = $this->manager->getCheckoutEntity( $context->getTitle() )->getUser();
		$realname = empty( $user->getRealName() ) ? $user->getName() : $user->getRealName();
		return $context->msg( 'pagecheckout-alertbanner-checkout' )
			->params( $user->getName(), $realname )
			->text();
	}

	/**
	 * @return string
	 */
	public function getType(): string {
		return IAlertProvider::TYPE_INFO;
	}

}