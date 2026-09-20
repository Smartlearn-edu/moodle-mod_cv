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

        // AI Provider selector.
        $provideroptions = \mod_cv\ai_processor::get_provider_options(true);
        $mform->addElement('select', 'aiprovider', get_string('aiprovider', 'mod_cv'), $provideroptions);
        $mform->setDefault('aiprovider', \mod_cv\ai_processor::PROVIDER_DEFAULT);
        $mform->addHelpButton('aiprovider', 'aiprovider', 'mod_cv');

        $mform->addElement('text', 'webhookurl', get_string('webhook_url_override', 'mod_cv'), ['size' => '64']);
        $mform->setType('webhookurl', PARAM_URL);
        $mform->addHelpButton('webhookurl', 'webhook_url_override', 'mod_cv');
        $mform->hideIf('webhookurl', 'aiprovider', 'in', [
            \mod_cv\ai_processor::PROVIDER_AIHUB,
            \mod_cv\ai_processor::PROVIDER_CORE_AI,
        ]);

        $mform->addElement('textarea', 'customprompt', get_string('custom_prompt', 'mod_cv'), ['rows' => 6, 'cols' => 60]);
        $mform->setType('customprompt', PARAM_RAW);
        $mform->addHelpButton('customprompt', 'custom_prompt', 'mod_cv');

        // Section display options.
        $mform->addElement('header', 'displayoptions', get_string('display_settings', 'mod_cv'));

        $mform->addElement('selectyesno', 'showcandidateinfo', get_string('show_candidate_info', 'mod_cv'));
        $mform->setDefault('showcandidateinfo', 1);
        $mform->setType('showcandidateinfo', PARAM_INT);
        $mform->addHelpButton('showcandidateinfo', 'show_candidate_info', 'mod_cv');

        $mform->addElement('selectyesno', 'showcourseeducation', get_string('show_course_education', 'mod_cv'));
        $mform->setDefault('showcourseeducation', 1);
        $mform->setType('showcourseeducation', PARAM_INT);
        $mform->addHelpButton('showcourseeducation', 'show_course_education', 'mod_cv');

        $mform->addElement('selectyesno', 'showreview', get_string('show_review', 'mod_cv'));
        $mform->setDefault('showreview', 1);
        $mform->setType('showreview', PARAM_INT);
        $mform->addHelpButton('showreview', 'show_review', 'mod_cv');

        $mform->addElement('selectyesno', 'showsummary', get_string('show_summary', 'mod_cv'));
        $mform->setDefault('showsummary', 1);
        $mform->setType('showsummary', PARAM_INT);
        $mform->addHelpButton('showsummary', 'show_summary', 'mod_cv');

        // Attempts & Generation Limit options.
        $mform->addElement('header', 'attemptoptions', get_string('attempt_settings', 'mod_cv'));

        $attemptoptions = [0 => get_string('unlimited', 'mod_cv')];
        for ($i = 1; $i <= 20; $i++) {
            $attemptoptions[$i] = $i;
        }
        $mform->addElement('select', 'maxattempts', get_string('max_attempts', 'mod_cv'), $attemptoptions);
        $mform->setDefault('maxattempts', 0);
        $mform->setType('maxattempts', PARAM_INT);
        $mform->addHelpButton('maxattempts', 'max_attempts', 'mod_cv');

        // Project Fields Configuration.
        $mform->addElement('header', 'projectfieldsheader', get_string('project_fields_settings', 'mod_cv'));
        $mform->addHelpButton('projectfieldsheader', 'project_fields_settings', 'mod_cv');

        $mform->addElement('hidden', 'projectfields');
        $mform->setType('projectfields', PARAM_RAW);

        // Container element for interactive field management UI.
        $fieldmanagerhtml = '
        <div id="cv_project_fields_manager" class="cv-fields-manager-container mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <button type="button" class="btn btn-primary btn-sm" id="btn_add_custom_field">
                        <i class="fa fa-plus"></i> ' . get_string('add_custom_field', 'mod_cv') . '
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn_reset_default_fields">
                        <i class="fa fa-refresh"></i> ' . get_string('reset_default_fields', 'mod_cv') . '
                    </button>
                </div>
                <div class="text-muted small">
                    <span class="badge badge-info bg-info text-white" id="cv_fields_count_badge">0 fields</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover cv-fields-table align-middle" id="cv_fields_table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;" class="text-center">#</th>
                            <th>' . get_string('field_label', 'mod_cv') . '</th>
                            <th>' . get_string('field_key', 'mod_cv') . '</th>
                            <th>' . get_string('field_type', 'mod_cv') . '</th>
                            <th>' . get_string('field_section', 'mod_cv') . '</th>
                            <th class="text-center">' . get_string('field_required', 'mod_cv') . '</th>
                            <th class="text-center">' . get_string('status_active', 'mod_cv') . '</th>
                            <th class="text-end" style="width: 140px;">' . get_string('actions') . '</th>
                        </tr>
                    </thead>
                    <tbody id="cv_fields_table_body">
                        <!-- Populated by AMD Javascript -->
                    </tbody>
                </table>
            </div>

            <!-- Field Edit/Create Modal -->
            <div class="modal fade" id="cvFieldModal" tabindex="-1" aria-labelledby="cvFieldModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="cvFieldModalLabel">' . get_string('add_custom_field', 'mod_cv') . '</h5>
                            <button type="button" class="btn-close close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="' . get_string('closebuttontitle') . '">&times;</button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="modal_field_original_key" value="">
                            <div class="mb-3">
                                <label for="modal_field_label" class="form-label font-weight-bold">' . get_string('field_label', 'mod_cv') . ' <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="modal_field_label" required placeholder="e.g. Project Budget">
                            </div>
                            <div class="mb-3">
                                <label for="modal_field_key" class="form-label font-weight-bold">' . get_string('field_key', 'mod_cv') . ' <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="modal_field_key" required placeholder="e.g. project_budget">
                                <small class="form-text text-muted">Lowercase letters, numbers, and underscores only.</small>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="modal_field_type" class="form-label font-weight-bold">' . get_string('field_type', 'mod_cv') . '</label>
                                    <select class="form-select custom-select" id="modal_field_type">
                                        <option value="text">' . get_string('type_text', 'mod_cv') . '</option>
                                        <option value="textarea">' . get_string('type_textarea', 'mod_cv') . '</option>
                                        <option value="select">' . get_string('type_select', 'mod_cv') . '</option>
                                        <option value="date">' . get_string('type_date', 'mod_cv') . '</option>
                                        <option value="number">' . get_string('type_number', 'mod_cv') . '</option>
                                        <option value="checkbox">' . get_string('type_checkbox', 'mod_cv') . '</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="modal_field_section" class="form-label font-weight-bold">' . get_string('field_section', 'mod_cv') . '</label>
                                    <select class="form-select custom-select" id="modal_field_section">
                                        <option value="basic">' . get_string('section_basic', 'mod_cv') . '</option>
                                        <option value="timeline">' . get_string('section_timeline', 'mod_cv') . '</option>
                                        <option value="deliverables">' . get_string('section_deliverables', 'mod_cv') . '</option>
                                        <option value="governance">' . get_string('section_governance', 'mod_cv') . '</option>
                                        <option value="custom">' . get_string('section_custom', 'mod_cv') . '</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3" id="modal_field_options_group" style="display: none;">
                                <label for="modal_field_options" class="form-label font-weight-bold">' . get_string('field_options', 'mod_cv') . '</label>
                                <textarea class="form-control" id="modal_field_options" rows="3" placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
                                <small class="form-text text-muted">' . get_string('field_options_help', 'mod_cv') . '</small>
                            </div>
                            <div class="mb-3">
                                <label for="modal_field_placeholder" class="form-label font-weight-bold">' . get_string('field_placeholder', 'mod_cv') . '</label>
                                <input type="text" class="form-control" id="modal_field_placeholder" placeholder="e.g. Ex.: $100,000">
                            </div>
                            <div class="mb-3">
                                <label for="modal_field_helptext" class="form-label font-weight-bold">' . get_string('field_helptext', 'mod_cv') . '</label>
                                <input type="text" class="form-control" id="modal_field_helptext" placeholder="Short description shown below the input">
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="modal_field_required">
                                <label class="form-check-label" for="modal_field_required">' . get_string('field_required', 'mod_cv') . '</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modal_field_enabled" checked>
                                <label class="form-check-label" for="modal_field_enabled">' . get_string('status_active', 'mod_cv') . '</label>
                            </div>
                            <div class="alert alert-danger d-none mt-3 mb-0" id="modal_field_error"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">' . get_string('cancel') . '</button>
                            <button type="button" class="btn btn-primary" id="btn_save_modal_field">' . get_string('save_field', 'mod_cv') . '</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>';

        $mform->addElement('html', $fieldmanagerhtml);

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
        $defaultvalues['aiprovider'] = $defaultvalues['aiprovider'] ?? \mod_cv\ai_processor::PROVIDER_DEFAULT;
        $defaultvalues['showcandidateinfo'] = isset($defaultvalues['showcandidateinfo']) ?
            (int) $defaultvalues['showcandidateinfo'] : 1;
        $defaultvalues['showcourseeducation'] = isset($defaultvalues['showcourseeducation']) ?
            (int) $defaultvalues['showcourseeducation'] : 1;
        $defaultvalues['showreview'] = isset($defaultvalues['showreview']) ?
            (int) $defaultvalues['showreview'] : 1;
        $defaultvalues['showsummary'] = isset($defaultvalues['showsummary']) ?
            (int) $defaultvalues['showsummary'] : 1;
        $defaultvalues['maxattempts'] = isset($defaultvalues['maxattempts']) ?
            (int) $defaultvalues['maxattempts'] : 0;

        $rawfields = $defaultvalues['projectfields'] ?? null;
        if (!empty($rawfields) && is_string($rawfields)) {
            $decoded = json_decode($rawfields, true);
            if (is_array($decoded)) {
                $fields = \mod_cv\fields_manager::sanitize_fields($decoded);
            } else {
                $fields = \mod_cv\fields_manager::get_default_fields();
            }
        } else {
            $fields = \mod_cv\fields_manager::get_default_fields();
        }

        $fieldsjson = json_encode($fields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $defaultvalues['projectfields'] = $fieldsjson;

        // Initialize AMD Javascript for the form fields manager.
        global $PAGE;
        $sections = \mod_cv\fields_manager::get_sections();
        $types = \mod_cv\fields_manager::get_types();
        $PAGE->requires->js_call_amd('mod_cv/form_fields', 'init', [$fields, $sections, $types]);
    }

    /**
     * Ensure form fields and AMD module are initialized for new activity creation.
     */
    public function definition_after_data() {
        parent::definition_after_data();

        $mform = $this->_form;
        $val = $mform->getElementValue('projectfields');
        if (empty($val)) {
            $defaultfields = \mod_cv\fields_manager::get_default_fields();
            $fieldsjson = json_encode($defaultfields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $mform->setDefault('projectfields', $fieldsjson);

            global $PAGE;
            $sections = \mod_cv\fields_manager::get_sections();
            $types = \mod_cv\fields_manager::get_types();
            $PAGE->requires->js_call_amd('mod_cv/form_fields', 'init', [$defaultfields, $sections, $types]);
        }
    }
}
