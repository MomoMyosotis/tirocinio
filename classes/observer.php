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
 * Plugin strings are defined here.
 *
 * @package     mod_coripodatacollection
 * @category    string
 * @copyright   2024 Cordioli Davide cordiolidavide1@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for mod_coripodatacollection.
 */
class mod_coripodatacollection_observer {

    /**
     * Triggered via course_deleted event.
     *
     * @param \core\event\course_deleted $event
     */
    public static function course_deleted(\core\event\course_deleted $event): void {

        global $DB;
        $erogation = $DB->get_record('coripodatacollection_erogations', ['courseid' => $event->courseid]);
        $classes = $DB->get_records('coripodatacollection_classes', ['erogazione' => $erogation->id]);
        $DB->delete_records('coripodatacollection_istituti_x_progetto_x_aa', ['erogation' => $erogation->id]);
        $DB->delete_records('coripodatacollection_classes', ['erogazione' => $erogation->id]);

        foreach ($classes as $class) {
            $DB->delete_records('coripodatacollection_class_students', ['classid' => $class->id]);
            $DB->delete_records('coripodatacollection_risultati', ['classe' => $class->id]);
            $DB->delete_records('coripodatacollection_medie_risultati', ['classe' => $class->id]);
            $DB->delete_records('coripodatacollection_stddev_risultati', ['classe' => $class->id]);
            $DB->delete_records('coripodatacollection_stddev_alunno', ['classe' => $class->id]);
            $DB->delete_records('coripodatacollection_classhistory', ['classid' => $class->id]);
        }
        $DB->delete_records('coripodatacollection_erogations', ['courseid' => $event->courseid]);
    }
}