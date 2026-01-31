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

require_once('../../config.php');
require_once('lib.php');
require_once('../../user/lib.php');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'mod_coripodatacollection'));

require_login();
if (!has_capability('mod/coripodatacollection:projectadmin', $context)) {
    require_capability('mod/coripodatacollection:schoolmanager', $context);
    $projectid = optional_param('projectid', -1,PARAM_INT);
    if ($projectid !== -1) {
        require_capability('mod/coripodatacollection:projectadmin', $context);
    }
} else {
    $projectid = required_param('projectid', PARAM_INT);
}

$visualizepage = optional_param('page', '', PARAM_TEXT);
$istituteid = optional_param('istituteid', -1, PARAM_INT);
$institute = $DB->get_record('coripodatacollection_istituti', ['id' => $istituteid]);

if ($visualizepage == '') {
    $PAGE->set_url(new moodle_url('/mod/coripodatacollection/instituteadmin.php'),
            ['istituteid' => $istituteid, 'projectid' => $projectid]);
} else {
    $PAGE->set_url(new moodle_url('/mod/coripodatacollection/instituteadmin.php',
            ['page' => $visualizepage, 'istituteid' => $istituteid, 'projectid' => $projectid]));
}

if ($istituteid == -1){
    $PAGE->set_heading(get_string('institutes_admin_overview', 'mod_coripodatacollection'));
} else {
    if ($projectid !== -1) {
        $project = $DB->get_record('coripodatacollection_projects',['id' => $projectid]);
        $PAGE->set_heading($project->projectname . ' -- ' . $institute->denominazioneistituto);
    } else {
        $PAGE->set_heading($institute->denominazioneistituto);
    }
}
$PAGE->add_body_class('mediumwidth');


// Eliminazione di un plesso.
if ($visualizepage == 'delplex') {
    $plexid = required_param('plexid', PARAM_INT);
    $DB->delete_records('coripodatacollection_plessi', ['id' => $plexid]);
    $institute->numerodiplessi = count($DB->get_records('coripodatacollection_plessi',
            ['instituteid' => $institute->id]));
    $DB->update_record('coripodatacollection_istituti', $institute);
    redirect(new moodle_url('/mod/coripodatacollection/instituteadmin.php',
            ['page' => 'institute', 'istituteid' => $istituteid, 'projectid' => $projectid]));
}



// Rimozione di un insegnante.
if ($visualizepage == 'delteacher') {
    $userid = required_param('userid', PARAM_INT);
    $teacher = $DB->get_record('coripodatacollection_teachers', ['userid' => $userid, 'instituteid' => $institute->id]);
    $teacher->instituteid = null;
    $DB->update_record('coripodatacollection_teachers', $teacher);

    redirect(new moodle_url('/mod/coripodatacollection/instituteadmin.php',
            ['page' => 'insegnanti', 'istituteid' => $istituteid, 'projectid' => $projectid]));
}



// Codice per piccolo form per cercare insegnante.
if($visualizepage == 'insegnanti') {
    $url = new moodle_url('/mod/coripodatacollection/instituteadmin.php',
            ['page' => 'insegnanti', 'istituteid' => $istituteid, 'projectid' => $projectid]);
    $addteacher = new \mod_coripodatacollection\forms\searchentities_form($url, ['id' => $institute->id, 'teachers' => true]);
    if ($data = $addteacher->get_data()) {

        if (!empty($data->searchentity)) {
            foreach ($data->searchentity as $d) {

                $teacher = $DB->get_record('coripodatacollection_teachers', ['userid' => $d, 'instituteid' => null], '*', IGNORE_MULTIPLE);
                if (!empty($teacher)) {
                    $teacher->instituteid = $institute->id;
                    $DB->update_record('coripodatacollection_teachers', $teacher);
                } else {
                    $teacher = new stdClass();
                    $teacher->userid = $d;
                    $teacher->instituteid = $institute->id;
                    $DB->insert_record('coripodatacollection_teachers', $teacher);
                }

                $roleid = $DB->get_record_sql('SELECT id FROM {role} WHERE name = "Insegnante"');
                role_assign($roleid->id, $teacher->userid, 1);

                $user = $DB->get_record('user', ['id' => $teacher->userid]);
                $supportuser = core_user::get_support_user();
                $subject = get_string('add_istitute_teacher_object', 'mod_coripodatacollection');
                $body = sprintf(
                        get_string('add_istitute_teacher_body', 'mod_coripodatacollection'),
                        $user->lastname . ' ' . $user->firstname,
                        $institute->denominazioneistituto,
                        $SITE->fullname,
                        $CFG->wwwroot . '/login/index.php'
                );
                email_to_user($user, $supportuser, $subject, $body);

            }
            redirect($url);
        }
    }
}



