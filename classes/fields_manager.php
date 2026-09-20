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
 * Fields manager helper for dynamic mod_cv project fields.
 *
 * @package    mod_cv
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_cv;

/**
 * Class fields_manager
 *
 * Provides utilities for managing, sanitizing, and retrieving project experience
 * field definitions per activity instance.
 */
class fields_manager {
    /** @var string Basic project information section. */
    public const SECTION_BASIC = 'basic';

    /** @var string Project timeline section. */
    public const SECTION_TIMELINE = 'timeline';

    /** @var string Core deliverables and outcomes section. */
    public const SECTION_DELIVERABLES = 'deliverables';

    /** @var string Stakeholders and governance section. */
    public const SECTION_GOVERNANCE = 'governance';

    /** @var string Custom/additional section. */
    public const SECTION_CUSTOM = 'custom';

    /** @var string Single-line text input. */
    public const TYPE_TEXT = 'text';

    /** @var string Multi-line textarea. */
    public const TYPE_TEXTAREA = 'textarea';

    /** @var string Dropdown selection. */
    public const TYPE_SELECT = 'select';

    /** @var string Date picker input. */
    public const TYPE_DATE = 'date';

    /** @var string Number input. */
    public const TYPE_NUMBER = 'number';

    /** @var string Checkbox input. */
    public const TYPE_CHECKBOX = 'checkbox';

