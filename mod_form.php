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
 * Activity configuration form for mod_cv.
 *
 * @package    mod_cv
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Module settings form for mod_cv.
 */
class mod_cv_mod_form extends moodleform_mod {
    /**
     * Define the form elements.
     */
    public function definition() {
        $mform = $this->_form;

        // General settings.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), ['size' => '64']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        // Certification & Track Configuration.
        $mform->addElement('header', 'examsettings', get_string('activity_settings', 'mod_cv'));

        // Domain / Professional Track selector.
        $domainoptions = \mod_cv\domains::get_domains();
        $mform->addElement('select', 'fieldtype', get_string('field_type', 'mod_cv'), $domainoptions);
        $mform->setDefault('fieldtype', \mod_cv\domains::DOMAIN_PMI);
        $mform->addHelpButton('fieldtype', 'field_type', 'mod_cv');

        // Dynamic certification dropdowns per domain track.
        foreach (array_keys($domainoptions) as $domainkey) {
            $certoptions = \mod_cv\domains::get_certifications($domainkey);
            $elementname = 'examtype_' . $domainkey;
            $mform->addElement('select', $elementname, get_string('exam_type', 'mod_cv'), $certoptions);
            $defaultcert = key($certoptions);
            $mform->setDefault($elementname, $defaultcert);
            $mform->hideIf($elementname, 'fieldtype', 'neq', $domainkey);
        }

        // Custom certification / track title (optional override).
        $mform->addElement('text', 'customcert', get_string('custom_cert_name', 'mod_cv'), ['size' => '48']);
        $mform->setType('customcert', PARAM_TEXT);
        $mform->addHelpButton('customcert', 'custom_cert_name', 'mod_cv');

        $mform->addElement('text', 'contacthours', get_string('contact_hours', 'mod_cv'), ['size' => '10']);
        $mform->setType('contacthours', PARAM_INT);
        $mform->setDefault('contacthours', 35);
        $mform->addHelpButton('contacthours', 'contact_hours', 'mod_cv');

        $mform->addElement('text', 'providername', get_string('provider_name', 'mod_cv'), ['size' => '48']);
        $mform->setType('providername', PARAM_TEXT);
        $mform->setDefault('providername', 'SmartLearn Education');
        $mform->addHelpButton('providername', 'provider_name', 'mod_cv');

        $mform->addElement('text', 'webhookurl', get_string('webhook_url_override', 'mod_cv'), ['size' => '64']);
        $mform->setType('webhookurl', PARAM_URL);
        $mform->addHelpButton('webhookurl', 'webhook_url_override', 'mod_cv');

        $mform->addElement('textarea', 'customprompt', get_string('custom_prompt', 'mod_cv'), ['rows' => 6, 'cols' => 60]);
        $mform->setType('customprompt', PARAM_RAW);
        $mform->addHelpButton('customprompt', 'custom_prompt', 'mod_cv');

        // Standard course module elements.
        $this->standard_coursemodule_elements();

        // Standard buttons.
        $this->add_action_buttons();
    }

    /**
     * Prepare form data before display.
     *
     * @param array $defaultvalues
     */
    public function data_preprocessing(&$defaultvalues) {
        parent::data_preprocessing($defaultvalues);

        $fieldtype = $defaultvalues['fieldtype'] ?? \mod_cv\domains::DOMAIN_PMI;
        $examtype = $defaultvalues['examtype'] ?? 'pmp';
        $defaultvalues['fieldtype'] = $fieldtype;
        $defaultvalues['examtype_' . $fieldtype] = $examtype;
    }
}
