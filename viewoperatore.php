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
require_capability('mod/coripodatacollection:operator', $context);
require_capability('mod/data:viewentry', $context);

$modulecontext = context_module::instance($cm->id);

$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$page = optional_param('page', 'confirm_students', PARAM_TEXT);

if ($page == 'assign_student') {
    $PAGE->add_body_class('wide');
} else {
    $PAGE->add_body_class('mediumwidth');
}

$PAGE->set_url('/mod/coripodatacollection/viewoperatore.php', ['id' => $cm->id, 'page' => $page]);
$node = $PAGE->secondarynav->add(get_string('confirm_students', 'mod_coripodatacollection'));
$node->isactive = $page == 'confirm_students';
$node->action = new moodle_url('/mod/coripodatacollection/viewoperatore.php', ['id' => $cm->id, 'page' => 'confirm_students']);

$node = $PAGE->secondarynav->add(get_string('assign_student', 'mod_coripodatacollection'));
$node->isactive = $page == 'assign_student';
$node->action = new moodle_url('/mod/coripodatacollection/viewoperatore.php', ['id' => $cm->id, 'page' => 'assign_student']);

$erogation = $DB->get_record('coripodatacollection_erogations', ['courseid' => $course->id]);


if ($page == 'confirm_students') {

    $mode = optional_param('mode', '', PARAM_TEXT);
    if ($mode == 'remove') {
        $class_student_id = required_param('class_student_id', PARAM_INT);
        $class_student = $DB->get_record('coripodatacollection_class_students',
                ['id' => $class_student_id]);
        $class_student->consenso_recupero = 0;
        $DB->update_record('coripodatacollection_class_students', $class_student);
        $redirect = new moodle_url($PAGE->url, ['id' => $id, 'page' => 'confirm_students']);
        redirect($redirect);
    } elseif ($mode == 'add') {
        $class_student_id = required_param('class_student_id', PARAM_INT);
        $class_student = $DB->get_record('coripodatacollection_class_students',
                ['id' => $class_student_id]);
        $class_student->consenso_recupero = 1;
        $DB->update_record('coripodatacollection_class_students', $class_student);
        $redirect = new moodle_url($PAGE->url, ['id' => $id, 'page' => 'confirm_students']);
        redirect($redirect);
    }


    $url = new moodle_url($PAGE->url, ['id' => $id, 'page' => 'confirm_students']);
    $upload_file_form = new \mod_coripodatacollection\forms\uploadcsvupdate_form($url);
    if ($data = $upload_file_form->get_data()) {

        if (isset($data->get_csv_report)) {
            if (isset($SESSION->csv_array))
                send_csv($SESSION->csv_array, "info_update.csv");
        }

        $file = $upload_file_form->get_file_content('fileinput');
        $delimiter = substr_count($file, ',') > substr_count($file, ';') ? ',' : ';';

        if ($file) {
            $csv = explode("\n", $file);
            $new_csv_lines = [];

            foreach ($csv as $line) {
                $fields = str_getcsv($line, $delimiter);

                if ($csv[0] == $line || $line == '') {
                    $new_csv_lines[] = $fields;
                    continue;
                }

                if (!empty($fields)) {
                    $hascode = $fields[10];
                    $email_genitore = $fields[9];
                    $cel_genitore = $fields[8];
                    $student = $DB->get_record_sql('SELECT * FROM mdl_coripodatacollection_alunni WHERE hash_code = "' . $hascode .
                            '"');
                    if (empty($student)) {
                        $fields[] = "Studente non trovato";
                        $new_csv_lines[] = $fields;
                        continue;
                    }
                    $class_student = $DB->get_record_sql('
                        SELECT DISTINCT class_students.* 
                        FROM mdl_coripodatacollection_class_students as class_students
                        JOIN mdl_coripodatacollection_classes as class on class.id = class_students.classid
                        WHERE class.erogazione = ' . $erogation->id . ' and class_students.studentid = ' . $student->id . '
                    ');
                    if ($class_student->consenso_recupero == 1 || !is_null($student->email_genitore) || !is_null($student->telefono_genitore))
                        $fields[] = "Studente aggiornato";
                    else
                        $fields[] = "Studente inserito";

                    $class_student->consenso_recupero = 1;
                    $DB->update_record('coripodatacollection_class_students', $class_student);

                    $student->email_genitore = $email_genitore;
                    $student->telefono_genitore = $cel_genitore;
                    $DB->update_record('coripodatacollection_alunni', $student);

                    $new_csv_lines[] = $fields;
                }
            }

            $SESSION->csv_array = $new_csv_lines;

            $redirect = new moodle_url($PAGE->url, ['id' => $id, 'page' => 'confirm_students']);
            redirect($redirect);

        }
    }
}

if ($page == 'assign_student') {

    $mode = optional_param('mode', '', PARAM_TEXT);
    if ($mode == 'close_group') {

        $groupid = required_param('groupid', PARAM_INT);
        $DB->update_record('coripodatacollection_gruppi', ['id' => $groupid, 'chiuso' => 1]);
        $redirect_url = new moodle_url('/mod/coripodatacollection/viewoperatore.php',
                ['id' => $cm->id, 'page' => 'assign_student', 'mode' => 'view']);
        redirect($redirect_url);

    } elseif ( $mode == 'add_note'){

        require_once("$CFG->libdir/formslib.php");

        $groupid = required_param('groupid', PARAM_INT);
        $group = $DB->get_record('coripodatacollection_gruppi', ['id' => $groupid]);
        $actualpage = new moodle_url('/mod/coripodatacollection/viewoperatore.php',
                ['id' => $id, 'page' => 'assign_student' ,'mode' => 'add_note', 'groupid' => $groupid]);
        $note_form = new MoodleQuickForm('', 'POST', $actualpage);
        $note_form->addElement('textarea', 'nota', get_string('group_note', 'mod_coripodatacollection'),
                'wrap="virtual" rows="10" style="width: 100%"');
        $note_form->setType('nota', PARAM_TEXT);
        $note_form->addElement('submit', 'submitbutton_noteform', get_string('save_note', 'mod_coripodatacollection'));
        $note_form->setDefault('nota', $group->nota);
        if (isset($_POST['submitbutton_noteform'])) {
            $nota = $_POST['nota'] ?? '';
            $DB->update_record('coripodatacollection_gruppi', ['id' => $groupid, 'nota' => $nota]);
            $group = $DB->get_record('coripodatacollection_gruppi', ['id' => $groupid]);
            $redirect_url = new moodle_url('/mod/coripodatacollection/viewoperatore.php',
                    ['id' => $cm->id, 'page' => 'assign_student', 'mode' => 'view']);
            redirect($redirect_url);
        }

    } elseif ($mode == 'download_csv') {
        $groupid = required_param('groupid', PARAM_INT);
        $gruppo = $DB->get_record('coripodatacollection_gruppi', ['id' => $groupid]);

        $alunni_gruppo = $DB->get_records('coripodatacollection_alunni_gruppo',
                ['groupid' => $groupid]);
        $csv_array = [];
        $csv_array[] = [
                get_string('code', 'mod_coripodatacollection'),
                get_string('email', 'mod_coripodatacollection'),
                get_string('email_insegante', 'mod_coripodatacollection'),
                get_string('telephone', 'mod_coripodatacollection'),
                get_string('email_object', 'mod_coripodatacollection'),
                get_string('message', 'mod_coripodatacollection'),
        ];
        foreach ($alunni_gruppo as $alunno_gruppo) {

            $alunno = $DB->get_record('coripodatacollection_alunni',
                    ['id' => $alunno_gruppo->studentid]);
            $classe = $DB->get_record_sql('SELECT classes.* 
                                                   FROM mdl_coripodatacollection_classes as classes
                                                   JOIN mdl_coripodatacollection_class_students as class_students ON classes.id=class_students.classid
                                                   WHERE classes.erogazione=' . $erogation->id . ' 
                                                   AND class_students.studentid=' . $alunno_gruppo->studentid);
            $istituto = $DB->get_record('coripodatacollection_istituti', ['id' => $classe->istituto]);
            $plesso = $DB->get_record('coripodatacollection_plessi', ['id' => $classe->plesso]);
            $insegante = $DB->get_record('coripodatacollection_classadmin', ['classid' => $classe->id]);
            $user_insegnante = $DB->get_record('user', ['id' => $insegante->userid]);

            $orario1 = DateTime::createFromFormat('H:i', $gruppo->orario1);
            $orario1->modify('+1 hour');
            $orario2 = DateTime::createFromFormat('H:i', $gruppo->orario2);
            $orario2->modify('+1 hour');

            if ( $gruppo->orario1 ==  $gruppo->orario2) {
                $str_orario = $gruppo->orario1 . '-' . $orario1->format('H:i');
            } else {
                $str_orario = $gruppo->orario1 . '-' . $orario1->format('H:i') .
                        ' e ' .$gruppo->orario2 . '-' . $orario2->format('H:i');
            }

            $oggetto_email = sprintf(
                    get_string('oggetto_email_genitore', 'mod_coripodatacollection'),
                    $alunno->hash_code
            );

            $testo_messaggio = sprintf(
                    get_string('email_genitore', 'mod_coripodatacollection'),
                    $istituto->denominazioneistituto, $plesso->denominazioneplesso,
                    $classe->classe,
                    $alunno->hash_code,
                    $gruppo->codice,
                    $gruppo->sede, $gruppo->dettaglio_sede, $gruppo->indirizzo,
                    $gruppo->giorno1, $gruppo->giorno2,
                    $str_orario
            );

            $csv_array[] = [
                    $alunno->hash_code,
                    $alunno->email_genitore ?? 'none',
                    $user_insegnante->email,
                    $alunno->telefono_genitore ?? 'none',
                    $oggetto_email,
                    $testo_messaggio,
            ];
        }
        $redirect_url = new moodle_url('/mod/coripodatacollection/viewoperatore.php',
                ['id' => $cm->id, 'page' => 'assign_student', 'mode' => 'view']);
        send_csv($csv_array, "messagio_genitori.csv", ';');
        redirect($redirect_url);

    } elseif ($mode == 'download_all_csv') {

        $gruppi = $DB->get_records('coripodatacollection_gruppi', ['erogationid' => $erogation->id]);

        $csv_array = [];
        $csv_array[] = [
                get_string('code', 'mod_coripodatacollection'),
                get_string('email', 'mod_coripodatacollection'),
                get_string('email_insegante', 'mod_coripodatacollection'),
                get_string('telephone', 'mod_coripodatacollection'),
                get_string('email_object', 'mod_coripodatacollection'),
                get_string('message', 'mod_coripodatacollection'),
        ];

        foreach ($gruppi as $gruppo) {

            $groupid = $gruppo->id;

            $alunni_gruppo = $DB->get_records('coripodatacollection_alunni_gruppo',
                    ['groupid' => $groupid]);
            foreach ($alunni_gruppo as $alunno_gruppo) {

                $alunno = $DB->get_record('coripodatacollection_alunni',
                        ['id' => $alunno_gruppo->studentid]);
                $classe = $DB->get_record_sql('SELECT classes.* 
                                                   FROM mdl_coripodatacollection_classes as classes
                                                   JOIN mdl_coripodatacollection_class_students as class_students ON classes.id=class_students.classid
                                                   WHERE classes.erogazione=' . $erogation->id . ' 
                                                   AND class_students.studentid=' . $alunno_gruppo->studentid);
                $istituto = $DB->get_record('coripodatacollection_istituti', ['id' => $classe->istituto]);
                $plesso = $DB->get_record('coripodatacollection_plessi', ['id' => $classe->plesso]);
                $insegante = $DB->get_record('coripodatacollection_classadmin', ['classid' => $classe->id]);
                $user_insegnante = $DB->get_record('user', ['id' => $insegante->userid]);

                $orario1 = DateTime::createFromFormat('H:i', $gruppo->orario1);
                $orario1->modify('+1 hour');
                $orario2 = DateTime::createFromFormat('H:i', $gruppo->orario2);
                $orario2->modify('+1 hour');

                if ( $gruppo->orario1 == $gruppo->orario2) {
                    $str_orario = $gruppo->orario1 . '-' . $orario1->format('H:i');
                } else {
                    $str_orario = $gruppo->orario1 . '-' . $orario1->format('H:i') .
                            ' e ' .$gruppo->orario2 . '-' . $orario2->format('H:i');
                }

                $oggetto_email = sprintf(
                        get_string('oggetto_email_genitore', 'mod_coripodatacollection'),
                        $alunno->hash_code
                );

                $testo_messaggio = sprintf(
                        get_string('email_genitore', 'mod_coripodatacollection'),
                        $istituto->denominazioneistituto, $plesso->denominazioneplesso,
                        $classe->classe,
                        $alunno->hash_code,
                        $gruppo->codice,
                        $gruppo->sede, $gruppo->dettaglio_sede, $gruppo->indirizzo,
                        $gruppo->giorno1, $gruppo->giorno2,
                        $str_orario
                );

                $csv_array[] = [
                        $alunno->hash_code,
                        $alunno->email_genitore ?? 'none',
                        $user_insegnante->email,
                        $alunno->telefono_genitore ?? 'none',
                        $oggetto_email,
                        $testo_messaggio,
                ];
            }
        }

        $redirect_url = new moodle_url('/mod/coripodatacollection/viewoperatore.php',
                ['id' => $cm->id, 'page' => 'assign_student', 'mode' => 'view']);
        send_csv($csv_array, "messagio_genitori.csv", ';');
        redirect($redirect_url);

    } elseif ($mode == 'open_group') {
        $groupid = required_param('groupid', PARAM_INT);
        $DB->update_record('coripodatacollection_gruppi',
                ['id' => $groupid, 'completato' => 0, 'chiuso' => 0, 'definitivo' => 0]);
        $redirect_url = new moodle_url('/mod/coripodatacollection/viewoperatore.php',
                ['id' => $cm->id, 'page' => 'assign_student', 'mode' => 'view']);
        redirect($redirect_url);
    } elseif ($mode == 'definitive_group') {
        $groupid = required_param('groupid', PARAM_INT);
        $DB->update_record('coripodatacollection_gruppi',
                ['id' => $groupid, 'definitivo' => 1]);
        $redirect_url = new moodle_url('/mod/coripodatacollection/viewoperatore.php',
                ['id' => $cm->id, 'page' => 'assign_student', 'mode' => 'view']);
        redirect($redirect_url);
    }

}


echo $OUTPUT->header();

if ($page == 'confirm_students') {

    echo html_writer::tag('h2',
            get_string('confirm_students', 'mod_coripodatacollection'),
            ['class' => 'h2', 'style' => 'margin-bottom: 50px']);

    $actualpage = new moodle_url('/mod/coripodatacollection/viewoperatore.php',
            ['id' => $cm->id, 'page' => 'confirm_students']);
    $search_form = new MoodleQuickForm('', 'GET', $actualpage);
    $search_text = $search_form->createElement('text', 'code_search', '');
    $search_form->setType('code_search', PARAM_TEXT);
    $searh_button = $search_form->createElement('submit', 'submitbutton', 'Cerca');
    $search_form->addGroup([$search_text, $searh_button], 'group', get_string('search_by_code', 'mod_coripodatacollection'));
    $search_form->addHelpButton('group', 'search_by_code', 'mod_coripodatacollection');
    $search_form->display();

    $code_search = '';
    if (isset($_GET['group']))
        $code_search = $_GET['group']['code_search'];

    $sql = '
        SELECT DISTINCT alunni.*, indici.erogazione, indici.classe, indici.alunno, indici.periodo
        FROM mdl_coripodatacollection_alunni as alunni
        JOIN mdl_coripodatacollection_indici_valutazione as indici on indici.alunno = alunni.id
        WHERE indici.periodo = "prerinforzo" and indici.erogazione = ' . $erogation->id . ' and
            (indici.valutazione_classe = "Rosso" or indici.valutazione_classe = "Giallo") and
            (indici.valutazione_globale = "Verde Scuro" or indici.valutazione_globale = "Giallo" or indici.valutazione_globale = "Rosso")
    ';
    $students = $DB->get_records_sql($sql);

    $table = new html_table();
    $table->head =  [
        get_string('code', 'mod_coripodatacollection'),
        get_string('consensus_reinforce', 'mod_coripodatacollection'),
        get_string('class', 'mod_coripodatacollection'),
        get_string('searchplesso_name', 'mod_coripodatacollection'),
        get_string('istitutomenu', 'mod_coripodatacollection'),
        ''
    ];
    $table->align = ['center', 'center', 'center', 'center', 'center', 'center'];
    $table->attributes = ['style' => 'max-height: 500px'];

    foreach ($students as $student) {

        if ($code_search != '' && !str_starts_with($student->hash_code, $code_search))
            continue;

        $class_student = $DB->get_record('coripodatacollection_class_students',
                ['classid' => $student->classe, 'studentid' => $student->alunno]);
        $class = $DB->get_record('coripodatacollection_classes', ['id' => $student->classe]);
        $plesso = $DB->get_record('coripodatacollection_plessi', ['id' => $class->plesso]);
        $istituto = $DB->get_record('coripodatacollection_istituti', ['id' => $class->istituto]);


        if ($class_student->consenso_recupero == 1) {
            $button = html_writer::link(
                    new moodle_url(
                            $PAGE->url,
                            ['id' => $id, 'page' => 'confirm_students', 'mode' => 'remove', 'class_student_id' => $class_student->id]
                    ),
                    $OUTPUT->pix_icon('i/excluded', get_string('remove', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );
        } else {
            $button = html_writer::link(
                    new moodle_url(
                            $PAGE->url,
                            ['id' => $id, 'page' => 'confirm_students', 'mode' => 'add', 'class_student_id' => $class_student->id]
                    ),
                    $OUTPUT->pix_icon('i/addblock', get_string('add', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );
        }

        $table->data[] = [
            $student->hash_code,
            $class_student->consenso_recupero == 1 ? get_string('yes') : get_string('no'),
            $class->classe,
            $plesso->denominazioneplesso,
            $istituto->denominazioneistituto,
            $button
        ];


    }

    echo substr_replace(html_writer::table($table), ' style="max-height:300px"', 29, 0);

    $upload_file_form->display();

    $search_nonin_form = new MoodleQuickForm('', 'GET', $actualpage);
    $search_nonin_form->addElement('header', 'headerrrrr', get_string('search_nonin_student', 'mod_coripodatacollection'));


    $search_nonin_text = $search_nonin_form->createElement('text', 'code_search', '');
    $search_nonin_form->setType('code_search', PARAM_TEXT);
    $search_nonin_button = $search_nonin_form->createElement('submit', 'submitbutton', 'Cerca');
    $search_nonin_form->addGroup([$search_nonin_text, $search_nonin_button], 'group_nonin', get_string('search_by_code', 'mod_coripodatacollection'));
    $search_nonin_form->addHelpButton('group_nonin', 'search_by_code', 'mod_coripodatacollection');

    $code_nonin_search = '';
    if (isset($_GET['group_nonin']['code_search'])) {
        $code_nonin_search = $_GET['group_nonin']['code_search'];
    }

    if ($code_nonin_search != ''){

        $sql = '
        SELECT DISTINCT alunni.*, classes.id as classe
        FROM mdl_coripodatacollection_alunni as alunni
        JOIN mdl_coripodatacollection_class_students as class_students ON class_students.studentid = alunni.id
        JOIN mdl_coripodatacollection_classes as classes ON classes.id = class_students.classid
        WHERE classes.erogazione = ' . $erogation->id . ' AND alunni.hash_code = "' . $code_nonin_search . '"';
        $student = $DB->get_record_sql($sql);

        if (!empty($student)) {

            $class_student = $DB->get_record('coripodatacollection_class_students',
                    ['classid' => $student->classe, 'studentid' => $student->id]);
            $class = $DB->get_record('coripodatacollection_classes', ['id' => $student->classe]);
            $plesso = $DB->get_record('coripodatacollection_plessi', ['id' => $class->plesso]);
            $istituto = $DB->get_record('coripodatacollection_istituti', ['id' => $class->istituto]);

            $table = new html_table();
            $table->head = [
                    get_string('code', 'mod_coripodatacollection'),
                    get_string('surname', 'mod_coripodatacollection'),
                    get_string('name', 'mod_coripodatacollection'),
                    get_string('class', 'mod_coripodatacollection'),
                    get_string('searchplesso_name', 'mod_coripodatacollection'),
                    get_string('istitutomenu', 'mod_coripodatacollection'),
            ];
            $table->align = ['center', 'center', 'center', 'center', 'center', 'center'];
            $table->attributes = ['style' => 'max-height: 500px'];

            $table->data[] = [
                $student->hash_code,
                $student->cognome,
                $student->nome,
                $class->classe,
                $plesso->denominazioneplesso,
                $istituto->denominazioneistituto,
            ];

            $table_str = substr_replace(html_writer::table($table), ' style="max-height:300px"', 29, 0);
            $search_nonin_form->addElement('html', $table_str);
        } else {

            $no_student = 'pix/noentries_zero_state.svg';
            $html_string = html_writer::start_div('text-xs-center text-center mt-4',
                    ['style' => ' display: flex; align-items: center; 
                                justify-content: center; gap: 20px; margin: 20px auto;']);
            $html_string .= html_writer::img(
                    $no_student,
                    get_string('no_student_found', 'mod_coripodatacollection'),
                    ['style' => 'width: 100px; height: auto;']);
            $html_string .= html_writer::tag(
                    'h5',
                    get_string('no_student_found', 'mod_coripodatacollection'),
                    ['class' => 'h5 mt-3 mb-0']);
            $html_string .= html_writer::end_div();
            $search_nonin_form->addElement('html', $html_string);
        }
    }

    $search_nonin_form->display();

} elseif ($page == 'assign_student') {

    $mode = optional_param('mode', 'view', PARAM_TEXT);

    if ($mode == 'view') {

        echo html_writer::tag('h2',
                get_string('assign_student', 'mod_coripodatacollection'),
                ['class' => 'h2', 'style' => 'margin-bottom: 25px']);

        $gruppi = $DB->get_records('coripodatacollection_gruppi', ['erogationid' => $erogation->id]);

        $table_orari = new html_table();
        $table_orari->head = [
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
        $table_orari->align = ['center', 'center', 'center', 'center', 'center', 'center', 'center', 'center',
                'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center'];

        foreach ($gruppi as $gruppo) {

            if ($gruppo->completato != 1)
                continue;

            $group_students = $DB->get_records_sql('
                SELECT DISTINCT alunni.* 
                FROM mdl_coripodatacollection_alunni_gruppo as alunni_gruppo
                JOIN mdl_coripodatacollection_alunni as alunni on alunni.id = alunni_gruppo.studentid
                WHERE alunni_gruppo.groupid =  ' . $gruppo->id);
            $group_students = array_values($group_students);


            $close_url = new moodle_url('/mod/coripodatacollection/viewoperatore.php',
                    ['id' => $cm->id, 'page' => 'assign_student', 'mode' => 'close_group', 'groupid' => $gruppo->id]);
            $close_group_button = html_writer::tag('a',
                    get_string('close_group', 'mod_coripodatacollection'),
                    ['href' => $close_url, 'class' => 'btn btn-primary', 'style' => 'margin-right: 5px']
            );

            $open_url = new moodle_url('/mod/coripodatacollection/viewoperatore.php',
                    ['id' => $cm->id, 'page' => 'assign_student', 'mode' => 'open_group', 'groupid' => $gruppo->id]);
            $open_group_button = html_writer::tag('a',
                    get_string('open_group', 'mod_coripodatacollection'),
                    ['href' => $open_url, 'class' => 'btn btn-primary', 'style' => 'margin-right: 5px']
            );

            $definitive_url = new moodle_url('/mod/coripodatacollection/viewoperatore.php',
                    ['id' => $cm->id, 'page' => 'assign_student', 'mode' => 'definitive_group', 'groupid' => $gruppo->id]);
            $definitive_group_button = html_writer::tag('a',
                    get_string('definitive_group', 'mod_coripodatacollection'),
                    ['href' => $definitive_url, 'class' => 'btn btn-primary', 'style' => 'margin-right: 5px']
            );

            $addnote_button = html_writer::link(
                    new moodle_url(
                            $PAGE->url,
                            ['id' => $cm->id, 'page' => 'assign_student', 'mode' => 'add_note', 'groupid' => $gruppo->id]),
                    $OUTPUT->pix_icon('i/files', get_string('download', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );

            $downlaod_button = html_writer::link(
                    new moodle_url(
                            $PAGE->url,
                            ['id' => $cm->id, 'page' => 'assign_student', 'mode' => 'download_csv', 'groupid' => $gruppo->id]),
                    $OUTPUT->pix_icon('i/export', get_string('download', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );

            if ($gruppo->chiuso != 1)
                $out_button = $close_group_button;
            else
                $out_button = $open_group_button;

            if ($gruppo->definitivo != 1 and $gruppo->chiuso == 1)
                $out_button .= $definitive_group_button . $downlaod_button;

            $table_orari->data[] = [
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
                    $group_students[0]->hash_code ?? '',
                    $group_students[1]->hash_code ?? '',
                    $group_students[2]->hash_code ?? '',
                    $group_students[3]->hash_code ?? '',
                    $group_students[4]->hash_code ?? '',
                    $out_button . $addnote_button];
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
            echo html_writer::table($table_orari);
        }

        $stickyfooterelements = \html_writer::start_div();
        $stickyfooterelements .= html_writer::tag('a',
                get_string('getcsvbutton', 'mod_coripodatacollection'),
                ['href' => new moodle_url( $PAGE->url,
                        ['id' => $cm->id, 'page' => 'assign_student', 'mode' => 'download_all_csv']),
                        'class' => 'btn btn-primary', 'style' => 'display: inline-block;  margin-right: 5px;']);
        $stickyfooterelements .= \html_writer::end_div();
        if (!empty($stickyfooterelements)) {
            $stickyfooter = new \core\output\sticky_footer($stickyfooterelements, ' ',
                    ['style' => 'display: flex; justify-content: space-between;']);
            echo $OUTPUT->render($stickyfooter);
        }

    } elseif ( $mode == 'add_note') {

        $groupid = required_param('groupid', PARAM_INT);
        $group = $DB->get_record('coripodatacollection_gruppi', ['id' => $groupid]);

        echo html_writer::tag('h2',
                get_string('add_note', 'mod_coripodatacollection') . $group->codice,
                ['class' => 'h2', 'style' => 'margin-bottom: 25px']);

        $note_form->display();
    }

}

echo $OUTPUT->footer();