// Codice per aggiunta di un nuovo plesso.
if ($visualizepage == 'nuovoplesso') {
    $url = new moodle_url('/mod/coripodatacollection/instituteadmin.php?',
            ['page' => 'nuovoplesso', 'istituteid' => $istituteid, 'projectid' => $projectid]);
    $newplexform = new mod_coripodatacollection\forms\instituteplexes_form($url, ['instituteid' => $institute->id]);
    if ($newplexform->is_cancelled()) {
        redirect(new moodle_url('/mod/coripodatacollection/instituteadmin.php',
                ['page' => 'institute', 'istituteid' => $istituteid, 'projectid' => $projectid]));
    } else if ($data = $newplexform->get_data()) {

        $newplex = new stdClass();
        $newplex->instituteid = required_param('instituteid', PARAM_INT);
        $newplex->denominazioneplesso = required_param('denominazioneplesso', PARAM_TEXT);
        $newplex->indirizzo = required_param('indirizzo', PARAM_ALPHANUMEXT);
        $DB->insert_record('coripodatacollection_plessi', $newplex);

        $institute->numerodiplessi = count($DB->get_records('coripodatacollection_plessi',
                ['instituteid' => $newplex->instituteid]));
        $DB->update_record('coripodatacollection_istituti', $institute);

        redirect(new moodle_url('/mod/coripodatacollection/instituteadmin.php',
                ['page' => 'institute', 'istituteid' => $istituteid, 'projectid' => $projectid]));
    }
}



// Codice per aggiunta di un nuovo insegnante.
if ($visualizepage == 'nuovoinsegnante') {
    $url = new moodle_url('/mod/coripodatacollection/instituteadmin.php?',
            ['page' => 'nuovoinsegnante', 'istituteid' => $istituteid, 'projectid' => $projectid]);
    $newteachgerform = new mod_coripodatacollection\forms\projectnewuser_form($url, ['id' => $institute->id]);
    if ($newteachgerform->is_cancelled()) {
        redirect(new moodle_url('/mod/coripodatacollection/instituteadmin.php',
                ['page' => 'insegnanti', 'istituteid' => $istituteid, 'projectid' => $projectid]));
    } else if ($data = $newteachgerform->get_data()) {

        $newuser = new stdClass();
        $newuser->firstname = required_param('newuser_name', PARAM_TEXT);
        $newuser->lastname = required_param('newuser_surname', PARAM_TEXT);
        $newuser->username = strtolower(
                preg_replace('/[^a-zA-Z]/', '', $newuser->firstname)
                . '.' . preg_replace('/[^a-zA-Z]/', '', $newuser->lastname)
        );

        $counter = 0;
        $username_test = $newuser->username;
        while ($DB->count_records('user', ['username' => $username_test])!=0){
            $counter = $counter + 1;
            $username_test = $newuser->username . $counter;
        }
        if ($counter > 0) {
            $newuser->username = $username_test;
        }

        $newuser->email = required_param('newuser_email', PARAM_TEXT);
        $newuser->password = hash_internal_user_password(required_param('newuser_password', PARAM_TEXT));

        $newuser->confirmed = 1;
        $newuser->mnethostid = 1;
        $newuser->timecreated = time();
        $newuser->timemodified = $newuser->timecreated;

        $newuser->id = user_create_user($newuser, false, false);
        profile_save_data($newuser);
        $newuser = $DB->get_record('user', ['id' => $newuser->id]);
        send_newuser_mail($newuser, $data->newuser_password);
        unset_user_preference('create_password', $newuser);
        set_user_preference('auth_forcepasswordchange', 1, $newuser);
        \core\event\user_created::create_from_userid($newuser->id)->trigger();

        $newteachr = new stdClass();
        $newteachr->instituteid = required_param('id', PARAM_INT);
        $newteachr->userid = $newuser->id;

        $DB->insert_record('coripodatacollection_teachers', $newteachr);

        $roleid = $DB->get_record_sql('SELECT id FROM {role} WHERE name = "Insegnante"');
        role_assign($roleid->id, $newuser->id, 1);

        $user = $DB->get_record('user', ['id' => $newteachr->userid]);
        $supportuser = core_user::get_support_user();
        $subject = get_string('add_istitute_teacher_object', 'mod_coripodatacollection');
        $body = sprintf(
                get_string('add_istitute_teacher_body', 'mod_coripodatacollection'),
                $user->lastname . ' ' . $user->firstname,
                $institute->denominazioneistituto,
                $SITE->fullname,
                $CFG->wwwroot . '/login/index.php'
        );
        email_to_user($user, $supportuser, $subject, $body);

        redirect(new moodle_url('/mod/coripodatacollection/instituteadmin.php',
                ['page' => 'insegnanti', 'istituteid' => $istituteid, 'projectid' => $projectid]));
    }
}



