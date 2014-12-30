<?php

namespace Moodle\BehatExtension\Formatter;

use Behat\Behat\Formatter\ProgressFormatter;

use Behat\Behat\DataCollector\LoggerDataCollector,
    Behat\Behat\Event\SuiteEvent,
    Behat\Behat\Exception\FormatterException;

/**
 * MoodleProgressFormatter
 *
 * Basic ProgressFormatter extension to add the site
 * info to the CLI output.
 *
 * @package   moodlehq/moodle-behat-extension
 * @copyright 2013 David Monllaó
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleProgressFormatter extends ProgressFormatter
{

    /**
     * Adding beforeSuite event.
     *
     * @return array The event names to listen to.
     */
    public static function getSubscribedEvents()
    {
        $events = parent::getSubscribedEvents();
        $events['beforeSuite'] = 'beforeSuite';

        return $events;
    }

    /**
     * We print the site info + driver used and OS.
     *
     * At this point behat_hooks::before_suite() already
     * ran, so we have $CFG and family.
     *
     * @param SuiteEvent $event
     * @return void
     */
    public function beforeSuite(SuiteEvent $event)
    {
        global $CFG;

        // NO $CFG, surely a dry-run, let's try to find config.php using passed dirroot.
        if (empty($CFG)) {
            $params = $event->getContextParameters();
            if (!isset($params['dirroot'])) {
                return; // No param, nothing to do.
            }

            $configpath = $params['dirroot'] . '/config.php';
            if (!file_exists($configpath) or !is_readable($configpath)) {
                return; // No config.php, nothing to do.
            }

            // Have found a readable config.php, add it.
            define('CLI_SCRIPT', true);
            require_once($configpath);
        }

        require_once($CFG->dirroot . '/lib/behat/classes/util.php');

        $browser = \Moodle\BehatExtension\Driver\MoodleSelenium2Driver::getBrowser();

        // Calling all directly from here as we avoid more behat framework extensions.
        $runinfo = \behat_util::get_site_info();
        $runinfo .= 'Server OS "' . PHP_OS . '"' . ', Browser: "' . $browser . '"' . PHP_EOL;
        if (in_array(strtolower($browser), array('chrome', 'safari', 'iexplore'))) {
            $runinfo .= 'Browser specific fixes have been applied. See http://docs.moodle.org/dev/Acceptance_testing#Browser_specific_fixes' .  PHP_EOL;
        }
        $runinfo .= 'Started at ' . date('d-m-Y, H:i', time());

        $this->writeln($runinfo);
    }

}
