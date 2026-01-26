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

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$PAGE->set_url('/mod/coripodatacollection/viewproject.php', ['id' => $cm->id]);
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
$PAGE->set_url('/mod/coripodatacollection/viewproject.php', $urlparam);
if ($page == 'alunni' || $page == 'primevalutazioni' || $page == 'ultimevalutazioni' ||  $page == 'newalunno'
        || $page == 'modalunno' || $page == '') {
    $node = $PAGE->secondarynav->add(get_string('classi', 'mod_coripodatacollection'));
    $node->isactive = false;
    $node->action = new moodle_url('/mod/coripodatacollection/viewproject.php', ['id' => $cm->id, 'page' => 'classes']);

    $node = $PAGE->secondarynav->add(get_string('alunni', 'mod_coripodatacollection'));
    $node->isactive = $page == 'alunni' || $page == 'newalunno' || $page == 'modalunno';
    $node->action = new moodle_url('/mod/coripodatacollection/viewproject.php',
            ['id' => $cm->id, 'page' => 'alunni', 'classid' => $classid]);

    $node = $PAGE->secondarynav->add(get_string('primevalutazioni', 'mod_coripodatacollection'));
    $node->isactive = $page == 'primevalutazioni';
    $node->action = new moodle_url('/mod/coripodatacollection/viewproject.php',
            ['id' => $cm->id, 'page' => 'primevalutazioni', 'classid' => $classid]);

    $node = $PAGE->secondarynav->add(get_string('ultimevalutazioni', 'mod_coripodatacollection'));
    $node->isactive = $page == 'ultimevalutazioni';
    $node->action = new moodle_url('/mod/coripodatacollection/viewproject.php',
            ['id' => $cm->id, 'page' => 'ultimevalutazioni', 'classid' => $classid]);
} else {
    $node = $PAGE->secondarynav->add(get_string('classi', 'mod_coripodatacollection'));
    $node->isactive = true;
    $node->action = new moodle_url('/mod/coripodatacollection/viewproject.php', ['id' => $cm->id, 'page' => 'classes']);

    $node = $PAGE->secondarynav->add('Utenti valutatori');
    $node->isactive = false;
    $node->action = new moodle_url('/mod/coripodatacollection/erogationmanager.php', ['id' => $cm->id, 'page' => 'evaluators']);

    $node = $PAGE->secondarynav->add('Istituti');
    $node->isactive = false;
    $node->action = new moodle_url('/mod/coripodatacollection/erogationmanager.php', ['id' => $cm->id, 'page' => 'institutes']);

    $node = $PAGE->secondarynav->add('Gestione periodi');
    $node->isactive = false;
    $node->action = new moodle_url('/mod/coripodatacollection/erogationmanager.php',
            ['id' => $cm->id, 'page' => 'periods', 'mode' => 'view']);

    $node = $PAGE->secondarynav->add('Gruppi');
    $node->isactive = false;
    $node->action = new moodle_url('/mod/coripodatacollection/erogationmanager.php',
            ['id' => $cm->id, 'page' => 'gruppi', 'mode' => 'view']);

}



$action = optional_param('action', '', PARAM_TEXT);
if ( $action == 'riapertura' && $classid > 0) {

    $class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);
    $class->can_edit_censimento = ($page=='alunni') ? 1 : $class->can_edit_censimento;
    $class->can_edit_val_pre = ($page=='primevalutazioni') ? 1 : $class->can_edit_val_pre;
    $class->completati_res_pre = ($page=='primevalutazioni') ? 0 : $class->completati_res_pre;
    $class->can_edit_val_post = ($page=='ultimevalutazioni') ? 1: $class->can_edit_val_post;
    $class->completati_res_post = ($page=='ultimevalutazioni') ? 0: $class->completati_res_post;
    $DB->update_record('coripodatacollection_classes', $class);
    redirect( new moodle_url('/mod/coripodatacollection/viewproject.php',
            ['id' => $id, 'page' => $page, 'classid' => $classid]));

} elseif ( $action == 'chiusura') {

    $class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);
    $class->can_edit_censimento = ($page=='alunni') ? 0 : $class->can_edit_censimento;
    $class->can_edit_val_pre = ($page=='primevalutazioni') ? 0 : $class->can_edit_val_pre;
    $class->can_edit_val_post = ($page=='ultimevalutazioni') ? 0: $class->can_edit_val_post;
    $DB->update_record('coripodatacollection_classes', $class);
    redirect( new moodle_url('/mod/coripodatacollection/viewproject.php',
            ['id' => $id, 'page' => $page, 'classid' => $classid]));

}

if ($page == 'getcsv') {
    send_erogation_info_csv($course);
    $url  = new moodle_url('/mod/coripodatacollection/viewproject.php',
            ['id' => $id, 'page' => 'classes']);
    redirect($url);
}

if ($page == 'send_principal_email') {
    $erogation = $DB->get_record('coripodatacollection_erogations', ['courseid' => $course->id]);
    $erogation->evaluation_completed_pre = 1;
    $DB->update_record('coripodatacollection_erogations', $erogation);
    $url  = new moodle_url('/mod/coripodatacollection/viewproject.php',
            ['id' => $id, 'page' => 'classes']);
    redirect($url);
}


