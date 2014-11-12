<?php

namespace Moodle\BehatExtension\Tester;

use Behat\Behat\Tester\ScenarioTester;

use Behat\Gherkin\Node\ScenarioNode,
    Behat\Gherkin\Node\StepNode,
    Behat\Gherkin\Node\BackgroundNode;

use Behat\Behat\Context\ContextInterface;


/**
 * ScenarioTester extension.
 *
 * Allows outlines to pass it's examples
 * to the step tester so chained steps
 * can correctly display the used tokens.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleScenarioTester extends ScenarioTester
{

    /**
     * @var bool ScenarioTester::skip is marked as private.
     */
    private $moodleskip;

    /**
     * Sets tester to dry-run mode.
     *
     * Extended to set an attribute that
     * MoodleScenarioTester can access.
     *
     * @param Boolean $skip
     */
    public function setSkip($skip = true)
    {
        $this->skip = (bool) $skip;
        $this->moodleskip = $this->skip;
    }

    /**
     * Visits & tests StepNode.
     *
     * Simple ScenarioTester::visitStep() extension just
     * calling StepTester::setExampleTokens()
     *
     * @param StepNode         $step          step instance
     * @param ScenarioNode     $logicalParent logical parent of the step
     * @param ContextInterface $context       context instance
     * @param array            $tokens        step replacements for tokens
     * @param boolean          $skip          mark step as skipped?
     *
     * @see StepTester::visit()
     *
     * @return integer
     */
    protected function visitStep(StepNode $step, ScenarioNode $logicalParent,
                                 ContextInterface $context, array $tokens = array(), $skip = false)
    {
        if ($logicalParent instanceof OutlineNode) {
            $step = $step->createExampleRowStep($tokens);
        }

        $tester = $this->container->get('behat.tester.step');
        $tester->setLogicalParent($logicalParent);
        $tester->setContext($context);
        $tester->skip($skip || $this->moodleskip);

        // Attaching tokens for chained steps.
        $tester->setExampleTokens($tokens);

        return $step->accept($tester);
    }

    /**
     * Visits & tests BackgroundNode.
     *
     * @param BackgroundNode   $background
     * @param ScenarioNode     $logicalParent
     * @param ContextInterface $context
     *
     * @see BackgroundTester::visit()
     *
     * @return integer
     */
    protected function visitBackground(BackgroundNode $background, ScenarioNode $logicalParent,
                                       ContextInterface $context)
    {
        $tester = $this->container->get('behat.tester.background');
        $tester->setLogicalParent($logicalParent);
        $tester->setContext($context);
        $tester->setSkip($this->moodleskip);

        return $background->accept($tester);
    }
}
