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

use core\output\sticky_footer;

require_once('../../config.php');
require_once('lib.php');
require_once('../../user/lib.php');
require_once('../../course/lib.php');
require_once('../../course/modlib.php');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'mod_coripodatacollection'));

$projectid = required_param('id', PARAM_INT);
$displaypage = optional_param('page', 'overview', PARAM_TEXT);
$PAGE->set_url(new moodle_url('/mod/coripodatacollection/projectview.php'), ['page' => $displaypage, 'id' => $projectid]);
$project = $DB->get_record('coripodatacollection_projects', ['id' => $projectid]);
$projectadmins = $DB->get_records('coripodatacollection_projectadmin', ['projectid' => $projectid, 'userid' => $USER->id]);

$PAGE->set_heading($project->projectname);
$PAGE->add_body_class('mediumwidth');


require_login();
require_capability('mod/coripodatacollection:projectadmin', $context);

if (isguestuser() || empty($projectadmins)) {
    throw new moodle_exception('noguest');
}



$panoramicaurl = new moodle_url('/mod/coripodatacollection/projectview.php',
        ['page' => 'overview', 'id' => $projectid, 'sesskey' => sesskey()]);
$evaluserurl = new moodle_url('/mod/coripodatacollection/projectview.php',
        ['page' => 'evaluators', 'id' => $projectid, 'sesskey' => sesskey()]);
$schoolsurl = new moodle_url('/mod/coripodatacollection/projectview.php',
        ['page' => 'institutes', 'id' => $projectid, 'sesskey' => sesskey()]);
$allprojects = new moodle_url('/mod/coripodatacollection/projectsadmin.php',
        ['page' => 'projects']);
$menuelements = [
        ["text" => get_string('projectmenu', 'mod_coripodatacollection'),
                "url" => $allprojects, "key" => "viewallproject"],
        ["text" => get_string('overviewmenu', 'mod_coripodatacollection'),
                "url" => $panoramicaurl, "key" => "viewproject"],
        ["text" => get_string('evaluatorsmenu', 'mod_coripodatacollection'),
                "url" => $evaluserurl, "key" => "viewvaluser"],
        ["text" => get_string('institutesmenu', 'mod_coripodatacollection'),
                "url" => $schoolsurl, "key" => "viewschools"],
];


if ($displaypage == 'evaluators' || $displaypage == 'newevaluator') {
    $menuelements[2]['isactive' ] = true;
} elseif ($displaypage == 'institutes' || $displaypage == 'newinstitute') {
    $menuelements[3]['isactive'] = true;
} else{
    $menuelements[1]['isactive'] = true;
}

coripodatacollection_projectview_displaymenu($menuelements);



// Code for removing an erogation deleting also the course.
if ($displaypage == 'delerogation') {
    $courseid = required_param('courseid', PARAM_INT);
    if (optional_param('confirm', 0, PARAM_INT) == 1 ) {
        delete_course($courseid, false);
        redirect(new moodle_url('/mod/coripodatacollection/projectview.php', ['page' => 'overview', 'id' => $projectid]));
    } else {
        echo $OUTPUT->header();
        $formcontinue = new single_button(new moodle_url('/mod/coripodatacollection/projectview.php',
                ['id' => $projectid, 'page' => $displaypage, 'courseid' => $courseid, 'confirm' => 1]), get_string('yes'));
        $formcancel = new single_button(new moodle_url('/mod/coripodatacollection/projectview.php',
                ['page' => 'overview', 'id' => $projectid]), get_string('no'), 'get');
        echo $OUTPUT->confirm(get_string('allert_del_erogtion', 'mod_coripodatacollection'),
                $formcontinue, $formcancel);
        echo $OUTPUT->footer();
        die();
    }
}



// Removing an evaluator from the current project.
if ($displaypage == 'evalremove') {
    $userid = required_param('userid', PARAM_INT);
    $evaluator = $DB->get_record('coripodatacollection_evaluators', ['projectid' => $projectid, 'userid' => $userid]);
    $evaluator->projectid = null;
    $DB->update_record('coripodatacollection_evaluators', $evaluator);
    redirect(new moodle_url('/mod/coripodatacollection/projectview.php', ['page' => 'evaluators', 'id' => $projectid]));
}



// Code for dealing with the deletion of an institute from a project.
$action = optional_param('action', '', PARAM_TEXT);
if ($action == 'del') {
    $delinstituteid = required_param('institute', PARAM_INT);
    $DB->delete_records('coripodatacollection_istituti_x_progetto',
            ['projectid' => $projectid , 'instituteid' => $delinstituteid]);
    redirect($PAGE->url);
}



