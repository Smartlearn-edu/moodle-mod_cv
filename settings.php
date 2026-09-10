<?php
// This file is part of Moodle - http://moodle.org/
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
 * Administration settings for mod_cv.
 *
 * @package    mod_cv
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading(
        'mod_cv/ai_settings',
        get_string('aiprovider', 'mod_cv'),
        get_string('aiprovider_desc', 'mod_cv')
    ));

    $provideroptions = \mod_cv\ai_processor::get_provider_options(false);
    $settings->add(new admin_setting_configselect(
        'mod_cv/default_aiprovider',
        get_string('aiprovider', 'mod_cv'),
        get_string('aiprovider_desc', 'mod_cv'),
        \mod_cv\ai_processor::PROVIDER_AUTO,
        $provideroptions
    ));

    $settings->add(new admin_setting_heading(
        'mod_cv/n8n_settings',
        get_string('settings_n8n_heading', 'mod_cv'),
        get_string('settings_n8n_heading_desc', 'mod_cv')
    ));

    $settings->add(new admin_setting_configtext(
        'mod_cv/default_webhook_url',
        get_string('default_webhook_url', 'mod_cv'),
        get_string('default_webhook_url_desc', 'mod_cv'),
        '',
        PARAM_URL
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'mod_cv/default_auth_token',
        get_string('default_auth_token', 'mod_cv'),
        get_string('default_auth_token_desc', 'mod_cv'),
        ''
    ));

    $settings->add(new admin_setting_configtextarea(
        'mod_cv/default_prompt',
        get_string('default_prompt', 'mod_cv'),
        get_string('default_prompt_desc', 'mod_cv'),
        '',
        PARAM_RAW,
        60,
        8
    ));
}
