<?php

namespace MediaWiki\Extension\PageCheckout\Activity;

use MediaWiki\Extension\Workflows\Activity\ExecutionStatus;
use MediaWiki\Extension\Workflows\WorkflowContext;
use MWException;
use Title;
use User;

class PageCheckoutActivity extends CheckoutActivity {
	/** @var bool */
	private $force;

	/**
	 * @inheritDoc
	 */
	public function execute( $data, WorkflowContext $context ): ExecutionStatus {
		$this->force = isset( $data['force'] ) ? (bool)$data['force'] : false;
		return parent::execute( $data, $context );
	}

	/**
	 * @param User $user
	 * @param Title $title
	 * @throws MWException
	 */
	protected function doAction( User $user, Title $title ) {
		if ( $this->force && $this->manager->isCheckedOut( $title ) ) {
			$this->manager->clearCheckout( $title );
		}
		$this->manager->checkout( $title, $user );
	}
}
