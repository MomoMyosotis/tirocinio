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

require_once('../../config.php');
require_once('lib.php');

$context = context_system::instance();
$PAGE->set_context($context);

$displaypage = optional_param('page', 'projects', PARAM_TEXT);
$PAGE->set_url(new moodle_url('/mod/coripodatacollection/projectsadmin.php'), ['page' => $displaypage]);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'mod_coripodatacollection'));
$PAGE->set_heading(get_string('manageproject', 'mod_coripodatacollection'));

require_login();
require_capability('mod/coripodatacollection:projectadmin', $context);

if (isguestuser()) {
    throw new moodle_exception('noguest');
}

$redir = new moodle_url('/mod/coripodatacollection/projectsadmin.php', ['page' => 'newproject']);
$newprojectform = new \mod_coripodatacollection\forms\projectadmin_form($redir, ['viewmode' => 'edit']);

if ($newprojectform->is_cancelled()) {
    redirect(new moodle_url('/mod/coripodatacollection/projectsadmin.php', ['page' => 'projects']));
} elseif ($data = $newprojectform->get_data()) {

    $projectadmin = new stdClass();
    $projectadmin->projectid = $DB->insert_record('coripodatacollection_projects', $data);
    $projectadmin->userid = $USER->id;
    $DB->insert_record('coripodatacollection_projectadmin', $projectadmin);

    redirect(new moodle_url('/mod/coripodatacollection/projectsadmin.php', ['page' => 'projects']));
}



$homeurl = new moodle_url('/', ['redirect' => 0]);
$projecturl = $PAGE->url;
$menuelemnts = [
        ["text" => get_string('home', 'mod_coripodatacollection'),
                "url" => $homeurl, "key" => "viewproject"],
        ["text" => get_string('projects', 'mod_coripodatacollection'),
                "url" => $projecturl, "key" => "viewvaluser", "isactive" => true],
];

coripodatacollection_projectview_displaymenu($menuelemnts);

echo $OUTPUT->header();

if ($displaypage == 'newproject') {
    $newprojectform->display();
} else {

    echo $OUTPUT->box_start();

    $linkaddbutton = new moodle_url('/mod/coripodatacollection/projectsadmin.php', ['page' => 'newproject']);
    echo html_writer::start_div('container-fluid tertiary-navigation d-flex', ['style' => 'text-align: left;']);
    echo html_writer::tag('a', get_string('addnewproject', 'mod_coripodatacollection'),
            ['href' => $linkaddbutton, 'class' => 'btn btn-secondary', 'style' => 'display: inline-block;']
    );
    echo html_writer::end_div();

    echo html_writer::start_tag('ul', ['class' => 'section m-0 p-0 img-text  d-block ', 'data-for' => 'cmlist']);


    $results = $DB->get_records_sql('SELECT projectname, mdl_coripodatacollection_projects.id
                                                FROM mdl_coripodatacollection_projects 
                                                    JOIN mdl_coripodatacollection_projectadmin 
                                                        on projectid=mdl_coripodatacollection_projects.id
                                          WHERE userid=' . $USER->id);
    foreach ($results as $r) {
        echo html_writer::start_tag('li', ['class' => 'activity activity-wrapper ']);
        echo html_writer::start_tag('div', ['class' => 'activity-item focus-control ']);

        echo html_writer::start_tag('div', ['class' => 'text-center']);
        echo html_writer::link(
                new moodle_url(
                        '/mod/coripodatacollection/projectview.php',
                        ['id' => $r->id, 'sesskey' => sesskey()]
                ),
                format_text($r->projectname, FORMAT_PLAIN),
        );
        echo html_writer::end_tag('div');

        echo html_writer::end_tag('div');
        echo html_writer::end_tag('li');
    }

    echo html_writer::end_tag('ul');

    echo $OUTPUT->box_end();
}

echo $OUTPUT->footer();
