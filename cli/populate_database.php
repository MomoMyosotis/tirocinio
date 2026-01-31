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

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');
require_once('../../../user/lib.php');
require_once('../../../user/profile/lib.php');
require_once('../../../course/lib.php');
require_once('../../../course/modlib.php');


/*
 * Fase manuale: generare un progetto, censire gli istituti, i plessi, le insegnanti e gli utenti valutatori,
 * quindi creare una nuova erogazione ed aggiungere le classi.
 *
 * */


/*** CENSIMENTO ALUNNI ***/
$erogation = $DB->get_record('coripodatacollection_erogations', ['courseid' => 16]);
$classes = $DB->get_records('coripodatacollection_classes', ['erogazione' => $erogation->id]);
$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
foreach ($classes as $class) {

    $registered_student = $DB->count_records('coripodatacollection_class_students', ['classid' => $class->id]);
    if ($registered_student > 0 )
        continue;

    for ($i=1; $i <= $class->numerostudenti; $i++) {

        $alunno = new stdClass();
        $alunno->cognome =  substr(str_shuffle($characters), 0, 6);
        $alunno->nome = substr(str_shuffle($characters), 0, 6);
        $alunno->natoinitalia = 'Sì';
        $alunno->difficoltalinguaggio = 'No';
        $alunno->linguaparlatacasa = 'Italiano';
        $alunno->frequenzascuolainfanzia = 'Sì';
        $alunno->difficoltascuolainfanzia = 'No';
        $alunno->notadifficolta = 'No';
        $alunno->leggecentoquattro = 'No';

        $alunno->id = $DB->insert_record('coripodatacollection_alunni', $alunno);

        $class_student = new stdClass();
        $class_student->classid = $class->id;
        $class_student->studentid = $alunno->id;
        $class_student->numeroregistro = $i;
        $class_student->carta_identita = 1;
        $class_student->consenso = 1;
        $class_student->codice_consenso = 'CODICE';

        $DB->insert_record('coripodatacollection_class_students', $class_student);

        do {
            $alunno->hash_code = strtoupper(hash('joaat', $alunno->nome . $alunno->cognome . $alunno->id));
            $sql = 'SELECT * FROM mdl_coripodatacollection_alunni where hash_code="' . $alunno->hash_code .'"';
        } while ($DB->record_exists_sql($sql));
        $DB->update_record('coripodatacollection_alunni', ['id' => $alunno->id, 'hash_code' => $alunno->hash_code]);
        $DB->insert_record('coripodatacollection_risultati',
                ['alunno' => $alunno->id, 'erogazione' => $class->erogazione, 'classe' => $class->id, 'periodo' => 'prerinforzo']);
        $DB->insert_record('coripodatacollection_risultati',
                ['alunno' => $alunno->id, 'erogazione' => $class->erogazione, 'classe' => $class->id, 'periodo' => 'postrinforzo']);
    }
}


/*** CREAZIONE RISULTATI CASUALE ***/
$results = $DB->get_records('coripodatacollection_risultati', ['erogazione' => $erogation->id]);
foreach ($results as $r) {
    if ($r->periodo == 'postrinforzo') {
        unset($results[$r->id]);
    }
}

foreach ($results as $r) {

    $x = random_int(0, 10);
    $r->inserimento_parziale = $x == 4 ? 'Sì' : 'No';
    $r->metodo_didattico = 'Sillabico';
    $x = random_int(0, 10);
    $r->proveaccessorie =  $x == 8 ? 'Sì' : 'No';
    if ($x == 8) {
        $r->metafonologia_fusione = random_int(0, 10);
        $r->metafonologia_analisi_cv = random_int(0, 10);
        $r->metafonologia_analisi_cvc = random_int(0, 10);
        $r->metafonologia_segment = random_int(0, 10);
        $r->metafonologia_segment_gruppo = random_int(0, 10);
    }
    $x = random_int(0, 10);
    $r->inserimento_parziale = $x == 5 ? 'Sì' : 'No';
    foreach ($r as $key=>$value) {
        if (($key != 'lettura_modalita' && str_starts_with($key, 'lettura'))
                || str_starts_with($key, 'scrittura') || str_starts_with($key, 'matematica')) {
            $x = random_int(0, 10);
            if ($r->inserimento_parziale == 'Sì' and $x == 2) {
                $r->$key = null;
            } else {
                $r->$key = random_int(0, 10);
            }
        }
    }

    $DB->update_record('coripodatacollection_risultati', $r);

}

