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

defined('MOODLE_INTERNAL') || die();

$capabilities = [
        'mod/coripodatacollection:addinstance' => [
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => [
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW,
                ],
                'clonepermissionsfrom' => 'moodle/course:manageactivities',
        ],
        'mod/coripodatacollection:view' => [
                'captype' => 'read',
                'contextlevel' => CONTEXT_MODULE,
                'archetypes' => [
                        'guest' => CAP_ALLOW,
                        'student' => CAP_ALLOW,
                        'teacher' => CAP_ALLOW,
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW,
                ],
        ],

        'mod/coripodatacollection:projectadmin' => [
                'riskbitmask' => RISK_SPAM,
                'captype' => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
        ],
        'mod/coripodatacollection:evaluator' => [
                'riskbitmask' => RISK_SPAM,
                'captype' => 'read',
                'contextlevel' => CONTEXT_SYSTEM,
        ],
        'mod/coripodatacollection:schoolmanager' => [
                'riskbitmask' => RISK_SPAM,
                'captype' => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
        ],
        'mod/coripodatacollection:teacher' => [
                'riskbitmask' => RISK_SPAM,
                'captype' => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
        ],
        'mod/coripodatacollection:operator' => [
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
]
];