// Code for creating the form for adding a new project evaluator.
if ($displaypage == 'newevaluator') {
    $newprojectform = new mod_coripodatacollection\forms\projectnewuser_form($PAGE->url, ['id' => $projectid]);

    if ($newprojectform->is_cancelled()) {
        redirect(new moodle_url('/mod/coripodatacollection/projectview.php',
                ['page' => 'evaluators', 'id' => $projectid, 'sesskey' => sesskey()]));
    } else if ($data = $newprojectform->get_data()) {

        // Creo la classe per salvare il nuovo utente.
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
        $newuser->password = hash_internal_user_password($data->newuser_password);

        $newuser->confirmed = 1;
        $newuser->mnethostid = 1;
        $newuser->timecreated = time();
        $newuser->timemodified = $newuser->timecreated;

        $newuser->id = user_create_user($newuser);
        profile_save_data($newuser);
        $newuser = $DB->get_record('user', ['id' => $newuser->id]);
        send_newuser_mail($newuser, $data->newuser_password);
        unset_user_preference('create_password', $newuser);
        set_user_preference('auth_forcepasswordchange', 1, $newuser);
        \core\event\user_created::create_from_userid($newuser->id)->trigger();

        // Creo la classe per assegnare l'associazione tra utente valutatore e progetto.
        $newprojectevaluator = new stdClass();
        $newprojectevaluator->projectid = $projectid;
        $newprojectevaluator->userid = $newuser->id;

        $DB->insert_record('coripodatacollection_evaluators', $newprojectevaluator);
        $user = $DB->get_record('user', ['id' => $newuser->id]);
        $supportuser = core_user::get_support_user();
        $subject = get_string('add_project_evaluator_object', 'mod_coripodatacollection');
        $body = sprintf(
                get_string('add_project_evaluator_body', 'mod_coripodatacollection'),
                $user->lastname . ' ' . $user->firstname,
                $project->projectname,
                $SITE->fullname,
                $CFG->wwwroot . '/login/index.php'
        );
        email_to_user($user, $supportuser, $subject, $body);

        // Trovo l'id del ruolo per l'utente valutatore e aggiungo l'utente.
        $roleid = $DB->get_record_sql('SELECT id FROM {role} WHERE name = "Utente valutatore"');
        role_assign($roleid->id, $newuser->id, 1);

        redirect(new moodle_url('/mod/coripodatacollection/projectview.php',
                ['page' => 'evaluators', 'id' => $projectid, 'sesskey' => sesskey()]));
    }
}



// Code for adding the little form for adding an existing evaluator.
if($displaypage == 'evaluators') {
    $url = new moodle_url('/mod/coripodatacollection/projectview.php',
            ['page' => 'evaluators', 'id' => $projectid]);
    $addevaluator = new \mod_coripodatacollection\forms\searchentities_form($url,['id' => $projectid, 'evaluators' => true]);
    if ($data = $addevaluator->get_data()) {

        if (!empty($data->searchentity)) {
            foreach ($data->searchentity as $d) {

                $eval = $DB->get_record('coripodatacollection_evaluators', ['userid' => $d, 'projectid' => null]);
                $eval->projectid = $projectid;
                $DB->update_record('coripodatacollection_evaluators', $eval);

                $user = $DB->get_record('user', ['id' => $d]);
                $supportuser = core_user::get_support_user();
                $subject = get_string('add_project_evaluator_object', 'mod_coripodatacollection');
                $body = sprintf(
                        get_string('add_project_evaluator_body', 'mod_coripodatacollection'),
                        $user->lastname . ' ' . $user->firstname,
                        $project->projectname,
                        $SITE->fullname,
                        $CFG->wwwroot . '/login/index.php'
                );
                email_to_user($user, $supportuser, $subject, $body);
            }
            redirect($url);
        }
    }
}



