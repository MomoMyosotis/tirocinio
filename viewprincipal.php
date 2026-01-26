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
$page = optional_param('page', 'classes', PARAM_TEXT);

if ($id) {
    $cm = get_coursemodule_from_id('coripodatacollection', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('coripodatacollection', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('coripodatacollection', ['id' => $c], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $moduleinstance->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('coripodatacollection', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

$context = context_system::instance();
require_login($course, true, $cm);
require_capability('mod/coripodatacollection:schoolmanager', $context);
require_capability('mod/data:viewentry', $context);

if ($DB->count_records('coripodatacollection_principals', ['userid' => $USER->id]) == 0)
    redirect(new moodle_url('/mod/coripodatacollection/viewdirector.php', ['id' => $cm->id, 'page' => $page]));

$modulecontext = context_module::instance($cm->id);

$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
$PAGE->add_body_class('wide');

$page = optional_param('page', 'classes', PARAM_TEXT);

$user_institute_administrations = $DB->get_records('coripodatacollection_instituteadmin', ['userid' => $USER->id]);
$erogation = $DB->get_record('coripodatacollection_erogations', ['courseid' => $course->id]);
$institutes = [];
foreach ($user_institute_administrations as $uia) {

    if ($DB->record_exists('coripodatacollection_istituti_x_progetto_x_aa',
            ['instituteid' => $uia->instituteid, 'projectid' => $erogation->projectid, 'erogation' => $erogation->id])) {

        $istitute_toadd = $DB->get_record('coripodatacollection_istituti', ['id' => $uia->instituteid]);
        $institutes[$istitute_toadd->id] = $istitute_toadd;

    }
}

if (empty($institutes)) {
    $teaching_classes = $DB->get_records_sql('SELECT DISTINCT mdl_coripodatacollection_classes.* 
                                                  FROM mdl_coripodatacollection_classes
                                                  JOIN mdl_coripodatacollection_classadmin ON classid=mdl_coripodatacollection_classes.id
                                                  WHERE userid=' . $USER->id . ' AND erogazione=' . $erogation->id);
    if (!empty($teaching_classes)) {
        redirect(new moodle_url('/mod/coripodatacollection/viewteacher.php', ['id' => $id, 'page' => 'classes']));
    }
}


$PAGE->set_url('/mod/coripodatacollection/viewprincipal.php', ['id' => $cm->id, 'page' => $page]);
$node = $PAGE->secondarynav->add(get_string('classi', 'mod_coripodatacollection'));
$node->isactive = true;
$node->action = new moodle_url('/mod/coripodatacollection/viewdirector.php', ['id' => $cm->id, 'page' => 'classes']);


$confirm_istitute = optional_param('confirm_istitute', -1, PARAM_INT);
echo $OUTPUT->header();

if ($confirm_istitute != -1 || count($institutes) == 1) {

    if (count($institutes) == 1)
        $istitute = reset($institutes);
    else
        $istitute = $DB->get_record('coripodatacollection_istituti', ['id' => $confirm_istitute]);

    echo html_writer::tag('h2', $istitute->denominazioneistituto . ' - ' . get_string('reported_students', 'mod_coripodatacollection'),
            ['class' => 'h2', 'style' => 'margin-bottom: 50px;']);
    echo '<div id="istituto" style="display: none">' . $istitute->denominazioneistituto .  '</div>';

    $table = new html_table();
    $table->head = [
            get_string('searchplesso_name', 'mod_coripodatacollection'),
            get_string('class', 'mod_coripodatacollection'),
            get_string('surname', 'mod_coripodatacollection'),
            get_string('name', 'mod_coripodatacollection'),
            get_string('code', 'mod_coripodatacollection')
    ];
    $table->align = ['center', 'center', 'center', 'center', 'center'];

    $classes = $DB->get_records('coripodatacollection_classes', ['istituto' => $istitute->id]);
    foreach ($classes as $class) {
        $plesso = $DB->get_record('coripodatacollection_plessi', ['id' => $class->plesso]);
        $indexes = $DB->get_records('coripodatacollection_indici_valutazione', ['classe' => $class->id]);
        foreach ($indexes as $index) {
            if ($index->periodo == 'prerinforzo'
                    &&  (
                        $index->valutazione_globale == get_string('dark_green', 'mod_coripodatacollection')
                        || $index->valutazione_globale == get_string('yellow', 'mod_coripodatacollection')
                        || $index->valutazione_globale == get_string('red', 'mod_coripodatacollection')
                    )
                    && (
                        $index->valutazione_classe == get_string('yellow', 'mod_coripodatacollection')
                        || $index->valutazione_classe == get_string('red', 'mod_coripodatacollection')
                    )
            ) {
                $alunno = $DB->get_record('coripodatacollection_alunni', ['id' => $index->alunno]);

                $plex_cell = new html_table_cell($plesso->denominazioneplesso);
                $plex_cell->id = 'plesso[' . $alunno->id . ']';
                $classe_cell = new html_table_cell($class->classe);
                $classe_cell->id = 'classe[' . $alunno->id . ']';
                $cognome_cell = new html_table_cell($alunno->cognome);
                $cognome_cell->id = 'cognome[' . $alunno->id . ']';
                $nome_cell = new html_table_cell($alunno->nome);
                $nome_cell->id = 'nome[' . $alunno->id . ']';
                $code_cell = new html_table_cell($alunno->hash_code);
                $code_cell->id = 'code[' . $alunno->id . ']';


                $table->data[] = new html_table_row([
                    $plex_cell,
                    $classe_cell,
                    $cognome_cell,
                    $nome_cell,
                    $code_cell
                ]);
            }
        }
    }

    echo html_writer::table($table);

    $stickyfooterelements = \html_writer::start_div('', ['style' => 'display: flex; justify-content: space-between;']);
    $stickyfooterelements .= \html_writer::start_div('', ['style' => 'display: block; text-align: center;']);
    $url = new moodle_url('/mod/coripodatacollection/viewdirector.php', ['id' => $cm->id, 'page' => 'classes']);
    $stickyfooterelements .= html_writer::tag('a',
            get_string('back', 'mod_coripodatacollection'),
            [
                    'class' => 'btn btn-secondary',
                    'style' => 'display: inline-block; margin-right: 5px;',
                    'href' => $url,
            ]
    );
    $stickyfooterelements .= \html_writer::end_div();
    $stickyfooterelements .= \html_writer::end_div();

    $stickyfooterelements .= \html_writer::start_div();
    $stickyfooterelements .= html_writer::tag('a',
            get_string('pdf_reported', 'mod_coripodatacollection'),
            [
                    'id' => 'get-pdf-zip',
                    'class' => 'btn btn-primary',
                    'style' => 'display: inline-block; margin-right: 5px;',
                    'href' => '#',
            ]
    );
    $stickyfooterelements .= \html_writer::end_div();

    if (!empty($stickyfooterelements)) {
        $stickyfooter = new \core\output\sticky_footer($stickyfooterelements, ' ',
                ['style' => 'display: flex; justify-content: space-between;']);
        echo $OUTPUT->render($stickyfooter);
    }

    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>';
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.9.1/jszip.min.js"></script>';
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>';
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>';

    echo '<script type="module" src="javascript/download_zip.js"></script>';

} else {


    echo html_writer::tag('h2', get_string('select_istitute_reported', 'mod_coripodatacollection'),
            ['class' => 'h2', 'style' => 'margin-bottom: 50px;']);

    # TODO: Completare con la selezione dell'istituto nel caso un direttore sia il preside per più istituti

}


echo $OUTPUT->footer();
