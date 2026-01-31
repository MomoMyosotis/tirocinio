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

require_once('../../config.php');
require_once('lib.php');
require_once('../../user/lib.php');
require_once('../../course/lib.php');
require_once('../../course/modlib.php');

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

$PAGE->set_url('/mod/coripodatacollection/viewproject.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$page = optional_param('page', 'classes', PARAM_TEXT);
$classid = optional_param('classid', -1, PARAM_INT);
$mode = optional_param('mode', '', PARAM_TEXT);

if ($page == 'gruppi' && $mode == 'view') {
    $PAGE->add_body_class('wide');
} else {
    $PAGE->add_body_class('mediumwidth');
}


$urlparam = ['id' => $cm->id, 'page' => $page];
if ($classid != -1) {
    $urlparam['classid'] = $classid;
}
$PAGE->set_url('/mod/coripodatacollection/erogationmanager.php', $urlparam);



$evaluserurl = new moodle_url('/mod/coripodatacollection/erogationmanager.php', ['id' => $id, 'page' => 'evaluators']);
$schoolsurl = new moodle_url('/mod/coripodatacollection/erogationmanager.php', ['id' => $id, 'page' => 'institutes']);
$classiurl = new moodle_url('/mod/coripodatacollection/viewproject.php', ['id' => $cm->id, 'page' => 'classes']);
$periodsurl = new moodle_url('/mod/coripodatacollection/erogationmanager.php', ['id' => $cm->id, 'page' => 'periods', 'mode' => 'view']);
$centriurl = new moodle_url('/mod/coripodatacollection/erogationmanager.php',
        ['id' => $cm->id, 'page' => 'gruppi', 'mode' => 'view']);
$menuelements = [
        ["text" => get_string('classi', 'mod_coripodatacollection'), "url" => $classiurl, "key" => "viewallproject"],
        ["text" => get_string('evaluatorsmenu', 'mod_coripodatacollection'), "url" => $evaluserurl, "key" => "viewvaluser"],
        ["text" => get_string('institutesmenu', 'mod_coripodatacollection'), "url" => $schoolsurl, "key" => "viewschools"],
        ["text" => get_string('periodsmenu', 'mod_coripodatacollection'), "url" => $periodsurl, "key" => "viewschools"],
        ["text" => get_string('groups', 'mod_coripodatacollection'), "url" => $centriurl],
];

if ($page == 'evaluators') {
    $menuelements[1]['isactive' ] = true;
} elseif ($page == 'institutes') {
    $menuelements[2]['isactive'] = true;
} elseif ($page == 'periods') {
    $menuelements[3]['isactive'] = true;
} elseif ($page == 'gruppi') {
    $menuelements[4]['isactive'] = true;
}

coripodatacollection_projectview_displaymenu($menuelements);

$erogation = $DB->get_record('coripodatacollection_erogations', ['courseid' => $course->id]);


// Removing an evaluator from the current erogation.
if ($page == 'evalremove') {
    $userid = required_param('userid', PARAM_INT);

    $enrol = $DB->get_record_sql('SELECT * FROM {enrol} WHERE enrol = "manual" AND courseid = ' . $course->id);
    $DB->delete_records('user_enrolments', ['userid' => $userid, 'enrolid' => $enrol->id]);

    redirect(new moodle_url('/mod/coripodatacollection/erogationmanager.php', ['page' => 'evaluators', 'id' => $id]));
}

if ($page == 'evaladd') {
    $userid = required_param('userid', PARAM_INT);
    enrol_try_internal_enrol($course->id, $userid);
    redirect(new moodle_url('/mod/coripodatacollection/erogationmanager.php', ['page' => 'evaluators', 'id' => $id]));
}



// Code for dealing with the deletion of an institute from the current erogation.
if ($page == 'instituteremove') {

    $instituteid = required_param('instituteid', PARAM_INT);

    $confirm = optional_param('confirm', false, PARAM_BOOL);
    if (!$confirm) {

        echo $OUTPUT->header();
        $formcontinue = new single_button(
                new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                        [ 'page' => 'instituteremove', 'id' => $id, 'confirm' => true, 'instituteid' => $instituteid]),
                get_string('yes'));
        $formcancel = new single_button(
                new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                        ['page' => 'institutes', 'id' => $id]),
                get_string('no'), 'get');
        $errstr = get_string('erogation_remove_institute_err', 'mod_coripodatacollection');
        echo $OUTPUT->confirm($errstr, $formcontinue, $formcancel);
        echo $OUTPUT->footer();
        die();

    }

    $istituto = $DB->get_record('coripodatacollection_istituti', ['id' => $instituteid]);
    $sql = "SELECT * 
            FROM mdl_coripodatacollection_instituteadmin 
            WHERE instituteid = " . $istituto->id . " AND userid NOT IN
                (SELECT userid 
                 FROM mdl_coripodatacollection_instituteadmin as ist_admin
                 JOIN mdl_coripodatacollection_istituti_x_progetto_x_aa as ist_erog ON ist_admin.instituteid = ist_erog.instituteid
                 WHERE ist_admin.instituteid <> " . $istituto->id . " AND erogation = " . $erogation->id . ") ";
    $admin_istituto = $DB->get_records_sql($sql);
    foreach ($admin_istituto as $admin){

        if (!$DB->record_exists('coripodatacollection_classadmin', ['userid' => $admin->userid])) {
            unenrol_user($course->id, $admin->userid);
        } else {

            $exist_sql = 'SELECT * 
                          FROM mdl_coripodatacollection_classes as cls 
                          JOIN mdl_coripodatacollection_classadmin as cls_admin on cls.id=cls_admin.classid
                          WHERE cls_admin.userid=' . $admin->userid . ' AND cls.erogazione=' . $erogation->id . '
                          AND cls.istituto <> ' . $admin->instituteid;

            if (!$DB->record_exists_sql($exist_sql)) {
                unenrol_user($course->id, $admin->userid);
            }
        }
    }

    $teachers = $DB->get_records('coripodatacollection_teachers', ['instituteid' => $instituteid]);
    foreach ($teachers as $t) {

        $continuare = false;
        $erog_ist = $DB->get_records('coripodatacollection_istituti_x_progetto_x_aa', ['erogation' => $erogation->id]);
        foreach ($erog_ist as $ei) {
            if ($DB->record_exists('coripodatacollection_instituteadmin',
                    ['instituteid' => $ei->instituteid, 'userid' => $t->userid])) {
                $continuare = true;
                break;
            }
        }

        if($continuare)
            continue;

        $exist_sql = 'SELECT * 
                      FROM mdl_coripodatacollection_classes as cls 
                      JOIN mdl_coripodatacollection_classadmin as cls_admin on cls.id=cls_admin.classid
                      WHERE cls_admin.userid=' . $t->userid . ' AND cls.erogazione=' . $erogation->id . '
                      AND cls.istituto <> ' . $instituteid;

        if (!$DB->record_exists_sql($exist_sql)) {
            unenrol_user($course->id, $t->userid);
        }
    }

    $classes = $DB->get_records('coripodatacollection_classes',
            ['erogazione' => $erogation->id, 'istituto' => $instituteid]);
    $DB->delete_records('coripodatacollection_classes',
            ['erogazione' => $erogation->id, 'istituto' => $instituteid]);
    foreach ($classes as $class) {
        $DB->delete_records('coripodatacollection_risultati', ['classe' => $class->id]);
        $DB->delete_records('coripodatacollection_medie_risultati', ['classe' => $class->id]);
        $DB->delete_records('coripodatacollection_stddev_risultati', ['classe' => $class->id]);
        $DB->delete_records('coripodatacollection_stddev_alunno', ['classe' => $class->id]);
        $DB->delete_records('coripodatacollection_class_students', ['classid' => $class->id]);
        $DB->delete_records('coripodatacollection_classadmin', ['classid' => $class->id]);
    }

    $DB->delete_records('coripodatacollection_istituti_x_progetto_x_aa',
            ['instituteid' => $instituteid, 'erogation' => $erogation->id]);


    redirect(new moodle_url('/mod/coripodatacollection/erogationmanager.php', ['page' => 'institutes', 'id' => $id]));
}