// Codice per aggiunta di un nuovo amministratore.
if ($visualizepage == 'newadmin') {
    $url = new moodle_url('/mod/coripodatacollection/instituteadmin.php?',
            ['page' => 'newadmin', 'istituteid' => $istituteid, 'projectid' => $projectid]);
    $newadminform = new mod_coripodatacollection\forms\projectnewuser_form($url, ['id' => $institute->id]);
    if ($newadminform->is_cancelled()) {
        redirect(new moodle_url('/mod/coripodatacollection/instituteadmin.php',
                ['page' => 'admins', 'istituteid' => $istituteid, 'projectid' => $projectid]));
    } else if ($data = $newadminform->get_data()) {

        $newuser = new stdClass();
        $newuser->firstname = required_param('newuser_name', PARAM_TEXT);
        $newuser->lastname = required_param('newuser_surname', PARAM_TEXT);
        $newuser->username = strtolower(
                preg_replace('/[^a-zA-Z]/', '', $newuser->firstname)
                . '.' . preg_replace('/[^a-zA-Z]/', '', $newuser->lastname)
        );

        $counter = 0;
        $username_test = $newuser->username;
        while ($DB->count_records('user', ['username' => $username_test])!=0){
            $counter = $counter + 1;
            $username_test = $newuser->username . $counter;
        }
        if ($counter > 0) {
            $newuser->username = $username_test;
        }

        $newuser->email = required_param('newuser_email', PARAM_TEXT);
        $newuser->password = hash_internal_user_password(required_param('newuser_password', PARAM_TEXT));

        $newuser->confirmed = 1;
        $newuser->mnethostid = 1;
        $newuser->timecreated = time();
        $newuser->timemodified = $newuser->timecreated;

        $newuser->id = user_create_user($newuser, false, false);
        profile_save_data($newuser);
        $newuser = $DB->get_record('user', ['id' => $newuser->id]);
        send_newuser_mail($newuser, $data->newuser_password);
        unset_user_preference('create_password', $newuser);
        set_user_preference('auth_forcepasswordchange', 1, $newuser);
        \core\event\user_created::create_from_userid($newuser->id)->trigger();

        $newadmin = new stdClass();
        $newadmin->instituteid = required_param('id', PARAM_INT);
        $newadmin->userid = $newuser->id;

        $DB->insert_record('coripodatacollection_instituteadmin', $newadmin);

        $roleid = $DB->get_record_sql('SELECT id FROM {role} WHERE name = "Dirigente scolastico"');
        role_assign($roleid->id, $newuser->id, 1);

        $erogazioni_istituto = $DB->get_records('coripodatacollection_istituti_x_progetto_x_aa',
                ['instituteid' => $institute->id]);
        foreach ($erogazioni_istituto as $erog_ist) {
            $erogazione = $DB->get_record('coripodatacollection_erogations', ['id' =>$erog_ist->erogation]);
            $context = context_course::instance($erogazione->courseid);
            if (!is_enrolled($context, $newadmin->userid)) {
                $studentrole = $DB->get_record('role', ['shortname' => 'student']);
                enrol_try_internal_enrol($erogazione->courseid, $newadmin->userid, $studentrole->id);
            }
        }

        redirect(new moodle_url('/mod/coripodatacollection/instituteadmin.php',
                ['page' => 'admins', 'istituteid' => $istituteid, 'projectid' => $projectid]));
    }
}



