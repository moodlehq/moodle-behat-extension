moodle-behat-extension
======================

Behat extension for Moodle to get features and steps definitions from different moodle components; it basically allows multiple features folders and helps with the contexts spreads across components of an external app.

Following custom formats are supported.
======================================
* **moodle_progress**: Prints Moodle branch information and dots for each step.
* **moodle_list**: List all scenarios.
* **moodle_stepcount**: List all features with total steps in each feature file. Used for parallel run.
* **moodle_screenshot**: Take screenshot and core dump of each step. With following options you can dump either or both.
  * **--format-settings '{"formats": "image"}'**: will dump image only
  * **--format-settings '{"formats": "html"}'**: will dump html only.
  * **--format-settings '{"formats": "html,image"}'**: will dump both.
  * **--format-settings '{"formats": "html", "dir_permissions": "0777"}'**

Contributing
============

http://docs.moodle.org/dev/Acceptance_testing/Contributing_to_Moodle_behat_extension

Upgrade from moodle-behat-extension 1.31.x to 3.31.0
====================================================
* Chained steps are not natively supported by behat 3.
  * You should either replace Behat\Behat\Context\Step\Given with Behat\Behat\Context\Step\Given;
  * or use behat_context_helper::get('BEHAT_CONTEXT_CLASS'); and call api to execute the step.
* named selectors are deprecated, use named_exact or named_partial instead.
* Failed steps are cached for rerun and doesn't require an empty file to save failed scenarios.
