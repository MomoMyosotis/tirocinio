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
 * Prints an instance of mod_coripodatacollection.
 *
 * @package     mod_coripodatacollection
 * @copyright   2024 Cordioli Davide cordiolidavide1@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require "$CFG->libdir/tablelib.php";

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$c = optional_param('c', 0, PARAM_INT);

// Page to visualize.
$page = optional_param('page', '', PARAM_TEXT);

if ($id) {
    $cm = get_coursemodule_from_id('coripodatacollection', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('coripodatacollection', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('coripodatacollection', ['id' => $c], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $moduleinstance->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('coripodatacollection', $moduleinstance->id,
            $course->id, false, MUST_EXIST);
}

$context = context_system::instance();
require_login($course, true, $cm);
require_capability('mod/coripodatacollection:evaluator', $context);
require_capability('mod/data:viewentry', $context);

$modulecontext = context_module::instance($cm->id);

$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
$PAGE->add_body_class('wide');

$page = optional_param('page', 'classes', PARAM_TEXT);
$classid = optional_param('classid', -1, PARAM_INT);




$urlparam = ['id' => $cm->id, 'page' => $page];
if ($classid != -1) {
    $urlparam['classid'] = $classid;
}
$PAGE->set_url('/mod/coripodatacollection/viewevaluator.php', $urlparam);
if ($page == 'alunni' || $page == 'primevalutazioni' || $page == 'ultimevalutazioni' ||  $page == 'newalunno'
        || $page == 'modalunno' || $page == '') {
    $node = $PAGE->secondarynav->add(get_string('classi', 'mod_coripodatacollection'));
    $node->isactive = false;
    $node->action = new moodle_url('/mod/coripodatacollection/viewevaluator.php', ['id' => $cm->id, 'page' => 'classes']);

    $node = $PAGE->secondarynav->add(get_string('alunni', 'mod_coripodatacollection'));
    $node->isactive = $page == 'alunni' || $page == 'newalunno' || $page == 'modalunno';
    $node->action = new moodle_url('/mod/coripodatacollection/viewevaluator.php',
            ['id' => $cm->id, 'page' => 'alunni', 'classid' => $classid]);

    $node = $PAGE->secondarynav->add(get_string('primevalutazioni', 'mod_coripodatacollection'));
    $node->isactive = $page == 'primevalutazioni';
    $node->action = new moodle_url('/mod/coripodatacollection/viewevaluator.php',
            ['id' => $cm->id, 'page' => 'primevalutazioni', 'classid' => $classid]);

    $node = $PAGE->secondarynav->add(get_string('ultimevalutazioni', 'mod_coripodatacollection'));
    $node->isactive = $page == 'ultimevalutazioni';
    $node->action = new moodle_url('/mod/coripodatacollection/viewevaluator.php',
            ['id' => $cm->id, 'page' => 'ultimevalutazioni', 'classid' => $classid]);
} else {
    $node = $PAGE->secondarynav->add(get_string('classi', 'mod_coripodatacollection'));
    $node->isactive = true;
    $node->action = new moodle_url('/mod/coripodatacollection/viewevaluator.php', ['id' => $cm->id, 'page' => 'classes']);
}


$class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);

if ($page == 'global-calculus' || $page == 'global-calculus-no-out') {

    $periodo = required_param('period', PARAM_TEXT);
    $redirect_url = new moodle_url('/mod/coripodatacollection/viewevaluator.php', ['id' => $id, 'page' => 'classes']);
    if ($periodo != 'prerinforzo' && $periodo != 'postrinforzo')
        redirect($redirect_url);

    $erogation = $DB->get_record('coripodatacollection_erogations', ['courseid' => $course->id]);

    $outlier = $page != 'global-calculus-no-out';

    calcolo_mediaindici_erogazione($erogation->id, $periodo, $outlier);
    calcolo_stddevindici_erogazione($erogation->id, $periodo, $outlier);
    compute_finalphase_eval($erogation->id, $periodo);

    if ($periodo == 'prerinforzo') {
        $erogation->calcolo_globale_pre = 1;
        $erogation->outlier_pre = $outlier ? 1 : 0;
    } else {
        $erogation->calcolo_globale_post = 1;
        $erogation->outlier_post = $outlier ? 1 : 0;
    }
    $DB->update_record('coripodatacollection_erogations', $erogation);
    redirect($redirect_url);

}

if ($page == 'primevalutazioni') {


    $actualpage = new moodle_url('/mod/coripodatacollection/viewevaluator.php',
            ['id' => $id, 'page' => 'primevalutazioni', 'classid' => $classid]);
    $evaluationtable = new \mod_coripodatacollection\forms\prova_evalview_form($actualpage,
            ['idclasse' => $classid, 'onlyview' => true, 'table' => 'pre', 'anonym' => true, 'export' => true, 'evaluator' => true]);

    if ($data = $evaluationtable->get_data()) {
        foreach ($data as $d) {
            $res = $DB->get_record('coripodatacollection_risultati', ['id' => $d->id]);
            $res->includi_calcolo = $d->includi_calcolo;
            $DB->update_record('coripodatacollection_risultati', $res);
        }

        $outliers = optional_param('submitbutton1', null, PARAM_ALPHA);

        calcolo_mediares_classe($classid, 'prerinforzo', is_null($outliers));
        calcolo_stddevres_classe($classid, 'prerinforzo', is_null($outliers));
        calcolo_indici_alunno($classid, 'prerinforzo');
        calcolo_mediaindici_classe($classid, 'prerinforzo', is_null($outliers));
        calcolo_stddevindici_classe($classid, 'prerinforzo', is_null($outliers));
        compute_phaseone_eval($classid, 'prerinforzo');

        $class->statistichepre = 1;
        $class->risultatipre = 0;
        $class->valutazione_classe_pre = 0;
        $class->outlier_pre = is_null($outliers) ? 1 : 0;

        $risultati = $DB->get_records_sql('SELECT * FROM {coripodatacollection_risultati} 
                                            WHERE classe = ' . $classid . ' AND periodo = "prerinforzo"');
        if (!empty($risultati)) {
            $DB->update_record('coripodatacollection_classes', $class);
        }

        redirect($actualpage);
    }

    if ($class->statistichepre == 1) {

        $statsviewtable = new \mod_coripodatacollection\forms\statsview_form($actualpage,
                ['classid' => $classid, 'display' => 'classe', 'periodo' => 'prerinforzo']);

        if ($data = $statsviewtable->get_data()) {
            foreach ($data as $d) {
                $indici = $DB->get_record('coripodatacollection_indici_valutazione', ['id' => $d->id]);
                $indici->valutazione_classe = $d->valutazione_classe;
                $indici->nota_specialistica = $d->nota_specialistica;
                $DB->update_record('coripodatacollection_indici_valutazione', $indici);
                $class->valutazione_classe_pre = 0;
                $DB->update_record('coripodatacollection_classes', $class);
            }
            redirect($actualpage);
        }

        $close_eval = optional_param('close_eval', 0, PARAM_INT);
        if ($close_eval == 1) {

            $index_evaluation = $DB->get_records_sql('SELECT * 
                                                          FROM mdl_coripodatacollection_indici_valutazione
                                                          WHERE classe= ' . $class->id . ' AND periodo ="prerinforzo"');

            $allinserted = true;
            foreach ($index_evaluation as $ind) {
                if (is_null($ind->valutazione_classe) || $ind->valutazione_classe == '' )
                    $allinserted = false;
            }
            if ($allinserted) {
                $class->valutazione_classe_pre = 1;
                $DB->update_record('coripodatacollection_classes', $class);
                redirect($actualpage);
            }
        }

    }

    $erogation = $DB->get_record('coripodatacollection_erogations', ['id' => $class->erogazione]);
    if ($class->statistichepre == 1 && $class->valutazione_classe_pre == 1 && $erogation->calcolo_globale_pre) {
        $statsviewtable_globale = new \mod_coripodatacollection\forms\statsview_form($actualpage,
                ['classid' => $classid, 'display' => 'global', 'periodo' => 'prerinforzo']);

        if ($data = $statsviewtable_globale->get_data()) {
            foreach ($data as $d) {
                $indici = $DB->get_record('coripodatacollection_indici_valutazione', ['id' => $d->id]);
                $indici->valutazione_globale = $d->valutazione_globale;
                $indici->nota_specialistica = $d->nota_specialistica;
                $DB->update_record('coripodatacollection_indici_valutazione', $indici);
                $class->valutazione_globale_pre = 0;
                $DB->update_record('coripodatacollection_classes', $class);
            }
            redirect($actualpage);
        }

        $close_global = optional_param('close_global', 0, PARAM_INT);
        if ($close_global == 1) {

            $index_evaluation = $DB->get_records_sql('SELECT * 
                                                          FROM mdl_coripodatacollection_indici_valutazione
                                                          WHERE classe= ' . $class->id . ' AND periodo ="prerinforzo"
                                                          AND valutazione_classe <> "Verde"');

            $allinserted_global = true;
            foreach ($index_evaluation as $ind) {
                if (is_null($ind->valutazione_globale) || $ind->valutazione_globale == '' )
                    $allinserted_global = false;
            }
            if ($allinserted_global) {
                $class->valutazione_globale_pre = 1;
                $DB->update_record('coripodatacollection_classes', $class);
                redirect($actualpage);
            }
        }
    }
}


if ($page == 'ultimevalutazioni') {


    $actualpage = new moodle_url('/mod/coripodatacollection/viewevaluator.php',
            ['id' => $id, 'page' => 'ultimevalutazioni', 'classid' => $classid]);
    $evaluationtable = new \mod_coripodatacollection\forms\prova_evalview_form($actualpage,
            ['idclasse' => $classid, 'onlyview' => true, 'table' => 'post', 'anonym' => true, 'export' => true, 'evaluator' => true]);

    if ($data = $evaluationtable->get_data()) {
        foreach ($data as $d) {
            $res = $DB->get_record('coripodatacollection_risultati', ['id' => $d->id]);
            $res->includi_calcolo = $d->includi_calcolo;
            $DB->update_record('coripodatacollection_risultati', $res);
        }

        $outliers = optional_param('submitbutton1', null, PARAM_ALPHA);

        calcolo_mediares_classe($classid, 'postrinforzo', is_null($outliers));
        calcolo_stddevres_classe($classid, 'postrinforzo', is_null($outliers));
        calcolo_indici_alunno($classid, 'postrinforzo');
        calcolo_mediaindici_classe($classid, 'postrinforzo', is_null($outliers));
        calcolo_stddevindici_classe($classid, 'postrinforzo', is_null($outliers));
        compute_phaseone_eval($classid, 'postrinforzo');

        $class->statistichepost = 1;
        $class->risultatipost = 0;
        $class->valutazione_classe_post = 0;
        $class->outlier_post = is_null($outliers) ? 1 : 0;

        $risultati = $DB->get_records_sql('SELECT * FROM {coripodatacollection_risultati} 
                                            WHERE classe = ' . $classid . ' AND periodo = "postrinforzo"');
        if (!empty($risultati)) {
            $DB->update_record('coripodatacollection_classes', $class);
        }

        redirect($actualpage);
    }

    if ($class->statistichepost == 1) {

        $statsviewtable = new \mod_coripodatacollection\forms\statsview_form($actualpage,
                ['classid' => $classid, 'display' => 'classe', 'periodo' => 'postrinforzo']);

        if ($data = $statsviewtable->get_data()) {
            foreach ($data as $d) {
                $indici = $DB->get_record('coripodatacollection_indici_valutazione', ['id' => $d->id]);
                $indici->valutazione_classe = $d->valutazione_classe;
                $indici->nota_specialistica = $d->nota_specialistica;
                $DB->update_record('coripodatacollection_indici_valutazione', $indici);
                $class->valutazione_classe_post = 0;
                $DB->update_record('coripodatacollection_classes', $class);
            }
            redirect($actualpage);
        }

        $close_eval = optional_param('close_eval', 0, PARAM_INT);
        if ($close_eval == 1) {

            $index_evaluation = $DB->get_records_sql('SELECT * 
                                                          FROM mdl_coripodatacollection_indici_valutazione
                                                          WHERE classe= ' . $class->id . ' AND periodo ="postrinforzo"');

            $allinserted = true;
            foreach ($index_evaluation as $ind) {
                if (is_null($ind->valutazione_classe) || $ind->valutazione_classe == '' )
                    $allinserted = false;
            }
            if ($allinserted) {
                $class->valutazione_classe_post = 1;
                $DB->update_record('coripodatacollection_classes', $class);
                redirect($actualpage);
            }
        }

    }

    $erogation = $DB->get_record('coripodatacollection_erogations', ['id' => $class->erogazione]);
    if ($class->statistichepost == 1 && $class->valutazione_classe_post == 1 && $erogation->calcolo_globale_post) {
        $statsviewtable_globale = new \mod_coripodatacollection\forms\statsview_form($actualpage,
                ['classid' => $classid, 'display' => 'global', 'periodo' => 'postrinforzo']);

        if ($data = $statsviewtable_globale->get_data()) {
            foreach ($data as $d) {
                $indici = $DB->get_record('coripodatacollection_indici_valutazione', ['id' => $d->id]);
                $indici->valutazione_globale = $d->valutazione_globale;
                $indici->nota_specialistica = $d->nota_specialistica;
                $DB->update_record('coripodatacollection_indici_valutazione', $indici);
                $class->valutazione_globale_post = 0;
                $DB->update_record('coripodatacollection_classes', $class);
            }
            redirect($actualpage);
        }

        $close_global = optional_param('close_global', 0, PARAM_INT);
        if ($close_global == 1) {

            $index_evaluation = $DB->get_records_sql('SELECT * 
                                                          FROM mdl_coripodatacollection_indici_valutazione
                                                          WHERE classe= ' . $class->id . ' AND periodo ="postrinforzo"
                                                          AND valutazione_classe <> "Verde"');

            $allinserted_global = true;
            foreach ($index_evaluation as $ind) {
                if (is_null($ind->valutazione_globale) || $ind->valutazione_globale == '' )
                    $allinserted_global = false;
            }
            if ($allinserted_global) {
                $class->valutazione_globale_post = 1;
                $DB->update_record('coripodatacollection_classes', $class);
                redirect($actualpage);
            }
        }
    }
}




echo $OUTPUT->header();

echo $OUTPUT->box_start();

if ($page != 'classes') {
    $class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);
    $plesso = $DB->get_record('coripodatacollection_plessi', ['id' => $class->plesso]);
    $istituto = $DB->get_record('coripodatacollection_istituti', ['id' => $plesso->instituteid]);
    $opzionianni = [
            -1 => get_string('pluri_class', 'mod_coripodatacollection'),
            0 => get_string('year', 'mod_coripodatacollection') . ' '
                    . get_string('first', 'mod_coripodatacollection'),
            1 => get_string('year', 'mod_coripodatacollection') . ' '
                    . get_string('second', 'mod_coripodatacollection'),
            2 => get_string('year', 'mod_coripodatacollection') . ' '
                    . get_string('third', 'mod_coripodatacollection'),
            3 => get_string('year', 'mod_coripodatacollection') . ' '
                    . get_string('fourth', 'mod_coripodatacollection'),
            4 => get_string('year', 'mod_coripodatacollection') . ' '
                    . get_string('fifth', 'mod_coripodatacollection')
    ];
    echo html_writer::start_div('page-header-headings');
    echo html_writer::tag('h2', $plesso->denominazioneplesso . ' - ' .
            $opzionianni[$class->anno - 1] . ' - ' . $class->classe, ['id' => 'classname']);
    echo html_writer::end_div();
}

if ($page == 'alunni') {

    // Numero studenti necessita conferma da parte dell'insegnante

    if ($class->confermato == 2 || $class->confermato == 0) {
        $paramerror = [
                'title-error' => get_string('wait_teacher_confirm_title', 'mod-coripodatacollection'),
                'message-error' => get_string('wait_teacher_confirm_message', 'mod_coripodatacollection')
        ];
        echo $OUTPUT->render_from_template('coripodatacollection/errorinfo', $paramerror);
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();
        die();
    }


    $sql = 'SELECT * 
            FROM {coripodatacollection_alunni} JOIN {coripodatacollection_class_students} 
            ON {coripodatacollection_class_students}.studentid = {coripodatacollection_alunni}.id
            WHERE classid = ' . $classid . ' ORDER BY numeroregistro';
    $results = $DB->get_records_sql($sql);

    $table = new html_table();

    if ($class->pluriclasse == 1) {
        $table->align = ['center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center'];
        $table->head = [
                '',
                get_string('code', 'mod_coripodatacollection'),
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
        ];
    } else {
        $table->align = ['center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center'];
        $table->head = [
                '',
                get_string('code', 'mod_coripodatacollection'),
                get_string('consensus', 'mod_coripodatacollection'),
                get_string('born_in_italy', 'mod_coripodatacollection'),
                get_string('language_difficulty', 'mod_coripodatacollection'),
                get_string('home_language', 'mod_coripodatacollection'),
                get_string('nursery_school_freq', 'mod_coripodatacollection'),
                get_string('nursery_school_difficulty', 'mod_coripodatacollection'),
                get_string('difficulty_noted', 'mod_coripodatacollection'),
                get_string('centoquattro_law_table', 'mod_coripodatacollection'),
                get_string('centoquattro_problem_table', 'mod_coripodatacollection'),
        ];
    }


    if (!empty($results)) {
        $select_year = [
                0 => get_string('first_1', 'mod_coripodatacollection'),
                1 => get_string('second_2', 'mod_coripodatacollection'),
                2 => get_string('third_3', 'mod_coripodatacollection'),
                3 => get_string('fourth_4', 'mod_coripodatacollection'),
                4 => get_string('fifth_5', 'mod_coripodatacollection')
        ];
        foreach ($results as $r) {

            if($r->carta_identita == 0) {
                $cie = $OUTPUT->pix_icon('i/grade_incorrect',
                        get_string('carta_identita_given', 'mod_coripodatacollection'));
            } else {
                $cie = $OUTPUT->pix_icon('i/valid',
                        get_string('carta_identita_not_given', 'mod_coripodatacollection'));
            }
            if($r->consenso == 0) {
                $consenso = $OUTPUT->pix_icon('i/grade_incorrect',
                        get_string('consensus_not_given', 'mod_coripodatacollection'));
            } else {
                $consenso = $OUTPUT->pix_icon('i/valid',
                        get_string('consensus_given', 'mod_coripodatacollection'));
            }

            if ($class->pluriclasse == 1) {
                $table->data[] = new html_table_row([
                        $r->numeroregistro,
                        $r->hash_code,
                        $select_year[$r->annofrequentazione],
                        $cie . $consenso,
                        returnNoInfo($r->natoinitalia),
                        returnNoInfo($r->difficoltalinguaggio),
                        returnNoInfo($r->linguaparlatacasa),
                        returnNoInfo($r->frequenzascuolainfanzia),
                        returnNoInfo($r->difficoltascuolainfanzia),
                        returnNoInfo($r->notadifficolta),
                        returnNoInfo($r->leggecentoquattro),
                        is_null($r->problematicacentoquattro) ? '' : $r->problematicacentoquattro
                        ]);
            } else {
                $table->data[] = new html_table_row([
                        $r->numeroregistro,
                        $r->hash_code,
                        $cie . $consenso,
                        returnNoInfo($r->natoinitalia),
                        returnNoInfo($r->difficoltalinguaggio),
                        returnNoInfo($r->linguaparlatacasa),
                        returnNoInfo($r->frequenzascuolainfanzia),
                        returnNoInfo($r->difficoltascuolainfanzia),
                        returnNoInfo($r->notadifficolta),
                        returnNoInfo($r->leggecentoquattro),
                        is_null($r->problematicacentoquattro) ? '' : $r->problematicacentoquattro
                        ]);
            }
        }
        echo html_writer::table($table);
    }

} elseif ($page == 'primevalutazioni') {

    if ($class->statistichepre == 1 && $class->risultatipre == 1) {
        $paramerror = [
                'title-error' => get_string('new_results_title', 'mod_coripodatacollection'),
                'message-error' => get_string('new_results_message', 'mod_coripodatacollection'),
        ];
        echo $OUTPUT->render_from_template('coripodatacollection/errorinfo', $paramerror);
    }

    $evaluationtable->display();
    if ($class->statistichepre == 1) {

        if ($class->valutazione_classe_pre == 1) {
            $paramsuccess = [
                    'id-value' => 'class_evaluation_complete',
                    'title-error' => get_string('evaluation_complete_title', 'mod_coripodatacollection'),
                    'message-error' => get_string('evaluation_complete_message', 'mod_coripodatacollection'),
            ];
            echo $OUTPUT->render_from_template('coripodatacollection/allertsuccess', $paramsuccess);
        }

        $close_eval = optional_param('close_eval', 0, PARAM_INT);
        if ($close_eval == 1) {
            if (!$allinserted) {
                $paramerror = [
                        'id-value' => 'class_evaluation_incomplete',
                        'title-error' => get_string('evaluation_incomplete_title', 'mod_coripodatacollection'),
                        'message-error' => get_string('evaluation_incomplete_message', 'mod_coripodatacollection'),
                ];
                echo $OUTPUT->render_from_template('coripodatacollection/errorinfo', $paramerror);
            }
        }
        $statsviewtable->display();
    }
    if ($class->statistichepre == 1 && $class->valutazione_classe_pre == 1 && $erogation->calcolo_globale_pre) {

        if ($class->valutazione_globale_pre == 1) {
            $paramsuccess = [
                    'id-value' => 'global_evaluation_complete',
                    'title-error' => get_string('evaluation_complete_title', 'mod_coripodatacollection'),
                    'message-error' => get_string('ending_evaluation_complete_message', 'mod_coripodatacollection'),
            ];
            echo $OUTPUT->render_from_template('coripodatacollection/allertsuccess', $paramsuccess);
        }

        $close_global = optional_param('close_global', 0, PARAM_INT);
        if ($close_global == 1) {
            if (!$allinserted_global) {
                $paramerror = [
                        'id-value' => 'global_evaluation_incomplete',
                        'title-error' => get_string('evaluation_incomplete_title', 'mod_coripodatacollection'),
                        'message-error' => get_string('evaluation_incomplete_message', 'mod_coripodatacollection'),
                ];
                echo $OUTPUT->render_from_template('coripodatacollection/errorinfo', $paramerror);
            }
        }
        $statsviewtable_globale->display();
    }

} elseif ($page == 'ultimevalutazioni') {

    if ($class->statistichepost == 1 && $class->risultatipost == 1) {
        $paramerror = [
                'title-error' => get_string('new_results_title', 'mod_coripodatacollection'),
                'message-error' => get_string('new_results_message', 'mod_coripodatacollection'),
        ];
        echo $OUTPUT->render_from_template('coripodatacollection/errorinfo', $paramerror);
    }

    $evaluationtable->display();
    if ($class->statistichepost == 1) {

        if ($class->valutazione_classe_post == 1) {
            $paramsuccess = [
                    'id-value' => 'class_evaluation_complete',
                    'title-error' => get_string('evaluation_complete_title', 'mod_coripodatacollection'),
                    'message-error' => get_string('evaluation_complete_message', 'mod_coripodatacollection'),
            ];
            echo $OUTPUT->render_from_template('coripodatacollection/allertsuccess', $paramsuccess);
        }

        $close_eval = optional_param('close_eval', 0, PARAM_INT);
        if ($close_eval == 1) {
            if (!$allinserted) {
                $paramerror = [
                        'id-value' => 'class_evaluation_incomplete',
                        'title-error' => get_string('evaluation_incomplete_title', 'mod_coripodatacollection'),
                        'message-error' => get_string('evaluation_incomplete_message', 'mod_coripodatacollection'),
                ];
                echo $OUTPUT->render_from_template('coripodatacollection/errorinfo', $paramerror);
            }
        }
        $statsviewtable->display();
    }
    if ($class->statistichepost == 1 && $class->valutazione_classe_post == 1 && $erogation->calcolo_globale_post) {

        if ($class->valutazione_globale_post == 1) {
            $paramsuccess = [
                    'id-value' => 'global_evaluation_complete',
                    'title-error' => get_string('evaluation_complete_title', 'mod_coripodatacollection'),
                    'message-error' => get_string('ending_evaluation_complete_message', 'mod_coripodatacollection'),
            ];
            echo $OUTPUT->render_from_template('coripodatacollection/allertsuccess', $paramsuccess);
        }

        $close_global = optional_param('close_global', 0, PARAM_INT);
        if ($close_global == 1) {
            if (!$allinserted_global) {
                $paramerror = [
                        'id-value' => 'global_evaluation_incomplete',
                        'title-error' => get_string('evaluation_incomplete_title', 'mod_coripodatacollection'),
                        'message-error' => get_string('evaluation_incomplete_message', 'mod_coripodatacollection'),
                ];
                echo $OUTPUT->render_from_template('coripodatacollection/errorinfo', $paramerror);
            }
        }
        $statsviewtable_globale->display();
    }

} else {

    echo html_writer::tag('h2', get_string('registered_classes', 'mod_coripodatacollection'),
            ['class' => 'h2', 'style' => 'margin-bottom: 50px;']);

    $erogation = $DB->get_record('coripodatacollection_erogations', ['courseid' => $course->id]);

    $time = time();
    $periodo_prerinforzo = $erogation->start_val_pre < $time && $time < $erogation->end_val_pre;
    $periodo_valutazione_pre = $erogation->end_val_pre < $time && $time < $erogation->start_val_post;
    $periodo_postrinforzo = $erogation->start_val_post < $time && $time < $erogation->end_val_post;
    $periodo_valutazione_post = $erogation->end_val_post < $time;

    $completed_all_res_pre = true;
    $completed_all_res_post = true;

    $erogation_classes = $DB->get_records('coripodatacollection_classes', ['erogazione' => $erogation->id]);
    foreach ($erogation_classes as $erogation_class) {
        if ($erogation_class->completati_res_pre != 1)
            $completed_all_res_pre = false;
        if ($erogation_class->completati_res_post != 1) {
            $completed_all_res_post = false;
        }
    }

    $completed_all_index_pre = true;
    $completed_all_index_post = true;

    foreach ($erogation_classes as $erogation_class) {
        if ($erogation_class->completati_res_pre == 1 && $erogation_class->statistichepre != 1)
            $completed_all_index_pre = false;
        if ($erogation_class->completati_res_post == 1 && $erogation_class->statistichepost != 1) {
            $completed_all_index_post = false;
        }
    }

    if (($periodo_prerinforzo && $completed_all_res_pre) || $periodo_valutazione_pre)
        if (!$completed_all_index_pre) {
            $paramsuccess = [
                    'title-error' => get_string('index_missing_title', 'mod_coripodatacollection'),
                    'message-error' => get_string('index_missing_body', 'mod_coripodatacollection'),
            ];
            echo $OUTPUT->render_from_template('coripodatacollection/allertsuccess', $paramsuccess);
        }
    else if (($periodo_postrinforzo && $completed_all_res_post) || $periodo_valutazione_post)
        if (!$completed_all_index_post) {
            $paramsuccess = [
                    'title-error' => get_string('index_missing_title', 'mod_coripodatacollection'),
                    'message-error' => get_string('index_missing_body', 'mod_coripodatacollection'),
            ];
            echo $OUTPUT->render_from_template('coripodatacollection/allertsuccess', $paramsuccess);
        }


    if ($erogation->calcolo_globale_pre == 1 && ($periodo_prerinforzo || $periodo_valutazione_pre)) {
        $paramsuccess = [
                'id-value' => 'class_evaluation_complete',
                'title-error' => get_string('stats_calculated_title', 'mod_coripodatacollection'),
                'message-error' => get_string('stats_calculated_message_pre', 'mod_coripodatacollection'),
        ];
        echo $OUTPUT->render_from_template('coripodatacollection/allertsuccess', $paramsuccess);

        $indici = $DB->get_records_sql('SELECT * FROM mdl_coripodatacollection_indici_valutazione
                                            WHERE periodo="prerinforzo" 
                                              AND (valutazione_classe = "Rosso" OR valutazione_classe = "Giallo")
                                              AND erogazione=' . $erogation->id);
        $count = 0;
        foreach ($indici as $ind) {
            $classe = $DB->get_record('coripodatacollection_classes', ['id' => $ind->classe]);
            if ($classe->valutazione_globale_pre != 1)
                continue;
            if ($ind->valutazione_globale == 'Verde scuro' || $ind->valutazione_globale == 'Giallo'
                    || $ind->valutazione_globale == 'Rosso')
                $count += 1;
        }
        echo html_writer::tag('h5',get_string('total_passed', 'mod_coripodatacollection') . count($indici), ['class' => 'h5']);
        echo html_writer::tag('h5',get_string('total_reported', 'mod_coripodatacollection') . $count, ['class' => 'h5']);

    } elseif ($erogation->calcolo_globale_post == 1 && ($periodo_postrinforzo || $periodo_valutazione_post)) {
        $paramsuccess = [
                'id-value' => 'class_evaluation_complete',
                'title-error' => get_string('stats_calculated_title', 'mod_coripodatacollection'),
                'message-error' => get_string('stats_calculated_message_post', 'mod_coripodatacollection'),
        ];
        echo $OUTPUT->render_from_template('coripodatacollection/allertsuccess', $paramsuccess);
    }


    $allclasses = new \mod_coripodatacollection\forms\viewallclasses_form(null,
            ['instanceid' => $id, 'courseid' => $course->id, 'viewmode' => 'evaluator']);
    $allclasses->display();

    $stickyfooterelements = \html_writer::start_div('', ['style' => 'display: flex; justify-content: space-between;']);
    $stickyfooterelements .= \html_writer::start_div('', ['style' => 'display: block; text-align: center;']);
    $stickyfooterelements .= html_writer::tag('h5',
            get_string('census_period', 'mod_coripodatacollection')
    );
    $stickyfooterelements .= html_writer::tag('strong',
            $data = date("d/m/Y", $erogation->start_censimento) . '--' .
                    $data = date("d/m/Y", $erogation->end_censimento),
            ['style' => 'color: lightgray;']);
    $stickyfooterelements .= \html_writer::end_div();
    $stickyfooterelements .= \html_writer::end_div();

    $stickyfooterelements .= \html_writer::start_div('', ['' =>'flex: 1; text-align: center;']);
    $stickyfooterelements .= \html_writer::start_div('', ['style' => 'display: block; text-align: center;']);
    $stickyfooterelements .= html_writer::tag('h5',
            get_string('pre_reinforce_period', 'mod_coripodatacollection'));
    $stickyfooterelements .= html_writer::tag('strong',
            $data = date("d/m/Y", $erogation->start_val_pre) . '--' . $data = date("d/m/Y", $erogation->end_val_pre),
            ['style' => 'color: lightgray;']);
    $stickyfooterelements .= \html_writer::end_div();
    $stickyfooterelements .= \html_writer::end_div();

    $stickyfooterelements .= \html_writer::start_div('', ['' =>'flex: 1; text-align: right;']);
    $stickyfooterelements .= \html_writer::start_div('', ['style' => 'display: block; text-align: center;']);
    $stickyfooterelements .= html_writer::tag('h5',
            get_string('post_reinforce_period', 'mod_coripodatacollection'));
    $stickyfooterelements .= html_writer::tag('strong',
            $data = date("d/m/Y", $erogation->start_val_post) . '--' . $data = date("d/m/Y", $erogation->end_val_post),
            ['style' => 'color: lightgray;']);
    $stickyfooterelements .= \html_writer::end_div();
    $stickyfooterelements .= \html_writer::end_div();

    $stickyfooterelements .= \html_writer::start_div();
    $stickyfooterelements .= html_writer::tag('a',
            get_string('getcsvbutton', 'mod_coripodatacollection'),
            ['id' => 'get-all-info', 'class' => 'btn btn-primary', 'style' => 'display: inline-block;  margin-right: 5px;']);

    if ((($periodo_prerinforzo && $completed_all_res_pre) || $periodo_valutazione_pre) && $completed_all_index_pre){
        $url = new moodle_url('/mod/coripodatacollection/viewevaluator.php',
                ['id' => $id, 'page' => 'global-calculus', 'classid' => $classid, 'period' => 'prerinforzo']);
        $stickyfooterelements .= html_writer::tag('a',
                get_string('calculate_global_stats_pre', 'mod_coripodatacollection'),
                [
                    'id' => 'get-all-info',
                    'class' => 'btn btn-primary',
                    'style' => 'display: inline-block; margin-right: 5px;',
                    'href' => $url,
                ]
        );
        $url = new moodle_url('/mod/coripodatacollection/viewevaluator.php',
                ['id' => $id, 'page' => 'global-calculus-no-out', 'classid' => $classid, 'period' => 'prerinforzo']);
        $stickyfooterelements .= html_writer::tag('a',
                get_string('calculate_global_stats_pre_no_outlier', 'mod_coripodatacollection'),
                ['href' => $url, 'class' => 'btn btn-primary', 'style' => 'display: inline-block;']
        );
    } elseif ((($periodo_postrinforzo && $completed_all_res_post) || $periodo_valutazione_post) && $completed_all_index_post) {
        $url = new moodle_url('/mod/coripodatacollection/viewevaluator.php',
                ['id' => $id, 'page' => 'global-calculus', 'classid' => $classid, 'period' => 'postrinforzo']);
        $stickyfooterelements .= html_writer::tag('a',
                get_string('calculate_global_stats_pre', 'mod_coripodatacollection'),
                ['href' => $url, 'class' => 'btn btn-primary', 'style' => 'display: inline-block; margin-right: 5px;']
        );
        $url = new moodle_url('/mod/coripodatacollection/viewevaluator.php',
                ['id' => $id, 'page' => 'global-calculus-no-out', 'classid' => $classid, 'period' => 'postrinforzo']);
        $stickyfooterelements .= html_writer::tag('a',
                get_string('calculate_global_stats_pre_no_outlier', 'mod_coripodatacollection'),
                ['href' => $url, 'class' => 'btn btn-primary', 'style' => 'display: inline-block;']
        );

    }

    $stickyfooterelements .= \html_writer::end_div();

    if (!empty($stickyfooterelements)) {
        $stickyfooter = new \core\output\sticky_footer($stickyfooterelements, ' ',
                ['style' => 'display: flex; justify-content: space-between;']);
        echo $OUTPUT->render($stickyfooter);
    }

    echo send_erogation_students_csv($course);

}

echo $OUTPUT->box_end();

if ($page == 'classes' || $page == '') {
    echo '<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>';
    echo '<script src="javascript/download_info.js"></script>';
}
if ($page == 'primevalutazioni' or $page == 'ultimevalutazioni') {
    echo '<script src="javascript/fixed_table_col.js"></script>';
    echo '<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>';
    echo '<script src="javascript/export_excel.js"></script>';
    echo '<script src="javascript/show_hide_tables.js"></script>';
    echo '<script src="javascript/reported_student_output.js"></script>';
}
echo $OUTPUT->footer();
