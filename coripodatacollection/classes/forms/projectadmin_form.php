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
use core_customfield\data;
use core_reportbuilder\local\filters\date;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class projectadmin_form extends \moodleform {

    public function definition() {

        $mform = $this->_form;

        $mform->addElement('text', 'projectname', get_string('projectname', 'mod_coripodatacollection'));
        $mform->setType('projectname', PARAM_TEXT);
        $mform->addRule('projectname', get_string('mandatoryformelement', 'mod_coripodatacollection'),
                'required');

        $mform->addElement('date_selector', 'creationdate', get_string('creationdate', 'mod_coripodatacollection'));
        $mform->setType('creationdate', PARAM_INT);
        $mform->addRule('creationdate', get_string('mandatoryformelement', 'mod_coripodatacollection'),
                'required');

        $mform->addElement('text', 'corporation', get_string('corporation', 'mod_coripodatacollection'),
                ['timezone' => get_config('moodle', 'timezone')]);
        $mform->setType('corporation', PARAM_TEXT);
        $mform->addRule('corporation', get_string('mandatoryformelement', 'mod_coripodatacollection'),
                'required');

        if (isset($this->_customdata['viewmode'])) {
            if ($this->_customdata['viewmode'] == 'viewonly') {
                $mform->hardFreezeAllVisibleExcept([]);
                $this->add_sticky_buttons( );
            } elseif ($this->_customdata['viewmode'] == 'edit') {
                $mform->addHelpButton('projectname', 'projectname', 'mod_coripodatacollection');
                $mform->addHelpButton('creationdate', 'creationdate', 'mod_coripodatacollection');
                $mform->addHelpButton('corporation', 'corporation', 'mod_coripodatacollection');

                if (!empty($this->_customdata['projectid'])) {
                    $mform->addElement('hidden', 'id', $this->_customdata['projectid']);
                    $mform->setType('id', PARAM_INT);
                }

                $this->add_sticky_action_buttons();
            }
        }

    }

    public function validation($data, $files) {

        global $DB;
        $err = [];
        $data = (object)$data;

        if ($DB->record_exists_sql('SELECT * FROM mdl_coripodatacollection_projects 
                                        WHERE projectname="' . $data->projectname .'"')) {

            if (property_exists($data, 'id')) {
                $query = 'SELECT *
                            FROM mdl_coripodatacollection_projects 
                            WHERE projectname="' . $data->projectname .'" 
                            AND id!=' . $data->id;
                if ($DB->record_exists_sql($query)){
                    $err['projectname'] = get_string('projectname_error', 'mod_coripodatacollection');
                }
            } else {
                $err['projectname'] = get_string('projectname_error', 'mod_coripodatacollection');
            }
        }
        return $err;
    }

    public function add_sticky_action_buttons(bool $cancel = true, ?string $submitlabel = null): void {

        global $OUTPUT;
        $mform = $this->_form;

        if ($submitlabel == null) {
            $submitlabel = get_string('send', 'mod_coripodatacollection');
        }

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

    /**
     *
     * Function for adding the little form at the end of the page, to use only in view mode. This buttons will allow to add a new
     * edition and to move to the page for modify the project information.
     *
     * @return void add the buttons and display the sticky footer
     * @throws \coding_exception
     */
    public function add_sticky_buttons() {

        global $OUTPUT, $DB;
        $mform = $this->_form;

        $currentyear = intval(date('Y'));
        if (date('n') >= 8) {
            $currentyear = $currentyear + 1;
        }
        $academicyears = [$currentyear - 1 . '/' . $currentyear  => $currentyear - 1 . '/' . $currentyear,
                $currentyear . '/' . ($currentyear + 1) => $currentyear . '/' . ($currentyear + 1)];
        $existingerogations = $DB->get_records('coripodatacollection_erogations', ['projectid' => $this->_customdata['id']]);
        foreach ($existingerogations as $e) {
            $index = array_search($e->academicyearedition, $academicyears);
            if ($index !== false) {
                unset($academicyears[$index]);
            }
        }

        $stickyhtml = '';

        if (!empty($academicyears)) {

            $academicyear = $mform->createElement('select', 'academicyear',
                    get_string('academicyear', 'mod_coripodatacollection'),
                    $academicyears, ['class' => 'custom-select']);
            $mform->setType('academicyear', PARAM_TEXT);
            $stickyhtml = \html_writer::start_div('', ['style' => 'display: flex; justify-content: space-between;']);
            $stickyhtml .= $academicyear->toHtml();
            $stickyhtml .= \html_writer::tag('input', '',
                    [
                            'type' => 'submit',
                            'name' => 'submitbutton',
                            'id' => 'id_submitbutton',
                            'value' => get_string('new_erogation', 'mod_coripodatacollection'),
                            'class' => 'btn btn-primary mx-1'
                    ]);
            $stickyhtml .= \html_writer::end_div();

        } else {
            $stickyhtml .= \html_writer::start_div();
            $stickyhtml .= \html_writer::end_div();
        }

        $stickyhtml .= \html_writer::start_div('', ['' =>'flex: 1; text-align: right;']);
        $stickyhtml .= \html_writer::tag('input', '',
                [
                        'type' => 'submit',
                        'name' => 'submitbutton',
                        'id' => 'id_submitbutton',
                        'value' => get_string('modify', 'mod_coripodatacollection'),
                        'class' => 'btn btn-primary mx-1'
                ]);
        $stickyhtml .= \html_writer::end_div();

        $stickyfooter = new sticky_footer($stickyhtml,' ', ['style' => 'display: flex; justify-content: space-between;']);
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


