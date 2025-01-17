<?php

namespace MediaWiki\Extension\PageCheckout\Activity;

use MediaWiki\Title\Title;
use MWException;
use User;

class PageCheckInActivity extends CheckoutActivity {
	/**
	 * @param User $user
	 * @param Title $title
	 * @throws MWException
	 */
	protected function doAction( User $user, Title $title ) {
		$this->manager->checkIn( $title, $user );
	}
}