    /**
     * Get default standard project experience fields.
     *
     * @return array Array of standard field definition dictionaries.
     */
    public static function get_default_fields(): array {
        return [
            // 1. Basic Project Information.
            [
                'key' => 'title',
                'label' => get_string('field_project_name', 'mod_cv'),
                'type' => self::TYPE_TEXT,
                'section' => self::SECTION_BASIC,
                'required' => true,
                'placeholder' => get_string('placeholder_project_name', 'mod_cv'),
                'helptext' => '',
                'options' => [],
                'enabled' => true,
                'sortorder' => 1,
                'is_standard' => true,
            ],
            [
                'key' => 'industry',
                'label' => get_string('field_industry', 'mod_cv'),
                'type' => self::TYPE_TEXT,
                'section' => self::SECTION_BASIC,
                'required' => true,
                'placeholder' => get_string('placeholder_industry', 'mod_cv'),
                'helptext' => '',
                'options' => [],
                'enabled' => true,
                'sortorder' => 2,
                'is_standard' => true,
            ],
            [
                'key' => 'organization',
                'label' => get_string('field_organization', 'mod_cv'),
                'type' => self::TYPE_TEXT,
                'section' => self::SECTION_BASIC,
                'required' => false,
                'placeholder' => get_string('placeholder_organization', 'mod_cv'),
                'helptext' => '',
                'options' => [],
                'enabled' => true,
                'sortorder' => 3,
                'is_standard' => true,
            ],
            [
                'key' => 'jobtitle',
                'label' => get_string('field_job_title', 'mod_cv'),
                'type' => self::TYPE_TEXT,
                'section' => self::SECTION_BASIC,
                'required' => true,
                'placeholder' => get_string('placeholder_job_title', 'mod_cv'),
                'helptext' => '',
                'options' => [],
                'enabled' => true,
                'sortorder' => 4,
                'is_standard' => true,
            ],
            [
                'key' => 'role',
                'label' => get_string('field_project_role', 'mod_cv'),
                'type' => self::TYPE_TEXT,
                'section' => self::SECTION_BASIC,
                'required' => true,
                'placeholder' => get_string('placeholder_project_role', 'mod_cv'),
                'helptext' => '',
                'options' => [],
                'enabled' => true,
                'sortorder' => 5,
                'is_standard' => true,
            ],
            [
                'key' => 'methodology',
                'label' => get_string('field_methodology', 'mod_cv'),
                'type' => self::TYPE_SELECT,
                'section' => self::SECTION_BASIC,
                'required' => true,
                'placeholder' => get_string('placeholder_methodology', 'mod_cv'),
                'helptext' => '',
                'options' => [
                    ['value' => 'predictive', 'label' => get_string('methodology_predictive', 'mod_cv')],
                    ['value' => 'agile', 'label' => get_string('methodology_agile', 'mod_cv')],
                    ['value' => 'hybrid', 'label' => get_string('methodology_hybrid', 'mod_cv')],
                ],
                'enabled' => true,
                'sortorder' => 6,
                'is_standard' => true,
            ],

            // 2. Project Timeline.
            [
                'key' => 'startdate',
                'label' => get_string('field_start_date', 'mod_cv'),
                'type' => self::TYPE_DATE,
                'section' => self::SECTION_TIMELINE,
                'required' => true,
                'placeholder' => get_string('placeholder_start_date', 'mod_cv'),
                'helptext' => get_string('help_date_select', 'mod_cv'),
                'options' => [],
                'enabled' => true,
                'sortorder' => 7,
                'is_standard' => true,
            ],
            [
                'key' => 'enddate',
                'label' => get_string('field_end_date', 'mod_cv'),
                'type' => self::TYPE_DATE,
                'section' => self::SECTION_TIMELINE,
                'required' => true,
                'placeholder' => get_string('placeholder_end_date', 'mod_cv'),
                'helptext' => get_string('help_date_select', 'mod_cv'),
                'options' => [],
                'enabled' => true,
                'sortorder' => 8,
                'is_standard' => true,
            ],
            [
                'key' => 'iscurrent',
                'label' => get_string('project_is_current', 'mod_cv'),
                'type' => self::TYPE_CHECKBOX,
                'section' => self::SECTION_TIMELINE,
                'required' => false,
                'placeholder' => '',
                'helptext' => '',
                'options' => [],
                'enabled' => true,
                'sortorder' => 9,
                'is_standard' => true,
            ],

            // 3. Core Project Experience & Deliverables.
            [
                'key' => 'objective',
                'label' => get_string('field_objective', 'mod_cv'),
                'type' => self::TYPE_TEXTAREA,
                'section' => self::SECTION_DELIVERABLES,
                'required' => true,
                'placeholder' => get_string('placeholder_objective', 'mod_cv'),
                'helptext' => '',
                'options' => [],
                'enabled' => true,
                'sortorder' => 10,
                'is_standard' => true,
            ],
            [
                'key' => 'scope',
                'label' => get_string('field_scope', 'mod_cv'),
                'type' => self::TYPE_TEXTAREA,
                'section' => self::SECTION_DELIVERABLES,
                'required' => true,
                'placeholder' => get_string('placeholder_scope', 'mod_cv'),
                'helptext' => '',
                'options' => [],
                'enabled' => true,
                'sortorder' => 11,
                'is_standard' => true,
            ],
            [
                'key' => 'responsibilities',
                'label' => get_string('field_responsibilities', 'mod_cv'),
                'type' => self::TYPE_TEXTAREA,
                'section' => self::SECTION_DELIVERABLES,
                'required' => true,
                'placeholder' => get_string('placeholder_responsibilities', 'mod_cv'),
                'helptext' => '',
                'options' => [],
                'enabled' => true,
                'sortorder' => 12,
                'is_standard' => true,
            ],
            [
                'key' => 'deliverables',
                'label' => get_string('field_deliverables', 'mod_cv'),
                'type' => self::TYPE_TEXTAREA,
                'section' => self::SECTION_DELIVERABLES,
                'required' => true,
                'placeholder' => get_string('placeholder_deliverables', 'mod_cv'),
                'helptext' => '',
                'options' => [],
                'enabled' => true,
                'sortorder' => 13,
                'is_standard' => true,
            ],
            [
                'key' => 'challenges',
                'label' => get_string('field_challenges', 'mod_cv'),
                'type' => self::TYPE_TEXTAREA,
                'section' => self::SECTION_DELIVERABLES,
                'required' => true,
                'placeholder' => get_string('placeholder_challenges', 'mod_cv'),
                'helptext' => '',
                'options' => [],
                'enabled' => true,
                'sortorder' => 14,
                'is_standard' => true,
            ],
            [
                'key' => 'outcomes',
                'label' => get_string('field_outcomes', 'mod_cv'),
                'type' => self::TYPE_TEXTAREA,
                'section' => self::SECTION_DELIVERABLES,
                'required' => true,
                'placeholder' => get_string('placeholder_outcomes', 'mod_cv'),
                'helptext' => '',
                'options' => [],
                'enabled' => true,
                'sortorder' => 15,
                'is_standard' => true,
            ],

            // 4. Stakeholders, Resources & Governance.
            [
                'key' => 'stakeholders',
                'label' => get_string('field_stakeholders', 'mod_cv'),
                'type' => self::TYPE_TEXT,
                'section' => self::SECTION_GOVERNANCE,
                'required' => false,
                'placeholder' => get_string('placeholder_stakeholders', 'mod_cv'),
                'helptext' => '',
                'options' => [],
                'enabled' => true,
                'sortorder' => 16,
                'is_standard' => true,
            ],
            [
                'key' => 'teamresources',
                'label' => get_string('field_team_resources', 'mod_cv'),
                'type' => self::TYPE_TEXT,
                'section' => self::SECTION_GOVERNANCE,
                'required' => false,
                'placeholder' => get_string('placeholder_team_resources', 'mod_cv'),
                'helptext' => '',
                'options' => [],
                'enabled' => true,
                'sortorder' => 17,
                'is_standard' => true,
            ],
            [
                'key' => 'changes',
                'label' => get_string('field_changes', 'mod_cv'),
                'type' => self::TYPE_TEXT,
                'section' => self::SECTION_GOVERNANCE,
                'required' => false,
                'placeholder' => get_string('placeholder_changes', 'mod_cv'),
                'helptext' => '',
                'options' => [],
                'enabled' => true,
                'sortorder' => 18,
                'is_standard' => true,
            ],
            [
                'key' => 'measurableresults',
                'label' => get_string('field_measurable_results', 'mod_cv'),
                'type' => self::TYPE_TEXT,
                'section' => self::SECTION_GOVERNANCE,
                'required' => false,
                'placeholder' => get_string('placeholder_measurable_results', 'mod_cv'),
                'helptext' => '',
                'options' => [],
                'enabled' => true,
                'sortorder' => 19,
                'is_standard' => true,
            ],
            [
                'key' => 'closure',
                'label' => get_string('field_closure', 'mod_cv'),
                'type' => self::TYPE_TEXT,
                'section' => self::SECTION_GOVERNANCE,
                'required' => false,
                'placeholder' => get_string('placeholder_closure', 'mod_cv'),
                'helptext' => '',
                'options' => [],
                'enabled' => true,
                'sortorder' => 20,
                'is_standard' => true,
            ],
            [
                'key' => 'additionalinfo',
                'label' => get_string('field_additional_info', 'mod_cv'),
                'type' => self::TYPE_TEXTAREA,
                'section' => self::SECTION_GOVERNANCE,
                'required' => false,
                'placeholder' => get_string('placeholder_additional_info', 'mod_cv'),
                'helptext' => '',
                'options' => [],
                'enabled' => true,
                'sortorder' => 21,
                'is_standard' => true,
            ],
        ];
    }