// Rimozione di un amministratore.
if ($visualizepage == 'removeadmin') {
    $administrationid = required_param('adminid', PARAM_INT);
    $user_admin = $DB->get_record('coripodatacollection_instituteadmin', ['id' => $administrationid]);
    $DB->delete_records('coripodatacollection_instituteadmin', ['id' => $administrationid]);

    if (!$DB->record_exists('coripodatacollection_instituteadmin', ['userid' => $user_admin->userid])) {
        $roleid = $DB->get_record_sql('SELECT id FROM {role} WHERE name = "Dirigente scolastico"');
        role_unassign($roleid->id, $user_admin->userid, 1);
    }

    redirect(new moodle_url('/mod/coripodatacollection/instituteadmin.php',
            ['page' => 'admins', 'istituteid' => $istituteid, 'projectid' => $projectid]));
}



// Codice per piccolo form per cercare nuovo amministratore.
if($visualizepage == 'admins') {
    $url = new moodle_url('/mod/coripodatacollection/instituteadmin.php',
            ['page' => 'admins', 'istituteid' => $istituteid, 'projectid' => $projectid]);
    $addadmin = new \mod_coripodatacollection\forms\searchentities_form($url, ['id' => $institute->id]);
    if ($data = $addadmin->get_data()) {

        if (!empty($data->searchentity)) {
            foreach ($data->searchentity as $d) {
                $newadmin = new stdClass();
                $newadmin->userid = $d;
                $newadmin->instituteid = $institute->id;
                $DB->insert_record('coripodatacollection_instituteadmin', $newadmin);

                $roleid = $DB->get_record_sql('SELECT id FROM {role} WHERE name = "Dirigente scolastico"');
                role_assign($roleid->id, $newadmin->userid, 1);

                $erogazioni_istituto = $DB->get_records('coripodatacollection_istituti_x_progetto_x_aa',
                        ['instituteid' => $institute->id]);
                foreach ($erogazioni_istituto as $erog_ist) {
                    $erogazione = $DB->get_record('coripodatacollection_erogations', ['id' =>$erog_ist->erogation]);
                    $context = context_course::instance($erogazione->courseid);
                    if (!is_enrolled($context, $newadmin->userid)) {
                        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
                        enrol_try_internal_enrol($erogazione->courseid, $newadmin->userid, $studentrole->id);
                    }
                }

            }
            redirect($url);
        }
    }
}



// Codice per modifica delle informazioni sull'istituto.
if ($visualizepage == 'modifica') {
    $url = new moodle_url('/mod/coripodatacollection/instituteadmin.php?',
            ['page' => 'modifica', 'istituteid' => $istituteid, 'projectid' => $projectid]);
    $modifyinstitute = new mod_coripodatacollection\forms\institutemodify_form($url, ['instituteid' => $institute->id]);
    if ($modifyinstitute->is_cancelled()) {
        redirect(new moodle_url('/mod/coripodatacollection/instituteadmin.php',
                ['page' => 'institute', 'istituteid' => $istituteid, 'projectid' => $projectid]));
    } else if ($data = $modifyinstitute->get_data()) {

        $changedinstitute = new stdClass();
        $changedinstitute->id = $institute->id;
        $changedinstitute->denominazioneistituto = required_param('newinstitute_name', PARAM_TEXT);
        $changedinstitute->scuolasingola = optional_param('schoolonly', 0, PARAM_INT);
        $changedinstitute->numerodiplessi = $institute->numerodiplessi;
        $changedinstitute->nome_direttore = $data->nome_direttore;
        $changedinstitute->cognome_direttore = $data->cognome_direttore;
        $changedinstitute->email_direttore = $data->email_direttore;
        $changedinstitute->nome_dsga = $data->nome_dsga;
        $changedinstitute->cognome_dsga = $data->cognome_dsga;
        $changedinstitute->email_dsga = $data->email_dsga;
        $changedinstitute->zona = $data->zona;

        $DB->update_record('coripodatacollection_istituti', $changedinstitute);

        redirect(new moodle_url('/mod/coripodatacollection/instituteadmin.php',
                ['page' => 'institute', 'istituteid' => $istituteid, 'projectid' => $projectid]));
    }
}



