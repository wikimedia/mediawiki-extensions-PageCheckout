<?php

namespace MediaWiki\Extension\PageCheckout;

use MediaWiki\Extension\PageCheckout\Entity\CheckoutEntity;
use MediaWiki\Page\PageIdentity;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\FormEngine\IFormSpecification;

interface IPageCheckoutPlugin {

	/**
	 * @param PageIdentity $forPage
	 * @param UserIdentity $forUser
	 * @return IFormSpecification|null
	 */
	public function getCheckInLayout( PageIdentity $forPage, UserIdentity $forUser ): ?IFormSpecification;

	/**
	 * @param CheckoutEntity $checkoutEntity
	 * @return void
	 */
	public function onCheckout( CheckoutEntity $checkoutEntity ): void;

	/**
	 * @param CheckoutEntity $checkoutEntity
	 * @param array $data
	 * @return void
	 */
	public function onCheckIn( CheckoutEntity $checkoutEntity, array $data ): void;
}
