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
 * Library of interface functions and constants.
 *
 * @package     mod_coripodatacollection
 * @copyright   2024 Cordioli Davide cordiolidavide1@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function coripodatacollection_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_coripodatacollection into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_coripodatacollection_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function coripodatacollection_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();

    $id = $DB->insert_record('coripodatacollection', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the mod_coripodatacollection in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_coripodatacollection_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function coripodatacollection_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('coripodatacollection', $moduleinstance);
}

/**
 * Removes an instance of the mod_coripodatacollection from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function coripodatacollection_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('coripodatacollection', ['id' => $id]);
    if (!$exists) {
        return false;
    }

    $DB->delete_records('coripodatacollection', ['id' => $id]);

    return true;
}

/**
 * Extends the global navigation tree by adding mod_coripodatacollection nodes if there is a relevant content.
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $coripodatacollectionnode An object representing the navigation tree node.
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function coripodatacollection_extend_navigation($coripodatacollectionnode, $course, $module, $cm) {
}

/**
 * Extends the settings navigation with the mod_coripodatacollection settings.
 *
 * This function is called when the context for the page is a mod_coripodatacollection module.
 * This is not called by AJAX so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@see settings_navigation}
 * @param navigation_node $coripodatacollectionnode {@see navigation_node}
 */
function coripodatacollection_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $coripodatacollectionnode ) {
}

/**
 * Insert a link on the site front page navigation menu.
 *
 * @param navigation_node $frontpage Node representing the front page in the navigation tree.
 */
function coripodatacollection_extend_navigation_frontpage(navigation_node $frontpage): void {

    $context = context_system::instance();
    if (isloggedin() && !isguestuser()) {

        if (has_capability('mod/coripodatacollection:projectadmin', $context)) {
            $frontpage->add(
                    get_string('projectadmin', 'mod_coripodatacollection'),
                    new moodle_url('/mod/coripodatacollection/projectsadmin.php', ['page' => 'projects'])
            );
        }

        if (has_capability('mod/coripodatacollection:schoolmanager', $context)) {
            $frontpage->add(
                    get_string('schoolmanager', 'mod_coripodatacollection'),
                    new moodle_url('/mod/coripodatacollection/instituteadmin.php')
            );
        }
    }
}

/**
 * Generate a secondary navigation menu in the page where it is called.
 *
 * @param array $data An array defining the elements of the menu defined by the elements: text, url, key, isactive;
 */
function coripodatacollection_projectview_displaymenu(array $data): void {

    global $PAGE;

    $activenode = $PAGE->secondarynav->find_active_node();
    if (!empty($activenode)) {
        $activenode->isactive = false;
    }

    foreach ($data as $newnode) {
        $addednode = $PAGE->secondarynav->add($newnode['text'], $newnode['url']);
        if (empty($newnode['isactive'])) {
            $addednode->isactive=false;
        } else {
            $addednode->isactive=true;
        }
    }
}

/**
 * Getting the data after the submission, it creat a new course with some standard elements like the name, the category,
 * the format any some others. Then it add the instance of the coripodatacollection module to the first section in the course.
 * It also add the erogation of the project in the database and finaly it also add the right users depending on the project
 * to the course.
 *
 * @param stdClass $data A class containing just the academic year in the format YYYY/YYYY;
 * @param int $projectid The id of the project of which add the new erogation;
 * @return stdClass the class containing all the information of the new generated course;
 */