$homeurl = new moodle_url('/', ['redirect' => 0]);
$allistitutes = new moodle_url('/mod/coripodatacollection/instituteadmin.php');
$instituteurl = new moodle_url($PAGE->url, ['page' => 'institute', 'istituteid' => $istituteid]);
$teacherurl = new moodle_url($PAGE->url, ['page' => 'insegnanti', 'istituteid' => $istituteid]);
$insadminurl = new moodle_url($PAGE->url, ['page' => 'admins', 'istituteid' => $istituteid]);

if ($visualizepage ==  'institute' || $visualizepage == 'nuovoplesso' || $visualizepage == 'modifica') {
    $menuelemnts = [
            ["text" => "Home", "url" => $homeurl, "key" => "viewproject"],
            ["text" => get_string('schoolmanager', 'mod_coripodatacollection'), "url" => $allistitutes, "key" => "viewvaluser"],
            ["text" => get_string('istitutomenu', 'mod_coripodatacollection'), "url" => $instituteurl, "key" => "viewvaluser", "isactive" => true],
            ["text" => get_string('insegnantimenu','mod_coripodatacollection'), "url" => $teacherurl, "key" => "viewvaluser"],
            ["text" => get_string('instituteadminmenu','mod_coripodatacollection'), "url" => $insadminurl, "key" => "viewvaluser"],
    ];

} elseif ( $visualizepage ==  'insegnanti' || $visualizepage ==  'nuovoinsegnante') {
    $menuelemnts = [
            ["text" => "Home", "url" => $homeurl, "key" => "viewproject"],
            ["text" => get_string('schoolmanager', 'mod_coripodatacollection'), "url" => $allistitutes, "key" => "viewvaluser"],
            ["text" => get_string('istitutomenu', 'mod_coripodatacollection'), "url" => $instituteurl, "key" => "viewvaluser"],
            ["text" => get_string('insegnantimenu','mod_coripodatacollection'), "url" => $teacherurl, "key" => "viewvaluser", "isactive" => true],
            ["text" => get_string('instituteadminmenu','mod_coripodatacollection'), "url" => $insadminurl, "key" => "viewvaluser"],
    ];
} elseif ( $visualizepage ==  'admins' || $visualizepage ==  'newadmin') {
$menuelemnts = [
        ["text" => "Home", "url" => $homeurl, "key" => "viewproject"],
        ["text" => get_string('schoolmanager', 'mod_coripodatacollection'), "url" => $allistitutes, "key" => "viewvaluser"],
        ["text" => get_string('istitutomenu', 'mod_coripodatacollection'), "url" => $instituteurl, "key" => "viewvaluser"],
        ["text" => get_string('insegnantimenu','mod_coripodatacollection'), "url" => $teacherurl, "key" => "viewvaluser"],
        ["text" => get_string('instituteadminmenu','mod_coripodatacollection'), "url" => $insadminurl, "key" => "viewvaluser", "isactive" => true],
];
} else {
    $menuelemnts = [
            ["text" => "Home", "url" => $homeurl, "key" => "viewproject"],
            ["text" => get_string('schoolmanager', 'mod_coripodatacollection'), "url" => $allistitutes, "key" => "viewvaluser", "isactive" => true]
    ];
}

