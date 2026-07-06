<?php

namespace MediaWiki\Extension\PageCheckout\Hook;

use MediaWiki\Preferences\Hook\GetPreferencesHook;

class AddCheckoutPromptPreference implements GetPreferencesHook {

	/**
	 * @inheritDoc
	 */
	public function onGetPreferences( $user, &$preferences ) {
		$preferences['page-checkout-show-popup'] = [
			'type' => 'toggle',
			'label-message' => 'pagecheckout-pref-show-popup',
			'help-message' => 'pagecheckout-pref-show-popup-help',
			'section' => 'editing/editor',
		];
	}
}
