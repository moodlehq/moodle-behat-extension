<?php

namespace Moodle\BehatExtension\Context\Initializer;

use Moodle\BehatExtension\Context\MoodleContext,
    Behat\Behat\Context\ContextInterface,
    Behat\Behat\Context\Initializer\InitializerInterface;

/**
 * MoodleContext initializer
 *
 * @author    David Monllaó <david.monllao@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleAwareInitializer implements InitializerInterface
{
    private $config_file_path;

    /**
     * @param string $config_file_path
     */
    public function __construct($configFilePath)
    {
        $this->config_file_path = $configFilePath;
    }

    /**
     * @see Behat\Behat\Context\Initializer.InitializerInterface::supports()
     * @param ContextInterface $context
     */
    public function supports(ContextInterface $context)
    {
        return ($context instanceof MoodleContext);
    }

    /**
     * Passes the Moodle config file path to the main Moodle context
     * @see Behat\Behat\Context\Initializer.InitializerInterface::initialize()
     * @param ContextInterface $context
     */
    public function initialize(ContextInterface $context)
    {
        $context->setMoodleConfig($this->config_file_path);
    }
}