if (has_capability('mod/coripodatacollection:projectadmin', $context)) {
    $panoramicaurl = new moodle_url('/mod/coripodatacollection/projectview.php',
            ['page' => 'overview', 'id' => $projectid, 'sesskey' => sesskey()]);
    $evaluserurl = new moodle_url('/mod/coripodatacollection/projectview.php',
            ['page' => 'evaluators', 'id' => $projectid, 'sesskey' => sesskey()]);
    $schoolsurl = new moodle_url('/mod/coripodatacollection/projectview.php',
            ['page' => 'institutes', 'id' => $projectid, 'sesskey' => sesskey()]);
    $allprojects = new moodle_url('/mod/coripodatacollection/projectsadmin.php',
            ['page' => 'projects']);
    $admin_menuelements = [
            ["text" => get_string('projectmenu', 'mod_coripodatacollection'),
                    "url" => $allprojects, "key" => "viewallproject"],
            ["text" => get_string('overviewmenu', 'mod_coripodatacollection'),
                    "url" => $panoramicaurl, "key" => "viewproject"],
            ["text" => get_string('evaluatorsmenu', 'mod_coripodatacollection'),
                    "url" => $evaluserurl, "key" => "viewvaluser"],
            ["text" => get_string('institutesmenu', 'mod_coripodatacollection'),
                    "url" => $schoolsurl, "key" => "viewschools"],
    ];

    unset($menuelemnts[0]);
    unset($menuelemnts[1]);

    foreach ($menuelemnts as &$button) {
        $button["url"]->param('projectid', $projectid);
    }

    $menuelemnts = array_merge($admin_menuelements, $menuelemnts);
}

coripodatacollection_projectview_displaymenu($menuelemnts);

echo $OUTPUT->header();