    /**
     * Get configured project fields for a CV activity instance.
     *
     * Falls back to default standard fields if none are defined.
     *
     * @param object|null $cv Activity database record.
     * @return array Array of sanitized field definitions.
     */
    public static function get_fields(?object $cv = null): array {
        if (!empty($cv->projectfields) && is_string($cv->projectfields)) {
            $decoded = json_decode($cv->projectfields, true);
            if (is_array($decoded) && !empty($decoded)) {
                return self::sanitize_fields($decoded);
            }
        }
        return self::get_default_fields();
    }

    /**
     * Sanitize and validate an array of field definitions.
     *
     * @param array $rawfields Raw input field definitions.
     * @return array Sanitized field definitions sorted by sortorder.
     */
    public static function sanitize_fields(array $rawfields): array {
        $validsections = [
            self::SECTION_BASIC,
            self::SECTION_TIMELINE,
            self::SECTION_DELIVERABLES,
            self::SECTION_GOVERNANCE,
            self::SECTION_CUSTOM,
        ];

        $validtypes = [
            self::TYPE_TEXT,
            self::TYPE_TEXTAREA,
            self::TYPE_SELECT,
            self::TYPE_DATE,
            self::TYPE_NUMBER,
            self::TYPE_CHECKBOX,
        ];

        $sanitized = [];
        $index = 1;

        foreach ($rawfields as $f) {
            if (!is_array($f)) {
                continue;
            }

            // Key sanitization: lowercase alphanumeric and underscores only.
            $rawkey = (string) ($f['key'] ?? '');
            $cleankey = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $rawkey));
            if ($cleankey === '') {
                $rawlabel = (string) ($f['label'] ?? '');
                $cleankey = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $rawlabel)));
                if ($cleankey === '') {
                    $cleankey = 'custom_field_' . $index;
                }
            }

            $label = trim((string) ($f['label'] ?? $cleankey));
            if ($label === '') {
                $label = ucfirst(str_replace('_', ' ', $cleankey));
            }

            $type = (string) ($f['type'] ?? self::TYPE_TEXT);
            if (!in_array($type, $validtypes, true)) {
                $type = self::TYPE_TEXT;
            }

            $section = (string) ($f['section'] ?? self::SECTION_CUSTOM);
            if (!in_array($section, $validsections, true)) {
                $section = self::SECTION_CUSTOM;
            }

            $required = !empty($f['required']);
            $placeholder = trim((string) ($f['placeholder'] ?? ''));
            $helptext = trim((string) ($f['helptext'] ?? ''));

            // Parse options for select dropdowns.
            $options = [];
            if (!empty($f['options'])) {
                if (is_array($f['options'])) {
                    foreach ($f['options'] as $opt) {
                        if (is_array($opt) && isset($opt['value'])) {
                            $optval = trim((string) $opt['value']);
                            $optlbl = trim((string) ($opt['label'] ?? $optval));
                            if ($optval !== '') {
                                $options[] = ['value' => $optval, 'label' => $optlbl];
                            }
                        } else if (is_string($opt) && trim($opt) !== '') {
                            $optval = trim($opt);
                            $options[] = ['value' => $optval, 'label' => $optval];
                        }
                    }
                } else if (is_string($f['options'])) {
                    $lines = preg_split('/[\r\n,]+/', $f['options']);
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if ($line !== '') {
                            $options[] = ['value' => $line, 'label' => $line];
                        }
                    }
                }
            }

            $enabled = isset($f['enabled']) ? !empty($f['enabled']) : true;
            $sortorder = isset($f['sortorder']) ? (int) $f['sortorder'] : $index;
            $isstandard = !empty($f['is_standard']);

            $sanitized[] = [
                'key' => $cleankey,
                'label' => $label,
                'type' => $type,
                'section' => $section,
                'required' => $required,
                'placeholder' => $placeholder,
                'helptext' => $helptext,
                'options' => $options,
                'enabled' => $enabled,
                'sortorder' => $sortorder,
                'is_standard' => $isstandard,
            ];

            $index++;
        }

        // Sort by sortorder ascending.
        usort($sanitized, function ($a, $b) {
            return ($a['sortorder'] ?? 0) <=> ($b['sortorder'] ?? 0);
        });

        // Re-index sort order sequentially.
        foreach ($sanitized as $k => &$item) {
            $item['sortorder'] = $k + 1;
        }

        return $sanitized;
    }

    /**
     * Get all supported sections with localized labels and icons.
     *
     * @return array Associative array of section data.
     */
    public static function get_sections(): array {
        return [
            self::SECTION_BASIC => [
                'id' => self::SECTION_BASIC,
                'title' => get_string('section_basic', 'mod_cv'),
                'icon' => 'fa fa-id-card',
            ],
            self::SECTION_TIMELINE => [
                'id' => self::SECTION_TIMELINE,
                'title' => get_string('section_timeline', 'mod_cv'),
                'icon' => 'fa fa-calendar',
            ],
            self::SECTION_DELIVERABLES => [
                'id' => self::SECTION_DELIVERABLES,
                'title' => get_string('section_deliverables', 'mod_cv'),
                'icon' => 'fa fa-tasks',
            ],
            self::SECTION_GOVERNANCE => [
                'id' => self::SECTION_GOVERNANCE,
                'title' => get_string('section_governance', 'mod_cv'),
                'icon' => 'fa fa-users',
            ],
            self::SECTION_CUSTOM => [
                'id' => self::SECTION_CUSTOM,
                'title' => get_string('section_custom', 'mod_cv'),
                'icon' => 'fa fa-list-alt',
            ],
        ];
    }

    /**
     * Get all supported field types with localized labels.
     *
     * @return array Associative array of field types.
     */
    public static function get_types(): array {
        return [
            self::TYPE_TEXT => get_string('type_text', 'mod_cv'),
            self::TYPE_TEXTAREA => get_string('type_textarea', 'mod_cv'),
            self::TYPE_SELECT => get_string('type_select', 'mod_cv'),
            self::TYPE_DATE => get_string('type_date', 'mod_cv'),
            self::TYPE_NUMBER => get_string('type_number', 'mod_cv'),
            self::TYPE_CHECKBOX => get_string('type_checkbox', 'mod_cv'),
        ];
    }
}
