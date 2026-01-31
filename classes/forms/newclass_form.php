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
use PhpParser\Node\Param;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class newclass_form extends \moodleform {

    public function definition() {

        global $DB;

        $mform = $this->_form;

        if (isset($this->_customdata['instituteid'])) {

            $instituteid = $this->_customdata['instituteid'];

            $mform->addElement('hidden', 'istituto', $instituteid);
            $mform->setType('istituto', PARAM_INT);

        }

        if (isset($this->_customdata['courseid'])) {
            $erogation = $DB->get_record('coripodatacollection_erogations',
                    ['courseid' => $this->_customdata['courseid']]);

            $mform->addElement('hidden', 'erogazione', $erogation->id);
            $mform->setType('erogazione', PARAM_INT);
        }

        $mform->addElement('text', 'classe' ,
                get_string('classdenomination', 'mod_coripodatacollection'));
        $mform->setType('classe', PARAM_TEXT);
        $mform->addRule('classe', get_string('mandatoryformelement', 'mod_coripodatacollection'),
                'required');
        $mform->addHelpButton('classe', 'classdenomination', 'mod_coripodatacollection');

        $mform->addElement('select', 'anno', get_string('class_year', 'mod_coripodatacollection'), [
                    1 => get_string('first_1', 'mod_coripodatacollection'),
                    2 => get_string('second_2', 'mod_coripodatacollection'),
                    3 => get_string('third_3', 'mod_coripodatacollection'),
                    4 => get_string('fourth_4', 'mod_coripodatacollection'),
                    5 => get_string('fifth_5', 'mod_coripodatacollection')
                ]);
        $mform->setType('anno', PARAM_INT);
        $mform->addHelpButton('anno', 'class_year', 'mod_coripodatacollection');

        $insegnanti = $DB->get_records_sql('select distinct userid, firstname, lastname
                                                from {user}  join {coripodatacollection_teachers} on userid={user}.id
                                                where instituteid = ' . $instituteid);
        $areanames = [];
        foreach ($insegnanti as $i) {
            $areanames[$i->userid] = $i->lastname . ' ' . $i->firstname;
        }
        $options = [
                'multiple' => false,
                'noselectionstring' => get_string('allareas', 'search'),
        ];
        $mform->addElement('autocomplete', 'insegnante',
                get_string('searchinsegnante_name', 'mod_coripodatacollection'), $areanames, $options);
        $mform->addHelpButton('insegnante', 'searchinsegnante_name', 'mod_coripodatacollection');

        $plessi = $DB->get_records('coripodatacollection_plessi', ['instituteid' => $instituteid]);
        $areanames = [];
        foreach ($plessi as $p) {
            $areanames[$p->id] = $p->denominazioneplesso;
        }
        $options = [
                'multiple' => false,
                'noselectionstring' => get_string('allareas', 'search'),
        ];
        $mform->addElement('autocomplete', 'plesso',
                get_string('searchplesso_name', 'mod_coripodatacollection'), $areanames, $options);
        $mform->addHelpButton('plesso', 'searchplesso_name', 'mod_coripodatacollection');

        $mform->addElement('text', 'numerostudenti',
                get_string('numberstudents', 'mod_coripodatacollection'),
                ['type' => 'number']);
        $mform->setType('numerostudenti', PARAM_INT);
        $mform->addRule('numerostudenti',
                get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required');
        $mform->addHelpButton('numerostudenti', 'numberstudents', 'mod_coripodatacollection');

        $mform->addElement('advcheckbox', 'pluriclasse',
                get_string('pluri_class', 'mod_coripodatacollection'), ' ');
        $mform->setType('pluriclasse', PARAM_INT);
        $mform->addHelpButton('pluriclasse', 'pluri_class', 'mod_coripodatacollection');
        $mform->hideIf('anno', 'pluriclasse', 'checked');


        if (isset($this->_customdata['modifyclass'])) {

            $classid = $this->_customdata['modifyclass'];
            $class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);
            $insegnante = $DB->get_record('coripodatacollection_classadmin', ['classid' => $class->id]);

            if (isset($this->_customdata['courseid'])) {
                $erogation = $DB->get_record('coripodatacollection_erogations',
                        ['courseid' => $this->_customdata['courseid']]);
                $current_date = time();
                $post_censimento = $erogation->end_censimento <= $current_date && $class->can_edit_censimento == 0;
                if ($post_censimento) {
                    $mform->hardFreezeAllVisibleExcept(['insegnante']);
                }
            }



            $mform->addElement('hidden', 'classid', $classid);
            $mform->setType('classid', PARAM_INT);

            $mform->addElement('hidden', 'confermato', $class->confermato);
            $mform->setType('confermato', PARAM_INT);
            $mform->setDefault('classe', $class->classe);
            $mform->setDefault('anno', $class->anno);
            $mform->setDefault('insegnante', $insegnante->userid);
            $mform->setDefault('plesso', $class->plesso);
            $mform->setDefault('numerostudenti', $class->numerostudenti);
            $mform->setDefault('pluriclasse', $class->pluriclasse);

            $this->add_sticky_action_buttons(true, get_string('modclass', 'mod_coripodatacollection'));
        } else {
            $this->add_sticky_action_buttons(true, get_string('submit_newclass', 'mod_coripodatacollection'));

        }

    }

    public function validation($data, $files) {

        global $DB;

        $data = (object)$data;
        $err = [];

        $existingclasse = $DB->get_records('coripodatacollection_classes',
                ['istituto' => $data->istituto, 'erogazione' => $data->erogazione]);
        foreach ($existingclasse as $e) {

            if (empty($data->classid)){
                if ($e->classe == $data->classe && $e->plesso == $data->plesso && $e->anno == $data->anno) {
                    $err['classe'] = get_string('classalreadyexist', 'mod_coripodatacollection');
                    break;
                }
            } else {
                if ($e->classe == $data->classe && $e->plesso == $data->plesso
                        && $e->anno == $data->anno && $e->id != $data->classid) {
                    $err['classe'] = get_string('classalreadyexist', 'mod_coripodatacollection');
                    break;
                }
            }

        }

        return $err;
    }

    public function display() {

        global $PAGE;

        parent::display();
        $PAGE->requires->js_init_code("
            document.getElementsByName('numerostudenti').forEach(text =>{
                text.setAttribute('type', 'number');
                text.setAttribute('min', '1');
            });
        ");
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