// Code for adding the little form for adding an existing insitute.
if($displaypage == 'institutes') {
    $url = new moodle_url('/mod/coripodatacollection/projectview.php',
            ['page' => 'institutes', 'id' => $projectid]);
    $addinstitues = new \mod_coripodatacollection\forms\searchentities_form($url,['id' => $projectid, 'institutes' => true]);
    if ($data = $addinstitues->get_data()) {

        if (!empty($data->searchentity)) {
            foreach ($data->searchentity as $d) {

                $projectxinstitute = new stdClass();
                $projectxinstitute->projectid = $projectid;
                $projectxinstitute->instituteid = $d;

                $DB->insert_record('coripodatacollection_istituti_x_progetto', $projectxinstitute);

                $admins = $DB->get_records('coripodatacollection_instituteadmin', ['instituteid' => $d]);
                $istitute = $DB->get_record('coripodatacollection_istituti', ['id' => $d]);
                foreach ($admins as $admin) {

                    $user = $DB->get_record('user', ['id' => $admin->userid]);
                    $supportuser = core_user::get_support_user();
                    $subject = get_string('add_project_istitute_object', 'mod_coripodatacollection');
                    $body = sprintf(
                            get_string('add_project_istitute_body', 'mod_coripodatacollection'),
                            $user->lastname . ' ' . $user->firstname,
                            $project->projectname,
                            $istitute->denominazioneistituto,
                            $SITE->fullname,
                            $CFG->wwwroot . '/login/index.php'
                    );
                    email_to_user($user, $supportuser, $subject, $body);

                }
            }
            redirect($url);
        }
    }
}




// Code for creating the form for adding a new institute to the form.
if ($displaypage == 'newinstitute') {
    $newinstituteform = new mod_coripodatacollection\forms\projectnewinstitute_form($PAGE->url, ['id' => $projectid]);

    if ($newinstituteform->is_cancelled()) {
        redirect(new moodle_url('/mod/coripodatacollection/projectview.php',
                ['page' => 'institutes', 'id' => $projectid, 'sesskey' => sesskey()]));
    } else if ($data = $newinstituteform->get_data()) {

        // Creo la classe per salvare il nuovo utente.
        $newuser = new stdClass();
        $newuser->firstname = required_param('newinstitute_manager_name', PARAM_TEXT);
        $newuser->lastname = required_param('newinstitute_manager_surname', PARAM_TEXT);
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

        $newuser->email = required_param('newinstitute_manager_email', PARAM_TEXT);
        $newuser->password = hash_internal_user_password($data->newinstitute_manager_password);

        $newuser->confirmed = 1;
        $newuser->mnethostid = 1;
        $newuser->timecreated = time();
        $newuser->timemodified = $newuser->timecreated;

        $newuser->id = user_create_user($newuser);
        profile_save_data($newuser);
        $newuser = $DB->get_record('user', ['id' => $newuser->id]);
        send_newuser_mail($newuser, $data->newinstitute_manager_password);
        unset_user_preference('create_password', $newuser);
        set_user_preference('auth_forcepasswordchange', 1, $newuser);
        \core\event\user_created::create_from_userid($newuser->id)->trigger();

        $newinstitute = new stdClass();
        $newinstitute->denominazioneistituto = required_param('newinstitute_name', PARAM_TEXT);
        $newinstitute->scuolasingola = optional_param('schoolonly', 0, PARAM_INT);
        $newinstitute->numerodiplessi = 1;
        $newinstitute->nome_direttore = $data->nome_direttore;
        $newinstitute->cognome_direttore = $data->cognome_direttore;
        $newinstitute->email_direttore = $data->email_direttore;
        $newinstitute->nome_dsga = $data->nome_dsga;
        $newinstitute->cognome_dsga = $data->cognome_dsga;
        $newinstitute->email_dsga = $data->email_dsga;
        $newinstitute->zona = $data->zona;

        $instituteid = $DB->insert_record('coripodatacollection_istituti', $newinstitute);

        $newinstituteadmin = new stdClass();
        $newinstituteadmin->userid = $newuser->id;
        $newinstituteadmin->instituteid = $instituteid;
        $DB->insert_record('coripodatacollection_instituteadmin', $newinstituteadmin);

        if ($newinstitute->scuolasingola == 1) {
            $newplex = new stdClass();
            $newplex->instituteid = $instituteid;
            $newplex->denominazioneplesso = $newinstitute->denominazioneistituto;
            $newplex->indirizzo = 'Indirizzo plesso';
            $DB->insert_record('coripodatacollection_plessi', $newplex);
        }

        // Find the id for evaluator user and add the user to it.
        $roleid = $DB->get_record_sql('SELECT id FROM {role} WHERE name = "Dirigente scolastico"');
        role_assign($roleid->id, $newuser->id, 1);

        // Add the created institute to the ones that are logged in for the current project
        $projectxinstitute = new stdClass();
        $projectxinstitute->projectid = $projectid;
        $projectxinstitute->instituteid = $instituteid;
        $DB->insert_record('coripodatacollection_istituti_x_progetto', $projectxinstitute);

        $user = $DB->get_record('user', ['id' => $newuser->id]);
        $supportuser = core_user::get_support_user();
        $subject = get_string('add_project_istitute_object', 'mod_coripodatacollection');
        $body = sprintf(
                get_string('add_project_istitute_body', 'mod_coripodatacollection'),
                $user->lastname . ' ' . $user->firstname,
                $project->projectname,
                $newinstitute->denominazioneistituto,
                $SITE->fullname,
                $CFG->wwwroot . '/login/index.php'
        );
        email_to_user($user, $supportuser, $subject, $body);

        redirect(new moodle_url('/mod/coripodatacollection/projectview.php',
                ['page' => 'institutes', 'id' => $projectid, 'sesskey' => sesskey()]));
    }
}



