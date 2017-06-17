<?php

/**
 * @file plugins/citationFormats/GOST/GOSTCitationPlugin.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * With contributions from by Lepidus Tecnologia
 *
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GOSTCitationPlugin
 * @ingroup plugins_citationFormats_GOST
 *
 * @brief GOST citation format plugin
 */

import('classes.plugins.CitationPlugin');

class GOSTCitationPlugin extends CitationPlugin {
	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->addLocaleData();
		return $success;
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'GOSTCitationPlugin';
	}

	/**
	 * @copydoc Plugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.citationFormats.GOST.displayName');
	}

	/**
	 * @copydoc CitationPlugin::getCitationFormatName()
	 */
	function getCitationFormatName() {
		return __('plugins.citationFormats.GOST.citationFormatName');
	}

	/**
	 * @copydoc Plugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.citationFormats.GOST.description');
	}

	/**
	 * Get the localized location for citations in this journal
	 * @param $journal Journal
	 * @return string
	 */
	function getLocalizedLocation($journal) {
		$settings = $this->getSetting($journal->getId(), 'location');
		if ($settings === null) {
			return null;
		}
		$location = $settings[AppLocale::getLocale()];
		if (empty($location)) {
			$location = $settings[AppLocale::getPrimaryLocale()];
		}
		return $location;
	}
}

?>
