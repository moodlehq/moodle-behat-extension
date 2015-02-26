<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Feature step counter for distributing features between parallel runs.
 *
 * Use it with --dry-run (and any other selectors combination) to
 * get the results quickly.
 *
 * @copyright  2015 onwards Rajesh Taneja
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodle\BehatExtension\Formatter;

use Behat\Behat\Event\FeatureEvent;
use Behat\Behat\Event\ScenarioEvent,
    Behat\Behat\Event\OutlineExampleEvent,
    Behat\Behat\Event\OutlineEvent,
    Behat\Behat\Event\StepEvent;

class MoodleStepCountFormatter extends \Behat\Behat\Formatter\ConsoleFormatter {

    /** @var int Number of steps executed in feature file. */
    private static $stepcount = 0;

    /**
     * Returns default parameters to construct ParameterBag.
     *
     * @return array
     */
    protected function getDefaultParameters() {
        return array();
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents() {
        $events = array('afterStep', 'beforeFeature', 'afterFeature');
        return array_combine($events, $events);
    }

    /**
     * Listens to "feature.before" event.
     *
     * @param FeatureEvent $event
     */
    public function beforeFeature(FeatureEvent $event) {
        self::$stepcount = 0;
    }

    /**
     * Listens to "feature.after" event.
     *
     * @param FeatureEvent $event
     */
    public function afterFeature(FeatureEvent $event) {
        $this->writeln($event->getFeature()->getFile() . '::' . self::$stepcount);
    }

    /**
     * Listens to "step.after" event.
     *
     * @param StepEvent $event
     */
    public function afterStep(StepEvent $event) {
        self::$stepcount++;
    }
}