if ($page == 'instituteadd') {

    $instituteid = required_param('instituteid', PARAM_INT);
    $context = context_course::instance($course->id);


    // Adding the directors of institutes and the institutes to the project new edition.
    $institute = $DB->get_record_sql('SELECT * FROM {coripodatacollection_istituti} as ins
                                           join {coripodatacollection_istituti_x_progetto} on ins.id = instituteid
                                           WHERE projectid = ' . $erogation->projectid . ' AND ins.id = ' . $instituteid);
    $admin_istituto = $DB->get_records('coripodatacollection_instituteadmin', ['instituteid' => $institute->instituteid]);

    foreach ($admin_istituto as $admin) {
        if (!is_enrolled($context, $admin->userid)) {
            enrol_try_internal_enrol($course->id, $admin->userid);
        }
    }

    $newinstituteinerogation = new stdClass();
    $newinstituteinerogation->instituteid = $institute->instituteid;
    $newinstituteinerogation->projectid = $erogation->projectid;
    $newinstituteinerogation->erogation = $erogation->id;
    $DB->insert_record('coripodatacollection_istituti_x_progetto_x_aa', $newinstituteinerogation);

    redirect(new moodle_url('/mod/coripodatacollection/erogationmanager.php', ['page' => 'institutes', 'id' => $id]));
}

if ($page == 'periods') {

    $mode = required_param('mode', PARAM_TEXT);
    $actualpage = new moodle_url('/mod/coripodatacollection/erogationmanager.php',
            ['id' => $cm->id, 'page' => 'periods', 'mode' => $mode]);
    $dates_selctors = new \mod_coripodatacollection\forms\periods_manager($actualpage);

    $erogation = $DB->get_record('coripodatacollection_erogations', ['courseid' => $course->id],
            'start_censimento, end_censimento, start_val_pre, end_val_pre, start_val_post, end_val_post');

    $dates_selctors->set_data($erogation);
    if ($mode == 'view') {
        $dates_selctors->freeze_all();
    }

    if ($dates_selctors->is_cancelled()) {
        $return_page = $actualpage = new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                ['id' => $cm->id, 'page' => 'periods', 'mode' => 'view']);
        redirect($return_page);
    } elseif ($data = $dates_selctors->get_data()) {

        $erogation = $DB->get_record('coripodatacollection_erogations', ['courseid' => $course->id]);
        $data->id = $erogation->id;
        $DB->update_record('coripodatacollection_erogations', $data);
        $return_page = $actualpage = new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                ['id' => $cm->id, 'page' => 'periods', 'mode' => 'view']);
        redirect($return_page);
    }
}

