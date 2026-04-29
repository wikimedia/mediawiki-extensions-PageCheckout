<?php

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

namespace MediaWiki\Extension\PageCheckout\Hook;

use MediaWiki\Extension\PageCheckout\CheckoutManager;
use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\Permissions\PermissionManager;
use SkinTemplate;

class AddContentActions implements SkinTemplateNavigation__UniversalHook {

	/** @var PermissionManager */
	private $permissionManager;
	/** @var CheckoutManager */
	private $manager;

	/**
	 * @param PermissionManager $permissionManager
	 * @param CheckoutManager $manager
	 */
	public function __construct( PermissionManager $permissionManager, CheckoutManager $manager ) {
		$this->permissionManager = $permissionManager;
		$this->manager = $manager;
	}

	/**
	 * @param SkinTemplate $sktemplate
	 * @param array &$links
	 */
	public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
		$title = $sktemplate->getTitle();
		if ( !$title->exists() ) {
			return;
		}
		$this->addClearCheckout( $sktemplate, $links );
		$this->addCheckoutAction( $sktemplate, $links );
		$this->addCheckinAction( $sktemplate, $links );
	}

	/**
	 * @param SkinTemplate $sktemplate
	 * @param array &$links
	 * @return void
	 */
	private function addClearCheckout( SkinTemplate $sktemplate, array &$links ): void {
		if ( !$this->permissionManager->userHasRight( $sktemplate->getUser(), 'page-checkout-clear' ) ) {
			return;
		}

		$title = $sktemplate->getTitle();
		if ( !$this->manager->isCheckedOut( $title ) ) {
			return;
		}

		$links['actions']['pc_clear'] = [
			'text' => $sktemplate->getContext()->msg( 'pagecheckout-action-clear-label' )->text(),
			'href' => '#',
			'class' => false,
			'id' => 'ca-pc_clear'
		];
		$sktemplate->getOutput()->addModules( 'ext.pagecheckout.contentActions' );
	}

	/**
	 * @param SkinTemplate $sktemplate
	 * @param array &$links
	 * @return void
	 */
	private function addCheckinAction( SkinTemplate $sktemplate, array &$links ) {
		$title = $sktemplate->getTitle();
		if ( !$this->manager->isCheckedOut( $title ) ) {
			return;
		}
		$checkout = $this->manager->getCheckoutEntity( $title );
		if ( !$checkout ) {
			return;
		}
		$owner = $checkout->getUser();
		if ( $owner->getId() !== $sktemplate->getUser()->getId() ) {
			return;
		}
		$links['actions']['pc_clear'] = [
			'text' => $sktemplate->getContext()->msg( 'pagecheckout-title-checkin-label' )->text(),
			'href' => '#',
			'class' => false,
			'id' => 'ca-pc_checkin'
		];
		$sktemplate->getOutput()->addModules( 'ext.pagecheckout.contentActions' );
	}

	private function addCheckoutAction( SkinTemplate $sktemplate, array &$links ): void {
		$title = $sktemplate->getTitle();
		if ( $this->manager->isCheckedOut( $title ) ) {
			return;
		}
		if ( !$this->permissionManager->quickUserCan( 'edit', $sktemplate->getUser(), $title ) ) {
			return;
		}
		$links['actions']['pc_checkout'] = [
			'text' => $sktemplate->getContext()->msg( 'pagecheckout-action-checkout-label' )->text(),
			'href' => '#',
			'class' => false,
			'id' => 'ca-pc_checkout'
		];
		$sktemplate->getOutput()->addModules( 'ext.pagecheckout.contentActions' );
	}
}
