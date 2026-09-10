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

namespace mod_cv;

/**
 * Domain tracks and certifications registry for mod_cv.
 *
 * @package    mod_cv
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class domains {
    /** @var string Domain constant for Project Management */
    public const DOMAIN_PMI = 'pmi';

    /** @var string Domain constant for Healthcare & Medical */
    public const DOMAIN_MEDICAL = 'medical';

    /** @var string Domain constant for Computer & IT */
    public const DOMAIN_COMPUTER = 'computer';

    /** @var string Domain constant for Languages & Translation */
    public const DOMAIN_LANGUAGES = 'languages';

    /** @var string Domain constant for Business & Finance */
    public const DOMAIN_BUSINESS = 'business';

    /** @var string Domain constant for General Professional */
    public const DOMAIN_GENERAL = 'general';

    /**
     * Get all available domains as key => display string map.
     *
     * @return array
     */
    public static function get_domains(): array {
        return [
            self::DOMAIN_PMI => get_string('field_pmi', 'mod_cv'),
            self::DOMAIN_MEDICAL => get_string('field_medical', 'mod_cv'),
            self::DOMAIN_COMPUTER => get_string('field_computer', 'mod_cv'),
            self::DOMAIN_LANGUAGES => get_string('field_languages', 'mod_cv'),
            self::DOMAIN_BUSINESS => get_string('field_business', 'mod_cv'),
            self::DOMAIN_GENERAL => get_string('field_general', 'mod_cv'),
        ];
    }

    /**
     * Get available certifications for a specific domain.
     *
     * @param string $domain The domain key.
     * @return array Array of certification key => display string.
     */
    public static function get_certifications(string $domain): array {
        switch ($domain) {
            case self::DOMAIN_PMI:
                return [
                    'pmp' => get_string('exam_pmp', 'mod_cv'),
                    'capm' => get_string('exam_capm', 'mod_cv'),
                    'pmi_acp' => get_string('exam_pmi_acp', 'mod_cv'),
                    'pmi_rmp' => get_string('exam_pmi_rmp', 'mod_cv'),
                    'pmi_pba' => get_string('exam_pmi_pba', 'mod_cv'),
                    'pgmp' => get_string('exam_pgmp', 'mod_cv'),
                    'pmi_custom' => get_string('exam_pmi_custom', 'mod_cv'),
                ];

            case self::DOMAIN_MEDICAL:
                return [
                    'med_board' => get_string('exam_med_board', 'mod_cv'),
                    'med_rn' => get_string('exam_med_rn', 'mod_cv'),
                    'med_bls_acls' => get_string('exam_med_bls_acls', 'mod_cv'),
                    'med_fellow' => get_string('exam_med_fellow', 'mod_cv'),
                    'med_bps' => get_string('exam_med_bps', 'mod_cv'),
                    'med_admin' => get_string('exam_med_admin', 'mod_cv'),
                    'med_custom' => get_string('exam_med_custom', 'mod_cv'),
                ];

            case self::DOMAIN_COMPUTER:
                return [
                    'cs_aws' => get_string('exam_cs_aws', 'mod_cv'),
                    'cs_cisco' => get_string('exam_cs_cisco', 'mod_cv'),
                    'cs_security' => get_string('exam_cs_security', 'mod_cv'),
                    'cs_kubernetes' => get_string('exam_cs_kubernetes', 'mod_cv'),
                    'cs_data_ai' => get_string('exam_cs_data_ai', 'mod_cv'),
                    'cs_fullstack' => get_string('exam_cs_fullstack', 'mod_cv'),
                    'cs_custom' => get_string('exam_cs_custom', 'mod_cv'),
                ];

            case self::DOMAIN_LANGUAGES:
                return [
                    'lang_ielts_toefl' => get_string('exam_lang_ielts_toefl', 'mod_cv'),
                    'lang_translator' => get_string('exam_lang_translator', 'mod_cv'),
                    'lang_cefr' => get_string('exam_lang_cefr', 'mod_cv'),
                    'lang_tefl' => get_string('exam_lang_tefl', 'mod_cv'),
                    'lang_custom' => get_string('exam_lang_custom', 'mod_cv'),
                ];

            case self::DOMAIN_BUSINESS:
                return [
                    'biz_cfa' => get_string('exam_biz_cfa', 'mod_cv'),
                    'biz_cpa' => get_string('exam_biz_cpa', 'mod_cv'),
                    'biz_shrm' => get_string('exam_biz_shrm', 'mod_cv'),
                    'biz_sixsigma' => get_string('exam_biz_sixsigma', 'mod_cv'),
                    'biz_cma' => get_string('exam_biz_cma', 'mod_cv'),
                    'biz_custom' => get_string('exam_biz_custom', 'mod_cv'),
                ];

            case self::DOMAIN_GENERAL:
            default:
                return [
                    'gen_cv' => get_string('exam_gen_cv', 'mod_cv'),
                    'gen_portfolio' => get_string('exam_gen_portfolio', 'mod_cv'),
                    'gen_custom' => get_string('exam_gen_custom', 'mod_cv'),
                ];
        }
    }

    /**
     * Get a flattened map of all certification keys to their localized titles.
     *
     * @return array
     */
    public static function get_all_certifications(): array {
        $all = [];
        $domains = array_keys(self::get_domains());
        foreach ($domains as $d) {
            $all = array_merge($all, self::get_certifications($d));
        }
        // Legacy fallback support for 'custom'.
        if (!isset($all['custom'])) {
            $all['custom'] = get_string('exam_custom', 'mod_cv');
        }
        return $all;
    }

    /**
     * Get the display title of a domain.
     *
     * @param string $domain
     * @return string
     */
    public static function get_domain_title(string $domain): string {
        $domains = self::get_domains();
        return $domains[$domain] ?? ucfirst($domain);
    }

    /**
     * Get the display title of a certification within a domain.
     *
     * @param string $domain The domain key.
     * @param string $cert The certification key.
     * @param string $customcert Optional custom certification title.
     * @return string
     */
    public static function get_cert_title(string $domain, string $cert, string $customcert = ''): string {
        if (!empty($customcert) && (strpos($cert, 'custom') !== false || $cert === 'custom')) {
            return $customcert;
        }

        $certs = self::get_certifications($domain);
        if (isset($certs[$cert])) {
            return $certs[$cert];
        }

        $all = self::get_all_certifications();
        return $all[$cert] ?? strtoupper($cert);
    }

    /**
     * Get default pre-made system AI prompt for a domain.
     *
     * @param string $domain Domain identifier.
     * @param string $cert Certification identifier.
     * @return string
     */
    public static function get_default_prompt(string $domain, string $cert = ''): string {
        switch ($domain) {
            case self::DOMAIN_MEDICAL:
                return 'You are an elite Clinical Healthcare Credentialing and Medical Application Specialist. ' .
                    'Your objective is to evaluate and refine clinical casework, hospital rotations, clinical trials, ' .
                    'and healthcare project experiences for formal medical board certification, hospital credentialing, ' .
                    'and clinical fellowship dossiers.' . "\n\n" .
                    'Key Instructions:' . "\n" .
                    '1. Structure each clinical experience with clear headings: Clinical Objective, Patient Scope & Setting, ' .
                    'Clinical Responsibilities, Key Interventions & Diagnostic Deliverables, Patient Safety & Protocol Management, ' .
                    'and Measurable Clinical Outcomes.' . "\n" .
                    '2. Emphasize evidence-based clinical guidelines, multidisciplinary collaboration, infection control, ' .
                    'and patient safety standards.' . "\n" .
                    '3. Return output as valid JSON containing an executive "summary" and an array of "projects" ' .
                    'with "title", "role", and "formatted_description".';

            case self::DOMAIN_COMPUTER:
                return 'You are a Principal Software Architect and Technical Recruiter specializing in IT certifications, ' .
                    'cloud architecture (AWS/Azure/GCP), DevOps, and enterprise software engineering dossiers.' . "\n\n" .
                    'Key Instructions:' . "\n" .
                    '1. Structure each technical project write-up with: Technical Problem & Objective, Architecture & Technology Stack, ' .
                    'Personal Engineering Responsibilities, System Deliverables, Technical Challenges & Mitigations, ' .
                    'and Measurable Engineering Impact (latency, scalability, uptime, automated test coverage, cost efficiency).' . "\n" .
                    '2. Highlight clean code, microservices/serverless design patterns, CI/CD pipelines, security best practices, ' .
                    'and modern agile delivery.' . "\n" .
                    '3. Return output as valid JSON containing an executive "summary" and an array of "projects" ' .
                    'with "title", "role", and "formatted_description".';

            case self::DOMAIN_LANGUAGES:
                return 'You are an expert Linguistic Assessor and Translation Accreditation Consultant. ' .
                    'Your goal is to articulate language teaching, translation, interpretation, and localization projects ' .
                    'into high-impact credentialing dossiers.' . "\n\n" .
                    'Key Instructions:' . "\n" .
                    '1. Structure write-ups highlighting: Linguistic Project Scope, Source/Target Language Registers, ' .
                    'Methodology & Quality Assurance (CAT tools, back-translation, CEFR benchmarks), Key Deliverables, ' .
                    'Linguistic & Cultural Challenges Overcome, and Measurable Outcomes.' . "\n" .
                    '2. Maintain high academic, professional, and cross-cultural standards.' . "\n" .
                    '3. Return output as valid JSON containing an executive "summary" and an array of "projects" ' .
                    'with "title", "role", and "formatted_description".';

            case self::DOMAIN_BUSINESS:
                return 'You are a Senior Management Consultant and Executive Financial Credentialing Specialist. ' .
                    'Your mission is to formulate business, corporate finance, accounting, HR, and operational excellence ' .
                    'projects into authoritative professional dossiers.' . "\n\n" .
                    'Key Instructions:' . "\n" .
                    '1. Structure each initiative focusing on: Business Objective & Problem Statement, Strategic Scope, ' .
                    'Leadership & Execution Responsibilities, Financial & Operational Deliverables, Risk & Governance Management, ' .
                    'and Quantifiable ROI / KPI Outcomes.' . "\n" .
                    '2. Apply recognized corporate finance and management principles (e.g. GAAP/IFRS, Lean Six Sigma DMAIC, SHRM).' . "\n" .
                    '3. Return output as valid JSON containing an executive "summary" and an array of "projects" ' .
                    'with "title", "role", and "formatted_description".';

            case self::DOMAIN_GENERAL:
                return 'You are a Professional Career Portfolio Strategist and Executive Resume Writer. ' .
                    'Your task is to transform career milestones, leadership initiatives, and project deliverables ' .
                    'into an exceptional professional experience dossier.' . "\n\n" .
                    'Key Instructions:' . "\n" .
                    '1. Format each project using the STAR (Situation, Task, Action, Result) methodology.' . "\n" .
                    '2. Ensure crisp, active-voice descriptions emphasizing ownership, innovation, leadership, and measurable achievements.' . "\n" .
                    '3. Return output as valid JSON containing an executive "summary" and an array of "projects" ' .
                    'with "title", "role", and "formatted_description".';

            case self::DOMAIN_PMI:
            default:
                return 'You are an expert PMI (Project Management Institute) Application Reviewer and PMP/CAPM/PMI-ACP Coach. ' .
                    'Your task is to take candidate project experience and format it into professional, audit-proof project descriptions ' .
                    'compliant with PMI standards and PMBOK guide terminology.' . "\n\n" .
                    'Format Requirements:' . "\n" .
                    '1. For each project, write a concise, formal description (200-500 words) using PMI terminology.' . "\n" .
                    '2. Structure each project description with clear sections: Project Objective, My Role & Responsibilities, ' .
                    'Key Deliverables, Challenges & Risk Management, and Project Outcomes.' . "\n" .
                    '3. Emphasize what the candidate individually led, directed, managed, and controlled.' . "\n" .
                    '4. Return output as valid JSON containing an executive "summary" and an array of "projects" ' .
                    'with "title", "role", and "formatted_description".';
        }
    }
}
