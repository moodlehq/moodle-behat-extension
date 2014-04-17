<?php

namespace Moodle\BehatExtension\Tester;

use Behat\Behat\Tester\StepTester,
    Behat\Behat\Event\StepEvent,
    Behat\Behat\Definition\DefinitionInterface,
    Behat\Behat\Context\ContextInterface,
    Behat\Gherkin\Node\OutlineNode,
    Behat\Behat\Context\Step\SubstepInterface,
    Behat\Gherkin\Node\AbstractNode,
    Behat\Gherkin\Node\StepNode,
    Behat\Gherkin\Node\ScenarioNode;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\EventDispatcher\Event;

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
     * The text of the step to look for exceptions / debugging messages.
     */
    const EXCEPTIONS_STEP_TEXT = 'I look for exceptions';

    /**
     * Grrrrr, we can not overwrite the parent one.
     *
     * @var ContextInterface
     */
    private $moodlecontext;

    /**
     * Grrrrr, we can not overwrite the parent one.
     *
     * @var Event
     */
    private $moodledispatcher;

    /**
     * Grrrrr, we can not overwrite the parent one.
     *
     * @var ScenarioNode
     */
    private $moodlelogicalParent;

    /**
     * Tokens in case of running a outline example.
     *
     * @var array
     */
    private $tokens = array();

    /**
     * We only dispatch the after step event when a "final" step has been reached.
     *
     * @var bool
     */
    private $dispatchafterstep = false;

    /**
     * If a step fails we don't want more outputs.
     *
     * @var bool
     */
    private $failedstep = false;

    /**
     * Initializes tester.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->moodledispatcher  = $container->get('behat.event_dispatcher');
        parent::__construct($container);
    }

    /**
     * Sets run context.
     *
     * Grrrrr, we can not overwrite the parent one.
     *
     * @param ContextInterface $context
     */
    public function setContext(ContextInterface $context)
    {
        $this->moodlecontext = $context;
        parent::setContext($context);
    }

    /**
     * Sets logical parent of the step, which is always a ScenarioNode.
     *
     * Grrrrr, we can not overwrite the parent one.
     *
     * @param ScenarioNode $parent
     */
    public function setLogicalParent(ScenarioNode $parent)
    {
        $this->moodlelogicalParent = $parent;
        parent::setLogicalParent($parent);
    }

    /**
     * Sets the example tokens if they exists.
     *
     * @param array $tokens
     * @return void
     */
    public function setExampleTokens($tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * Visits & tests StepNode.
     *
     * Parent method overwriten to dispatch the afterStep event
     * only when the executed step was not a chained step.
     *
     * @param AbstractNode $step
     *
     * @return integer
     */
    public function visit(AbstractNode $step)
    {

        // executeStepsChainWithHooks() will mark it as true if necessary.
        $this->dispatchafterstep = false;
        $this->failedstep = false;

        $this->moodledispatcher->dispatch('beforeStep', new StepEvent(
            $step, $this->moodlelogicalParent, $this->moodlecontext
        ));
        $afterEvent = $this->executeStep($step);

        // Only if it is not a step with chained steps.
        if ($this->dispatchafterstep !== false) {
            $this->moodledispatcher->dispatch('afterStep', $afterEvent);
        }

        return $afterEvent->getResult();
    }

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

        // We set $this->dispatchafterstep to true when a step is in the lower level
        // but if a step is throwing an exception it doesn't arrive to the point where
        // we set dispatchafterstep to true and the event is not dispatched; here we
        // set it but we also check failredstep so the parent steps (in a chain) don't
        // continue dispatching the event.
        if ($afterEvent->getResult() !== StepEvent::PASSED && $this->failedstep === false) {
            $this->dispatchafterstep = true;
        }

        // Extra step, looking for a moodle exception, a debugging() message or a PHP debug message.
        $checkingStep = new StepNode('Then', self::EXCEPTIONS_STEP_TEXT, $step->getLine());
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

    /**
     * Executes provided step definition.
     *
     * We extended because executeStepsChain is private.
     *
     * @param StepNode            $step       step node
     * @param DefinitionInterface $definition step definition
     */
    protected function executeStepDefinition(StepNode $step, DefinitionInterface $definition)
    {
        $this->executeStepsChainWithHooks($step, $definition->run($this->moodlecontext));
    }

    /**
     * Executes steps chain (if there's one).
     *
     * Overwriten method to run behat hooks between chain steps.
     *
     * @param StepNode $step  step node
     * @param mixed    $chain chain
     *
     * @throws \Exception
     */
    private function executeStepsChainWithHooks(StepNode $step, $chain = null)
    {
        if (null === $chain) {

            // If there are no more chained steps below we will dispatch the
            // after step event, skipping the step that looks for exceptions here.
            if (strstr($step->getText(), self::EXCEPTIONS_STEP_TEXT) === false) {
                $this->dispatchafterstep = true;
            }
            return;
        }

        $chain = is_array($chain) ? $chain : array($chain);
        foreach ($chain as $chainItem) {
            if ($chainItem instanceof SubstepInterface) {

                $substepNode = $chainItem->getStepNode();
                $substepNode->setParent($step->getParent());

                // Replace by tokens when needed.
                if ($substepNode->getParent() instanceof OutlineNode) {
                    $substepNode = $substepNode->createExampleRowStep($this->tokens);
                }

                $this->dispatchafterstep = false;

                // Dispatch beforeStep event.
                $this->moodledispatcher->dispatch('beforeStep', new StepEvent(
                    $substepNode, $this->moodlelogicalParent, $this->moodlecontext
                ));

                $substepEvent = $this->executeStep($substepNode);

                // Dispatch afterStep event.
                if ($this->dispatchafterstep === true) {
                    $this->moodledispatcher->dispatch('afterStep', $substepEvent);
                    $this->dispatchafterstep = false;
                }

                // Here we mark the step as failed so parent steps in the chain
                // will not continue dispatching the afterStep event.
                if (StepEvent::PASSED !== $substepEvent->getResult()) {
                    $this->failedstep = true;
                    throw $substepEvent->getException();
                }

            } elseif (is_callable($chainItem)) {
                $this->executeStepsChainWithHooks($step, call_user_func($chainItem));
            }
        }

    }

}
