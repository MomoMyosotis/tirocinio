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
$page = optional_param('page', 'classes', PARAM_TEXT);

if ($id) {
    $cm = get_coursemodule_from_id('coripodatacollection', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('coripodatacollection', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('coripodatacollection', ['id' => $c], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $moduleinstance->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('coripodatacollection', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

$context = context_system::instance();
require_login($course, true, $cm);
require_capability('mod/coripodatacollection:schoolmanager', $context);
require_capability('mod/data:viewentry', $context);

$modulecontext = context_module::instance($cm->id);

$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
$PAGE->add_body_class('wide');

$page = optional_param('page', 'classes', PARAM_TEXT);
$classid = optional_param('classid', -1, PARAM_INT);


$user_institute_administrations = $DB->get_records('coripodatacollection_instituteadmin', ['userid' => $USER->id]);
$erogation = $DB->get_record('coripodatacollection_erogations', ['courseid' => $course->id]);
$institutes = [];
foreach ($user_institute_administrations as $uia) {

    if ($DB->record_exists('coripodatacollection_istituti_x_progetto_x_aa',
            ['instituteid' => $uia->instituteid, 'projectid' => $erogation->projectid, 'erogation' => $erogation->id])) {

        $istitute_toadd = $DB->get_record('coripodatacollection_istituti', ['id' => $uia->instituteid]);
        $institutes[$istitute_toadd->id] = $istitute_toadd;

    }
}

if (empty($institutes)) {
    $teaching_classes = $DB->get_records_sql('SELECT DISTINCT mdl_coripodatacollection_classes.* 
                                                  FROM mdl_coripodatacollection_classes
                                                  JOIN mdl_coripodatacollection_classadmin ON classid=mdl_coripodatacollection_classes.id
                                                  WHERE userid=' . $USER->id . ' AND erogazione=' . $erogation->id);
    if (!empty($teaching_classes)) {
        redirect(new moodle_url('/mod/coripodatacollection/viewteacher.php', ['id' => $id, 'page' => 'classes']));
    }
}


$urlparam = ['id' => $cm->id, 'page' => $page];
if ($classid != -1) {
    $urlparam['classid'] = $classid;
}
if (optional_param('instituteid', -1, PARAM_INT) != -1) {
    $urlparam['instituteid'] = optional_param('instituteid', -1, PARAM_INT);
}
$PAGE->set_url('/mod/coripodatacollection/viewdirector.php', $urlparam);
if ($page == 'alunni' || $page == 'primevalutazioni' || $page == 'ultimevalutazioni' ||  $page == 'newalunno' || $page == 'modalunno'
        || $page == '') {
    $node = $PAGE->secondarynav->add(get_string('classi', 'mod_coripodatacollection'));
    $node->isactive = false;
    $node->action = new moodle_url('/mod/coripodatacollection/viewdirector.php', ['id' => $cm->id, 'page' => 'classes']);

    $node = $PAGE->secondarynav->add(get_string('alunni', 'mod_coripodatacollection'));
    $node->isactive = $page == 'alunni' || $page == 'newalunno' || $page == 'modalunno';
    $node->action = new moodle_url('/mod/coripodatacollection/viewdirector.php', ['id' => $cm->id, 'page' => 'alunni', 'classid' => $classid]);

    $node = $PAGE->secondarynav->add(get_string('primevalutazioni', 'mod_coripodatacollection'));
    $node->isactive = $page == 'primevalutazioni';
    $node->action = new moodle_url('/mod/coripodatacollection/viewdirector.php', ['id' => $cm->id, 'page' => 'primevalutazioni', 'classid' => $classid]);

    $node = $PAGE->secondarynav->add(get_string('ultimevalutazioni', 'mod_coripodatacollection'));
    $node->isactive = $page == 'ultimevalutazioni';
    $node->action = new moodle_url('/mod/coripodatacollection/viewdirector.php', ['id' => $cm->id, 'page' => 'ultimevalutazioni', 'classid' => $classid]);
} else {
    $node = $PAGE->secondarynav->add(get_string('classi', 'mod_coripodatacollection'));
    $node->isactive = true;
    $node->action = new moodle_url('/mod/coripodatacollection/viewdirector.php', ['id' => $cm->id, 'page' => 'classes']);
}



// Form per eliminazione classi.
$delclass = optional_param('delete', -1, PARAM_INT);
if ($delclass != -1) {
    $erogation = $DB->get_record('coripodatacollection_erogations',['courseid' => $course->id]);
    $current_date = time();
    if ( $erogation->end_censimento < $current_date ) {
        // Se non sono più nella fase di censimento
        redirect( new moodle_url('/mod/coripodatacollection/viewdirector.php', ['id' =>$id, 'page' => 'classes']));
    }
}
$delconfirm = optional_param('confirm', 0, PARAM_INT);
if ($delclass != -1 && $delconfirm == 1) {
    $DB->delete_records('coripodatacollection_classes', ['id' => $delclass]);
    $DB->delete_records('coripodatacollection_classhistory', ['classid' => $delclass]);
    redirect(new moodle_url('/mod/coripodatacollection/viewdirector.php',  ['id' => $id, 'page' => 'classes']));
}



// Form per inserimento classi
if ($page == 'newclass') {

    $modifyparam = optional_param('modify', -1, PARAM_INT);
    if ($modifyparam != -1) {

        $instituteid = optional_param('instituteid', -1, PARAM_INT);
        if ($instituteid == -1) {
            $class = $DB->get_record('coripodatacollection_classes', ['id' => $modifyparam]);
            redirect(new moodle_url('/mod/coripodatacollection/viewdirector.php',
                    ['id' => $id, 'page' => 'newclass', 'modify' => $modifyparam, 'instituteid' => $class->istituto]));
        }

        $class = $DB->get_record('coripodatacollection_classes', ['id' => $modifyparam]);
        $institute = $DB->get_record('coripodatacollection_istituti', ['id' => $class->istituto]);

        $returnlink = new moodle_url('/mod/coripodatacollection/viewdirector.php',
                ['id' => $id, 'page' => 'classes', 'classid' => $modifyparam]);
        $PAGE->set_url('/mod/coripodatacollection/viewdirector.php', ['id' => $cm->id, 'page' => $page, 'modify' => $modifyparam]);
        $newclass = new mod_coripodatacollection\forms\newclass_form(
                new moodle_url('/mod/coripodatacollection/viewdirector.php',
                        ['id' => $id, 'page' => 'newclass', 'modify'=> $modifyparam, 'instituteid' => $instituteid]),
                ['instituteid' => $institute->id, 'modifyclass' => $modifyparam, 'courseid' => $course->id]);
    } else {

        $instituteid = optional_param('instituteid', -1, PARAM_INT);

        if ($instituteid !== -1) {

            $institute = $DB->get_record('coripodatacollection_istituti', ['id' => $instituteid]);

            $erogation = $DB->get_record('coripodatacollection_erogations', ['courseid' => $course->id]);
            $current_date = time();
            if ($erogation->end_censimento < $current_date) {
                // Se non sono più nella fase di censimento
                redirect(new moodle_url('/mod/coripodatacollection/viewdirector.php', ['id' => $id, 'page' => 'classes']));
            }

            $returnlink = new moodle_url('/mod/coripodatacollection/viewdirector.php', ['id' => $cm->id, 'page' => 'classes']);
            $newclass = new mod_coripodatacollection\forms\newclass_form($PAGE->url,
                    ['instituteid' => $institute->id, 'courseid' => $course->id]);

        } else {
            if (count($institutes) == 1) {
                $institute = reset($institutes);
                redirect(new moodle_url('/mod/coripodatacollection/viewdirector.php',
                        ['id' => $id, 'page' => 'newclass', 'instituteid' => $institute->id]));
            }
        }
    }

    if (isset($newclass)) {

        if ($newclass->is_cancelled()) {
            $returnlink = new moodle_url('/mod/coripodatacollection/viewdirector.php',
                    ['id' => $id, 'page' => 'classes']);
            redirect($returnlink);
        } else if ($data = $newclass->get_data()) {

            $data->erogazione = $DB->get_record('coripodatacollection_erogations', ['courseid' => $course->id])->id;

            if ($data->pluriclasse == 1) {
                $data->anno = null;
            }

            if ($modifyparam == -1) {
                $data->can_edit_censimento = 0;
                $data->can_edit_val_pre = 0;
                $data->can_edit_val_post = 0;
                $data->statistichepre = 0;
                $data->risultatipre = 0;
                $data->completati_res_pre = 0;
                $data->valutazione_classe_pre = 0;
                $data->valutazione_globale_pre = 0;
                $data->statistichepost = 0;
                $data->risultatipost = 0;
                $data->completati_res_post = 0;
                $data->valutazione_classe_post = 0;
                $data->valutazione_globale_post = 0;
                $newclass = $DB->insert_record('coripodatacollection_classes', $data);

                $class_teacher = new stdClass();
                $class_teacher->classid = $newclass;
                $class_teacher->userid = $data->insegnante;
                $DB->insert_record('coripodatacollection_classadmin', $class_teacher);

                $plesso = $DB->get_record('coripodatacollection_plessi', ['id' => $data->plesso]);
                $user = $DB->get_record('user', ['id' => $class_teacher->userid]);
                $supportuser = core_user::get_support_user();
                $subject = get_string('add_edition_teacher_object', 'mod_coripodatacollection');
                $body = sprintf(
                        get_string('add_edition_teacher_body', 'mod_coripodatacollection'),
                        $user->lastname . ' ' . $user->firstname,
                        $institute->denominazioneistituto,
                        $data->anno . '-' . $data->classe,
                        $plesso->denominazioneplesso,
                        $course->fullname,
                        $SITE->fullname,
                        $CFG->wwwroot . '/login/index.php'
                );
                email_to_user($user, $supportuser, $subject, $body);

            } else {
                $data->id = $data->classid;
                $data->confermato = ($data->confermato == 2) ? 0 : $data->confermato;
                $newclass = $DB->update_record('coripodatacollection_classes', $data);

                $class_teacher = $DB->get_record('coripodatacollection_classadmin', ['classid' => $data->classid]);
                if ($class_teacher->userid != $data->insegnante) {
                    $class_teacher->userid = $data->insegnante;
                    $DB->update_record('coripodatacollection_classadmin', $class_teacher);

                    $plesso = $DB->get_record('coripodatacollection_plessi', ['id' => $data->plesso]);
                    $user = $DB->get_record('user', ['id' => $class_teacher->userid]);
                    $supportuser = core_user::get_support_user();
                    $subject = get_string('add_edition_teacher_object', 'mod_coripodatacollection');
                    $body = sprintf(
                            get_string('add_edition_teacher_body', 'mod_coripodatacollection'),
                            $user->lastname . ' ' . $user->firstname,
                            $institute->denominazioneistituto,
                            $data->anno . '-' . $data->classe,
                            $plesso->denominazioneplesso,
                            $course->fullname,
                            $SITE->fullname,
                            $CFG->wwwroot . '/login/index.php'
                    );
                    email_to_user($user, $supportuser, $subject, $body);
                }

            }

            if (!is_enrolled($modulecontext, $data->insegnante)) {
                $studentrole = $DB->get_record('role', ['shortname' => 'student']);
                enrol_try_internal_enrol($course->id, $data->insegnante, $studentrole->id);
            }

            redirect($returnlink);

        }
    }
}


//Form import classi
if ($page == 'importclasses') {

    $instituteid = optional_param('instituteid', -1, PARAM_INT);
    if ($instituteid !== -1) {

        $institute = $DB->get_record('coripodatacollection_istituti', ['id' => $instituteid]);

        $errogation_corrente = $DB->get_record('coripodatacollection_erogations', ['courseid' => $course->id]);
        $erogation_precedenti = $DB->get_records_sql('SELECT * FROM {coripodatacollection_erogations} 
            WHERE projectid=' . $errogation_corrente->projectid . ' AND startingyear<' . $errogation_corrente->startingyear
                . ' ORDER BY startingyear DESC');
        $erogation_precedente = reset($erogation_precedenti);

        $importclassform = new mod_coripodatacollection\forms\importclasses_form($PAGE->url,
                ['courseid' => $erogation_precedente->courseid, 'instituteid' => $institute->id]);
        $returnlink = new moodle_url('/mod/coripodatacollection/viewdirector.php',
                ['id' => $id, 'page' => 'classes']);

        if ($importclassform->is_cancelled()) {
            redirect($returnlink);
        } else if ($data = $importclassform->get_data()) {

            foreach ($data as $key => $importingclass) {

                if ($importingclass->inserita) {
                    continue;
                }

                if (array_key_exists($importingclass->union, $data) and $data[$importingclass->union]->inserita) {
                    $newclass = $DB->get_record('coripodatacollection_classes', [
                            'id' => $DB->get_record('coripodatacollection_classhistory',
                                    ['parent_classid' => $importingclass->union])->classid
                    ]);
                } else if (array_key_exists($importingclass->union, $data) or $importingclass->union == 0) {

                    if ($importingclass->union == 0) {
                        $classjoinwith = $importingclass;
                    } else {
                        $classjoinwith = $data[$importingclass->union];
                    }
                    $oldclass = $DB->get_record('coripodatacollection_classes', ['id' => $classjoinwith->id]);

                    $newclass = new stdClass();
                    $newclass->erogazione = $DB->get_record('coripodatacollection_erogations', ['courseid' => $course->id])->id;
                    $newclass->istituto = $oldclass->istituto;
                    $newclass->plesso = $oldclass->plesso;
                    if ($oldclass->pluriclasse == 1) {
                        $newclass->anno = $oldclass->anno;
                    } else {
                        $newclass->anno = $oldclass->anno + 1;
                    }
                    $newclass->classe = $oldclass->classe;
                    $newclass->pluriclasse = $oldclass->pluriclasse;
                    $newclass->confermato = -1;
                    $newclass->can_edit_censimento = 0;
                    $newclass->can_edit_val_pre = 0;
                    $newclass->can_edit_val_post = 0;
                    $newclass->statistichepre = 0;
                    $newclass->risultatipre = 0;
                    $newclass->completati_res_pre = 0;
                    $data->valutazione_classe_pre = 0;
                    $data->valutazione_globale_pre = 0;
                    $newclass->statistichepost = 0;
                    $newclass->risultatipost = 0;
                    $newclass->completati_res_post = 0;
                    $data->valutazione_classe_post = 0;
                    $data->valutazione_globale_post = 0;
                    $newclass->id = $DB->insert_record('coripodatacollection_classes', $newclass);

                    $class_teacher = new stdClass();
                    $class_teacher->classid = $newclass->id;
                    $class_teacher->userid = $classjoinwith->teacher;
                    $DB->insert_record('coripodatacollection_classadmin', $class_teacher);

                    if (!is_enrolled($modulecontext, $classjoinwith->teacher)) {
                        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
                        enrol_try_internal_enrol($course->id, $classjoinwith->teacher, $studentrole->id);
                    }
                    $DB->insert_record('coripodatacollection_classhistory',
                            ['classid' => $newclass->id, 'parent_classid' => $classjoinwith->id, 'parent_deleted' => 0]);
                    $data[$classjoinwith->id]->inserita = true;
                } else {
                    $newclass = $DB->get_record('coripodatacollection_classes', [
                            'id' => $DB->get_record('coripodatacollection_classhistory',
                                    ['parent_classid' => $importingclass->union])->classid
                    ]);
                }

                if ($importingclass->union != 0) {
                    $DB->insert_record('coripodatacollection_classhistory',
                            ['classid' => $newclass->id, 'parent_classid' => $importingclass->id, 'parent_deleted' => 1]);
                }
                $class_parents = $DB->get_records('coripodatacollection_classhistory', ['classid' => $newclass->id]);
                $total_student = 0;
                foreach ($class_parents as $parent) {
                    $total_student += $DB->count_records('coripodatacollection_class_students',
                            ['classid' => $parent->parent_classid]);
                }
                $newclass->numerostudenti = $total_student;
                $DB->update_record('coripodatacollection_classes', $newclass);

            }
            redirect($returnlink);
        }
    } else {
        if (count($institutes) == 1) {
            $institute = reset($institutes);
            redirect(new moodle_url('/mod/coripodatacollection/viewdirector.php',
                    ['id' => $id, 'page' => 'importclasses', 'instituteid' => $institute->id]));
        }
    }
}



echo $OUTPUT->header();

if ($page != 'classes' && $page != 'newclass' && $page != 'importclasses') {
    $class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);
    $plesso = $DB->get_record('coripodatacollection_plessi', ['id' => $class->plesso]);
    $opzionianni = [
            -1 => get_string('pluri_class', 'mod_coripodatacollection'),
            0 => get_string('year', 'mod_coripodatacollection') . ' '
                    . get_string('first', 'mod_coripodatacollection'),
            1 => get_string('year', 'mod_coripodatacollection') . ' '
                    . get_string('second', 'mod_coripodatacollection'),
            2 => get_string('year', 'mod_coripodatacollection') . ' '
                    . get_string('third', 'mod_coripodatacollection'),
            3 => get_string('year', 'mod_coripodatacollection') . ' '
                    . get_string('fourth', 'mod_coripodatacollection'),
            4 => get_string('year', 'mod_coripodatacollection') . ' '
                    . get_string('fifth', 'mod_coripodatacollection')
    ];
    echo html_writer::start_div('page-header-headings');
    echo html_writer::tag('h2', $plesso->denominazioneplesso . ' - ' .
            $opzionianni[$class->anno - 1] . ' - ' . $class->classe);
    echo html_writer::end_div();
}

if ($page == 'newclass') {

    $instituteid = optional_param('instituteid', -1, PARAM_INT);

    if ($instituteid == -1) {

        echo html_writer::tag('h3', get_string('select_istitute', 'mod_coripodatacollection'));
        echo html_writer::start_tag('ul', ['class' => 'section m-0 p-0 img-text  d-block ', 'data-for' => 'cmlist']);

        foreach ($institutes as $institute) {
            echo html_writer::start_tag('li', ['class' => 'activity activity-wrapper ']);
            echo html_writer::start_tag('div', ['class' => 'activity-item focus-control ']);

            echo html_writer::start_tag('div', ['class' => 'text-center']);
            echo html_writer::link(
                    new moodle_url('/mod/coripodatacollection/viewdirector.php',
                            ['id' => $id, 'page' => 'newclass', 'instituteid' => $institute->id]),
                    format_text($institute->denominazioneistituto, FORMAT_PLAIN),
            );
            echo html_writer::end_tag('div');

            echo html_writer::end_tag('div');
            echo html_writer::end_tag('li');
        }

        echo html_writer::end_tag('ul');

    } else {
        $newclass->display();
    }

} elseif ($delclass != -1){

    $formcontinue = new single_button(new moodle_url('/mod/coripodatacollection/viewdirector.php',
            ['id' => $id, 'delete' => $delclass, 'confirm' => 1]), get_string('yes'));
    $formcancel = new single_button(new moodle_url('/mod/coripodatacollection/viewdirector.php',
            ['id' => $id, 'page' => 'classes', 'classid' => $delclass]), get_string('no'), 'get');
    echo $OUTPUT->confirm(get_string('confirmdeleteclass', 'mod_coripodatacollection',), $formcontinue, $formcancel);
    echo $OUTPUT->footer();
    die();

} elseif ($page == 'alunni') {

    // Numero studenti necessita conferma da parte dell'insegnante

    if ($class->confermato == 2 || $class->confermato == 0 || $class->confermato == -1) {
            $paramerror = [
                    'title-error' => get_string('wait_teacher_confirm_title', 'mod_coripodatacollection'),
                    'message-error' => get_string('wait_teacher_confirm_message', 'mod_coripodatacollection')
            ];
            echo $OUTPUT->render_from_template('coripodatacollection/errorinfo', $paramerror);
            echo $OUTPUT->footer();
            die();
        }


        $sql = 'SELECT * 
                FROM {coripodatacollection_alunni} JOIN {coripodatacollection_class_students} 
                ON {coripodatacollection_class_students}.studentid = {coripodatacollection_alunni}.id
                WHERE classid = ' . $classid . ' ORDER BY numeroregistro';
        $results = $DB->get_records_sql($sql);

        $table = new html_table();
        if ($class->pluriclasse == 1) {
            $table->align =
                    ['center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center'];
            $table->head = [
                    '',
                    get_string('surname', 'mod_coripodatacollection'),
                    get_string('name', 'mod_coripodatacollection'),
                    get_string('freq_year', 'mod_coripodatacollection'),
                    get_string('consensus', 'mod_coripodatacollection'),
                    get_string('born_in_italy', 'mod_coripodatacollection'),
                    get_string('language_difficulty', 'mod_coripodatacollection'),
                    get_string('home_language', 'mod_coripodatacollection'),
                    get_string('nursery_school_freq', 'mod_coripodatacollection'),
                    get_string('nursery_school_difficulty', 'mod_coripodatacollection'),
                    get_string('difficulty_noted', 'mod_coripodatacollection'),
                    get_string('centoquattro_law_table', 'mod_coripodatacollection'),
                    get_string('centoquattro_problem_table', 'mod_coripodatacollection'),
            ];
        } else {
            $table->align =
                    [ 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center'];
            $table->head = [
                    '',
                    get_string('surname', 'mod_coripodatacollection'),
                    get_string('name', 'mod_coripodatacollection'),
                    get_string('consensus', 'mod_coripodatacollection'),
                    get_string('born_in_italy', 'mod_coripodatacollection'),
                    get_string('language_difficulty', 'mod_coripodatacollection'),
                    get_string('home_language', 'mod_coripodatacollection'),
                    get_string('nursery_school_freq', 'mod_coripodatacollection'),
                    get_string('nursery_school_difficulty', 'mod_coripodatacollection'),
                    get_string('difficulty_noted', 'mod_coripodatacollection'),
                    get_string('centoquattro_law_table', 'mod_coripodatacollection'),
                    get_string('centoquattro_problem_table', 'mod_coripodatacollection'),
            ];
        }

        if (!empty($results)) {
            $select_year = [
                    0 => get_string('first_1', 'mod_coripodatacollection'),
                    1 => get_string('second_2', 'mod_coripodatacollection'),
                    2 => get_string('third_3', 'mod_coripodatacollection'),
                    3 => get_string('fourth_4', 'mod_coripodatacollection'),
                    4 => get_string('fifth_5', 'mod_coripodatacollection')
            ];
            foreach ($results as $r) {

                if($r->carta_identita == 0) {
                    $cie = $OUTPUT->pix_icon('i/grade_incorrect',
                            get_string('carta_identita_given', 'mod_coripodatacollection'));
                } else {
                    $cie = $OUTPUT->pix_icon('i/valid',
                            get_string('carta_identita_not_given', 'mod_coripodatacollection'));
                }
                if($r->consenso == 0) {
                    $consenso = $OUTPUT->pix_icon('i/grade_incorrect',
                            get_string('consensus_not_given', 'mod_coripodatacollection'));
                } else {
                    $consenso = $OUTPUT->pix_icon('i/valid',
                            get_string('consensus_given', 'mod_coripodatacollection'));
                }

                if ($class->pluriclasse) {
                    $table->data[] = new html_table_row([
                            $r->numeroregistro,
                            $r->cognome,
                            $r->nome,
                            $select_year[$r->annofrequentazione],
                            $cie . $consenso,
                            returnNoInfo($r->natoinitalia),
                            returnNoInfo($r->difficoltalinguaggio),
                            returnNoInfo($r->linguaparlatacasa),
                            returnNoInfo($r->frequenzascuolainfanzia),
                            returnNoInfo($r->difficoltascuolainfanzia),
                            returnNoInfo($r->notadifficolta),
                            returnNoInfo($r->leggecentoquattro),
                            is_null($r->problematicacentoquattro) ? '' : $r->problematicacentoquattro,
                    ]);
                } else {
                    $table->data[] = new html_table_row([
                            $r->numeroregistro,
                            $r->cognome,
                            $r->nome,
                            $cie . $consenso,
                            returnNoInfo($r->natoinitalia),
                            returnNoInfo($r->difficoltalinguaggio),
                            returnNoInfo($r->linguaparlatacasa),
                            returnNoInfo($r->frequenzascuolainfanzia),
                            returnNoInfo($r->difficoltascuolainfanzia),
                            returnNoInfo($r->notadifficolta),
                            returnNoInfo($r->leggecentoquattro),
                            is_null($r->problematicacentoquattro) ? '' : $r->problematicacentoquattro,
                    ]);
                }
            }
            echo html_writer::table($table);
        }

} elseif ($page == 'primevalutazioni') {

    if (results_missing($class->id, 'prerinforzo')) {
        $paramerror = [
                'title-error' => get_string('missing_results_title', 'mod_coripodatacollection'),
                'message-error' => get_string('missing_results_message', 'mod_coripodatacollection'),
        ];
        echo $OUTPUT->render_from_template('coripodatacollection/errorinfo', $paramerror);
    }

    $evaluationtable = new \mod_coripodatacollection\forms\newprova_form(null, ['idclasse' => $classid, 'onlyview' => true, 'table' => 'pre']);
    $evaluationtable->display();

} elseif ($page == 'ultimevalutazioni') {

    if (results_missing($class->id, 'postrinforzo')) {
        $paramerror = [
                'title-error' => get_string('missing_results_title', 'mod_coripodatacollection'),
                'message-error' => get_string('missing_results_message', 'mod_coripodatacollection'),
        ];
        echo $OUTPUT->render_from_template('coripodatacollection/errorinfo', $paramerror);
    }

    $evaluationtable = new \mod_coripodatacollection\forms\newprova_form(null, ['idclasse' => $classid, 'onlyview' => true, 'table' => 'post']);
    $evaluationtable->display();

} elseif ($page == 'importclasses') {

    $instituteid = optional_param('instituteid', -1, PARAM_INT);
    if ($instituteid == -1) {
        if (count($institutes) == 1) {
            $institute = reset($institutes);
            redirect(new moodle_url('/mod/coripodatacollection/viewdirector.php',
                    ['id' => $id, 'page' => 'importclasses', 'instituteid' => $institute->id]));
        } else {

            echo html_writer::tag('h3', get_string('select_istitute_import', 'mod_coripodatacollection'));
            echo html_writer::start_tag('ul', ['class' => 'section m-0 p-0 img-text  d-block ', 'data-for' => 'cmlist']);

            foreach ($institutes as $institute) {
                echo html_writer::start_tag('li', ['class' => 'activity activity-wrapper ']);
                echo html_writer::start_tag('div', ['class' => 'activity-item focus-control ']);

                echo html_writer::start_tag('div', ['class' => 'text-center']);
                echo html_writer::link(
                        new moodle_url('/mod/coripodatacollection/viewdirector.php',
                                ['id' => $id, 'page' => 'importclasses', 'instituteid' => $institute->id]),
                        format_text($institute->denominazioneistituto, FORMAT_PLAIN),
                );
                echo html_writer::end_tag('div');

                echo html_writer::end_tag('div');
                echo html_writer::end_tag('li');
            }

            echo html_writer::end_tag('ul');

        }
    } else {
        echo html_writer::tag('h2', get_string('import_class_title', 'mod_coripodatacollection')
                . $erogation_precedente->academicyearedition, ['class' => 'h2']);
        $importclassform->display();
    }

} else {

    echo html_writer::tag('h2', get_string('registered_classes', 'mod_coripodatacollection'),
            ['class' => 'h2', 'style' => 'margin-bottom: 50px;']);

    echo $OUTPUT->box_start();

    foreach ($institutes as &$institute) {

        $institute->error = false;
        $redirectpage = new moodle_url('/mod/coripodatacollection/instituteadmin.php',
                ['page' => 'institute', 'istituteid' => $institute->id, 'projectid' => -1]);
        if (empty($DB->get_records('coripodatacollection_plessi', ['instituteid' => $institute->id]))) {
            $template = [
                    'title-error' => get_string('error_missingplessotitle', 'mod_coripodatacollection') . $institute->denominazioneistituto,
                    'message-error' => get_string('error_missingplessotext', 'mod_coripodatacollection'),
                    'redirect-link' => $redirectpage,
                    'redirect-link-text' => get_string('error_buttontoinstituteadmin', 'mod_coripodatacollection')];
            echo $OUTPUT->render_from_template('coripodatacollection/errorinfo', $template);
            $institute->error = true;
        }
        $redirectpage = new moodle_url('/mod/coripodatacollection/instituteadmin.php',
                ['page' => 'institute', 'insegnanti' => $institute->id, 'projectid' => -1]);
        if (empty($DB->get_records('coripodatacollection_teachers', ['instituteid' => $institute->id]))) {
            $template = [
                    'title-error' => get_string('error_missingteachertitle', 'mod_coripodatacollection') . $institute->denominazioneistituto,
                    'message-error' => get_string('error_missingteachertext', 'mod_coripodatacollection'),
                    'redirect-link' => $redirectpage,
                    'redirect-link-text' => get_string('error_buttontoinstituteadmin', 'mod_coripodatacollection')];
            echo $OUTPUT->render_from_template('coripodatacollection/errorinfo', $template);
            $institute->error = true;
        }
    }

    $teacher_istitute = $DB->get_records('coripodatacollection_teachers',
            ['userid' => $USER->id], '', 'instituteid,userid');
    foreach ($teacher_istitute as $key => $val) {
        if ($key == null) {
            unset($teacher_istitute[$key]);
        }

        $controllo_classi = $DB->get_records_sql('SELECT DISTINCT mdl_coripodatacollection_classes.* 
                                                      FROM mdl_coripodatacollection_classes 
                                                        JOIN mdl_coripodatacollection_classadmin ON classid=mdl_coripodatacollection_classes.id
                                                      WHERE userid=' . $USER->id . ' 
                                                            AND istituto=' . $val->instituteid . ' 
                                                            AND erogazione=' . $erogation->id);

        if (empty($controllo_classi)) {
            unset($teacher_istitute[$key]);
        }

    }
    foreach ($institutes as &$institute) {
        if (!$institute->error) {
            $allclasses = new \mod_coripodatacollection\forms\viewallclasses_form(null,
                    ['instanceid' => $id, 'courseid' => $course->id, 'viewmode' => 'institute', 'instituteid' => $institute->id]);
            $allclasses->display();
            echo '<br><br>';
        }

        if (array_key_exists($institute->id, $teacher_istitute)) {
            unset($teacher_istitute[$institute->id]);
        }

    }

    if (count($teacher_istitute) > 0) {
        echo html_writer::tag('h4', get_string('registered_classes_teaching', 'mod_coripodatacollection'),
                ['class' => 'h4', 'style' => 'margin-top: 100px; margin-bottom: 50px;']);
    }

    foreach ($teacher_istitute as $t) {
        if (!empty($t->instituteid)) {
            $teach_ins = $DB->get_record('coripodatacollection_istituti', ['id' => $t->instituteid]);
            $allclasses = new \mod_coripodatacollection\forms\viewallclasses_form(null,
                    ['instanceid' => $id, 'courseid' => $course->id, 'viewmode' => 'teacher',
                            'instituteid' => $teach_ins->id, 'teacherid' => $USER->id]);
            $allclasses->display();
        }
    }

    echo $OUTPUT->box_end();

    $erogation = $DB->get_record('coripodatacollection_erogations',['courseid' => $course->id]);

    date_default_timezone_set('Europe/Rome');
    $current_date = time();
    if ( $erogation->start_censimento <= $current_date && $current_date <= $erogation->end_censimento) {

        $stickyfooterelements = \html_writer::start_div('', ['style' => 'display: flex; justify-content: space-between;']);
        $stickyfooterelements .= \html_writer::start_div('', ['style' => 'display: block; text-align: center;']);
        $stickyfooterelements .= html_writer::tag(
                'h5',
                get_string('census_period', 'mod_coripodatacollection'),
        );
        $stickyfooterelements .= html_writer::tag('strong',
                $data = date("d/m/Y", $erogation->start_censimento) . '--' . $data = date("d/m/Y", $erogation->end_censimento),
                ['style' => 'color: lightgray;']);
        $stickyfooterelements .= \html_writer::end_div();
        $stickyfooterelements .= \html_writer::end_div();


        $stickyfooterelements .= \html_writer::start_div('', ['' =>'flex: 1; text-align: right;']);
        $linknewclassbutton = new moodle_url('/mod/coripodatacollection/viewdirector.php', ['id' => $id, 'page' => 'newclass']);
        $stickyfooterelements .= html_writer::tag('a',
                get_string('submit_newclass', 'mod_coripodatacollection'),
                ['href' => $linknewclassbutton, 'class' => 'btn btn-primary', 'style' => 'display: inline-block;']
        );

        // Controllo se sono presenti erogazioni precedenti, se vero crea bottone
        $errogation_corrente = $DB->get_record('coripodatacollection_erogations', ['courseid' => $course->id]);
        $erogation_precedenti = $DB->get_records_sql('SELECT * 
                                        FROM {coripodatacollection_erogations} 
                                        WHERE projectid=' . $errogation_corrente->projectid . ' 
                                        AND startingyear<' . $errogation_corrente->startingyear);
        if (!empty($erogation_precedenti)) {
            $linkimportclassbutton = new moodle_url('/mod/coripodatacollection/viewdirector.php',
                    ['id' => $id, 'page' => 'importclasses']);
            $stickyfooterelements .= html_writer::tag(
                    'a',
                    get_string('import_classes', 'mod_coripodatacollection'),
                    [
                            'href' => $linkimportclassbutton,
                            'class' => 'btn btn-primary',
                            'style' => 'display: inline-block; margin: 5px;'
                    ]
            );

        }
        $stickyfooterelements .= html_writer::tag('a',
                get_string('get_participation_certificate', 'mod_coripodatacollection'),
                [
                        'id' => 'get-participation-certificate',
                        'class' => 'btn btn-primary',
                        'style' => 'display: inline-block; margin-left: 5px; margin-right: 5px;',
                        'href' => '',
                        'data-user-name' => $USER->lastname . ' ' . $USER->firstname,
                        'data-istitute-teacher' => get_istitutes_erogation_teachers($erogation->id, $institutes)
                ]
        );

        $stickyfooterelements .= \html_writer::end_div();



    } elseif ($erogation->start_val_pre <= $current_date && $current_date <= $erogation->end_val_pre) {

        $stickyfooterelements = \html_writer::start_div('', ['style' => 'display: block; text-align: center;']);
        $stickyfooterelements .= html_writer::tag('h5',
                get_string('pre_reinforce_period', 'mod_coripodatacollection'));
        $stickyfooterelements .= html_writer::tag('strong',
                $data = date("d/m/Y", $erogation->start_val_pre) . '--' . $data = date("d/m/Y", $erogation->end_val_pre),
                ['style' => 'color: lightgray;']);
        $stickyfooterelements .= \html_writer::end_div();
        $stickyfooterelements .= \html_writer::start_div('', ['' =>'flex: 1; text-align: right;']);
        $stickyfooterelements .= html_writer::tag('a',
                get_string('get_participation_certificate', 'mod_coripodatacollection'),
                [
                        'id' => 'get-participation-certificate',
                        'class' => 'btn btn-primary',
                        'style' => 'display: inline-block; margin-left: 5px; margin-right: 5px;',
                        'href' => '',
                        'data-user-name' => $USER->lastname . ' ' . $USER->firstname,
                        'data-istitute-teacher' => get_istitutes_erogation_teachers($erogation->id, $institutes)
                ]
        );
        $stickyfooterelements .= \html_writer::end_div();
    } elseif ($erogation->end_val_pre <= $current_date && $current_date <= $erogation->start_val_post) {
        foreach ($institutes as $ins) {
            if ($DB->record_exists('coripodatacollection_principals', ['instituteid' => $ins->id, 'userid' => $USER->id])) {
                if ($erogation->evaluation_completed_pre == 1) {
                    if (!isset($stickyfooterelements))
                        $stickyfooterelements = '';
                    $stickyfooterelements .= \html_writer::start_div('', ['' =>'flex: 1; text-align: right;']);
                    $linknewclassbutton = new moodle_url('/mod/coripodatacollection/viewprincipal.php', ['id' => $id]);
                    $stickyfooterelements .= html_writer::tag('a',
                            get_string('show_reported', 'mod_coripodatacollection'),
                            ['href' => $linknewclassbutton, 'class' => 'btn btn-primary', 'style' => 'display: inline-block;']
                    );
                    $stickyfooterelements .= \html_writer::end_div();
                }
            }
        }
        if (isset($stickyfooterelements)) {
            $stickyfooterelements .= \html_writer::start_div('', ['' => 'flex: 1; text-align: right;']);
            $stickyfooterelements .= html_writer::tag('a',
                    get_string('get_participation_certificate', 'mod_coripodatacollection'),
                    [
                            'id' => 'get-participation-certificate',
                            'class' => 'btn btn-primary',
                            'style' => 'display: inline-block; margin-left: 5px; margin-right: 5px;',
                            'href' => '',
                            'data-user-name' => $USER->lastname . ' ' . $USER->firstname,
                            'data-istitute-teacher' => get_istitutes_erogation_teachers($erogation->id, $institutes)
                    ]
            );
            $stickyfooterelements .= \html_writer::end_div();
        }

    } elseif ($erogation->start_val_post <= $current_date && $current_date <= $erogation->end_val_post) {

        $stickyfooterelements = \html_writer::start_div('', ['style' => 'display: block; text-align: center;']);
        $stickyfooterelements .= html_writer::tag('h5',
                get_string('post_reinforce_period', 'mod_coripodatacollection'));
        $stickyfooterelements .= html_writer::tag('strong',
                $data = date("d/m/Y", $erogation->start_val_post) . '--' . $data = date("d/m/Y", $erogation->end_val_post),
                ['style' => 'color: lightgray;']);
        $stickyfooterelements .= \html_writer::end_div();
        $stickyfooterelements .= \html_writer::start_div('', ['' =>'flex: 1; text-align: right;']);
        $stickyfooterelements .= html_writer::tag('a',
                get_string('get_participation_certificate', 'mod_coripodatacollection'),
                [
                        'id' => 'get-participation-certificate',
                        'class' => 'btn btn-primary',
                        'style' => 'display: inline-block; margin-left: 5px; margin-right: 5px;',
                        'href' => '',
                        'data-user-name' => $USER->lastname . ' ' . $USER->firstname,
                        'data-istitute-teacher' => get_istitutes_erogation_teachers($erogation->id, $institutes)
                ]
        );
        $stickyfooterelements .= \html_writer::end_div();
    }



    if (empty($stickyfooterelements) || $stickyfooterelements == '') {
        $stickyfooterelements = \html_writer::start_div('', ['style' => 'display: block;']);
        $stickyfooterelements .= html_writer::tag('a',
                get_string('get_participation_certificate', 'mod_coripodatacollection'),
                [
                        'id' => 'get-participation-certificate',
                        'class' => 'btn btn-primary',
                        'style' => 'display: inline-block; margin-right: 5px;',
                        'href' => '',
                        'data-user-name' => $USER->lastname . ' ' . $USER->firstname,
                        'data-istitute-teacher' => get_istitutes_erogation_teachers($erogation->id, $institutes)
                ]
        );
        $stickyfooterelements .= \html_writer::end_div();
        $stickyfooter = new \core\output\sticky_footer($stickyfooterelements);
    } else {
        $stickyfooter = new \core\output\sticky_footer($stickyfooterelements, ' ',
                ['style' => 'display: flex; justify-content: space-between;']);
    }
    echo $OUTPUT->render($stickyfooter);

    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>';
    echo '<script type="module" src="javascript/download_consensus.js"></script>';
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>';
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.9.1/jszip.min.js"></script>';
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>';
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>';
    echo '<script type="module" src="javascript/download_partecipation_certificate_zip.js"></script>';

}

if ($page == 'importclasses') {
    echo '<script src="javascript/import_classes.js"></script>';
} elseif ($page == 'primevalutazioni' or $page == 'ultimevalutazioni') {
    echo '<script src="javascript/fixed_table_col.js"></script>';
}
echo $OUTPUT->footer();
