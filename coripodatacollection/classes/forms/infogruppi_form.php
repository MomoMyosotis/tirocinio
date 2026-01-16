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
use core_table\local\filter\string_filter;
use html_table_row;
use html_writer;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class  infogruppi_form extends \moodleform {

    public function definition() {

        global $DB;
        $mform = $this->_form;

        $erogationid = $this->_customdata['erogationid'];
        $erogation = $DB->get_record('coripodatacollection_erogations', ['id' => $erogationid]);

        $gruppo_studenti_colori = $DB->get_records_sql('
                    SELECT DISTINCT alunni_gruppo.studentid, gruppi.zona, indici.valutazione_globale
                    FROM mdl_coripodatacollection_gruppi as gruppi
                    JOIN mdl_coripodatacollection_alunni_gruppo as alunni_gruppo on alunni_gruppo.groupid = gruppi.id
                    JOIN mdl_coripodatacollection_indici_valutazione as indici on alunni_gruppo.studentid = indici.alunno
                    WHERE gruppi.erogationid = ' . $erogationid
        );

        $table_global = new \html_table();
        $table_global->head = [
                '',
                get_string('dark_green', 'mod_coripodatacollection'),
                get_string('yellow', 'mod_coripodatacollection'),
                get_string('red', 'mod_coripodatacollection')
        ];
        $table_global->align = ['center', 'center', 'center', 'center'];

        $table_global->data[] = [
            get_string('north', 'mod_coripodatacollection'),
            count(array_filter($gruppo_studenti_colori, fn($std_color) =>
                    $std_color->zona == get_string('north', 'mod_coripodatacollection') &&
                    $std_color->valutazione_globale == get_string('dark_green', 'mod_coripodatacollection'),
            )),
            count(array_filter($gruppo_studenti_colori, fn($std_color) =>
                    $std_color->zona == get_string('north', 'mod_coripodatacollection') &&
                    $std_color->valutazione_globale == get_string('yellow', 'mod_coripodatacollection'),
            )),
            count(array_filter($gruppo_studenti_colori, fn($std_color) =>
                    $std_color->zona == get_string('north', 'mod_coripodatacollection') &&
                    $std_color->valutazione_globale == get_string('red', 'mod_coripodatacollection'),
            )),
        ];

        $table_global->data[] = [
                get_string('south', 'mod_coripodatacollection'),
                count(array_filter($gruppo_studenti_colori, fn($std_color) =>
                        $std_color->zona == get_string('south', 'mod_coripodatacollection') &&
                        $std_color->valutazione_globale == get_string('dark_green', 'mod_coripodatacollection'),
                )),
                count(array_filter($gruppo_studenti_colori, fn($std_color) =>
                        $std_color->zona == get_string('south', 'mod_coripodatacollection') &&
                        $std_color->valutazione_globale == get_string('yellow', 'mod_coripodatacollection'),
                )),
                count(array_filter($gruppo_studenti_colori, fn($std_color) =>
                        $std_color->zona == get_string('south', 'mod_coripodatacollection') &&
                        $std_color->valutazione_globale == get_string('red', 'mod_coripodatacollection'),
                )),
        ];

        $table_global->data[] = [
                get_string('east', 'mod_coripodatacollection'),
                count(array_filter($gruppo_studenti_colori, fn($std_color) =>
                        $std_color->zona == get_string('east', 'mod_coripodatacollection') &&
                        $std_color->valutazione_globale == get_string('dark_green', 'mod_coripodatacollection'),
                )),
                count(array_filter($gruppo_studenti_colori, fn($std_color) =>
                        $std_color->zona == get_string('east', 'mod_coripodatacollection') &&
                        $std_color->valutazione_globale == get_string('yellow', 'mod_coripodatacollection'),
                )),
                count(array_filter($gruppo_studenti_colori, fn($std_color) =>
                        $std_color->zona == get_string('east', 'mod_coripodatacollection') &&
                        $std_color->valutazione_globale == get_string('red', 'mod_coripodatacollection'),
                )),
        ];

        $table_global->data[] = [
                get_string('west', 'mod_coripodatacollection'),
                count(array_filter($gruppo_studenti_colori, fn($std_color) =>
                        $std_color->zona == get_string('west', 'mod_coripodatacollection') &&
                        $std_color->valutazione_globale == get_string('dark_green', 'mod_coripodatacollection'),
                )),
                count(array_filter($gruppo_studenti_colori, fn($std_color) =>
                        $std_color->zona == get_string('west', 'mod_coripodatacollection') &&
                        $std_color->valutazione_globale == get_string('yellow', 'mod_coripodatacollection'),
                )),
                count(array_filter($gruppo_studenti_colori, fn($std_color) =>
                        $std_color->zona == get_string('west', 'mod_coripodatacollection') &&
                        $std_color->valutazione_globale == get_string('red', 'mod_coripodatacollection'),
                )),
        ];


        $gruppi = $DB->get_records('coripodatacollection_gruppi', ['erogationid' => $erogationid]);

        $completed_group_table = new \html_table();
        $completed_group_table->head = [
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
        ];
        $completed_group_table->align = ['center', 'center', 'center', 'center', 'center', 'center', 'center', 'center',
                'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center'];

        $closed_group_table = new \html_table();
        $closed_group_table->head = $completed_group_table->head;
        $closed_group_table->align = $completed_group_table->align;

        $definitive_group_table = new \html_table();
        $definitive_group_table->head = $completed_group_table->head;
        $definitive_group_table->align = $completed_group_table->align;

        foreach ($gruppi as $gruppo) {

            $group_students = $DB->get_records_sql('
                SELECT DISTINCT alunni.* 
                FROM mdl_coripodatacollection_alunni_gruppo as alunni_gruppo
                JOIN mdl_coripodatacollection_alunni as alunni on alunni.id = alunni_gruppo.studentid
                WHERE alunni_gruppo.groupid =  ' . $gruppo->id);
            $group_students = array_values($group_students);

            $table_row = [
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
            ];

            if ($gruppo->completato == 1 && $gruppo->chiuso != 1 && $gruppo->definitivo != 1)
                $completed_group_table->data[] = $table_row;
            if ($gruppo->completato == 1 && $gruppo->chiuso == 1 && $gruppo->definitivo != 1)
                $closed_group_table->data[] = $table_row;
            if ($gruppo->completato == 1 && $gruppo->chiuso == 1 && $gruppo->definitivo == 1)
                $definitive_group_table->data[] = $table_row;
        }

        $noinputimg = 'pix/noentries_zero_state.svg';
        $html_string = html_writer::start_div('text-xs-center text-center mt-4',
                ['style' => ' display: flex; align-items: center; 
                                justify-content: center; gap: 20px; margin: 20px auto']);
        $html_string .= html_writer::img(
                $noinputimg,
                get_string('no_centers', 'mod_coripodatacollection'),
                ['style' => 'width: 100px; height: auto;']);
        $html_string .= html_writer::tag(
                'h5',
                get_string('no_groups', 'mod_coripodatacollection'),
                ['class' => 'h5 mt-3 mb-0']);
        $html_string .= html_writer::end_div();

        $mform->addElement('header', 'header1',
                get_string('global_group_view', 'mod_coripodatacollection'));
        $mform->addElement('html', html_writer::table($table_global));

        $mform->addElement('header', 'header2',
                get_string('completed_groups', 'mod_coripodatacollection'));
        if (!empty($completed_group_table->data))
            $mform->addElement('html', html_writer::table($completed_group_table));
        else
            $mform->addElement('html', $html_string);

        $mform->addElement('header', 'header3',
                get_string('closed_groups', 'mod_coripodatacollection'));
        if (!empty($closed_group_table->data))
            $mform->addElement('html', html_writer::table($closed_group_table));
        else
            $mform->addElement('html', $html_string);

        $mform->addElement('header', 'header4',
                get_string('definitive_groups', 'mod_coripodatacollection'));
        if (!empty($definitive_group_table->data))
            $mform->addElement('html', html_writer::table($definitive_group_table));
        else
            $mform->addElement('html', $html_string);

    }
}