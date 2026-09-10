# Application & CV Builder (`mod_cv`)

A course-aware Moodle activity module designed to help students assemble, format, and generate official professional credentialing applications, portfolio write-ups, and CV dossiers across multiple disciplines (Project Management / PMI, Healthcare & Medical, Computer & IT, Languages & Translation, Business & Finance, and General).

---

## Key Features

1. **Multi-Domain Track & Certification Aware**:
   - Configured directly inside the Moodle course.
   - Choose from 6 major professional disciplines:
     - **Project Management (PMI®)**: PMP®, CAPM®, PMI-ACP®, PMI-RMP®, PMI-PBA®, PgMP®.
     - **Healthcare & Medical**: Medical Board Licensing, Clinical Nursing (RN), BLS/ACLS, Clinical Fellowship, Pharmacy Specialties (BPS), Healthcare Admin (CPHQ).
     - **Information Technology & Computing**: Cloud Architecture (AWS/Azure/GCP), Cisco Networking (CCNA/CCNP), Cybersecurity (Security+/CEH), Kubernetes (CKA), Data Science & AI, Full-Stack Software Engineering.
     - **Languages & Translation**: IELTS/TOEFL, Certified Translator, CEFR C1/C2, TEFL/TESOL.
     - **Business & Finance**: CFA®, CPA, SHRM HR, Lean Six Sigma, CMA.
     - **General Professional**: Comprehensive CV, Portfolio & Custom Track.
   - Automatically injects course metadata (Course name, target track, qualifying contact hours, training provider name).

2. **Built-in Domain AI Prompts with Custom Override**:
   - High-performance, domain-specific AI system prompts pre-configured for each track.
   - Teachers can optionally override or customize prompts per course activity.

3. **AI-Powered via n8n Webhook**:
   - Collects candidate profile, education dates, and full 20 structured experience fields.
   - Sends structured JSON to your n8n workflow.
   - n8n handles AI processing (prompting LLMs like Claude, OpenAI, or Gemini) to format descriptions into credential-compliant structures.

4. **Convenient Copy & PDF Dossier**:
   - One-click "Copy" buttons next to each formatted project description.
   - Instant download of a clean, structured PDF dossier using Moodle's built-in PDF generator (`export.php`).

---

## Installation & Setup

1. Copy or clone the `cv` folder into your Moodle installation under `mod/cv`:
   ```bash
   # In your Moodle root
   git clone <repo> mod/cv
   ```
2. Visit **Site Administration > Notifications** to complete the database installation.
3. Configure the default webhook at:
   **Site Administration > Plugins > Activity modules > Application & CV Builder**
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