function newedition_course(stdClass $data, int $projectid): stdClass {

    global $DB, $SITE, $CFG;
    $project = $DB->get_record('coripodatacollection_projects', ['id' => $projectid]);

    // Generation of the new course.
    $newedition_course = new stdClass();
    $newedition_course->category = 1;
    $newedition_course->fullname = $DB->get_record('coripodatacollection_projects',
                    ['id' => $projectid], 'projectname')->projectname . ' ' . $data->academicyear;
    $newedition_course->shortname = $newedition_course->fullname;
    $newedition_course->summaryformat = 1;
    $newedition_course->format = 'topics';
    $newedition_course->showgrades = 0;
    $newedition_course->newsitems = 5;
    $newedition_course->startdate = strtotime('today');
    $newedition_course->timecreated = time();
    $newedition_course->timemodified = $newedition_course->timecreated;
    $newcourse = create_course($newedition_course);

    // Generation of the new module instance.
    $datacollectioninstance = new stdClass();
    $datacollectioninstance->name = 'Database';
    $datacollectioninstance->modulename = 'coripodatacollection';
    $datacollectioninstance->module = $DB->get_record('modules', ['name' => 'coripodatacollection'])->id;
    $datacollectioninstance->visible = 1;
    $datacollectioninstance->downloadcontent = 1;
    $datacollectioninstance->groupmode = 0;
    $datacollectioninstance->groupingid = 0;
    $datacollectioninstance->completion = 0;
    $datacollectioninstance->completionview = 0;
    $datacollectioninstance->completionexpected = 0;
    $datacollectioninstance->section = 0;
    add_moduleinfo($datacollectioninstance, $newcourse);

    // Adding the new edition for the project.
    $newedition = new stdClass();
    $newedition->projectid = $projectid;
    $newedition->academicyearedition = $data->academicyear;
    $newedition->courseid = $newcourse->id;
    $newedition->phase = 0;
    $newedition->startingyear= explode("/", (int)$newedition->academicyearedition)[0];
    $newedition->start_censimento = time();
    $newedition->end_censimento = strtotime("+1 month", $newedition->start_censimento);
    $newedition->start_val_pre = strtotime("+1 day", $newedition->end_censimento);
    $newedition->end_val_pre = strtotime("+1 month", $newedition->start_val_pre);
    $newedition->start_val_post = strtotime("+1 day", $newedition->end_val_pre);;
    $newedition->end_val_post = strtotime("+1 month", $newedition->start_val_post);
    $newedition->calcolo_globale_pre = 0;
    $newedition->calcolo_globale_post = 0;
    $newerogation = $DB->insert_record('coripodatacollection_erogations', $newedition);


    $context = context_course::instance($newcourse->id);

    // Adding the evaluators
    $studentrole = $DB->get_record('role', ['shortname' => 'student']);

    $evaluators = $DB->get_records('coripodatacollection_evaluators', ['projectid' => $projectid], 'userid');
    foreach ($evaluators as $evaluator) {
        if (!is_enrolled($context, $evaluator->userid)) {
            if (enrol_try_internal_enrol($newcourse->id, $evaluator->userid, $studentrole->id)) {

                $user = $DB->get_record('user', ['id' => $evaluator->userid]);
                $supportuser = core_user::get_support_user();
                $subject = get_string('add_newedition_evaluator_object', 'mod_coripodatacollection');
                $body = sprintf(
                        get_string('add_newedition_evaluator_body', 'mod_coripodatacollection'),
                        $user->lastname . ' ' . $user->firstname,
                        $project->projectname,
                        $SITE->fullname,
                        $CFG->wwwroot . '/login/index.php',
                        $newcourse->fullname
                );
                email_to_user($user, $supportuser, $subject, $body);

            }
        }
    }

    // Adding the directors of institutes and the institutes to the project new edition.
    $institutes = $DB->get_records_sql('SELECT *
                                            FROM {coripodatacollection_istituti} 
                                            join {coripodatacollection_istituti_x_progetto} 
                                            on {coripodatacollection_istituti}.id = instituteid
                                            join {coripodatacollection_instituteadmin}
                                            on {coripodatacollection_instituteadmin}.instituteid = {coripodatacollection_istituti}.id
                                            WHERE projectid = ' . $projectid);
    foreach ($institutes as $institute) {
        if (!is_enrolled($context, $institute->userid)) {
            if (enrol_try_internal_enrol($newcourse->id, $institute->userid, $studentrole->id)) {

                $user = $DB->get_record('user', ['id' => $institute->userid]);
                $supportuser = core_user::get_support_user();
                $subject = get_string('add_newedition_istitute_object', 'mod_coripodatacollection');
                $body = sprintf(
                        get_string('add_newedition_istitute_body', 'mod_coripodatacollection'),
                        $user->lastname . ' ' . $user->firstname,
                        $project->projectname,
                        $institute->denominazioneistituto,
                        $SITE->fullname,
                        $CFG->wwwroot . '/login/index.php',
                        $newcourse->fullname
                );
                email_to_user($user, $supportuser, $subject, $body);

            }
        }
        $newinstituteinerogation = new stdClass();
        $newinstituteinerogation->instituteid = $institute->instituteid;
        $newinstituteinerogation->projectid = $projectid;
        $newinstituteinerogation->erogation = $newerogation;
        if (empty($DB->get_records('coripodatacollection_istituti_x_progetto_x_aa',
                (array)$newinstituteinerogation))) {
            $DB->insert_record('coripodatacollection_istituti_x_progetto_x_aa', $newinstituteinerogation);
        }
    }

    return $newcourse;
}

/**
 * Funzione per il display della tabella degli allunni. Nel database se non ci sono informazioni riguardo ad un determinato oggetto
 * si salva la stringa NoInfo, che quindi viene resa esplicita. Altrimenti ritorna la stringa così come è
 *
 * @param $str object elemento qualsiasi
 * @param string|object in base al'esito del controllo
 */
function returnNoInfo($str) {
    return $str == 'NoInfo' ? 'Nessuna informazione' : $str;
}



/**
 * Funzione che dato un valore e un array che lo contiene, restituisce vero se è un outlier rispetto agli elementi dell'array, falso
 * se non lo è. In particolare gli outlier sono calcolati come segue:
 *  - se il numero di elementi presenti all'interno dell'array è di meno di 20 elementi, l'outlier è solo il valore più basso
 *  - se il numero di elementi è di 20 o più elementi, vengono eliminati i valori che costituiscono il 5% delle code della
 * gaussiana
 *
 */
function is_outlier( $value_key,  $array, $higher_worse = true ) {

    $array = array_filter($array, fn($v) => !is_null($v));
    if (count(array_unique($array)) == 1)
        return false;

    asort($array);

    if (count($array) == 0 )
        return false;

    $media = array_sum($array) / count($array);
    $sommatoria = array_sum(array_map(fn($x) => pow($x - $media, 2), $array));
    $devStandard = sqrt($sommatoria / count($array));

    if ($media == 0)
        return false;
    if( ($devStandard / $media) <= 0.2 )
        return false;

    $outlier_number = max(1, round(count($array) * 0.05));
    $lowerbound = array_slice($array, 0, $outlier_number, true);
    $upperbound = array_slice($array, -$outlier_number, null, true);

    if ( count($array) < 20 )
        if ($higher_worse)
            return array_key_exists($value_key, $upperbound);
        else
            return array_key_exists($value_key, $lowerbound);

    return array_key_exists($value_key, $lowerbound) || array_key_exists($value_key, $upperbound);
}

/**
 * Funzione che dato un array associativo contenente come valori dei stdClass, restituisce il medesimo array con i valori
 * corrispondenti agli outlier settati a -1 per le colonne riportate come parametro $cols
 *
 */
function outlier_remove( $risultati,  $cols ) {

    foreach ($cols as $field) {
        $values = [];
        foreach ($risultati as $obj) {
            $values[$obj->id] = $obj->$field;
        }
        foreach ($risultati as $res) {
            if (is_outlier($res->id, $values))
                $res->$field = -1;
        }
    }

    return $risultati;

}

/**
 * Funzione che esegue la data completion di un array di stdObject contenenti i risultati degli alunni.
 * La data completion funziona riempiendo i valori null con il valore della media calcolato per quel risultato
 *
 * Questa funzione deve essere eseguita dopo 'calcolo_media_classe' e dopo 'calcolo_stddev_classe'
 *
 */
function data_completion( $risultati ) {

    global $DB;

    $cols = array_keys($DB->get_columns('coripodatacollection_risultati'));
    $cols = array_slice($cols, 16);
    unset($cols[10]);
    unset($cols[11]);
    unset($cols[16]);
    unset($cols[17]);

    foreach ($cols as $field) {
        if (str_ends_with($field, 'tempo'))
            foreach ($risultati as $res) {
                if ($res->$field == 0)
                    $res->$field = null;
            }
    }

    foreach ($cols as $field) {

        $media = 0;
        $count_not_null = 0;
        foreach ($risultati as $res) {
            if (!is_null($res->$field) && $res->$field != -1) {
                $media += $res->$field;
                $count_not_null += 1;
            }
        }
        $media = $count_not_null == 0 ? 0 : $media / $count_not_null;
        foreach ($risultati as $res) {
            if (is_null($res->$field)) {
                $res->$field = $media;
            }
        }
    }

    return $risultati;

}



/**
 * Funzione per il calcolo della media dei dati grezzi di una determinata classe, il periodo indica se si tratta delle valutazioni
 * pre o post rinforzo.
 *
 * @param int $classid è l'id della classe a cui fare rifermineto. Esso ricordiamo è univoco per ogni erogazione
 * @param string $periodo per indicare il periodo inserire 'prerinforzo' per indicare i test pre rinforzo, altrimenti postrinforzo
 */
function calcolo_mediares_classe( int $classid, string $periodo, bool $remove_outlier ) : void {

    global $DB;

    $risultati = $DB->get_records_sql('SELECT DISTINCT res.*
                                           FROM mdl_coripodatacollection_risultati as res
                                                JOIN mdl_coripodatacollection_class_students as cls_std
                                                    ON res.alunno = cls_std.studentid
                                           WHERE classe = ' . $classid . ' 
                                                    AND periodo = "' . $periodo . '"
                                                    AND cls_std.consenso = 1
                                                    AND includi_calcolo="Sì"');

    if ($remove_outlier) {
        $cols = array_keys($DB->get_columns('coripodatacollection_risultati'));
        $cols = array_slice($cols, 16);
        unset($cols[10]);
        unset($cols[11]);
        unset($cols[16]);
        unset($cols[17]);
        $risultati = outlier_remove($risultati, $cols);
    }

    $risultati = data_completion($risultati);

    if (empty($risultati)) {
        return;
    }

    $sql = 'SELECT * FROM mdl_coripodatacollection_medie_risultati
            WHERE classe =' . $classid . ' AND periodo ="' . $periodo . '"';
    if ($DB->record_exists_sql($sql)) {
        $medie_res = $DB->get_record_sql($sql);
    } else {
        $class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);
        $medie_res = new stdClass();
        $medie_res->erogazione = $class->erogazione;
        $medie_res->classe = $class->id;
        $medie_res->periodo = $periodo;
    }

    $medie_cols = array_keys($DB->get_columns('coripodatacollection_medie_risultati'));
    unset($medie_cols[0]);
    unset($medie_cols[1]);
    unset($medie_cols[2]);
    unset($medie_cols[3]);

    $outliers = [];
    foreach ($medie_cols as $col) {
        $medie_res->$col = 0;
        $outliers[$col] = 0;
    }

    foreach ($risultati as $res)
        foreach ($medie_cols as $col)
            if ($res->$col == -1)
                $outliers[$col] += 1;
            else
                $medie_res->$col += $res->$col;

    foreach ($medie_cols as $col)
        $medie_res->$col = $medie_res->$col / (count($risultati) - $outliers[$col]);

    if ($DB->record_exists_sql($sql)) {
        $DB->update_record('coripodatacollection_medie_risultati', $medie_res);
    } else {
        $DB->insert_record('coripodatacollection_medie_risultati', $medie_res);
    }
}




/**
 * Funzione per il calcolo della deviazione standard dei dati grezzi di una determinata classe, il periodo indica se si tratta delle
 * valutazioni pre o post rinforzo.
 *
 * @param int $classid è l'id della classe a cui fare rifermineto. Esso ricordiamo è univoco per ogni erogazione
 * @param string $periodo per indicare il periodo inserire 'prerinforzo' per indicare i test pre rinforzo, altrimenti postrinforzo
 */
function calcolo_stddevres_classe( int $classid, string $periodo, bool $remove_outlier ) : void {

    global $DB;

    $risultati = $DB->get_records_sql('SELECT DISTINCT res.*
                                           FROM mdl_coripodatacollection_risultati as res
                                                JOIN mdl_coripodatacollection_class_students as cls_std
                                                    ON res.alunno = cls_std.studentid
                                           WHERE classe = ' . $classid . ' 
                                                    AND periodo = "' . $periodo . '"
                                                    AND cls_std.consenso = 1
                                                    AND includi_calcolo="Sì"');

    if ($remove_outlier) {
        $cols = array_keys($DB->get_columns('coripodatacollection_risultati'));
        $cols = array_slice($cols, 16);
        unset($cols[10]);
        unset($cols[11]);
        unset($cols[16]);
        unset($cols[17]);
        $risultati = outlier_remove($risultati, $cols);
    }

    $risultati = data_completion($risultati);

    if (empty($risultati)) {
        return;
    }

    $sql = 'SELECT * FROM mdl_coripodatacollection_medie_risultati
            WHERE classe =' . $classid . ' AND periodo ="' . $periodo . '"';
    $medie_res = $DB->get_record_sql($sql);

    $sql = 'SELECT * FROM mdl_coripodatacollection_stddev_risultati
            WHERE classe =' . $classid . ' AND periodo ="' . $periodo . '"';
    if ($DB->record_exists_sql($sql)) {
        $stddev_res = $DB->get_record_sql($sql);
    } else {
        $class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);
        $stddev_res = new stdClass();
        $stddev_res->erogazione = $class->erogazione;
        $stddev_res->classe = $class->id;
        $stddev_res->periodo = $periodo;
    }

    $stddev_cols = array_keys($DB->get_columns('coripodatacollection_stddev_risultati'));
    unset($stddev_cols[0]);
    unset($stddev_cols[1]);
    unset($stddev_cols[2]);
    unset($stddev_cols[3]);

    $outliers = [];
    foreach ($stddev_cols as $col) {
        $stddev_res->$col = 0;
        $outliers[$col] = 0;
    }

    foreach ($risultati as $res)
        foreach ($stddev_cols as $col)
            if ($res->$col == -1)
                $outliers[$col] += 1;
            else
                $stddev_res->$col += pow($res->$col - $medie_res->$col, 2);;

    foreach ($stddev_cols as $col)
        $stddev_res->$col = sqrt($stddev_res->$col / (count($risultati) - $outliers[$col]));


    if ($DB->record_exists_sql($sql)) {
        $DB->update_record('coripodatacollection_stddev_risultati', $stddev_res);
    } else {
        $DB->insert_record('coripodatacollection_stddev_risultati', $stddev_res);
    }

}



/**
 * Funzione per il calcolo degli indici di valutazione per ogni alunno. Nel caso della mancanza di dati nella tabella
 * dei risultati si usa la data completion per completare i dati.
 *
 * Questa funzione deve essere eseguita dopo 'calcolo_media_classe' e dopo 'calcolo_stddev_classe'
 *
 * @param int $classid è l'id della classe a cui fare rifermineto. Esso ricordiamo è univoco per ogni erogazione
 * @param string $periodo per indicare il periodo inserire 'prerinforzo' per indicare i test pre rinforzo, altrimenti postrinforzo
 */
function calcolo_indici_alunno( $classid, $periodo ) : void {

    global $DB;

    $risultati = $DB->get_records_sql('SELECT DISTINCT res.*
                                           FROM mdl_coripodatacollection_risultati as res
                                                JOIN mdl_coripodatacollection_class_students as cls_std
                                                    ON res.alunno = cls_std.studentid
                                           WHERE classe = ' . $classid . ' 
                                                    AND periodo = "' . $periodo . '"
                                                    AND cls_std.consenso = 1
                                                    AND includi_calcolo="Sì"');

    $risultati = data_completion($risultati);

    if (empty($risultati)) {
        return;
    }

    foreach ($risultati as $res) {

        if ($res->includi_calcolo == 'No')
            continue;

        $sql = 'SELECT * 
                FROM mdl_coripodatacollection_indici_valutazione 
                WHERE risultato_originale = ' . $res->id . ' AND periodo = "' . $periodo . '"';
        if($DB->record_exists_sql($sql)) {
            $indici_val = $DB->get_record_sql($sql);
        } else {
            $class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);

            $indici_val = new stdClass();
            $indici_val->erogazione = $class->erogazione;
            $indici_val->classe = $class->id;
            $indici_val->periodo = $periodo;
            $indici_val->alunno = $res->alunno;
            $indici_val->risultato_originale = $res->id;
        }

        if ($periodo == 'prerinforzo') {

            $indici_val->lettura_correttezza = $res->lettura_fonemi_errori + $res->lettura_sillabe_cv_errori +
                    $res->lettura_sillabe_cvc_errori + $res->lettura_piane_errori +
                    $res->lettura_nonparole_errori;

            $indici_val->lettura_rapidita = $res->lettura_fonemi_tempo + $res->lettura_sillabe_cv_tempo +
                    $res->lettura_sillabe_cvc_tempo + $res->lettura_piane_tempo +
                    $res->lettura_nonparole_tempo;

            $indici_val->lettura_sublessicale = 36 / ($res->lettura_sillabe_cv_tempo +
                            $res->lettura_sillabe_cvc_tempo + $res->lettura_nonparole_tempo);

            $indici_val->lettura_lessicale = 15 / $res->lettura_piane_tempo;

            $indici_val->lettura_media = 51 / ($res->lettura_sillabe_cv_tempo +
                            $res->lettura_sillabe_cvc_tempo + $res->lettura_piane_tempo +
                            $res->lettura_nonparole_tempo);

            $indici_val->scrittura_correttezza = $res->scrittura_sillabe_cv_errori + $res->scrittura_sillabe_cvc_errori +
                    $res->scrittura_piane_errori + $res->scrittura_nonparole_errori;

            $indici_val->matematica_correttezza_lettura = $res->matematica_letturan_errori + $res->matematica_dettaton_errori;

            $indici_val->matematica_correttezza_enumerazione =
                    $res->matematica_enumavanti_errori + $res->matematica_enumindietro_errori;

            $indici_val->matematica_correttezza_decodifica =
                    $res->matematica_ricquantita_errori + $res->matematica_confronto_errori +
                    $res->matematica_orddalpiupiccolo_errori + $res->matematica_orddalpiugrande_errori;

            $indici_val->matematica_correttezza_calcolo = $res->matematica_addizioni_errori + $res->matematica_sottrazioni_errori;
        } else {
            $indici_val->lettura_correttezza = ($res->lettura_sillabe_cv_errori + $res->lettura_sillabe_cvc_errori +
                    $res->lettura_piane_errori + $res->lettura_nonparole_errori + $res->lettura_parolecons_errori) / 5;

            $indici_val->lettura_rapidita = ($res->lettura_sillabe_cv_tempo + $res->lettura_sillabe_cvc_tempo +
                            $res->lettura_piane_tempo + $res->lettura_nonparole_tempo + $res->lettura_parolecons_tempo) / 5;;

            $indici_val->lettura_sublessicale = 36 / ($res->lettura_sillabe_cv_tempo +
                            $res->lettura_sillabe_cvc_tempo + $res->lettura_nonparole_tempo);

            $indici_val->lettura_lessicale = 15 / $res->lettura_piane_tempo;

            $indici_val->lettura_media = 51 / ($res->lettura_sillabe_cv_tempo +
                            $res->lettura_sillabe_cvc_tempo + $res->lettura_piane_tempo +
                            $res->lettura_nonparole_tempo);

            $indici_val->scrittura_correttezza = ($res->scrittura_sillabe_cv_errori + $res->scrittura_sillabe_cvc_errori +
                    $res->scrittura_piane_errori + $res->scrittura_nonparole_errori + $res->scrittura_parolecons_errori +
                    $res->scrittura_paroleort_errori) / 6;

            $indici_val->matematica_correttezza_lettura =
                    ($res->matematica_letturan_errori + $res->matematica_trasforma_cifre_errori) / 2;

            $indici_val->matematica_correttezza_enumerazione =
                    ($res->matematica_enumavanti_errori + $res->matematica_enumindietro_errori) / 2;

            $indici_val->matematica_correttezza_decodifica = ($res->matematica_confronto_errori +
                    $res->matematica_orddalpiupiccolo_errori + $res->matematica_orddalpiugrande_errori) / 2;

            $indici_val->matematica_correttezza_calcolo =
                    ($res->matematica_addizioni_errori + $res->matematica_sottrazioni_errori) / 2;

        }

        if($DB->record_exists_sql($sql)) {
            $DB->update_record('coripodatacollection_indici_valutazione ', $indici_val);
        } else {
            $DB->insert_record('coripodatacollection_indici_valutazione ', $indici_val);
        }

    }

    $risultati = $DB->get_records_sql('SELECT DISTINCT res.*
                                           FROM mdl_coripodatacollection_risultati as res
                                                JOIN mdl_coripodatacollection_class_students as cls_std
                                                    ON res.alunno = cls_std.studentid
                                           WHERE classe = ' . $classid . ' 
                                                    AND periodo = "' . $periodo . '"
                                                    AND cls_std.consenso = 1
                                                    AND includi_calcolo="No"');

    foreach ($risultati as $res) {

        $sql = 'SELECT * 
                FROM mdl_coripodatacollection_indici_valutazione 
                WHERE risultato_originale = ' . $res->id . ' AND periodo = "' . $periodo . '"';
        if($DB->record_exists_sql($sql)) {
            $indici_val = $DB->get_record_sql($sql);
        } else {
            $class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);

            $indici_val = new stdClass();
            $indici_val->erogazione = $class->erogazione;
            $indici_val->classe = $class->id;
            $indici_val->periodo = $periodo;
            $indici_val->alunno = $res->alunno;
            $indici_val->risultato_originale = $res->id;
        }

        $indici_val->lettura_correttezza = null;
        $indici_val->lettura_rapidita = null;
        $indici_val->scrittura_correttezza = null;
        $indici_val->matematica_correttezza_lettura = null;
        $indici_val->matematica_correttezza_enumerazione = null;
        $indici_val->matematica_correttezza_decodifica = null;
        $indici_val->matematica_correttezza_calcolo = null;
        $indici_val->lettura_sublessicale = null;
        $indici_val->lettura_lessicale = null;
        $indici_val->lettura_media = null;

        if($DB->record_exists_sql($sql)) {
            $DB->update_record('coripodatacollection_indici_valutazione ', $indici_val);
        } else {
            $DB->insert_record('coripodatacollection_indici_valutazione ', $indici_val);
        }

    }

}

/**
 * Funzione per il calcolo della media delle valutazioni di una determinata classe, il periodo indica se si tratta delle valutazioni
 * pre o post rinforzo.
 *
 * @param int $classid è l'id della classe a cui fare rifermineto. Esso ricordiamo è univoco per ogni erogazione
 * @param string $periodo per indicare il periodo inserire 'prerinforzo' per indicare i test pre rinforzo, altrimenti postrinforzo
 */
function calcolo_mediaindici_classe( int $classid, string $periodo, bool $remove_outlier  ) : void {

    global $DB;

    $indici_valutazione = $DB->get_records_sql('SELECT * FROM mdl_coripodatacollection_indici_valutazione 
                                                    WHERE classe = ' . $classid . ' AND periodo ="' . $periodo . '"');

    if ($remove_outlier) {
        $cols = array_keys($DB->get_columns('coripodatacollection_indici_valutazione'));
        $cols = array_slice($cols, 9);
        $indici_valutazione = outlier_remove($indici_valutazione, $cols);
    }

    $sql = 'SELECT * FROM mdl_coripodatacollection_medie_indici 
            WHERE classe =' . $classid . ' AND periodo ="' . $periodo . '"';
    if ($DB->record_exists_sql($sql)) {
        $medie_indici = $DB->get_record_sql($sql);
    } else {
        $class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);
        $medie_indici = new stdClass();
        $medie_indici->erogazione = $class->erogazione;
        $medie_indici->classe = $class->id;
        $medie_indici->periodo = $periodo;
    }

    $medie_indici->lettura_correttezza = 0;
    $medie_indici->lettura_rapidita = 0;
    $medie_indici->lettura_sublessicale = 0;
    $medie_indici->lettura_lessicale = 0;
    $medie_indici->lettura_media= 0;
    $medie_indici->scrittura_correttezza = 0;
    $medie_indici->matematica_correttezza_lettura = 0;
    $medie_indici->matematica_correttezza_enumerazione = 0;
    $medie_indici->matematica_correttezza_decodifica = 0;
    $medie_indici->matematica_correttezza_calcolo = 0;

    $outlier = new stdClass();
    $outlier->lettura_correttezza = 0;
    $outlier->lettura_rapidita = 0;
    $outlier->lettura_sublessicale = 0;
    $outlier->lettura_lessicale = 0;
    $outlier->lettura_media= 0;
    $outlier->scrittura_correttezza = 0;
    $outlier->matematica_correttezza_lettura = 0;
    $outlier->matematica_correttezza_enumerazione = 0;
    $outlier->matematica_correttezza_decodifica = 0;
    $outlier->matematica_correttezza_calcolo = 0;

    foreach ($indici_valutazione as $ind_val) {

        $original_res = $DB->get_record('coripodatacollection_risultati', ['id' => $ind_val->risultato_originale]);
        if ($original_res->includi_calcolo == 'No') {
            unset($indici_valutazione[$ind_val->id]);
            continue;
        }

        if ($ind_val->lettura_correttezza != -1)
            $medie_indici->lettura_correttezza += $ind_val->lettura_correttezza;
        else
            $outlier->lettura_correttezza += 1;

        if ($ind_val->lettura_rapidita != -1)
            $medie_indici->lettura_rapidita += $ind_val->lettura_rapidita;
        else
            $outlier->lettura_rapidita += 1;

        if ($ind_val->lettura_sublessicale != -1)
            $medie_indici->lettura_sublessicale += $ind_val->lettura_sublessicale;
        else
            $outlier->lettura_sublessicale += 1;

        if ($ind_val->lettura_lessicale != -1)
            $medie_indici->lettura_lessicale += $ind_val->lettura_lessicale;
        else
            $outlier->lettura_lessicale += 1;

        if ($ind_val->lettura_media != -1)
            $medie_indici->lettura_media += $ind_val->lettura_media;
        else
            $outlier->lettura_media += 1;

        if ($ind_val->scrittura_correttezza != -1)
            $medie_indici->scrittura_correttezza += $ind_val->scrittura_correttezza;
        else
            $outlier->scrittura_correttezza += 1;

        if ($ind_val->matematica_correttezza_lettura != -1)
            $medie_indici->matematica_correttezza_lettura += $ind_val->matematica_correttezza_lettura;
        else
            $outlier->matematica_correttezza_lettura += 1;

        if ($ind_val->matematica_correttezza_enumerazione != -1)
            $medie_indici->matematica_correttezza_enumerazione += $ind_val->matematica_correttezza_enumerazione;
        else
            $outlier->matematica_correttezza_enumerazione += 1;

        if ($ind_val->matematica_correttezza_decodifica != -1)
            $medie_indici->matematica_correttezza_decodifica += $ind_val->matematica_correttezza_decodifica;
        else
            $outlier->matematica_correttezza_decodifica += 1;

        if ($ind_val->matematica_correttezza_calcolo != -1)
            $medie_indici->matematica_correttezza_calcolo += $ind_val->matematica_correttezza_calcolo;
        else
            $outlier->matematica_correttezza_calcolo += 1;

    }

    $medie_indici->lettura_correttezza = $medie_indici->lettura_correttezza / (count($indici_valutazione) - $outlier->lettura_correttezza);
    $medie_indici->lettura_rapidita = $medie_indici->lettura_rapidita / (count($indici_valutazione) - $outlier->lettura_rapidita);
    $medie_indici->lettura_sublessicale = $medie_indici->lettura_sublessicale / (count($indici_valutazione) - $outlier->lettura_sublessicale);
    $medie_indici->lettura_lessicale = $medie_indici->lettura_lessicale / (count($indici_valutazione) - $outlier->lettura_lessicale);
    $medie_indici->lettura_media= $medie_indici->lettura_media / (count($indici_valutazione) - $outlier->lettura_media);
    $medie_indici->scrittura_correttezza = $medie_indici->scrittura_correttezza / (count($indici_valutazione) - $outlier->scrittura_correttezza);
    $medie_indici->matematica_correttezza_lettura = $medie_indici->matematica_correttezza_lettura / (count($indici_valutazione) - $outlier->matematica_correttezza_lettura);
    $medie_indici->matematica_correttezza_enumerazione = $medie_indici->matematica_correttezza_enumerazione / (count($indici_valutazione) - $outlier->matematica_correttezza_enumerazione);
    $medie_indici->matematica_correttezza_decodifica = $medie_indici->matematica_correttezza_decodifica / (count($indici_valutazione) - $outlier->matematica_correttezza_decodifica);
    $medie_indici->matematica_correttezza_calcolo = $medie_indici->matematica_correttezza_calcolo / (count($indici_valutazione) - $outlier->matematica_correttezza_calcolo);

    if ($DB->record_exists_sql($sql)) {
        $DB->update_record('coripodatacollection_medie_indici', $medie_indici);
    } else {
        $DB->insert_record('coripodatacollection_medie_indici', $medie_indici);
    }
}

/**
 * Funzione per il calcolo della deviazione standard delle valutazioni di una determinata classe,
 * il periodo indica se si tratta delle valutazioni pre o post rinforzo.
 *
 * Questa funzione deve essere eseguita dopo 'calcolo_media_classe'
 *
 * @param int $classid è l'id della classe a cui fare rifermineto. Esso ricordiamo è univoco per ogni erogazione
 * @param string $periodo per indicare il periodo inserire 'prerinforzo' per indicare i test pre rinforzo, altrimenti postrinforzo
 */
function calcolo_stddevindici_classe( int $classid, string $periodo, bool $remove_outlier) : void {

    global $DB;

    $indici_valutazione = $DB->get_records_sql('SELECT * FROM mdl_coripodatacollection_indici_valutazione 
                                                    WHERE classe = ' . $classid . ' AND periodo ="' . $periodo . '"');

    if ($remove_outlier) {
        $cols = array_keys($DB->get_columns('coripodatacollection_indici_valutazione'));
        $cols = array_slice($cols, 9);
        $indici_valutazione = outlier_remove($indici_valutazione, $cols);
    }

    $sql = 'SELECT * FROM mdl_coripodatacollection_medie_indici 
            WHERE classe =' . $classid . ' AND periodo ="' . $periodo . '"';
    $medie_indici = $DB->get_record_sql($sql);

    $sql = 'SELECT * FROM  mdl_coripodatacollection_stddev_indici
            WHERE classe =' . $classid . ' AND periodo ="' . $periodo . '"';
    if ($DB->record_exists_sql($sql)) {
        $stddev_indici = $DB->get_record_sql($sql);
    } else {
        $class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);
        $stddev_indici = new stdClass();
        $stddev_indici->erogazione = $class->erogazione;
        $stddev_indici->classe = $class->id;
        $stddev_indici->periodo = $periodo;
    }

    $stddev_indici->lettura_correttezza = 0;
    $stddev_indici->lettura_rapidita = 0;
    $stddev_indici->lettura_sublessicale = 0;
    $stddev_indici->lettura_lessicale = 0;
    $stddev_indici->lettura_media = 0;
    $stddev_indici->scrittura_correttezza = 0;
    $stddev_indici->matematica_correttezza_lettura = 0;
    $stddev_indici->matematica_correttezza_enumerazione = 0;
    $stddev_indici->matematica_correttezza_decodifica = 0;
    $stddev_indici->matematica_correttezza_calcolo = 0;

    $outlier = new stdClass();
    $outlier->lettura_correttezza = 0;
    $outlier->lettura_rapidita = 0;
    $outlier->lettura_sublessicale = 0;
    $outlier->lettura_lessicale = 0;
    $outlier->lettura_media= 0;
    $outlier->scrittura_correttezza = 0;
    $outlier->matematica_correttezza_lettura = 0;
    $outlier->matematica_correttezza_enumerazione = 0;
    $outlier->matematica_correttezza_decodifica = 0;
    $outlier->matematica_correttezza_calcolo = 0;

    foreach ($indici_valutazione as $ind_val) {

        $original_res = $DB->get_record('coripodatacollection_risultati', ['id' => $ind_val->risultato_originale]);
        if ($original_res->includi_calcolo == 'No') {
            unset($indici_valutazione[$ind_val->id]);
            continue;
        }

        if ($ind_val->lettura_correttezza != -1)
            $stddev_indici->lettura_correttezza += pow($ind_val->lettura_correttezza - $medie_indici->lettura_correttezza, 2);
        else
            $outlier->lettura_correttezza += 1;

        if ($ind_val->lettura_rapidita != -1)
            $stddev_indici->lettura_rapidita += pow($ind_val->lettura_rapidita - $medie_indici->lettura_rapidita, 2);
        else
            $outlier->lettura_rapidita += 1;

        if ($ind_val->lettura_sublessicale != -1)
            $stddev_indici->lettura_sublessicale += pow($ind_val->lettura_sublessicale - $medie_indici->lettura_sublessicale, 2);
        else
            $outlier->lettura_sublessicale += 1;

        if ($ind_val->lettura_lessicale != -1)
            $stddev_indici->lettura_lessicale += pow($ind_val->lettura_lessicale - $medie_indici->lettura_lessicale, 2);
        else
            $outlier->lettura_lessicale += 1;

        if ($ind_val->lettura_media != -1)
            $stddev_indici->lettura_media += pow($ind_val->lettura_media - $medie_indici->lettura_media, 2);
        else
            $outlier->lettura_media += 1;

        if ($ind_val->scrittura_correttezza != -1)
            $stddev_indici->scrittura_correttezza += pow($ind_val->scrittura_correttezza - $medie_indici->scrittura_correttezza, 2);
        else
            $outlier->scrittura_correttezza += 1;

        if ($ind_val->matematica_correttezza_lettura != -1)
            $stddev_indici->matematica_correttezza_lettura += pow($ind_val->matematica_correttezza_lettura - $medie_indici->matematica_correttezza_lettura, 2);
        else
            $outlier->matematica_correttezza_lettura += 1;

        if ($ind_val->matematica_correttezza_enumerazione != -1)
            $stddev_indici->matematica_correttezza_enumerazione += pow($ind_val->matematica_correttezza_enumerazione - $medie_indici->matematica_correttezza_enumerazione, 2);
        else
            $outlier->matematica_correttezza_enumerazione += 1;

        if ($ind_val->matematica_correttezza_decodifica != -1)
            $stddev_indici->matematica_correttezza_decodifica += pow($ind_val->matematica_correttezza_decodifica - $medie_indici->matematica_correttezza_decodifica, 2);
        else
            $outlier->matematica_correttezza_decodifica += 1;

        if ($ind_val->matematica_correttezza_calcolo != -1)
            $stddev_indici->matematica_correttezza_calcolo += pow($ind_val->matematica_correttezza_calcolo - $medie_indici->matematica_correttezza_calcolo, 2);
        else
            $outlier->matematica_correttezza_calcolo += 1;

    }

    $stddev_indici->lettura_correttezza = sqrt($stddev_indici->lettura_correttezza / (count($indici_valutazione) - $outlier->lettura_correttezza));
    $stddev_indici->lettura_rapidita = sqrt($stddev_indici->lettura_rapidita / (count($indici_valutazione) - $outlier->lettura_rapidita));
    $stddev_indici->lettura_sublessicale = sqrt($stddev_indici->lettura_sublessicale / (count($indici_valutazione) - $outlier->lettura_sublessicale));
    $stddev_indici->lettura_lessicale = sqrt($stddev_indici->lettura_lessicale / (count($indici_valutazione) - $outlier->lettura_lessicale));
    $stddev_indici->lettura_media = sqrt($stddev_indici->lettura_media / (count($indici_valutazione) - $outlier->lettura_media));
    $stddev_indici->scrittura_correttezza = sqrt($stddev_indici->scrittura_correttezza / (count($indici_valutazione) - $outlier->scrittura_correttezza));
    $stddev_indici->matematica_correttezza_lettura = sqrt($stddev_indici->matematica_correttezza_lettura / (count($indici_valutazione) - $outlier->matematica_correttezza_lettura));
    $stddev_indici->matematica_correttezza_enumerazione = sqrt($stddev_indici->matematica_correttezza_enumerazione / (count($indici_valutazione) - $outlier->matematica_correttezza_enumerazione));
    $stddev_indici->matematica_correttezza_decodifica = sqrt($stddev_indici->matematica_correttezza_decodifica / (count($indici_valutazione) - $outlier->matematica_correttezza_decodifica));
    $stddev_indici->matematica_correttezza_calcolo = sqrt($stddev_indici->matematica_correttezza_calcolo / (count($indici_valutazione) - $outlier->matematica_correttezza_calcolo));

    if ($DB->record_exists_sql($sql)) {
        $DB->update_record('coripodatacollection_stddev_indici', $stddev_indici);
    } else {
        $DB->insert_record('coripodatacollection_stddev_indici', $stddev_indici);
    }
}

/**
 * Funzione per il calcolo della media delle valutazioni di tutte le classi di una determinata erogazione,
 * il periodo indica se si tratta delle valutazioni pre o post rinforzo.
 *
 * @param int $classid è l'id della classe a cui fare rifermineto. Esso ricordiamo è univoco per ogni erogazione
 * @param string $periodo per indicare il periodo inserire 'prerinforzo' per indicare i test pre rinforzo, altrimenti postrinforzo
 */
function calcolo_mediaindici_erogazione( int $erogationid, string $periodo, bool $remove_outlier ) : void {

    global $DB;

    $indici_valutazione = $DB->get_records_sql('SELECT * FROM mdl_coripodatacollection_indici_valutazione 
                                                    WHERE erogazione = ' . $erogationid . ' AND periodo ="' . $periodo . '"
                                                    AND (valutazione_classe="Rosso" or valutazione_classe="Giallo")');

    if ($remove_outlier) {
        $cols = array_keys($DB->get_columns('coripodatacollection_indici_valutazione'));
        $cols = array_slice($cols, 9);
        $indici_valutazione = outlier_remove($indici_valutazione, $cols);
    }

    $sql = 'SELECT * FROM  mdl_coripodatacollection_medie_globali
            WHERE erogazione =' . $erogationid . ' AND periodo ="' . $periodo . '"';
    if ($DB->record_exists_sql($sql)) {
        $medie_indici = $DB->get_record_sql($sql);
    } else {
        $medie_indici = new stdClass();
        $medie_indici->erogazione = $erogationid;
        $medie_indici->periodo = $periodo;
    }

    $medie_indici->lettura_correttezza = 0;
    $medie_indici->lettura_rapidita = 0;
    $medie_indici->lettura_sublessicale = 0;
    $medie_indici->lettura_lessicale = 0;
    $medie_indici->lettura_media= 0;
    $medie_indici->scrittura_correttezza = 0;
    $medie_indici->matematica_correttezza_lettura = 0;
    $medie_indici->matematica_correttezza_enumerazione = 0;
    $medie_indici->matematica_correttezza_decodifica = 0;
    $medie_indici->matematica_correttezza_calcolo = 0;

    $outlier = new stdClass();
    $outlier->lettura_correttezza = 0;
    $outlier->lettura_rapidita = 0;
    $outlier->lettura_sublessicale = 0;
    $outlier->lettura_lessicale = 0;
    $outlier->lettura_media= 0;
    $outlier->scrittura_correttezza = 0;
    $outlier->matematica_correttezza_lettura = 0;
    $outlier->matematica_correttezza_enumerazione = 0;
    $outlier->matematica_correttezza_decodifica = 0;
    $outlier->matematica_correttezza_calcolo = 0;

    foreach ($indici_valutazione as $ind_val) {

        $original_res = $DB->get_record('coripodatacollection_risultati', ['id' => $ind_val->risultato_originale]);
        if ($original_res->includi_calcolo == 'No') {
            unset($indici_valutazione[$ind_val->id]);
            continue;
        }

        if ($ind_val->lettura_correttezza != -1)
            $medie_indici->lettura_correttezza += $ind_val->lettura_correttezza;
        else
            $outlier->lettura_correttezza += 1;

        if ($ind_val->lettura_rapidita != -1)
            $medie_indici->lettura_rapidita += $ind_val->lettura_rapidita;
        else
            $outlier->lettura_rapidita += 1;

        if ($ind_val->lettura_sublessicale != -1)
            $medie_indici->lettura_sublessicale += $ind_val->lettura_sublessicale;
        else
            $outlier->lettura_sublessicale += 1;

        if ($ind_val->lettura_lessicale != -1)
            $medie_indici->lettura_lessicale += $ind_val->lettura_lessicale;
        else
            $outlier->lettura_lessicale += 1;

        if ($ind_val->lettura_media != -1)
            $medie_indici->lettura_media += $ind_val->lettura_media;
        else
            $outlier->lettura_media += 1;

        if ($ind_val->scrittura_correttezza != -1)
            $medie_indici->scrittura_correttezza += $ind_val->scrittura_correttezza;
        else
            $outlier->scrittura_correttezza += 1;

        if ($ind_val->matematica_correttezza_lettura != -1)
            $medie_indici->matematica_correttezza_lettura += $ind_val->matematica_correttezza_lettura;
        else
            $outlier->matematica_correttezza_lettura += 1;

        if ($ind_val->matematica_correttezza_enumerazione != -1)
            $medie_indici->matematica_correttezza_enumerazione += $ind_val->matematica_correttezza_enumerazione;
        else
            $outlier->matematica_correttezza_enumerazione += 1;

        if ($ind_val->matematica_correttezza_decodifica != -1)
            $medie_indici->matematica_correttezza_decodifica += $ind_val->matematica_correttezza_decodifica;
        else
            $outlier->matematica_correttezza_decodifica += 1;

        if ($ind_val->matematica_correttezza_calcolo != -1)
            $medie_indici->matematica_correttezza_calcolo += $ind_val->matematica_correttezza_calcolo;
        else
            $outlier->matematica_correttezza_calcolo += 1;

    }

    $medie_indici->lettura_correttezza = $medie_indici->lettura_correttezza / (count($indici_valutazione) - $outlier->lettura_correttezza);
    $medie_indici->lettura_rapidita = $medie_indici->lettura_rapidita / (count($indici_valutazione) - $outlier->lettura_rapidita);
    $medie_indici->lettura_sublessicale = $medie_indici->lettura_sublessicale / (count($indici_valutazione) - $outlier->lettura_sublessicale);
    $medie_indici->lettura_lessicale = $medie_indici->lettura_lessicale / (count($indici_valutazione) - $outlier->lettura_lessicale);
    $medie_indici->lettura_media= $medie_indici->lettura_media / (count($indici_valutazione) - $outlier->lettura_media);
    $medie_indici->scrittura_correttezza = $medie_indici->scrittura_correttezza / (count($indici_valutazione) - $outlier->scrittura_correttezza);
    $medie_indici->matematica_correttezza_lettura = $medie_indici->matematica_correttezza_lettura / (count($indici_valutazione) - $outlier->matematica_correttezza_lettura);
    $medie_indici->matematica_correttezza_enumerazione = $medie_indici->matematica_correttezza_enumerazione / (count($indici_valutazione) - $outlier->matematica_correttezza_enumerazione);
    $medie_indici->matematica_correttezza_decodifica = $medie_indici->matematica_correttezza_decodifica / (count($indici_valutazione) - $outlier->matematica_correttezza_decodifica);
    $medie_indici->matematica_correttezza_calcolo = $medie_indici->matematica_correttezza_calcolo / (count($indici_valutazione) - $outlier->matematica_correttezza_calcolo);

    if ($DB->record_exists_sql($sql)) {
        $DB->update_record('coripodatacollection_medie_globali', $medie_indici);
    } else {
        $DB->insert_record('coripodatacollection_medie_globali', $medie_indici);
    }
}

/**
 * Funzione per il calcolo della deviazione standard delle valutazioni di tutte le classi di una erogazione,
 *
 *
 * Questa funzione deve essere eseguita dopo 'calcolo_media_classe'
 *
 * @param int $classid è l'id della classe a cui fare rifermineto. Esso ricordiamo è univoco per ogni erogazione
 * @param string $periodo per indicare il periodo inserire 'prerinforzo' per indicare i test pre rinforzo, altrimenti postrinforzo
 */
function calcolo_stddevindici_erogazione( int $erogationid, string $periodo, bool $remove_outlier ) : void {

    global $DB;

    $indici_valutazione = $DB->get_records_sql('SELECT * FROM mdl_coripodatacollection_indici_valutazione 
                                                    WHERE erogazione = ' . $erogationid . ' AND periodo ="' . $periodo . '"
                                                    AND (valutazione_classe="Rosso" or valutazione_classe="Giallo")');

    if ($remove_outlier) {
        $cols = array_keys($DB->get_columns('coripodatacollection_indici_valutazione'));
        $cols = array_slice($cols, 9);
        $indici_valutazione = outlier_remove($indici_valutazione, $cols);
    }

    $sql = 'SELECT * FROM  mdl_coripodatacollection_medie_globali
            WHERE erogazione =' . $erogationid . ' AND periodo ="' . $periodo . '"';
    $medie_indici = $DB->get_record_sql($sql);

    $sql = 'SELECT * FROM  mdl_coripodatacollection_stddev_globali
            WHERE erogazione =' . $erogationid . ' AND periodo ="' . $periodo . '"';
    if ($DB->record_exists_sql($sql)) {
        $stddev_indici = $DB->get_record_sql($sql);
    } else {
        $stddev_indici = new stdClass();
        $stddev_indici->erogazione = $erogationid;
        $stddev_indici->periodo = $periodo;
    }

    $stddev_indici->lettura_correttezza = 0;
    $stddev_indici->lettura_rapidita = 0;
    $stddev_indici->lettura_sublessicale = 0;
    $stddev_indici->lettura_lessicale = 0;
    $stddev_indici->lettura_media = 0;
    $stddev_indici->scrittura_correttezza = 0;
    $stddev_indici->matematica_correttezza_lettura = 0;
    $stddev_indici->matematica_correttezza_enumerazione = 0;
    $stddev_indici->matematica_correttezza_decodifica = 0;
    $stddev_indici->matematica_correttezza_calcolo = 0;

    $outlier = new stdClass();
    $outlier->lettura_correttezza = 0;
    $outlier->lettura_rapidita = 0;
    $outlier->lettura_sublessicale = 0;
    $outlier->lettura_lessicale = 0;
    $outlier->lettura_media= 0;
    $outlier->scrittura_correttezza = 0;
    $outlier->matematica_correttezza_lettura = 0;
    $outlier->matematica_correttezza_enumerazione = 0;
    $outlier->matematica_correttezza_decodifica = 0;
    $outlier->matematica_correttezza_calcolo = 0;

    foreach ($indici_valutazione as $ind_val) {

        $original_res = $DB->get_record('coripodatacollection_risultati', ['id' => $ind_val->risultato_originale]);
        if ($original_res->includi_calcolo == 'No') {
            unset($indici_valutazione[$ind_val->id]);
            continue;
        }

        if ($ind_val->lettura_correttezza != -1)
            $stddev_indici->lettura_correttezza += pow($ind_val->lettura_correttezza - $medie_indici->lettura_correttezza, 2);
        else
            $outlier->lettura_correttezza += 1;

        if ($ind_val->lettura_rapidita != -1)
            $stddev_indici->lettura_rapidita += pow($ind_val->lettura_rapidita - $medie_indici->lettura_rapidita, 2);
        else
            $outlier->lettura_rapidita += 1;

        if ($ind_val->lettura_sublessicale != -1)
            $stddev_indici->lettura_sublessicale += pow($ind_val->lettura_sublessicale - $medie_indici->lettura_sublessicale, 2);
        else
            $outlier->lettura_sublessicale += 1;

        if ($ind_val->lettura_lessicale != -1)
            $stddev_indici->lettura_lessicale += pow($ind_val->lettura_lessicale - $medie_indici->lettura_lessicale, 2);
        else
            $outlier->lettura_lessicale += 1;

        if ($ind_val->lettura_media != -1)
            $stddev_indici->lettura_media += pow($ind_val->lettura_media - $medie_indici->lettura_media, 2);
        else
            $outlier->lettura_media += 1;

        if ($ind_val->scrittura_correttezza != -1)
            $stddev_indici->scrittura_correttezza += pow($ind_val->scrittura_correttezza - $medie_indici->scrittura_correttezza, 2);
        else
            $outlier->scrittura_correttezza += 1;

        if ($ind_val->matematica_correttezza_lettura != -1)
            $stddev_indici->matematica_correttezza_lettura += pow($ind_val->matematica_correttezza_lettura - $medie_indici->matematica_correttezza_lettura, 2);
        else
            $outlier->matematica_correttezza_lettura += 1;

        if ($ind_val->matematica_correttezza_enumerazione != -1)
            $stddev_indici->matematica_correttezza_enumerazione += pow($ind_val->matematica_correttezza_enumerazione - $medie_indici->matematica_correttezza_enumerazione, 2);
        else
            $outlier->matematica_correttezza_enumerazione += 1;

        if ($ind_val->matematica_correttezza_decodifica != -1)
            $stddev_indici->matematica_correttezza_decodifica += pow($ind_val->matematica_correttezza_decodifica - $medie_indici->matematica_correttezza_decodifica, 2);
        else
            $outlier->matematica_correttezza_decodifica += 1;

        if ($ind_val->matematica_correttezza_calcolo != -1)
            $stddev_indici->matematica_correttezza_calcolo += pow($ind_val->matematica_correttezza_calcolo - $medie_indici->matematica_correttezza_calcolo, 2);
        else
            $outlier->matematica_correttezza_calcolo += 1;

    }

    $stddev_indici->lettura_correttezza = sqrt($stddev_indici->lettura_correttezza / (count($indici_valutazione) - $outlier->lettura_correttezza));
    $stddev_indici->lettura_rapidita = sqrt($stddev_indici->lettura_rapidita / (count($indici_valutazione) - $outlier->lettura_rapidita));
    $stddev_indici->lettura_sublessicale = sqrt($stddev_indici->lettura_sublessicale / (count($indici_valutazione) - $outlier->lettura_sublessicale));
    $stddev_indici->lettura_lessicale = sqrt($stddev_indici->lettura_lessicale / (count($indici_valutazione) - $outlier->lettura_lessicale));
    $stddev_indici->lettura_media = sqrt($stddev_indici->lettura_media / (count($indici_valutazione) - $outlier->lettura_media));
    $stddev_indici->scrittura_correttezza = sqrt($stddev_indici->scrittura_correttezza / (count($indici_valutazione) - $outlier->scrittura_correttezza));
    $stddev_indici->matematica_correttezza_lettura = sqrt($stddev_indici->matematica_correttezza_lettura / (count($indici_valutazione) - $outlier->matematica_correttezza_lettura));
    $stddev_indici->matematica_correttezza_enumerazione = sqrt($stddev_indici->matematica_correttezza_enumerazione / (count($indici_valutazione) - $outlier->matematica_correttezza_enumerazione));
    $stddev_indici->matematica_correttezza_decodifica = sqrt($stddev_indici->matematica_correttezza_decodifica / (count($indici_valutazione) - $outlier->matematica_correttezza_decodifica));
    $stddev_indici->matematica_correttezza_calcolo = sqrt($stddev_indici->matematica_correttezza_calcolo / (count($indici_valutazione) - $outlier->matematica_correttezza_calcolo));

    if ($DB->record_exists_sql($sql)) {
        $DB->update_record('coripodatacollection_stddev_globali', $stddev_indici);
    } else {
        $DB->insert_record('coripodatacollection_stddev_globali', $stddev_indici);
    }
}

/**
 * Funzione per il calcolo della valutazione per ogni alunno di una classe per la fase di valutazione primaria
 *
 * @param int $classid id della classe
 * @param string $periodo stringa che indica il periodo della valutazione, se pre o post valutazione
 * */
function compute_phaseone_eval( int $classid, string $periodo ) : void {

    global $DB;

    $class_indexes = $DB->get_records_sql('SELECT * FROM mdl_coripodatacollection_indici_valutazione 
                                                    WHERE classe = ' . $classid . ' AND periodo ="' . $periodo . '"');
    $media = $DB->get_record_sql('SELECT * FROM mdl_coripodatacollection_medie_indici
                                             WHERE classe = ' . $classid . ' AND periodo = "' . $periodo . '"');
    $stddev = $DB->get_record_sql('SELECT * FROM mdl_coripodatacollection_stddev_indici
                                             WHERE classe = ' . $classid . ' AND periodo = "' . $periodo . '"');

    foreach ($class_indexes as $index) {

        $original_res = $DB->get_record('coripodatacollection_risultati', ['id' => $index->risultato_originale]);
        if ($original_res->includi_calcolo == 'No')
            continue;

        if( is_null($index->valutazione_classe) || $index->valutazione_classe == '') {

            $ind = [];
            $ind[0] = compute_value_color($index->lettura_correttezza, $media->lettura_correttezza, $stddev->lettura_correttezza);
            $ind[1] = compute_value_color($index->lettura_rapidita, $media->lettura_rapidita, $stddev->lettura_rapidita);
            $ind[2] = compute_value_color($index->scrittura_correttezza, $media->scrittura_correttezza, $stddev->scrittura_correttezza);

            $green_count = count(array_filter($ind, fn($i) => $i === "green"));
            $yellow_count = count(array_filter($ind, fn($i) => $i === "yellow"));
            $red_count = count(array_filter($ind, fn($i) => $i === "red"));

            if ($original_res->proveaccessorie == 'Sì' || $red_count == 3)
                $index->valutazione_classe = get_string('red', 'mod_coripodatacollection');
            elseif ($green_count == 3 || ($green_count == 2 && $yellow_count == 1))
                $index->valutazione_classe = get_string('green', 'mod_coripodatacollection');
            else
                $index->valutazione_classe = get_string('yellow', 'mod_coripodatacollection');

            $DB->update_record('coripodatacollection_indici_valutazione', $index);

        }
    }
}

/**
 * Funzione per il calcolo della valutazione per ogni alunno di una classe per la fase di valutazione primaria
 *
 * @param int $classid id della classe
 * @param string $periodo stringa che indica il periodo della valutazione, se pre o post valutazione
 * */
function compute_finalphase_eval( int $erogationid, string $periodo ) : void {

    global $DB;

    $class_indexes = $DB->get_records_sql('SELECT * FROM mdl_coripodatacollection_indici_valutazione 
                                               WHERE (valutazione_classe = "Rosso" OR valutazione_classe = "Giallo")
                                                    AND erogazione = ' . $erogationid . ' AND periodo ="' . $periodo . '"');
    $media = $DB->get_record_sql('SELECT * FROM mdl_coripodatacollection_medie_globali
                                             WHERE erogazione = ' . $erogationid . ' AND periodo = "' . $periodo . '"');
    $stddev = $DB->get_record_sql('SELECT * FROM mdl_coripodatacollection_stddev_globali
                                             WHERE erogazione = ' . $erogationid . ' AND periodo = "' . $periodo . '"');

    foreach ($class_indexes as $index) {

        $original_res = $DB->get_record('coripodatacollection_risultati', ['id' => $index->risultato_originale]);

        if( is_null($index->valutazione_globale) || $index->valutazione_globale == '') {

            $ind = [];
            $ind[0] = compute_value_color($index->lettura_correttezza, $media->lettura_correttezza, $stddev->lettura_correttezza);
            $ind[1] = compute_value_color($index->lettura_rapidita, $media->lettura_rapidita, $stddev->lettura_rapidita);
            $ind[2] = compute_value_color($index->scrittura_correttezza, $media->scrittura_correttezza, $stddev->scrittura_correttezza);

            $green_count = count(array_filter($ind, fn($i) => $i === "green"));
            $yellow_count = count(array_filter($ind, fn($i) => $i === "yellow"));
            $red_count = count(array_filter($ind, fn($i) => $i === "red"));

            if ($original_res->proveaccessorie == 'Sì' || $red_count == 3)
                $index->valutazione_globale = get_string('red', 'mod_coripodatacollection');
            elseif ($green_count == 3 || ($green_count == 2 && $yellow_count == 1))
                $index->valutazione_globale = get_string('light_green', 'mod_coripodatacollection');
            else
                $index->valutazione_globale = get_string('yellow', 'mod_coripodatacollection');

            $DB->update_record('coripodatacollection_indici_valutazione', $index);

        }
    }
}

/**
 * Funzione che dati gli indici di un alunno, la media e la stddev, esegue una valutazione preliminare circa gli indici di
 * correttezza e rapidità della lettura e correttezza della scrittura.
 *
 * @param object $index gli indici calcolati per un alunno
 * @param object $media medie degli indici sulla classe o globali
 * @param object $stddev deviazioni standard degli indici sulla classe o globali
 *
 * @return string contenente rosso, giallo o verde in base alla classificazione calcolata
 */
function compute_preliminar_eval( $index, $media,  $stddev) : string {

    global $DB;
    $original_res = $DB->get_record('coripodatacollection_risultati', ['id' => $index->risultato_originale]);

    $ind = [];
    $ind[0] = compute_value_color($index->lettura_correttezza, $media->lettura_correttezza, $stddev->lettura_correttezza);
    $ind[1] = compute_value_color($index->lettura_rapidita, $media->lettura_rapidita, $stddev->lettura_rapidita);
    $ind[2] = compute_value_color($index->scrittura_correttezza, $media->scrittura_correttezza, $stddev->scrittura_correttezza);

    $green_count = count(array_filter($ind, fn($i) => $i === "green"));
    $yellow_count = count(array_filter($ind, fn($i) => $i === "yellow"));
    $red_count = count(array_filter($ind, fn($i) => $i === "red"));

    if ($original_res->proveaccessorie == 'Sì' || $red_count == 3)
        return get_string('red', 'mod_coripodatacollection');
    elseif ($green_count == 3 || ($green_count == 2 && $yellow_count == 1))
        return get_string('green', 'mod_coripodatacollection');
    else
        return get_string('yellow', 'mod_coripodatacollection');
}

/**
 * Funzione che dati gli indici di un alunno, la media e la stddev, esegue una valutazione preliminare circa gli indici di
 * correttezza e rapidità della lettura e correttezza della scrittura.
 *
 * @param object $index gli indici calcolati per un alunno
 * @param object $media medie degli indici sulla classe o globali
 * @param object $stddev deviazioni standard degli indici sulla classe o globali
 *
 * @return string contenente rosso, giallo o verde in base alla classificazione calcolata
 */
function compute_gravity_profile($index) : string {

    global $DB;
    $original_res = $DB->get_record('coripodatacollection_risultati', ['id' => $index->risultato_originale]);
    $media =$DB->get_record_sql('SELECT AVG(lettura_sillabe_cv_errori) as lettura_sillabe_cv_errori,
                                            AVG(scrittura_sillabe_cv_errori) as scrittura_sillabe_cv_errori 
                                          FROM mdl_coripodatacollection_risultati
                                          WHERE includi_calcolo="Sì" AND id in (
                                                    SELECT risultato_originale
                                                    FROM mdl_coripodatacollection_indici_valutazione
                                                    WHERE (valutazione_classe="Rosso" OR valutazione_classe="Giallo")
                                                        AND erogazione= "'. $index->erogazione .'"
                                                        AND periodo="' . $index->periodo . '"
                                                )');
    $stddev =$DB->get_record_sql('SELECT STDDEV(lettura_sillabe_cv_errori) as lettura_sillabe_cv_errori,
                                             STDDEV(scrittura_sillabe_cv_errori) as scrittura_sillabe_cv_errori 
                                          FROM mdl_coripodatacollection_risultati
                                          WHERE includi_calcolo="Sì" AND id in (
                                                    SELECT risultato_originale
                                                    FROM mdl_coripodatacollection_indici_valutazione
                                                    WHERE (valutazione_classe="Rosso" OR valutazione_classe="Giallo")
                                                        AND erogazione= "'. $index->erogazione .'"
                                                        AND periodo="' . $index->periodo . '"
                                                )');


    if ($original_res->proveaccessorie == 'Sì')
        return get_string('red', 'mod_coripodatacollection');

    $profile1 = $index->lettura_media <= 0.2
        && $original_res->scrittura_sillabe_cv_errori >= $media->scrittura_sillabe_cv_errori + 2 * $stddev->scrittura_sillabe_cv_errori;

    $profile3 = (0.2 <= $index->lettura_media && $index->lettura_media <= 0.4)
            && $original_res->lettura_sillabe_cv_errori >= $media->lettura_sillabe_cv_errori + 2 * $stddev->lettura_sillabe_cv_errori
            && $original_res->scrittura_sillabe_cv_errori >= $media->scrittura_sillabe_cv_errori + 2 * $stddev->scrittura_sillabe_cv_errori;
    if ( $profile1 || $profile3 )
        return get_string('red', 'mod_coripodatacollection');
    else
        return get_string('green', 'mod_coripodatacollection');

}

/**
 * Funzione che calcola il colore (semaforo) per il valore dato in input. La formula che viene implementata restituisce:
 *  - verde se $val < $media + 1.5 * $stddev
 *  - giallo se $val < $media + 1.8 * $stddev
 *  - rosso altrimenti
 *
 * Nel caso si impostasse inverted a true, il calcolo viene cambiato a
 *  - verde se $val > $media - 1.5 * $stddev
 *  - giallo se $val > $media - 1.8 * $stddev
 *  - rosso altrimenti
 * */
function compute_value_color( $val, $media, $stddev, $inverted = false ) : string {

    if (!$inverted) {
        if ($val < $media + 1.5 * $stddev) {
            $color = 'green';
        } else if ($val < $media + 1.8 * $stddev) {
            $color = 'yellow';
        } else {
            $color = 'red';
        }
    } else {
        if ($val > $media - 1.5 * $stddev) {
            $color = 'green';
        } else if ($val > $media - 1.8 * $stddev) {
            $color = 'yellow';
        } else {
            $color = 'red';
        }
    }
    return $color;
}



/**
 * Funzione che serve per controllare se di una determinata classe ci sono dei risultati mancanti.
 *
 * @param int $classid id della classe di cui controllare i risultati
 * @param string $periodo il periodo pre o post rinforozo di cui controllare i risultati. Deve essere una stringa
 * equivalente a 'prerinforzo' oppure 'postrinforzo' per evitare che restituisca errori.
 * @return bool Ritornerà vero se vi saranno dei risultati mancanti, altrimenti falso.
 * @throws dml_exception
 */
function results_missing($classid, $periodo) : bool {

    global $DB;
    $risultati = $DB->get_records_sql('SELECT DISTINCT res.* 
                                            FROM {coripodatacollection_risultati} as res
                                            JOIN {coripodatacollection_class_students} as class ON res.classe = class.classid 
                                            WHERE res.classe = ' . $classid . ' 
                                                AND res.periodo = "' . $periodo . '"
                                                AND class.consenso = 1
                                                AND class.studentid = res.alunno');


    foreach ($risultati as $res) {
        if (!$DB->record_exists('coripodatacollection_class_students',
                ['studentid' => $res->alunno, 'classid' => $classid])) {
            continue;
        }
        $cls_student = $DB->get_record('coripodatacollection_class_students',
                ['studentid' => $res->alunno, 'classid' => $classid]);
        if ($cls_student->consenso == 0) {
            continue;
        }
        if (is_null($res->metodo_didattico) && $periodo=='prerinforzo') {
            return true;
        }
        if ($res->inserimento_parziale == 'Sì') {
            continue;
        }
        if ($res->proveaccessorie == 'Sì' && $periodo=='prerinforzo') {
            if (is_null($res->metafonologia_fusione) || is_null($res->metafonologia_analisi_cv) || is_null($res->metafonologia_analisi_cvc)
                    || is_null($res->metafonologia_segment) || is_null($res->metafonologia_segment_gruppo)) {
                return true;
            }
        } else {
            foreach ($res as $key => $val) {

                if ( $key == 'id' || $key == 'erogazione' || $key == 'classe' || $key == 'periodo' ||  $key == 'alunno'
                        || $key == 'includi_calcolo' || $key == 'difficolta_prerinforzo' || $key == 'inserimento_parziale'
                        || $key == 'proveaccessorie' || str_starts_with($key, 'metafonologia')) {
                    continue;
                }
                if ( $periodo == 'prerinforzo') {
                    if ( $key == 'lettura_modalita' || $key == 'lettura_parolecons_tempo' || $key == 'lettura_parolecons_errori'
                         || $key == 'scrittura_parolecons_errori' || $key == 'scrittura_paroleort_errori' || $key == 'matematica_trasforma_cifre_errori') {
                        continue;
                    }
                }

                if ( $periodo == 'postrinforzo') {
                    if ( $key == 'lettura_modalita' ||  $key == 'lettura_fonemi_tempo'
                            || $key == 'lettura_fonemi_errori' || $key == 'metodo_didattico'
                            || $key == 'matematica_ricquantita_tempo' || $key == 'matematica_ricquantita_errori') {
                        continue;
                    }
                }

                if (is_null($val)) {
                    return true;
                }
            }
        }
    }
    return false;

}

/**
 * Funzione che serve per contare di una determinata classe quanti risultati sono stati inseriti.
 *
 * @param int $classid id della classe di cui controllare i risultati
 * @param string $periodo il periodo pre o post rinforozo di cui controllare i risultati. Deve essere una stringa
 * equivalente a 'prerinforzo' oppure 'postrinforzo' per evitare che restituisca errori.
 * @return bool Ritornerà vero se vi saranno dei risultati mancanti, altrimenti falso.
 * @throws dml_exception
 */
function results_counting($classid, $periodo) : int {

    global $DB;
    $risultati = $DB->get_records_sql('SELECT DISTINCT res.* 
                                            FROM {coripodatacollection_risultati} as res
                                            JOIN {coripodatacollection_class_students} as class ON res.classe = class.classid 
                                            WHERE res.classe = ' . $classid . ' 
                                                AND res.periodo = "' . $periodo . '"
                                                AND class.consenso = 1
                                                AND class.studentid = res.alunno');

    foreach ($risultati as $res) {
        $is_all_null = true;
        foreach ($res as $key => $value) {
            if ($key == 'metodo_didattico' || $key == 'inserimento_parziale' || $key == 'difficolta_prerinforzo'
                    || $key == 'proveaccessorie' || str_starts_with($key, 'lettura') || str_starts_with($key, 'scrittura')
                    || str_starts_with($key, 'matematica') || str_starts_with($key, 'metafonologia')) {
                if (!is_null($res->$key)) {
                    $is_all_null = false;
                }
            }
        }
        if ($is_all_null) {
            unset($risultati[$res->id]);
        }
    }

    return count($risultati);
}

/**
 * Funzione che serve per controllare se per un dato indice, i valori sono totalmente completi oppure se ne manca qualcuno.
 * Ritorna true se alcunidei risultati che servono per calcolare l'indice manca
 *
 * @param int $index_name nome dell'indice da controllare
 * @param string $periodo risultati grezzi
 */
function result_missing_for_index( $index_name, $result ) : bool {

    switch ($index_name) {
        case 'lettura_correttezza':
            return is_null($result->lettura_fonemi_errori) || is_null($result->lettura_sillabe_cv_errori) ||
                    is_null($result->lettura_sillabe_cvc_errori) || is_null($result->lettura_piane_errori) ||
                    is_null($result->lettura_nonparole_errori);
        case 'lettura_rapidita':
            return is_null($result->lettura_fonemi_tempo) || is_null($result->lettura_sillabe_cv_tempo) ||
                    is_null($result->lettura_sillabe_cvc_tempo) || is_null($result->lettura_piane_tempo) ||
                    is_null($result->lettura_nonparole_tempo);
        case 'lettura_sublessicale':
            return is_null($result->lettura_sillabe_cv_tempo) || is_null($result->lettura_sillabe_cvc_tempo) ||
                    is_null($result->lettura_nonparole_tempo);
        case 'lettura_lessicale':
            return is_null($result->lettura_piane_tempo);
        case 'lettura_media':
            return is_null($result->lettura_sillabe_cv_tempo) || is_null($result->lettura_nonparole_tempo) ||
                    is_null($result->lettura_sillabe_cvc_tempo) || is_null($result->lettura_piane_tempo) ;
        case 'scrittura_correttezza':
            return is_null($result->scrittura_sillabe_cv_errori) || is_null($result->scrittura_sillabe_cvc_errori) ||
                    is_null($result->scrittura_piane_errori) || is_null($result->scrittura_nonparole_errori);
        case 'matematica_correttezza_lettura':
            return is_null($result->matematica_letturan_errori) || is_null($result->matematica_dettaton_errori);
        case 'matematica_correttezza_enumerazione':
            return is_null($result->matematica_enumavanti_errori) || is_null($result->matematica_enumindietro_errori);
        case 'matematica_correttezza_decodifica':
            return is_null($result->matematica_ricquantita_errori) || is_null($result->matematica_confronto_errori) ||
                    is_null($result->matematica_orddalpiupiccolo_errori) || is_null($result->matematica_orddalpiugrande_errori);
        case 'matematica_correttezza_calcolo':
            return is_null($result->matematica_addizioni_errori) || is_null($result->matematica_sottrazioni_errori);
    }
    return false;
}




/**
 * Disiscrive l'utente dal corso indicato
 *
 * @param $courseid int id del corso
 * @param $userid int id dell'utente
 * @return void
 * @throws dml_exception
 */
function unenrol_user($courseid, $userid) : void{

    global $DB;

    $enrol = $DB->get_record_sql('SELECT * FROM {enrol} WHERE enrol = "manual" AND courseid = ' . $courseid);
    $DB->delete_records('user_enrolments', ['userid' => $userid, 'enrolid' => $enrol->id]);

}


/**
 * This function just return an associative array in which all the keys have the same name of the columns in the table
 * mdl_coripodatacollection_risultati, omitting the first 4 that are the 'id', 'classe', 'periodo', 'alunno' values.
 *
 * The purpose of usage of this function is getting an array in which all the values for each key are null. In this way if the
 * new results will have no values, they will be cancelled in the database when the save button will be pushed.
 *
 * @return array
 */
function result_array_init() {
    global $DB;
    $res = $DB->get_records_sql('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME="mdl_coripodatacollection_risultati"');
    $nuovorisultato = [];
    foreach ($res as $key=>$val) {
        if ($key != 'id' && $key != 'classe' && $key != 'periodo' && $key != 'alunno')
            $nuovorisultato[$key] = null;
    }

    return $nuovorisultato;
}



/**
 * This function is used for some test, for writing content of some variables during the execution. The writing on an existing
 * file,
 * will add the content at the end.
 *
 * @param $log string is the log to write in the file
 * @param $file_name string is a string containing the path and the name of the file in which write the log.If the file do not
 *         exists, it will create it
 */
function write_log($log, $file_name): void {

    // Apri il file in modalità scrittura ("w" crea un file nuovo o sovrascrive quello esistente)
    $handle = fopen($file_name, "a");

    $log = '
    
    [' . date("Y-m-d H:i:s") . ']
    ' . $log;

    if ($handle) {
        fwrite($handle, $log);
        fclose($handle);
    }

}



/**
 * Send the new password to the user via email.
 *
 * @param stdClass $user A {@link $USER} object
 * @param bool $fasthash If true, use a low cost factor when generating the hash for speed.
 * @return bool|string Returns "true" if mail was sent OK and "false" if there was an error
 */
function send_newuser_mail($user, $password) {
    global $CFG;

    // We try to send the mail in language the user understands,
    // unfortunately the filter_string() does not support alternative langs yet
    // so multilang will not work properly for site->fullname.
    $lang = empty($user->lang) ? get_newuser_language() : $user->lang;

    $site  = get_site();

    $supportuser = core_user::get_support_user();

    update_internal_user_password($user, $password);

    $a = new stdClass();
    $a->firstname   = fullname($user, true);
    $a->sitename    = format_string($site->fullname);
    $a->username    = $user->username;
    $a->newpassword = $password;
    $a->link        = $CFG->wwwroot .'/login/?lang='.$lang;
    $a->signoff     = generate_email_signoff();

    $message = (string)new lang_string('newusernewpasswordtext', '', $a, $lang);

    $subject = format_string($site->fullname) .': '. (string)new lang_string('newusernewpasswordsubj', '', $a, $lang);

    // Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
    return email_to_user($user, $supportuser, $subject, $message);

}



/**
 * Get an associative array of all the languages
 *
 * @return array
 */
function get_languages_list() {

    $linguearray = [
            'Select' => get_string('select_languages', 'mod_coripodatacollection'),
            get_string('italiano', 'mod_coripodatacollection') => get_string('italiano', 'mod_coripodatacollection'),
            get_string('inglese', 'mod_coripodatacollection') => get_string('inglese', 'mod_coripodatacollection'),
            get_string('francese', 'mod_coripodatacollection') => get_string('francese', 'mod_coripodatacollection'),
            get_string('spagnolo', 'mod_coripodatacollection') => get_string('spagnolo', 'mod_coripodatacollection'),
            get_string('portoghese', 'mod_coripodatacollection') => get_string('portoghese', 'mod_coripodatacollection'),
            get_string('tedesco', 'mod_coripodatacollection') => get_string('tedesco', 'mod_coripodatacollection'),
            get_string('polacco', 'mod_coripodatacollection') => get_string('polacco', 'mod_coripodatacollection'),
            get_string('albanese', 'mod_coripodatacollection') => get_string('albanese', 'mod_coripodatacollection'),
            get_string('rumeno', 'mod_coripodatacollection') => get_string('rumeno', 'mod_coripodatacollection'),
            get_string('ucraino', 'mod_coripodatacollection') => get_string('ucraino', 'mod_coripodatacollection'),
            get_string('russo', 'mod_coripodatacollection') => get_string('russo', 'mod_coripodatacollection'),
            get_string('swahili', 'mod_coripodatacollection') => get_string('swahili', 'mod_coripodatacollection'),
            get_string('huasa', 'mod_coripodatacollection') => get_string('huasa', 'mod_coripodatacollection'),
            get_string('igbo', 'mod_coripodatacollection') => get_string('igbo', 'mod_coripodatacollection'),
            get_string('yoruba', 'mod_coripodatacollection') => get_string('yoruba', 'mod_coripodatacollection'),
            get_string('berbero', 'mod_coripodatacollection') => get_string('berbero', 'mod_coripodatacollection'),
            get_string('oromo', 'mod_coripodatacollection') => get_string('oromo', 'mod_coripodatacollection'),
            get_string('amarico', 'mod_coripodatacollection') => get_string('amarico', 'mod_coripodatacollection'),
            get_string('arabo', 'mod_coripodatacollection') => get_string('arabo', 'mod_coripodatacollection'),
            get_string('srilankese', 'mod_coripodatacollection') => get_string('srilankese', 'mod_coripodatacollection'),
            get_string('hindi', 'mod_coripodatacollection') => get_string('hindi', 'mod_coripodatacollection'),
            get_string('punjabi', 'mod_coripodatacollection') => get_string('punjabi', 'mod_coripodatacollection'),
            get_string('urdu', 'mod_coripodatacollection') => get_string('urdu', 'mod_coripodatacollection'),
            get_string('mandarino', 'mod_coripodatacollection') => get_string('mandarino', 'mod_coripodatacollection'),
            get_string('cantonese', 'mod_coripodatacollection') => get_string('cantonese', 'mod_coripodatacollection'),
            get_string('african_language', 'mod_coripodatacollection') => get_string('african_language', 'mod_coripodatacollection'),
            get_string('asian_language', 'mod_coripodatacollection') => get_string('asian_language', 'mod_coripodatacollection'),
            get_string('indoeurpean_language', 'mod_coripodatacollection') => get_string('indoeurpean_language', 'mod_coripodatacollection'),
            get_string('notinlist_language', 'mod_coripodatacollection') => get_string('notinlist_language', 'mod_coripodatacollection'),
            'NoInfo' => get_string('noinfo', 'mod_coripodatacollection')
    ];

    return $linguearray;

}


/**
 * When called, generates a csv with all the info of the current erogation and save them into a csv_file
 */
function send_erogation_info_csv($course) {

    global $DB;

    $filename = "info_erogazione.csv";

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    fputcsv($output, [
            get_string('newinstitute_name', 'mod_coripodatacollection'),
            get_string('denominazioneplesso', 'mod_coripodatacollection'),
            get_string('year', 'mod_coripodatacollection'),
            get_string('class', 'mod_coripodatacollection'),
            get_string('numberstudents', 'mod_coripodatacollection'),
            get_string('numberstudents_registered', 'mod_coripodatacollection'),
            get_string('numberstudents_identity', 'mod_coripodatacollection'),
            get_string('numberstudents_consensus', 'mod_coripodatacollection'),
            get_string('pre-reinforce_results', 'mod_coripodatacollection'),
            get_string('post-reinforce_results', 'mod_coripodatacollection'),
            get_string('status', 'mod_coripodatacollection')
    ]);

    $data = [];
    $erogation = $DB->get_record('coripodatacollection_erogations', ['courseid' => $course->id]);
    $plessi = $DB->get_records_sql('SELECT plessi.id as idplesso, denominazioneistituto, denominazioneplesso
                                            FROM mdl_coripodatacollection_erogations as erog
                                            JOIN mdl_coripodatacollection_istituti_x_progetto_x_aa as iiaa 
                                                ON erog.id = iiaa.erogation
                                            JOIN mdl_coripodatacollection_istituti as istituti 
                                                ON istituti.id = iiaa.instituteid
                                            JOIN mdl_coripodatacollection_plessi as plessi 
                                                ON plessi.instituteid = istituti.id
                                            WHERE erog.courseid =' . $course->id);
    foreach ($plessi as $plesso) {
        $classes = $DB->get_records('coripodatacollection_classes',
                ['plesso' => $plesso->idplesso, 'erogazione' => $erogation->id]);

        foreach ($classes as $class) {

            $current_date = time();
            $fase_censimento = $erogation->start_censimento <= $current_date && $erogation->end_censimento >= $current_date;
            $fase_pre_rinforzo = $erogation->start_val_pre <= $current_date && $erogation->end_val_pre >= $current_date;
            $fase_post_rinforzo = $erogation->start_val_post <= $current_date && $erogation->end_val_post >= $current_date;

            $status = '';
            if ($fase_censimento) {
                $status = get_string('census', 'mod_coripodatacollection');
            } else if ($fase_pre_rinforzo) {
                $status = $class->completati_res_pre == 0 ?
                        get_string('insertion_results', 'coripodatacollection') :
                        get_string('insertion_completed', 'coripodatacollection');
            } else if ($fase_post_rinforzo) {
                $status = $class->completati_res_post == 0 ?
                        get_string('insertion_results', 'coripodatacollection'):
                        get_string('insertion_completed', 'coripodatacollection');
            }

            $data[] = [
                    $plesso->denominazioneistituto,
                    $plesso->denominazioneplesso,
                    $class->anno,
                    $class->classe,
                    $class->numerostudenti,
                    count($DB->get_records('coripodatacollection_class_students', ['classid' => $class->id])),
                    count($DB->get_records('coripodatacollection_class_students',
                            ['classid' => $class->id, 'carta_identita' => 1])),
                    count($DB->get_records('coripodatacollection_class_students',
                            ['classid' => $class->id, 'consenso' => 1])),
                    results_counting($class->id, 'prerinforzo'),
                    results_counting($class->id, 'postrinforzo'),
                    $status
            ];
        }

    }

    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

/**
 *
 * Given an array of arrays, transform it in a csv file and send it to the browser
 *
 * @param $array
 * @return void
 */
function send_csv($array, $filename, $separator=',') {

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    foreach ($array as $row) {
        fputcsv($output, array_map(fn($value) => mb_convert_encoding($value, 'UTF-8', 'auto'), $row), $separator);
    }
    fclose($output);
    if (isset($SESSION->csv_array)) {
        unset($SESSION->csv_array);
    }
    exit();

}



/**
 * When called, generates the html string for the table of all students in the erogation and return it.
 */
function send_erogation_students_csv($course) {

    global $DB;

    $table = new html_table();
    $htmlstringtable = '';

    $table->head = [
            get_string('info_alunno', 'mod_coripodatacollection'),
            get_string('results', 'mod_coripodatacollection'),
            get_string('indexes', 'mod_coripodatacollection'),
    ];
    $table->headspan = [ 16, 38, 10 ];
    $table->align = array_fill(0, count($table->head), 'center; border: 1px solid lightgrey;');
    $table->attributes = ['style' => 'display: none'];
    $table->id = 'table-infos-erogation';
    $htmlstringtable .= substr( $head = html_writer::table($table), 0, strpos($head, '</thead>'));

    $table = new html_table();
    $table->head = [
            '',
            '',
            get_string('reading', 'mod_coripodatacollection'),
            get_string('writing', 'mod_coripodatacollection'),
            get_string('math', 'mod_coripodatacollection'),
            get_string('accessories_tests', 'mod_coripodatacollection'),
            ''
    ];
    $table->headspan = [
            16,
            2,
            10, 4, 16, 6,
            10
    ];
    $table->align = array_fill(0, count($table->head), 'center; border: 1px solid lightgrey;');
    $htmlstringtable .= substr( $head = html_writer::table($table),
            $start = strpos($head, '<tr>'), strpos($head, '</thead>') - $start);

    $table = new \html_table();
    $table->head = [
            '', '', '',
            get_string('phonemes', 'mod_coripodatacollection'),
            get_string('syllables_cv', 'mod_coripodatacollection'),
            get_string('syllables_cvc', 'mod_coripodatacollection'),
            get_string('flat_words', 'mod_coripodatacollection'),
            get_string('nonwords', 'mod_coripodatacollection'),
            get_string('syllables_cv', 'mod_coripodatacollection'),
            get_string('syllables_cvc', 'mod_coripodatacollection'),
            get_string('flat_words', 'mod_coripodatacollection'),
            get_string('nonwords', 'mod_coripodatacollection'),
            get_string('reading_numbers', 'mod_coripodatacollection'),
            get_string('forward_enumeration', 'mod_coripodatacollection'),
            get_string('backward_enumeration', 'mod_coripodatacollection'),
            get_string('quantity_recognition', 'mod_coripodatacollection'),
            get_string('additions', 'mod_coripodatacollection'),
            get_string('subtractions', 'mod_coripodatacollection'),
            get_string('comparison', 'mod_coripodatacollection'),
            get_string('ascending_order', 'mod_coripodatacollection'),
            get_string('descending_order', 'mod_coripodatacollection'),
            get_string('numerical_dictation', 'mod_coripodatacollection'),
            '',
            get_string('syllables_fusion', 'mod_coripodatacollection'),
            get_string('cv_syllables_analyses', 'mod_coripodatacollection'),
            get_string('cvc_syllables_analyses', 'mod_coripodatacollection'),
            get_string('syllables_segmentation', 'mod_coripodatacollection'),
            get_string('consonant_groups_segmentation', 'mod_coripodatacollection'),
            ''
    ];
    $table->headspan = [
            16,
            1, 1,
            2, 2, 2, 2, 2,
            1, 1, 1, 1,
            2, 2, 2, 2, 2, 2,
            1, 1, 1, 1,
            1, 1, 1, 1, 1, 1,
            10];
    $table->align = array_fill(0, count($table->head), 'center; border: 1px solid lightgrey;');
    $htmlstringtable .= substr( $head = html_writer::table($table),
            $start = strpos($head, '<tr>'), strpos($head, '</thead>') - $start);

    $data = [
            get_string('code', 'mod_coripodatacollection'),
            get_string('istitutomenu', 'mod_coripodatacollection'),
            get_string('searchplesso_name', 'mod_coripodatacollection'),
            get_string('year', 'mod_coripodatacollection'),
            get_string('class', 'mod_coripodatacollection'),
            get_string('register_number', 'mod_coripodatacollection'),
            get_string('freq_year', 'mod_coripodatacollection'),
            get_string('consensus', 'mod_coripodatacollection'),
            get_string('born_in_italy', 'mod_coripodatacollection'),
            get_string('language_difficulty', 'mod_coripodatacollection'),
            get_string('home_language', 'mod_coripodatacollection'),
            get_string('nursery_school_freq', 'mod_coripodatacollection'),
            get_string('nursery_school_difficulty', 'mod_coripodatacollection'),
            get_string('difficulty_noted', 'mod_coripodatacollection'),
            get_string('centoquattro_law_table', 'mod_coripodatacollection'),
            get_string('centoquattro_problem_table', 'mod_coripodatacollection'),
            get_string('partial_result', 'mod_coripodatacollection'),
            get_string('didatic_method', 'mod_coripodatacollection')
    ];

    foreach ($table->headspan as $i => $val) {
        if ($i<3) {
            continue;
        } elseif ($i == 22) {
            $data[] = get_string('test_administration', 'mod_coripodatacollection');
        } elseif ($val == 10) {
            break;
        } elseif ($val == 1) {
            $data[] = get_string('errors', 'mod_coripodatacollection');
        } else {
            $data[] = get_string('time', 'mod_coripodatacollection');
            $data[] = get_string('errors', 'mod_coripodatacollection');
        }
    }

    $data[] = get_string('reading_correctness', 'mod_coripodatacollection');
    $data[] = get_string('reading_speed', 'mod_coripodatacollection');
    $data[] = get_string('writing_correctness', 'mod_coripodatacollection');
    $data[] = get_string('math_reading_correctness', 'mod_coripodatacollection');
    $data[] = get_string('math_enumeration_correctness', 'mod_coripodatacollection');
    $data[] = get_string('math_decoding_correctness', 'mod_coripodatacollection');
    $data[] = get_string('math_calculus_correctness', 'mod_coripodatacollection');
    $data[] = get_string('reading_sublex', 'mod_coripodatacollection');
    $data[] = get_string('reading_lex', 'mod_coripodatacollection');
    $data[] = get_string('reading_mean', 'mod_coripodatacollection');


    $table = new \html_table();
    $table->head = $data;
    $table->align = array_fill(0, count($table->head), 'center; border: 1px solid lightgrey;');
    $htmlstringtable .= substr( $head = html_writer::table($table),
            $start = strpos($head, '<tr>'), strpos($head, '</thead>') - $start);
    $htmlstringtable .= '</thead>';

    $n = count($table->head);
    $table = new \html_table();
    $table->align = array_fill(0, $n, 'center; border: 1px solid lightgrey;');

    $erogazione = $DB->get_record('coripodatacollection_erogations', ['courseid' => $course->id]);
    $periodo = time() < $erogazione->start_val_post? 'prerinforzo' : 'postrinforzo';

    $classes = $DB->get_records('coripodatacollection_classes', ['erogazione' => $erogazione->id]);
    foreach ($classes as $class) {

        $istituto = $DB->get_record('coripodatacollection_istituti', ['id' => $class->istituto]);
        $plesso = $DB->get_record('coripodatacollection_plessi', ['id' => $class->plesso]);

        $alunni = $DB->get_records('coripodatacollection_class_students', ['classid' => $class->id]);
        foreach ($alunni as $alunno) {

            $info_alunno = $DB->get_record('coripodatacollection_alunni', ['id' => $alunno->studentid]);
            $row = [
                $info_alunno->hash_code,
                $istituto->denominazioneistituto,
                $plesso->denominazioneplesso,
                $class->anno,
                $class->classe,
                $alunno->numeroregistro,
                $alunno->annofrequentazione,
                $alunno->consenso,
                $info_alunno->natoinitalia,
                $info_alunno->difficoltalinguaggio,
                $info_alunno->linguaparlatacasa,
                $info_alunno->frequenzascuolainfanzia,
                $info_alunno->difficoltascuolainfanzia,
                $info_alunno->notadifficolta,
                $info_alunno->leggecentoquattro,
                $info_alunno->problematicacentoquattro,
            ];

            $results = $DB->get_records('coripodatacollection_risultati',
                    ['alunno' => $alunno->studentid, 'erogazione' => $erogazione->id, 'classe' => $class->id]);
            foreach ($results as $res) {
                if ($res->periodo == $periodo) {

                    $row[] = $res->inserimento_parziale;
                    $row[] = $res->metodo_didattico;

                    $arrayvalutazione = get_object_vars($res);
                    foreach ($arrayvalutazione as $key => $val) {
                        if ($key == 'proveaccessorie' || str_starts_with($key, 'metafonologia')) {
                            unset($arrayvalutazione[$key]);
                            $arrayvalutazione[$key] = $val;
                        }
                    }

                    foreach ($arrayvalutazione as $key => $val) {
                        if ($key == 'id' || $key == 'classe' || $key == 'alunno' || $key == 'periodo' || $key == 'erogazione'
                                || $key == 'difficolta_prerinforzo' || $key == 'lettura_parolecons_tempo' || $key == 'lettura_parolecons_errori'
                                || $key == 'scrittura_parolecons_errori' || $key == 'scrittura_paroleort_errori' || $key == 'lettura_modalita'
                                || $key == 'inserimento_parziale' || $key == 'metodo_didattico' || $key == 'includi_calcolo') {
                            continue;
                        } else {
                            $row[] = $val;
                        }
                    }

                    $indexes = $DB->get_record('coripodatacollection_indici_valutazione', ['risultato_originale' => $res->id]);
                    if (!empty($indexes)) {

                        $row[] = $indexes->lettura_correttezza;
                        $row[] = $indexes->lettura_rapidita;
                        $row[] = $indexes->scrittura_correttezza;
                        $row[] = $indexes->matematica_correttezza_lettura;
                        $row[] = $indexes->matematica_correttezza_enumerazione;
                        $row[] = $indexes->matematica_correttezza_decodifica;
                        $row[] = $indexes->matematica_correttezza_calcolo;
                        $row[] = $indexes->lettura_sublessicale;
                        $row[] = $indexes->lettura_lessicale;
                        $row[] = $indexes->lettura_media;

                    }
                }
            }
            $tablerow = new \html_table_row($row);
            $table->data[] = $tablerow;
        }
    }
    $htmlstringtable .= substr( $body = html_writer::table($table), strpos($body, '<tbody>'),
            strpos($body, '</tbody>') + 9);

    return $htmlstringtable;

}



function getstudent_colored_cell($student, $erogation) {

    global $DB;

    $indice = $DB->get_record_sql('
                    SELECT * 
                    FROM mdl_coripodatacollection_indici_valutazione 
                    WHERE periodo="prerinforzo" and erogazione = ' . $erogation->id . ' and alunno = ' . $student->id);
    $classe = $DB->get_record('coripodatacollection_classes', ['id' => $indice->classe]);
    $istituto = $DB->get_record('coripodatacollection_istituti', ['id' => $classe->istituto]);

    $cel = new html_table_cell();

    $zona = '←';
    if ($istituto->zona == get_string('north', 'mod_coripodatacollection'))
        $zona = '↑';
    if ($istituto->zona == get_string('south', 'mod_coripodatacollection'))
        $zona = '↓';
    if ($istituto->zona == get_string('east', 'mod_coripodatacollection'))
        $zona = '→';
    if ($istituto->zona == get_string('west', 'mod_coripodatacollection'))
        $zona = '←';

    $cel->text = $student->hash_code . '  ' . $zona;

    if ($indice->valutazione_globale == get_string('dark_green', 'mod_coripodatacollection'))
        $color = 'index-color-green';
    if ($indice->valutazione_globale == get_string('yellow', 'mod_coripodatacollection'))
        $color = 'index-color-yellow';
    if ($indice->valutazione_globale == get_string('red', 'mod_coripodatacollection'))
        $color = 'index-color-red';


    $cel->attributes = ['class' => $color ?? ''];

    return $cel;

}



/**
 * Function that given an associative array and two keys expressed as strings, return the same array with the two
 * elements in the specified positions switched
 */
function swapAssociativeKeys(array $array, string $key1, string $key2): array {
    $pairs = [];
    foreach ($array as $k => $v) {
        $pairs[] = [$k, $v];
    }

    $index1 = $index2 = null;
    foreach ($pairs as $i => [$k, $v]) {
        if ($k === $key1) $index1 = $i;
        if ($k === $key2) $index2 = $i;
    }

    if ($index1 === null || $index2 === null) return $array;

    $tmp = $pairs[$index1];
    $pairs[$index1] = $pairs[$index2];
    $pairs[$index2] = $tmp;

    $newArr = [];
    foreach ($pairs as [$k, $v]) {
        $newArr[$k] = $v;
    }

    return $newArr;
}



/**
 * Function that given an erogation id and an array containing institutes objects (each element must contain an object with id
 * element), return a string of comma separated names of the teachers that are registered for the current erogation for those
 * institutes.
 */
function get_istitutes_erogation_teachers(int $erogationid, array $institutes) : string {

    global $DB;

    $return_string_array = [];

    foreach ($institutes as $institute) {

        $classes = $DB->get_records('coripodatacollection_classes',
                ['erogazione' => $erogationid, 'istituto' => $institute->id]);

        foreach ($classes as $class) {

            $class_admins = $DB->get_records('coripodatacollection_classadmin', ['classid' => $class->id]);
            foreach ($class_admins as $class_admin) {
                $user = $DB->get_record('user', ['id'=> $class_admin->userid]);
                $user_name = $user->lastname . ' ' . $user->firstname;
                if (!in_array($user_name, $return_string_array))
                    $return_string_array[] = $user_name;
            }

        }
    }
    return implode(',', $return_string_array);
}
