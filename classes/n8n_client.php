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

use moodle_exception;

/**
 * Client for communicating with the n8n webhook.
 *
 * @package    mod_cv
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class n8n_client {
    /**
     * Send payload to n8n webhook and return decoded JSON response.
     *
     * @param string $webhookurl Target webhook URL
     * @param array $payload Structured data to send
     * @param string|null $authtoken Optional bearer token
     * @return array Decoded response from n8n
     * @throws moodle_exception If the request fails or cannot be parsed
     */
    public static function send(string $webhookurl, array $payload, ?string $authtoken = null): array {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        if (empty($webhookurl)) {
            throw new moodle_exception('error_no_webhook', 'mod_cv', '', null, 'No webhook URL configured.');
        }

        $curl = new \curl();
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        if (!empty($authtoken)) {
            $headers[] = 'Authorization: Bearer ' . trim($authtoken);
        }

        $curl->setHeader($headers);
        $options = [
            'CURLOPT_TIMEOUT' => 90,
            'CURLOPT_CONNECTTIMEOUT' => 15,
        ];
        $curl->setopt($options);

        $jsonpayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $rawresponse = $curl->post($webhookurl, $jsonpayload);

        $info = $curl->get_info();
        $httpstatus = (int) ($info['http_code'] ?? 0);
        if ($httpstatus < 200 || $httpstatus >= 300) {
            $errormsg = 'n8n HTTP Error ' . $httpstatus . ': ' . ($curl->error ?? $rawresponse);
            throw new moodle_exception('error_n8n_request', 'mod_cv', '', null, $errormsg);
        }

        $clean = trim($rawresponse ?? '');
        // Unwrap markdown code blocks if the LLM wrapped the JSON in ```json ... ```.
        if (preg_match('/^```(?:json)?\s*([\s\S]*?)\s*```$/i', $clean, $matches)) {
            $clean = trim($matches[1]);
        }

        $decoded = json_decode($clean, true);
        if ($decoded === null && !empty($clean)) {
            return [
                'summary' => $clean,
                'projects' => [],
            ];
        }

        return is_array($decoded) ? $decoded : ['result' => $decoded];
    }
}
