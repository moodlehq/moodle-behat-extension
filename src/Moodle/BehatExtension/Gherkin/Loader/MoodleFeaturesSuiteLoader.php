<?php

namespace Moodle\BehatExtension\Gherkin\Loader;

use Behat\Behat\Gherkin\Loader\FeatureSuiteLoader;

/**
 * FeaturesSuiteExtension to load multiple features folders
 *
 * Moodle has multiple features folders across all Moodle
 * components (including 3rd party plugins) this extension loads
 * a file from moodle codebase with all the folders with features inside
 *
 * @todo      Extend load()
 * @author    David Monllaó <david.monllao@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleFeaturesSuiteLoader extends FeatureSuiteLoader
{
}