if ($visualizepage == 'nuovoplesso') {
    echo html_writer::tag('h2', get_string('new_plex', 'mod_coripodatacollection'), ['class' => 'h2']);
    $newplexform->display();
} elseif ($visualizepage == 'modifica') {
    echo html_writer::tag('h2', get_string('modify_institute_info', 'mod_coripodatacollection'),
            ['class' => 'h2']);
    $modifyinstitute->display();
} elseif ($visualizepage == 'insegnanti') {

    echo html_writer::tag('h2', get_string('teachers', 'mod_coripodatacollection'),
            ['class' => 'h2']);

    $sql = 'SELECT {user}.id, firstname, lastname, email, username
            FROM {user} WHERE id in  ( 
                        SELECT userid 
                        FROM {coripodatacollection_teachers} 
                        WHERE instituteid = ' . $institute->id . ' )';
    $teachers = $DB->get_records_sql($sql);

    $table = new html_table();
    $table->align = ['center', 'center', 'center', 'center', 'center'];
    $table->head = [
            get_string('username', 'mod_coripodatacollection'),
            get_string('name', 'mod_coripodatacollection'),
            get_string('surname', 'mod_coripodatacollection'),
            get_string('email', 'mod_coripodatacollection'),
            ' '
    ];


    if (!empty($teachers)) {
        foreach ($teachers as $t) {
            $delbutton = html_writer::link(
                    new moodle_url('/mod/coripodatacollection/instituteadmin.php',
                            ['page' => 'delteacher', 'userid' => $t->id, 'istituteid' => $istituteid, 'projectid' => $projectid]),
                    $OUTPUT->pix_icon('i/delete', get_string('delete_teacher', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );
            $table->data[] = new html_table_row([$t->username, $t->firstname, $t->lastname, $t->email, $delbutton]);
        }
        echo html_writer::table($table);
    } else {
        $noinputimg = 'pix/noentries_zero_state.svg';
        $noerog =  html_writer::start_div('text-xs-center text-center mt-4');
        $noerog .=  html_writer::img($noinputimg, get_string('no_teachers', 'mod_coripodatacollection'),
                ['style' => 'display: block; margin: 0 auto;']);
        $noerog .=  html_writer::tag(
                'h5',
                get_string('no_registered_teacher', 'mod_coripodatacollection'),
                ['class' => 'h5 mt-3 mb-0']);
        $noerog .=  html_writer::end_div();
        echo $noerog;
    }

    $addteacher->display();

    $linkaddbutton = new moodle_url('/mod/coripodatacollection/instituteadmin.php',
            ['page' => 'nuovoinsegnante', 'istituteid' => $istituteid, 'projectid' => $projectid]);
    $stickyfooterelements = html_writer::tag('a', get_string('addnewteacher', 'mod_coripodatacollection'),
            ['href' => $linkaddbutton, 'class' => 'btn btn-primary', 'style' => 'display: inline-block; margin:2px;']
    );

    $stickyfooter = new \core\output\sticky_footer($stickyfooterelements);
    echo $OUTPUT->render($stickyfooter);

} elseif ($visualizepage == 'nuovoinsegnante') {
    echo html_writer::tag('h2', get_string('new_teacher', 'mod_coripodatacollection'),
            ['class' => 'h2']);
    $newteachgerform->display();
} elseif ($visualizepage == 'admins') {

    echo html_writer::tag('h2', get_string('institute_admins', 'mod_coripodatacollection'),
            ['class' => 'h2']);

    $tabledisplay = new MoodleQuickForm('', '', 'GET');

    $admins = $DB->get_records('coripodatacollection_instituteadmin', ['instituteid' => $institute->id]);

    $table = new html_table();
    $table->align = ['center', 'center', 'center', 'center', 'center'];
    $table->head = [
            get_string('username', 'mod_coripodatacollection'),
            get_string('name', 'mod_coripodatacollection'),
            get_string('surname', 'mod_coripodatacollection'),
            get_string('email', 'mod_coripodatacollection'),
            ' '
    ];


    if (!empty($admins)) {
        foreach ($admins as $a) {
            $delbutton = html_writer::link(
                    new moodle_url('/mod/coripodatacollection/instituteadmin.php',
                            ['page' => 'removeadmin', 'adminid' => $a->id, 'istituteid' => $istituteid, 'projectid' => $projectid]),
                    $OUTPUT->pix_icon('i/delete', get_string('remove_admin', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );
            $admininfo = $DB->get_record('user', ['id' => $a->userid]);
            if ($a->userid == $USER->id) {
                $table->data[] = new html_table_row([
                        $admininfo->username,
                        $admininfo->firstname,
                        $admininfo->lastname,
                        $admininfo->email,
                        ''
                ]);
            } else {
                $table->data[] = new html_table_row([
                        $admininfo->username,
                        $admininfo->firstname,
                        $admininfo->lastname,
                        $admininfo->email,
                        $delbutton
                ]);
            }
        }
        $tabledisplay->addElement('html', html_writer::table($table));
    } else {
        $noinputimg = 'pix/noentries_zero_state.svg';
        $noerog =  html_writer::start_div('text-xs-center text-center mt-4');
        $noerog .=  html_writer::img($noinputimg, get_string('no_admins', 'mod_coripodatacollection'),
                ['style' => 'display: block; margin: 0 auto;']);
        $noerog .=  html_writer::tag('h5',  get_string('no_admins', 'mod_coripodatacollection'),
                ['class' => 'h5 mt-3 mb-0']);
        $noerog .=  html_writer::end_div();
        $tabledisplay->addElement('html', $noerog);
    }

    $tabledisplay->display();
    $addadmin->display();

    $linkaddadmin = new moodle_url('/mod/coripodatacollection/instituteadmin.php',
            ['page' => 'newadmin', 'istituteid' => $istituteid, 'projectid' => $projectid]);

    $stickyfooterelements = html_writer::tag('a', get_string('new_admin', 'mod_coripodatacollection'),
            ['href' => $linkaddadmin, 'class' => 'btn btn-primary', 'style' => 'display: inline-block; margin:2px;']
    );

    $stickyfooter = new \core\output\sticky_footer($stickyfooterelements);
    echo $OUTPUT->render($stickyfooter);

} elseif ($visualizepage == 'newadmin') {
    echo html_writer::tag('h2', get_string('new_admin', 'mod_coripodatacollection'), ['class' => 'h2']);
    $newadminform->display();
} elseif ($visualizepage == 'institute') {

    echo html_writer::tag('h2', get_string('institute_infos', 'mod_coripodatacollection'),
            ['class' => 'h2']);

    $instituteinfo = [ 'numerodiplessi' => $institute->numerodiplessi ];
    $infoins = new \mod_coripodatacollection\forms\institutemodify_form(null,
            ['instituteid' => $institute->id, 'viewmode' => true]);
    $infoins->set_data($instituteinfo);
    $infoins->display();

    $tabledisplay = new MoodleQuickForm('', '', 'GET');
    $tabledisplay->addElement('header', 'header', get_string('plexes', 'mod_coripodatacollection'));

    $plessi = $DB->get_records('coripodatacollection_plessi', ['instituteid' => $institute->id]);

    $table = new html_table();
    $table->align = ['center', 'center', 'center'];
    $table->head = [
            get_string('denomination', 'mod_coripodatacollection'),
            get_string('address', 'mod_coripodatacollection'),
            ''
    ];


    if (!empty($plessi)) {
        foreach ($plessi as $p) {
            $delbutton = html_writer::link(
                    new moodle_url('/mod/coripodatacollection/instituteadmin.php',
                            ['page' => 'delplex', 'plexid' => $p->id, 'istituteid' => $istituteid, 'projectid' => $projectid]),
                    $OUTPUT->pix_icon('i/delete', get_string('delete_plex', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );
            $table->data[] = new html_table_row([$p->denominazioneplesso, $p->indirizzo, $delbutton]);
        }
        $tabledisplay->addElement('html', html_writer::table($table));
    } else {
        $noinputimg = 'pix/noentries_zero_state.svg';
        $noerog =  html_writer::start_div('text-xs-center text-center mt-4');
        $noerog .=  html_writer::img($noinputimg, get_string('no_plexes', 'mod_coripodatacollection'),
                ['style' => 'display: block; margin: 0 auto;']);
        $noerog .=  html_writer::tag('h5', get_string('no_plexes', 'mod_coripodatacollection'),
                ['class' => 'h5 mt-3 mb-0']);
        $noerog .=  html_writer::end_div();
        $tabledisplay->addElement('html', $noerog);
    }

    $tabledisplay->display();

    $linkmodify = new moodle_url('/mod/coripodatacollection/instituteadmin.php',
            ['page' => 'modifica', 'istituteid' => $istituteid, 'projectid' => $projectid]);
    $linkaddplexe = new moodle_url('/mod/coripodatacollection/instituteadmin.php',
            ['page' => 'nuovoplesso', 'istituteid' => $istituteid, 'projectid' => $projectid]);

    $stickyfooterelements = html_writer::tag('a', get_string('modifyinstitute', 'mod_coripodatacollection'),
            ['href' => $linkmodify, 'class' => 'btn btn-primary', 'style' => 'display: inline-block; margin:2px;']
    );
    if ($institute->scuolasingola == 0) {
        $stickyfooterelements .= html_writer::tag('a', get_string('addplexe', 'mod_coripodatacollection'),
                ['href' => $linkaddplexe, 'class' => 'btn btn-secondary', 'style' => 'display: inline-block; margin:2px;']
        );
    }

    $stickyfooter = new \core\output\sticky_footer($stickyfooterelements);
    echo $OUTPUT->render($stickyfooter);

} else {

    $institute_admin = $DB->get_records('coripodatacollection_instituteadmin', ['userid' => $USER->id]);

    echo html_writer::start_tag('ul', ['class' => 'section m-0 p-0 img-text  d-block ', 'data-for' => 'cmlist']);
    foreach ($institute_admin as $i) {
        $istitute = $DB->get_record('coripodatacollection_istituti', ['id' => $i->instituteid]);
        echo html_writer::start_tag('li', ['class' => 'activity activity-wrapper ']);
        echo html_writer::start_tag('div', ['class' => 'activity-item focus-control ']);

        echo html_writer::start_tag('div', ['class' => 'text-center']);
        echo html_writer::link(
                new moodle_url(
                        '/mod/coripodatacollection/instituteadmin.php',
                        ['page' => 'institute', 'istituteid' => $istitute->id, 'projectid' => $projectid]
                ),
                format_text($istitute->denominazioneistituto, FORMAT_PLAIN),
        );
        echo html_writer::end_tag('div');

        echo html_writer::end_tag('div');
        echo html_writer::end_tag('li');
    }

    echo html_writer::end_tag('ul');

}

echo html_writer::div('', '', ['style ' => 'height: 250px;']);
echo $OUTPUT->footer();
