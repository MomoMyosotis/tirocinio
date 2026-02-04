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
require "$CFG->libdir/tablelib.php";

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
require_capability('mod/coripodatacollection:teacher', $context);
require_capability('mod/data:viewentry', $context);

$modulecontext = context_module::instance($cm->id);

$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
$PAGE->add_body_class('wide');

$classid = optional_param('classid', -1, PARAM_INT);
$class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);

$urlparam = ['id' => $cm->id, 'page' => $page];
if ($classid != -1) {
    $urlparam['classid'] = $classid;
}
$PAGE->set_url('/mod/coripodatacollection/viewteacher.php', $urlparam);
if ($page == 'alunni' || $page == 'primevalutazioni' || $page == 'ultimevalutazioni' ||  $page == 'newalunno' || $page == 'modalunno'
        || $page == '') {
    $node = $PAGE->secondarynav->add(get_string('classi', 'mod_coripodatacollection'));
    $node->isactive = false;
    $node->action = new moodle_url('/mod/coripodatacollection/viewteacher.php', ['id' => $cm->id, 'page' => 'classes']);
    if (has_capability('mod/coripodatacollection:schoolmanager', $context)) {

        $amministrazioni = $DB->get_records('coripodatacollection_instituteadmin', ['userid' => $USER->id]);
        foreach ($amministrazioni as $admin) {
            if ($DB->record_exists('coripodatacollection_istituti_x_progetto_x_aa',
                    ['instituteid' => $admin->instituteid, 'erogation' => $class->erogazione])) {
                $node->action = new moodle_url('/mod/coripodatacollection/viewdirector.php',
                        ['id' => $cm->id, 'page' => 'classes']);
                break;
            }
        }
    }


    $node = $PAGE->secondarynav->add(get_string('alunni', 'mod_coripodatacollection'));
    $node->isactive = $page == 'alunni' || $page == 'newalunno' || $page == 'modalunno';
    $node->action = new moodle_url('/mod/coripodatacollection/viewteacher.php',
            ['id' => $cm->id, 'page' => 'alunni', 'classid' => $classid]);

    $node = $PAGE->secondarynav->add(get_string('primevalutazioni', 'mod_coripodatacollection'));
    $node->isactive = $page == 'primevalutazioni';
    $node->action = new moodle_url('/mod/coripodatacollection/viewteacher.php',
            ['id' => $cm->id, 'page' => 'primevalutazioni', 'classid' => $classid]);

    $node = $PAGE->secondarynav->add(get_string('ultimevalutazioni', 'mod_coripodatacollection'));
    $node->isactive = $page == 'ultimevalutazioni';
    $node->action = new moodle_url('/mod/coripodatacollection/viewteacher.php',
            ['id' => $cm->id, 'page' => 'ultimevalutazioni', 'classid' => $classid]);
} else {
    $node = $PAGE->secondarynav->add(get_string('classi', 'mod_coripodatacollection'));
    $node->isactive = true;
    $node->action = new moodle_url('/mod/coripodatacollection/viewteacher.php', ['id' => $cm->id, 'page' => 'classes']);
}



// Codice per aprire nella nuova pagina il documento del consenso.
if ($page == 'getfile') {
    $filenum = required_param('file', PARAM_INT);
    $file = $DB->get_record_sql('SELECT * FROM {files} WHERE itemid = ' . $filenum .
            ' AND filename != "." AND filearea = "fileconsenso"');
    $fs = get_file_storage();
    $storedfile = $fs->get_file_by_id($file->id);
    send_stored_file($storedfile);
    die();
}

// Codice per aprire nella nuova pagina il pdf degli alunni censiti.
if ($page == 'pdfalunni') {
    generate_student_pdf($class);
    die();
}

// Codice per la cancellazione di un alunno.
if ($page == 'delalunno') {
    $idalunno = required_param('delete', PARAM_INT);
    $confirm = optional_param('confirm', 0, PARAM_INT);
    if ($confirm != 0) {
        $redirect = new moodle_url( '/mod/coripodatacollection/viewteacher.php',
                ['id' => $id, 'page' => 'alunni', 'classid' => $classid]);
        $DB->delete_records('coripodatacollection_class_students',
                ['studentid' => $idalunno, 'classid' => $classid]);
        $student_results = $DB->get_records('coripodatacollection_risultati',
                ['classe' => $classid, 'alunno' => $idalunno]);
        foreach ($student_results as $student_result) {
            $student_result->classe = null;
            $DB->update_record('coripodatacollection_risultati', $student_result);
        }
        redirect($redirect);
    } else {
        $formcontinue = new single_button(new moodle_url('/mod/coripodatacollection/viewteacher.php',
                ['id' => $id, 'page' => 'delalunno', 'classid' => $classid, 'delete' =>$idalunno,'confirm' => 2]),
                get_string('yes', 'mod_coripodatacollection'));
        $formcancel = new single_button(new moodle_url('/mod/coripodatacollection/viewteacher.php',
                ['id' => $id, 'page' => 'alunni', 'classid' => $classid]),
                get_string('no', 'mod_coripodatacollection'), 'get');
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(get_string('students_delete_allert', 'mod_coripodatacollection'),
                $formcontinue, $formcancel);
        echo $OUTPUT->footer();
        die();
    }
}



