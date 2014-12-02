<?php

/**
 * This file is part of the Behat
 *
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

spl_autoload_register(function($class) {
    if (false !== strpos($class, 'Moodle\\BehatExtension')) {
        require_once(__DIR__.'/src/'.str_replace('\\', '/', $class).'.php');
        return true;
    }
    if (false !== strpos($class, 'Behat\\Mink\\Driver\\Selenium2Driver')) {
        require_once(__DIR__.'/src/Moodle/BehatExtension/Driver/MoodleSelenium2Driver.php');
        return true;
    }
}, true, false);

return new Moodle\BehatExtension\ServiceContainer\BehatExtension;
