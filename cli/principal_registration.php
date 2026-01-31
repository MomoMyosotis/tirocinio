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


$istituti_erog = $DB->get_records('coripodatacollection_istituti_x_progetto_x_aa', ['erogation' => 13]);
$erogation = $DB->get_record('coripodatacollection_erogations', ['id' => 13]);

foreach ($istituti_erog as $istituto_erog) {

    $istituto = $DB->get_record('coripodatacollection_istituti', ['id' => $istituto_erog->instituteid]);
    $project = $DB->get_record('coripodatacollection_projects', ['id' => $istituto_erog->projectid]);

    if ($istituto->cognome_direttore != 'Fichera' and $istituto->cognome_direttore != 'Venti') {
        continue;
    }

    $newuser = new stdClass();
    $newuser->firstname = $istituto->nome_direttore;
    $newuser->lastname = $istituto->cognome_direttore;
    $newuser->username = strtolower(
            preg_replace('/[^a-zA-Z]/', '', $newuser->firstname)
            . '.' . preg_replace('/[^a-zA-Z]/', '', $newuser->lastname)
    );

    $counter = 0;
    $username_test = $newuser->username;
    while ($DB->count_records('user', ['username' => $username_test]) != 0) {
        $counter = $counter + 1;
        $username_test = $newuser->username . $counter;
    }
    if ($counter > 0) {
        $newuser->username = $username_test;
    }

    $newuser->email = $istituto->email_direttore;

    $caratteri = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $passwordCasuale = '';
    $maxIndex = strlen($caratteri) - 1;
    for ($i = 0; $i < 10; $i++) {
        $passwordCasuale .= $caratteri[random_int(0, $maxIndex)];
    }
    $newuser->password = hash_internal_user_password($passwordCasuale);

    $newuser->confirmed = 1;
    $newuser->mnethostid = 1;
    $newuser->timecreated = time();
    $newuser->timemodified = $newuser->timecreated;

    $newuser->id = user_create_user($newuser);
    profile_save_data($newuser);
    $newuser = $DB->get_record('user', ['id' => $newuser->id]);
    send_newuser_mail($newuser, $passwordCasuale);
    unset_user_preference('create_password', $newuser);
    set_user_preference('auth_forcepasswordchange', 1, $newuser);
    \core\event\user_created::create_from_userid($newuser->id)->trigger();

    $newinstituteadmin = new stdClass();
    $newinstituteadmin->userid = $newuser->id;
    $newinstituteadmin->instituteid = $istituto->id;
    $DB->insert_record('coripodatacollection_instituteadmin', $newinstituteadmin);

    $newinstituteprincipal = new stdClass();
    $newinstituteprincipal->userid = $newuser->id;
    $newinstituteprincipal->instituteid = $istituto->id;
    $DB->insert_record('coripodatacollection_principals', $newinstituteprincipal);

    $roleid = $DB->get_record_sql('SELECT id FROM {role} WHERE name = "Dirigente scolastico"');
    role_assign($roleid->id, $newuser->id, 1);
    $studentrole = $DB->get_record('role', ['shortname' => 'student']);
    enrol_try_internal_enrol($erogation->courseid, $newuser->id, $studentrole->id);

    $user = $DB->get_record('user', ['id' => $newuser->id]);
    $supportuser = core_user::get_support_user();
    $subject = get_string('add_project_principal_object', 'mod_coripodatacollection');
    $body = sprintf(
            get_string('add_project_principal_body', 'mod_coripodatacollection'),
            $user->lastname . ' ' . $user->firstname,
            $project->projectname,
            $istituto->denominazioneistituto,
            $SITE->fullname,
            $CFG->wwwroot . '/login/index.php'
    );
    email_to_user($user, $supportuser, $subject, $body);

    echo '   Registrato dirigente ' . $istituto->cognome_direttore;

}