// Codice per la gestione del form per modifica o aggiunta di un allunno.
if ($page == 'newalunno' || $page == 'modalunno') {

    if ($page == 'modalunno') {
        $idalunno = required_param('modifica', PARAM_INT);
        $alunno = $DB->get_record('coripodatacollection_alunni', ['id' => $idalunno]);
        $alunno_in_classe = $DB->get_record('coripodatacollection_class_students',
                ['studentid' => $alunno->id, 'classid' => $classid]);

        $alunno->numeroregistro = $alunno_in_classe->numeroregistro;
        $alunno->carta_identita = $alunno_in_classe->carta_identita;
        $alunno->consenso = $alunno_in_classe->consenso;
        if (!empty($alunno_in_classe->codice_consenso)) {
            $alunno->codice_consenso = substr($alunno_in_classe->codice_consenso, -5);
        }

        $newalunno = new \mod_coripodatacollection\forms\newalunno_form(new moodle_url($PAGE->url, ['modifica' => $idalunno]),
                ['classid' => $classid, 'alunno' => $alunno]);
        $newalunno->set_data($alunno);
    } else {
        $newalunno = new \mod_coripodatacollection\forms\newalunno_form($PAGE->url, ['classid' => $classid]);
    }
    $returnpage = new moodle_url('/mod/coripodatacollection/viewteacher.php',
            ['id' => $id, 'page' => 'alunni', 'classid' => $classid]);
    if ($newalunno->is_cancelled()) {
        redirect($returnpage);
    } else if ($data = $newalunno->get_data()) {
        if ($data->consenso == 0) {
            $data->fileconsenso = null;
        }
        if ($data->leggecentoquattro !== get_string('yes', 'mod_coripodatacollection')) {
            $data->problematicacentoquattro = null;
        }

        if ($page == 'modalunno') {
            $data->id = $data->idalunno;
            $DB->update_record('coripodatacollection_alunni', $data);
            $class_student = $DB->get_record('coripodatacollection_class_students',
                    ['classid' => $classid, 'studentid' => $data->id]);
            $class_student->consenso = $data->consenso;
            $class_student->carta_identita = $data->carta_identita;
            $class_student->numeroregistro = $data->numeroregistro;
            if (property_exists($data, 'annofrequentazione')) {
                $class_student->annofrequentazione = $data->annofrequentazione;
            }
            if (property_exists($data, 'codice_consenso')) {
                if ($class_student->carta_identita == 1 && $class_student->consenso == 1) {
                    $class_student->codice_consenso = $newalunno->get_code_prefix($classid) . $data->codice_consenso;
                } else {
                    $class_student->codice_consenso = null;
                }
            }
            $DB->update_record('coripodatacollection_class_students', $class_student);
        } else {
            $data->id = $DB->insert_record('coripodatacollection_alunni', $data);
            if (!property_exists($data, 'annofrequentazione')) {
                $data->annofrequentazione = null;
            }
            $class_student = new stdClass();
            $class_student->classid = $classid;
            $class_student->studentid = $data->id;
            $class_student->numeroregistro = $data->numeroregistro;
            if (property_exists($data, 'annofrequentazione')) {
                $class_student->annofrequentazione = $data->annofrequentazione;
            }
            $class_student->carta_identita = $data->carta_identita;
            $class_student->consenso = $data->consenso;
            if (property_exists($data, 'codice_consenso')) {
                if ($class_student->carta_identita == 1 && $class_student->consenso == 1) {
                    $class_student->codice_consenso = $newalunno->get_code_prefix($classid) . $data->codice_consenso;
                } else {
                    $class_student->codice_consenso = null;
                }
            }
            $DB->insert_record('coripodatacollection_class_students', $class_student);

            do {
                $data->hash_code = strtoupper(hash('joaat', $data->nome . $data->cognome . $data->id));
                $sql = 'SELECT * FROM mdl_coripodatacollection_alunni where hash_code="' . $data->hash_code .'"';
            } while ($DB->record_exists_sql($sql));
            $DB->update_record('coripodatacollection_alunni', ['id' => $data->id, 'hash_code' => $data->hash_code]);

            $class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);
            $DB->insert_record('coripodatacollection_risultati',
                    ['alunno' => $data->id, 'erogazione' => $class->erogazione, 'classe' => $classid, 'periodo' => 'prerinforzo']);
            $DB->insert_record('coripodatacollection_risultati',
                    ['alunno' => $data->id, 'erogazione' => $class->erogazione, 'classe' => $classid, 'periodo' => 'postrinforzo']);

        }
        redirect($returnpage);
    }
}



// Codice per import studenti
$class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);
if ($page == 'alunni' and $class->confermato == -1) {
    $impostingstudentform = new \mod_coripodatacollection\forms\importstudents_form(
            new moodle_url($PAGE->url, ['classid' => $classid]), ['classid' => $classid]);
    if ($impostingstudentform->is_cancelled()) {
        $returnpage = new moodle_url('/mod/coripodatacollection/viewteacher.php', ['id' => $id]);
        redirect($returnpage);
    } elseif ($data = $impostingstudentform->get_data()) {
        $returnpage = new moodle_url('/mod/coripodatacollection/viewteacher.php',
                ['id' => $id, 'page' => 'alunni', 'classid' => $classid]);
        $i = 1;
        foreach ($data as $student) {
            $info_student = $DB->get_record('coripodatacollection_alunni', ['id' => $student->id]);
            $class_student = new stdClass();
            $class_student->classid = $classid;
            $class_student->studentid = $info_student->id;
            $class_student->numeroregistro = $i;
            $class_student->consenso = 0;
            if (property_exists($student, 'annofrequentazione')) {
                $class_student->annofrequentazione = $student->annofrequentazione;
            }
            $DB->insert_record('coripodatacollection_class_students', $class_student);
            $i += 1;

            $class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);
            $class->numerostudenti = $i;
            $class->confermato = 1;
            $DB->update_record('coripodatacollection_classes', $class);

            $DB->insert_record('coripodatacollection_risultati',
                    ['alunno' => $student->id, 'erogazione' => $class->erogazione,
                            'classe' => $classid, 'periodo' => 'prerinforzo']);
            $DB->insert_record('coripodatacollection_risultati',
                    ['alunno' => $student->id, 'erogazione' => $class->erogazione,
                            'classe' => $classid, 'periodo' => 'postrinforzo']);

        }

        redirect($returnpage);
    }

}



