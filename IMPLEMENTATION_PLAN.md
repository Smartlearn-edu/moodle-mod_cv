# Implementation Plan - Generalization & Multi-Domain Tracks (Application & CV Builder)

Transform the plugin from a PMI-specific tool to a general **Application & CV Builder (`mod_cv`)** that supports multiple professional fields (PMI, Medical/Healthcare, Computer/IT, Languages, Business, and General) with dynamic related certification lists and pre-made domain-specific AI prompts.

---

## User Review Required

> [!IMPORTANT]
> - **Plugin Rebranding**: The plugin name changes from `PMI Application & CV Builder` to **`Application & CV Builder`** (in English, Arabic, code identifiers, UI badges, and PDF exports).
> - **New Field & Certifications Selector**: Instructors will be able to select the **Professional Domain / Field** (PMI, Medical, Computer/IT, Languages, Business, General). Selecting a field will dynamically show its related certifications list in `mod_form.php` via Moodle's native form rules.
> - **Pre-Made Domain AI Prompts**: Pre-made AI system prompts will be built in for each domain (Medical clinical practice, IT system architecture, PMI PMBOK, Language proficiency & translation, Business finance, and General CV). If the teacher leaves the custom prompt empty, the plugin automatically applies the pre-made prompt for the selected domain.
> - **Database Schema**: Column `fieldtype` (CHAR 50) and `customcert` (CHAR 255) will be added to `{cv}` with an upgrade step in `db/upgrade.php` and version bumped to `2026090901`.

---

## Proposed Changes

### 1. Architecture & Domain Definitions

#### [NEW] [classes/domains.php](file:///home/mohammad/Dev/plugins/mod/cv/classes/domains.php)
- Centralized registry class `mod_cv\domains` defining:
  - **Domains**:
    - `pmi`: Project Management (PMI®)
    - `medical`: Healthcare & Medical
    - `computer`: Information Technology & Computer Science
    - `languages`: Languages & Translation
    - `business`: Business & Finance
    - `general`: General Professional / Other
  - **Certifications per domain**:
    - `pmi`: PMP, CAPM, PMI-ACP, PMI-RMP, PMI-PBA, PgMP
    - `medical`: Medical Board License, Nursing Practice (RN), BLS/ACLS, Clinical Fellowship, Pharmacy Specialties (BPS), Healthcare Admin
    - `computer`: AWS Cloud Architect, Cisco Certified (CCNA/CCNP), CompTIA Security+, Kubernetes & DevOps (CKA), Data Science & AI, Full-Stack Software Engineering
    - `languages`: IELTS/TOEFL, Certified Translator, CEFR C1/C2, TEFL/TESOL
    - `business`: CFA, CPA, SHRM HR, Lean Six Sigma, CMA
    - `general`: Custom / General CV
  - **Pre-made AI Prompts**:
    - High-performance domain prompts tailored to each track's specific terminology, evaluation standards, and formatting requirements.

---

### 2. Database & Versioning

#### [MODIFY] [db/install.xml](file:///home/mohammad/Dev/plugins/mod/cv/db/install.xml)
- Add `fieldtype` (CHAR 50, NOT NULL, DEFAULT 'pmi') and `customcert` (CHAR 255, NULL) to table `cv`.

#### [MODIFY] [db/upgrade.php](file:///home/mohammad/Dev/plugins/mod/cv/db/upgrade.php)
- Add upgrade step for version `2026090901` to add `fieldtype` and `customcert` columns if not present.

#### [MODIFY] [version.php](file:///home/mohammad/Dev/plugins/mod/cv/version.php)
- Bump version to `2026090901` (`v0.3.0`).

---

### 3. Activity Settings Form (`mod_form.php`) & Core Lib

#### [MODIFY] [mod_form.php](file:///home/mohammad/Dev/plugins/mod/cv/mod_form.php)
- Rename header from "Exam & Certification Configuration" to "Certification & Professional Track Configuration".
- Add **Professional Domain / Field** (`fieldtype`) dropdown before the certification list.
- Add domain-specific certification dropdowns with `$mform->hideIf()` so selecting a field immediately reveals the related certification list.
- Add `customcert` field (revealed when custom certification is chosen).
- Add AI Prompt options: allow selecting "Use Pre-made Prompt for Selected Domain" or entering a custom prompt.
- Handle preprocessing and form data saving in `cv_add_instance` / `cv_update_instance` in `lib.php`.

#### [MODIFY] [lib.php](file:///home/mohammad/Dev/plugins/mod/cv/lib.php)
- Handle extracting the correct `examtype` based on the chosen `fieldtype` in `cv_add_instance` and `cv_update_instance`.

---

### 4. Language Strings (English & Arabic)

#### [MODIFY] [lang/en/cv.php](file:///home/mohammad/Dev/plugins/mod/cv/lang/en/cv.php)
- Change plugin name to `Application & CV Builder`.
- Generalize all descriptions, headers, and UI strings.
- Add strings for all domains (`field_pmi`, `field_medical`, `field_computer`, `field_languages`, `field_business`, `field_general`).
- Add strings for all certifications across the 6 domains.
- Add strings for pre-made prompt options.

#### [MODIFY] [lang/ar/cv.php](file:///home/mohammad/Dev/plugins/mod/cv/lang/ar/cv.php)
- Change Arabic plugin name to `مساعد طلبات الاعتماد وبناء السيرة الذاتية`.
- Translate all new domains, certifications, and options into Arabic.

---

### 5. UI, Frontend Controller & PDF Export

#### [MODIFY] [templates/view.mustache](file:///home/mohammad/Dev/plugins/mod/cv/templates/view.mustache)
- Display both Domain and Certification badges in the header.
- Generalize labels from "Target PMI Certification" to "Target Certification".

#### [MODIFY] [amd/src/main.js](file:///home/mohammad/Dev/plugins/mod/cv/amd/src/main.js)
- Change button text from "Copy for PMI.org" to "Copy Write-Up".
- Generalize helper descriptions and re-compile to `amd/build/main.min.js`.

#### [MODIFY] [export.php](file:///home/mohammad/Dev/plugins/mod/cv/export.php)
- PDF Header: "Professional Application & Experience Dossier".
- Dynamic subtitle referencing the specific Certification & Domain.

#### [MODIFY] [classes/external/submit.php](file:///home/mohammad/Dev/plugins/mod/cv/classes/external/submit.php)
- Resolve prompt: if custom prompt is provided, use it; otherwise automatically load the pre-made prompt for the activity's selected domain.
- Send domain and certification details in payload to n8n.

#### [MODIFY] [README.md](file:///home/mohammad/Dev/plugins/mod/cv/README.md)
- Update documentation to reflect multi-domain support and the new name.

---

## Verification Plan

### Automated Checks
1. Run Moodle Plugin Validator:
   ```bash
   python3 /home/mohammad/Dev/validator/validator.py /home/mohammad/Dev/plugins/mod/cv
   ```
   Must PASS all checks:
   - `phplint`
   - `phpcs`
   - `mustache`
   - `validate`
2. Minify and verify JavaScript build (`amd/build/main.min.js`).

### Manual Verification
1. Inspect `mod_form.php` form definition:
   - Verify `fieldtype` select appears first.
   - Verify selecting different fields displays the corresponding certification list.
2. Verify resolved pre-made prompt logic for Medical, Computer, PMI, etc.
