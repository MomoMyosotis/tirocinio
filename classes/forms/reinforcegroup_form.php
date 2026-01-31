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
use html_writer;
use moodle_url;
use PhpParser\Node\Param;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class reinforcegroup_form extends \moodleform {

    public function definition() {

        global $DB;

        $mform = $this->_form;
        $mform->addElement('text', 'codice' ,
                get_string('group_code', 'mod_coripodatacollection'));
        $mform->setType('codice', PARAM_TEXT);
        $mform->addRule('codice', get_string('mandatoryformelement', 'mod_coripodatacollection'),
                'required');

        $mform->addElement('text', 'sede' ,
                get_string('sede', 'mod_coripodatacollection'));
        $mform->setType('sede', PARAM_TEXT);
        $mform->addRule('sede', get_string('mandatoryformelement', 'mod_coripodatacollection'),
                'required');

        $mform->addElement('text', 'dettaglio_sede' ,
                get_string('dettaglio_sede', 'mod_coripodatacollection'));
        $mform->setType('dettaglio_sede', PARAM_TEXT);
        $mform->addRule('dettaglio_sede', get_string('mandatoryformelement', 'mod_coripodatacollection'),
                'required');

        $mform->addElement('text', 'indirizzo' ,
                get_string('center_address', 'mod_coripodatacollection'));
        $mform->setType('indirizzo', PARAM_TEXT);
        $mform->addRule('indirizzo', get_string('mandatoryformelement', 'mod_coripodatacollection'),
                'required');

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


        $mform->addElement('text', 'aula' ,
                get_string('aula', 'mod_coripodatacollection'));
        $mform->setType('aula', PARAM_TEXT);
        $mform->addRule('aula', get_string('mandatoryformelement', 'mod_coripodatacollection'),
                'required');

        $weekdays = [
                get_string('monday', 'mod_coripodatacollection') => get_string('monday', 'mod_coripodatacollection'),
                get_string('tuesday', 'mod_coripodatacollection') => get_string('tuesday', 'mod_coripodatacollection'),
                get_string('wednesday', 'mod_coripodatacollection') => get_string('wednesday', 'mod_coripodatacollection'),
                get_string('thursday', 'mod_coripodatacollection') => get_string('thursday', 'mod_coripodatacollection'),
                get_string('friday', 'mod_coripodatacollection') => get_string('friday', 'mod_coripodatacollection'),
                get_string('saturday', 'mod_coripodatacollection') => get_string('saturday', 'mod_coripodatacollection'),
                get_string('sunday', 'mod_coripodatacollection') => get_string('sunday', 'mod_coripodatacollection'),
        ];

        $orari = [];
        for ($h = 0; $h < 24; $h++) {
            for ($m = 0; $m < 60; $m += 5) {
                $orari[sprintf("%02d:%02d", $h, $m)] = sprintf("%02d:%02d", $h, $m);
            }
        }

        $mform->addElement('select', 'giorno1' ,
                get_string('day1', 'mod_coripodatacollection'), $weekdays);
        $mform->setType('giorno1', PARAM_TEXT);
        $mform->addRule('giorno1', get_string('mandatoryformelement', 'mod_coripodatacollection'),
                'required');

        $mform->addElement('select', 'orario1',
                get_string('orario1', 'mod_coripodatacollection'), $orari);
        $mform->setType('orario1', PARAM_TEXT);
        $mform->addRule('orario1', get_string('mandatoryformelement', 'mod_coripodatacollection'),
                'required');

        $mform->addElement('select', 'giorno2' ,
                get_string('day2', 'mod_coripodatacollection'), $weekdays);
        $mform->setType('giorno2', PARAM_TEXT);
        $mform->addRule('giorno2', get_string('mandatoryformelement', 'mod_coripodatacollection'),
                'required');

        $mform->addElement('select', 'orario2' ,
                get_string('orario2', 'mod_coripodatacollection'), $orari);
        $mform->setType('orario2', PARAM_TEXT);
        $mform->addRule('orario2', get_string('mandatoryformelement', 'mod_coripodatacollection'),
                'required');

        $surname = $mform->createElement('text', 'cognome_logopedista' , '');
        $mform->setType('cognome_logopedista', PARAM_TEXT);

        $name = $mform->createElement('text', 'nome_logopedista' , '');
        $mform->setType('nome_logopedista', PARAM_TEXT);

        $mform->addGroup([$surname, $name], '',
                get_string('name_surname_logopedista', 'mod_coripodatacollection'));


        if (isset($this->_customdata['viewmode']) && $this->_customdata['viewmode'] ) {

            $gruppo = $DB->get_record('coripodatacollection_gruppi', ['id' => $this->_customdata['groupid']]);
            $mform->addElement('hidden', 'groupid', $gruppo->id);
            $mform->setType('groupid', PARAM_INT);

            $mform->setDefault('codice', $gruppo->codice);
            $mform->setDefault('sede', $gruppo->sede);
            $mform->setDefault('dettaglio_sede', $gruppo->dettaglio_sede);
            $mform->setDefault('indirizzo', $gruppo->indirizzo);
            $mform->setDefault('zona', $gruppo->zona);
            $mform->setDefault('aula', $gruppo->aula);
            $mform->setDefault('giorno1', $gruppo->giorno1);
            $mform->setDefault('orario1', $gruppo->orario1);
            $mform->setDefault('giorno2', $gruppo->giorno2);
            $mform->setDefault('orario2', $gruppo->orario2);
            $mform->setDefault('cognome_logopedista', $gruppo->cognome_logopedista);
            $mform->setDefault('nome_logopedista', $gruppo->nome_logopedista);

            $this->add_action_buttons(false, get_string('save', 'mod_coripodatacollection'));
        }

        $this->add_sticky_action_buttons(true, get_string('save', 'mod_coripodatacollection'));
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
                            'value' => get_string('back', 'mod_coripodatacollection'),
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
                        'name' => $submitlabel == get_string('save', 'mod_coripodatacollection') ? 'submitbutton': 'add_time' ,
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