// Code for displaying the main information for an institute.
if ($displaypage == 'overview') {
    $modifica = optional_param('modifica', false, PARAM_BOOL);
    if ($modifica) {
        $redir = new moodle_url('/mod/coripodatacollection/projectview.php',
                ['page' => 'overview', 'id' => $projectid, 'modifica' => true]);
        $projectinfo = new \mod_coripodatacollection\forms\projectadmin_form($redir,
                ['viewmode' => 'edit', 'projectid' => $projectid]);
    } else {
        $redir = new moodle_url('/mod/coripodatacollection/projectview.php',
                ['page' => 'overview', 'id' => $projectid, 'modifica' => false]);
        $projectinfo = new \mod_coripodatacollection\forms\projectadmin_form($redir,
                ['viewmode' => 'viewonly', 'id' => $projectid]);
    }

    if ($modifica) {
        if ($projectinfo->is_cancelled()) {
            $redir = new moodle_url('/mod/coripodatacollection/projectview.php',
                    ['page' => 'overview', 'id' => $projectid, 'modifica' => false]);
            redirect(($redir));
        } else if ($data = $projectinfo->get_data()) {
            $redir = new moodle_url('/mod/coripodatacollection/projectview.php',
                    ['page' => 'overview', 'id' => $projectid, 'modifica' => false]);
            $data->id = $project->id;
            $DB->update_record('coripodatacollection_projects', $data);
            redirect($redir);
        }
    } else {
        if ($projectinfo->is_submitted() ) {
            if ($_POST['submitbutton'] == 'Modifica') {
                $redir = new moodle_url('/mod/coripodatacollection/projectview.php',
                        ['page' => 'overview', 'id' => $projectid, 'modifica' => true]);
                redirect($redir);
            } else if ($_POST['submitbutton'] == 'Nuova erogazione') {
                $data = new stdClass();
                $data->academicyear = required_param('academicyear', PARAM_TEXT);
                $newcourse = newedition_course($data, $projectid);
                $studentrole = $DB->get_record('role', ['shortname' => 'student']);
                enrol_try_internal_enrol($newcourse->id, $USER->id, $studentrole->id);

                $module = $DB->get_record('modules', ['name' => 'coripodatacollection']);
                $course_module = $DB->get_record('course_modules',
                        ['course' => $newcourse->id, 'module' => $module->id]);

                $redir = new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                        ['id' => $course_module->id, 'page' => 'periods', 'mode' => 'edit']);
                redirect($redir);
            }
        }
    }

    $projectinfo->set_data($project);
}



echo $OUTPUT->header();

