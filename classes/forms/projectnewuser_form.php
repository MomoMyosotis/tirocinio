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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

use core\output\sticky_footer;
use core_user;

class projectnewuser_form extends \moodleform {

    public function definition() {
        global $OUTPUT;
        $mform = $this->_form;

        if (isset($this->_customdata['id'])) {

            $id = $this->_customdata['id'];

            $mform->addElement('hidden', 'id', $id);
            $mform->setType('id', PARAM_INT);

        }

        $mform->addElement('text', 'newuser_name', get_string('newuser_name', 'mod_coripodatacollection'));
        $mform->setType('newuser_name', PARAM_TEXT);
        $mform->addRule('newuser_name', get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required', '',  'client');
        $mform->addHelpButton('newuser_name', 'newuser_name', 'mod_coripodatacollection');


        $mform->addElement('text', 'newuser_surname', get_string('newuser_surname', 'mod_coripodatacollection'));
        $mform->setType('newuser_surname', PARAM_TEXT);
        $mform->addRule('newuser_surname', get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required', '',  'client');
        $mform->addHelpButton('newuser_surname', 'newuser_surname', 'mod_coripodatacollection');


        $mform->addElement('text', 'newuser_email', get_string('newuser_email', 'mod_coripodatacollection'));
        $mform->setType('newuser_email', PARAM_EMAIL);
        $mform->addRule('newuser_email', get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required', '',  'client');
        $mform->addRule('newuser_email', get_string('emailformelement', 'mod_coripodatacollection'), 'email', '',  'client');
        $mform->addHelpButton('newuser_email', 'newuser_email', 'mod_coripodatacollection');


        $mform->addElement('passwordunmask', 'newuser_password',
                get_string('newuser_password', 'mod_coripodatacollection'),
                'maxlength="'.MAX_PASSWORD_CHARACTERS.'" size="20"');
        $mform->addRule('newuser_password', get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required', '',  'client');
        $mform->addRule('newuser_password', get_string('maximumchars', '', MAX_PASSWORD_CHARACTERS),
                'maxlength', MAX_PASSWORD_CHARACTERS, 'client');
        $mform->setType('newuser_password', core_user::get_property_type('password'));
        $mform->addHelpButton('newuser_password', 'newuser_password', 'mod_coripodatacollection');


        $this->add_sticky_action_buttons(true, get_string('save', 'mod_coripodatacollection'));
    }


    /**
     * Validate the form data checking if elements are already present in the database.
     * @param array $usernew
     * @param array $files
     * @return array|bool
     */
    public function validation($usernew, $files) {

        global $CFG, $DB;

        $usernew = (object)$usernew;
        $err = [];

        $registeredusers = $DB->get_records('user');
        foreach ($registeredusers as $ru) {

            if ($ru->email == $usernew->newuser_email) {
                $err['newuser_email'] = 'Email già presente';
            }

        }

        if (!empty($usernew->newuser_password)) {
            if (!check_password_policy($usernew->newuser_password, $errmsg, $usernew)) {
                $err['newuser_password'] = $errmsg;
            }
        }

        return $err;
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


