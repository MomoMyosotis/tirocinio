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
 * Code to be executed after the plugin's database scheme has been installed is defined here.
 *
 * @package     mod_coripodatacollection
 * @category    upgrade
 * @copyright   2024 Cordioli Davide cordiolidavide1@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_coripodatacollection_install() {

    global $DB;

    update_capabilities('mod_coripodatacollection');


    $studentroleid  = $DB->get_record('role', ['shortname' => 'student'], 'id');
    $studentcontextlevels = get_role_contextlevels($studentroleid->id);

    // Creation of needed roles.
    $adminprogetto = new stdClass();
    $adminprogetto->name = get_string('adminproject_role', 'mod_coripodatacollection');
    $adminprogetto->shortname = str_replace(' ', '', strtolower($adminprogetto->name));
    $adminprogetto->description = '';
    $adminprogetto->archetype = 'student';

    $adminprogetto->id = create_role($adminprogetto->name, $adminprogetto->shortname, $adminprogetto->description, $adminprogetto->archetype);
    reset_role_capabilities($adminprogetto->id);
    assign_capability('mod/coripodatacollection:projectadmin', 1, $adminprogetto->id, 1);
    set_role_contextlevels($adminprogetto->id, $studentcontextlevels);


    $utentevalutatore = new stdClass();
    $utentevalutatore->name = get_string('evaluator_role', 'mod_coripodatacollection');
    $utentevalutatore->shortname = str_replace(' ', '', strtolower($utentevalutatore->name));
    $utentevalutatore->description = '';
    $utentevalutatore->archetype = 'student';

    $utentevalutatore->id = create_role($utentevalutatore->name, $utentevalutatore->shortname, $utentevalutatore->description, $utentevalutatore->archetype);
    reset_role_capabilities($utentevalutatore->id);
    assign_capability('mod/coripodatacollection:evaluator', 1, $utentevalutatore->id, 1);
    set_role_contextlevels($utentevalutatore->id, $studentcontextlevels);


    $dirigentescolastico = new stdClass();
    $dirigentescolastico->name = get_string('institutemanager_role', 'mod_coripodatacollection');
    $dirigentescolastico->shortname = str_replace(' ', '', strtolower($dirigentescolastico->name));
    $dirigentescolastico->description = '';
    $dirigentescolastico->archetype = 'student';

    $dirigentescolastico->id = create_role($dirigentescolastico->name, $dirigentescolastico->shortname, $dirigentescolastico->description, $dirigentescolastico->archetype);
    reset_role_capabilities($dirigentescolastico->id);
    assign_capability('mod/coripodatacollection:schoolmanager', 1, $dirigentescolastico->id, 1);
    set_role_contextlevels($dirigentescolastico->id, $studentcontextlevels);


    $insegnante = new stdClass();
    $insegnante->name = get_string('teacher_role', 'mod_coripodatacollection');
    $insegnante->shortname = str_replace(' ', '', strtolower($insegnante->name));
    $insegnante->description = '';
    $insegnante->archetype = 'student';

    $insegnante->id = create_role($insegnante->name, $insegnante->shortname, $insegnante->description, $insegnante->archetype);
    reset_role_capabilities($insegnante->id);
    assign_capability('mod/coripodatacollection:teacher', 1, $insegnante->id, 1);
    set_role_contextlevels($insegnante->id, $studentcontextlevels);

    $operatore = new stdClass();
    $operatore->name = get_string('operator', 'mod_coripodatacollection');
    $operatore->shortname = str_replace(' ', '', strtolower($operatore->name));
    $operatore->description = '';
    $operatore->archetype = 'student';

    $operatore->id = create_role($operatore->name, $operatore->shortname, $operatore->description, $operatore->archetype);
    reset_role_capabilities($operatore->id);
    assign_capability('mod/coripodatacollection:operator', 1, $operatore->id, 1);
    set_role_contextlevels($operatore->id, $studentcontextlevels);
}
