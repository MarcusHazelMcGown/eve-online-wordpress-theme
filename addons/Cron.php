<?php
/**
 * Class Name: Cron
 */
namespace WordPress\Themes\EveOnline\Addons;

use WordPress\Themes\EveOnline;

class Cron {
	private $themeOptions = null;

	public $cronEvents = array();

	/**
	 * Constructor
	 *
	 * @param bool $init Init the actions and stuff or not
	 */
	public function __construct($init = false) {
		$this->themeOptions = \get_option('eve_theme_options', EveOnline\Helper\ThemeHelper::getThemeDefaultOptions());
		$this->cronEvents = $this->getTemeCronEvents();

		if($init === true) {
			$this->init();
		} // END if($init === true)
	} // END public function __construct()

	/**
	 * Returning all known theme crons as an array
	 *
	 * @return array Themes Cron Events with their respective hooks
	 */
	public function getTemeCronEvents() {
		return array(
			// Daily Image Cache Cleanup
			'Cleanup Image Cache' => array(
				'hook' => 'cleanupThemeImageCache',
				'recurrence' => 'daily'
			)
		);
	} // END public function getTemeCronEvents()

	/**
	 * Initializing all the stuff
	 */
	public function init() {
		// Managing the crons action hooks
		foreach($this->cronEvents as $cronEvent) {
			/**
			 * Only add the cron if the theme settings say so or else remove them
			 */
			if(!empty($this->themeOptions['cron'][$cronEvent['hook']])) {
				\add_action($cronEvent['hook'], array($this, 'cron' . \ucfirst($cronEvent['hook'])));
			} else {
				$this->removeCron($cronEvent['hook']);
			} // END if(!empty($this->themeOptions['cron'][$cronEvent['hook']]))
		} // END foreach($this->cronEvents as $cronEvent)

		\add_action('switch_theme', array($this, 'removeAllCrons'), 10 , 2);

		$this->scheduleCronEvents();
	} // END public function init()

	/**
	 * Removing all known theme crons
	 */
	public function removeAllCrons() {
		foreach($this->cronEvents as $cronEvent) {
			// removing $cronEvent
			$this->removeCron($cronEvent['hook']);
		} // END foreach($this->cronEvents as $cronEvent)
	} // END public function removeAllCrons()

	/**
	 * Remove a single cron job
	 *
	 * @param string $cronEvent Hook of the cron to remove
	 */
	public function removeCron($cronEvent = null) {
		\wp_clear_scheduled_hook($cronEvent);
	} // END public function removeCron($cron = null)

	/**
	 * schedule the cron jobs
	 */
	public function scheduleCronEvents() {
		foreach($this->cronEvents as $cronEvent) {
			if(!\wp_next_scheduled($cronEvent['hook']) && !empty($this->themeOptions['cron'][$cronEvent['hook']])) {
				\wp_schedule_event(\time(), $cronEvent['recurrence'], $cronEvent['hook']);
			} // END if(!\wp_next_scheduled($cronEvent['hook']) && !empty($this->themeOptions['cron'][$cronEvent['hook']]))
		} // END foreach($this->cronEvents as $cronEvent)
	} //END public function scheduleDailyCron()

	/**
	 * Cron Job: cleanupThemeImageCache
	 * Schedule: Daily
	 */
	public function cronCleanupThemeImageCache() {
		$imageCacheDirectory = EveOnline\Helper\CacheHelper::getImageCacheDir();

		EveOnline\Helper\FilesystemHelper::deleteDirectoryRecursive($imageCacheDirectory, false);
	} // END public function cronCleanupCacheDirectories()
} // END class Cron
