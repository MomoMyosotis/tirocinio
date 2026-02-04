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

use core\output\sticky_footer;
use html_writer;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class newprova_form extends \moodleform {

    public function definition() {

        global $DB;

        $mform = $this->_form;


        if (isset($this->_customdata['idclasse'])) {
            $idclasse = $this->_customdata['idclasse'];

            $mform->addElement('hidden', 'classe', $idclasse);
            $mform->setType('classe', PARAM_INT);

            if(isset($this->_customdata['table'])) {
                $valutazioni = $this->query_valutazione($this->_customdata['table'], $idclasse);
                $phase = $this->_customdata['table'];

                if ($phase == 'pre') {
                    $this->get_table_pre($mform, $valutazioni, $idclasse);
                } else {
                    $this->get_table_post($mform, $valutazioni, $idclasse);
                }

            }
        }

    }

    private function get_table_pre($mform, $valutazioni, $idclasse) {

        global $DB;

        $htmlstringtable = '';
        $table = new \html_table();

        $table->id = 'result_table';
        $table->head = [
                '', '', '', '',
                get_string('reading', 'mod_coripodatacollection'),
                get_string('writing', 'mod_coripodatacollection'),
                get_string('math', 'mod_coripodatacollection'),
                get_string('accessories_tests', 'mod_coripodatacollection'),
        ];

        if (isset($this->_customdata['export'])) {
            if (!empty($valutazioni)) {
                $downloadlink = html_writer::tag('a',
                        get_string('export_table', 'mod_coripodatacollection'),
                        ['href' => '', 'id' => 'download-link']);
                $table->head[0] = $downloadlink;
            }
        }

        $table->align = ['center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;',
                'center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;',
                'center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;'];
        $table->headspan = [1, 1, 1, 1, 10, 4, 16, 6];
        $table->colclasses = ['gradecell grade i4 overridden grade_type_value cell c2'];
        $htmlstringtable .= substr( $head = html_writer::table($table), 0, strpos($head, '</thead>'));

        $table = new \html_table();
        $table->head = [
                '', '', '', '',
                get_string('phonemes', 'mod_coripodatacollection'),
                get_string('syllables_cv', 'mod_coripodatacollection'),
                get_string('syllables_cvc', 'mod_coripodatacollection'),
                get_string('flat_words', 'mod_coripodatacollection'),
                get_string('nonwords', 'mod_coripodatacollection'),
                get_string('syllables_cv', 'mod_coripodatacollection'),
                get_string('syllables_cvc', 'mod_coripodatacollection'),
                get_string('flat_words', 'mod_coripodatacollection'),
                get_string('nonwords', 'mod_coripodatacollection'),
                get_string('reading_numbers', 'mod_coripodatacollection'),
                get_string('forward_enumeration', 'mod_coripodatacollection'),
                get_string('backward_enumeration', 'mod_coripodatacollection'),
                get_string('quantity_recognition', 'mod_coripodatacollection'),
                get_string('additions', 'mod_coripodatacollection'),
                get_string('subtractions', 'mod_coripodatacollection'),
                get_string('comparison', 'mod_coripodatacollection'),
                get_string('ascending_order', 'mod_coripodatacollection'),
                get_string('descending_order', 'mod_coripodatacollection'),
                get_string('numerical_dictation', 'mod_coripodatacollection'),
                '',
                get_string('syllables_fusion', 'mod_coripodatacollection'),
                get_string('cv_syllables_analyses', 'mod_coripodatacollection'),
                get_string('cvc_syllables_analyses', 'mod_coripodatacollection'),
                get_string('syllables_segmentation', 'mod_coripodatacollection'),
                get_string('consonant_groups_segmentation', 'mod_coripodatacollection'),
        ];
        $table->headspan = [
                1, 1, 1, 1,
                2, 2, 2, 2, 2,
                1, 1, 1, 1,
                2, 2, 2, 2, 2, 2,
                1, 1, 1, 1,
                1, 1, 1, 1, 1, 1,];
        $table->align = array_fill(0, count($table->head), 'center; border: 1px solid lightgrey;');
        $table->class = 'gradereport-grader-table';
        $table->colclasses = ['gradecell grade i4 overridden grade_type_value cell c2'];
        $htmlstringtable .= substr( $head = html_writer::table($table),
                $start = strpos($head, '<tr>'), strpos($head, '</thead>') - $start);

        $data = [
                '',
                get_string('student', 'mod_coripodatacollection'),
                get_string('NAI', 'mod_coripodatacollection'),
                get_string('partial_result', 'mod_coripodatacollection'),
        ];

        foreach ($table->headspan as $i => $val) {
            if ($i<4) {
                continue;
            } elseif ($i == 23) {
                $data[] = get_string('test_administration', 'mod_coripodatacollection');
            } elseif ($val == 1) {
                $data[] = get_string('errors', 'mod_coripodatacollection');
            } else {
                $data[] = get_string('time', 'mod_coripodatacollection');
                $data[] = get_string('errors', 'mod_coripodatacollection');
            }
        }
        $table = new \html_table();
        $table->head = $data;
        $table->align = array_fill(0, count($table->head), 'center; border: 1px solid lightgrey;');
        $htmlstringtable .= substr( $head = html_writer::table($table),
                $start = strpos($head, '<tr>'), strpos($head, '</thead>') - $start);

        $htmlstringtable .= '</thead>';

        $n = count($table->head);
        $table = new \html_table();
        $table->align = array_fill(0, $n, 'center; border: 1px solid lightgrey;');

        $editmode = false;
        if (isset($this->_customdata['editmode'])) {
            $editmode = $this->_customdata['editmode'];
        }

        $listaidvalutazioni = '';
        foreach ($valutazioni as $valutazione) {

            $idvalutazione = $valutazione->id;
            $listaidvalutazioni .= $idvalutazione .'-';

            $arrayvalutazione = get_object_vars($valutazione);
            foreach ($arrayvalutazione as $key => $val) {
                if ($key == 'proveaccessorie' || str_starts_with($key, 'metafonologia')) {
                    unset($arrayvalutazione[$key]);
                    $arrayvalutazione[$key] = $val;
                }
            }

            // Build row with a fixed initial column order to ensure alignment
            $row = [];
            $row[] = $arrayvalutazione['numeroregistro'] ?? '';
            $row[] = $arrayvalutazione['cn'] ?? '';

            // NAI column
            $naival = $arrayvalutazione['nai'] ?? null;
            if ($editmode) {
                $naielement = 'nai[' . $idvalutazione . ']';
                $naioptions = [
                        'type' => 'number',
                        'name' => $naielement,
                        'value' => ($naival === null || $naival === "") ? 0 : intval($naival),
                        'class' => 'form-control statusicons validate-input',
                        'min' => 0,
                        'max' => 2,
                        'step' => 1,
                        'style' => 'width: 70px; margin: 0 auto; display: block; text-align: right;'
                ];
                $row[] = html_writer::empty_tag('input', $naioptions);
            } else {
                $row[] = ($naival === null || $naival === "") ? 0 : intval($naival);
            }

            // Inserimento parziale column
            $partialval = $arrayvalutazione['inserimento_parziale'] ?? null;
            if ($editmode) {
                $partialelement = 'inserimento_parziale[' . $idvalutazione . ']';
                $selections = [1 => get_string('yes', 'mod_coripodatacollection'), 0 => get_string('no', 'mod_coripodatacollection')];
                $options = ['class' => 'form-control statusicons', 'style' => 'width: 70px; margin: 0 auto; display: block; text-align: center;'];
                $selected = ($partialval === null || $partialval === "") ? 0 : intval($partialval);
                $row[] = html_writer::select($selections, $partialelement, $selected, [], $options);
            } else {
                $row[] = ($partialval === null || $partialval === "") ? '' : $partialval;
            }

            foreach ($arrayvalutazione as $key => $val) {
                if (in_array($key, ['id', 'classe', 'alunno', 'periodo', 'erogazione', 'difficolta_prerinforzo', 'lettura_parolecons_tempo', 'lettura_parolecons_errori', 'scrittura_parolecons_errori', 'scrittura_paroleort_errori', 'metodo_didattico', 'lettura_modalita', 'includi_calcolo', 'matematica_trasforma_cifre_errori', 'numeroregistro', 'cn', 'nai', 'inserimento_parziale'])) {
                    continue;
                }

                if ($editmode) {
                    $nomeelemento = $key . '[' . $idvalutazione . ']';
                    if ($key == 'proveaccessorie' || $key == 'inserimento_parziale') {
                        // Use numeric option values for boolean-like fields (0 = No, 1 = Yes).
                        $selections = [
                                1 => get_string('yes', 'mod_coripodatacollection'),
                                0 => get_string('no', 'mod_coripodatacollection')
                        ];
                        $options = [
                                'class' => 'form-control statusicons',
                                'style' => 'width: 70px; margin: 0 auto; display: block; text-align: center;'
                        ];
                        $selected = ($val === null || $val === "") ? 0 : intval($val);
                        $cellatabella = html_writer::select($selections, $nomeelemento, $selected, [], $options);
                    } else {
                        $minvalue = 0;
                        $maxvalue = null;

                        if (str_ends_with($key, 'tempo')) {
                            $minvalue = 3;
                        } elseif (str_ends_with($key, 'errori')) {

                            if (str_contains($key, 'fonemi')) {
                                $maxvalue = 20;
                            } elseif (str_contains($key, 'sillabe_cv') || str_contains($key, 'syllables_cv')) {
                                $maxvalue = 15;
                            } elseif (str_contains($key, 'sillabe_cvc') || str_contains($key, 'syllables_cvc')) {
                                $maxvalue = 6;
                            } elseif (str_contains($key, 'piane') || str_contains($key, 'flat')) {
                                $maxvalue = 6;
                            } elseif (str_contains($key, 'nonparole') || str_contains($key, 'nonwords')) {
                                $maxvalue = 6;
                            } elseif (str_contains($key, 'lettura_numeri') || str_contains($key, 'reading_numbers')) {
                                $maxvalue = 9;
                            } elseif (str_contains($key, 'enumeration') || str_contains($key, 'enumerazione')) {
                                $maxvalue = 20;
                            } elseif (str_contains($key, 'quantita') || str_contains($key, 'quantity')) {
                                $maxvalue = 6;
                            } elseif (str_contains($key, 'addizioni') || str_contains($key, 'additions')) {
                                $maxvalue = 3;
                            } elseif (str_contains($key, 'sottrazioni') || str_contains($key, 'subtractions')) {
                                $maxvalue = 3;
                            } elseif (str_contains($key, 'confronto') || str_contains($key, 'comparison')) {
                                $maxvalue = 6;
                            }
                        }

                        // Special handling for NAI: integer in {0,1,2}, default 0.
                        if (strtolower($key) === 'nai' || strtolower($key) === 'NAI') {
                            $minvalue = 0;
                            $maxvalue = 2;
                            // If stored value is empty, default to 0 for display.
                            $displayvalue = ($val === null || $val === "") ? 0 : intval($val);
                            $step = 1;
                        } else {
                            $displayvalue = $val;
                            $step = str_ends_with($key, 'tempo') ? '0.1' : '0';
                        }

                        $options = [
                                'type' => 'number',
                                'name' => $nomeelemento,
                                'value' => $displayvalue,
                                'class' => 'form-control statusicons validate-input',
                                'min' => $minvalue,
                                'step' => $step,
                                'data-field-name' => $key,
                                'data-min-value' => $minvalue,
                                'style' => 'width: 70px; margin: 0 auto; display: block; text-align: right;'
                        ];

                        if ($maxvalue !== null) {
                            $options['max'] = $maxvalue;
                            $options['data-max-value'] = $maxvalue;
                        }
                        $cellatabella = html_writer::empty_tag('input', $options);
                    }
                    $row[] = $cellatabella;
                } else {
                    $row[] = $val;
                }
            }
            $tablerow = new \html_table_row($row);
            $table->data[] = $tablerow;
        }

        $table->class = 'gradereport-grader-table';
        $table->colclasses = ['gradecell grade i4 overridden grade_type_value cell c2'];
        $htmlstringtable .= substr( $body = html_writer::table($table), strpos($body, '<tbody>'),
                strpos($body, '</tbody>') + 9);

        $mform->addElement('html', $htmlstringtable);

        if ($editmode) {
            $listaidvalutazioni = substr($listaidvalutazioni, 0, -1);
            $mform->addElement('hidden', 'listaIDvalutazioni', $listaidvalutazioni);
            $mform->setType('listaIDvalutazioni', PARAM_ALPHANUMEXT);
            $this->add_sticky_action_buttons(true, get_string('save', 'mod_coripodatacollection'));
        } else {
            if (!isset($this->_customdata['onlyview']) ) {
                $erogation = $DB->get_record('coripodatacollection_erogations', ['courseid' => $this->_customdata['course']]);
                $class = $DB->get_record('coripodatacollection_classes', ['id' => $idclasse]);
                $current_date = time();
                $pre_eval_phase = $erogation->start_val_pre <= $current_date && $current_date <= $erogation->end_val_pre;
                if ( $pre_eval_phase || $class->can_edit_val_pre == 1) {
                    if ($class->completati_res_pre == 0)
                        $this->add_sticky_action_buttons(false,
                                get_string('modify', 'mod_coripodatacollection'));
                    else
                        $this->add_sticky_action_buttons();
                } else {
                    if ($erogation->start_val_pre <= $current_date) {
                        $this->add_sticky_action_buttons();
                    }
                }
            }
        }
    }

    private function get_table_post($mform, $valutazioni, $idclasse) {

        global $DB;

        $htmlstringtable = '';
        $table = new \html_table();
        $table->id = 'result_table';

        $table->head = [
                '', '', '', '',
                get_string('reading', 'mod_coripodatacollection'),
                get_string('writing', 'mod_coripodatacollection'),
                get_string('math', 'mod_coripodatacollection'),
        ];

        if (isset($this->_customdata['export'])) {
            if (!empty($valutazioni)) {
                $downloadlink = html_writer::tag('a',
                        get_string('export_table', 'mod_coripodatacollection'),
                        ['href' => '', 'id' => 'download-link']);
                $table->head[0] = $downloadlink;
            }
        }

        $table->align = ['center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;',
                'center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;',
                'center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;',
                'center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;'];
        $table->headspan = [1, 1, 1, 1, 10, 6, 15,];
        $table->colclasses = ['gradecell grade i4 overridden grade_type_value cell c2'];
        $htmlstringtable .= substr( $head = html_writer::table($table), 0, strpos($head, '</thead>'));

        // Generazione heading della tabella.
        $table = new \html_table();
        $table->head = [
                '', '', '', '',
                get_string('syllables_cv', 'mod_coripodatacollection'),
                get_string('syllables_cvc', 'mod_coripodatacollection'),
                get_string('nonwords', 'mod_coripodatacollection'),
                get_string('flat_words', 'mod_coripodatacollection'),
                get_string('consonant_cluster_words', 'mod_coripodatacollection'),
                get_string('syllables_cv', 'mod_coripodatacollection'),
                get_string('syllables_cvc', 'mod_coripodatacollection'),
                get_string('nonwords', 'mod_coripodatacollection'),
                get_string('flat_words', 'mod_coripodatacollection'),
                get_string('consonant_cluster_words', 'mod_coripodatacollection'),
                get_string('orthographic_cluster_words', 'mod_coripodatacollection'),
                get_string('reading_numbers', 'mod_coripodatacollection'),
                get_string('forward_enumeration', 'mod_coripodatacollection'),
                get_string('backward_enumeration', 'mod_coripodatacollection'),
                get_string('additions', 'mod_coripodatacollection'),
                get_string('subtractions', 'mod_coripodatacollection'),
                get_string('insert_symbol', 'mod_coripodatacollection'),
                get_string('ascending_order', 'mod_coripodatacollection'),
                get_string('descending_order', 'mod_coripodatacollection'),
                get_string('numerical_dictation', 'mod_coripodatacollection'),
                get_string('number_trasformation', 'mod_coripodatacollection')
        ];
        $table->headspan = [
                1, 1, 1, 1,
                2, 2, 2, 2, 2,
                1, 1, 1, 1, 1, 1,
                2, 2, 2, 2, 2,
                1, 1, 1, 1, 1,];
        $table->align = array_fill(0, count($table->head), 'center; border: 1px solid lightgrey;');
        $table->class = 'gradereport-grader-table';
        $table->colclasses = ['gradecell grade i4 overridden grade_type_value cell c2'];
        $htmlstringtable .= substr( $head = html_writer::table($table),
                $start = strpos($head, '<tr>'), strpos($head, '</thead>') - $start);

        $data = [
                '',
                get_string('student', 'mod_coripodatacollection'),
                get_string('partial_result', 'mod_coripodatacollection'),
                get_string('precedent_observation_difficulties', 'mod_coripodatacollection'),
        ];

        foreach ($table->headspan as $i => $val) {
            if ($i<4) {
                continue;
            } elseif ($val == 1) {
                $data[] = get_string('errors', 'mod_coripodatacollection');
            } else {
                $data[] = get_string('time', 'mod_coripodatacollection');
                $data[] = get_string('errors', 'mod_coripodatacollection');
            }
        }
        $table = new \html_table();
        $table->head = $data;
        $table->align = array_fill(0, count($table->head), 'center; border: 1px solid lightgrey;');
        $htmlstringtable .= substr( $head = html_writer::table($table),
                $start = strpos($head, '<tr>'), strpos($head, '</thead>') - $start);

        $htmlstringtable .= '</thead>';


        // Definizione corpo tabella
        $n = count($table->head);
        $table = new \html_table();
        $table->align = array_fill(0, $n, 'center; border: 1px solid lightgrey;');

        $editmode = false;
        if (isset($this->_customdata['editmode'])) {
            $editmode = $this->_customdata['editmode'];
        }

        if(isset($this->_customdata['table'])) {
            $valutazioni = $this->query_valutazione($this->_customdata['table'], $idclasse);
        }
        $listaidvalutazioni = '';

        foreach ($valutazioni as $valutazione) {

            $idvalutazione = $valutazione->id;
            $listaidvalutazioni .= $idvalutazione .'-';

            $arrayvalutazione = get_object_vars($valutazione);
            foreach ($arrayvalutazione as $key => $val) {
                if ($key == 'proveaccessorie' || str_starts_with($key, 'metafonologia')) {
                    unset($arrayvalutazione[$key]);
                }
            }
            $arrayvalutazione = swapAssociativeKeys($arrayvalutazione, 'lettura_piane_tempo', 'lettura_nonparole_tempo');
            $arrayvalutazione = swapAssociativeKeys($arrayvalutazione, 'lettura_piane_errori', 'lettura_nonparole_errori');
            $arrayvalutazione = swapAssociativeKeys($arrayvalutazione, 'scrittura_piane_errori', 'scrittura_nonparole_errori');

            $row = [];
            // Fixed order: register number, student name, partial insertion, preceding observation difficulties
            $row[] = $arrayvalutazione['numeroregistro'] ?? '';
            $row[] = $arrayvalutazione['cn'] ?? '';
            // partial_result / inserimento_parziale
            $partialval = $arrayvalutazione['inserimento_parziale'] ?? ($arrayvalutazione['partial_result'] ?? null);
            if ($editmode) {
                $partialelement = 'inserimento_parziale[' . $idvalutazione . ']';
                $selections = [1 => get_string('yes', 'mod_coripodatacollection'), 0 => get_string('no', 'mod_coripodatacollection')];
                $options = ['class' => 'form-control statusicons', 'style' => 'width: 70px; margin: 0 auto; display: block; text-align: center;'];
                $selected = ($partialval === null || $partialval === "") ? 0 : intval($partialval);
                $row[] = html_writer::select($selections, $partialelement, $selected, [], $options);
            } else {
                $row[] = ($partialval === null || $partialval === "") ? '' : $partialval;
            }
            // preceding observation difficulties (difficolta_prerinforzo)
            $difficoltaval = $arrayvalutazione['difficolta_prerinforzo'] ?? null;
            if ($editmode) {
                $difficoltelement = 'difficolta_prerinforzo[' . $idvalutazione . ']';
                $selections = [1 => get_string('yes', 'mod_coripodatacollection'), 0 => get_string('no', 'mod_coripodatacollection')];
                $options = ['class' => 'form-control statusicons', 'style' => 'width: 70px; margin: 0 auto; display: block; text-align: center;'];
                $selected = ($difficoltaval === null || $difficoltaval === "") ? 0 : intval($difficoltaval);
                $row[] = html_writer::select($selections, $difficoltelement, $selected, [], $options);
            } else {
                $row[] = ($difficoltaval === null || $difficoltaval === "") ? '' : $difficoltaval;
            }

            foreach ($arrayvalutazione as $key => $val) {
                if (in_array($key, ['id', 'classe', 'alunno', 'periodo', 'erogazione', 'lettura_fonemi_tempo', 'lettura_fonemi_errori', 'metodo_didattico', 'matematica_ricquantita_tempo', 'matematica_ricquantita_errori', 'includi_calcolo', 'lettura_modalita', 'numeroregistro', 'cn', 'inserimento_parziale', 'partial_result', 'difficolta_prerinforzo'])) {
                    continue;
                }

                if ($editmode) {
                    $nomeelemento = $key . '[' . $idvalutazione . ']';
                    if ($key == 'difficolta_prerinforzo' || $key == 'proveaccessorie' || $key == 'partial_result' || $key == 'inserimento_parziale') {
                        // Use numeric option values (1 = Yes, 0 = No)
                        $selections = [
                                1 => get_string('yes', 'mod_coripodatacollection'),
                                0 => get_string('no', 'mod_coripodatacollection')
                        ];
                        $options = [
                                'class' => 'form-control statusicons',
                                'style' => 'width: 70px; margin: 0 auto; display: block; text-align: center;'
                        ];
                        $selected = ($val === null || $val === "") ? 0 : intval($val);
                        $cellatabella = html_writer::select($selections, $nomeelemento,$selected, [], $options);
                    } else {
                        $minvalue = 0;
                        $maxvalue = null;

                        if (str_ends_with($key, 'tempo')) {
                            $minvalue = 3;
                        } elseif (str_ends_with($key, 'errori')) {
                            if (str_contains($key, 'fonemi')) {
                                $maxvalue = 20;
                            } elseif (str_contains($key, 'sillabe_cv') || str_contains($key, 'syllables_cv')) {
                                $maxvalue = 15;
                            } elseif (str_contains($key, 'sillabe_cvc') || str_contains($key, 'syllables_cvc')) {
                                $maxvalue = 6;
                            } elseif (str_contains($key, 'piane') || str_contains($key, 'flat')) {
                                $maxvalue = 6;
                            } elseif (str_contains($key, 'nonparole') || str_contains($key, 'nonwords')) {
                                $maxvalue = 6;
                            } elseif (str_contains($key, 'lettura_numeri') || str_contains($key, 'reading_numbers')) {
                                $maxvalue = 9;
                            } elseif (str_contains($key, 'enumeration') || str_contains($key, 'enumerazione')) {
                                $maxvalue = 20;
                            } elseif (str_contains($key, 'quantita') || str_contains($key, 'quantity')) {
                                $maxvalue = 6;
                            } elseif (str_contains($key, 'addizioni') || str_contains($key, 'additions')) {
                                $maxvalue = 3;
                            } elseif (str_contains($key, 'sottrazioni') || str_contains($key, 'subtractions')) {
                                $maxvalue = 3;
                            } elseif (str_contains($key, 'confronto') || str_contains($key, 'comparison')) {
                                $maxvalue = 6;
                            }
                        }

                        $options = [
                                'type' => 'number',
                                'name' => $nomeelemento,
                                'value' => $val,
                                'class' => 'form-control statusicons validate-input',
                                'min' => $minvalue,
                                'step' => str_ends_with($key, 'tempo') ? '0.1' : '0',
                                'data-field-name' => $key,
                                'data-min-value' => $minvalue,
                                'style' => 'width: 70px; margin: 0 auto; display: block; text-align: right;'
                        ];

                        if ($maxvalue !== null) {
                            $options['max'] = $maxvalue;
                            $options['data-max-value'] = $maxvalue;
                        }

                        $cellatabella = html_writer::empty_tag('input', $options);
                    }
                    $row[] = $cellatabella;
                } else {
                    $row[] = $val;
                }
            }
            $tablerow = new \html_table_row($row);
            $table->data[] = $tablerow;
        }

        $table->class = 'gradereport-grader-table';
        $table->colclasses = ['gradecell grade i4 overridden grade_type_value cell c2'];
        $htmlstringtable .= substr( $body = html_writer::table($table), strpos($body, '<tbody>'),
                strpos($body, '</tbody>') + 9);

        $mform->addElement('html', $htmlstringtable);

        if ($editmode) {
            $listaidvalutazioni = substr($listaidvalutazioni, 0, -1);
            $mform->addElement('hidden', 'listaIDvalutazioni', $listaidvalutazioni);
            $mform->setType('listaIDvalutazioni', PARAM_ALPHANUMEXT);
            $this->add_sticky_action_buttons(true, get_string('save', 'mod_coripodatacollection'));
        } else {
            if (!isset($this->_customdata['onlyview']) ) {
                $erogation = $DB->get_record('coripodatacollection_erogations', ['courseid' => $this->_customdata['course']]);
                $class = $DB->get_record('coripodatacollection_classes', ['id' => $idclasse]);
                $current_date = time();
                $post_eval_phase = $erogation->start_val_post <= $current_date && $current_date <= $erogation->end_val_post;
                if ( ($post_eval_phase || $class->can_edit_val_post == 1)) {
                    if ($class->completati_res_post == 0)
                        $this->add_sticky_action_buttons(false,
                                get_string('modify', 'mod_coripodatacollection'));
                    else
                        $this->add_sticky_action_buttons();
                } else {
                    if ($erogation->end_val_post <= $current_date)
                        $this->add_sticky_action_buttons();
                }
            }
        }
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
            } elseif (empty($data['listaIDvalutazioni'])) {
                return (object)$data;
            }else {
                $idvalutazioni = array_map('intval', explode('-', $data['listaIDvalutazioni']));
                unset($data['listaIDvalutazioni']);
                $arraynuovirisultati = [];
                foreach ($idvalutazioni as $id) {
                    $nuovorisultato = result_array_init();
                    $nuovorisultato['id'] = $id;
                    foreach ($data as $field => $listanuovirisultati) {
                        if (!empty($listanuovirisultati[$id])) {
                            // Boolean-like fields: store as integer 0 or 1.
                            if (in_array($field, ['proveaccessorie', 'inserimento_parziale', 'difficolta_prerinforzo', 'partial_result'])) {
                                $valint = intval($listanuovirisultati[$id]);
                                $nuovorisultato[$field] = ($valint === 1) ? 1 : 0;
                            // Textual fields: keep as string.
                            } elseif ($field == 'lettura_modalita' || $field == 'metodo_didattico') {
                                $nuovorisultato[$field] = strval($listanuovirisultati[$id]);
                            // Numeric fields: floats.
                            } else {
                                $nuovorisultato[$field] = floatval($listanuovirisultati[$id]);
                            }
                        }

                        if (isset($listanuovirisultati[$id]) && $listanuovirisultati[$id] == 0) {
                            // If a value of 0 was submitted, ensure numeric/boolean-like fields get 0 and textual fields keep string form.
                            if (in_array($field, ['proveaccessorie', 'inserimento_parziale', 'difficolta_prerinforzo', 'partial_result'])) {
                                $nuovorisultato[$field] = 0;
                            } elseif ($field == 'lettura_modalita' || $field == 'metodo_didattico') {
                                $nuovorisultato[$field] = strval($listanuovirisultati[$id]);
                            } else {
                                $nuovorisultato[$field] = floatval($listanuovirisultati[$id]);
                            }
                        }
                    }
                    // Ensure NAI is always present and constrained to 0,1,2 (integer).
                    if (!isset($nuovorisultato['nai'])) {
                        $nuovorisultato['nai'] = 0;
                    } else {
                        $naiint = intval($nuovorisultato['nai']);
                        if ($naiint < 0) $naiint = 0;
                        if ($naiint > 2) $naiint = 2;
                        $nuovorisultato['nai'] = $naiint;
                    }

                    $arraynuovirisultati[] = (object)$nuovorisultato;
                }
                return $arraynuovirisultati;
            }
        } else {
            return NULL;
        }
    }

    private function query_valutazione($param, $idclasse): ?array {

        global $DB;

        if ($param == 'pre') {
            if (isset($this->_customdata['anonym'])) {
                return $DB->get_records_sql('
                                            SELECT DISTINCT cls_al.numeroregistro, alun.hash_code as cn, res.*
                                            FROM {coripodatacollection_risultati} AS res
                                            join {coripodatacollection_alunni} AS alun ON res.alunno = alun.id
                                            join {coripodatacollection_class_students} AS cls_al ON cls_al.studentid = alun.id
                                            WHERE res.classe = ' . $idclasse . ' 
                                                AND res.classe = cls_al.classid
                                                AND res.periodo = "prerinforzo" 
                                                AND cls_al.numeroregistro is not null 
                                                AND cls_al.consenso = 1
                                            ORDER BY cls_al.numeroregistro');
            } else {
                return $DB->get_records_sql('
                                            SELECT DISTINCT cls_al.numeroregistro, CONCAT(alun.cognome, " " ,alun.nome) as cn, res.*
                                            FROM {coripodatacollection_alunni} AS alun
                                            JOIN {coripodatacollection_class_students} AS cls_al ON alun.id = cls_al.studentid
                                            JOIN {coripodatacollection_risultati} AS res ON alun.id = res.alunno
                                            WHERE res.classe = ' . $idclasse . '  
                                                AND res.classe = cls_al.classid
                                                AND res.periodo = "prerinforzo" 
                                                AND cls_al.numeroregistro is not null 
                                                AND cls_al.consenso = 1
                                            ORDER BY cls_al.numeroregistro');}
        }
        if ($param == 'post') {
            if (isset($this->_customdata['anonym'])) {
                return $DB->get_records_sql('
                                            SELECT DISTINCT cls_al.numeroregistro, alun.hash_code as cn, res.*
                                            FROM {coripodatacollection_risultati} AS res
                                            join {coripodatacollection_alunni} AS alun ON res.alunno = alun.id
                                            join {coripodatacollection_class_students} AS cls_al ON cls_al.studentid = alun.id
                                            WHERE res.classe = ' . $idclasse . '  
                                                AND res.classe = cls_al.classid
                                                AND res.periodo = "postrinforzo" 
                                                AND cls_al.numeroregistro is not null 
                                                AND cls_al.consenso = 1
                                            ORDER BY cls_al.numeroregistro');
            } else {
                return $DB->get_records_sql('
                                            SELECT DISTINCT cls_al.numeroregistro, CONCAT(alun.cognome, " " ,alun.nome) as cn, res.*
                                            FROM {coripodatacollection_risultati} AS res
                                            join {coripodatacollection_alunni} AS alun ON res.alunno = alun.id
                                            join {coripodatacollection_class_students} AS cls_al ON cls_al.studentid = alun.id
                                            WHERE res.classe = ' . $idclasse . ' 
                                                AND res.classe = cls_al.classid
                                                AND res.periodo = "postrinforzo" 
                                                AND cls_al.numeroregistro is not null 
                                                AND cls_al.consenso = 1
                                            ORDER BY cls_al.numeroregistro');
            }
        }

        return null;

    }

    public function add_sticky_action_buttons(bool $cancel = true, ?string $submitlabel = null): void {

        global $OUTPUT, $PAGE, $DB;
        $mform = $this->_form;

        $stickyhtml = '';
        $classic_display = true;

        $editmode = false;
        if (isset($this->_customdata['editmode'])) {
            $editmode = $this->_customdata['editmode'];
        }
        if (!$editmode) {
            $page_param = $PAGE->url->params();
            $class = $DB->get_record('coripodatacollection_classes', ['id' => $page_param['classid']]);
            if ($page_param['page'] = 'primevalutazioni' and !empty($class->metodo_didattico)) {
                $stickyhtml .= \html_writer::start_div('',
                        ['style' => 'display: flex; justify-content: space-between; text-align: center;']);
                $stickyhtml .= \html_writer::start_div('', ['style' => 'display: block; text-align: center;']);
                $stickyhtml .= html_writer::tag('h5', 'Metodo didattico:');
                $stickyhtml .= html_writer::tag('strong', $class->metodo_didattico, ['style' => 'color: lightgray;']);
                $stickyhtml .= \html_writer::end_div();
                $stickyhtml .= \html_writer::start_div('',
                        ['style' => 'display: flex; text-align: center; align-items: center;']);
                $page_param['change_didactic_method'] = true;
                $url = new moodle_url('/mod/coripodatacollection/viewteacher.php', $page_param);
                $stickyhtml .= html_writer::tag('a',
                        'Modifica metodo didattico',
                        ['href' => $url, 'class' => 'btn btn-secondary', 'style' => 'display: inline-block; margin-left:10px;']
                );
                $stickyhtml .= \html_writer::end_div();
                $stickyhtml .= \html_writer::end_div();

                $classic_display = false;
            }
        }

        $stickyhtml .= \html_writer::start_div('', ['style' => 'display: block;']);

        if (is_null($submitlabel)) {
            $stickyhtml.= html_writer::tag('a',
                    get_string('pdf_risultati', 'mod_coripodatacollection'),
                    ['id' => 'pdf_button', 'class' => 'btn btn-primary', 'style' => 'display: inline-block; margin-right:5px;']
            );
            if ($cancel) {
                $page_param = $PAGE->url->params();

                $class = $DB->get_record('coripodatacollection_classes', ['id' => $page_param['classid']]);
                $erogazione = $DB->get_record('coripodatacollection_erogations', ['id' => $class->erogazione]);
                $time = time();
                $prerinforzo = $erogazione->start_val_pre < $time && $time < $erogazione->end_val_pre;
                $postrinforzo = $erogazione->start_val_post < $time && $time < $erogazione->end_val_post;
                if ( ($prerinforzo && $class->statistichepre == 0) || ($postrinforzo && $class->statistichepost == 0) ) {
                    $page_param['reopenmod'] = 1;
                    $url = new moodle_url('/mod/coripodatacollection/viewteacher.php', $page_param);
                    $stickyhtml .= html_writer::tag('a',
                            get_string('result_reopen', 'mod_coripodatacollection'),
                            ['href' => $url, 'class' => 'btn btn-primary', 'style' => 'display: inline-block; margin-right:5px;']
                    );
                }
            }
        } else {

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
            } else {
                $stickyhtml .= html_writer::tag('a',
                        get_string('pdf_risultati', 'mod_coripodatacollection'),
                        ['id' => 'pdf_button', 'class' => 'btn btn-primary', 'style' => 'display: inline-block; margin-right:5px;']
                );
                $stickyhtml .= \html_writer::tag('input', '',
                        [
                                'type' => 'submit',
                                'name' => 'cancel',
                                'id' => 'id_cancel',
                                'value' => get_string('notify_results', 'mod_coripodatacollection'),
                                'class' => 'btn btn-primary mx-1',
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
        }
        $stickyhtml .= \html_writer::end_div();

        if ($classic_display)
            $stickyfooter = new sticky_footer($stickyhtml);
        else
            $stickyfooter = new sticky_footer($stickyhtml, ' ',
                    ['style' => 'display: flex; justify-content: space-between;']);
        $mform->addElement('html', $OUTPUT->render($stickyfooter));

    }

    public function is_cancelled() {
        $mform =& $this->_form;
        if ($mform->isSubmitted()){
            if ($this->optional_param('cancel', 0, PARAM_RAW)) {
                return true;
            }
        }
        return false;
    }


}
