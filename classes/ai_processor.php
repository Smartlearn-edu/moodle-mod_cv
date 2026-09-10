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

use context;
use moodle_exception;

/**
 * Multi-backend AI processor for mod_cv.
 *
 * Coordinates execution across local_aihub (BYOK), Moodle core_ai,
 * and external webhooks (n8n, Make, custom API) with auto-detection and fallback.
 *
 * @package    mod_cv
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ai_processor {
    /** @var string Use site default AI engine. */
    public const PROVIDER_DEFAULT = 'default';

    /** @var string Auto-detect best available provider. */
    public const PROVIDER_AUTO = 'auto';

    /** @var string AI Hub plugin (local_aihub - BYOK). */
    public const PROVIDER_AIHUB = 'aihub';

    /** @var string Moodle Core AI Subsystem (core_ai). */
    public const PROVIDER_CORE_AI = 'core_ai';

    /** @var string External Webhook (n8n, Make, custom API). */
    public const PROVIDER_WEBHOOK = 'webhook';

    /**
     * Get available provider options for form dropdowns.
     *
     * @param bool $includedefault Whether to include 'Site Default' in the list.
     * @return array Associative array of provider key => label.
     */
    public static function get_provider_options(bool $includedefault = false): array {
        $options = [];

        if ($includedefault) {
            $sitedefault = get_config('mod_cv', 'default_aiprovider') ?: self::PROVIDER_AUTO;
            $defaultlabel = self::get_provider_label($sitedefault);
            $options[self::PROVIDER_DEFAULT] = get_string('aiprovider_default', 'mod_cv', $defaultlabel);
        }

        $options[self::PROVIDER_AUTO] = get_string('aiprovider_auto', 'mod_cv');
        $options[self::PROVIDER_AIHUB] = get_string('aiprovider_aihub', 'mod_cv');
        $options[self::PROVIDER_CORE_AI] = get_string('aiprovider_core_ai', 'mod_cv');
        $options[self::PROVIDER_WEBHOOK] = get_string('aiprovider_webhook', 'mod_cv');

        return $options;
    }

    /**
     * Get human-readable label for a provider.
     *
     * @param string $provider
     * @return string
     */
    public static function get_provider_label(string $provider): string {
        switch ($provider) {
            case self::PROVIDER_AIHUB:
                return get_string('aiprovider_aihub', 'mod_cv');
            case self::PROVIDER_CORE_AI:
                return get_string('aiprovider_core_ai', 'mod_cv');
            case self::PROVIDER_WEBHOOK:
                return get_string('aiprovider_webhook', 'mod_cv');
            case self::PROVIDER_AUTO:
            default:
                return get_string('aiprovider_auto', 'mod_cv');
        }
    }

    /**
     * Check whether local_aihub plugin is installed and ready.
     *
     * @return bool
     */
    public static function is_aihub_available(): bool {
        return class_exists('\local_aihub\ai');
    }

    /**
     * Check whether Moodle core_ai subsystem is available and configured.
     *
     * @return bool
     */
    public static function is_core_ai_available(): bool {
        return class_exists('\core_ai\manager');
    }

    /**
     * Resolve the effective provider to execute.
     *
     * @param string $configuredprovider Provider configured on the activity instance.
     * @return string One of PROVIDER_AUTO, PROVIDER_AIHUB, PROVIDER_CORE_AI, PROVIDER_WEBHOOK.
     */
    public static function resolve_provider(string $configuredprovider = self::PROVIDER_DEFAULT): string {
        if (empty($configuredprovider) || $configuredprovider === self::PROVIDER_DEFAULT) {
            $sitedefault = get_config('mod_cv', 'default_aiprovider');
            return !empty($sitedefault) ? $sitedefault : self::PROVIDER_AUTO;
        }
        return $configuredprovider;
    }

    /**
     * Build system and user prompt strings for direct LLM generation.
     *
     * @param object $cv Activity DB record.
     * @param array $profile Candidate profile array.
     * @param array $projects Candidate projects array.
     * @param array $coursedata Course details array.
     * @param string $baseprompt Base domain or custom prompt instructions.
     * @return array Keyed array with 'system' and 'user' prompt texts.
     */
    public static function build_prompts(
        object $cv,
        array $profile,
        array $projects,
        array $coursedata,
        string $baseprompt
    ): array {
        $fieldtype = !empty($cv->fieldtype) ? $cv->fieldtype : \mod_cv\domains::DOMAIN_PMI;
        $customcert = !empty($cv->customcert) ? $cv->customcert : '';
        $domaintitle = \mod_cv\domains::get_domain_title($fieldtype);
        $certtitle = \mod_cv\domains::get_cert_title($fieldtype, $cv->examtype, $customcert);
        $providername = $cv->providername ?? 'SmartLearn Education';
        $contacthours = (int) ($cv->contacthours ?? 35);

        // Construct system prompt with strict JSON output schema.
        $system = $baseprompt . "\n\n";
        $system .= "### OUTPUT REQUIREMENTS (STRICT JSON ONLY):\n";
        $system .= "You must output ONLY a valid, parseable JSON object matching this structure.\n";
        $system .= "Do NOT include any markdown code blocks (```), backticks, or explanatory text before or after the JSON.\n";
        $system .= "JSON structure:\n";
        $system .= "{\n";
        $system .= "  \"summary\": \"Executive summary of the candidate's qualifications and readiness for the credential.\",\n";
        $system .= "  \"projects\": [\n";
        $system .= "    {\n";
        $system .= "      \"title\": \"Project title matching candidate entry\",\n";
        $system .= "      \"role\": \"Role on project\",\n";
        $system .= "      \"methodology\": \"Predictive / Agile / Hybrid\",\n";
        $system .= "      \"formatted_description\": \"Detailed, high-impact description covering Objective, Scope, ";
        $system .= "Responsibilities, Deliverables, Challenges, and Measurable Outcomes.\"\n";
        $system .= "    }\n";
        $system .= "  ]\n";
        $system .= "}\n";

        // Construct structured user prompt.
        $user = "Target Track & Certification:\n";
        $user .= "- Domain Track: {$domaintitle}\n";
        $user .= "- Target Certification / Credential: {$certtitle}\n";
        $user .= "- Qualifying Contact Hours: {$contacthours}\n";
        $user .= "- Training Provider: {$providername}\n\n";

        $user .= "Qualifying Course Information:\n";
        $user .= "- Course Name: " . ($coursedata['name'] ?? '') . "\n";
        $user .= "- Course Start Date: " . ($coursedata['startdate'] ?? 'N/A') . "\n";
        $user .= "- Course Completion Date: " . ($coursedata['enddate'] ?? 'N/A') . "\n\n";

        $user .= "Candidate Profile:\n";
        $user .= "- Name: " . ($profile['name'] ?? '') . "\n";
        $user .= "- Email: " . ($profile['email'] ?? '') . "\n";
        $user .= "- Phone: " . ($profile['phone'] ?? 'N/A') . "\n";
        $user .= "- Country: " . ($profile['country'] ?? 'N/A') . "\n";
        $user .= "- Highest Academic Degree: " . ($profile['degree'] ?? '') . "\n";
        $user .= "- Institution: " . ($profile['institution'] ?? '') . "\n";
        $user .= "- Degree Dates: " . ($profile['degree_startdate'] ?? '') . " to " . ($profile['degree_enddate'] ?? '') . "\n\n";

        $user .= "Candidate Project Experiences (" . count($projects) . " projects):\n";
        foreach ($projects as $index => $proj) {
            $num = $index + 1;
            $user .= "--- Project #{$num} ---\n";
            $user .= "Title: " . ($proj['title'] ?? '') . "\n";
            $user .= "Industry: " . ($proj['industry'] ?? 'N/A') . "\n";
            $user .= "Organization: " . ($proj['organization'] ?? 'N/A') . "\n";
            $user .= "Job Title: " . ($proj['jobtitle'] ?? '') . "\n";
            $user .= "Role: " . ($proj['role'] ?? '') . "\n";
            $user .= "Approach/Methodology: " . ($proj['methodology'] ?? 'predictive') . "\n";
            $user .= "Timeline: " . ($proj['startdate'] ?? '') . " to ";
            $user .= (!empty($proj['iscurrent']) ? "Present (Ongoing)" : ($proj['enddate'] ?? '')) . "\n";
            $user .= "Objective: " . ($proj['objective'] ?? '') . "\n";
            $user .= "Scope: " . ($proj['scope'] ?? '') . "\n";
            $user .= "Responsibilities: " . ($proj['responsibilities'] ?? '') . "\n";
            $user .= "Deliverables: " . ($proj['deliverables'] ?? '') . "\n";
            $user .= "Stakeholders Managed: " . ($proj['stakeholders'] ?? 'N/A') . "\n";
            $user .= "Team & Resources: " . ($proj['teamresources'] ?? 'N/A') . "\n";
            $user .= "Challenges / Risks / Issues: " . ($proj['challenges'] ?? '') . "\n";
            $user .= "Changes Managed: " . ($proj['changes'] ?? 'N/A') . "\n";
            $user .= "Outcomes: " . ($proj['outcomes'] ?? '') . "\n";
            $user .= "Measurable Results: " . ($proj['measurableresults'] ?? 'N/A') . "\n";
            $user .= "Closure / Handover: " . ($proj['closure'] ?? 'N/A') . "\n";
            if (!empty($proj['additionalinfo'])) {
                $user .= "Additional Info: " . $proj['additionalinfo'] . "\n";
            }
            $user .= "\n";
        }

        $user .= "Please generate the professional application dossier matching the strict JSON format specified.";

        return [
            'system' => $system,
            'user' => $user,
        ];
    }

    /**
     * Process candidate submission through the designated AI provider.
     *
     * @param object $cv Activity DB record.
     * @param context $context Module context.
     * @param array $params Cleaned parameters from external request.
     * @param array $coursedata Course information array.
     * @param string $prompt Effective prompt instructions.
     * @param string|null $webhookurl Webhook URL (if applicable).
     * @param string|null $authtoken Auth token (if applicable).
     * @param array $webhookpayload Complete payload for webhook.
     * @return array Associative array with 'completed' (bool), 'outputjson' (string), 'provider' (string).
     * @throws moodle_exception
     */
    public static function process(
        object $cv,
        context $context,
        array $params,
        array $coursedata,
        string $prompt,
        ?string $webhookurl,
        ?string $authtoken,
        array $webhookpayload
    ): array {
        global $USER;

        $targetprovider = self::resolve_provider($cv->aiprovider ?? self::PROVIDER_DEFAULT);
        $prompts = self::build_prompts($cv, $params['profile'], $params['projects'], $coursedata, $prompt);

        if ($targetprovider === self::PROVIDER_AIHUB) {
            return self::execute_aihub($prompts['system'], $prompts['user'], (int) ($USER->id ?? 0));
        }

        if ($targetprovider === self::PROVIDER_CORE_AI) {
            return self::execute_core_ai($prompts['system'], $prompts['user'], $context, (int) ($USER->id ?? 0));
        }

        if ($targetprovider === self::PROVIDER_WEBHOOK) {
            return self::execute_webhook($webhookurl, $webhookpayload, $authtoken);
        }

        // PROVIDER_AUTO: Fallback chain (AI Hub -> Core AI -> Webhook).
        // 1. Try AI Hub if available.
        if (self::is_aihub_available()) {
            try {
                return self::execute_aihub($prompts['system'], $prompts['user'], (int) ($USER->id ?? 0));
            } catch (\Throwable $e) {
                // Fall through to next provider.
            }
        }

        // 2. Try Core AI if available.
        if (self::is_core_ai_available()) {
            try {
                return self::execute_core_ai($prompts['system'], $prompts['user'], $context, (int) ($USER->id ?? 0));
            } catch (\Throwable $e) {
                // Fall through to next provider.
            }
        }

        // 3. Try Webhook if URL is configured.
        if (!empty($webhookurl)) {
            return self::execute_webhook($webhookurl, $webhookpayload, $authtoken);
        }

        // No working AI provider could be resolved.
        throw new moodle_exception(
            'error_no_aiprovider',
            'mod_cv',
            '',
            null,
            'No functional AI provider is available (AI Hub, Moodle Core AI, or Webhook).'
        );
    }

    /**
     * Execute generation via AI Hub plugin (local_aihub).
     *
     * @param string $system System prompt.
     * @param string $user User prompt.
     * @param int $userid User ID.
     * @return array
     * @throws moodle_exception
     */
    protected static function execute_aihub(string $system, string $user, int $userid): array {
        if (!self::is_aihub_available()) {
            throw new moodle_exception(
                'error_aihub_failed',
                'mod_cv',
                '',
                null,
                'AI Hub plugin (local_aihub) is not installed or enabled.'
            );
        }

        $result = \local_aihub\ai::generate_text(
            $system,
            $user,
            true,
            'mod_cv',
            'Candidate Application Dossier Generation',
            $userid
        );

        if (empty($result['success'])) {
            $errormsg = $result['message'] ?? 'AI Hub returned an unsuccessful response.';
            throw new moodle_exception('error_aihub_failed', 'mod_cv', '', null, $errormsg);
        }

        $rawoutput = (string) ($result['data'] ?? '');
        $parsed = self::parse_ai_output($rawoutput);
        $outputjson = json_encode($parsed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return [
            'completed' => true,
            'outputjson' => $outputjson,
            'provider' => self::PROVIDER_AIHUB,
        ];
    }

    /**
     * Execute generation via Moodle Core AI subsystem.
     *
     * @param string $system System prompt.
     * @param string $user User prompt.
     * @param context $context Module context.
     * @param int $userid User ID.
     * @return array
     * @throws moodle_exception
     */
    protected static function execute_core_ai(string $system, string $user, context $context, int $userid): array {
        if (!self::is_core_ai_available()) {
            throw new moodle_exception(
                'error_core_ai_failed',
                'mod_cv',
                '',
                null,
                'Moodle Core AI subsystem is not available on this site.'
            );
        }

        global $DB;
        $combinedprompt = $system . "\n\n" . $user;

        try {
            $action = new \core_ai\aiactions\generate_text(
                contextid: $context->id,
                userid: $userid,
                prompttext: $combinedprompt
            );

            $manager = new \core_ai\manager($DB);
            $response = $manager->process_action($action);

            $status = method_exists($response, 'get_status') ? $response->get_status() : false;
            $responsedata = method_exists($response, 'get_response_data') ? $response->get_response_data() : [];

            if (!$status || empty($responsedata['generatedcontent'])) {
                $err = method_exists($response, 'get_error_message') ? $response->get_error_message() : 'Generation failed.';
                throw new moodle_exception('error_core_ai_failed', 'mod_cv', '', null, $err);
            }

            $rawoutput = (string) $responsedata['generatedcontent'];
            $parsed = self::parse_ai_output($rawoutput);
            $outputjson = json_encode($parsed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return [
                'completed' => true,
                'outputjson' => $outputjson,
                'provider' => self::PROVIDER_CORE_AI,
            ];
        } catch (\Throwable $e) {
            throw new moodle_exception('error_core_ai_failed', 'mod_cv', '', null, $e->getMessage());
        }
    }

    /**
     * Execute generation via external webhook (n8n, Make, custom API).
     *
     * @param string|null $webhookurl
     * @param array $payload
     * @param string|null $authtoken
     * @return array
     * @throws moodle_exception
     */
    protected static function execute_webhook(?string $webhookurl, array $payload, ?string $authtoken): array {
        if (empty($webhookurl)) {
            throw new moodle_exception('error_no_webhook', 'mod_cv', '', null, 'No webhook URL configured.');
        }

        $airesponse = \mod_cv\n8n_client::send($webhookurl, $payload, $authtoken);

        $iscompleted = false;
        $outputjson = '';

        if (!empty($airesponse['summary']) || !empty($airesponse['projects'])) {
            $iscompleted = true;
            $outputjson = json_encode($airesponse, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return [
            'completed' => $iscompleted,
            'outputjson' => $outputjson,
            'provider' => self::PROVIDER_WEBHOOK,
        ];
    }

    /**
     * Parse raw AI string into clean structured array.
     *
     * Handles markdown code blocks (```json ... ```) and loose formatting.
     *
     * @param string $rawoutput
     * @return array Keyed array containing 'summary' and 'projects'.
     */
    public static function parse_ai_output(string $rawoutput): array {
        $clean = trim($rawoutput);

        // Unwrap markdown code fences if wrapped in ```json ... ``` or ``` ... ```.
        if (preg_match('/^```(?:json)?\s*([\s\S]*?)\s*```$/i', $clean, $matches)) {
            $clean = trim($matches[1]);
        } else if (preg_match('/\{[\s\S]*\}/', $clean, $matches)) {
            // Find outermost JSON object.
            $clean = trim($matches[0]);
        }

        $decoded = json_decode($clean, true);
        if (is_array($decoded) && (!empty($decoded['summary']) || !empty($decoded['projects']))) {
            return [
                'summary' => (string) ($decoded['summary'] ?? ''),
                'projects' => is_array($decoded['projects'] ?? null) ? $decoded['projects'] : [],
            ];
        }

        // Fallback for unstructured text response.
        return [
            'summary' => $rawoutput,
            'projects' => [],
        ];
    }
}
