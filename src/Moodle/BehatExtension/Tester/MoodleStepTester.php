<?php

namespace Moodle\BehatExtension\Tester;

use Behat\Behat\Tester\StepTester,
    Behat\Behat\Event\StepEvent,
    Behat\Gherkin\Node\StepNode;

/**
 * StepTester extension to look for exceptions after each step.
 *
 * This can not be implemented as a after step hook as exceptions throw
 * there are considered framework level exceptions, so behat execution
 * is stopped and the logged data is lost. We want to capture exceptions
 * and consider them as a failed tests.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleStepTester extends StepTester
{

    /**
     * Searches and runs provided step delegating all the process to the parent class
     *
     * Method overwritten to look for:
     * - Moodle exceptions
     * - Moodle debugging() calls
     * - PHP debug messages (depends on the PHP debug level)
     *
     * @param StepNode $step step node
     * @return StepEvent
     */
    protected function executeStep(StepNode $step)
    {
        // Redirect to the parent to run the step.
        $afterEvent = parent::executeStep($step);

        // Extra step, looking for a moodle exception, a debugging() message or a PHP debug message.
        $checkingStep = new StepNode('Then', 'I look for exceptions', $step->getLine());
        $afterExceptionCheckingEvent = parent::executeStep($checkingStep);

        // If it find something wrong we overwrite the original step result.
        if ($afterExceptionCheckingEvent->getResult() == StepEvent::FAILED) {

            // Creating a mix of both StepEvents to report about it as a failure in the original step.
            $afterEvent = new StepEvent(
                $afterEvent->getStep(),
                $afterEvent->getLogicalParent(),
                $afterEvent->getContext(),
                $afterExceptionCheckingEvent->getResult(),
                $afterEvent->getDefinition(),
                $afterExceptionCheckingEvent->getException(),
                null
            );
        }

        return $afterEvent;
    }
}