if ($page == 'gruppi') {

    $mode = required_param('mode', PARAM_TEXT);

    if ($mode == 'delete') {

        $centerid = optional_param('centreid', -1, PARAM_INT);
        $confirm = optional_param('confirm_center_delete', 0, PARAM_INT);
        if ($confirm == 1 && $centerid != -1){

            $DB->delete_records('coripodatacollection_gruppi', ['id' => $centerid]);
            $DB->delete_records('coripodatacollection_alunni_gruppo', ['groupid' => $centerid]);

            $redirect_url = new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                    ['id' => $cm->id, 'page' => 'gruppi', 'mode' => 'view']);
            redirect($redirect_url);

        }
    } elseif ($mode == 'new_center' || $mode == 'view_centro') {
        $groupid = optional_param('groupid', -1, PARAM_INT);
        $actualpage = new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                ['id' => $cm->id, 'page' => 'gruppi', 'mode' => $mode, 'groupid' => $groupid]);
        $group_form = new \mod_coripodatacollection\forms\reinforcegroup_form($actualpage,
                ['viewmode' => $mode == 'view_centro', 'groupid' => $groupid]);

        if ($group_form->is_cancelled()) {
            $redirect_url = new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                    ['id' => $cm->id, 'page' => 'gruppi', 'mode' => 'view']);
            redirect($redirect_url);
        } else if ($data = $group_form->get_data()) {
            if ($mode == 'view_centro') {
                $data->id = $data->groupid;
                $DB->update_record('coripodatacollection_gruppi', $data);
                $redirect_url = new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                        ['id' => $cm->id, 'page' => 'gruppi', 'mode' => 'view']);
            } else {
                $data->erogationid = $erogation->id;
                $DB->insert_record('coripodatacollection_gruppi', $data);
                $redirect_url = new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                        ['id' => $cm->id, 'page' => 'gruppi', 'mode' => 'view']);
            }
            redirect($redirect_url);
        }
    } elseif ($mode == 'group_students'){

        require_once("$CFG->libdir/formslib.php");

        $groupid = required_param('groupid', PARAM_INT);
        $group = $DB->get_record('coripodatacollection_gruppi', ['id' => $groupid]);
        $actualpage = new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                ['id' => $id, 'page' => 'gruppi' ,'mode' => 'group_students', 'groupid' => $groupid]);
        $note_form = new MoodleQuickForm('', 'POST', $actualpage);
        $note_form->addElement('textarea', 'nota', get_string('group_note', 'mod_coripodatacollection'),
                                            'wrap="virtual" rows="10" style="width: 100%"');
        $note_form->setType('nota', PARAM_TEXT);
        $note_form->addElement('submit', 'submitbutton_noteform', get_string('save_note', 'mod_coripodatacollection'));
        if (isset($_POST['submitbutton_noteform'])) {
            $nota = $_POST['nota'] ?? '';
            $DB->update_record('coripodatacollection_gruppi', ['id' => $groupid, 'nota' => $nota]);
            $group = $DB->get_record('coripodatacollection_gruppi', ['id' => $groupid]);
        }
        $note_form->setDefault('nota', $group->nota);

    }elseif ($mode == 'group_students_remove') {

        $confirm = optional_param('confirm', -1, PARAM_INT);
        if ($confirm == 1) {

            $recordid = required_param('recordid', PARAM_INT);
            $groupid = required_param('groupid', PARAM_INT);
            $DB->delete_records('coripodatacollection_alunni_gruppo', ['id' => $recordid]);

            $redirect_url = new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                    ['id' => $cm->id, 'page' => 'gruppi', 'mode' => 'group_students', 'groupid' => $groupid]);
            redirect($redirect_url);

        }

    } elseif ($mode == 'group_students_add') {

        $groupid = required_param('groupid', PARAM_INT);
        $studentid = required_param('studentid', PARAM_INT);
        $DB->insert_record('coripodatacollection_alunni_gruppo',
                ['erogationid' => $erogation->id, 'groupid' => $groupid, 'studentid' => $studentid]);

        $redirect_url = new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                ['id' => $cm->id, 'page' => 'gruppi', 'mode' => 'group_students', 'groupid' => $groupid]);
        redirect($redirect_url);

    } elseif ($mode == 'complete_group') {

        $groupid = required_param('groupid', PARAM_INT);
        $DB->update_record('coripodatacollection_gruppi', ['id' => $groupid, 'completato' => 1]);
        $redirect_url = new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                ['id' => $cm->id, 'page' => 'gruppi', 'mode' => 'view']);
        redirect($redirect_url);

    }
}


echo $OUTPUT->header();