// Codice per controllo form per inserimento valutazioni.
if ($page == 'primevalutazioni') {
    $editmode = optional_param('editmode', false, PARAM_BOOL);
    $confirmres = optional_param('confirmres', 0, PARAM_INT);
    $reopen = optional_param('reopenmod', 0, PARAM_INT);

    $actualpage = new moodle_url('/mod/coripodatacollection/viewteacher.php',
            ['id' => $id, 'page' => 'primevalutazioni', 'classid' => $classid, 'editmode' => $editmode]);
    $evaluationtable = new \mod_coripodatacollection\forms\newprova_form($actualpage,
            ['idclasse' => $classid, 'editmode' => $editmode, 'table' => 'pre', 'course' => $course->id]);
    $returnpage = new moodle_url('/mod/coripodatacollection/viewteacher.php',
            ['id' => $id, 'page' => 'primevalutazioni', 'classid' => $classid, 'editmode' => false]);

    // Display allert salvataggio definitivo
    if( $confirmres == 1) {
        $class->completati_res_pre = 1;
        $class->can_edit_val_pre = 0;
        $DB->update_record('coripodatacollection_classes', $class);
        redirect($returnpage);
    }

    // Richiesta riapertura risultati per modifica
    if ( $reopen == 1 ) {

        $institute = $DB->get_record('coripodatacollection_istituti', ['id' => $class->istituto]);
        $plesso = $DB->get_record('coripodatacollection_plessi', ['id' => $class->plesso]);
        $erogation = $DB->get_record('coripodatacollection_erogations', ['id' => $class->erogazione]);
        $project = $DB->get_record('coripodatacollection_projects', ['id' => $erogation->projectid]);
        $admins = $DB->get_records('coripodatacollection_projectadmin', ['projectid' => $erogation->projectid]);

        foreach ($admins as $admin) {

            $user =$DB->get_record('user', ['id' => $admin->userid]);

            $subject = get_string('open_result_request_object', 'mod_coripodatacollection');
            $body = sprintf(
                    get_string('open_result_request_body', 'mod_coripodatacollection'),
                    $user->lastname . ' ' . $user->firstname,
                    'pre rinforzo',
                    $class->classe,
                    $institute->denominazioneistituto,
                    $plesso->denominazioneplesso,
                    $erogation->academicyearedition,
                    $project->projectname
            );
            email_to_user($user, $USER, $subject, $body);
        }

        $class->completati_res_pre = 2;
        $DB->update_record('coripodatacollection_classes', $class);
        redirect($returnpage);
    }

    // Gestione form di popup per metodo didattico.
    $change_didactic_method = optional_param('change_didactic_method', false, PARAM_BOOL);
    if (($editmode and empty($class->metodo_didattico)) or $change_didactic_method) {
        $actualpage = new moodle_url('/mod/coripodatacollection/viewteacher.php',
                ['id' => $id, 'page' => 'primevalutazioni', 'classid' => $classid,
                        'editmode' => $editmode, 'change_didactic_method' => $change_didactic_method]);
        $didactic_method_form = new \mod_coripodatacollection\forms\didactic_method_popup($actualpage,
                ['idclasse' => $classid, 'editmode' => $editmode]);
        if ($didactic_method_form->is_cancelled()) {
            redirect($returnpage);
        } else if ($data = $didactic_method_form->get_data()) {

            $class->metodo_didattico = $data->confirmoptions == 'altro' ? $data->othermethod : $data->confirmoptions;

            $DB->update_record('coripodatacollection_classes', $class);
            $returnpage = new moodle_url('/mod/coripodatacollection/viewteacher.php',
                    ['id' => $id, 'page' => 'primevalutazioni', 'classid' => $classid, 'editmode' => !$change_didactic_method]);
            redirect($returnpage);
        }
    }

    if ($evaluationtable->is_cancelled()) {
        if ($editmode) {
            redirect($returnpage);
        } else {
            $class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);

            if (!results_missing($classid, 'prerinforzo')) {
                if ($confirmres == 0) {
                    echo $OUTPUT->header();
                    $formcontinue = new moodle_url('/mod/coripodatacollection/viewteacher.php',
                            ['id' => $id, 'page' => 'primevalutazioni', 'classid' => $classid, 'editmode' => false, 'confirmres' => 1]);
                    $formcancel = new moodle_url('/mod/coripodatacollection/viewteacher.php',
                            ['id' => $id, 'page' => 'primevalutazioni', 'classid' => $classid, 'editmode' => false]);
                    echo $OUTPUT->confirm(get_string('confirm_allert', 'mod_coripodatacollection',), $formcontinue, $formcancel);
                    echo $OUTPUT->footer();
                    die();
                }
            } else {
                $returnpage = new moodle_url('/mod/coripodatacollection/viewteacher.php',
                    ['id' => $id, 'page' => 'primevalutazioni', 'classid' => $classid, 'editmode' => false, 'resmissing' => true]);
                redirect($returnpage);
            }
        }

    } elseif ($data = $evaluationtable->get_data()) {
        if ($editmode) {
            $class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);
            $class->risultatipre = 1;
            $class->completati_res_pre = 0;
            foreach ($data as $d) {
                $d->periodo = 'prerinforzo';
                $d->difficolta_prerinforzo = 'None';
                $d->erogazione = $class->erogazione;
                $d->proveaccessorie = empty($d->proveaccessorie) ? 0 : intval($d->proveaccessorie);
                $d->inserimento_parziale = empty($d->inserimento_parziale) ? 0 : intval($d->inserimento_parziale);

                $is_all_null = true;
                foreach ($d as $key => $value) {
                    if ($key == 'metodo_didattico' || str_starts_with($key, 'lettura') || str_starts_with($key, 'scrittura')
                            || str_starts_with($key, 'matematica') || str_starts_with($key, 'metafonologia')) {
                        if ($key !== 'lettura_modalita') {
                            if (!is_null($d->$key)) {
                                $is_all_null = false;
                            }
                        }
                    }
                }
                if ($is_all_null) {
                    $d->inserimento_parziale = ($d->inserimento_parziale == 1) ? 1 : null;
                    $d->proveaccessorie = null;
                    $d->lettura_modalita = null;
                }

                // Enforce maximum allowed values to prevent invalid/overflow inputs (server-side clamp).
                $max_map = [
                        'lettura_numeri' => 9,
                        'reading_numbers' => 9,
                        'numeri' => 9,
                        'enumavanti' => 20,
                        'enumindietro' => 20,
                        'enumeration' => 20,
                        'quantita' => 6,
                        'addizioni' => 3,
                        'sottrazioni' => 3,
                        'confronto' => 6,
                ];

                foreach ($d as $key => $val) {
                    if (is_numeric($val) && !is_null($val)) {
                        // Do not allow negative values.
                        if ($val < 0) {
                            $d->$key = 0;
                            continue;
                        }
                        foreach ($max_map as $substr => $maxv) {
                            if (strpos($key, $substr) !== false) {
                                if ($val > $maxv) {
                                    $d->$key = $maxv;
                                }
                                break;
                            }
                        }
                    }
                }

                $DB->update_record('coripodatacollection_risultati', $d);
            }
            $DB->update_record('coripodatacollection_classes', $class);
            redirect($returnpage);
        } else {
            $returnpage = new moodle_url('/mod/coripodatacollection/viewteacher.php',
                    ['id' => $id, 'page' => 'primevalutazioni', 'classid' => $classid, 'editmode' => true]);
            redirect($returnpage);
        }
    }
}



