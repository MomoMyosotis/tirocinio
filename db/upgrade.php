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
 * Plugin upgrade steps are defined here.
 *
 * @package     mod_coripodatacollection
 * @category    upgrade
 * @copyright   2024 Cordioli Davide cordiolidavide1@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/upgradelib.php');

/**
 * Execute mod_coripodatacollection upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_coripodatacollection_upgrade($oldversion) {

    global $DB, $OUTPUT;
    $DBManager = $DB->get_manager();

    // For further information please read {@link https://docs.moodle.org/dev/Upgrade_API}.
    //
    // You will also have to create the db/install.xml file by using the XMLDB Editor.
    // Documentation for the XMLDB Editor can be found at {@link https://docs.moodle.org/dev/XMLDB_editor}.

    if ($oldversion < 2026011901) {

        $xmlFile = '../mod/coripodatacollection/db/install.xml'; // Percorso al file XML
        if (!file_exists($xmlFile)) {
            die("Il file XML non esiste: " . getcwd());
        }

        $xml = simplexml_load_file($xmlFile);
        foreach ($xml->TABLES->TABLE as $table) {
            if (!$DBManager->table_exists((string)$table['NAME'])) {
                $DBManager->install_one_table_from_xmldb_file($xmlFile, (string)$table['NAME']);
                $paramerror = [
                        'title-error' => 'Aggiornamento database:',
                        'message-error' => '  Creata tabella ' . $table['NAME']
                ];
                echo $OUTPUT->render_from_template('coripodatacollection/allertsuccess', $paramerror);

            }

            foreach ($table->FIELDS->FIELD as $field) {

                if (!$DBManager->field_exists((string)$table['NAME'], (string) $field['NAME'])) {

                    $xmldb_table = new xmldb_table((string) $table['NAME']);

                    $fieldname = (string) $field['NAME'];
                    $fieldtype = (string) $field['TYPE'];
                    $fieldlength = (string) $field['LENGTH'];
                    $fieldunsigned = isset($field['UNSIGNED']) ?
                            filter_var($field['UNSIGNED'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                            : null;
                    $fieldnotnull = isset($field['NOTNULL']) ?
                            filter_var($field['NOTNULL'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                            : null;
                    $fieldsequence = isset($field['SEQUENCE']) ?
                            filter_var($field['SEQUENCE'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                            : null;

                    if (isset($field['DECIMALS'])) {
                        $fieldlength .= ',' . $field['DECIMALS'];
                    }

                    $type_mapping = [
                            'text' => XMLDB_TYPE_TEXT,
                            'char' => XMLDB_TYPE_CHAR,
                            'number' => XMLDB_TYPE_NUMBER,
                            'integer' => XMLDB_TYPE_INTEGER
                    ];

                    $moodletype = $type_mapping[$fieldtype] ?? XMLDB_TYPE_CHAR;

                    $xmldb_field = new xmldb_field(
                            $fieldname,
                            $moodletype,
                            $fieldlength,
                            $fieldunsigned,
                            $fieldnotnull,
                            $fieldsequence
                    );

                    $DBManager->add_field($xmldb_table, $xmldb_field);

                    $paramerror = [
                            'title-error' => 'Aggiornamento tabella ' . $table['NAME'],
                            'message-error' => ':  Colonna ' . $field['NAME'] . ' aggiunta'
                    ];
                    echo $OUTPUT->render_from_template('coripodatacollection/allertsuccess', $paramerror);

                }
            }
        }
    }

    return true;
}
