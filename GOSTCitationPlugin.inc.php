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

	/**
	 * @copydoc Plugin::getManagementVerbLinkAction()
	 */
	function getManagementVerbLinkAction($request, $verb) {
		list($verbName, $verbLocalized) = $verb;

		switch ($verbName) {
			case 'settings':
				// Generate a link action for the "settings" action
				$dispatcher = $request->getDispatcher();
				import('lib.pkp.classes.linkAction.request.RedirectAction');
				return new LinkAction(
					$verbName,
					new RedirectAction($dispatcher->url(
						$request, ROUTE_PAGE,
						null, 'management', 'settings', 'website',
						array('uid' => uniqid()), // Force reload
						'staticPages' // Anchor for tab
					)),
					$verbLocalized,
					null
				);
			default:
				return parent::getManagementVerbLinkAction($request, $verb);
		}
	}

	/**
	 * Display an HTML-formatted citation. We register PKPString::strtoupper modifier
	 * in order to convert author names to uppercase.
	 * @param $article Article
	 * @param $issue Issue
	 * @param $journal Journal
	 */
	function fetchCitation(&$article, &$issue, &$journal) {
		$templateMgr = TemplateManager::getManager($this->getRequest());
		$templateMgr->register_modifier('mb_upper', array('PKPString', 'strtoupper'));
		$templateMgr->register_modifier('GOST_date_format', array($this, 'GOSTDateFormat'));
		$templateMgr->register_modifier('GOST_date_format_with_day', array($this, 'GOSTDateFormatWithDay'));
		return parent::fetchCitation($article, $issue, $journal);
	}

 	/**
	 * @copydoc Plugin::manage()
	 */
	function manage($verb, $args, &$message, &$messageParams, &$pluginModalContent = null) {
		$request = $this->getRequest();
		switch ($verb) {
			case 'settings':
				$templateMgr = TemplateManager::getManager($request);
				$templateMgr->register_function('plugin_url', array($this, 'smartyPluginUrl'));
				$journal = $request->getJournal();

				$this->import('GOSTSettingsForm');
				$form = new GOSTSettingsForm($this, $journal->getId());
				if ($request->getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						$request->redirect(null, 'manager', 'plugin');
						return false;
					} else {
						$form->display();
					}
				} else {
					if ($form->isLocaleResubmit()) {
						$form->readInputData();
					} else {
						$form->initData();
					}
					$form->display();
				}
				return true;
			default:
				// Unknown management verb, delegate to parent
				return parent::manage($verb, $args, $message);
		}
	}

	/**
	 * Extend the {url ...} smarty to support this plugin.
	 * @param $params array
	 * @param $smarty Smarty
	 */
	function smartyPluginUrl($params, &$smarty) {
		$path = array($this->getCategory(), $this->getName());
		if (is_array($params['path'])) {
			$params['path'] = array_merge($path, $params['path']);
		} elseif (!empty($params['path'])) {
			$params['path'] = array_merge($path, array($params['path']));
		} else {
			$params['path'] = $path;
		}

		return $smarty->smartyUrl($params, $smarty);
	}

	/**
	 * @function GOSTDateFormat Format date taking in consideration GOST month abbreviations
	 * @param $string string
	 * @return string
	 */
	function GOSTDateFormat($string) {
		if (is_numeric($string)) {
			// it is a numeric string, we handle it as timestamp
			$timestamp = (int)$string;
		} else {
			$timestamp = strtotime($string);
		}
		$format = "%B %Y";
		if (PKPString::strlen(strftime("%B", $timestamp)) > 4) {
			$format = "%b. %Y";
		}

		return PKPString::strtolower(strftime($format, $timestamp));
	}

	/**
	 * @function GOSTDateFormatWithDay Format date taking in consideration GOST month abbreviations
	 * @param $string string
	 * @return string
	 */
	function GOSTDateFormatWithDay($string) {
		if (is_numeric($string)) {
			// it is a numeric string, we handle it as timestamp
			$timestamp = (int)$string;
		} else {
			$timestamp = strtotime($string);
		}
		$format = "%d %B %Y";
		if (PKPString::strlen(strftime("%B", $timestamp)) > 4) {
			$format = "%d %b. %Y";
		}

		return PKPString::strtolower(strftime($format, $timestamp));
	}
}

?>