if ($displaypage == 'evaluators') {

    // Code for displaying the list of evaluators for a project.

    echo html_writer::tag('h2', get_string('evaluators', 'mod_coripodatacollection'), ['class' => 'h2']);

    $sql ='SELECT id, firstname, lastname, email, username
           FROM {user} 
           WHERE id in  ( 
                SELECT userid 
                FROM {coripodatacollection_evaluators} 
                WHERE projectid = ' . $projectid . ' 
           )';
    $evaluators = $DB->get_records_sql($sql);

    $table = new html_table();
    $table->align = ['center', 'center', 'center', 'center', 'center'];
    $table->head = [
            get_string('username', 'mod_coripodatacollection'),
            get_string('name', 'mod_coripodatacollection'),
            get_string('surname', 'mod_coripodatacollection'),
            get_string('email', 'mod_coripodatacollection'),
            ' '
    ];

    if (!empty($evaluators)) {
        foreach ($evaluators as $e) {
            $removebutton = html_writer::link(
                    new moodle_url('/mod/coripodatacollection/projectview.php',
                            ['page' => 'evalremove', 'id' => $projectid, 'userid' => $e->id]),
                    $OUTPUT->pix_icon('e/delete_row',
                            get_string('remove_evaluator', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );
            $table->data[] = new html_table_row([$e->username, $e->firstname, $e->lastname, $e->email, $removebutton]);
        }
        echo html_writer::table($table);
    } else {
        $noinputimg = 'pix/view_zero_state.svg';
        echo html_writer::start_div('text-xs-center text-center mt-4');
        echo html_writer::img($noinputimg, get_string('no_evaluators', 'mod_coripodatacollection'),
                ['style' => 'display: block; margin: 0 auto;']);
        echo html_writer::tag('h5', get_string('no_evaluators', 'mod_coripodatacollection'),
                ['class' => 'h5 mt-3 mb-0']);
        echo html_writer::end_div();
    }

    $addevaluator->display();

    $linkaddbutton = new moodle_url('/mod/coripodatacollection/projectview.php',
            ['page' => 'newevaluator','id' => $projectid, 'sesskey' => sesskey()]);
    $stickyfooterelements =  html_writer::tag('a',
            get_string('addnewuser', 'mod_coripodatacollection'),
            ['href' => $linkaddbutton, 'class' => 'btn btn-primary', 'style' => 'display: inline-block;']
    );
    $stickyfooter = new \core\output\sticky_footer($stickyfooterelements);
    echo $OUTPUT->render($stickyfooter);


} elseif ($displaypage == 'newevaluator') {

    // Code for displaying the form for adding a new evaluator
    echo html_writer::tag('h2', get_string('new_evaluator', 'mod_coripodatacollection'),
            ['class' => 'h2']);
    $newprojectform->display();

} elseif ($displaypage == 'institutes') {

    echo html_writer::tag('h2', get_string('institutes', 'mod_coripodatacollection'),
            ['class' => 'h2']);

    $project_institutes = $DB->get_records('coripodatacollection_istituti_x_progetto', ['projectid' => $projectid]);

    $table = new html_table();
    $table->align = ['center', 'center', 'center', 'center', 'center'];
    $table->head = [
            get_string('denomination', 'mod_coripodatacollection'),
            get_string('username', 'mod_coripodatacollection') . ' ' .
                get_string('manager', 'mod_coripodatacollection'),
            get_string('manager', 'mod_coripodatacollection'),
            get_string('email', 'mod_coripodatacollection'),
            ''
    ];

    if (!empty($project_institutes)) {
        foreach ($project_institutes as $pi) {

            $institute = $DB->get_record('coripodatacollection_istituti', ['id' => $pi->instituteid]);
            $ins_administrator = $DB->get_records('coripodatacollection_instituteadmin', ['instituteid' => $pi->instituteid]);
            $ins_administrator = reset($ins_administrator);
            $ins_administrator = $DB->get_record('user', ['id' => $ins_administrator->userid]);

            $viewbutton = html_writer::link(
                    new moodle_url(
                            '/mod/coripodatacollection/instituteadmin.php',
                            ['page' => 'institute', 'istituteid' => $pi->instituteid, 'projectid' => $projectid],
                    ),
                    $OUTPUT->pix_icon('i/hide', get_string('view_istitute', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );

            $delbutton = html_writer::link(
                    new moodle_url(
                            $PAGE->url,
                            ['action' => 'del', 'id' => $projectid, 'sesskey' => sesskey(), 'institute' => $pi->instituteid]
                    ),
                    $OUTPUT->pix_icon('t/delete', get_string('cancel', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );

            $table->data[] = new html_table_row([
                    $institute->denominazioneistituto,
                    $ins_administrator->username,
                    $ins_administrator->lastname . ' ' . $ins_administrator->firstname,
                    $ins_administrator->email, $viewbutton . $delbutton]);
        }
        echo html_writer::table($table);
    } else {
            $noinputimg = 'pix/noentries_zero_state.svg';
            echo html_writer::start_div('text-xs-center text-center mt-4');
            echo html_writer::img($noinputimg, get_string('no_institutes', 'mod_coripodatacollection'),
                    ['style' => 'display: block; margin: 0 auto;']);
            echo html_writer::tag('h5', get_string('no_institutes', 'mod_coripodatacollection'),
                    ['class' => 'h5 mt-3 mb-0']);
            echo html_writer::end_div();
    }

    $addinstitues->display();


    $linknewinstitutebutton = new moodle_url('/mod/coripodatacollection/projectview.php',
            ['page' => 'newinstitute','id' => $projectid, 'sesskey' => sesskey()]);
    $stickyfooterelements =   html_writer::tag('a',
            get_string('addnewinstitute', 'mod_coripodatacollection'),
            ['href' => $linknewinstitutebutton, 'class' => 'btn btn-primary', 'style' => 'display: inline-block;']
    );

    $stickyfooter = new \core\output\sticky_footer($stickyfooterelements);
    echo $OUTPUT->render($stickyfooter);

} elseif ($displaypage == 'newinstitute'){

    // Code for displaying the form for adding a new institute.
    echo html_writer::tag('h2', get_string('new_institute', 'mod_coripodatacollection'),
            ['class' => 'h2']);
    $newinstituteform->display();
} else {

    echo $OUTPUT->box_start();
    echo html_writer::tag('h2', get_string('information', 'mod_coripodatacollection'),
            ['class' => 'h2']);
    $projectinfo->display();

    $erogationstable = new MoodleQuickForm('erogationstable', 'GET', '');
    $erogationstable->addElement('header', 'header1',
            get_string('erogations', 'mod_coripodatacollection'));

    $erogations = $DB->get_records('coripodatacollection_erogations', ['projectid' => $projectid]);
    if (!empty($erogations)) {
        $table = new html_table();
        $table->head = [
                get_string('course_name', 'mod_coripodatacollection'),
                get_string('accademic_year', 'mod_coripodatacollection'),
                get_string('institutes', 'mod_coripodatacollection'),
                get_string('classes', 'mod_coripodatacollection'),
                get_string('students', 'mod_coripodatacollection'),
                get_string('phase', 'mod_coripodatacollection'),
                ''];
        $table->align = ['center', 'center', 'center', 'center', 'center', 'center', 'center'];

        foreach ($erogations as $erog) {

            $nomecorso = $DB->get_record('course', ['id' => $erog->courseid]);

            $viewbutton = html_writer::link(
                    new moodle_url('/course/view.php', ['id' => $erog->courseid]),
                    $OUTPUT->pix_icon('i/hide', get_string('go_to_course', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );
            $delbutton = html_writer::link(
                    new moodle_url('/mod/coripodatacollection/projectview.php',
                            ['page' => 'delerogation', 'id' => $projectid, 'courseid' => $erog->courseid]),
                    $OUTPUT->pix_icon('i/delete', get_string('delete_course', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );

            $row = [
                    ($DB->get_record('course', ['id' => $erog->courseid]))->fullname,
                    $erog->academicyearedition,
                    count($DB->get_records('coripodatacollection_istituti_x_progetto_x_aa', ['erogation' => $erog->id])),
                    count($DB->get_records('coripodatacollection_classes', ['erogazione' => $erog->id])),
                    count($DB->get_records_sql('SELECT mdl_coripodatacollection_alunni.* 
                                FROM mdl_coripodatacollection_alunni
                                JOIN mdl_coripodatacollection_class_students 
                                    ON mdl_coripodatacollection_alunni.id=mdl_coripodatacollection_class_students.studentid
                                JOIN mdl_coripodatacollection_classes 
                                    ON mdl_coripodatacollection_classes.id=mdl_coripodatacollection_class_students.classid
                                WHERE erogazione=' . $erog->id . ' AND consenso=1')),
                    get_string('open', 'mod_coripodatacollection'),
                    $viewbutton . $delbutton,
            ];

            $table->data[] = new html_table_row($row);
        }

        $erogationstable->addElement('html', html_writer::table($table));
    } else {
        $noinputimg = 'pix/noentries_zero_state.svg';
        $noerog_string = get_string('no_erogation', 'mod_coripodatacollection');
        $noerog =  html_writer::start_div('text-xs-center text-center mt-4');
        $noerog .=  html_writer::img($noinputimg, $noerog_string, ['style' => 'display: block; margin: 0 auto;']);
        $noerog .=  html_writer::tag('h5', $noerog_string, ['class' => 'h5 mt-3 mb-0']);
        $noerog .=  html_writer::end_div();
        $erogationstable->addElement('html', $noerog);
    }

    $erogationstable->display();
    echo $OUTPUT->box_end();

}

echo html_writer::div('', '', ['style ' => 'height: 250px;']);
echo $OUTPUT->footer();
