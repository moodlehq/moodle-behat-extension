<?php

namespace Moodle\BehatExtension\Driver;

use Behat\Mink\Driver\Selenium2Driver as Selenium2Driver;

/**
 * Selenium2 driver extension to allow extra selenium capabilities restricted by behat/mink-extension.
 */
class MoodleSelenium2Driver extends Selenium2Driver
{

    /**
     * Dirty attribute to get the browser name; $browserName is private
     * @var string
     */
    protected static $browser;

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

        // This class is instantiated by the dependencies injection system so
        // prior to all of beforeSuite subscribers which will call getBrowser*()
        self::$browser = $browserName;
    }

    /**
     * Forwards to getBrowser() so we keep compatibility with both static and non-static accesses.
     *
     * @deprecated
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        if ($name == 'getBrowserName') {
            return self::getBrowser();
        }

        // Fallbacks calling the requested static method, we don't
        // even know if it exists or not.
        return call_user_func(array(self, $name), $arguments);
    }

    /**
     * Forwards to getBrowser() so we keep compatibility with both static and non-static accesses.
     *
     * @deprecated
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if ($name == 'getBrowserName') {
            return self::getBrowser();
        }

        // Fallbacks calling the requested instance method, we don't
        // even know if it exists or not.
        return call_user_func(array($this, $name), $arguments);
    }

    /**
     * Returns the browser being used.
     *
     * We need to know it:
     * - To show info about the run.
     * - In case there are differences between browsers in the steps.
     *
     * @static
     * @return string
     */
    public static function getBrowser()
    {
        return self::$browser;
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

    /**
     * Overwriten method to use our custom Syn library.
     *
     * Makes sure that the Syn event library has been injected into the current page,
     * and return $this for a fluid interface,
     *
     *     $this->withSyn()->executeJsOnXpath($xpath, $script);
     *
     * @return Selenium2Driver
     */
    protected function withSyn()
    {
        $hasSyn = $this->getWebDriverSession()->execute(array(
            'script' => 'return typeof window["Syn"]!=="undefined"',
            'args'   => array()
        ));

        if (!$hasSyn) {
            $synJs = file_get_contents(__DIR__.'/Selenium2/moodle_syn-min.js');
            $this->getWebDriverSession()->execute(array(
                'script' => $synJs,
                'args'   => array()
            ));
        }

        return $this;
    }

}
