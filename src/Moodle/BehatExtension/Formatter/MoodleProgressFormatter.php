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

        require_once($CFG->dirroot . '/lib/behat/classes/util.php');

        $browser = \Moodle\BehatExtension\Driver\MoodleSelenium2Driver::getBrowser();

        // Calling all directly from here as we avoid more behat framework extensions.
        $runinfo = \behat_util::get_site_info();
        $runinfo .= 'Server OS "' . PHP_OS . '"' . ', Browser: "' . $browser . '"' . PHP_EOL;
        $runinfo .= 'Behat specific fixes have been applied. See http://docs.moodle.org/dev/Acceptance_testing#Browser_specific_fixes' .  PHP_EOL;
        $runinfo .= 'Started at ' . date('d-m-Y, H:i', time());

        $this->writeln($runinfo);
    }

}
