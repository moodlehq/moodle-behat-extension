<?php

namespace Moodle\BehatExtension\Driver;

use Behat\Mink\Session;
use Facebook\WebDriver\WebDriverKeys;
use Moodle\BehatExtension\Element\NodeElement;
use OAndreyev\Mink\Driver\WebDriver as UpstreamDriver;

/**
 * WebDriver Driver to allow extra selenium capabilities required by Moodle.
 */
class WebDriver extends UpstreamDriver
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
    public function __construct($browserName = 'firefox', $desiredCapabilities = null, $wdHost = 'http://localhost:4444/wd/hub', $moodleParameters = array()) {
        parent::__construct($browserName, $desiredCapabilities, $wdHost);

        // This class is instantiated by the dependencies injection system so
        // prior to all of beforeSuite subscribers which will call getBrowser*()
        self::$browser = $browserName;
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
    public static function getBrowserName() {
        return self::$browser;
    }

    /**
     * Post key on specified xpath.
     *
     * @param string $xpath
     */
    public function post_key($key, $xpath) {
        throw new \Exception('No longer used - please use keyDown and keyUp');
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($xpath, $value)
    {
        $element = $this->findElement($xpath);
        $elementName = strtolower($element->getTagName());

        if ('select' === $elementName) {
            $select = new WebDriverSelect($element);

            if (is_array($value)) {
                $select->deselectAll();
                foreach ($value as $option) {
                    $select->selectByValue($option);
                }

                return;
            }

            $select->selectByValue($value);

            return;
        }

        if ('input' === $elementName) {
            $elementType = strtolower($element->getAttribute('type'));

            if (in_array($elementType, array('submit', 'image', 'button', 'reset'))) {
                throw new DriverException(sprintf('Impossible to set value an element with XPath "%s" as it is not a select, textarea or textbox', $xpath));
            }

            if ('checkbox' === $elementType) {
                if ($element->isSelected() xor (bool) $value) {
                    $this->clickOnElement($element);
                }

                return;
            }

            if ('radio' === $elementType) {
                $radios = new WebDriverRadios($element);
                $radios->selectByValue($value);
                return;
            }

            if ('file' === $elementType) {
                $this->attachFile($xpath, $value);
                return;
            }

            // WebDriver does not support setting value in color inputs.
            // Each OS will show native color picker
            // See https://code.google.com/p/selenium/issues/detail?id=7650
            if ('color' === $elementType) {
                $this->executeJsOnElement($element, sprintf('return {{ELEMENT}}.value = "%s"', $value));
                return;
            }

            // using DateTimeFormat to detect local format
            // See https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/DateTimeFormat#Syntax
            if ('date' === $elementType) {
                $time = strtotime($value);
                if ($this->browserName === 'firefox') {
                    $this->executeJsOnElement($element, sprintf('return {{ELEMENT}}.valueAsNumber = %d', $time * 1000));
                    return;
                }

                $format = $this->getDateTimeFormatForRemoteDriver();
                $value = date($format, $time);
            }
        }

        $value = (string) $value;

        if (in_array($elementName, array('input', 'textarea'))) {
            $existingValueLength = strlen($element->getAttribute('value'));
            // Add the TAB key to ensure we unfocus the field as browsers are triggering the change event only
            // after leaving the field.
            $value = str_repeat(WebDriverKeys::BACKSPACE . WebDriverKeys::DELETE, $existingValueLength) . $value;
        }

        $element->sendKeys($value);

        // Trigger a change event
        $script = <<<EOF
{{ELEMENT}}.dispatchEvent(new Event("change", {
    bubbles: true,
    cancelable: false,
}));
EOF;

        $this->executeJsOnXpath($xpath, $script);
    }
}
