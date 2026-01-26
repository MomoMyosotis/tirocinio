<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Prints an instance of mod_coripodatacollection.
 *
 * @package     mod_coripodatacollection
 * @copyright   2024 Cordioli Davide cordiolidavide1@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$c = optional_param('c', 0, PARAM_INT);

// Page to visualize.
$page = optional_param('page', '', PARAM_TEXT);

if ($id) {
    $cm = get_coursemodule_from_id('coripodatacollection', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('coripodatacollection', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('coripodatacollection', ['id' => $c], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $moduleinstance->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('coripodatacollection', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$PAGE->set_url('/mod/coripodatacollection/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$PAGE->add_body_class('mediumwidth');

$context = context_system::instance();
require_capability('mod/data:viewentry', $context);

if (has_capability('mod/coripodatacollection:projectadmin', $context)) {
    redirect(new moodle_url('/mod/coripodatacollection/viewproject.php', ['id' => $cm->id]));
} elseif (has_capability('mod/coripodatacollection:evaluator', $context)) {
    redirect(new moodle_url('/mod/coripodatacollection/viewevaluator.php', ['id' => $cm->id]));
} elseif (has_capability('mod/coripodatacollection:schoolmanager', $context)) {
    redirect(new moodle_url('/mod/coripodatacollection/viewdirector.php', ['id' => $cm->id]));
} elseif (has_capability('mod/coripodatacollection:teacher', $context)) {
    redirect(new moodle_url('/mod/coripodatacollection/viewteacher.php', ['id' => $cm->id]));
} elseif (has_capability('mod/coripodatacollection:operator', $context)) {
    redirect(new moodle_url('/mod/coripodatacollection/viewoperatore.php', ['id' => $cm->id]));
} else {
    redirect(new moodle_url('my'));
}
