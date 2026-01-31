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
use html_table_row;
use html_writer;
use stdClass;
use tool_brickfield\local\htmlchecker\common\html_elements;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class  importstudents_form extends \moodleform {

    public function definition() {

        global $DB;
        $mform = $this->_form;


        if (isset($this->_customdata['classid'])) {
            $classid = $this->_customdata['classid'];
        }

        $this->add_sticky_action_buttons(true, 'Importa studenti selezionati');

        $sql = 'SELECT mdl_coripodatacollection_class_students.* 
                FROM mdl_coripodatacollection_class_students
                JOIN mdl_coripodatacollection_alunni ON studentid=mdl_coripodatacollection_alunni.id
                WHERE classid IN (
                    SELECT parent_classid
                    FROM mdl_coripodatacollection_classhistory
                    WHERE classid= ' . $classid . '
                )
                ORDER BY cognome ASC, nome ASC;';

        $students = $DB->get_records_sql($sql);
        $class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);
        if ($class->pluriclasse == 1) {
            $table = $this->get_pluriclass_table($students);
        } else {
            $table = $this->get_class_table($students);
        }
        $mform->addElement('html', html_writer::table($table));
    }

    /**
     * Gives as return the table that should displayed on the viewdirector page all the classes for each plex of the insititute
     */
    private function get_class_table($students) : \html_table {

        global $DB;

        $table = new \html_table();
        $table->head = [
                '',
                '',
                get_string('surname', 'mod_coripodatacollection'),
                get_string('name', 'mod_coripodatacollection')
        ];
        $table->align = ['center', 'center', 'center', 'center'];
        $i=1;
        foreach ($students as $student) {

            $info_student = $DB->get_record('coripodatacollection_alunni', ['id' => $student->studentid]);
            $table->data[] = new html_table_row([
                    html_writer::checkbox('checkbox[' . $info_student->id . ']', '', true),
                    html_writer::label($i, $info_student->id),
                    $info_student->cognome,
                    $info_student->nome
            ]);
            $i += 1;
        }
        return $table;
    }

    private function get_pluriclass_table($students) : \html_table {

        global $DB;

        $table = new \html_table();
        $table->head = [
                '',
                '',
                get_string('surname', 'mod_coripodatacollection'),
                get_string('name', 'mod_coripodatacollection'),
                get_string('freq_year', 'mod_coripodatacollection')
        ];
        $table->align = ['center', 'center', 'center', 'center', 'center'];
        $i=1;
        foreach ($students as $student) {

            $select_anno = [
                    -1 => get_string('anyone_m', 'mod_coripodatacollection'),
                    0 => get_string('first_1', 'mod_coripodatacollection'),
                    1 => get_string('second_2', 'mod_coripodatacollection'),
                    2 => get_string('third_3', 'mod_coripodatacollection'),
                    3 => get_string('fourth_4', 'mod_coripodatacollection'),
                    4 => get_string('fifth_5', 'mod_coripodatacollection')
            ];
            $anno_studente_ora = $student->annofrequentazione + 1;
            if ($anno_studente_ora >= 5 ) {
                $anno_studente_ora = -1;
            }

            $info_student = $DB->get_record('coripodatacollection_alunni', ['id' => $student->studentid]);
            $table->data[] = new html_table_row([
                    html_writer::checkbox('checkbox[' . $info_student->id . ']', '', $anno_studente_ora>=0),
                    html_writer::label($i, $info_student->id),
                    $info_student->cognome,
                    $info_student->nome,
                    html_writer::select( $select_anno,
                            'select[' . $info_student->id . ']',
                            $anno_studente_ora,
                            false)
            ]);
            $i += 1;
        }
        return $table;
    }


    function get_data(): object|array|null {

        if (!$this->is_cancelled() and $this->is_submitted() and $this->is_validated()) {
            $data = $_POST;
            $return_data = [];
            foreach ($data['checkbox'] as $studentid=>$_) {
                $studenttoimport = new stdClass();
                $studenttoimport->id = $studentid;
                if (array_key_exists('select', $data)) {
                    $studenttoimport->annofrequentazione = $data['select'][$studentid];
                }
                $return_data[] = $studenttoimport;
            }

            return $return_data;
        } else {
            return NULL;
        }
    }


    public function add_sticky_action_buttons(bool $cancel = true, ?string $submitlabel = null): void {

        global $OUTPUT;
        $mform = $this->_form;

        $stickyhtml = \html_writer::start_div();
        if ($cancel) {
            $stickyhtml .= \html_writer::tag('input', '',
                    [
                            'type' => 'submit',
                            'name' => 'cancel',
                            'id' => 'id_cancel',
                            'value' => 'Cancella',
                            'class' => 'btn btn-secondary mx-1',
                            'data-skip-validation' => 1,
                            'data-cancel' => 1,
                            'onclick' => 'skipClientValidation = true; return true;'
                    ]);
            $mform->_registerCancelButton('Cancella');
        } else {
            $stickyhtml .= \html_writer::tag('input', '',
                    [
                            'type' => 'submit',
                            'name' => 'cancel',
                            'id' => 'id_cancel',
                            'value' => 'Notifica completamento risultati',
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
        $stickyhtml .= \html_writer::end_div();

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