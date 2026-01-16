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

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');
require_once('../../../user/lib.php');
require_once('../../../user/profile/lib.php');
require_once('../../../course/lib.php');
require_once('../../../course/modlib.php');

$classes = $DB->get_records('coripodatacollection_classes', ['erogazione' => 13]);
foreach ($classes as $class) {

    if ($class->statistichepre == 1) {
        $class->statistichepre = 0;
        $DB->update_record('coripodatacollection_classes', $class);
    }

}
