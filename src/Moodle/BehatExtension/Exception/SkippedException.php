<?php

namespace Moodle\BehatExtension\Exception;

use Behat\Behat\Exception;

/**
 * Skipped exception (throw this to mark step as "skipped").
 *
 * @author Jerome Mouneyrac
 */
class SkippedException extends Exception\BehaviorException{}
