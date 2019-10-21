<?php
// This file is part of the Moodle Behat extension - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Node Element.
 *
 * @package    moodlehq-behat-extension
 * @copyright  2019 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodle\BehatExtension\Element;

use Behat\Mink\Element\NodeElement as MinkNodeElement;

/**
 * Node Element.
 *
 * @package    moodlehq-behat-extension
 * @copyright  2019 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class NodeElement extends MinkNodeElement {
    /**
     * Click the current node.
     *
     * This implementation includes a fix for Firefox on Ubuntu 1804 to ensure that the clicked node is visible before
     * it is clicked.
     */
    public function click() {
        $browser = \Moodle\BehatExtension\Driver\MoodleSelenium2Driver::getBrowser();
        if (in_array(strtolower($browser), ['firefox'])) {
            $this->ensure_node_is_in_viewport();
        }

        parent::click();
    }


    /**
     * Attempt to ensure that the node is in the viewport given a margin of error.
     *
     * This ensures that the top is below the top of the viewport, and the bottom is above the bottom of the viewport.
     * It does not handle the following cases:
     * - Where the node is bigger than the viewport
     * - Where the node is covered by another element.
     * - Where the node is to the left or right of the viewport
     *
     * @param int $margin A margin aroudn th
     */
    public function ensure_node_is_in_viewport(int $margin = 20) {
        $xpath = $this->getXPath();
        $xpathsubs = [
            "\r\n" => " ",
            "\r" => " ",
            "\n" => " ",
            '"' => '\"',
        ];
        $xpath = str_replace(array_keys($xpathsubs), array_values($xpathsubs), $this->getXpath());
        // Fetch the node by xpath, then check that the top is below the top of the document, and the bottom is below
        // the bottom of the document.
        // Note: This does not handle the case where the item being clicked is bigger than the window. Nor does it
        // handle the case where the node is covered by another element.
        $js = <<<EOF
(function() {
    var node = document.evaluate("{$xpath}", document, null, XPathResult.ANY_TYPE, null).iterateNext();
    function isVisible(element) {
        var margin = {$margin};
        var rect = element.getBoundingClientRect();
        var viewHeight = Math.max(document.documentElement.clientHeight, window.innerHeight);
        return !(rect.top < 0 + margin || (rect.bottom - viewHeight >= 0 - margin));
    }

    if (node && !isVisible(node)) {
        node.scrollIntoView();
    }
})();
EOF;
        $this->getSession()->executeScript($js);
    }
}
