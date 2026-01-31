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

namespace mod_coripodatacollection\forms;

use core_reportbuilder\local\aggregation\count;
use html_table_row;
use html_writer;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class  viewallclasses_form extends \moodleform {

    public function definition() {

        global $DB;
        $mform = $this->_form;

        if (isset($this->_customdata['instanceid'])) {
            $id = $this->_customdata['instanceid'];
        }

        if (isset($this->_customdata['courseid'])) {
            $courseid = $this->_customdata['courseid'];
        }

        if (isset($this->_customdata['viewmode'])) {
            $viewmode = $this->_customdata['viewmode'];
        } else {
            $viewmode = 'unsetted';
        }

        if ($viewmode == 'institute') {
            $institute = $this->_customdata['instituteid'];
            $plessi = $this->get_institute_plexes($courseid, $institute);
        } elseif ($viewmode == 'project') {
            $plessi = $this->getplessi($courseid);
        } elseif ($viewmode == 'teacher') {
            $institute = $this->_customdata['instituteid'];
            $plessi = $this->get_institute_plexes($courseid, $institute);
        } else {
            $plessi = $this->getplessi($courseid);
        }

        $erogazione = $DB->get_record('coripodatacollection_erogations', ['courseid' => $courseid]);

        if (empty($plessi)) {

            if ($viewmode !== 'teacher') {
                $noinputimg = 'pix/noentries_zero_state.svg';
                echo html_writer::start_div('text-xs-center text-center mt-4');
                echo html_writer::img($noinputimg, get_string('no_registered_plex', 'mod_coripodatacollection'),
                        ['style' => 'display: block; margin: 0 auto;']);
                echo html_writer::tag('h5', get_string('no_registered_plex', 'mod_coripodatacollection'),
                        ['class' => 'h5 mt-3 mb-0']);
                echo html_writer::end_div();
            }
        }

        foreach ($plessi as $plesso) {


            if ($viewmode == 'teacher') {
                $teacherid = $this->_customdata['teacherid'];
                $classes = $DB->get_records_sql('SELECT mdl_coripodatacollection_classes.* 
                        FROM mdl_coripodatacollection_classes JOIN mdl_coripodatacollection_classadmin 
                        ON mdl_coripodatacollection_classadmin.classid = mdl_coripodatacollection_classes.id
                        WHERE plesso = ' . $plesso->idplesso . ' AND erogazione = '. $erogazione->id . ' AND userid = ' . $teacherid);
            } else {
                $classes = $DB->get_records('coripodatacollection_classes',
                        ['plesso' => $plesso->idplesso, 'erogazione' => $erogazione->id]);
            }

            if ($viewmode == 'institute') {
                $mform->addElement('header', 'header' . $plesso->idplesso,
                        $plesso->denominazioneistituto . ' -- ' . $plesso->denominazioneplesso);
                $mform->setExpanded('header' . $plesso->idplesso, false);
                $table = $this->get_institute_table($classes, $id);
            } elseif ($viewmode == 'project') {
                $mform->addElement('header', 'header' . $plesso->idplesso,
                        $plesso->denominazioneistituto . ' -- ' . $plesso->denominazioneplesso);
                $mform->setExpanded('header' . $plesso->idplesso, false);
                $table = $this->get_project_table($classes, $id);
            } elseif ($viewmode == 'teacher') {
                $table = $this->get_teacher_table($classes, $id);
                if (!empty($classes)) {
                    $mform->addElement('header', 'header' . $plesso->idplesso,
                            $plesso->denominazioneistituto . ' -- ' . $plesso->denominazioneplesso);
                    $mform->setExpanded('header' . $plesso->idplesso, false);
                }
            } else {

                $current_date = time();
                $pre_eval_phase = $erogazione->start_val_pre <= $current_date && $current_date <= $erogazione->end_val_pre;
                $post_eval_phase = $erogazione->start_val_post <= $current_date && $current_date <= $erogazione->end_val_post;
                foreach ($classes as $class) {
                    if ($pre_eval_phase && $class->completati_res_pre == 0) {
                        unset($classes[$class->id]);
                    } elseif ($post_eval_phase && $class->completati_res_post == 0) {
                        unset($classes[$class->id]);
                    }
                }
                $table = $this->get_evaluator_table($classes, $id);
                if (!empty($classes)) {
                    $mform->addElement('header', 'header' . $plesso->idplesso,
                            $plesso->denominazioneistituto . ' -- ' . $plesso->denominazioneplesso);
                    $mform->setExpanded('header' . $plesso->idplesso, false);
                }
            }

            if (empty($classes)) {
                if ($viewmode != 'teacher' and $viewmode != 'evaluator') {
                    $noinputimg = 'pix/noentries_zero_state.svg';
                    $html_string = html_writer::start_div('text-xs-center text-center mt-4',
                            ['style' => ' display: flex; align-items: center; 
                            justify-content: center; gap: 20px; margin: 20px auto;']);
                    $html_string .= html_writer::img(
                            $noinputimg,
                            get_string('no_class_for_plex', 'mod_coripodatacollection'),
                            ['style' => 'width: 100px; height: auto;']);
                    $html_string .= html_writer::tag(
                            'h5',
                            get_string('no_class_for_plex', 'mod_coripodatacollection'),
                            ['class' => 'h5 mt-3 mb-0']);
                    $html_string .= html_writer::end_div();
                    $mform->addElement('html', $html_string);
                }
            } else {
                $mform->addElement('html', html_writer::table($table));
            }


        }

    }

    /**
     * Get all the plexes in the database.
    */
    private function getplessi($courseid): array {
        global $DB;
        return $DB->get_records_sql('SELECT plessi.id as idplesso, denominazioneistituto, denominazioneplesso
                                            FROM mdl_coripodatacollection_erogations as erog
                                            JOIN mdl_coripodatacollection_istituti_x_progetto_x_aa as iiaa 
                                                ON erog.id = iiaa.erogation
                                            JOIN mdl_coripodatacollection_istituti as istituti 
                                                ON istituti.id = iiaa.instituteid
                                            JOIN mdl_coripodatacollection_plessi as plessi 
                                                ON plessi.instituteid = istituti.id
                                            WHERE erog.courseid =' . $courseid);

    }

    /**
     * Get all the plexes of a given institute.
    */
    private function get_institute_plexes($courseid, $institute): array {
        global $DB;
        return $DB->get_records_sql('SELECT plessi.id as idplesso, denominazioneistituto, denominazioneplesso
                                            FROM mdl_coripodatacollection_erogations as erog
                                            JOIN mdl_coripodatacollection_istituti_x_progetto_x_aa as iiaa 
                                                ON erog.id = iiaa.erogation
                                            JOIN mdl_coripodatacollection_istituti as istituti 
                                                ON istituti.id = iiaa.instituteid
                                            JOIN mdl_coripodatacollection_plessi as plessi 
                                                ON plessi.instituteid = istituti.id
                                            WHERE erog.courseid =' . $courseid . ' AND istituti.id = ' . $institute);
    }

    /**
     * Gives as return the table that should displayed on the viewevaluator page all the classes for each plex in the database
     */
    private function get_evaluator_table($classes, $id) : \html_table {

        global $DB, $OUTPUT;

        $table = new \html_table();
        $table->head = [
                get_string('year', 'mod_coripodatacollection'),
                get_string('denomination', 'mod_coripodatacollection'),
                get_string('refering_teacher', 'mod_coripodatacollection'),
                get_string('email', 'mod_coripodatacollection'),
                get_string('total_students', 'mod_coripodatacollection'),
                get_string('numberstudents_registered', 'mod_coripodatacollection'),
                get_string('numberstudents_identity', 'mod_coripodatacollection'),
                get_string('numberstudents_consensus', 'mod_coripodatacollection'),
                get_string('status', 'mod_coripodatacollection'),
                ''
        ];
        $table->align = ['center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center'];

        foreach ($classes as $class) {

            $insegnante = $DB->get_record('coripodatacollection_classadmin', ['classid' => $class->id]);
            $insegnante = $DB->get_record('user', ['id' => $insegnante->userid]);
            $censiti = $DB->get_records('coripodatacollection_class_students', ['classid' => $class->id]);
            $con_cie = $DB->get_records('coripodatacollection_class_students',
                    ['classid' => $class->id, 'carta_identita' => 1]);
            $con_consenso = $DB->get_records('coripodatacollection_class_students',
                    ['classid' => $class->id, 'consenso' => 1]);
            $erogation = $DB->get_record('coripodatacollection_erogations',
                    ['courseid' => $this->_customdata['courseid']]);

            $viewbutton = html_writer::link(
                    new moodle_url(
                            '/mod/coripodatacollection/viewevaluator.php',
                            ['id' => $id, 'page' => 'alunni', 'classid' => $class->id]
                    ),
                    $OUTPUT->pix_icon('i/hide', get_string('view_class', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );

            $current_date = time();
            $fase_censimento = $erogation->start_censimento <= $current_date && $erogation->end_censimento >= $current_date;
            $fase_pre_rinforzo = $erogation->start_val_pre <= $current_date && $erogation->start_val_post >= $current_date;
            $fase_post_rinforzo = $erogation->start_val_post <= $current_date;

            $status = '';
            if ($fase_censimento) {
                $status = get_string('census', 'mod_coripodatacollection');
            } else if ($fase_pre_rinforzo) {
                if ( $class->completati_res_pre == 1 && $class->statistichepre == 0 )
                    $status = get_string('index_calculus', 'mod_coripodatacollection');
                elseif ( $class->statistichepre == 1 && $class->valutazione_classe_pre == 0 )
                    $status = get_string('primary_eval', 'mod_coripodatacollection');
                elseif ( $class->valutazione_classe_pre == 1 && $class->valutazione_globale_pre == 0 )
                    $status = get_string('final_eval', 'mod_coripodatacollection');
                elseif ( $class->valutazione_globale_pre == 1 )
                    $status = get_string('eval_ended', 'mod_coripodatacollection');
            } else if ($fase_post_rinforzo) {
                if ( $class->completati_res_post == 1 && $class->statistichepost == 0 )
                    $status = get_string('index_calculus', 'mod_coripodatacollection');
                elseif ( $class->statistichepost == 1 && $class->valutazione_classe_post == 0 )
                    $status = get_string('primary_eval', 'mod_coripodatacollection');
                elseif ( $class->valutazione_classe_post == 1 && $class->valutazione_globale_post == 0 )
                    $status = get_string('final_eval', 'mod_coripodatacollection');
                elseif ( $class->valutazione_globale_post == 1 )
                    $status = get_string('eval_ended', 'mod_coripodatacollection');
            }

            $opzionianni = [
                    -1 => get_string('pluri_class', 'mod_coripodatacollection'),
                    0 => get_string('first_1', 'mod_coripodatacollection'),
                    1 => get_string('second_2', 'mod_coripodatacollection'),
                    2 => get_string('third_3', 'mod_coripodatacollection'),
                    3 => get_string('fourth_4', 'mod_coripodatacollection'),
                    4 => get_string('fifth_5', 'mod_coripodatacollection')
            ];
            $table->data[] = new html_table_row([
                    $opzionianni[$class->anno - 1],
                    $class->classe,
                    $insegnante->lastname . ' ' . $insegnante->firstname,
                    $insegnante->email,
                    $class->numerostudenti,
                    count($censiti),
                    count($con_cie),
                    count($con_consenso),
                    $status,
                    $viewbutton]);
        }
        return $table;
    }

    /**
     * Gives as return the table that should displayed on the viewevaluator page all the classes for each plex in the database
     */
    private function get_project_table($classes, $id) : \html_table {

        global $DB, $OUTPUT;

        $table = new \html_table();
        $table->head = [
                get_string('year', 'mod_coripodatacollection'),
                get_string('denomination', 'mod_coripodatacollection'),
                get_string('refering_teacher', 'mod_coripodatacollection'),
                get_string('email', 'mod_coripodatacollection'),
                get_string('total_students', 'mod_coripodatacollection'),
                get_string('numberstudents_registered', 'mod_coripodatacollection'),
                get_string('numberstudents_identity', 'mod_coripodatacollection'),
                get_string('numberstudents_consensus', 'mod_coripodatacollection'),
                get_string('registered_results_pre', 'mod_coripodatacollection'),
                get_string('registered_results_post', 'mod_coripodatacollection'),
                get_string('status', 'mod_coripodatacollection'),
                ''
        ];

        $table->align = ['center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center'];

        foreach ($classes as $class) {
            $insegnante = $DB->get_record('coripodatacollection_classadmin', ['classid' => $class->id]);
            $insegnante = $DB->get_record('user', ['id' => $insegnante->userid]);
            $censiti = $DB->get_records('coripodatacollection_class_students', ['classid' => $class->id]);
            $con_cie = $DB->get_records('coripodatacollection_class_students',
                    ['classid' => $class->id, 'carta_identita' => 1]);
            $con_consenso = $DB->get_records('coripodatacollection_class_students',
                            ['classid' => $class->id, 'consenso' => 1]);
            $erogation = $DB->get_record('coripodatacollection_erogations',
                    ['courseid' => $this->_customdata['courseid']]);

            $viewbutton = html_writer::link(
                    new moodle_url(
                            '/mod/coripodatacollection/viewproject.php',
                            ['id' => $id, 'page' => 'alunni', 'classid' => $class->id]
                    ),
                    $OUTPUT->pix_icon('i/hide', get_string('view_class', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );

            $current_date = time();
            $fase_censimento = $erogation->start_censimento <= $current_date && $erogation->end_censimento >= $current_date;
            $fase_pre_rinforzo = $erogation->start_val_pre <= $current_date && $erogation->end_val_pre >= $current_date;
            $fase_post_rinforzo = $erogation->start_val_post <= $current_date && $erogation->end_val_post >= $current_date;

            $status = '';
            if ($fase_censimento) {
                $status = get_string('census', 'mod_coripodatacollection');
            } else if ($fase_pre_rinforzo) {
                $status = $class->completati_res_pre == 0 ?
                        get_string('insertion_results', 'coripodatacollection') :
                        get_string('insertion_completed', 'coripodatacollection');
            } else if ($fase_post_rinforzo) {
                $status = $class->completati_res_post == 0 ?
                        get_string('insertion_results', 'coripodatacollection'):
                        get_string('insertion_completed', 'coripodatacollection');
            }

            $opzionianni = [
                    -1 => get_string('pluri_class', 'mod_coripodatacollection'),
                    0 => get_string('first_1', 'mod_coripodatacollection'),
                    1 => get_string('second_2', 'mod_coripodatacollection'),
                    2 => get_string('third_3', 'mod_coripodatacollection'),
                    3 => get_string('fourth_4', 'mod_coripodatacollection'),
                    4 => get_string('fifth_5', 'mod_coripodatacollection')
            ];
            $table->data[] = new html_table_row([
                    $opzionianni[$class->anno - 1],
                    $class->classe,
                    $insegnante->lastname . ' ' . $insegnante->firstname,
                    $insegnante->email,
                    $class->numerostudenti,
                    count($censiti),
                    count($con_cie),
                    count($con_consenso),
                    results_counting($class->id, 'prerinforzo'),
                    results_counting($class->id, 'postrinforzo'),
                    $status,
                    $viewbutton]);
        }
        return $table;
    }

    /**
     * Gives as return the table that should displayed on the viewdirector page all the classes for each plex of the insititute
     */
    private function get_institute_table($classes, $id) : \html_table {

        global $DB, $OUTPUT, $USER;

        $table = new \html_table();
        $table->head = [
                get_string('year', 'mod_coripodatacollection'),
                get_string('denomination', 'mod_coripodatacollection'),
                get_string('refering_teacher', 'mod_coripodatacollection'),
                get_string('email', 'mod_coripodatacollection'),
                get_string('total_students', 'mod_coripodatacollection'),
                get_string('numberstudents_registered', 'mod_coripodatacollection'),
                get_string('numberstudents_identity', 'mod_coripodatacollection'),
                get_string('numberstudents_consensus', 'mod_coripodatacollection'),
                get_string('partecipant_students', 'mod_coripodatacollection'),
                ''
        ];
        $table->align = ['center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center'];

        foreach ($classes as $class) {
            $insegnante = $DB->get_record('coripodatacollection_classadmin', ['classid' => $class->id]);
            $insegnante = $DB->get_record('user', ['id' => $insegnante->userid]);
            $censiti = $DB->get_records('coripodatacollection_class_students', ['classid' => $class->id]);
            $partecipanti = $DB->get_records_sql('
                    SELECT * FROM mdl_coripodatacollection_alunni 
                    JOIN mdl_coripodatacollection_class_students 
                    ON studentid=mdl_coripodatacollection_alunni.id
                    WHERE classid = ' . $class->id . ' AND consenso = 1
                    AND mdl_coripodatacollection_class_students.carta_identita = 1');
            $con_cie = $DB->get_records('coripodatacollection_class_students',
                    ['classid' => $class->id, 'carta_identita' => 1]);
            $con_consenso = $DB->get_records('coripodatacollection_class_students',
                    ['classid' => $class->id, 'consenso' => 1]);
            $erogation = $DB->get_record('coripodatacollection_erogations',
                    ['courseid' => $this->_customdata['courseid']]);

            $delbutton = html_writer::link(
                    new moodle_url(
                            '/mod/coripodatacollection/viewdirector.php',
                            ['id' => $id, 'delete' => $class->id]
                    ),
                    $OUTPUT->pix_icon('i/delete', get_string('cancel', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );
            $editbutton = html_writer::link(
                    new moodle_url(
                            '/mod/coripodatacollection/viewdirector.php',
                            ['id' => $id, 'page' => 'newclass', 'modify' => $class->id]
                    ),
                    $OUTPUT->pix_icon('i/edit', get_string('modify', 'mod_coripodatacollection'),
                                    'moodle', ['role' => 'button'])
            );

            $viewbuttonurl = new moodle_url('/mod/coripodatacollection/viewdirector.php',
                    ['id' => $id, 'page' => 'alunni', 'classid' => $class->id]);
            if (!empty($DB->get_record('coripodatacollection_classadmin',
                    ['classid' => $class->id, 'userid' => $USER->id]))) {
                $viewbuttonurl = new moodle_url('/mod/coripodatacollection/viewteacher.php',
                        ['id' => $id, 'page' => 'alunni', 'classid' => $class->id]);
            }
            $viewbutton = html_writer::link(
                    $viewbuttonurl,
                    $OUTPUT->pix_icon('i/hide', get_string('view_class', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );


            $plesso = $DB->get_record('coripodatacollection_plessi', ['id' => $class->plesso]);
            $istituto = $DB->get_record('coripodatacollection_istituti', ['id' => $class->istituto]);
            $erogation = $DB->get_record('coripodatacollection_erogations', ['id' => $class->erogazione]);
            $progetto = $DB->get_record('coripodatacollection_projects', ['id' => $erogation->projectid]);


            $documentbutton = '<a name="pdf_button" progetto="' .$progetto->projectname.'" classe="' . $class->classe . '" 
                                istituto="'. $istituto->denominazioneistituto . '" plesso="' . $plesso->denominazioneplesso .'">
                                <i class="icon fa fa-id-badge fa-fw " title="Vedi documento per consenso" role="img" aria-label="Vedi documento per consenso" id="yui_3_18_1_1_1737235085991_57" style="color: #0F6CBF; cursor: pointer;"></i>
                               </a>';
            $missing_res = '';

            $current_date = time();
            $fase_censimento = $erogation->start_censimento <= $current_date && $erogation->end_censimento >= $current_date;
            $fase_pre_rinforzo = $erogation->start_val_pre <= $current_date && $erogation->end_val_pre >= $current_date;
            $fase_post_rinforzo = $erogation->start_val_post <= $current_date && $erogation->end_val_post >= $current_date;

            if ($fase_censimento) {
                if (count($censiti) < $class->numerostudenti) {
                    $missing_res = $OUTPUT->pix_icon('i/risk_xss',
                            get_string('missing_students', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button']);
                }
            } else if ($fase_pre_rinforzo) {
                if (results_missing($class->id, 'prerinforzo')) {
                    $missing_res = $OUTPUT->pix_icon('i/risk_xss',
                            get_string('missing_result', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button']);
                }
            } else if ($fase_post_rinforzo) {
                if (results_missing($class->id, 'postrinforzo')) {
                    $missing_res = $OUTPUT->pix_icon('i/risk_xss',
                            get_string('missing_result', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button']);
                }
            }

            if ( $erogation->end_censimento < $current_date ) {
                // Se non sono più nella fase di censimento
                $opzionianni = [
                        -1 => get_string('pluri_class', 'mod_coripodatacollection'),
                        0 => get_string('first_1', 'mod_coripodatacollection'),
                        1 => get_string('second_2', 'mod_coripodatacollection'),
                        2 => get_string('third_3', 'mod_coripodatacollection'),
                        3 => get_string('fourth_4', 'mod_coripodatacollection'),
                        4 => get_string('fifth_5', 'mod_coripodatacollection')
                ];
                $table->data[] = new html_table_row([
                        $opzionianni[$class->anno - 1],
                        $class->classe,
                        $insegnante->lastname . ' ' . $insegnante->firstname,
                        $insegnante->email,
                        $class->numerostudenti,
                        count($censiti),
                        count($con_cie),
                        count($con_consenso),
                        count($partecipanti),
                        $missing_res .  $viewbutton . $documentbutton . $editbutton]);
            } else {
                $opzionianni = [
                        -1 => get_string('pluri_class', 'mod_coripodatacollection'),
                        0 => get_string('first_1', 'mod_coripodatacollection'),
                        1 => get_string('second_2', 'mod_coripodatacollection'),
                        2 => get_string('third_3', 'mod_coripodatacollection'),
                        3 => get_string('fourth_4', 'mod_coripodatacollection'),
                        4 => get_string('fifth_5', 'mod_coripodatacollection')
                ];
                $table->data[] = new html_table_row([
                        $opzionianni[$class->anno - 1],
                        $class->classe,
                        $insegnante->lastname . ' ' . $insegnante->firstname,
                        $insegnante->email,
                        $class->numerostudenti,
                        count($censiti),
                        count($con_cie),
                        count($con_consenso),
                        count($partecipanti),
                        $missing_res . $viewbutton . $documentbutton . $editbutton . $delbutton ]);
            }
        }
        return $table;
    }

    /**
     * Gives as return the table that should displayed on the viewteacher page all the classes for each plex in the database
     */
    private function get_teacher_table($classes, $id) : \html_table {

        global $DB, $OUTPUT;

        $table = new \html_table();
        $table->head = ['Anno', 'Denominazione', 'Insegnante di riferimento', 'email', 'Studenti totali', 'Studenti partecipanti', ''];
        $table->head = [
                get_string('year', 'mod_coripodatacollection'),
                get_string('denomination', 'mod_coripodatacollection'),
                get_string('refering_teacher', 'mod_coripodatacollection'),
                get_string('email', 'mod_coripodatacollection'),
                get_string('total_students', 'mod_coripodatacollection'),
                get_string('numberstudents_registered', 'mod_coripodatacollection'),
                get_string('numberstudents_identity', 'mod_coripodatacollection'),
                get_string('numberstudents_consensus', 'mod_coripodatacollection'),
                get_string('partecipant_students', 'mod_coripodatacollection'),
                ''
        ];
        $table->align = ['center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center'];

        foreach ($classes as $class) {

            $insegnante = $DB->get_record('coripodatacollection_classadmin', ['classid' => $class->id]);
            $insegnante = $DB->get_record('user', ['id' => $insegnante->userid]);
            $censiti = $DB->get_records('coripodatacollection_class_students', ['classid' => $class->id]);
            $partecipanti = $DB->get_records_sql('
                    SELECT * FROM mdl_coripodatacollection_alunni 
                    JOIN mdl_coripodatacollection_class_students 
                    ON studentid=mdl_coripodatacollection_alunni.id
                    WHERE classid = ' . $class->id . ' AND consenso = 1
                    AND mdl_coripodatacollection_class_students.carta_identita = 1');
            $con_cie = $DB->get_records('coripodatacollection_class_students',
                    ['classid' => $class->id, 'carta_identita' => 1]);
            $con_consenso = $DB->get_records('coripodatacollection_class_students',
                    ['classid' => $class->id, 'consenso' => 1]);
            $erogation = $DB->get_record('coripodatacollection_erogations',
                    ['courseid' => $this->_customdata['courseid']]);

            $viewbutton = html_writer::link(
                    new moodle_url(
                            '/mod/coripodatacollection/viewteacher.php',
                            ['id' => $id, 'page' => 'alunni', 'classid' => $class->id]
                    ),
                    $OUTPUT->pix_icon('i/hide', get_string('view_class', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button'])
            );
            $missing_res = '';

            $current_date = time();
            $fase_censimento = $erogation->start_censimento <= $current_date && $erogation->end_censimento >= $current_date;
            $fase_pre_rinforzo = $erogation->start_val_pre <= $current_date && $erogation->end_val_pre >= $current_date;
            $fase_post_rinforzo = $erogation->start_val_post <= $current_date && $erogation->end_val_post >= $current_date;

            if ($fase_censimento) {
                if (count($censiti) < $class->numerostudenti) {
                    $missing_res = $OUTPUT->pix_icon('i/risk_xss',
                            get_string('missing_students', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button']);
                }
            } else if ($fase_pre_rinforzo) {
                if (results_missing($class->id, 'prerinforzo')) {
                    $missing_res = $OUTPUT->pix_icon('i/risk_xss',
                            get_string('missing_result', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button']);
                }
            } else if ($fase_post_rinforzo) {
                if (results_missing($class->id, 'postrinforzo')) {
                    $missing_res = $OUTPUT->pix_icon('i/risk_xss',
                            get_string('missing_result', 'mod_coripodatacollection'),
                            'moodle', ['role' => 'button']);
                }
            }

            $opzionianni = [
                    -1 => get_string('pluri_class', 'mod_coripodatacollection'),
                    0 => get_string('first_1', 'mod_coripodatacollection'),
                    1 => get_string('second_2', 'mod_coripodatacollection'),
                    2 => get_string('third_3', 'mod_coripodatacollection'),
                    3 => get_string('fourth_4', 'mod_coripodatacollection'),
                    4 => get_string('fifth_5', 'mod_coripodatacollection')
            ];
            $table->data[] = new html_table_row([
                    $opzionianni[$class->anno - 1],
                    $class->classe,
                    $insegnante->lastname . ' ' . $insegnante->firstname,
                    $insegnante->email,
                    $class->numerostudenti,
                    count($censiti),
                    count($con_cie),
                    count($con_consenso),
                    count($partecipanti),
                    $missing_res . $viewbutton]);
        }
        return $table;
    }
}