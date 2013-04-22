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

    /**
     * Drag one element onto another.
     *
     * Override the original one to give YUI drag & drop
     * time to consider it a valid drag & drop. It will need
     * more changes in future to properly adapt to how YUI dd
     * component behaves.
     *
     * @param   string  $sourceXpath
     * @param   string  $destinationXpath
     */
    public function dragTo($sourceXpath, $destinationXpath)
    {
        $source      = $this->getWebDriverSession()->element('xpath', $sourceXpath);
        $destination = $this->getWebDriverSession()->element('xpath', $destinationXpath);

        // TODO: MDL-39727 This method requires improvements according to the YUI drag and drop component.

        $this->getWebDriverSession()->moveto(array(
            'element' => $source->getID()
        ));

        $script = <<<JS
(function (element) {
    var event = document.createEvent("HTMLEvents");

    event.initEvent("dragstart", true, true);
    event.dataTransfer = {};

    element.dispatchEvent(event);
}({{ELEMENT}}));
JS;
        $this->withSyn()->executeJsOnXpath($sourceXpath, $script);

        $this->getWebDriverSession()->buttondown();
        $this->getWebDriverSession()->moveto(array(
            'element' => $destination->getID()
        ));

        // We add a 2 seconds wait to make YUI dd happy.
        $this->wait(2 * 1000, false);

        $this->getWebDriverSession()->buttonup();

        $script = <<<JS
(function (element) {
    var event = document.createEvent("HTMLEvents");

    event.initEvent("drop", true, true);
    event.dataTransfer = {};

    element.dispatchEvent(event);
}({{ELEMENT}}));
JS;
        $this->withSyn()->executeJsOnXpath($destinationXpath, $script);
    }
}
