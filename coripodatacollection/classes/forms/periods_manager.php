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

class periods_manager extends \moodleform {

    protected function definition() {

        $mform = $this->_form;
        $this->add_sticky_action_buttons(true, get_string('save', 'mod_coripodatacollection'));

        $mform->addElement('header', 'censimento',
                get_string('census_of_period', 'mod_coripodatacollection'));
        $mform->addElement('date_time_selector', 'start_censimento',
                get_string('start_census', 'mod_coripodatacollection'));
        $mform->addHelpButton('start_censimento','start_census', 'mod_coripodatacollection');
        $mform->addElement('date_time_selector', 'end_censimento',
                get_string('end_census', 'mod_coripodatacollection'));
        $mform->addHelpButton('end_censimento','end_census', 'mod_coripodatacollection');

        $mform->addElement('header', 'pre-rinforzo',
                get_string('prereinforce_period', 'mod_coripodatacollection'));
        $mform->addElement('date_time_selector', 'start_val_pre',
                get_string('start_reg_val_pre', 'mod_coripodatacollection'));
        $mform->addHelpButton('start_val_pre','start_reg_val_pre', 'mod_coripodatacollection');
        $mform->addElement('date_time_selector', 'end_val_pre',
                get_string('end_reg_val_pre', 'mod_coripodatacollection'));
        $mform->addHelpButton('end_val_pre','end_reg_val_pre', 'mod_coripodatacollection');

        $mform->addElement('header', 'post-rinforzo',
                get_string('postreinforce_period', 'mod_coripodatacollection'));
        $mform->addElement('date_time_selector', 'start_val_post',
                get_string('start_reg_val_post', 'mod_coripodatacollection'));
        $mform->addHelpButton('start_val_post','start_reg_val_post', 'mod_coripodatacollection');
        $mform->addElement('date_time_selector', 'end_val_post',
                get_string('end_reg_val_post', 'mod_coripodatacollection'));
        $mform->addHelpButton('end_val_post','end_reg_val_post', 'mod_coripodatacollection');

        $mform->setExpanded('censimento');
        $mform->setExpanded('pre-rinforzo');
        $mform->setExpanded('post-rinforzo');

    }

    public function freeze_all() {
        $mform = $this->_form;
        $mform->hardFreezeAllVisibleExcept([]);
    }

    public function validation($data, $files) {

        $data = (object)$data;
        $err = [];

        if ($data->start_censimento >= $data->end_censimento) {
            $err['start_censimento'] = 'Intervallo non valido';
        }
        if ($data->end_censimento >= $data->start_val_pre) {
            $err['end_censimento'] = 'Intervallo non valido: il censimento deve terminare prima dell\'inizio della fase di 
                                        registrazione dei risultati pre-rinforzo.';
            $err['start_val_pre'] = 'Intervallo non valido: la fase di registrazione dei risultati pre-rinforzo deve iniziare dopo
                                        la chiusura della fase di censimento';
        }
        if ($data->start_val_pre >= $data->end_val_pre) {
            $err['start_val_pre'] = 'Intervallo non valido';
        }
        if ($data->end_val_pre >= $data->start_val_post) {
            $err['end_val_pre'] = 'Intervallo non valido: la fase di registrazione dei risultati pre-rinforzo deve terminare prima 
                                        dell\'inizio della fase di  registrazione dei risultati post-rinforzo.';
            $err['start_val_post'] = 'Intervallo non valido: la fase di registrazione dei risultati post-rinforzo deve iniziare dopo
                                        la chiusura la fase di registrazione dei risultati pre-rinforzo';
        }
        if ($data->start_val_post >= $data->end_val_post) {
            $err['start_val_post'] = 'Intervallo non valido';
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
                            'value' => 'Cancella',
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
