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

class projectnewinstitute_form extends \moodleform {

    public function definition() {
        global $OUTPUT;
        $mform = $this->_form;

        if (isset($this->_customdata['id'])) {

            $id = $this->_customdata['id'];

            $mform->addElement('hidden', 'id', $id);
            $mform->setType('id', PARAM_INT);

        }

        $mform->addElement('text', 'newinstitute_name',
                get_string('newinstitute_name', 'mod_coripodatacollection'));
        $mform->setType('newinstitute_name', PARAM_TEXT);
        $mform->addRule('newinstitute_name',
                get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required', '',  'client');
        $mform->addHelpButton('newinstitute_name', 'newinstitute_name', 'mod_coripodatacollection');

        $mform->addElement('checkbox', 'schoolonly',
                get_string('single_school', 'mod_coripodatacollection'), ' ');
        $mform->setType('schoolonly', PARAM_INT);
        $mform->addHelpButton('schoolonly', 'single_school', 'mod_coripodatacollection');

        $zone = [
                '' => '',
                get_string('north', 'mod_coripodatacollection') =>
                        get_string('north', 'mod_coripodatacollection'),
                get_string('south', 'mod_coripodatacollection') =>
                        get_string('south', 'mod_coripodatacollection'),
                get_string('east', 'mod_coripodatacollection') =>
                        get_string('east', 'mod_coripodatacollection'),
                get_string('west', 'mod_coripodatacollection') =>
                        get_string('west', 'mod_coripodatacollection')
        ];
        $mform->addElement('select', 'zona',
                get_string('center_zone', 'mod_coripodatacollection'), $zone);
        $mform->setType('zona', PARAM_TEXT);
        $mform->addRule('zona',
                get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required', '',  'client');


        $mform->addElement('text', 'newinstitute_manager_name',
                get_string('newinstitute_manager_name', 'mod_coripodatacollection'));
        $mform->setType('newinstitute_manager_name', PARAM_TEXT);
        $mform->addRule('newinstitute_manager_name',
                get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required', '',  'client');
        $mform->addHelpButton(
                'newinstitute_manager_name',
                'newinstitute_manager_name',
                'mod_coripodatacollection'
        );

        $mform->addElement('text', 'newinstitute_manager_surname',
                get_string('newinstitute_manager_surname', 'mod_coripodatacollection'));
        $mform->setType('newinstitute_manager_surname', PARAM_TEXT);
        $mform->addRule('newinstitute_manager_surname',
                get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required', '',  'client');
        $mform->addHelpButton(
                'newinstitute_manager_surname',
                'newinstitute_manager_surname',
                'mod_coripodatacollection');

        $mform->addElement('text', 'newinstitute_manager_email',
                get_string('newinstitute_manager_email', 'mod_coripodatacollection'));
        $mform->setType('newinstitute_manager_email', PARAM_EMAIL);
        $mform->addRule('newinstitute_manager_email',
                get_string('mandatoryformelement', 'mod_coripodatacollection'),
                'required', '',  'client');
        $mform->addRule('newinstitute_manager_email',
                get_string('emailformelement', 'mod_coripodatacollection'),
                'email', '',  'client');
        $mform->addHelpButton(
                'newinstitute_manager_email',
                'newinstitute_manager_email',
                'mod_coripodatacollection');

        $mform->addElement('passwordunmask', 'newinstitute_manager_password',
                get_string('newinstitute_manager_password', 'mod_coripodatacollection'),
                'maxlength="'.MAX_PASSWORD_CHARACTERS.'" size="20"');
        $mform->addRule('newinstitute_manager_password',
                get_string('mandatoryformelement', 'mod_coripodatacollection'),
                'required', '',  'client');
        $mform->addRule('newinstitute_manager_password',
                get_string('maximumchars', '', MAX_PASSWORD_CHARACTERS),
                'maxlength', MAX_PASSWORD_CHARACTERS, 'client');
        $mform->setType('newinstitute_manager_password', core_user::get_property_type('password'));
        $mform->addHelpButton(
                'newinstitute_manager_password',
                'newinstitute_manager_password',
                'mod_coripodatacollection');

        $mform->addElement('header', 'header1',
                get_string('direcotor_form', 'mod_coripodatacollection'));
        $mform->addElement('text', 'nome_direttore',
                get_string('director_name', 'mod_coripodatacollection'));
        $mform->setType('nome_direttore', PARAM_TEXT);
        $mform->addRule('nome_direttore',
                get_string('mandatoryformelement', 'mod_coripodatacollection'),
                'required', '',  'client');

        $mform->addElement('text', 'cognome_direttore',
                get_string('director_surname', 'mod_coripodatacollection'));
        $mform->setType('cognome_direttore', PARAM_TEXT);
        $mform->addRule('cognome_direttore',
                get_string('mandatoryformelement', 'mod_coripodatacollection'),
                'required', '',  'client');


        $mform->addElement('text', 'email_direttore',
                get_string('director_email', 'mod_coripodatacollection'));
        $mform->setType('email_direttore', PARAM_TEXT);
        $mform->addRule('email_direttore',
                get_string('mandatoryformelement', 'mod_coripodatacollection'),
                'required', '',  'client');
        $mform->addRule('email_direttore',
                get_string('emailformelement', 'mod_coripodatacollection'),
                'email', '',  'client');

        $mform->addElement('header', 'header2',
                get_string('dsga_form', 'mod_coripodatacollection'));
        $mform->addElement('text', 'nome_dsga',
                get_string('dsga_name', 'mod_coripodatacollection'));
        $mform->setType('nome_dsga', PARAM_TEXT);
        $mform->addRule('nome_dsga',
                get_string('mandatoryformelement', 'mod_coripodatacollection'),
                'required', '',  'client');

        $mform->addElement('text', 'cognome_dsga',
                get_string('dsga_surname', 'mod_coripodatacollection'));
        $mform->setType('cognome_dsga', PARAM_TEXT);
        $mform->addRule('cognome_dsga',
                get_string('mandatoryformelement', 'mod_coripodatacollection'),
                'required', '',  'client');

        $mform->addElement('text', 'email_dsga',
                get_string('dsga_email', 'mod_coripodatacollection'));
        $mform->setType('email_dsga', PARAM_TEXT);
        $mform->addRule('email_dsga',
                get_string('mandatoryformelement', 'mod_coripodatacollection'),
                'required', '',  'client');
        $mform->addRule('email_dsga',
                get_string('emailformelement', 'mod_coripodatacollection'),
                'email', '',  'client');

        $mform->addHelpButton('nome_direttore', 'director_name', 'mod_coripodatacollection');
        $mform->addHelpButton('cognome_direttore', 'director_surname', 'mod_coripodatacollection');
        $mform->addHelpButton('email_direttore', 'director_email', 'mod_coripodatacollection');
        $mform->addHelpButton('nome_dsga', 'dsga_name', 'mod_coripodatacollection');
        $mform->addHelpButton('cognome_dsga', 'dsga_surname', 'mod_coripodatacollection');
        $mform->addHelpButton('email_dsga', 'dsga_email', 'mod_coripodatacollection');


        $this->add_sticky_action_buttons(true, get_string('save_institute', 'mod_coripodatacollection'));
    }


    /**
     * Validate the form data checking if elements are already present in the database.
     * @param array $data
     * @param array $files
     * @return array|bool
     */
    public function validation($data, $files) {

        global $CFG, $DB;

        $data = (object)$data;
        $err = [];

        $registeredusers = $DB->get_records('user');
        foreach ($registeredusers as $ru) {

            if ($ru->email == $data->newinstitute_manager_email) {
                $err['newinstitute_manager_email'] = 'Email già presente';
            }

        }

        $registeredinstitutes = $DB->get_records('coripodatacollection_istituti');
        foreach ($registeredinstitutes as $ri) {

            if ($ri->denominazioneistituto == $data->newinstitute_name) {
                $err['newinstitute_name'] = 'Istituto già presente';
            }

        }

        if (!empty($data->newinstitute_manager_password)) {
            if (!check_password_policy($data->newinstitute_manager_password, $errmsg, $data)) {
                $err['newinstitute_manager_password'] = $errmsg;
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


