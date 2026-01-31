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
use core_table\local\filter\string_filter;
use html_table_row;
use html_writer;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class  importclasses_form extends \moodleform {

    public function definition() {

        global $DB;
        $mform = $this->_form;


        if (isset($this->_customdata['courseid'])) {
            $courseid = $this->_customdata['courseid'];
        }

        $this->add_sticky_action_buttons(true,
                get_string('import_selected_classes', 'mod_coripodatacollection'));

        $istituteid = $this->_customdata['instituteid'];
        $plessi = $this->get_institute_plexes($courseid, $istituteid);
        $erogazione = $DB->get_record('coripodatacollection_erogations', ['courseid' => $courseid]);

        if (empty($plessi)) {
            $noinputimg = 'pix/noentries_zero_state.svg';
            echo html_writer::start_div('text-xs-center text-center mt-4');
            echo html_writer::img($noinputimg, get_string('no_registered_plex', 'mod_coripodatacollection'),
                    ['style' => 'display: block; margin: 0 auto;']);
            echo html_writer::tag('h5', get_string('no_registered_plex', 'mod_coripodatacollection'),
                    ['class' => 'h5 mt-3 mb-0']);
            echo html_writer::end_div();
        }

        foreach ($plessi as $plesso) {

            $classes = $DB->get_records('coripodatacollection_classes',
                    ['plesso' => $plesso->idplesso, 'erogazione' => $erogazione->id]);

            $mform->addElement('header',
                    'header' . $plesso->idplesso, $plesso->denominazioneistituto . ' -- ' . $plesso->denominazioneplesso);
            $table = $this->get_institute_table($classes, $istituteid, $erogazione->id);
            $mform->addElement('html', html_writer::table($table));

        }
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
     * Gives as return the table that should displayed on the viewdirector page all the classes for each plex of the insititute
     */
    private function get_institute_table($classes, $istituteid, $erogazioneid) : \html_table {

        global $DB;

        $table = new \html_table();
        $table->head = [
                '',
                get_string('year', 'mod_coripodatacollection'),
                get_string('denomination', 'mod_coripodatacollection'),
                get_string('year_after_import', 'mod_coripodatacollection'),
                get_string('refering_teacher', 'mod_coripodatacollection'),
                get_string('class_union_with', 'mod_coripodatacollection')
        ];
        $table->align = ['center', 'center', 'center', 'center', 'center', 'center'];

        foreach ($classes as $class) {

            if ($class->anno == 5) {
                continue;
            }
            if ($DB->record_exists('coripodatacollection_classhistory', ['parent_classid' => $class->id])) {
                continue;
            }

            $insegnanti = $DB->get_records_sql('select distinct userid, firstname, lastname
                                                from {user}  join {coripodatacollection_teachers} on userid={user}.id
                                                where instituteid = ' . $istituteid);
            $areanames_insegnanti = [];
            foreach ($insegnanti as $i) {
                $areanames_insegnanti[$i->userid] = $i->lastname . ' ' . $i->firstname;
            }

            $istitute_classes = $DB->get_records('coripodatacollection_classes',
                    ['istituto' => $istituteid, 'erogazione' => $erogazioneid]);
            $areanames_classes = [ 0 => get_string('anyone', 'mod_coripodatacollection')];
            foreach ($istitute_classes as $ic) {
                if ($ic->id == $class->id){
                    continue;
                }
                if ($DB->record_exists('coripodatacollection_classhistory',
                        ['parent_classid' => $ic->id, 'parent_deleted' => 1])) {
                    continue;
                }
                if (($ic->pluriclasse == 1 and $class->pluricalsse == 1) or $class->anno == $ic->anno) {
                    if ($DB->record_exists('coripodatacollection_plessi', ['id' => $ic->plesso])) {
                        $plesso = $DB->get_record('coripodatacollection_plessi', ['id' => $ic->plesso]);
                        $areanames_classes[$ic->id] = $ic->anno . ' ' . $ic->classe . '-' . $plesso->denominazioneplesso;
                    }
                }
            }

            $insegnante = $DB->get_record('coripodatacollection_classadmin', ['classid' => $class->id]);
            $insegnante = $DB->get_record('user', ['id' => $insegnante->userid]);

            $label_anno = [
                    0 => get_string('first_1', 'mod_coripodatacollection'),
                    1 => get_string('second_2', 'mod_coripodatacollection'),
                    2 => get_string('third_3', 'mod_coripodatacollection'),
                    3 => get_string('fourth_4', 'mod_coripodatacollection'),
                    4 => get_string('fifth_5', 'mod_coripodatacollection')
            ];
            if ($class->pluriclasse == 1) {
                $table->data[] = new html_table_row([
                        html_writer::checkbox('checkbox[' . $class->id . ']', '', true),
                        get_string('pluri_class', 'mod_coripodatacollection'),
                        $class->classe,
                        get_string('pluri_class', 'mod_coripodatacollection'),
                        html_writer::select($areanames_insegnanti, 'teacher[' . $class->id . ']', $insegnante->id, false),
                        html_writer::select($areanames_classes, 'union[' . $class->id . ']', 0, false),
                ]);
            } else {
                $table->data[] = new html_table_row([
                        html_writer::checkbox('checkbox[' . $class->id . ']', '', true),
                        $label_anno[$class->anno - 1],
                        $class->classe,
                        $label_anno[$class->anno],
                        html_writer::select($areanames_insegnanti, 'teacher[' . $class->id . ']', $insegnante->id, false),
                        html_writer::select($areanames_classes, 'union[' . $class->id . ']', 0, false),
                ]);
            }
        }
        return $table;
    }


    function get_data(): object|array|null {

        if (!$this->is_cancelled() and $this->is_submitted() and $this->is_validated()) {
            $data = $_POST;
            //unset($data['sesskey']); // we do not need to return sesskey
            //unset($data['_qf__'.$this->_formname]);   // we do not need the submission marker too
            //unset($data['id']);
            //unset($data['page']);
            $return_data = [];
            foreach ($data['checkbox'] as $classid=>$_) {
                $classtoimport = new stdClass();
                $classtoimport->id = $classid;
                $classtoimport->teacher = $data['teacher'][$classid];
                if(array_key_exists($classid, $data['union'])) {
                    $classtoimport->union = $data['union'][$classid];
                } else {
                    $classtoimport->union = 0;
                }
                $classtoimport->inserita = false;
                $return_data[$classid] = $classtoimport;
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
                            'value' => get_string('cancel', 'mod_coripodatacollection'),
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
                            'value' => get_string('notify_results', 'mod-coripodatacollection'),
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