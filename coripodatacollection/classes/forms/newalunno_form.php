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

class newalunno_form extends \moodleform {

    public function definition() {

        global $DB, $OUTPUT;

        $mform = $this->_form;

        if (isset($this->_customdata['classid'])) {

            $classeid = $this->_customdata['classid'];

            $mform->addElement('hidden', 'classe', $classeid);
            $mform->setType('classe', PARAM_INT);

            if ($classeid != -1) {
                $erogazioneid = $DB->get_record('coripodatacollection_classes',
                        ['id' => $classeid], 'erogazione')->erogazione;
                $mform->addElement('hidden', 'erogazione', $erogazioneid);
                $mform->setType('erogazione', PARAM_INT);
            }


        }

        if (isset($this->_customdata['alunno'])) {

            // Aggiunto prima per evitare che sia incluso nell'header della valutazione

            $alunno = $this->_customdata['alunno'];

            $mform->addElement('hidden', 'idalunno', $alunno->id);
            $mform->setType('idalunno', PARAM_INT);
            $this->add_sticky_action_buttons(true, get_string('update_infos', 'mod_coripodatacollection'));
        } else {
            $this->add_sticky_action_buttons(true, get_string('save_student', 'mod_coripodatacollection'));
        }

        $mform->addElement('header', 'header1', get_string('anagraphic', 'mod_coripodatacollection'));

        $mform->addElement('text', 'cognome',
                get_string('new_student_surname', 'mod_coripodatacollection'));
        $mform->setType('cognome', PARAM_TEXT);
        $mform->addRule('cognome',
                get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required');
        $mform->addHelpButton('cognome', 'new_student_surname', 'mod_coripodatacollection');

        $mform->addElement('text', 'nome',
                get_string('new_student_name', 'mod_coripodatacollection'));
        $mform->setType('nome', PARAM_TEXT);
        $mform->addRule('nome',
                get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required');
        $mform->addHelpButton('nome', 'new_student_name', 'mod_coripodatacollection');


        $mform->addElement('text', 'numeroregistro',
                get_string('register_number', 'mod_coripodatacollection'));
        $mform->setType('numeroregistro', PARAM_INT);
        $mform->addRule('numeroregistro',
                get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required');
        $mform->addHelpButton('numeroregistro', 'register_number', 'mod_coripodatacollection');

        if ($classeid != -1) {
            $opzioni_anni = [
                    0 => get_string('first_1', 'mod_coripodatacollection'),
                    1 => get_string('second_2', 'mod_coripodatacollection'),
                    2 => get_string('third_3', 'mod_coripodatacollection'),
                    3 => get_string('fourth_4', 'mod_coripodatacollection'),
                    4 => get_string('fifth_5', 'mod_coripodatacollection')
            ];
            if ($DB->get_record('coripodatacollection_classes', ['id' => $classeid])->pluriclasse == 1) {
                $mform->addElement('select', 'annofrequentazione',
                        get_string('freq_year', 'mod_coripodatacollection'), $opzioni_anni);
                $mform->setType('annofrequentazione', PARAM_INT);
                $mform->addRule('annofrequentazione',
                        get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required');
                $mform->addHelpButton('annofrequentazione', 'annofrequentazione', 'mod_coripodatacollection');

            }
        }

        $mform->addElement('header', 'header2', get_string('information', 'mod_coripodatacollection'));

        $paramarray = [
                get_string('select', 'mod_coripodatacollection') => '' ,
                get_string('yes', 'mod_coripodatacollection') =>
                        get_string('yes', 'mod_coripodatacollection'),
                get_string('no', 'mod_coripodatacollection') =>
                        get_string('no', 'mod_coripodatacollection'),
                get_string('no_info', 'mod_coripodatacollection') =>
                        get_string('no_info_extended', 'mod_coripodatacollection')];
        $mform->addElement('select', 'natoinitalia',
                get_string('born_in_italy', 'mod_coripodatacollection'), $paramarray);
        $mform->setType('natoinitalia', PARAM_TEXT);
        $mform->addRule('natoinitalia', get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required');
        $mform->addHelpButton('natoinitalia', 'born_in_italy', 'mod_coripodatacollection');


        $string = get_string('difficulty_present', 'mod_coripodatacollection');
        $mform->addElement('select', 'difficoltalinguaggio', $string, $paramarray);
        $mform->setType('difficoltalinguaggio', PARAM_TEXT);
        $mform->addRule('difficoltalinguaggio', get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required');
        $mform->addHelpButton('difficoltalinguaggio', 'difficulty_present', 'mod_coripodatacollection');

        $linguearray = get_languages_list();
        $mform->addElement('select', 'linguaparlatacasa',
                get_string('home_language', 'mod_coripodatacollection'), $linguearray);
        $mform->setType('linguaparlatacasa', PARAM_TEXT);
        $mform->addRule('linguaparlatacasa',
                get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required');
        $mform->addHelpButton('linguaparlatacasa', 'home_language', 'mod_coripodatacollection');



        $string = get_string('nursery_school_freq_insert', 'mod_coripodatacollection');
        $mform->addElement('select', 'frequenzascuolainfanzia', $string, $paramarray);
        $mform->setType('frequenzascuolainfanzia', PARAM_TEXT);
        $mform->addRule('frequenzascuolainfanzia',
                get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required');
        $mform->addHelpButton(
                'frequenzascuolainfanzia',
                'nursery_school_freq_insert',
                'mod_coripodatacollection');

        $string = get_string('nursery_difficulty_noted', 'mod_coripodatacollection');
        $mform->addElement('select', 'difficoltascuolainfanzia', $string, $paramarray);
        $mform->setType('difficoltascuolainfanzia', PARAM_TEXT);
        $mform->addRule('difficoltascuolainfanzia',
                get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required');
        $mform->addHelpButton(
                'difficoltascuolainfanzia',
                'nursery_difficulty_noted',
                'mod_coripodatacollection');


        $string = get_string('difficulty_noted_insert', 'mod_coripodatacollection');
        $mform->addElement('select', 'notadifficolta', $string, $paramarray);
        $mform->setType('notadifficolta', PARAM_TEXT);
        $mform->addRule('notadifficolta',
                get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required');
        $mform->addHelpButton(
                'notadifficolta',
                'difficulty_noted_insert',
                'mod_coripodatacollection');


        $string = get_string('centoquattro_law', 'mod_coripodatacollection');
        $mform->addElement('select', 'leggecentoquattro', $string, $paramarray);
        $mform->setType('leggecentoquattro', PARAM_TEXT);
        $mform->addRule('leggecentoquattro',
                get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required');
        $mform->addHelpButton(
                'leggecentoquattro',
                'centoquattro_law',
                'mod_coripodatacollection');

        $string = get_string('centoquattro_problem', 'mod_coripodatacollection');
        $string .= '  ' . $OUTPUT->pix_icon('req',
                get_string('mandatoryformelement', 'mod_coripodatacollection'));
        $mform->addElement('textarea', 'problematicacentoquattro', $string, 'wrap="virtual" rows="20" cols="20" style="height: 100px;"');
        $mform->setType('problematicacentoquattro', PARAM_TEXT);
        $mform->hideIf('problematicacentoquattro', 'leggecentoquattro',
                'neq', get_string('yes', 'mod_coripodatacollection'));


        $mform->addElement('header', 'header3',
                get_string('documents', 'mod_coripodatacollection'));

        $mform->addElement( 'selectyesno', 'carta_identita',
                get_string('carta_identita', 'mod_coripodatacollection'));
        $mform->addRule('carta_identita',
                get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required');

        $mform->addElement('selectyesno', 'consenso',
                get_string('confirm_button_consensus', 'mod_coripodatacollection'));
        $mform->addRule('consenso',
                get_string('mandatoryformelement', 'mod_coripodatacollection'), 'required');
        $mform->addHelpButton('consenso', 'confirm_button_consensus', 'mod_coripodatacollection');

        $mform->addElement('text', 'codice_consenso',
                get_string('consensus_code', 'mod_coripodatacollection'), ['readonly' => 'readonly']);
        //$mform->hardFreeze('codice_consenso');
        $mform->setType('codice_consenso', PARAM_TEXT);
        $mform->addHelpButton('codice_consenso', 'consensus_code', 'mod_coripodatacollection');
        $mform->hideIf('codice_consenso', 'consenso', 0);
        if (empty($_POST)) {
            $mform->setDefault('codice_consenso', substr($this->get_code($classeid), -5));
        } else {
            if (array_key_exists('codice_consenso', $_POST)) {
                $mform->setDefault('codice_consenso', substr($_POST['codice_consenso'], -5));
            } else {
                $mform->setDefault('codice_consenso', substr($this->get_code($classeid), -5));
            }
        }

    }


    private function get_code($classeid) {
        global $DB, $USER;

        $class = $DB->get_record('coripodatacollection_classes', ['id' => $classeid]);

        $insegnante = $DB->get_record('user', ['id' => $USER->id]);
        $insegnante_nome = substr(preg_replace('/[^bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ]/',
                '', $insegnante->lastname), 0, 3);

        $istituto = $DB->get_record('coripodatacollection_istituti', ['id' => $class->istituto]);
        $istituto_nome = str_replace(' ', '', $istituto->denominazioneistituto);

        do {
            $rand = rand(1000, 9999);
            $checksum = array_sum(str_split((string)$rand)) % 10 ;
            $codice = strtoupper($istituto_nome) . '-' . strtoupper($insegnante_nome) . '-' . $rand . $checksum;
            $sql = 'SELECT * FROM mdl_coripodatacollection_class_students WHERE codice_consenso="' . $codice . '"';
        } while ($DB->record_exists_sql($sql));

        return $codice;

    }

    public function get_code_prefix($classeid) {
        global $DB, $USER;

        $class = $DB->get_record('coripodatacollection_classes', ['id' => $classeid]);

        $insegnante = $DB->get_record('user', ['id' => $USER->id]);
        $insegnante_nome = substr(preg_replace('/[^bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ]/',
                '', $insegnante->lastname), 0, 3);

        $istituto = $DB->get_record('coripodatacollection_istituti', ['id' => $class->istituto]);
        $istituto_nome = str_replace(' ', '', $istituto->denominazioneistituto);

        return strtoupper($istituto_nome) . '-' . strtoupper($insegnante_nome) . '-';

    }

    public function display() {

        global $PAGE;

        parent::display();
        $PAGE->requires->js_init_code("
            document.getElementsByName('numeroregistro').forEach(text =>{
                text.setAttribute('type', 'number');
                text.setAttribute('min', '1');
            });
        ");
    }

    public function validation($data, $files) {
        global $DB, $USER;

        $data = (object)$data;
        $err = [];

        if ($data->numeroregistro == 0) {
            $err['numeroregistro'] = get_string('number_register_err', 'mod_coripodatacollection');
        }
        if ($data->natoinitalia == 'Select') {
            $err['natoinitalia'] = get_string('mandatoryformelement', 'mod_coripodatacollection');
        }
        if ($data->difficoltalinguaggio == 'Select') {
            $err['difficoltalinguaggio'] = get_string('mandatoryformelement', 'mod_coripodatacollection');
        }
        if ($data->linguaparlatacasa == 'Select') {
            $err['linguaparlatacasa'] = get_string('mandatoryformelement', 'mod_coripodatacollection');
        }
        if ($data->frequenzascuolainfanzia == 'Select') {
            $err['frequenzascuolainfanzia'] = get_string('mandatoryformelement', 'mod_coripodatacollection');
        }
        if ($data->difficoltascuolainfanzia == 'Select') {
            $err['difficoltascuolainfanzia'] = get_string('mandatoryformelement', 'mod_coripodatacollection');
        }
        if ($data->notadifficolta == 'Select') {
            $err['notadifficolta'] = get_string('mandatoryformelement', 'mod_coripodatacollection');
        }
        if ($data->leggecentoquattro == 'Select') {
            $err['leggecentoquattro'] = get_string('mandatoryformelement', 'mod_coripodatacollection');
        }
        if ($data->leggecentoquattro == get_string('yes', 'mod_coripodatacollection')) {
            if ($data->problematicacentoquattro == '' or is_null($data->problematicacentoquattro)) {
                $err['problematicacentoquattro'] = get_string('mandatoryformelement', 'mod_coripodatacollection');
            }
        }

        if (empty($data->idalunno)) {
            $sql = 'SELECT * 
                    FROM mdl_coripodatacollection_alunni JOIN mdl_coripodatacollection_class_students
                    ON mdl_coripodatacollection_alunni.id = mdl_coripodatacollection_class_students.studentid
                    WHERE classid = ' . $data->classe;
            $existingalunno = $DB->get_records_sql($sql);
            foreach ($existingalunno as $e) {

                if ($e->nome == $data->nome && $e->cognome == $data->cognome) {
                    $err['nome'] = get_string('new_student_err', 'mod_coripodatacollection');
                    $err['cognome'] = get_string('new_student_err', 'mod_coripodatacollection');
                    break;
                }

                if ($e->numeroregistro == $data->numeroregistro) {
                    $err['numeroregistro'] = get_string('register_number_err_2', 'mod_coripodatacollection');
                }
            }
        } else {
            $sql = 'SELECT * 
                    FROM mdl_coripodatacollection_alunni JOIN mdl_coripodatacollection_class_students
                    ON mdl_coripodatacollection_alunni.id = mdl_coripodatacollection_class_students.studentid
                    WHERE classid = ' . $data->classe;
            $existingalunno = $DB->get_records_sql($sql);
            foreach ($existingalunno as $e) {

                if ($e->studentid == $data->idalunno){
                    continue;
                }

                if ($e->nome == $data->nome && $e->cognome == $data->cognome) {
                    $err['nome'] = get_string('new_student_err', 'mod_coripodatacollection');
                    $err['cognome'] = get_string('new_student_err', 'mod_coripodatacollection');
                    break;
                }

                if ($e->numeroregistro == $data->numeroregistro) {
                    $err['numeroregistro'] = get_string('register_number_err_2', 'mod_coripodatacollection');
                }
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


