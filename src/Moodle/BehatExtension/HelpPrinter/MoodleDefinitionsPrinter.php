<?php

namespace Moodle\BehatExtension\HelpPrinter;

use Behat\Behat\Definition\DefinitionDispatcher;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * DefinitionsPrinter extension
 *
 * Allows step definition type filtering and
 * specifies a different template
 */
class MoodleDefinitionsPrinter
{

    private $dispatcher;

    public function __construct(DefinitionDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function printDefinitions(OutputInterface $output, $search = null, $language = 'en', $shortNotation = true)
    {
        $output->writeln($this->getMoodleDefinitionsForPrint($search, $language));
    }

    /**
     * Returns available definitions in string.
     *
     * @param string  $search        search string
     * @param string  $language      default definitions language
     *
     * @return string
     */
    private function getMoodleDefinitionsForPrint($search = null, $language = 'en')
    {

        $template = <<<TPL
<div class="step"><div class="stepdescription">{description}</div>
<div class="stepcontent"><span class="steptype">{type}</span><span class="stepregex">{regex}</span></div></div>

TPL;

        // If there is a specific type (given, when or then) required
        if (strpos($search, '&&') !== false) {
            list($search, $type) = explode('&&', $search);
        }

        $definitions = array();
        foreach ($this->dispatcher->getDefinitions() as $regex => $definition) {

            $description = $definition->getDescription();

            if (!empty($type) && strtolower($definition->getType()) != $type) {
                continue;
            }

            $regex = $this->dispatcher->translateDefinitionRegex($regex, $language);
            if ($search && !preg_match('/'.str_replace(' ', '.*', preg_quote($search, '/').'/'), $regex)) {
                continue;
            }

            // Leave the regexp as human-readable as possible
            $regex = substr($regex, 2, strlen($regex) - 4);   // Removing beginning and end
            // TODO preg_replace from regexps to <descriptor> values with fallback to "value"
            $regex = htmlentities($regex);

            $definitions[] = strtr($template, array(
                '{regex}'       => $regex,
                '{type}'        => str_pad($definition->getType(), 5, ' ', STR_PAD_LEFT),
                '{description}' => $description ? $description : ''
            ));
        }

        return implode("\n", $definitions);
    }
}