if ($page == 'evaluators') {

    // Code for displaying the list of evaluators for an erogation.
    echo html_writer::tag('h2', 'Valutatori', ['class' => 'h2']);

    $sql ='SELECT id, firstname, lastname, email, username FROM {user} 
           WHERE id in  ( 
              SELECT userid 
              FROM {coripodatacollection_evaluators} 
              WHERE projectid = ' . $erogation->projectid . ' )';

    $evaluators = [];
    foreach ($DB->get_records_sql($sql) as $evaluator) {
        if (is_enrolled(context_course::instance($course->id), $evaluator->id)) {
            $evaluators[] = $evaluator;
        }
    }

    $table = new html_table();
    $table->align = ['center', 'center', 'center', 'center', 'center'];
    $table->head = [
            get_string('username', 'mod_coripodatacollection'),
            get_string('name', 'mod_coripodatacollection'),
            get_string('surname', 'mod_coripodatacollection'),
            get_string('email', 'mod_coripodatacollection'),
            ''
    ];

    if (!empty($evaluators)) {
        foreach ($evaluators as $e) {
            $removebutton = html_writer::link(
                    new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                            ['page' => 'evalremove', 'id' => $id, 'userid' => $e->id]),
                    $OUTPUT->pix_icon(
                            'i/excluded',
                            get_string('erogation_remove_evaluator', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );
            $table->data[] = new html_table_row([$e->username, $e->firstname, $e->lastname, $e->email, $removebutton]);
        }
        echo html_writer::table($table);
    } else {
        $noinputimg = 'pix/view_zero_state.svg';
        echo html_writer::start_div('text-xs-center text-center mt-4');
        echo html_writer::img(
                $noinputimg,
                get_string('erogation_no_evaluators', 'mod_coripodatacollection'),
                ['style' => 'display: block; margin: 0 auto;']);
        echo html_writer::tag(
                'h5',
                get_string('erogation_no_evaluators', 'mod_coripodatacollection'),
                ['class' => 'h5 mt-3 mb-0']);
        echo html_writer::end_div();
    }


    // Code for displaying the list of evaluators not in the erogation.
    $sql ='SELECT id, firstname, lastname, email, username FROM {user} 
           WHERE id in  ( 
              SELECT userid 
              FROM {coripodatacollection_evaluators} 
              WHERE projectid = ' . $erogation->projectid . ' )';

    $evaluators = [];
    foreach ($DB->get_records_sql($sql) as $evaluator) {
        if (!is_enrolled(context_course::instance($course->id), $evaluator->id)) {
            $evaluators[] = $evaluator;
        }
    }

    $table = new html_table();
    $table->align = ['center', 'center', 'center', 'center', 'center'];
    $table->head = [
            get_string('username', 'mod_coripodatacollection'),
            get_string('name', 'mod_coripodatacollection'),
            get_string('surname', 'mod_coripodatacollection'),
            get_string('email', 'mod_coripodatacollection'),
            ''
    ];

    if (!empty($evaluators)) {
        foreach ($evaluators as $e) {
            $removebutton = html_writer::link(
                    new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                            ['page' => 'evaladd', 'id' => $id, 'userid' => $e->id]),
                    $OUTPUT->pix_icon(
                            'i/enrolusers',
                            get_string('erogation_add_evaluator', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );
            $table->data[] = new html_table_row([$e->username, $e->firstname, $e->lastname, $e->email, $removebutton]);
        }

        $form = new MoodleQuickForm('', 'GET', '');
        $form->addElement('header', 'header',
                get_string('erogation_add_evaluator', 'mod_coripodatacollection'));
        $form->addElement('html', html_writer::table($table));
        $form->display();
    }

    $linkaddbutton = new moodle_url('/mod/coripodatacollection/projectview.php',
            ['page' => 'evaluators','id' => $erogation->projectid]);
    $stickyfooterelements =  html_writer::tag(
            'a',
            get_string('erogation_admin_page', 'mod_coripodatacollection'),
            ['href' => $linkaddbutton, 'class' => 'btn btn-primary', 'style' => 'display: inline-block;']
    );
    $stickyfooter = new \core\output\sticky_footer($stickyfooterelements);
    echo $OUTPUT->render($stickyfooter);


} elseif ($page == 'institutes') {

    // Codice istituti già presenti nell'erogazione.
    echo html_writer::tag('h2', get_string('institutes', 'mod_coripodatacollection'), ['class' => 'h2']);

    $erogation_institutes = $DB->get_records('coripodatacollection_istituti_x_progetto_x_aa', ['erogation' => $erogation->id]);

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

    if (!empty($erogation_institutes)) {
        foreach ($erogation_institutes as $erog_ins) {

            $istituto = $DB->get_record('coripodatacollection_istituti', ['id' => $erog_ins->instituteid]);
            $admin_istituto = $DB->get_record('coripodatacollection_instituteadmin',
                    ['instituteid' => $erog_ins->instituteid], '*', IGNORE_MULTIPLE);
            $admin_istituto = $DB->get_record('user', ['id' => $admin_istituto->userid]);


            $delbutton = html_writer::link(
                    new moodle_url(
                            $PAGE->url,
                            ['page' => 'instituteremove', 'id' => $id, 'instituteid' => $erog_ins->instituteid]
                    ),
                    $OUTPUT->pix_icon('t/delete', get_string('cancel', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );

            $table->data[] = new html_table_row([
                    $istituto->denominazioneistituto,
                    $admin_istituto->username,
                    $admin_istituto->lastname . ' ' . $admin_istituto->firstname,
                    $admin_istituto->email,
                    $delbutton]);
        }
        echo html_writer::table($table);
    } else {
        $noinputimg = 'pix/noentries_zero_state.svg';
        echo html_writer::start_div('text-xs-center text-center mt-4');
        echo html_writer::img(
                $noinputimg,
                get_string('erogation_no_institutes', 'mod_coripodatacollection'),
                ['style' => 'display: block; margin: 0 auto;']);
        echo html_writer::tag(
                'h5',
                get_string('erogation_no_institutes', 'mod_coripodatacollection'),
                ['class' => 'h5 mt-3 mb-0']);
        echo html_writer::end_div();
    }

    $project_institutes = $DB->get_records('coripodatacollection_istituti_x_progetto', ['projectid' => $erogation->projectid]);

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
        foreach ($project_institutes as $prog_ins) {

            if ($DB->record_exists('coripodatacollection_istituti_x_progetto_x_aa',
                    ['instituteid' => $prog_ins->instituteid, 'projectid' =>  $prog_ins->projectid, 'erogation' => $erogation->id])){
                continue;
            }

            $istituto = $DB->get_record('coripodatacollection_istituti', ['id' => $prog_ins->instituteid]);
            $admin_istituto = $DB->get_record('coripodatacollection_instituteadmin',
                    ['instituteid' => $prog_ins->instituteid], '*', IGNORE_MULTIPLE);
            $admin_istituto = $DB->get_record('user', ['id' => $admin_istituto->userid]);

            $delbutton = html_writer::link(
                    new moodle_url(
                            $PAGE->url,
                            ['page' => 'instituteadd', 'id' => $id, 'instituteid' => $prog_ins->instituteid]
                    ),
                    $OUTPUT->pix_icon('i/nosubcat', get_string('cancel', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );

            $table->data[] = new html_table_row([
                    $istituto->denominazioneistituto,
                    $admin_istituto->username,
                    $admin_istituto->lastname . ' ' . $admin_istituto->firstname,
                    $admin_istituto->email,
                    $delbutton]);
        }
        $form = new MoodleQuickForm('', 'GET', '');
        $form->addElement('header', 'header',
                get_string('erogation_add_institute', 'mod_coripodatacollection'));
        $form->addElement('html', html_writer::table($table));
        $form->display();
    }



    $linknewinstitutebutton = new moodle_url('/mod/coripodatacollection/projectview.php',
            ['page' => 'institutes','id' => $erogation->projectid]);
    $stickyfooterelements =   html_writer::tag(
            'a',
            get_string('erogation_admin_page', 'mod_coripodatacollection'),
            ['href' => $linknewinstitutebutton, 'class' => 'btn btn-primary', 'style' => 'display: inline-block;']
    );

    $stickyfooter = new \core\output\sticky_footer($stickyfooterelements);
    echo $OUTPUT->render($stickyfooter);

} elseif ($page == 'periods'){

    $dates_selctors->display();

    if ($mode == 'view') {

        $linknewinstitutebutton = new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                ['id' => $cm->id, 'page' => 'periods', 'mode' => 'edit']);
        $stickyfooterelements =   html_writer::tag('a', get_string('modify', 'mod_coripodatacollection'),
                ['href' => $linknewinstitutebutton, 'class' => 'btn btn-primary', 'style' => 'display: inline-block;']
        );

        $stickyfooter = new \core\output\sticky_footer($stickyfooterelements);
        echo $OUTPUT->render($stickyfooter);

    }
} elseif ($page == 'gruppi') {

    $mode = optional_param('mode', 'view', PARAM_TEXT);

    if ($mode == 'view') {

        echo html_writer::tag('h2', get_string('groups', 'mod_coripodatacollection'),
                ['class' => 'h2', 'style' => 'margin-bottom: 50px']);

        $gruppi = $DB->get_records('coripodatacollection_gruppi', ['erogationid' => $erogation->id]);

        $table = new html_table();
        $table->head = [
                get_string('group_code', 'mod_coripodatacollection'),
                get_string('sede', 'mod_coripodatacollection'),
                get_string('dettaglio_sede', 'mod_coripodatacollection'),
                get_string('center_address', 'mod_coripodatacollection'),
                get_string('center_zone', 'mod_coripodatacollection'),
                get_string('aula', 'mod_coripodatacollection'),
                get_string('day1', 'mod_coripodatacollection'),
                get_string('orario1', 'mod_coripodatacollection'),
                get_string('day1', 'mod_coripodatacollection'),
                get_string('orario2', 'mod_coripodatacollection'),
                get_string('student_1', 'mod_coripodatacollection'),
                get_string('student_2', 'mod_coripodatacollection'),
                get_string('student_3', 'mod_coripodatacollection'),
                get_string('student_4', 'mod_coripodatacollection'),
                get_string('student_5', 'mod_coripodatacollection'),
                ''
        ];
        $table->align = ['center', 'center', 'center', 'center', 'center', 'center', 'center', 'center',
                'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center'];
        foreach ($gruppi as $gruppo){

            if ($gruppo->completato == 1 || $gruppo->chiuso == 1 || $gruppo->definitivo == 1)
                continue;

            $complete_url = new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                    ['id' => $cm->id, 'page' => 'gruppi', 'mode' => 'complete_group', 'groupid' => $gruppo->id]);
            $complete_group_button = html_writer::tag('a',
                    get_string('complete_group', 'mod_coripodatacollection'),
                    ['href' => $complete_url, 'class' => 'btn btn-primary', 'style' => 'margin-right: 5px']
            );

            $delbutton = html_writer::link(
                    new moodle_url(
                            $PAGE->url,
                            ['id' => $id, 'page' => 'gruppi', 'mode' => 'delete', 'centerid' => $gruppo->id]
                    ),
                    $OUTPUT->pix_icon('i/delete', get_string('cancel', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );

            $editbutton = html_writer::link(
                    new moodle_url(
                            $PAGE->url,
                            ['id' => $id, 'page' => 'gruppi', 'mode' => 'view_centro', 'groupid' => $gruppo->id]
                    ),
                    $OUTPUT->pix_icon('i/edit', get_string('modify', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );

            $addbutton = html_writer::link(
                    new moodle_url(
                            $PAGE->url,
                            ['id' => $id, 'page' => 'gruppi', 'mode' => 'group_students', 'groupid' => $gruppo->id]
                    ),
                    $OUTPUT->pix_icon('i/assignroles', get_string('add', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );

            $group_students = $DB->get_records_sql('
                SELECT DISTINCT alunni.* 
                FROM mdl_coripodatacollection_alunni_gruppo as alunni_gruppo
                JOIN mdl_coripodatacollection_alunni as alunni on alunni.id = alunni_gruppo.studentid
                WHERE alunni_gruppo.groupid =  ' . $gruppo->id);
            $group_students = array_values($group_students);

            $table->data[] = [
                    $gruppo->codice,
                    $gruppo->sede,
                    $gruppo->dettaglio_sede,
                    $gruppo->indirizzo,
                    $gruppo->zona,
                    $gruppo->aula,
                    $gruppo->giorno1,
                    $gruppo->orario1,
                    $gruppo->giorno2,
                    $gruppo->orario2,
                    $group_students[0]->hash_code ? getstudent_colored_cell($group_students[0], $erogation) : '',
                    $group_students[1]->hash_code ? getstudent_colored_cell($group_students[1], $erogation) : '',
                    $group_students[2]->hash_code ? getstudent_colored_cell($group_students[2], $erogation) : '',
                    $group_students[3]->hash_code ? getstudent_colored_cell($group_students[3], $erogation) : '',
                    $group_students[4]->hash_code ? getstudent_colored_cell($group_students[4], $erogation) : '',
                    $gruppo->completato != 1 ? $complete_group_button . $editbutton . $addbutton . $delbutton :
                            get_string('complete', 'mod_coripodatacollection')];
        }

        if (empty($gruppi)) {
            $noinputimg = 'pix/noentries_zero_state.svg';
            $html_string = html_writer::start_div('text-xs-center text-center mt-4',
                    ['style' => ' display: flex; align-items: center; 
                                justify-content: center; gap: 20px; margin: 20px auto; padding-top : 100px']);
            $html_string .= html_writer::img(
                    $noinputimg,
                    get_string('no_centers', 'mod_coripodatacollection'),
                    ['style' => 'width: 100px; height: auto;']);
            $html_string .= html_writer::tag(
                    'h5',
                    get_string('no_centers', 'mod_coripodatacollection'),
                    ['class' => 'h5 mt-3 mb-0']);
            $html_string .= html_writer::end_div();
            echo $html_string;
        } else {
            echo html_writer::table($table);
            $info_table = new \mod_coripodatacollection\forms\infogruppi_form('', ['erogationid' => $erogation->id]);
            $info_table->display();
        }

        $linknewcenter = new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                ['id' => $cm->id, 'page' => 'gruppi', 'mode' => 'new_center']);
        $stickyfooterelements =   html_writer::tag('a',
                get_string('new_group', 'mod_coripodatacollection'),
                ['href' => $linknewcenter, 'class' => 'btn btn-primary', 'style' => 'display: inline-block;']
        );

        $stickyfooter = new \core\output\sticky_footer($stickyfooterelements);
        echo $OUTPUT->render($stickyfooter);

    } elseif ($mode == 'new_center' || $mode == 'view_centro') {

        if ($mode == 'new_center')
            echo html_writer::tag('h2', get_string('new_groups', 'mod_coripodatacollection'),
                    ['class' => 'h2', 'style' => 'margin-bottom: 25px']);
        else {
            $groupid = optional_param('groupid', -1, PARAM_INT);
            $group = $DB->get_record('coripodatacollection_gruppi', ['id' => $groupid]);
            echo html_writer::tag('h2',
                    get_string('group', 'mod_coripodatacollection') . ' ' . $group->codice,
                    ['class' => 'h2', 'style' => 'margin-bottom: 25px']);
        }
        $group_form->display();
    } elseif ($mode == 'delete') {

        $centerid = optional_param('centerid', -1, PARAM_INT);
        $formcontinue = new single_button(new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                ['id' => $id, 'page' => 'gruppi', 'mode' => 'delete', 'centreid' => $centerid, 'confirm_center_delete' => 1]),
                get_string('yes', 'mod_coripodatacollection'));
        $formcancel = new single_button(new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                ['id' => $id, 'page' => 'gruppi', 'mode' => 'view']),
                get_string('no', 'mod_coripodatacollection'), 'get');
        echo $OUTPUT->confirm(get_string('delete_center_message', 'mod_coripodatacollection'),
                $formcontinue, $formcancel);

    } elseif ($mode == 'group_students') {

        $groupid = required_param('groupid', PARAM_INT);
        $group = $DB->get_record('coripodatacollection_gruppi', ['id' => $groupid]);
        $group_students = $DB->get_records_sql('
                SELECT DISTINCT alunni.*, alunni_gruppo.id as alunno_gruppo_id
                FROM mdl_coripodatacollection_alunni_gruppo as alunni_gruppo
                JOIN mdl_coripodatacollection_alunni as alunni on alunni.id = alunni_gruppo.studentid
                WHERE alunni_gruppo.groupid =  ' . $groupid);

        echo html_writer::tag('h2',
                get_string('group_student', 'mod_coripodatacollection') . ' ' . $group->codice,
                ['class' => 'h2', 'style' => 'margin-bottom: 25px']);

        $group_students_table = new html_table();
        $group_students_table->head = [
            get_string('code', 'mod_coripodatacollection'),
            get_string('color', 'mod_coripodatacollection'),
            get_string('center_zone', 'mod_coripodatacollection'),
            ''
        ];
        $group_students_table->align = ['center', 'center', 'center', 'center'];

        foreach ($group_students as $group_student) {

            $indice = $DB->get_record('coripodatacollection_indici_valutazione',
                    ['erogazione' => $erogation->id, 'alunno' => $group_student->id]);
            $class = $DB->get_record('coripodatacollection_classes', ['id' => $indice->classe]);
            $istituto = $DB->get_record('coripodatacollection_istituti', ['id' => $class->istituto]);

            $button = html_writer::link(
                    new moodle_url(
                            $PAGE->url,
                            ['id' => $id, 'page' => 'gruppi', 'mode' => 'group_students_remove', 'groupid' => $groupid,
                                    'recordid' => $group_student->alunno_gruppo_id, 'studentid' => $group_student->id]
                    ),
                    $OUTPUT->pix_icon('i/excluded', get_string('remove', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );

            $group_students_table->data[] = [
                    $group_student->hash_code,
                    $indice->valutazione_globale,
                    $istituto->zona,
                    $button
            ];
        }

        if (empty($group_students)) {
            $noinputimg = 'pix/noentries_zero_state.svg';
            $html_string = html_writer::start_div('text-xs-center text-center mt-4',
                    ['style' => ' display: flex; align-items: center; 
                                justify-content: center; gap: 20px; margin: 20px auto; padding-top : 100px']);
            $html_string .= html_writer::img(
                    $noinputimg,
                    get_string('no_centers_students', 'mod_coripodatacollection'),
                    ['style' => 'width: 100px; height: auto;']);
            $html_string .= html_writer::tag(
                    'h5',
                    get_string('no_centers', 'mod_coripodatacollection'),
                    ['class' => 'h5 mt-3 mb-0']);
            $html_string .= html_writer::end_div();
            echo $html_string;
        } else {
            echo html_writer::table($group_students_table);
        }

        if (isset($_POST['submitbutton_noteform'])) {
            $paramsuccess = [
                    'title-error' => get_string('saved_note', 'mod_coripodatacollection'),
                    'message-error' => ''
            ];
            echo $OUTPUT->render_from_template('coripodatacollection/allertsuccess', $paramsuccess);
        }
        $note_form->display();

        echo html_writer::tag('h2', get_string('add_student_part', 'mod_coripodatacollection'),
                ['class' => 'h2', 'style' => 'margin-top: 25px; margin-bottom: 25px']);

        $actualpage = new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                ['id' => $id, 'page' => 'gruppi' ,'mode' => 'group_students', 'groupid' => $groupid]);
        $search_form = new MoodleQuickForm('', 'GET', $actualpage);

        $search_code = $search_form->createElement('text', 'code_search', '',
                ['placeholder' => get_string('code', 'mod_coripodatacollection')]);
        $colors = [
                '' => get_string('search_color', 'mod_coripodatacollection'),
                get_string('dark_green', 'mod_coripodatacollection') =>
                        get_string('dark_green', 'mod_coripodatacollection'),
                get_string('yellow', 'mod_coripodatacollection') =>
                        get_string('yellow', 'mod_coripodatacollection'),
                get_string('red', 'mod_coripodatacollection') =>
                        get_string('red', 'mod_coripodatacollection')
        ];
        $search_color = $search_form->createElement('select', 'color_search', '', $colors);

        $zone = [
                '' => get_string('search_zona', 'mod_coripodatacollection'),
                get_string('north', 'mod_coripodatacollection') =>
                    get_string('north', 'mod_coripodatacollection'),
                get_string('south', 'mod_coripodatacollection') =>
                        get_string('south', 'mod_coripodatacollection'),
                get_string('east', 'mod_coripodatacollection') =>
                        get_string('east', 'mod_coripodatacollection'),
                get_string('west', 'mod_coripodatacollection') =>
                        get_string('west', 'mod_coripodatacollection')
        ];
        $search_zona = $search_form->createElement('select', 'zone_search', '', $zone);

        $search_form->setType('code_search', PARAM_TEXT);
        $searh_button = $search_form->createElement('submit', 'submitbutton', 'Cerca');
        $search_form->addGroup([$search_code, $search_color, $search_zona, $searh_button], 'group',
                get_string('search_by_code_color_zone', 'mod_coripodatacollection'));
        $search_form->addHelpButton('group', 'search_by_code_color_zone', 'mod_coripodatacollection');
        $search_form->display();

        $code_search = '';
        if (isset($_GET['group']['code_search']))
            $code_search = $_GET['group']['code_search'];

        $color_search = '';
        if (isset($_GET['group']['color_search']))
            $color_search = $_GET['group']['color_search'];

        $zone_search = '';
        if (isset($_GET['group']['zone_search']))
            $zone_search = $_GET['group']['zone_search'];

        $sql = '
        SELECT DISTINCT alunni.*, indici.erogazione, indici.classe, indici.alunno, indici.periodo, indici.valutazione_globale, istituti.zona
        FROM mdl_coripodatacollection_alunni as alunni
        JOIN mdl_coripodatacollection_class_students as class_students on class_students.studentid = alunni.id 
        JOIN mdl_coripodatacollection_indici_valutazione as indici on indici.alunno = alunni.id
        JOIN mdl_coripodatacollection_classes as classes on classes.id = indici.classe
        JOIN mdl_coripodatacollection_istituti as istituti on istituti.id = classes.istituto
        WHERE class_students.consenso_recupero = 1 and indici.periodo = "prerinforzo" and indici.erogazione = ' . $erogation->id . '
            and (indici.valutazione_classe = "Rosso" or indici.valutazione_classe = "Giallo") and
            (indici.valutazione_globale = "Verde Scuro" or indici.valutazione_globale = "Giallo" or indici.valutazione_globale = "Rosso")
        ';
        $students = $DB->get_records_sql($sql);
        $table_add_studets = new html_table();
        $table_add_studets->head = [
                get_string('code', 'mod_coripodatacollection'),
                get_string('color', 'mod_coripodatacollection'),
                get_string('center_zone', 'mod_coripodatacollection'),
                ''
        ];
        $table_add_studets->align = ['center', 'center', 'center', 'center'];
        foreach ($students as $student) {

            if ($code_search != '' && !str_starts_with($student->hash_code, $code_search))
                continue;
            if ($color_search != '' && $student->valutazione_globale != $color_search)
                continue;
            if ($zone_search != '' && $student->zona != $zone_search)
                continue;
            if ($DB->record_exists('coripodatacollection_alunni_gruppo',
                    ['erogationid' => $erogation->id, 'studentid' => $student->id]))
                continue;

            $button = html_writer::link(
                    new moodle_url(
                            $PAGE->url,
                            ['id' => $id, 'page' => 'gruppi', 'mode' => 'group_students_add',
                                    'groupid' => $groupid, 'studentid' => $student->id]
                    ),
                    $OUTPUT->pix_icon('i/addblock', get_string('add', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );

            $table_add_studets->data[] = [
                    $student->hash_code,
                    $student->valutazione_globale,
                    $student->zona,
                    $button
            ];
        }

        if (empty($students) || empty($table_add_studets->data)) {
            $noinputimg = 'pix/noentries_zero_state.svg';
            $html_string = html_writer::start_div('text-xs-center text-center mt-4',
                    ['style' => ' display: flex; align-items: center; 
                                justify-content: center; gap: 20px; margin: 20px auto; padding-top : 100px']);
            $html_string .= html_writer::img(
                    $noinputimg,
                    get_string('no_students', 'mod_coripodatacollection'),
                    ['style' => 'width: 100px; height: auto;']);
            $html_string .= html_writer::tag(
                    'h5',
                    get_string('no_students', 'mod_coripodatacollection'),
                    ['class' => 'h5 mt-3 mb-0']);
            $html_string .= html_writer::end_div();
            echo $html_string;
        } elseif (count($group_students) == 5) {
            $noinputimg = 'pix/noentries_zero_state.svg';
            $html_string = html_writer::start_div('text-xs-center text-center mt-4',
                    ['style' => ' display: flex; align-items: center; 
                                justify-content: center; gap: 20px; margin: 20px auto; padding-top : 100px']);
            $html_string .= html_writer::img(
                    $noinputimg,
                    get_string('group_full', 'mod_coripodatacollection'),
                    ['style' => 'width: 100px; height: auto;']);
            $html_string .= html_writer::tag(
                    'h5',
                    get_string('group_full', 'mod_coripodatacollection'),
                    ['class' => 'h5 mt-3 mb-0']);
            $html_string .= html_writer::end_div();
            echo $html_string;
        } else {
            echo html_writer::table($table_add_studets);
        }

        $stickyfooterelements = \html_writer::start_div('', ['style' => 'display: flex; justify-content: space-between;']);
        $stickyfooterelements .= \html_writer::start_div('', ['style' => 'display: block; text-align: center;']);
        $linkaddbutton = new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                ['id' => $id, 'page' => 'gruppi','mode' => 'view']);
        $stickyfooterelements .=  html_writer::tag(
                'a',
                get_string('back', 'mod_coripodatacollection'),
                ['href' => $linkaddbutton, 'class' => 'btn btn-secondary']
        );
        $stickyfooterelements .= \html_writer::end_div();
        $stickyfooterelements .= \html_writer::end_div();
        $stickyfooter = new \core\output\sticky_footer($stickyfooterelements, ' ',
                ['style' => 'display: flex; justify-content: space-between;']);
        echo $OUTPUT->render($stickyfooter);


    } elseif ($mode == 'group_students_remove') {

        $recordid = required_param('recordid', PARAM_INT);
        $groupid = required_param('groupid', PARAM_INT);
        $studentid = required_param('studentid', PARAM_INT);
        $student = $DB->get_record('coripodatacollection_alunni', ['id' => $studentid]);
        $formcontinue = new single_button(new moodle_url('/mod/coripodatacollection/erogationmanager.php',
            ['id' => $id, 'page' => 'gruppi', 'mode' => 'group_students_remove',
                    'recordid' => $recordid, 'groupid' => $groupid,'studentid' => $studentid, 'confirm' => 1]),
            get_string('yes', 'mod_coripodatacollection'));
        $formcancel = new single_button(new moodle_url('/mod/coripodatacollection/erogationmanager.php',
                ['id' => $id, 'page' => 'gruppi', 'mode' => 'group_students', 'groupid' => $groupid]),
                get_string('no', 'mod_coripodatacollection'), 'get');
        echo $OUTPUT->confirm(
                sprintf(
                        get_string('delete_group_student_message', 'mod_coripodatacollection'),
                        $student->hash_code
                ),
                $formcontinue, $formcancel);
    }



}

echo html_writer::div('', '', ['style ' => 'height: 250px;']);
echo $OUTPUT->footer();














