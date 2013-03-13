<?php

namespace Moodle\BehatExtension\Driver;

use Behat\Mink\Driver\Selenium2Driver as Selenium2Driver;

/**
 * Selenium2 driver extension to allow extra selenium capabilities restricted by behat/mink-extension.
 */
class MoodleSelenium2Driver extends Selenium2Driver
{

    /**
     * Instantiates the driver.
     *
     * @param string    $browser Browser name
     * @param array     $desiredCapabilities The desired capabilities
     * @param string    $wdHost The WebDriver host
     * @param array     $moodleParameters Moodle parameters including our non-behat-friendly selenium capabilities
     */
    public function __construct($browserName = 'firefox', $desiredCapabilities = null, $wdHost = 'http://localhost:4444/wd/hub', $moodleParameters = false)
    {

        // If they are set add them overridding if it's the case (not likely).
        if (!empty($moodleParameters) && !empty($moodleParameters['capabilities'])) {
            foreach ($moodleParameters['capabilities'] as $key => $capability) {
                $desiredCapabilities[$key] = $capability;
            }
        }

        parent::__construct($browserName, $desiredCapabilities, $wdHost);
    }
}
