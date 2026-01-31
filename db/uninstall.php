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
 * Code that is executed before the tables and data are dropped during the plugin uninstallation.
 *
 * @package     mod_coripodatacollection
 * @category    upgrade
 * @copyright   2024 Cordioli Davide cordiolidavide1@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom uninstallation procedure.
 */
function xmldb_coripodatacollection_uninstall() {

    global $DB;

    $adminprogettorole = $DB->get_record('role', ['name' => 'Admin progetto']);
    $valutatorerole = $DB->get_record('role', ['name' => 'Utente valutatore']);
    $dirigenterole = $DB->get_record('role', ['name' => 'Dirigente scolastico']);
    $insegnanterole = $DB->get_record('role', ['name' => 'Insegnante']);

    if (!empty($adminprogettorole)) {
        delete_role($adminprogettorole->id);
    }
    if (!empty($valutatorerole)) {
        delete_role($valutatorerole->id);
    }
    if (!empty($dirigenterole)) {
        delete_role($dirigenterole->id);
    }
    if (!empty($insegnanterole)) {
        delete_role($insegnanterole->id);
    }

    return true;
}
