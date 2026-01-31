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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class instituteplexes_form extends \moodleform {

    public function definition() {

        $mform = $this->_form;

        if (isset($this->_customdata['instituteid'])) {

            $instituteid = $this->_customdata['instituteid'];

            $mform->addElement('hidden', 'instituteid', $instituteid);
            $mform->setType('instituteid', PARAM_INT);

        }

        $mform->addElement('text', 'denominazioneplesso', get_string('denominazioneplesso', 'mod_coripodatacollection'));
        $mform->setType('denominazioneplesso', PARAM_TEXT);
        $mform->addRule('denominazioneplesso', get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required');
        $mform->addHelpButton('denominazioneplesso', 'denominazioneplesso', 'mod_coripodatacollection');

        $mform->addElement('text', 'indirizzo', get_string('indirizzoplesso', 'mod_coripodatacollection'));
        $mform->setType('indirizzo', PARAM_ALPHANUMEXT);
        $mform->addRule('indirizzo', get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required');
        $mform->addHelpButton('indirizzo', 'indirizzoplesso', 'mod_coripodatacollection');

        $this->add_sticky_action_buttons(true, get_string('submit_newplesso', 'mod_coripodatacollection'));

    }

    public function validation($data, $files) {

        global $DB;
        $err = [];
        $data = (object)$data;

        if ($DB->record_exists_sql('SELECT * FROM mdl_coripodatacollection_plessi
                                        WHERE denominazioneplesso="' . $data->denominazioneplesso .'"
                                        AND instituteid=' . $data->instituteid)) {
            $err['denominazioneplesso'] = get_string('denominazioneplesso_error', 'mod_coripodatacollection');
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