// Codice per controllo form per inserimento valutazioni post rinforzo.
if ($page == 'ultimevalutazioni') {
    $editmode = optional_param('editmode', false, PARAM_BOOL);
    $confirmres = optional_param('confirmres', 0, PARAM_INT);
    $reopen = optional_param('reopenmod', 0, PARAM_INT);

    $actualpage = new moodle_url('/mod/coripodatacollection/viewteacher.php',
            ['id' => $id, 'page' => 'ultimevalutazioni', 'classid' => $classid, 'editmode' => $editmode]);
    $evaluationtable = new \mod_coripodatacollection\forms\newprova_form($actualpage,
            ['idclasse' => $classid, 'editmode' => $editmode, 'table' => 'post', 'course' => $course->id]);
    $returnpage = new moodle_url('/mod/coripodatacollection/viewteacher.php',
            ['id' => $id, 'page' => 'ultimevalutazioni', 'classid' => $classid, 'editmode' => false]);

    // Display allert salvataggio definitivo
    if( $confirmres == 1) {
        $class->completati_res_post = 1;
        $class->can_edit_val_post = 0;
        $DB->update_record('coripodatacollection_classes', $class);
        redirect($returnpage);
    }

    // Richiesta riapertura risultati per modifica
    if ( $reopen == 1 ) {

        $institute = $DB->get_record('coripodatacollection_istituti', ['id' => $class->istituto]);
        $plesso = $DB->get_record('coripodatacollection_plessi', ['id' => $class->plesso]);
        $erogation = $DB->get_record('coripodatacollection_erogations', ['id' => $class->erogazione]);
        $project = $DB->get_record('coripodatacollection_projects', ['id' => $erogation->projectid]);
        $admins = $DB->get_records('coripodatacollection_projectadmin', ['projectid' => $erogation->projectid]);

        foreach ($admins as $admin) {

            $user =$DB->get_record('user', ['id' => $admin->userid]);

            $subject = get_string('open_result_request_object', 'mod_coripodatacollection');
            $body = sprintf(
                    get_string('open_result_request_body', 'mod_coripodatacollection'),
                    $user->lastname . ' ' . $user->firstname,
                    'post rinforzo',
                    $class->classe,
                    $institute->denominazioneistituto,
                    $plesso->denominazioneplesso,
                    $erogation->academicyearedition,
                    $project->projectname
            );
            email_to_user($user, $USER, $subject, $body);
        }

        $class->completati_res_post = 2;
        $DB->update_record('coripodatacollection_classes', $class);
        redirect($returnpage);
    }

    if ($evaluationtable->is_cancelled()) {
        if ($editmode) {
            redirect($returnpage);
        } else {
            $class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);

            if (!results_missing($classid, 'postrinforzo')) {
                if ($confirmres == 0) {
                    echo $OUTPUT->header();
                    $formcontinue = new moodle_url('/mod/coripodatacollection/viewteacher.php',
                            ['id' => $id, 'page' => 'ultimevalutazioni', 'classid' => $classid, 'editmode' => false, 'confirmres' => 1]);
                    $formcancel = new moodle_url('/mod/coripodatacollection/viewteacher.php',
                            ['id' => $id, 'page' => 'ultimevalutazioni', 'classid' => $classid, 'editmode' => false]);
                    echo $OUTPUT->confirm(get_string('confirm_allert', 'mod_coripodatacollection',), $formcontinue, $formcancel);
                    echo $OUTPUT->footer();
                    die();
                }
            } else {
                $returnpage = new moodle_url('/mod/coripodatacollection/viewteacher.php',
                        ['id' => $id, 'page' => 'ultimevalutazioni', 'classid' => $classid, 'editmode' => false, 'resmissing' => true]);
                redirect($returnpage);
            }
        }
    }elseif ($data = $evaluationtable->get_data()) {
        if ($editmode) {
            $class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);
            $class->risultatipost = 1;
            $class->completati_res_post = 0;
            foreach ($data as $d) {
                $d->periodo = 'postrinforzo';
                $d->erogazione = $class->erogazione;
                $d->difficolta_prerinforzo = empty($d->difficolta_prerinforzo) ? 0 : intval($d->difficolta_prerinforzo);
                $d->inserimento_parziale = empty($d->inserimento_parziale) ? 0 : intval($d->inserimento_parziale);

                $is_all_null = true;
                foreach ($d as $key => $value) {
                    if (str_starts_with($key, 'lettura') || str_starts_with($key, 'scrittura')
                            || str_starts_with($key, 'matematica') || str_starts_with($key, 'metafonologia')) {
                        if ($key !== 'lettura_modalita') {
                            if (!is_null($d->$key)) {
                                $is_all_null = false;
                            }
                        }
                    }
                }
                if ($is_all_null) {
                    $d->difficolta_prerinforzo = null;
                    $d->inserimento_parziale = $d->inserimento_parziale == 1 ? 1 : null;
                    $d->lettura_modalita = null;
                }

                // Enforce maximum allowed values to prevent invalid/overflow inputs (server-side clamp).
                $max_map = [
                        'lettura_numeri' => 9,
                        'reading_numbers' => 9,
                        'numeri' => 9,
                        'enumavanti' => 20,
                        'enumindietro' => 20,
                        'enumeration' => 20,
                        'quantita' => 6,
                        'addizioni' => 3,
                        'sottrazioni' => 3,
                        'confronto' => 6,
                ];

                foreach ($d as $key => $val) {
                    if (is_numeric($val) && !is_null($val)) {
                        // Do not allow negative values.
                        if ($val < 0) {
                            $d->$key = 0;
                            continue;
                        }
                        foreach ($max_map as $substr => $maxv) {
                            if (strpos($key, $substr) !== false) {
                                if ($val > $maxv) {
                                    $d->$key = $maxv;
                                }
                                break;
                            }
                        }
                    }
                }

                $DB->update_record('coripodatacollection_risultati', $d);
            }
            $DB->update_record('coripodatacollection_classes', $class);
            redirect($returnpage);
        } else {
            $returnpage = new moodle_url('/mod/coripodatacollection/viewteacher.php',
                    ['id' => $id, 'page' => 'ultimevalutazioni', 'classid' => $classid, 'editmode' => true]);
            redirect($returnpage);
        }
    }
}



