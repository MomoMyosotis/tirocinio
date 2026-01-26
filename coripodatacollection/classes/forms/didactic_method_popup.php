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

class didactic_method_popup extends \moodleform {

    protected function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $mform->addElement('hidden', 'classid', $customdata['idclasse']);
        $mform->setType('classid', PARAM_INT);

        $mform->addElement('hidden', 'editmode', $customdata['editmode']);
        $mform->setType('editmode', PARAM_INT);

        // Select element.
        $options = [
                '' => '',
                get_string('phonosyllabic', 'mod_coripodatacollection') => get_string('phonosyllabic', 'mod_coripodatacollection'),
                get_string('sillabic', 'mod_coripodatacollection') => get_string('sillabic', 'mod_coripodatacollection'),
                get_string('global', 'mod_coripodatacollection') => get_string('global', 'mod_coripodatacollection'),
                get_string('siglo', 'mod_coripodatacollection') => get_string('siglo', 'mod_coripodatacollection'),
                get_string('other', 'mod_coripodatacollection') => get_string('other', 'mod_coripodatacollection'),
        ];
        $mform->addElement(
                'select',
                'confirmoptions',
                get_string('didatic_method', 'mod_coripodatacollection'),
                $options
        );
        $mform->setType('confirmoptions', PARAM_TEXT);
        $mform->addRule('confirmoptions', null, 'required', null, 'client');

        // Other method text input.
        $mform->addElement(
                'text',
                'othermethod',
                get_string('other_didatic_method', 'mod_coripodatacollection')
        );
        $mform->setType('othermethod', PARAM_TEXT);

        // Hide unless specific option selected.
        $mform->hideIf('othermethod', 'confirmoptions', 'neq', get_string('other', 'mod_coripodatacollection'));

        // Buttons.
        $this->add_action_buttons(
                true,
                get_string('confirm')
        );
    }

    public function validation($data, $files) {

        $data = (object)$data;
        $err = [];

        if ($data->confirmoptions == get_string('other', 'mod_coripodatacollection')) {
            if ($data->othermethod == '' or empty($data->othermethod)) {
                $err['othermethod'] = get_string('complete_text_field', 'mod_coripodatacollection');
            }
        }
        return $err;
    }
}
