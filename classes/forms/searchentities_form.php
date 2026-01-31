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

use context;
use mod_h5pactivity\local\report\results;
use moodle_url;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class searchentities_form extends \moodleform {

    public function definition() {

        global $DB;

        $mform = $this->_form;

        if (isset($this->_customdata['id'])) {

            $id = $this->_customdata['id'];

            $mform->addElement('hidden', 'id', $id);
            $mform->setType('id', PARAM_INT);

            if(isset($this->_customdata['evaluators'])) {
                $mform->addElement('header', 'header',
                        get_string('search_evaluator', 'mod_coripodatacollection'));
                $entities = $this->get_project_evaluator();
                $areanames = [];
                foreach ($entities as $e) {
                    $areanames[$e->id] = $e->firstname . ' ' . $e->lastname . ' - ' . $e->email;
                }
            } elseif (isset($this->_customdata['institutes'])) {
                $mform->addElement('header', 'header',
                        get_string('search_registered_institute', 'mod_coripodatacollection'));
                $entities = $this->get_project_institutes($id);
                $areanames = [];
                foreach ($entities as $e) {
                    $areanames[$e->id] = $e->denominazioneistituto;
                }
            } elseif (isset($this->_customdata['teachers'])) {
                // Ora id conterra l'id dell'istituto di cui
                // si dovrà controllare se gli insegnanti appartengono o meno.
                $mform->addElement('header', 'header',
                get_string('search_registered_teachers', 'mod_coripodatacollection'));
                $entities = $this->get_institute_teachers($id);
                $areanames = [];
                foreach ($entities as $e) {
                    $areanames[$e->id] = $e->firstname . ' ' . $e->lastname . ' - ' . $e->email;
                }                
            } else {
                // Cerca quasliasi utente di moodle
                $mform->addElement('header', 'header',
                get_string('search_registered_users', 'mod_coripodatacollection'));
                $entities = $this->get_platoform_users($id);
                $areanames = [];
                foreach ($entities as $e) {
                    $areanames[$e->id] = $e->firstname . ' ' . $e->lastname . ' - ' . $e->email;
                }
            }
            
            $options = [
                    'multiple' => true,
                    'noselectionstring' => '',
            ];
            $autocomplete = $mform->createElement('autocomplete', 'searchentity', '', $areanames, $options);
            $submit = $mform->createElement('submit', 'submitbutton',
                    get_string('add', 'mod_coripodatacollection'));
            $mform->addGroup([$autocomplete, $submit]);

        }
    }

    private function get_project_institutes($projectid) {
        global $DB;
        return $DB->get_records_sql('SELECT id, denominazioneistituto
                FROM {coripodatacollection_istituti}
                WHERE id NOT in (
                    SELECT instituteid	
                    from {coripodatacollection_istituti_x_progetto}
                    where projectid = ' . $projectid . '
                )');
    }

    private function get_project_evaluator() {
        global $DB;
        return $DB->get_records_sql(
                'SELECT {user}.id, firstname, lastname, email
                FROM {coripodatacollection_evaluators}
                JOIN {user} on {coripodatacollection_evaluators}.userid = {user}.id
                WHERE projectid IS NULL');
    }

    private function get_institute_teachers($instituteid) {

        global $DB;
        $results =  $DB->get_records_sql(
                'SELECT DISTINCT {user}.id, firstname, lastname, email
                     FROM {user}  
                     join {coripodatacollection_teachers} ON {user}.id = {coripodatacollection_teachers}.userid
                     WHERE {user}.id not in (
                         SELECT userid
                         FROM {coripodatacollection_teachers}
                         WHERE instituteid = '. $instituteid . '
                     )');

        $results += $DB->get_records_sql(
                'SELECT DISTINCT {user}.id, firstname, lastname, email
                     FROM {user}  
                     join {coripodatacollection_instituteadmin} ON {user}.id = {coripodatacollection_instituteadmin}.userid
                     WHERE {user}.id not in (
                         SELECT userid
                         FROM {coripodatacollection_teachers}
                         WHERE instituteid = '. $instituteid . '
                     )');

        return $results;

    }

    private function get_platoform_users($instituteid) {
        global $DB;
        return $DB->get_records_sql('SELECT * FROM mdl_user WHERE id > 2 AND id NOT IN (
                                            SELECT userid FROM mdl_coripodatacollection_instituteadmin
                                            WHERE instituteid = ' . $instituteid . '
                                    )');
    }

}