echo $OUTPUT->header();

echo $OUTPUT->box_start();

if ($page != 'classes') {
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
    if ($page == 'newalunno') {
        echo html_writer::tag('h2', $plesso->denominazioneplesso . ' - ' . $opzionianni[$class->anno - 1] .
                ' - ' . $class->classe . ' - ' . 'Nuovo alunno');
    } elseif ($page == 'modalunno') {
        echo html_writer::tag('h2', $plesso->denominazioneplesso . ' - ' . $opzionianni[$class->anno - 1] .
                ' - ' . $class->classe . ' - ' . $alunno->cognome . ' ' . $alunno->nome);
    } else {
        echo html_writer::tag('h2', $plesso->denominazioneplesso . ' - ' . $opzionianni[$class->anno - 1] .
                ' - ' . $class->classe);
    }
    echo html_writer::end_div();
}

if ($page == 'alunni') {

    $conf = optional_param('confirm', 3, PARAM_INT);
    if ($conf == 1 || $conf == 2) {
        $class->confermato = $conf;
        $DB->update_record('coripodatacollection_classes', $class);
    }

    // Numero studenti necessita conferma da parte dell'insegnante
    if ($class->confermato == 0) {
        $formcontinue = new single_button(new moodle_url('/mod/coripodatacollection/viewteacher.php',
                ['id' => $id, 'page' => 'alunni', 'classid' => $classid,'confirm' => 1]),
                'Sì');
        $formcancel = new single_button(new moodle_url('/mod/coripodatacollection/viewteacher.php',
                ['id' => $id, 'page' => 'alunni', 'classid' => $classid,'confirm' => 2]),
                'No', 'get');
        echo $OUTPUT->confirm(get_string('confirm_students_number', 'mod_coripodatacollection')
                . $class->numerostudenti .'?', $formcontinue, $formcancel);
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();
        die();
    }
    if ($class->confermato == 2) {
        $paramerror = [
                'title-error' => get_string('confirm_change_number_title', 'mod_coripodatacollection'),
                'message-error' => get_string('confirm_change_number_message', 'mod_coripodatacollection')
        ];
        echo $OUTPUT->render_from_template('coripodatacollection/errorinfo', $paramerror);
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();
        die();
    }
    if ($class->confermato == -1) {

        $impostingstudentform->display();
        echo $OUTPUT->box_end();
        echo '<script src="javascript/import_students.js"></script>';
        echo $OUTPUT->footer();
        die();
    }

    $sql = 'SELECT * 
            FROM {coripodatacollection_alunni} JOIN {coripodatacollection_class_students} 
            ON {coripodatacollection_class_students}.studentid = {coripodatacollection_alunni}.id
            WHERE classid = ' . $classid . ' ORDER BY numeroregistro';
    $results = $DB->get_records_sql($sql);

    $erogation = $DB->get_record('coripodatacollection_erogations',['courseid' => $course->id]);

    $current_date = time();


    $stickyfooterelements = html_writer::tag('a',
            get_string('pdf_alunni', 'mod_coripodatacollection'),
            ['id' => 'pdf_button', 'class' => 'btn btn-primary', 'style' => 'display: inline-block; margin-right:5px;']
    );

    $periodo_cendimento = ($erogation->start_censimento <= $current_date && $current_date <= $erogation->end_censimento);
    if (count($results) < $class->numerostudenti && ($periodo_cendimento || $class->can_edit_censimento == 1)) {
        $linkaddbutton = new moodle_url('/mod/coripodatacollection/viewteacher.php',
                ['id' => $id, 'page' => 'newalunno', 'classid' => $classid]);

        $stickyfooterelements .= html_writer::tag('a',
                get_string('addnewalunno', 'mod_coripodatacollection'),
                ['href' => $linkaddbutton, 'class' => 'btn btn-primary', 'style' => 'display: inline-block;']
        );
    }


    $stickyfooter = new \core\output\sticky_footer($stickyfooterelements);
    echo $OUTPUT->render($stickyfooter);

    $table = new html_table();
    $table->id = 'student_table';
    if ($class->pluriclasse == 1) {
        $table->align =
                ['center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center'];
        $table->head = [
                '',
                get_string('surname', 'mod_coripodatacollection'),
                get_string('name', 'mod_coripodatacollection'),
                get_string('code', 'mod_coripodatacollection'),
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
                ''
        ];
    } else {
        $table->align =
                ['center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center'];
        $table->head = [
                '',
                get_string('surname', 'mod_coripodatacollection'),
                get_string('name', 'mod_coripodatacollection'),
                get_string('code', 'mod_coripodatacollection'),
                get_string('consensus', 'mod_coripodatacollection'),
                get_string('born_in_italy', 'mod_coripodatacollection'),
                get_string('language_difficulty', 'mod_coripodatacollection'),
                get_string('home_language', 'mod_coripodatacollection'),
                get_string('nursery_school_freq', 'mod_coripodatacollection'),
                get_string('nursery_school_difficulty', 'mod_coripodatacollection'),
                get_string('difficulty_noted', 'mod_coripodatacollection'),
                get_string('centoquattro_law_table', 'mod_coripodatacollection'),
                get_string('centoquattro_problem_table', 'mod_coripodatacollection'),
                ''
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

            $delbutton = html_writer::link(
                    new moodle_url(
                            $PAGE->url,
                            ['id' => $id, 'page' => 'delalunno', 'classid' => $classid, 'delete' => $r->studentid]
                    ),
                    $OUTPUT->pix_icon('i/delete', get_string('cancel', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );

            $viewbutton = html_writer::link(
                    new moodle_url(
                            $PAGE->url,
                            ['id' => $id, 'page' => 'modalunno', 'classid' => $classid, 'modifica' => $r->studentid]
                    ),
                    $OUTPUT->pix_icon('i/edit', get_string('modify', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );

            if($r->carta_identita == 0) {
                $cie = $OUTPUT->pix_icon('i/grade_incorrect',
                        get_string('carta_identita_not_given', 'mod_coripodatacollection'));
            } else {
                $cie = $OUTPUT->pix_icon('i/valid',
                        get_string('carta_identita_given', 'mod_coripodatacollection'));
            }
            if($r->consenso == 0) {
                $consenso = $OUTPUT->pix_icon('i/grade_incorrect',
                        get_string('consensus_not_given', 'mod_coripodatacollection'));
            } else {
                $consenso = $OUTPUT->pix_icon('i/valid',
                                get_string('consensus_given', 'mod_coripodatacollection'));
            }

            if ( $periodo_cendimento || $class->can_edit_censimento == 1) {

                if ($class->pluriclasse) {
                    $table->data[] = new html_table_row([
                            $r->numeroregistro,
                            $r->cognome,
                            $r->nome,
                            $r->hash_code,
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
                            $delbutton . $viewbutton]);
                } else {
                    $table->data[] = new html_table_row([
                            $r->numeroregistro,
                            $r->cognome,
                            $r->nome,
                            $r->hash_code,
                            $cie . $consenso,
                            returnNoInfo($r->natoinitalia),
                            returnNoInfo($r->difficoltalinguaggio),
                            returnNoInfo($r->linguaparlatacasa),
                            returnNoInfo($r->frequenzascuolainfanzia),
                            returnNoInfo($r->difficoltascuolainfanzia),
                            returnNoInfo($r->notadifficolta),
                            returnNoInfo($r->leggecentoquattro),
                            is_null($r->problematicacentoquattro) ? '' : $r->problematicacentoquattro,
                            $delbutton . $viewbutton]);
                }

            } else {

                if ($class->pluriclasse) {
                    $table->data[] = new html_table_row([
                            $r->numeroregistro,
                            $r->cognome,
                            $r->nome,
                            $r->hash_code,
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
                            $viewbutton]);
                } else {
                    $table->data[] = new html_table_row([
                            $r->numeroregistro,
                            $r->cognome,
                            $r->nome,
                            $r->hash_code,
                            $cie . $consenso,
                            returnNoInfo($r->natoinitalia),
                            returnNoInfo($r->difficoltalinguaggio),
                            returnNoInfo($r->linguaparlatacasa),
                            returnNoInfo($r->frequenzascuolainfanzia),
                            returnNoInfo($r->difficoltascuolainfanzia),
                            returnNoInfo($r->notadifficolta),
                            returnNoInfo($r->leggecentoquattro),
                            is_null($r->problematicacentoquattro) ? '' : $r->problematicacentoquattro,
                            $viewbutton]);
                }
            }
        }
        echo html_writer::table($table);
    } else {
        $noinputimg = 'pix/noentries_zero_state.svg';
        echo html_writer::start_div('text-xs-center text-center mt-4');
        echo html_writer::img($noinputimg, get_string('no_registered_students', 'mod_coripodatacollection'),
                ['style' => 'display: block; margin: 0 auto;']);
        echo html_writer::tag('h5', get_string('no_registered_students', 'mod_coripodatacollection'),
                ['class' => 'h5 mt-3 mb-0']);
        echo html_writer::end_div();
    }

} elseif ($page == 'newalunno' || $page == 'modalunno') {

    $newalunno->display();

} elseif ($page == 'primevalutazioni') {

    if (($editmode and empty($class->metodo_didattico)) or $change_didactic_method) {
        $didactic_method_form_render = $didactic_method_form->render();

        $popup_didactic_method_param = [
                'title' => get_string('didatic_method', 'mod_coripodatacollection'),
                'description' => get_string('didatic_method_popup_desc', 'mod_coripodatacollection'),
                'formhtml' => $didactic_method_form_render,
        ];

        echo $OUTPUT->render_from_template(
                'mod_coripodatacollection/didacticmethodpopup',
                $popup_didactic_method_param
        );
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();
        die();
    }

    $res_missing = optional_param('resmissing', false, PARAM_BOOL);
    if ($res_missing) {
        $paramerror = [
                'title-error' => get_string('missing_results_title', 'mod_coripodatacollection'),
                'displayClass' => 'block',
                'message-error' => get_string('missing_results_message_teacher', 'mod_coripodatacollection'),
        ];
        echo $OUTPUT->render_from_template('coripodatacollection/errorinfo', $paramerror);
        $paramerror = [
                'title-error' => ' 1. ',
                'displayClass' => 'block',
                'message-error' => get_string('missing_results_message_teacher_case_1', 'mod_coripodatacollection'),
        ];
        echo $OUTPUT->render_from_template('coripodatacollection/errorinfo', $paramerror);
        $paramerror = [
                'title-error' => ' 2. ',
                'displayClass' => 'block',
                'message-error' => get_string('missing_results_message_teacher_case_2', 'mod_coripodatacollection'),
        ];
        echo $OUTPUT->render_from_template('coripodatacollection/errorinfo', $paramerror);
        $paramerror = [
                'title-error' => ' 3. ',
                'displayClass' => 'block',
                'message-error' => get_string('missing_results_message_teacher_case_3', 'mod_coripodatacollection'),
        ];
        echo $OUTPUT->render_from_template('coripodatacollection/errorinfo', $paramerror);
    }

    if($class->completati_res_pre == 1) {
        $paramerror = [
                'title-error' => get_string('send_notification_title', 'mod_coripodatacollection'),
                'message-error' => get_string('send_notification_pre_message', 'mod_coripodatacollection'),
        ];
        echo $OUTPUT->render_from_template('coripodatacollection/allertsuccess', $paramerror);
    } elseif ($class->completati_res_pre == 2) {
        $paramerror = [
                'title-error' => get_string('send_reopen_title', 'mod_coripodatacollection'),
                'message-error' => get_string('send_reopen_pre_message', 'mod_coripodatacollection'),
        ];
        echo $OUTPUT->render_from_template('coripodatacollection/allertsuccess', $paramerror);
    }
    $evaluationtable->display();

} elseif ($page == 'ultimevalutazioni') {

    $res_missing = optional_param('resmissing', false, PARAM_BOOL);
    if ($res_missing) {
        $paramerror = [
                'title-error' => get_string('missing_results_title', 'mod_coripodatacollection'),
                'displayClass' => 'block',
                'message-error' => get_string('missing_results_message_teacher', 'mod_coripodatacollection'),
        ];
        echo $OUTPUT->render_from_template('coripodatacollection/errorinfo', $paramerror);
        $paramerror = [
                'title-error' => ' 1. ',
                'displayClass' => 'block',
                'message-error' => get_string('missing_results_message_teacher_case_1', 'mod_coripodatacollection'),
        ];
        echo $OUTPUT->render_from_template('coripodatacollection/errorinfo', $paramerror);
        $paramerror = [
                'title-error' => ' 2. ',
                'displayClass' => 'block',
                'message-error' => get_string('missing_results_message_teacher_case_3', 'mod_coripodatacollection'),
        ];
        echo $OUTPUT->render_from_template('coripodatacollection/errorinfo', $paramerror);
    }

    if($class->completati_res_post == 1) {
        $paramerror = [
                'title-error' => get_string('send_notification_title', 'mod_coripodatacollection'),
                'message-error' => get_string('send_notification_post_message', 'mod_coripodatacollection'),
        ];
        echo $OUTPUT->render_from_template('coripodatacollection/allertsuccess', $paramerror);
    } elseif ($class->completati_res_post == 2) {
        $paramerror = [
                'title-error' => get_string('send_reopen_title', 'mod_coripodatacollection'),
                'message-error' => get_string('send_reopen_pre_message', 'mod_coripodatacollection'),
        ];
        echo $OUTPUT->render_from_template('coripodatacollection/allertsuccess', $paramerror);
    }

    $evaluationtable->display();

} else {

    echo html_writer::tag('h2', get_string('registered_classes', 'mod_coripodatacollection'),
            ['class' => 'h2', 'style' => 'margin-bottom: 50px;']);

    $teacher = $DB->get_records('coripodatacollection_teachers', ['userid' => $USER->id]);
    foreach ($teacher as $t) {
        if (!empty($t->instituteid)) {
            $institute = $DB->get_record('coripodatacollection_istituti', ['id' => $t->instituteid]);
            $allclasses = new \mod_coripodatacollection\forms\viewallclasses_form(null,
                    ['instanceid' => $id, 'courseid' => $course->id, 'viewmode' => 'teacher',
                            'instituteid' => $institute->id, 'teacherid' => $USER->id]);
            $allclasses->display();
        }
    }


    $erogation = $DB->get_record('coripodatacollection_erogations',['courseid' => $course->id]);

    $current_date = time();
    $stickyfooterelements = '';
    if ( $erogation->start_censimento <= $current_date && $current_date <= $erogation->end_censimento) {

        $stickyfooterelements .= \html_writer::start_div('', ['style' => 'display: flex; justify-content: space-between;']);
        $stickyfooterelements .= \html_writer::start_div('', ['style' => 'display: block; text-align: center;']);
        $stickyfooterelements .= html_writer::tag('h5',
                get_string('census_period', 'mod_coripodatacollection'));
        $stickyfooterelements .= html_writer::tag('strong',
                $data = date("d/m/Y", $erogation->start_censimento) . '--' . $data = date("d/m/Y", $erogation->end_censimento),
                ['style' => 'color: lightgray;']);
        $stickyfooterelements .= \html_writer::end_div();
        $stickyfooterelements .= \html_writer::end_div();


    } elseif ($erogation->start_val_pre <= $current_date && $current_date <= $erogation->end_val_pre) {

        $stickyfooterelements .= \html_writer::start_div('', ['style' => 'display: block; text-align: center;']);
        $stickyfooterelements .= html_writer::tag('h5',
                get_string('pre_reinforce_period', 'mod_coripodatacollection'));
        $stickyfooterelements .= html_writer::tag('strong',
                $data = date("d/m/Y", $erogation->start_val_pre) . '--' . $data = date("d/m/Y", $erogation->end_val_pre),
                ['style' => 'color: lightgray;']);
        $stickyfooterelements .= \html_writer::end_div();
    } elseif ($erogation->start_val_post <= $current_date && $current_date <= $erogation->end_val_post) {

        $stickyfooterelements .= \html_writer::start_div('', ['style' => 'display: block; text-align: center;']);
        $stickyfooterelements .= html_writer::tag('h5',
                get_string('post_reinforce_period', 'mod_coripodatacollection'));
        $stickyfooterelements .= html_writer::tag('strong',
                $data = date("d/m/Y", $erogation->start_val_post) . '--' . $data = date("d/m/Y", $erogation->end_val_post),
                ['style' => 'color: lightgray;']);
        $stickyfooterelements .= \html_writer::end_div();
    }


    if ($stickyfooterelements == '') {
        $stickyfooterelements .= \html_writer::start_div('', ['style' => 'display: block;']);
        $stickyfooterelements .= html_writer::tag('a',
                get_string('get_participation_certificate', 'mod_coripodatacollection'),
                [
                        'id' => 'get-participation-certificate',
                        'class' => 'btn btn-primary',
                        'style' => 'display: inline-block; margin-right: 5px;',
                        'href' => '',
                        'data-user-name' => $USER->lastname . ' ' . $USER->firstname,
                ]
        );
        $stickyfooterelements .= \html_writer::end_div();
        $stickyfooter = new \core\output\sticky_footer($stickyfooterelements);
    } else {
        $stickyfooterelements .= \html_writer::start_div('', ['style' => 'display: block;']);
        $stickyfooterelements .= html_writer::tag('a',
                get_string('get_participation_certificate', 'mod_coripodatacollection'),
                [
                        'id' => 'get-participation-certificate',
                        'class' => 'btn btn-primary',
                        'style' => 'display: inline-block; margin-right: 5px;',
                        'href' => '',
                        'data-user-name' => $USER->lastname . ' ' . $USER->firstname,
                ]
        );
        $stickyfooterelements .= \html_writer::end_div();
        $stickyfooter = new \core\output\sticky_footer($stickyfooterelements, ' ',
                ['style' => 'display: flex; justify-content: space-between;']);
    }
    echo $OUTPUT->render($stickyfooter);


}

echo $OUTPUT->box_end();

if ($page == 'primevalutazioni' or $page == 'ultimevalutazioni') {
    if ($editmode == 1) {
        echo '<script src="javascript/check_result_registration.js"></script>';

        // validazione peri campi
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            // Get all input fields with validation data attributes
            const validateInputs = document.querySelectorAll(".validate-input");

            validateInputs.forEach(input => {
                const minValue = parseFloat(input.getAttribute("data-min-value")) || 0;
                const maxValue = input.getAttribute("data-max-value") ? parseFloat(input.getAttribute("data-max-value")) : null;
                const fieldName = input.getAttribute("data-field-name") || "";

                // On change validation
                input.addEventListener("change", function() {
                    validateField(this, minValue, maxValue, fieldName);
                });

                // On blur validation
                input.addEventListener("blur", function() {
                    validateField(this, minValue, maxValue, fieldName);
                });

                // On input (real-time feedback)
                input.addEventListener("input", function() {
                    const value = parseFloat(this.value);
                    if (isNaN(value) || value === "") return;

                    let warningMsg = "";
                    if (value < minValue) {
                        warningMsg = "Attenzione: il valore minimo è " + minValue;
                    } else if (maxValue !== null && value > maxValue) {
                        warningMsg = "Attenzione: il valore massimo è " + maxValue;
                    }

                    if (warningMsg) {
                        this.style.borderColor = "#ff9800";
                        this.title = warningMsg;
                    } else {
                        this.style.borderColor = "";
                        this.title = "";
                    }
                });
            });

            function validateField(input, minValue, maxValue, fieldName) {
                const value = parseFloat(input.value);
                if (isNaN(value) || value === "") return;

                let isValid = true;
                let message = "Campo valido";

                if (value < minValue) {
                    isValid = false;
                    message = "Valore minimo: " + minValue;
                } else if (maxValue !== null && value > maxValue) {
                    isValid = false;
                    message = "Valore massimo: " + maxValue;
                }

                if (!isValid) {
                    console.warn(fieldName + " - " + message + " (valore inserito: " + value + ")");
                    alert(fieldName + "\\n" + message);
                }
            }
        });
        </script>';
    } else {
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>';
        echo '<script type="module" src="javascript/download_results_pdf.js"></script>';
    }
    echo '<script src="javascript/fixed_table_col.js"></script>';
} elseif ($page == 'alunni') {
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>';
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>';
    echo '<script type="module" src="javascript/download_students_pdf.js"></script>';
} else {
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>';
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.9.1/jszip.min.js"></script>';
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>';
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>';
    echo '<script type="module" src="javascript/download_partecipation_certificate.js"></script>';
}

echo $OUTPUT->footer();
