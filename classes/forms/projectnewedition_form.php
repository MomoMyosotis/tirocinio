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

use core\check\environment\unsecuredataroot;
use core_user;

class projectnewedition_form extends \moodleform {

    public function definition() {

        global $DB;

        $mform = $this->_form;

        if (isset($this->_customdata['id'])) {

            $id = $this->_customdata['id'];

            $mform->addElement('hidden', 'id', $id);
            $mform->setType('id', PARAM_INT);

        }

        $currentyear = intval(date('Y'));
        $academicyears = [$currentyear - 1 . '/' . $currentyear  => $currentyear - 1 . '/' . $currentyear,
                $currentyear . '/' . ($currentyear + 1) => $currentyear . '/' . ($currentyear + 1)];

        $existingerogations = $DB->get_records('coripodatacollection_erogations', ['projectid' => $id]);
        foreach ($existingerogations as $e) {
            $index = array_search($e->academicyearedition, $academicyears);
            if ($index !== false) {
                unset($academicyears[$index]);
            }
        }

        if (!empty($academicyears)) {

            $academicyear = $mform->createElement('select', 'academicyear', get_string('academicyear', 'mod_coripodatacollection'), $academicyears);
            $mform->setType('academicyear', PARAM_TEXT);

            $submit = $mform->createElement('submit', 'submitbutton', 'Crea nuova edizione');
            $mform->addGroup([$academicyear, $submit]);

        }

    }

    public function validation($data, $files) {

        global $DB;

        $data = (object)$data;
        $err = [];

        $existingerogations = $DB->get_records('coripodatacollection_erogations', ['projectid' => $data->id]);
        foreach ($existingerogations as $e) {

            if ($e->academicyearedition == $data->academicyear) {
                $err['academicyear'] = 'Erogazione già presente';
            }

        }

        $eval = $DB->get_records('coripodatacollection_evaluators', ['projectid' => $data->id]);
        $scuola = $DB->get_records('coripodatacollection_istituti_x_progetto', ['projectid' => $data->id]);

        if (empty($eval) || empty($scuola)) {
            $err['academicyear'] = 'Censire almeno un istituto e un utente valutatore';
        }

        return $err;
    }


}


