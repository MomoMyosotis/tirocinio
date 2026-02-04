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
use core_reportbuilder\local\aggregation\count;
use html_table_cell;
use html_table_row;
use html_writer;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class prova_evalview_form extends \moodleform {

    public function definition() {

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
                    $this->class_stats_pre($mform, $idclasse);
                } else {
                    $this->get_table_post($mform, $valutazioni, $idclasse);
                    $this->class_stats_post($mform, $idclasse);
                }

            }
        }
    }

    private function get_table_pre($mform, $valutazioni, $idclasse) {

        global $DB;

        $class = $DB->get_record('coripodatacollection_classes', ['id' => $idclasse]);
        $erogazione = $DB->get_record('coripodatacollection_erogations', ['id' => $class->erogazione]);
        $media_res = $DB->get_record_sql('SELECT * FROM mdl_coripodatacollection_medie_risultati
            WHERE classe =' . $idclasse . ' AND periodo ="prerinforzo"');
        $stddev_res = $DB->get_record_sql('SELECT * FROM mdl_coripodatacollection_stddev_risultati
            WHERE classe =' . $idclasse . ' AND periodo ="prerinforzo"');

        $htmlstringtable = '';
        $table = new \html_table();

        $table->id = 'result_table_evaluator';
        $table->head = [
                '', '', '', '', '', '',
                get_string('reading', 'mod_coripodatacollection'),
                get_string('writing', 'mod_coripodatacollection'),
                get_string('math', 'mod_coripodatacollection'),
                get_string('accessories_tests', 'mod_coripodatacollection'),
        ];

        // Se ci sono elementi allora aggiungo il tasto per poter scaricare l'excel.
        if (!empty($valutazioni)) {
            $downloadlink = html_writer::tag('a',
                    get_string('export_table', 'mod_coripodatacollection'),
                    ['href' => '', 'id' => 'download-link-risultati']);
            $table->head[0] = $downloadlink;
        }

        $table->align = ['center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;',
                'center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;',
                'center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;',
                'center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;,
                center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;'];
        $table->headspan = [1, 1, 1, 1, 1, 1, 10, 4, 16, 6];
        $table->colclasses = ['gradecell grade i4 overridden grade_type_value cell c2'];
        $htmlstringtable .= substr( $head = html_writer::table($table), 0, strpos($head, '</thead>'));

        // Generazione heading della tabella.
        $table = new \html_table();
        $table->head = [
                '', '', '', '', '',
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
                1, 1, 1, 1, 1,
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
                get_string('includes', 'mod_coripodatacollection'),
                get_string('partial_result', 'mod_coripodatacollection'),
                get_string('didatic_method', 'mod_coripodatacollection')
        ];

        foreach ($table->headspan as $i => $val) {
            if ($i<6) {
                continue;
            } elseif ($i == 24) {
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


        // Definizione corpo tabella
        $n = count($table->head);
        $table = new \html_table();
        $table->align = array_fill(0, $n, 'center; border: 1px solid lightgrey;');

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

            $editmode = false;
            if (isset($this->_customdata['editmode'])) {
                $editmode = $this->_customdata['editmode'];
            }

            // Fixed initial column order to ensure alignment
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

            // includes/includi_calcolo column
            $includival = $arrayvalutazione['includi_calcolo'] ?? null;
            if ($includival !== null) {
                $nomeelemento = 'includi_calcolo[' . $idvalutazione . ']';
                $selections = [
                        'Sì' => get_string('yes', 'mod_coripodatacollection'),
                        'No' => get_string('no', 'mod_coripodatacollection')
                ];
                $options = [
                        'type' => 'number',
                        'class' => 'form-control statusicons',
                        'style' => 'height: 25px; width: 70px; padding-top: 0; margin: 0 auto; display: block; text-align: center;'
                ];
                $cellatabella = html_writer::select($selections, $nomeelemento, empty($includival) ? 'Sì' : $includival, [], $options);
                $row[] = $cellatabella;
            } else {
                $row[] = '';
            }

            // Now render remaining columns in original order
            foreach ($arrayvalutazione as $key => $val) {
                if (in_array($key, ['id', 'classe', 'alunno', 'periodo', 'erogazione', 'matematica_trasforma_cifre_errori', 'difficolta_prerinforzo',
                        'lettura_parolecons_tempo', 'lettura_parolecons_errori', 'scrittura_parolecons_errori', 'scrittura_paroleort_errori',
                        'lettura_modalita', 'numeroregistro', 'cn', 'nai', 'includi_calcolo'])) {
                    continue;
                }

                if ($class->statistichepre == 1 && (property_exists($media_res, $key)) && ($arrayvalutazione['includi_calcolo'] ?? '') == 'Sì') {
                    $array = [];
                    foreach ($valutazioni as $obj) {
                        if ($obj->includi_calcolo == 'Sì')
                            $array[$obj->id] = $obj->$key;
                    }
                    $outlier = $class->outlier_pre == 1 && is_outlier($valutazione->id, $array);
                    // Append computed percent or z-score in pre-phase when class statistics are available
                    $row[] = $this->get_colored_cell($val, $media_res->$key, $stddev_res->$key, $outlier, $key, ($class->statistichepre == 1));
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

        $listaidvalutazioni = substr($listaidvalutazioni, 0, -1);
        $mform->addElement('hidden', 'listaIDvalutazioni', $listaidvalutazioni);
        $mform->setType('listaIDvalutazioni', PARAM_ALPHANUMEXT);

        $left_buttons = [];
        if ($class->statistichepre == 1) {
            $left_buttons['stats-classe'] = get_string('show_stats_table', 'mod_coripodatacollection');
        }
        if ($class->valutazione_classe_pre == 1 && $erogazione->calcolo_globale_pre == 1) {
            $left_buttons['stats-globale'] = get_string('show_stats_table_global', 'mod_coripodatacollection');
        }
        $left_buttons = count($left_buttons) == 0 ? null : $left_buttons;
        if ( $erogazione->start_val_pre < time() )
            $this->add_sticky_action_buttons(false,
                    get_string('calculate_stats', 'mod_coripodatacollection'), $left_buttons);

    }

    private function get_table_post($mform, $valutazioni, $idclasse) {

        global $DB;

        $class = $DB->get_record('coripodatacollection_classes', ['id' => $idclasse]);
        $erogazione = $DB->get_record('coripodatacollection_erogations', ['id' => $class->erogazione]);
        $media_res = $DB->get_record_sql('SELECT * FROM mdl_coripodatacollection_medie_risultati
            WHERE classe =' . $idclasse . ' AND periodo ="postrinforzo"');
        $stddev_res = $DB->get_record_sql('SELECT * FROM mdl_coripodatacollection_stddev_risultati
            WHERE classe =' . $idclasse . ' AND periodo ="postrinforzo"');

        $htmlstringtable = '';
        $table = new \html_table();

        $table->id = 'result_table_evaluator';
        $table->head = [
                '', '', '', '', '',
                get_string('reading', 'mod_coripodatacollection'),
                get_string('writing', 'mod_coripodatacollection'),
                get_string('math', 'mod_coripodatacollection'),
        ];

        // Se ci sono elementi allora aggiungo il tasto per poter scaricare l'excel.
        if (isset($this->_customdata['export'])) {
            if (!empty($valutazioni)) {
                $downloadlink = html_writer::tag('a',
                        get_string('export_table', 'mod_coripodatacollection'),
                        ['href' => '', 'id' => 'download-link-risultati']);
                $table->head[0] = $downloadlink;
            }
        }

        $table->align = ['center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;',
                'center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;',
                'center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;',
                'center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;'];
        $table->headspan = [1, 1, 1, 1, 1, 10, 6, 15,];
        $table->colclasses = ['gradecell grade i4 overridden grade_type_value cell c2'];
        $htmlstringtable .= substr( $head = html_writer::table($table), 0, strpos($head, '</thead>'));

        // Generazione heading della tabella.
        $table = new \html_table();
        $table->head = [
                '', '', '', '', '',
                get_string('syllables_cv', 'mod_coripodatacollection'),
                get_string('syllables_cvc', 'mod_coripodatacollection'),
                get_string('flat_words', 'mod_coripodatacollection'),
                get_string('nonwords', 'mod_coripodatacollection'),
                get_string('consonant_cluster_words', 'mod_coripodatacollection'),
                get_string('syllables_cv', 'mod_coripodatacollection'),
                get_string('syllables_cvc', 'mod_coripodatacollection'),
                get_string('flat_words', 'mod_coripodatacollection'),
                get_string('nonwords', 'mod_coripodatacollection'),
                get_string('consonant_cluster_words', 'mod_coripodatacollection'),
                get_string('orthographic_cluster_words', 'mod_coripodatacollection'),
                get_string('reading_numbers', 'mod_coripodatacollection'),
                get_string('forward_enumeration', 'mod_coripodatacollection'),
                get_string('backward_enumeration', 'mod_coripodatacollection'),
                get_string('additions', 'mod_coripodatacollection'),
                get_string('subtractions', 'mod_coripodatacollection'),
                get_string('comparison', 'mod_coripodatacollection'),
                get_string('ascending_order', 'mod_coripodatacollection'),
                get_string('descending_order', 'mod_coripodatacollection'),
                get_string('numerical_dictation', 'mod_coripodatacollection'),
                get_string('number_trasformation', 'mod_coripodatacollection')
        ];
        $table->headspan = [
                1, 1, 1, 1, 1,
                2, 2, 2, 2, 2,
                1, 1, 1, 1, 1, 1,
                2, 2, 2, 2, 2,
                1, 1, 1, 1, 1, ];
        $table->align = array_fill(0, count($table->head), 'center; border: 1px solid lightgrey;');
        $table->class = 'gradereport-grader-table';
        $table->colclasses = ['gradecell grade i4 overridden grade_type_value cell c2'];
        $htmlstringtable .= substr( $head = html_writer::table($table),
                $start = strpos($head, '<tr>'), strpos($head, '</thead>') - $start);

        $data = [
                '',
                get_string('student', 'mod_coripodatacollection'),
                get_string('includes', 'mod_coripodatacollection'),
                get_string('partial_result', 'mod_coripodatacollection'),
                get_string('precedent_observation_difficulties_v2', 'mod_coripodatacollection'),
        ];

        foreach ($table->headspan as $i => $val) {
            if ($i<5) {
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
            foreach ($arrayvalutazione as $key => $val) {
                if ($key == 'id' || $key == 'classe' || $key == 'alunno' || $key == 'periodo' || $key == 'erogazione' || $key == 'proveaccessorie'
                        || $key == 'lettura_fonemi_tempo' || $key == 'lettura_fonemi_errori' || $key == 'metodo_didattico' || $key == 'lettura_modalita'
                        || $key == 'matematica_ricquantita_tempo' || $key == 'matematica_ricquantita_errori'
                ) {
                    continue;
                } elseif ($key == 'numeroregistro' || $key == 'cn') {
                    $row[] = $val;
                } elseif ($key == 'includi_calcolo') {
                    $nomeelemento = $key . '[' . $idvalutazione . ']';
                    $selections = [
                            'Sì' => get_string('yes', 'mod_coripodatacollection'),
                            'No' => get_string('no', 'mod_coripodatacollection')
                    ];
                    $options = [
                            'type' => 'number',
                            'class' => 'form-control statusicons',
                            'style' => 'height: 25px; width: 70px; padding-top: 0; margin: 0 auto; display: block; text-align: center;'
                    ];
                    $cellatabella = html_writer::select($selections, $nomeelemento,empty($val) ? 'Sì' : $val,
                            [], $options);
                    $row[] = $cellatabella;
                } else {
                    if ($class->statistichepost == 1 && (property_exists($media_res, $key)) && $valutazione->includi_calcolo == 'Sì') {

                        $array = [];
                        foreach ($valutazioni as $obj) {
                            if ($obj->includi_calcolo == 'Sì')
                                $array[$obj->id] = $obj->$key;
                        }
                        $outlier = $class->outlier_post == 1 && is_outlier($valutazione->id, $array);

                        // Do not append computed values in post-phase by default
                        $row[] = $this->get_colored_cell($val, $media_res->$key, $stddev_res->$key, $outlier, $key, false);
                    }
                    else
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

        $listaidvalutazioni = substr($listaidvalutazioni, 0, -1);
        $mform->addElement('hidden', 'listaIDvalutazioni', $listaidvalutazioni);
        $mform->setType('listaIDvalutazioni', PARAM_ALPHANUMEXT);

        $left_buttons = [];
        if ($class->statistichepost == 1) {
            $left_buttons['stats-classe'] = get_string('show_stats_table', 'mod_coripodatacollection');
        }
        if ($class->valutazione_classe_post == 1 && $erogazione->calcolo_globale_post == 1) {
            $left_buttons['stats-globale'] = get_string('show_stats_table_global', 'mod_coripodatacollection');
        }
        $left_buttons = count($left_buttons) == 0 ? null : $left_buttons;
        if ( $erogazione->start_val_post < time() )
            $this->add_sticky_action_buttons(false,
                    get_string('calculate_stats', 'mod_coripodatacollection'), $left_buttons);
    }

    private function class_stats_pre($mform, $idclasse) {

        global $DB;

        $class = $DB->get_record('coripodatacollection_classes', ['id' => $idclasse]);
        if ($class->statistichepre == 0)
            return;

        $mform->addElement('header', 'header1',
                get_string('avg_std_class', 'mod_coripodatacollection'));

        $htmlstringtable = '';
        $table = new \html_table();

        $table->id = 'result_table_evaluator';
        $table->head = [
                '',
                get_string('reading', 'mod_coripodatacollection'),
                get_string('writing', 'mod_coripodatacollection'),
                get_string('math', 'mod_coripodatacollection'),
        ];

        // Se ci sono elementi allora aggiungo il tasto per poter scaricare l'excel.
        if (!empty($valutazioni)) {
            $downloadlink = html_writer::tag('a',
                    get_string('export_table', 'mod_coripodatacollection'),
                    ['href' => '', 'id' => 'download-link-risultati']);
            $table->head[0] = $downloadlink;
        }

        $table->align = ['center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;',
                'center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;',
                'center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;',
                'center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;,
                center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;'];
        $table->headspan = [1, 10, 4, 16];
        $table->colclasses = ['gradecell grade i4 overridden grade_type_value cell c2'];
        $htmlstringtable .= substr( $head = html_writer::table($table), 0, strpos($head, '</thead>'));

        // Generazione heading della tabella.
        $table = new \html_table();
        $table->head = [
                '',
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
        ];
        $table->headspan = [
                1,
                2, 2, 2, 2, 2,
                1, 1, 1, 1,
                2, 2, 2, 2, 2, 2,
                1, 1, 1, 1];
        $table->align = array_fill(0, count($table->head), 'center; border: 1px solid lightgrey;');
        $table->class = 'gradereport-grader-table';
        $table->colclasses = ['gradecell grade i4 overridden grade_type_value cell c2'];
        $htmlstringtable .= substr( $head = html_writer::table($table),
                $start = strpos($head, '<tr>'), strpos($head, '</thead>') - $start);

        $data = [''];

        foreach ($table->headspan as $i => $val) {
            if ($i < 1) {
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

        $n = count($table->head);
        $table = new \html_table();
        $table->align = array_fill(0, $n, 'center; border: 1px solid lightgrey;');

        $medie = $DB->get_record_sql('SELECT * FROM mdl_coripodatacollection_medie_risultati
                                             WHERE classe = ' . $idclasse . ' AND periodo = "prerinforzo"');
        $stddev = $DB->get_record_sql('SELECT * FROM mdl_coripodatacollection_stddev_risultati
                                             WHERE classe = ' . $idclasse . ' AND periodo = "prerinforzo"');

        $stddev_cols = array_keys($DB->get_columns('coripodatacollection_stddev_risultati'));
        unset($stddev_cols[0]);
        unset($stddev_cols[1]);
        unset($stddev_cols[2]);
        unset($stddev_cols[3]);

        $row_medie = [get_string('avg_res', 'mod_coripodatacollection')];
        $row_stddev = [get_string('stddev_res', 'mod_coripodatacollection')];
        foreach ($stddev_cols as $col) {

            if ($col == 'lettura_parolecons_tempo' || $col == 'lettura_parolecons_errori'
                || $col == 'scrittura_parolecons_errori' || $col == 'scrittura_paroleort_errori')
                continue;

            $row_medie[] = $medie->$col;
            $row_stddev[] = $stddev->$col;
        }

        $table->data[] = new html_table_row($row_medie);
        $table->data[] = new html_table_row($row_stddev);

        $htmlstringtable .= substr( $body = html_writer::table($table), strpos($body, '<tbody>'),
                strpos($body, '</tbody>') + 9);

        $mform->addElement('html', $htmlstringtable);

    }

    private function class_stats_post($mform, $idclasse) {

        global $DB;

        $class = $DB->get_record('coripodatacollection_classes', ['id' => $idclasse]);
        if ($class->statistichepost == 0)
            return;

        $mform->addElement('header', 'header1',
                get_string('avg_std_class', 'mod_coripodatacollection'));

        $htmlstringtable = '';
        $table = new \html_table();

        $table->id = 'result_table_evaluator';
        $table->head = [
                '',
                get_string('reading', 'mod_coripodatacollection'),
                get_string('writing', 'mod_coripodatacollection'),
                get_string('math', 'mod_coripodatacollection'),
        ];

        // Se ci sono elementi allora aggiungo il tasto per poter scaricare l'excel.
        if (!empty($valutazioni)) {
            $downloadlink = html_writer::tag('a',
                    get_string('export_table', 'mod_coripodatacollection'),
                    ['href' => '', 'id' => 'download-link-risultati']);
            $table->head[0] = $downloadlink;
        }

        $table->align = ['center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;',
                'center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;',
                'center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;',
                'center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;,
                center; border: 1px solid lightgrey;', 'center; border: 1px solid lightgrey;'];
        $table->headspan = [1, 11, 6, 15];
        $table->colclasses = ['gradecell grade i4 overridden grade_type_value cell c2'];
        $htmlstringtable .= substr( $head = html_writer::table($table), 0, strpos($head, '</thead>'));

        // Generazione heading della tabella.
        $table = new \html_table();
        $table->head = [
                '',
                get_string('syllables_cv', 'mod_coripodatacollection'),
                get_string('syllables_cvc', 'mod_coripodatacollection'),
                get_string('flat_words', 'mod_coripodatacollection'),
                get_string('nonwords', 'mod_coripodatacollection'),
                get_string('consonant_cluster_words', 'mod_coripodatacollection'),
                get_string('syllables_cv', 'mod_coripodatacollection'),
                get_string('syllables_cvc', 'mod_coripodatacollection'),
                get_string('flat_words', 'mod_coripodatacollection'),
                get_string('nonwords', 'mod_coripodatacollection'),
                get_string('consonant_cluster_words', 'mod_coripodatacollection'),
                get_string('orthographic_cluster_words', 'mod_coripodatacollection'),
                get_string('reading_numbers', 'mod_coripodatacollection'),
                get_string('forward_enumeration', 'mod_coripodatacollection'),
                get_string('backward_enumeration', 'mod_coripodatacollection'),
                get_string('additions', 'mod_coripodatacollection'),
                get_string('subtractions', 'mod_coripodatacollection'),
                get_string('comparison', 'mod_coripodatacollection'),
                get_string('ascending_order', 'mod_coripodatacollection'),
                get_string('descending_order', 'mod_coripodatacollection'),
                get_string('numerical_dictation', 'mod_coripodatacollection'),
                get_string('number_trasformation', 'mod_coripodatacollection')
        ];
        $table->headspan = [
                1,
                2, 2, 2, 2, 2,
                1, 1, 1, 1, 1, 1,
                2, 2, 2, 2, 2,
                1, 1, 1, 1, 1,];
        $table->align = array_fill(0, count($table->head), 'center; border: 1px solid lightgrey;');
        $table->class = 'gradereport-grader-table';
        $table->colclasses = ['gradecell grade i4 overridden grade_type_value cell c2'];
        $htmlstringtable .= substr( $head = html_writer::table($table),
                $start = strpos($head, '<tr>'), strpos($head, '</thead>') - $start);

        $data = [''];

        foreach ($table->headspan as $i => $val) {
            if ($i < 1) {
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

        $n = count($table->head);
        $table = new \html_table();
        $table->align = array_fill(0, $n, 'center; border: 1px solid lightgrey;');

        $medie = $DB->get_record_sql('SELECT * FROM mdl_coripodatacollection_medie_risultati
                                             WHERE classe = ' . $idclasse . ' AND periodo = "postrinforzo"');
        $stddev = $DB->get_record_sql('SELECT * FROM mdl_coripodatacollection_stddev_risultati
                                             WHERE classe = ' . $idclasse . ' AND periodo = "postrinforzo"');

        $stddev_cols = array_keys($DB->get_columns('coripodatacollection_stddev_risultati'));
        unset($stddev_cols[0]);
        unset($stddev_cols[1]);
        unset($stddev_cols[2]);
        unset($stddev_cols[3]);

        $row_medie = [get_string('avg_res', 'mod_coripodatacollection')];
        $row_stddev = [get_string('stddev_res', 'mod_coripodatacollection')];
        foreach ($stddev_cols as $col) {

            if ($col == 'lettura_parolecons_tempo' || $col == 'lettura_parolecons_errori'
                    || $col == 'scrittura_parolecons_errori' || $col == 'scrittura_paroleort_errori')
                continue;

            $row_medie[] = $medie->$col;
            $row_stddev[] = $stddev->$col;
        }

        $table->data[] = new html_table_row($row_medie);
        $table->data[] = new html_table_row($row_stddev);

        $htmlstringtable .= substr( $body = html_writer::table($table), strpos($body, '<tbody>'),
                strpos($body, '</tbody>') + 9);

        $mform->addElement('html', $htmlstringtable);

    }

    private function get_colored_cell($val, $media, $stddev, $outlier, $colname = '', $appendcomputed = false, $inverted = false) {

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
            'matematica_correttezza_calcolo' => 6,
            'lettura_numeri' => 9,
            'enumavanti' => 20,
            'enumindietro' => 20,
            'quantita' => 6,
            'addizioni' => 3,
            'sottrazioni' => 3,
            'confronto' => 6
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
            } else if (in_array($colname, $time_based) || strpos($colname, 'tempo') !== false) {
                if ($stddev != 0) {
                    $z = ($val - $media) / $stddev;
                    $computed = 'z=' . round($z, 2);
                } else {
                    $computed = 'z=N/A';
                }
            }
        }

        if (!is_null($computed))
            $cell->text .= ' | ' . $computed;

        $cell->attributes = ['class' => $color];
        return $cell;
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
                    $nuovorisultato = ['id' => $id, 'includi_calcolo' => $data['includi_calcolo'][$id] ?? null, 'nai' => isset($data['nai'][$id]) ? intval($data['nai'][$id]) : null];
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

    public function add_sticky_action_buttons(bool $cancel = true, ?string $submitlabel = null, array $optional_buttons = null): void {

        global $OUTPUT;
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
        $stickyhtml .= \html_writer::tag('input', '',
                [
                        'type' => 'submit',
                        'name' => 'submitbutton1',
                        'id' => 'id_submitbutton1',
                        'value' => get_string('calculate_stats_no_outlier', 'mod_coripodatacollection'),
                        'class' => 'btn btn-primary mx-1'
                ]);
        $stickyhtml .= html_writer::end_div();

    if (!is_null($optional_buttons))
        $stickyfooter = new sticky_footer($stickyhtml, null,
                ['style' => 'display: flex; justify-content: space-between !important;']);
    else
        $stickyfooter = new sticky_footer($stickyhtml);
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