echo $OUTPUT->header();

echo $OUTPUT->box_start();

if ($page != 'classes') {
    $class = $DB->get_record('coripodatacollection_classes', ['id' => $classid]);
    $plesso = $DB->get_record('coripodatacollection_plessi', ['id' => $class->plesso]);
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
            $opzionianni[$class->anno - 1] . ' - ' . $class->classe);
    echo html_writer::end_div();
}

if ($page == 'alunni') {

    // Numero studenti necessita conferma da parte dell'insegnante

    if ($class->confermato == 2 || $class->confermato == 0) {
        $paramerror = [
                'title-error' => get_string('waiting_teacher_confrim_title', 'mod_coripodatacollection'),
                'message-error' => get_string('waiting_teacher_confrim_message', 'mod_coripodatacollection')
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
        $table->align = ['center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center',];
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
        $table->align = ['center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center',];
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
                        is_null($r->problematicacentoquattro) ? '' : $r->problematicacentoquattro]);
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
                        is_null($r->problematicacentoquattro) ? '' : $r->problematicacentoquattro]);
            }
        }
        echo html_writer::table($table);
    }

    $stickyfooterelements = '';
    $erogation = $DB->get_record('coripodatacollection_erogations', ['id' => $class->erogazione]);
    $current_date = time();
    $periodo_post_rinforzo = $current_date >= $erogation->end_censimento;
    if ($periodo_post_rinforzo && $class->can_edit_censimento == 0) {
        $url  = new moodle_url('/mod/coripodatacollection/viewproject.php',
                ['id' => $id, 'page' => 'alunni', 'classid' => $classid, 'action' => 'riapertura']);
        $stickyfooterelements = html_writer::tag('a',
                get_string('reopen_census', 'mod_coripodatacollection'),
                ['href' => $url, 'class' => 'btn btn-primary', 'style' => 'display: inline-block;']
        );
    }
    if ($periodo_post_rinforzo && $class->can_edit_censimento == 1) {
        $url  = new moodle_url('/mod/coripodatacollection/viewproject.php',
                ['id' => $id, 'page' => 'alunni', 'classid' => $classid, 'action' => 'chiusura']);
        $stickyfooterelements = html_writer::tag('a',
                get_string('close_census', 'mod_coripodatacollection'),
                ['href' => $url, 'class' => 'btn btn-primary', 'style' => 'display: inline-block;']
        );
    }

    if ($stickyfooterelements != '') {
        $stickyfooter = new \core\output\sticky_footer($stickyfooterelements);
        echo $OUTPUT->render($stickyfooter);
    }

} elseif ($page == 'primevalutazioni') {

    if ($class->completati_res_pre == 2) {
        $paramerror = [
                'title-error' => get_string('reopen_allert_title', 'mod_coripodatacollection'),
                'message-error' => get_string('reopen_allert_message_pre', 'mod_coripodatacollection'),
        ];
        echo $OUTPUT->render_from_template('coripodatacollection/allertsuccess', $paramerror);
    }

    $evaluationtable = new \mod_coripodatacollection\forms\newprova_form(null,
            ['idclasse' => $classid, 'onlyview' => true, 'table' => 'pre', 'anonym' => true]);
    $evaluationtable->display();

    $stickyfooterelements = '';

    $erogation = $DB->get_record('coripodatacollection_erogations', ['id' => $class->erogazione]);
    $current_date = time();
    $periodo_rinforzo = $current_date >= $erogation->end_val_pre;
    if (($periodo_rinforzo && $class->can_edit_val_pre == 0) || $class->completati_res_pre == 2) {
        $url  = new moodle_url('/mod/coripodatacollection/viewproject.php',
                ['id' => $id, 'page' => 'primevalutazioni', 'classid' => $classid, 'action' => 'riapertura']);
        $stickyfooterelements .= html_writer::tag('a',
                get_string('allow_modify_results', 'mod_coripodatacollection'),
                ['href' => $url, 'class' => 'btn btn-primary', 'style' => 'display: inline-block;']
        );
    }
    if ($periodo_rinforzo && $class->can_edit_val_pre == 1 ) {
        $url  = new moodle_url('/mod/coripodatacollection/viewproject.php',
                ['id' => $id, 'page' => 'primevalutazioni', 'classid' => $classid, 'action' => 'chiusura']);
        $stickyfooterelements .= html_writer::tag('a',
                get_string('block_modify_results', 'mod_coripodatacollection'),
                ['href' => $url, 'class' => 'btn btn-primary', 'style' => 'display: inline-block;']
        );
    }

    if ($stickyfooterelements != '') {
        $stickyfooter = new \core\output\sticky_footer($stickyfooterelements);
        echo $OUTPUT->render($stickyfooter);
    }

} elseif ($page == 'ultimevalutazioni') {

    if ($class->completati_res_post == 2) {
        $paramerror = [
                'title-error' => get_string('reopen_allert_title', 'mod_coripodatacollection'),
                'message-error' => get_string('reopen_allert_message_pre', 'mod_coripodatacollection'),
        ];
        echo $OUTPUT->render_from_template('coripodatacollection/allertsuccess', $paramerror);
    }

    $evaluationtable = new \mod_coripodatacollection\forms\newprova_form(null,
            ['idclasse' => $classid, 'onlyview' => true, 'table' => 'post', 'anonym' => true]);
    $evaluationtable->display();

    $stickyfooterelements = '';

    $erogation = $DB->get_record('coripodatacollection_erogations', ['id' => $class->erogazione]);
    $current_date = time();
    $periodo_finale = $current_date >= $erogation->end_val_pre;
    if (($periodo_finale && $class->can_edit_val_post == 0) || $class->completati_res_post == 2) {
        $url  = new moodle_url('/mod/coripodatacollection/viewproject.php',
                ['id' => $id, 'page' => 'ultimevalutazioni', 'classid' => $classid, 'action' => 'riapertura']);
        $stickyfooterelements .= html_writer::tag('a',
                get_string('allow_modify_results', 'mod_coripodatacollection'),
                ['href' => $url, 'class' => 'btn btn-primary', 'style' => 'display: inline-block;']
        );
    }
    if ($periodo_finale && $class->can_edit_val_post == 1 ) {
        $url  = new moodle_url('/mod/coripodatacollection/viewproject.php',
                ['id' => $id, 'page' => 'ultimevalutazioni', 'classid' => $classid, 'action' => 'chiusura']);
        $stickyfooterelements .= html_writer::tag('a',
                get_string('block_modify_results', 'mod_coripodatacollection'),
                ['href' => $url, 'class' => 'btn btn-primary', 'style' => 'display: inline-block;']
        );
    }

    if ($stickyfooterelements != '') {
        $stickyfooter = new \core\output\sticky_footer($stickyfooterelements);
        echo $OUTPUT->render($stickyfooter);
    }

} else {

    echo html_writer::tag('h2', get_string('registered_classes', 'mod_coripodatacollection'),
            ['class' => 'h2', 'style' => 'margin-bottom: 50px;']);

    $erogation = $DB->get_record('coripodatacollection_erogations', ['courseid' => $course->id]);
    $time = time();
    $periodo_prerinforzo = $erogation->start_val_pre < $time && $time < $erogation->end_val_pre;
    $periodo_valutazione_pre = $erogation->end_val_pre < $time && $time < $erogation->start_val_post;

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
    }

    $allclasses = new \mod_coripodatacollection\forms\viewallclasses_form(null,
            ['instanceid' => $id, 'courseid' => $course->id, 'viewmode' => 'project']);
    $allclasses->display();

    $stickyfooterelements = \html_writer::start_div('', ['style' => 'display: flex; justify-content: space-between;']);
    $stickyfooterelements .= \html_writer::start_div('', ['style' => 'display: block; text-align: center;']);
    $stickyfooterelements .= html_writer::tag('h5',
            get_string('census_period', 'mod_coripodatacollection'));
    $stickyfooterelements .= html_writer::tag('strong',
            $data = date("d/m/Y", $erogation->start_censimento) .
                    '--' . $data = date("d/m/Y", $erogation->end_censimento),
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

    $stickyfooterelements .= \html_writer::start_div('', ['' =>'flex: 1; text-align: right;']);
    $url  = new moodle_url('/mod/coripodatacollection/viewproject.php',
            ['id' => $id, 'page' => 'send_principal_email']);
    $stickyfooterelements .= html_writer::tag('a',
            get_string('director_notification', 'mod_coripodatacollection'),
            ['href' => $url, 'class' => 'btn btn-primary', 'style' => 'display: inline-block; margin-right: 5px;']);
    $url  = new moodle_url('/mod/coripodatacollection/erogationmanager.php',
            ['id' => $id, 'page' => 'periods', 'mode' => 'view']);
    $stickyfooterelements .= html_writer::tag('a',
            get_string('modify_periods', 'mod_coripodatacollection'),
            ['href' => $url, 'class' => 'btn btn-primary', 'style' => 'display: inline-block; margin-right: 5px;']);
    $url  = new moodle_url('/mod/coripodatacollection/viewproject.php',
            ['id' => $id, 'page' => 'getcsv']);
    $stickyfooterelements .= html_writer::tag('a',
            get_string('getcsvbutton', 'mod_coripodatacollection'),
            ['href' => $url, 'class' => 'btn btn-primary', 'style' => 'display: inline-block;']);
    $stickyfooterelements .= \html_writer::end_div();


    if (!empty($stickyfooterelements)) {
        $stickyfooter = new \core\output\sticky_footer($stickyfooterelements, ' ',
                ['style' => 'display: flex; justify-content: space-between;']);
        echo $OUTPUT->render($stickyfooter);
    }

}

echo $OUTPUT->box_end();
if ($page == 'primevalutazioni' or $page == 'ultimevalutazioni') {
    echo '<script src="javascript/fixed_table_col.js"></script>';
}
echo $OUTPUT->footer();
