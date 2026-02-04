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

use core\dml\table;
use core\output\sticky_footer;
use core_reportbuilder\local\filters\text;
use html_table_cell;
use html_table_row;
use html_writer;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class statsview_form extends \moodleform {

    protected function definition() {

        $mform = $this->_form;

        $this->students_indexes($mform);

        $mform->addElement('html', '<div style="height: 50px;"></div>');

        $this->class_stats($mform);

    }

    private function students_indexes($mform) {

        global $DB;

        $classid = $this->_customdata['classid'];
        $periodo = $this->_customdata['periodo'];
        $display = $this->_customdata['display'];

        if ($display == 'classe')
            $indici_valutazione = $DB->get_records_sql('SELECT DISTINCT mdl_coripodatacollection_indici_valutazione.* 
                                                 FROM mdl_coripodatacollection_indici_valutazione
                                                    JOIN mdl_coripodatacollection_class_students ON studentid=alunno
                                                 WHERE classe = ' . $classid . ' AND periodo = "' . $periodo . '"
                                                 ORDER BY numeroregistro');
        else
            $indici_valutazione = $DB->get_records_sql('SELECT mdl_coripodatacollection_indici_valutazione.* 
                                                 FROM mdl_coripodatacollection_indici_valutazione
                                                    JOIN mdl_coripodatacollection_class_students ON studentid=alunno
                                                 WHERE classe = ' . $classid . ' 
                                                 AND periodo = "' . $periodo . '"
                                                 AND (valutazione_classe = "Rosso" OR valutazione_classe = "Giallo")
                                                 ORDER BY numeroregistro');

        $class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);
        $erogazione = $DB->get_record('coripodatacollection_erogations', ['id' => $class->erogazione]);

        if ($display == 'classe') {
            $medie = $DB->get_record_sql('SELECT * FROM mdl_coripodatacollection_medie_indici
                                             WHERE classe = ' . $classid . ' AND periodo = "' . $periodo . '"');
            $stddev = $DB->get_record_sql('SELECT * FROM mdl_coripodatacollection_stddev_indici
                                             WHERE classe = ' . $classid . ' AND periodo = "' . $periodo . '"');
        } else {
            $medie = $DB->get_record_sql('SELECT * FROM mdl_coripodatacollection_medie_globali
                                             WHERE erogazione = ' . $class->erogazione . ' AND periodo = "' . $periodo . '"');
            $stddev = $DB->get_record_sql('SELECT * FROM mdl_coripodatacollection_stddev_globali
                                             WHERE erogazione = ' . $class->erogazione . ' AND periodo = "' . $periodo . '"');
        }


        $table = new \html_table();
        $table->id = $display == 'classe' ? 'class_stats_table' : 'global_stats_table';
        $table->head = [
                '',
                get_string('student', 'mod_coripodatacollection'),
                get_string('precedent_observation_difficulties_v2', 'mod_coripodatacollection'),
                get_string('partial_result', 'mod_coripodatacollection'),
                get_string('didatic_method', 'mod_coripodatacollection'),
                get_string('test_administration', 'mod_coripodatacollection'),
                get_string('evaluation', 'mod_coripodatacollection'),
                get_string('specialistic_note', 'mod_coripodatacollection'),
                get_string('reading_correctness', 'mod_coripodatacollection'),
                get_string('reading_speed', 'mod_coripodatacollection'),
                get_string('writing_correctness', 'mod_coripodatacollection'),
                get_string('math_reading_correctness', 'mod_coripodatacollection'),
                get_string('math_enumeration_correctness', 'mod_coripodatacollection'),
                get_string('math_decoding_correctness', 'mod_coripodatacollection'),
                get_string('math_calculus_correctness', 'mod_coripodatacollection'),
                get_string('reading_sublex', 'mod_coripodatacollection'),
                get_string('reading_lex', 'mod_coripodatacollection'),
                get_string('reading_mean', 'mod_coripodatacollection')
        ];
        if ($periodo == 'postrinforzo') {
            unset($table->head[4]);
            unset($table->head[5]);
        } else {
            unset($table->head[2]);
        }
        if ($display == 'global') {
            array_splice($table->head, 5, 0, get_string('preliminar_eval', 'mod_coripodatacollection'));
            array_splice($table->head, 6, 0, get_string('gravity_profile', 'mod_coripodatacollection'));
            array_splice($table->head, 8, 0, get_string('student_report', 'mod_coripodatacollection'));
        }

        if (!empty($indici_valutazione)) {
            $downloadlink = html_writer::tag('a',
                    get_string('export_table', 'mod_coripodatacollection'),
                    ['href' => '', 'id' => 'download-link-' . $display]);
            $table->head[0] = $downloadlink;
        }

        $table->align = array_fill(0, count($table->head), 'center;');
        $table->colclasses = ['gradecell grade i4 overridden grade_type_value cell c2'];

        $listaidindici = '';
        foreach ($indici_valutazione as $ind_val) {

            if ($display == 'global' && $ind_val->valutazione_classe == 'Verde')
                continue;

            $listaidindici .= $ind_val->id .'-';

            $student = $DB->get_record('coripodatacollection_alunni', ['id' => $ind_val->alunno]);
            $student_in_class = $DB->get_record('coripodatacollection_class_students',
                    ['studentid' => $student->id, 'classid' => $classid]);
            $original_res = $DB->get_record('coripodatacollection_risultati', ['id' => $ind_val->risultato_originale]);

            if ($student_in_class->consenso == 0) {
                continue;
            }

            if ($periodo == 'prerinforzo') {
                $row = [
                        $student_in_class->numeroregistro,
                        $student->hash_code,
                        $original_res->inserimento_parziale,
                        $original_res->metodo_didattico,
                        $original_res->proveaccessorie,
                ];
            } else {
                $row = [
                        $student_in_class->numeroregistro,
                        $student->hash_code,
                        $original_res->difficolta_prerinforzo,
                        $original_res->inserimento_parziale
                ];
            }

            if ($display == 'global') {
                $row[] = compute_preliminar_eval($ind_val, $medie, $stddev);
                $row[] = compute_gravity_profile($ind_val);
            }

            $selections = [
                    '' => '',
                    get_string('green', 'mod_coripodatacollection')
                        => get_string('green', 'mod_coripodatacollection'),
                    get_string('yellow', 'mod_coripodatacollection')
                        => get_string('yellow', 'mod_coripodatacollection'),
                    get_string('red', 'mod_coripodatacollection')
                        => get_string('red', 'mod_coripodatacollection')
            ];
            $options = [
                    'type' => 'number',
                    'class' => 'form-control statusicons',
                    'style' => 'height: 25px; width: 100px; padding-top: 0; margin: 0 auto; display: block; text-align: center;'
            ];
            if ($display == 'classe') {
                $row[] = html_writer::select($selections, 'valutazione_classe[' . $ind_val->id . ']',
                        empty($ind_val->valutazione_classe) ? '' : $ind_val->valutazione_classe, [], $options);
            }else {
                $selections = [
                        '' => '',
                        get_string('light_green', 'mod_coripodatacollection')
                        => get_string('light_green', 'mod_coripodatacollection'),
                        get_string('dark_green', 'mod_coripodatacollection')
                        => get_string('dark_green', 'mod_coripodatacollection'),
                        get_string('yellow', 'mod_coripodatacollection')
                        => get_string('yellow', 'mod_coripodatacollection'),
                        get_string('red', 'mod_coripodatacollection')
                        => get_string('red', 'mod_coripodatacollection')
                ];
                $options = [
                        'type' => 'number',
                        'class' => 'form-control statusicons',
                        'style' => 'height: 25px; width: 150px; padding-top: 0; margin: 0 auto; display: block; text-align: center;'
                ];
                $row[] = html_writer::select($selections, 'valutazione_globale[' . $ind_val->id . ']',
                        empty($ind_val->valutazione_globale) ? '' : $ind_val->valutazione_globale, [], $options);
            }
            if ($display == 'global') {
                if ($ind_val->valutazione_globale == "")
                    $reported_text = '';
                else if ($ind_val->valutazione_globale == "Verde chiaro")
                    $reported_text = get_string('not_reported', 'mod_coripodatacollection');
                else
                    $reported_text = get_string('reported', 'mod_coripodatacollection');
                $cell = new html_table_cell($reported_text);
                $cell->id = 'reported[' . $ind_val->id . ']';
                $row[] = $cell;
            }
            $row[] = html_writer::tag('textarea', $ind_val->nota_specialistica, [
                    'name' => 'nota_specialistica[' . $ind_val->id . ']',
                    'rows' => 1,
                    'cols' => 15,
                    'style' => 'resize: none;'
            ]);

            if ($display == 'global') {
                $indexes = $DB->get_records_sql('SELECT mdl_coripodatacollection_indici_valutazione.* 
                                                 FROM mdl_coripodatacollection_indici_valutazione
                                                 WHERE erogazione = ' . $erogazione->id . ' 
                                                 AND periodo = "' . $periodo . '"
                                                 AND (valutazione_classe = "Rosso" OR valutazione_classe = "Giallo")');
                if ($periodo == 'prerinforzo') {
                    $outlier = $erogazione->outlier_pre == 1;
                } else {
                    $outlier = $erogazione->outlier_post == 1;
                }
            }else {
                $indexes = $indici_valutazione;
                if ($periodo == 'prerinforzo') {
                    $outlier = $class->outlier_pre == 1;
                } else {
                    $outlier = $class->outlier_post == 1;
                }
            }

            $array = [];
            foreach ($indexes as $obj) {
                $array[$obj->id] = $obj->lettura_correttezza;
            }
            $row[] = $this->get_colored_cell(
                    $ind_val->lettura_correttezza,
                    $medie->lettura_correttezza,
                    $stddev->lettura_correttezza,
                    result_missing_for_index('lettura_correttezza', $original_res),
                    $outlier && is_outlier($ind_val->id, $array),
                    'lettura_correttezza',
                    ($periodo == 'prerinforzo')
            );

            $array = [];
            foreach ($indexes as $obj) {
                $array[$obj->id] = $obj->lettura_rapidita;
            }
            $row[] = $this->get_colored_cell(
                    $ind_val->lettura_rapidita,
                    $medie->lettura_rapidita,
                    $stddev->lettura_rapidita,
                    result_missing_for_index('lettura_rapidita', $original_res),
                    $outlier && is_outlier($ind_val->id, $array),
                    'lettura_rapidita',
                    ($periodo == 'prerinforzo')
            );

            $array = [];
            foreach ($indexes as $obj) {
                $array[$obj->id] = $obj->scrittura_correttezza;
            }
            $row[] = $this->get_colored_cell(
                    $ind_val->scrittura_correttezza,
                    $medie->scrittura_correttezza,
                    $stddev->scrittura_correttezza,
                    result_missing_for_index('scrittura_correttezza', $original_res),
                    $outlier && is_outlier($ind_val->id, $array),
                    'scrittura_correttezza',
                    ($periodo == 'prerinforzo')
            );

            $array = [];
            foreach ($indexes as $obj) {
                $array[$obj->id] = $obj->matematica_correttezza_lettura;
            }
            $row[] = $this->get_colored_cell(
                    $ind_val->matematica_correttezza_lettura,
                    $medie->matematica_correttezza_lettura,
                    $stddev->matematica_correttezza_lettura,
                    result_missing_for_index('matematica_correttezza_lettura', $original_res),
                    $outlier && is_outlier($ind_val->id, $array),
                    'matematica_correttezza_lettura',
                    ($periodo == 'prerinforzo')
            );

            $array = [];
            foreach ($indexes as $obj) {
                $array[$obj->id] = $obj->matematica_correttezza_enumerazione;
            }
            $row[] = $this->get_colored_cell(
                    $ind_val->matematica_correttezza_enumerazione,
                    $medie->matematica_correttezza_enumerazione,
                    $stddev->matematica_correttezza_enumerazione,
                    result_missing_for_index('matematica_correttezza_enumerazione', $original_res),
                    $outlier && is_outlier($ind_val->id, $array),
                    'matematica_correttezza_enumerazione',
                    ($periodo == 'prerinforzo')
            );

            $array = [];
            foreach ($indexes as $obj) {
                $array[$obj->id] = $obj->matematica_correttezza_decodifica;
            }
            $row[] = $this->get_colored_cell(
                    $ind_val->matematica_correttezza_decodifica,
                    $medie->matematica_correttezza_decodifica,
                    $stddev->matematica_correttezza_decodifica,
                    result_missing_for_index('matematica_correttezza_decodifica', $original_res),
                    $outlier && is_outlier($ind_val->id, $array),
                    'matematica_correttezza_decodifica',
                    ($periodo == 'prerinforzo')
            );

            $array = [];
            foreach ($indexes as $obj) {
                $array[$obj->id] = $obj->matematica_correttezza_calcolo;
            }
            $row[] = $this->get_colored_cell(
                    $ind_val->matematica_correttezza_calcolo,
                    $medie->matematica_correttezza_calcolo,
                    $stddev->matematica_correttezza_calcolo,
                    result_missing_for_index('matematica_correttezza_calcolo', $original_res),
                    $outlier && is_outlier($ind_val->id, $array),
                    'matematica_correttezza_calcolo',
                    ($periodo == 'prerinforzo')
            );

            $array = [];
            foreach ($indexes as $obj) {
                $array[$obj->id] = $obj->lettura_sublessicale;
            }
            $row[] = $this->get_colored_cell(
                    $ind_val->lettura_sublessicale,
                    $medie->lettura_sublessicale,
                    $stddev->lettura_sublessicale,
                    result_missing_for_index('lettura_sublessicale', $original_res),
                    $outlier && is_outlier($ind_val->id, $array, false),
                    'lettura_sublessicale',
                    ($periodo == 'prerinforzo'),
                    true);

            $array = [];
            foreach ($indexes as $obj) {
                $array[$obj->id] = $obj->lettura_lessicale;
            }
            $row[] = $this->get_colored_cell(
                    $ind_val->lettura_lessicale,
                    $medie->lettura_lessicale,
                    $stddev->lettura_lessicale,
                    result_missing_for_index('lettura_lessicale', $original_res),
                    $outlier && is_outlier($ind_val->id, $array, false),
                    'lettura_lessicale',
                    ($periodo == 'prerinforzo'),
                    true);

            $array = [];
            foreach ($indexes as $obj) {
                $array[$obj->id] = $obj->lettura_media;
            }
            $row[] = $this->get_colored_cell(
                    $ind_val->lettura_media,
                    $medie->lettura_media,
                    $stddev->lettura_media,
                    result_missing_for_index('lettura_media', $original_res),
                    $outlier && is_outlier($ind_val->id, $array, false),
                    'lettura_media',
                    ($periodo == 'prerinforzo'),
                    true);

            $table->data[] = new html_table_row($row);

        }

        $mform->addElement('html', html_writer::table($table));

        $listaidindici = substr($listaidindici, 0, -1);
        $mform->addElement('hidden', 'listaIDindici', $listaidindici);
        $mform->setType('listaIDindici', PARAM_ALPHANUMEXT);

        $erogazione = $DB->get_record('coripodatacollection_erogations', ['id' => $class->erogazione]);

        if ($periodo == 'prerinforzo') {
            $left_buttons = ['res-classe' => get_string('show_row_results', 'mod_coripodatacollection')];
            if ($class->statistichepre == 1 && $display == 'global') {
                $left_buttons['stats-classe'] = get_string('show_stats_table', 'mod_coripodatacollection');
            }
            if ($class->valutazione_classe_pre == 1 && $erogazione->calcolo_globale_pre == 1 && $display == 'classe') {
                $left_buttons['stats-globale'] = get_string('show_stats_table_global', 'mod_coripodatacollection');
            }
            $this->add_sticky_action_buttons(false,
                    get_string('save_evaluation', 'mod_coripodatacollection'), $left_buttons);
        } else {
            $left_buttons = ['res-classe' => get_string('show_row_results', 'mod_coripodatacollection')];
            if ($class->statistichepost == 1 && $display == 'global') {
                $left_buttons['stats-classe'] = get_string('show_stats_table', 'mod_coripodatacollection');
            }
            if ($class->valutazione_classe_post == 1 && $erogazione->calcolo_globale_post == 1 && $display == 'classe') {
                $left_buttons['stats-globale'] = get_string('show_stats_table_global', 'mod_coripodatacollection');
            }
            $this->add_sticky_action_buttons(false,
                    get_string('save_evaluation', 'mod_coripodatacollection'), $left_buttons);
        }


    }

    private function class_stats($mform) {

        global $DB;

        $classid = $this->_customdata['classid'];
        $periodo = $this->_customdata['periodo'];
        $display = $this->_customdata['display'];

        $class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);

        if ($display == 'classe')
            $mform->addElement('header', 'header1',
                    get_string('avg_std_class', 'mod_coripodatacollection'));
        else
            $mform->addElement('header', 'header1',
                    get_string('avg_std_global', 'mod_coripodatacollection'));

        if ($display == 'classe') {
            $medie = $DB->get_record_sql('SELECT * FROM mdl_coripodatacollection_medie_indici
                                             WHERE classe = ' . $classid . ' AND periodo = "' . $periodo . '"');
            $stddev = $DB->get_record_sql('SELECT * FROM mdl_coripodatacollection_stddev_indici
                                             WHERE classe = ' . $classid . ' AND periodo = "' . $periodo . '"');
        } else {
            $medie = $DB->get_record_sql('SELECT * FROM mdl_coripodatacollection_medie_globali
                                             WHERE erogazione = ' . $class->erogazione . ' AND periodo = "' . $periodo . '"');
            $stddev = $DB->get_record_sql('SELECT * FROM mdl_coripodatacollection_stddev_globali
                                             WHERE erogazione = ' . $class->erogazione . ' AND periodo = "' . $periodo . '"');
        }

        $table = new \html_table();
        $table->id = 'class_stats_table';
        $table->head = [
                get_string('measure', 'mod_coripodatacollection'),
                get_string('reading_correctness', 'mod_coripodatacollection'),
                get_string('reading_speed', 'mod_coripodatacollection'),
                get_string('writing_correctness', 'mod_coripodatacollection'),
                get_string('math_reading_correctness', 'mod_coripodatacollection'),
                get_string('math_enumeration_correctness', 'mod_coripodatacollection'),
                get_string('math_decoding_correctness', 'mod_coripodatacollection'),
                get_string('math_calculus_correctness', 'mod_coripodatacollection'),
                get_string('reading_sublex', 'mod_coripodatacollection'),
                get_string('reading_lex', 'mod_coripodatacollection'),
                get_string('reading_mean', 'mod_coripodatacollection')
        ];

        $table->align = array_fill(0, count($table->head), 'center;');
        $table->colclasses = ['gradecell grade i4 overridden grade_type_value cell c2'];

        $table->data[] = new html_table_row([
                get_string('avg_values', 'mod_coripodatacollection'),
                $medie->lettura_correttezza,
                $medie->lettura_rapidita,
                $medie->scrittura_correttezza,
                $medie->matematica_correttezza_lettura,
                $medie->matematica_correttezza_enumerazione,
                $medie->matematica_correttezza_decodifica,
                $medie->matematica_correttezza_calcolo,
                $medie->lettura_sublessicale,
                $medie->lettura_lessicale,
                $medie->lettura_media,
        ]);

        $table->data[] = new html_table_row([
                get_string('stddev_values', 'mod_coripodatacollection'),
                $stddev->lettura_correttezza,
                $stddev->lettura_rapidita,
                $stddev->scrittura_correttezza,
                $stddev->matematica_correttezza_lettura,
                $stddev->matematica_correttezza_enumerazione,
                $stddev->matematica_correttezza_decodifica,
                $stddev->matematica_correttezza_calcolo,
                $stddev->lettura_sublessicale,
                $stddev->lettura_lessicale,
                $stddev->lettura_media,
        ]);

        $mform->addElement('html', html_writer::table($table));

    }

    function get_data(): object|array|null {

        if (!$this->is_cancelled() and $this->is_submitted() and $this->is_validated()) {
            $data = $_POST;
            unset($data['sesskey']); // we do not need to return sesskey
            unset($data['_qf__'.$this->_formname]);   // we do not need the submission marker too
            unset($data['id']);
            unset($data['page']);
            unset($data['classid']);
            unset($data['classe']);
            unset($data['editmode']);
            if (empty($data)) {
                return NULL;
            } elseif (empty($data['listaIDindici'])) {
                return (object)$data;
            }else {

                $display = $this->_customdata['display'];
                if (!isset($data['valutazione_classe']) && $display == 'classe')
                    return null;
                else if (!isset($data['valutazione_globale']) && $display == 'global')
                    return null;
                else if (!isset($data['valutazione_classe']) && !isset($data['valutazione_globale']))
                    return null;

                $idvalutazioni = array_map('intval', explode('-', $data['listaIDindici']));
                unset($data['listaIDindici']);
                $arraynuovirisultati = [];
                foreach ($idvalutazioni as $id) {
                    if (isset($data['valutazione_classe'])) {
                        $nuovorisultato = [
                                'id' => $id,
                                'valutazione_classe' => $data['valutazione_classe'][$id],
                                'nota_specialistica' => $data['nota_specialistica'][$id]
                        ];
                    } else {
                        $nuovorisultato = [
                                'id' => $id,
                                'valutazione_globale' => $data['valutazione_globale'][$id],
                                'nota_specialistica' => $data['nota_specialistica'][$id]
                        ];
                    }
                    $arraynuovirisultati[] = (object)$nuovorisultato;
                }
                return $arraynuovirisultati;
            }
        } else {
            return NULL;
        }
    }

    private function get_colored_cell($val, $media, $stddev, $is_partial_index, $outlier, $colname = '', $appendcomputed = false, $inverted = false) {

        if (is_null( $val )){
            $cell = new html_table_cell();
            $cell->text = '';
            return $cell;
        }

        if (!$inverted) {
            if ($val < $media + 1.5 * $stddev || $stddev == 0) {
                $color = 'index-color-green';
            } else if ($val < $media + 1.8 * $stddev) {
                $color = 'index-color-yellow';
            } else {
                $color = 'index-color-red';
            }
        } else {
            if ($val > $media - 1.5 * $stddev || $stddev == 0) {
                $color = 'index-color-green';
            } else if ($val > $media - 1.8 * $stddev) {
                $color = 'index-color-yellow';
            } else {
                $color = 'index-color-red';
            }
        }

        if ($outlier) {
            if ($stddev == 0) {
                if ((!$inverted && $val > $media) || ($inverted && $val < $media))
                    $color = 'index-color-red';
            }
            $color .= ' outlier';
        }

        $cell = new html_table_cell();
        $cell->text = round($val, 2);

        // Optionally compute percentage or z-score depending on column
        $computed = null;
        // Manual maxima for percentage-based indices
        $percent_max = [
            'lettura_correttezza' => 53,
            'scrittura_correttezza' => 33,
            'matematica_correttezza_enumerazione' => 40,
            'matematica_correttezza_calcolo' => 6
        ];

        $time_based = [
            'lettura_rapidita',
            'lettura_sublessicale',
            'lettura_lessicale',
            'lettura_media'
        ];

        if (!empty($colname) && $appendcomputed) {
            if (array_key_exists($colname, $percent_max)) {
                $max = $percent_max[$colname];
                if ($max > 0) {
                    $computed = round(($val / $max) * 100, 1) . '%';
                }
            } else if (in_array($colname, $time_based)) {
                if ($stddev != 0) {
                    $z = ($val - $media) / $stddev;
                    $computed = 'z=' . round($z, 2);
                } else {
                    $computed = 'z=N/A';
                }
            }
        }

        if ($is_partial_index)
            $cell->text = '* ' . $cell->text;

        if (!is_null($computed))
            $cell->text .= ' | ' . $computed;

        $cell->attributes = ['class' => $color];
        return $cell;
    }

    public function add_sticky_action_buttons(bool $cancel = true, ?string $submitlabel = null, array $optional_buttons = null): void {

        global $OUTPUT, $PAGE;
        $display = $this->_customdata['display'];
        $mform = $this->_form;

        $stickyhtml = '';

        if ( !is_null($optional_buttons) ) {
            $stickyhtml .= \html_writer::start_div();
            foreach ($optional_buttons as $name => $optional_button)
                $stickyhtml .= html_writer::tag('a',
                        $optional_button,
                        ['name' => $name, 'class' => 'btn btn-secondary', 'style' => 'display: inline-block; margin-right:5px;']
                );
            $stickyhtml .= \html_writer::end_div();
        }

        $stickyhtml .= \html_writer::start_div();
        if ($cancel) {
            $stickyhtml .= \html_writer::tag('input', '',
                    [
                            'type' => 'submit',
                            'name' => 'cancel',
                            'id' => 'id_cancel',
                            'value' => get_string('cancel', 'mod_coripodatacollection'),
                            'class' => 'btn btn-secondary mx-1',
                            'data-skip-validation' => 1,
                            'data-cancel' => 1,
                            'onclick' => 'skipClientValidation = true; return true;'
                    ]);
            $mform->_registerCancelButton('Cancella');
        }

        $stickyhtml .= \html_writer::tag('input', '',
                [
                        'type' => 'submit',
                        'name' => 'submitbutton',
                        'id' => 'id_submitbutton',
                        'value' => $submitlabel,
                        'class' => 'btn btn-primary mx-1'
                ]);

        $page_param = $PAGE->url->params();
        if ($display == 'classe')
            $page_param['close_eval'] = 1;
        else
            $page_param['close_global'] = 1;
        $url = new moodle_url('/mod/coripodatacollection/viewevaluator.php', $page_param);
        $stickyhtml .= html_writer::tag('a',
                get_string('close_evaluation', 'mod_coripodatacollection'),
                ['href' => $url, 'class' => 'btn btn-primary', 'style' => 'display: inline-block; margin-left:5px;']
        );
        $stickyhtml .= html_writer::end_div();

        $stickyfooter = new sticky_footer($stickyhtml, null,
                ['style' => 'display: flex; justify-content: space-between !important;']);
        $mform->addElement('html', $OUTPUT->render($stickyfooter));

    }


}