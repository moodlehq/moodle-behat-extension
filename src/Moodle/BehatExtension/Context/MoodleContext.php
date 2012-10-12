<?php

namespace Moodle\BehatExtension\Context;

use Behat\Behat\Context\BehatContext,
    Symfony\Component\Yaml\Yaml;

/**
 * Moodle contexts loader
 *
 * @uses       Symfony\Component\Yaml\Yaml
 * @copyright  2012 David Monllaó
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleContext extends BehatContext
{

    /**
     * Moodle features and steps definitions list
     * @var array
     */
    protected $moodleConfig;

    /**
     * Loads Moodle config file and includes all the specified subcontexts
     * @param string $configFilePath
     */
    public function setMoodleConfig($configFilePath)
    {
        $this->moodleConfig = Yaml::parse($configFilePath);

        // Using the key as context identifier.
        if (!empty($this->moodleConfig['steps_definitions']) && $this->moodleConfig['steps_definitions'] != '/') {
            foreach ($this->moodleConfig['steps_definitions'] as $componentname => $path) {
                $classname = 'behat_' . $componentname;
                require_once($path . '/' . $classname . '.php');
                $this->useContext($componentname, new $classname());
            }
        }
    }
}
