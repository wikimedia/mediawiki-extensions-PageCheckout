<?php

namespace MediaWiki\Extension\PageCheckout\Hook;

use MediaWiki\Preferences\Hook\GetPreferencesHook;
use MediaWiki\User\UserOptionsLookup;

class AddUserPreferences implements GetPreferencesHook {

	/**
	 * @param UserOptionsLookup $userOptionsLookup
	 * @param \Config $config
	 */
	public function __construct(
		private readonly UserOptionsLookup $userOptionsLookup,
		private readonly \Config $config
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function onGetPreferences( $user, &$preferences ) {
		$askByDefault = $this->config->get( 'PageCheckoutPromptToCheckoutOnEdit' );
		$prefKey = 'pagecheckout-prompt-on-edit';
		$default = $this->userOptionsLookup->getOption( $user, $prefKey, $askByDefault ? '1' : '0' );

		$preferences[$prefKey] = [
			'type' => 'toggle',
			'label-message' => 'pagecheckout-prompt-on-edit',
			'section' => 'editing/editor',
			'default' => $default,
		];
	}
}
