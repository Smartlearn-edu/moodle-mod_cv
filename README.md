# PMI Application & CV Builder (`mod_cv`)

A course-aware Moodle activity module designed to help students assemble, format, and generate their official **PMI (Project Management Institute)** certification applications (PMP®, PMI-ACP®, CAPM®, PMI-RMP®, etc.) and CVs.

---

## Key Features

1. **Course & Certification Aware**:
   - Configured directly inside the Moodle course.
   - Automatically injects course metadata (Course name, target PMI exam, qualifying contact hours, training provider name).
2. **AI-Powered via n8n Webhook**:
   - Collects candidate profile and project experience (Title, Role, Methodology, Dates, Raw notes).
   - Sends structured JSON to your n8n workflow.
   - n8n handles the AI processing (prompting LLMs like Claude, OpenAI, or Gemini) to format descriptions into PMBOK-compliant structure (Objective, Outcome, Role, Responsibilities by process groups or agile domains).
3. **Copy-to-PMI.org Convenience**:
   - One-click "Copy" buttons next to each formatted project description for seamless pasting into the official PMI application portal.
4. **Audit-Ready PDF Dossier**:
   - Instant download of a clean, structured PDF application dossier using Moodle's built-in PDF generator (`export.php`).

---

## Installation & Setup

1. Copy or clone the `cv` folder into your Moodle installation under `mod/cv`:
   ```bash
   # In your Moodle root
   git clone <repo> mod/cv
   ```
2. Visit **Site Administration > Notifications** to complete the database installation.
3. Configure the default webhook at:
   **Site Administration > Plugins > Activity modules > PMI Application & CV Builder**
   - **Default n8n Webhook URL**: `https://your-n8n-instance.com/webhook/mod-cv-process`
   - **Bearer Auth Token**: Optional authentication token.

---

## n8n Workflow Setup

A ready-to-use sample workflow is included in:
`resources/n8n_cv_builder_workflow.json`

To use it:
1. Open your n8n dashboard.
2. Click **Add workflow** > **Import from File**.
3. Select `resources/n8n_cv_builder_workflow.json`.
4. Connect your preferred LLM node (OpenAI, Anthropic Claude, Google Gemini, or Ollama) to generate the responses.
5. Activate the workflow and copy the Production Webhook URL into Moodle.

---

## License

GNU GPL v3 or later.
© 2025 Mohammad Nabil <mohammad@smartlearn.education>
