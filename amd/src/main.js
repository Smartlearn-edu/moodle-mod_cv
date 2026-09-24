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
 * Frontend controller for mod_cv.
 *
 * @module     mod_cv/main
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/ajax', 'core/notification'], function(ajax, notification) {
    'use strict';

    var projectCounter = 0;
    var initialConfig = {};
    var lastAiOutputData = null;

    /**
     * Escape HTML helper.
     *
     * @param {string} str
     * @return {string}
     */
    function escapeHtml(str) {
        if (!str) {
            return '';
        }
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * Normalize date string to YYYY-MM-DD for HTML5 date inputs.
     *
     * @param {string} val
     * @return {string}
     */
    function normalizeDateForInput(val) {
        if (!val) {
            return '';
        }
        val = String(val).trim();
        if (/^\d{4}-\d{2}-\d{2}$/.test(val)) {
            return val;
        }
        var m1 = val.match(/^(\d{1,2})\/(\d{4})$/);
        if (m1) {
            var mm = m1[1].length === 1 ? '0' + m1[1] : m1[1];
            return m1[2] + '-' + mm + '-01';
        }
        var m2 = val.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
        if (m2) {
            var dd = m2[1].length === 1 ? '0' + m2[1] : m2[1];
            var mm2 = m2[2].length === 1 ? '0' + m2[2] : m2[2];
            return m2[3] + '-' + mm2 + '-' + dd;
        }
        return val;
    }

    /**
     * Get active configured fields for project entries.
     *
     * @return {Array}
     */
    function getActiveProjectFields() {
        if (initialConfig && initialConfig.project_fields && Array.isArray(initialConfig.project_fields)) {
            return initialConfig.project_fields.filter(function(f) {
                return f.enabled !== false;
            });
        }
        return [];
    }

    /**
     * Add a project card to the container dynamically based on configured fields.
     *
     * @param {Object} data Pre-saved project data if available.
     */
    function addProjectCard(data) {
        projectCounter++;
        var p = data || {};
        var idx = projectCounter;
        var activeFields = getActiveProjectFields();

        var html = '<div class="cv-project-card" id="project_card_' + idx + '">' +
            '<div class="cv-project-header">' +
            '   <h5 class="mb-0 cv-project-title font-weight-bold"><i class="fa fa-folder-open text-primary"></i> Project #' + idx + '</h5>' +
            '   <button type="button" class="btn btn-outline-danger btn-sm btn-remove-project" data-target="#project_card_' + idx + '">' +
            '       <i class="fa fa-trash"></i> Remove' +
            '   </button>' +
            '</div>';

        // Section metadata definitions.
        var sectionsMeta = {
            'basic': { title: 'Basic Project Information', icon: 'fa fa-id-card' },
            'timeline': { title: 'Project Timeline', icon: 'fa fa-calendar' },
            'deliverables': { title: 'Core Project Experience & Deliverables', icon: 'fa fa-tasks' },
            'governance': { title: 'Stakeholders, Resources & Governance', icon: 'fa fa-users' },
            'custom': { title: 'Additional Custom Details', icon: 'fa fa-list-alt' }
        };

        if (initialConfig.sections && typeof initialConfig.sections === 'object') {
            Object.keys(initialConfig.sections).forEach(function(k) {
                var s = initialConfig.sections[k];
                if (s && s.title) {
                    sectionsMeta[k] = {
                        title: s.title,
                        icon: s.icon || 'fa fa-folder'
                    };
                }
            });
        }

        // Group active fields by section preserving sort order.
        var grouped = {};
        var sectionOrder = ['basic', 'timeline', 'deliverables', 'governance', 'custom'];

        activeFields.forEach(function(f) {
            var sec = f.section || 'custom';
            if (!grouped[sec]) {
                grouped[sec] = [];
            }
            grouped[sec].push(f);
        });

        // Ensure any extra custom sections not in standard list are rendered.
        Object.keys(grouped).forEach(function(s) {
            if (sectionOrder.indexOf(s) === -1) {
                sectionOrder.push(s);
            }
        });

        sectionOrder.forEach(function(secKey) {
            var fields = grouped[secKey];
            if (!fields || fields.length === 0) {
                return;
            }

            var secInfo = sectionsMeta[secKey] || { title: secKey.charAt(0).toUpperCase() + secKey.slice(1), icon: 'fa fa-folder' };
            html += '<div class="cv-section-title"><i class="' + secInfo.icon + '"></i> ' + escapeHtml(secInfo.title) + '</div>';

            // Special layout for Timeline section if it has startdate and enddate.
            if (secKey === 'timeline') {
                html += '<div class="row">';
                fields.forEach(function(f) {
                    if (f.key === 'iscurrent') {
                        // Handled alongside enddate.
                        return;
                    }

                    var val = (p[f.key] !== undefined) ? p[f.key] : '';
                    if (f.type === 'date') {
                        val = normalizeDateForInput(val);
                    }

                    html += '<div class="col-md-6 mb-3">';
                    html += '   <label class="form-label font-weight-bold">' + escapeHtml(f.label) + (f.required ? ' <span class="text-danger">*</span>' : '') + '</label>';

                    if (f.key === 'enddate') {
                        html += '   <input type="date" class="form-control cv-project-input project-enddate project-' + f.key + '" data-field-key="' + f.key + '"' +
                            (p.iscurrent ? ' disabled' : (f.required ? ' required' : '')) + ' value="' + escapeHtml(val) + '" placeholder="' + escapeHtml(f.placeholder) + '">';
                        html += '   <div class="form-check mt-1">';
                        html += '       <input class="form-check-input project-iscurrent" type="checkbox" id="iscurrent_' + idx + '"' + (p.iscurrent ? ' checked' : '') + '>';
                        html += '       <label class="form-check-label small text-muted" for="iscurrent_' + idx + '">Project is ongoing</label>';
                        html += '   </div>';
                    } else {
                        html += '   <input type="' + (f.type === 'date' ? 'date' : 'text') + '" class="form-control cv-project-input project-' + f.key + '" data-field-key="' + f.key + '"' +
                            (f.required ? ' required' : '') + ' value="' + escapeHtml(val) + '" placeholder="' + escapeHtml(f.placeholder) + '">';
                        if (f.helptext) {
                            html += '   <small class="form-text text-muted">' + escapeHtml(f.helptext) + '</small>';
                        }
                    }
                    html += '</div>';
                });
                html += '</div>';
                return;
            }

            // Standard layout for other sections:
            // Single-line (text, select, date, number) in 2-column grid rows; textareas in full width.
            var rowBuffer = [];

            function flushRowBuffer() {
                if (rowBuffer.length === 0) {
                    return;
                }
                html += '<div class="row">';
                rowBuffer.forEach(function(itemHtml) {
                    html += itemHtml;
                });
                html += '</div>';
                rowBuffer = [];
            }

            fields.forEach(function(f) {
                var val = (p[f.key] !== undefined) ? p[f.key] : '';
                if (val === '' && p.custom_fields && Array.isArray(p.custom_fields)) {
                    for (var k = 0; k < p.custom_fields.length; k++) {
                        if (p.custom_fields[k].key === f.key) {
                            val = p.custom_fields[k].value;
                            break;
                        }
                    }
                }

                if (f.type === 'textarea') {
                    flushRowBuffer();
                    html += '<div class="mb-3">';
                    html += '   <label class="form-label font-weight-bold">' + escapeHtml(f.label) + (f.required ? ' <span class="text-danger">*</span>' : '') + '</label>';
                    html += '   <textarea class="form-control cv-project-input project-' + f.key + '" data-field-key="' + f.key + '" rows="2"' +
                        (f.required ? ' required' : '') + ' placeholder="' + escapeHtml(f.placeholder) + '">' + escapeHtml(val) + '</textarea>';
                    if (f.helptext) {
                        html += '   <small class="form-text text-muted">' + escapeHtml(f.helptext) + '</small>';
                    }
                    html += '</div>';
                } else if (f.type === 'select') {
                    var selectHtml = '<div class="col-md-6 mb-3">';
                    selectHtml += '   <label class="form-label font-weight-bold">' + escapeHtml(f.label) + (f.required ? ' <span class="text-danger">*</span>' : '') + '</label>';
                    selectHtml += '   <select class="form-select custom-select cv-project-input project-' + f.key + '" data-field-key="' + f.key + '"' + (f.required ? ' required' : '') + '>';
                    if (f.options && Array.isArray(f.options)) {
                        f.options.forEach(function(opt) {
                            var optVal = (typeof opt === 'object') ? opt.value : opt;
                            var optLbl = (typeof opt === 'object') ? (opt.label || opt.value) : opt;
                            var isSel = (String(val) === String(optVal));
                            selectHtml += '       <option value="' + escapeHtml(optVal) + '"' + (isSel ? ' selected' : '') + '>' + escapeHtml(optLbl) + '</option>';
                        });
                    }
                    selectHtml += '   </select>';
                    if (f.helptext) {
                        selectHtml += '   <small class="form-text text-muted">' + escapeHtml(f.helptext) + '</small>';
                    }
                    selectHtml += '</div>';
                    rowBuffer.push(selectHtml);
                    if (rowBuffer.length === 2) {
                        flushRowBuffer();
                    }
                } else if (f.type === 'checkbox') {
                    var checkHtml = '<div class="col-md-6 mb-3 d-flex align-items-center pt-4">';
                    checkHtml += '   <div class="form-check">';
                    checkHtml += '       <input class="form-check-input cv-project-input project-' + f.key + '" type="checkbox" data-field-key="' + f.key + '" id="' + f.key + '_' + idx + '"' + (val ? ' checked' : '') + '>';
                    checkHtml += '       <label class="form-check-label font-weight-bold" for="' + f.key + '_' + idx + '">' + escapeHtml(f.label) + '</label>';
                    if (f.helptext) {
                        checkHtml += '       <small class="form-text text-muted d-block">' + escapeHtml(f.helptext) + '</small>';
                    }
                    checkHtml += '   </div>';
                    checkHtml += '</div>';
                    rowBuffer.push(checkHtml);
                    if (rowBuffer.length === 2) {
                        flushRowBuffer();
                    }
                } else {
                    var inputType = (f.type === 'date') ? 'date' : ((f.type === 'number') ? 'number' : 'text');
                    var displayVal = (f.type === 'date') ? normalizeDateForInput(val) : val;
                    var colHtml = '<div class="col-md-6 mb-3">';
                    colHtml += '   <label class="form-label font-weight-bold">' + escapeHtml(f.label) + (f.required ? ' <span class="text-danger">*</span>' : '') + '</label>';
                    colHtml += '   <input type="' + inputType + '" class="form-control cv-project-input project-' + f.key + '" data-field-key="' + f.key + '"' +
                        (f.required ? ' required' : '') + ' value="' + escapeHtml(displayVal) + '" placeholder="' + escapeHtml(f.placeholder) + '">';
                    if (f.helptext) {
                        colHtml += '   <small class="form-text text-muted">' + escapeHtml(f.helptext) + '</small>';
                    }
                    colHtml += '</div>';
                    rowBuffer.push(colHtml);
                    if (rowBuffer.length === 2) {
                        flushRowBuffer();
                    }
                }
            });

            flushRowBuffer();
        });

        html += '</div>'; // End cv-project-card.

        document.getElementById('projects_container').insertAdjacentHTML('beforeend', html);
    }

    /**
     * Copy text to clipboard using modern API with fallback.
     *
     * @param {string} text
     * @return {Promise}
     */
    function copyTextToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }
        return new Promise(function(resolve, reject) {
            var textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                var successful = document.execCommand('copy');
                textArea.remove();
                if (successful) {
                    resolve();
                } else {
                    reject(new Error('execCommand copy failed'));
                }
            } catch (err) {
                textArea.remove();
                reject(err);
            }
        });
    }

    /**
     * Format entire dossier into clean plain text for clipboard copying.
     *
     * @param {Object|string} data
     * @return {string}
     */
    function formatDossierPlainText(data) {
        if (!data) {
            return '';
        }
        if (typeof data === 'string') {
            try {
                data = JSON.parse(data);
            } catch (e) {
                return data;
            }
        }
        var lines = [];
        var showSummary = (initialConfig.show_summary !== false);
        if (showSummary && data.summary) {
            lines.push('--- PROFESSIONAL SUMMARY ---');
            lines.push(data.summary.trim());
            lines.push('');
        }
        if (data.projects && Array.isArray(data.projects)) {
            data.projects.forEach(function(proj, i) {
                var pTitle = proj.title || ('Project #' + (i + 1));
                lines.push('--- ' + pTitle.toUpperCase() + ' ---');
                if (proj.role) {
                    lines.push('Role: ' + proj.role);
                }
                var desc = proj.formatted_description || proj.description || '';
                if (desc) {
                    lines.push(desc.trim());
                }
                lines.push('');
            });
        }
        return lines.join('\n').trim();
    }

    /**
     * Build structured HTML representation of AI output.
     *
     * @param {Object|string} data
     * @param {string} prefix
     * @return {string}
     */
    function buildOutputHtml(data, prefix) {
        var showSummary = (initialConfig.show_summary !== false);
        var pfx = prefix || '';
        var html = '';

        if (typeof data === 'string') {
            try {
                data = JSON.parse(data);
            } catch (e) {
                return '<div class="cv-output-box"><div id="' + pfx + 'raw_out" style="white-space: pre-wrap;" class="cv-formatted-output p-3 border rounded">' + escapeHtml(data) + '</div></div>';
            }
        }

        // Executive Summary if present and enabled.
        if (showSummary && data.summary) {
            var sumId = pfx + 'summary_content';
            html += '<div class="cv-output-box">' +
                '<div class="cv-output-header">' +
                '   <h5 class="mb-0 font-weight-bold text-primary"><i class="fa fa-id-badge"></i> Professional Summary</h5>' +
                '   <button type="button" class="btn btn-outline-secondary btn-sm cv-copy-btn" data-copy-target="#' + sumId + '">' +
                '       <i class="fa fa-clipboard"></i> Copy' +
                '   </button>' +
                '</div>' +
                '<div id="' + sumId + '" class="cv-summary-content">' + escapeHtml(data.summary) + '</div>' +
                '</div>';
        }

        // Projects list if present.
        if (data.projects && Array.isArray(data.projects)) {
            data.projects.forEach(function(proj, i) {
                var pId = pfx + 'proj_out_' + (i + 1);
                var formattedText = proj.formatted_description || proj.description || JSON.stringify(proj, null, 2);

                html += '<div class="cv-output-box">' +
                    '<div class="cv-output-header">' +
                    '   <h5 class="mb-0 font-weight-bold cv-project-title"><i class="fa fa-check-square text-success"></i> ' + escapeHtml(proj.title || ('Project #' + (i + 1))) + '</h5>' +
                    '   <button type="button" class="btn btn-outline-secondary btn-sm cv-copy-btn" data-copy-target="#' + pId + '">' +
                    '       <i class="fa fa-clipboard"></i> Copy' +
                    '   </button>' +
                    '</div>' +
                    (proj.role ? '<p class="text-muted small mb-2"><strong>Role:</strong> ' + escapeHtml(proj.role) + '</p>' : '') +
                    '<div id="' + pId + '" style="white-space: pre-wrap; font-family: inherit; line-height: 1.6;" class="cv-formatted-output p-3 border rounded">' +
                    escapeHtml(formattedText) +
                    '</div>' +
                    '</div>';
            });
        } else if (typeof data === 'string') {
            html += '<div class="cv-output-box">' +
                '<div id="' + pfx + 'raw_out" style="white-space: pre-wrap;" class="cv-formatted-output p-3 border rounded">' + escapeHtml(data) + '</div>' +
                '</div>';
        }

        return html;
    }

    /**
     * Show modal dialog safely across themes.
     *
     * @param {HTMLElement} modalEl
     */
    function showModal(modalEl) {
        if (!modalEl) {
            return;
        }
        if (modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }
        var backdrop = document.getElementById('cv_output_modal_backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'cv-modal-backdrop';
            backdrop.id = 'cv_output_modal_backdrop';
            document.body.appendChild(backdrop);
        }
        modalEl.style.display = 'block';
        modalEl.classList.add('show');
        modalEl.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');

        modalEl.onclick = function(e) {
            if (e.target === modalEl) {
                hideModal(modalEl);
            }
        };
    }

    /**
     * Hide modal dialog safely.
     *
     * @param {HTMLElement} modalEl
     */
    function hideModal(modalEl) {
        if (!modalEl) {
            return;
        }
        modalEl.style.display = 'none';
        modalEl.classList.remove('show');
        modalEl.setAttribute('aria-hidden', 'true');
        modalEl.onclick = null;
        document.body.classList.remove('modal-open');
        var backdrop = document.getElementById('cv_output_modal_backdrop');
        if (backdrop && backdrop.parentNode) {
            backdrop.parentNode.removeChild(backdrop);
        }
    }

    /**
     * Render AI response inside the preview container and modal window.
     *
     * @param {Object} data
     */
    function renderAiOutput(data, autoOpenModal) {
        lastAiOutputData = data;
        var showReview = (initialConfig.show_review !== false);

        var container = document.getElementById('cv_output_display');
        var modalContainer = document.getElementById('cv_modal_output_display');
        var reviewSection = document.getElementById('cv_review_section');
        var openModalDirectBtn = document.getElementById('btn_open_output_modal_direct');

        if (container) {
            container.innerHTML = buildOutputHtml(data, 'main_');
        }
        if (modalContainer) {
            modalContainer.innerHTML = buildOutputHtml(data, 'modal_');
        }

        if (openModalDirectBtn) {
            openModalDirectBtn.classList.remove('d-none');
        }

        if (showReview) {
            if (reviewSection) {
                reviewSection.classList.remove('d-none');
                if (autoOpenModal) {
                    reviewSection.scrollIntoView({ behavior: 'smooth' });
                }
            }
        } else if (autoOpenModal) {
            var modalEl = document.getElementById('cvOutputModal');
            if (modalEl) {
                showModal(modalEl);
            }
        }
    }

    /**
     * Update attempt counts and handle exhausted limit in the UI.
     *
     * @param {Object} res
     */
    function updateAttemptsUi(res) {
        if (!res) {
            return;
        }

        var badge = document.getElementById('cv_attempts_badge');
        var submitBtn = document.getElementById('btn_submit_ai');
        var exhaustedAlert = document.getElementById('cv_exhausted_alert');
        var notice = document.getElementById('cv_attempts_notice');

        if (badge && res.attempts_used !== undefined && initialConfig.max_attempts > 0) {
            badge.innerHTML = '<i class="fa fa-refresh"></i> Attempts: ' + res.attempts_used + ' / ' + initialConfig.max_attempts;
        }

        if (res.attempts_exhausted) {
            if (submitBtn) {
                submitBtn.disabled = true;
            }
            if (notice) {
                notice.classList.add('d-none');
            }
            if (exhaustedAlert) {
                exhaustedAlert.innerHTML = '<i class="fa fa-exclamation-triangle"></i> You have reached the maximum allowed attempts (' +
                    initialConfig.max_attempts + ') for this activity. You can review and download your previously generated dossier below.';
                exhaustedAlert.classList.remove('d-none');
            }
        } else if (res.attempts_remaining !== undefined && res.attempts_remaining >= 0) {
            if (notice) {
                notice.innerHTML = '<i class="fa fa-info-circle"></i> You have ' + res.attempts_remaining + ' attempt(s) remaining.';
                notice.classList.remove('d-none');
            }
        }
    }

    /**
     * Update diagnostic debug cards and modal with latest state.
     *
     * @param {Object|string} debugObj
     * @param {string} outputJson
     */
    function updateDebugUi(debugObj, outputJson) {
        if (!debugObj) {
            return;
        }
        var dbg = debugObj;
        if (typeof dbg === 'string') {
            try {
                dbg = JSON.parse(debugObj);
            } catch (e) {
                return;
            }
        }

        console.log('[mod_cv DEBUG] Diagnostic state update:', dbg);

        // Update submission status.
        var statusEls = document.querySelectorAll('#debug_top_sub_status, #debug_sub_status, #debug_modal_status_badge');
        statusEls.forEach(function(el) {
            el.textContent = dbg.submission_status || '';
        });

        // Update submission modified time.
        var timeEls = document.querySelectorAll('#debug_top_sub_modified, #debug_sub_time, #debug_modal_sub_time');
        timeEls.forEach(function(el) {
            el.textContent = dbg.submission_timemodified || '';
        });

        // Update callback received time.
        var cbTimeEls = document.querySelectorAll('#debug_top_cb_time, #debug_cb_time, #debug_modal_cb_time');
        cbTimeEls.forEach(function(el) {
            el.textContent = dbg.last_callback_time || 'None yet';
        });

        // Update callback target subid.
        var cbSubidEls = document.querySelectorAll('#debug_top_cb_subid, #debug_cb_subid, #debug_modal_cb_subid');
        cbSubidEls.forEach(function(el) {
            el.textContent = dbg.last_callback_subid || 'None';
        });

        // Update callback error.
        var cbErrEl = document.getElementById('debug_cb_error');
        if (cbErrEl) {
            cbErrEl.textContent = dbg.last_callback_error || 'None';
        }

        // Format and update callback payload.
        var rawCb = dbg.last_callback_raw || '';
        if (rawCb) {
            try {
                var parsedCb = JSON.parse(rawCb);
                rawCb = JSON.stringify(parsedCb, null, 2);
            } catch (e) {
                // Keep raw string.
            }
        }
        var cbBoxEls = document.querySelectorAll('#debug_display_callback_box, #debug_modal_display_callback');
        cbBoxEls.forEach(function(el) {
            el.textContent = rawCb || '(Empty)';
        });

        // Format and update ai_output.
        var rawAi = outputJson || dbg.ai_output_raw || '';
        if (rawAi) {
            try {
                var parsedAi = JSON.parse(rawAi);
                rawAi = JSON.stringify(parsedAi, null, 2);
            } catch (e) {
                // Keep raw string.
            }
        }
        var aiBoxEls = document.querySelectorAll('#debug_display_ai_output_box, #debug_modal_display_ai_output');
        aiBoxEls.forEach(function(el) {
            el.textContent = rawAi || '(Empty)';
        });

        // Update size.
        var sizeEls = document.querySelectorAll('#debug_top_output_size, #debug_modal_output_size');
        sizeEls.forEach(function(el) {
            el.textContent = (rawAi ? rawAi.length : 0) + ' bytes';
        });
    }

    var pollTimer = null;

    /**
     * Poll status for asynchronous processing.
     *
     * @param {Number} cmid
     */
    function startPolling(cmid) {
        if (pollTimer) {
            clearTimeout(pollTimer);
        }

        var loadingIndicator = document.getElementById('cv_loading_indicator');
        var submitBtn = document.getElementById('btn_submit_ai');
        var infoAlert = document.getElementById('cv_info_alert');
        var errorAlert = document.getElementById('cv_error_alert');

        if (loadingIndicator) {
            loadingIndicator.classList.add('active');
        }
        if (submitBtn) {
            submitBtn.disabled = true;
        }
        if (errorAlert) {
            errorAlert.classList.add('d-none');
        }
        if (infoAlert) {
            infoAlert.innerHTML = '<i class="fa fa-info-circle"></i> Your application is being processed by AI in the background. Please wait...';
            infoAlert.classList.remove('d-none');
        }

        function check() {
            ajax.call([{
                methodname: 'mod_cv_check_status',
                args: { cmid: cmid }
            }])[0].then(function(res) {
                console.log('[mod_cv DEBUG] Status check received:', res);
                if (res.debug_json) {
                    updateDebugUi(res.debug_json, res.outputjson);
                }
                if (res.has_output && res.outputjson) {
                    if (loadingIndicator) {
                        loadingIndicator.classList.remove('active');
                    }
                    if (submitBtn) {
                        submitBtn.disabled = !!res.attempts_exhausted;
                    }
                    if (infoAlert) {
                        infoAlert.classList.add('d-none');
                    }
                    updateAttemptsUi(res);
                    try {
                        var parsed = JSON.parse(res.outputjson);
                        renderAiOutput(parsed, true);
                    } catch (e) {
                        renderAiOutput(res.outputjson, true);
                    }
                } else if (res.status === 'pending') {
                    pollTimer = setTimeout(check, 4000);
                } else {
                    if (loadingIndicator) {
                        loadingIndicator.classList.remove('active');
                    }
                    if (submitBtn) {
                        submitBtn.disabled = !!res.attempts_exhausted;
                    }
                    if (infoAlert) {
                        infoAlert.classList.add('d-none');
                    }
                    updateAttemptsUi(res);
                }
            }).catch(function(err) {
                console.warn('[mod_cv DEBUG] Polling check failed, retry in 5s:', err);
                pollTimer = setTimeout(check, 5000);
            });
        }

        check();
    }

    return {
        /**
         * Initialize the mod_cv application.
         *
         * @param {Number} cmid
         */
        init: function(cmid) {
            var initialDataEl = document.getElementById('mod_cv_initial_data');
            var initialData = {};

            if (initialDataEl) {
                try {
                    initialData = JSON.parse(initialDataEl.textContent || '{}');
                } catch (e) {
                    initialData = {};
                }
            }
            initialConfig = initialData;

            if (initialData.attempts_exhausted) {
                var initialSubmitBtn = document.getElementById('btn_submit_ai');
                if (initialSubmitBtn) {
                    initialSubmitBtn.disabled = true;
                }
            }

            // Populate existing saved projects or add default empty project.
            if (initialData.saved_projects && initialData.saved_projects.length > 0) {
                initialData.saved_projects.forEach(function(proj) {
                    addProjectCard(proj);
                });
            } else {
                addProjectCard();
            }

            // Render existing AI output if already processed.
            if (initialData.ai_output) {
                renderAiOutput(initialData.ai_output, false);
            }

            // Initialize diagnostic debug UI.
            if (initialData.debug) {
                var initialOutputStr = initialData.ai_output ? JSON.stringify(initialData.ai_output) : '';
                updateDebugUi(initialData.debug, initialOutputStr);
            }

            // If already pending from a previous asynchronous request, resume polling.
            if (initialData.status === 'pending') {
                startPolling(cmid);
            }

            // Add Project button listeners.
            var btnAdd = document.getElementById('btn_add_project');
            var btnAddBottom = document.getElementById('btn_add_project_bottom');
            if (btnAdd) {
                btnAdd.addEventListener('click', function() { addProjectCard(); });
            }
            if (btnAddBottom) {
                btnAddBottom.addEventListener('click', function() { addProjectCard(); });
            }

            // Event delegation for Remove Project buttons.
            document.getElementById('projects_container').addEventListener('click', function(e) {
                var btn = e.target.closest('.btn-remove-project');
                if (btn) {
                    var targetSel = btn.getAttribute('data-target');
                    var card = document.querySelector(targetSel);
                    if (card) {
                        card.remove();
                    }
                }
            });

            // Event delegation for ongoing checkbox to disable/enable End Date.
            document.getElementById('projects_container').addEventListener('change', function(e) {
                if (e.target && e.target.classList.contains('project-iscurrent')) {
                    var card = e.target.closest('.cv-project-card');
                    if (card) {
                        var endInput = card.querySelector('.project-enddate');
                        if (endInput) {
                            if (e.target.checked) {
                                endInput.disabled = true;
                                endInput.removeAttribute('required');
                                endInput.value = '';
                            } else {
                                endInput.disabled = false;
                                endInput.setAttribute('required', 'required');
                            }
                        }
                    }
                }
            });

            // Event delegation for Copy buttons.
            document.addEventListener('click', function(e) {
                var copyBtn = e.target.closest('.cv-copy-btn');
                if (copyBtn) {
                    e.preventDefault();
                    var targetId = copyBtn.getAttribute('data-copy-target');
                    var textToCopy = '';

                    if (targetId === '#cv_output_display' || targetId === '#cv_modal_output_display') {
                        textToCopy = formatDossierPlainText(lastAiOutputData);
                        if (!textToCopy) {
                            var targetEl = document.querySelector(targetId);
                            if (targetEl) {
                                var clone = targetEl.cloneNode(true);
                                var btns = clone.querySelectorAll('button, .btn, .cv-copy-btn');
                                btns.forEach(function(b) {
                                    b.remove();
                                });
                                textToCopy = (clone.innerText || clone.textContent || '').trim();
                            }
                        }
                    } else if (targetId) {
                        var singleEl = document.querySelector(targetId);
                        if (singleEl) {
                            textToCopy = (singleEl.innerText || singleEl.textContent || '').trim();
                        }
                    }

                    if (!textToCopy) {
                        return;
                    }

                    copyTextToClipboard(textToCopy).then(function() {
                        var originalHtml = copyBtn.innerHTML;
                        var isAll = (targetId === '#cv_output_display' || targetId === '#cv_modal_output_display');
                        var copiedLabel = isAll ? (initialConfig.copied_all_text || initialConfig.copied_text || 'Copied!') :
                            (initialConfig.copied_text || 'Copied!');

                        copyBtn.innerHTML = '<i class="fa fa-check text-white"></i> ' + escapeHtml(copiedLabel);
                        var hadOutlineSecondary = copyBtn.classList.contains('btn-outline-secondary');
                        var hadPrimary = copyBtn.classList.contains('btn-primary');

                        copyBtn.classList.remove('btn-outline-secondary', 'btn-primary');
                        copyBtn.classList.add('btn-success');
                        setTimeout(function() {
                            copyBtn.innerHTML = originalHtml;
                            copyBtn.classList.remove('btn-success');
                            if (hadOutlineSecondary) {
                                copyBtn.classList.add('btn-outline-secondary');
                            }
                            if (hadPrimary) {
                                copyBtn.classList.add('btn-primary');
                            }
                        }, 2000);
                    }).catch(function(err) {
                        notification.exception(err);
                    });
                }
            });

            // Modal popup button listeners.
            var openModalBtn = document.getElementById('btn_open_output_modal');
            if (openModalBtn) {
                openModalBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var modalDialog = document.getElementById('cvOutputModal');
                    showModal(modalDialog);
                });
            }

            var openModalDirectBtn = document.getElementById('btn_open_output_modal_direct');
            if (openModalDirectBtn) {
                openModalDirectBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var modalDialog = document.getElementById('cvOutputModal');
                    showModal(modalDialog);
                });
            }

            var modalElement = document.getElementById('cvOutputModal');
            if (modalElement) {
                modalElement.querySelectorAll('[data-dismiss="modal"], [data-bs-dismiss="modal"], .close, .btn-close').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        hideModal(modalElement);
                    });
                });
            }

            var debugModalElement = document.getElementById('cvDebugModal');
            if (debugModalElement) {
                debugModalElement.querySelectorAll('[data-dismiss="modal"], [data-bs-dismiss="modal"], .close, .btn-close').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        hideModal(debugModalElement);
                    });
                });
            }

            var openDebugBtns = document.querySelectorAll('#btn_open_debug_modal, #btn_open_debug_modal_from_card, #btn_modal_open_debug');
            openDebugBtns.forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (debugModalElement) {
                        showModal(debugModalElement);
                    }
                });
            });

            var scrollDebugBtn = document.getElementById('btn_scroll_debug_card');
            if (scrollDebugBtn) {
                scrollDebugBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var card = document.getElementById('cv_debug_card');
                    if (card) {
                        card.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            }

            function refreshDebugStatus() {
                var btns = document.querySelectorAll('#btn_ajax_refresh_debug, #btn_modal_ajax_refresh_debug, #btn_modal_ajax_refresh_debug2');
                btns.forEach(function(b) {
                    b.disabled = true;
                    b.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Checking DB...';
                });

                ajax.call([{
                    methodname: 'mod_cv_check_status',
                    args: { cmid: cmid }
                }])[0].then(function(res) {
                    btns.forEach(function(b) {
                        b.disabled = false;
                        b.innerHTML = '<i class="fa fa-refresh"></i> Refresh DB Status (AJAX)';
                    });
                    console.log('[mod_cv DEBUG] Manual refresh result:', res);
                    if (res.debug_json) {
                        updateDebugUi(res.debug_json, res.outputjson);
                    }
                    updateAttemptsUi(res);
                    if (res.has_output && res.outputjson) {
                        try {
                            var parsed = JSON.parse(res.outputjson);
                            renderAiOutput(parsed, false);
                        } catch (e) {
                            renderAiOutput(res.outputjson, false);
                        }
                    }
                }).catch(function(err) {
                    btns.forEach(function(b) {
                        b.disabled = false;
                        b.innerHTML = '<i class="fa fa-refresh"></i> Refresh DB Status (AJAX)';
                    });
                    console.error('[mod_cv DEBUG] Manual refresh error:', err);
                });
            }

            var refreshBtns = document.querySelectorAll('#btn_ajax_refresh_debug, #btn_modal_ajax_refresh_debug, #btn_modal_ajax_refresh_debug2');
            refreshBtns.forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    refreshDebugStatus();
                });
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' || e.keyCode === 27) {
                    var m = document.getElementById('cvOutputModal');
                    if (m && m.classList.contains('show')) {
                        hideModal(m);
                    }
                    var dbgM = document.getElementById('cvDebugModal');
                    if (dbgM && dbgM.classList.contains('show')) {
                        hideModal(dbgM);
                    }
                }
            });

            // Course selector listener to auto-populate dates.
            var courseSelector = document.getElementById('course_selector');
            if (courseSelector) {
                courseSelector.addEventListener('change', function() {
                    var selectedOption = courseSelector.options[courseSelector.selectedIndex];
                    if (selectedOption) {
                        var sDate = selectedOption.getAttribute('data-startdate') || '';
                        var eDate = selectedOption.getAttribute('data-enddate') || '';
                        var startInput = document.getElementById('course_startdate');
                        var endInput = document.getElementById('course_enddate');
                        if (startInput && sDate) {
                            startInput.value = sDate;
                        }
                        if (endInput && eDate) {
                            endInput.value = eDate;
                        }
                    }
                });
            }

            // Form submission.
            var form = document.getElementById('cv_input_form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    var errorAlert = document.getElementById('cv_error_alert');
                    var loadingIndicator = document.getElementById('cv_loading_indicator');
                    var submitBtn = document.getElementById('btn_submit_ai');

                    errorAlert.classList.add('d-none');
                    errorAlert.textContent = '';

                    // Collect candidate profile if section is visible.
                    var candNameEl = document.getElementById('candidate_name');
                    var profile = {};

                    if (candNameEl) {
                        var instEl = document.getElementById('degree_institution');
                        var degreeStartEl = document.getElementById('degree_startdate');
                        var degreeEndEl = document.getElementById('degree_enddate');
                        var phoneEl = document.getElementById('candidate_phone');
                        var countryEl = document.getElementById('candidate_country');
                        var degreeLevelEl = document.getElementById('degree_level');
                        var candEmailEl = document.getElementById('candidate_email');

                        profile = {
                            name: candNameEl.value.trim(),
                            email: candEmailEl ? candEmailEl.value.trim() : '',
                            phone: phoneEl ? phoneEl.value.trim() : '',
                            country: countryEl ? countryEl.value.trim() : '',
                            degree: degreeLevelEl ? degreeLevelEl.value : 'bachelors',
                            institution: instEl ? instEl.value.trim() : '',
                            degree_startdate: degreeStartEl ? degreeStartEl.value.trim() : '',
                            degree_enddate: degreeEndEl ? degreeEndEl.value.trim() : ''
                        };

                        if (!profile.name || !profile.email || !profile.institution ||
                            !profile.degree_startdate || !profile.degree_enddate) {
                            errorAlert.textContent = 'Please complete all required fields (*) in Candidate Information.';
                            errorAlert.classList.remove('d-none');
                            if (instEl) {
                                instEl.scrollIntoView({ behavior: 'smooth' });
                            }
                            return;
                        }
                    } else {
                        // Section is hidden, use defaults.
                        var defaultCand = initialData.default_candidate || {};
                        profile = {
                            name: defaultCand.name || '',
                            email: defaultCand.email || '',
                            phone: '',
                            country: '',
                            degree: 'bachelors',
                            institution: '',
                            degree_startdate: '',
                            degree_enddate: ''
                        };
                    }

                    // Collect course info if section is visible.
                    var cSelector = document.getElementById('course_selector');
                    var courseStartEl = document.getElementById('course_startdate');
                    var courseEndEl = document.getElementById('course_enddate');
                    var courseInfo = {};

                    if (courseStartEl && courseEndEl) {
                        var courseId = cSelector ? parseInt(cSelector.value, 10) || 0 : 0;
                        var courseName = '';
                        if (cSelector && cSelector.selectedIndex >= 0) {
                            var opt = cSelector.options[cSelector.selectedIndex];
                            courseName = opt.getAttribute('data-fullname') || opt.text || '';
                        }
                        var courseStartDate = courseStartEl.value.trim();
                        var courseEndDate = courseEndEl.value.trim();

                        if (!courseStartDate || !courseEndDate) {
                            errorAlert.textContent = 'Please provide both start and end dates for the course.';
                            errorAlert.classList.remove('d-none');
                            courseStartEl.scrollIntoView({ behavior: 'smooth' });
                            return;
                        }

                        courseInfo = {
                            id: courseId,
                            name: courseName,
                            startdate: courseStartDate,
                            enddate: courseEndDate
                        };
                    } else {
                        // Section is hidden, pass default course data.
                        courseInfo = {
                            id: 0,
                            name: '',
                            startdate: '',
                            enddate: ''
                        };
                    }

                    // Collect project cards.
                    var projectCards = document.querySelectorAll('.cv-project-card');
                    if (projectCards.length === 0) {
                        errorAlert.textContent = 'Please add at least one project before processing.';
                        errorAlert.classList.remove('d-none');
                        return;
                    }

                    var activeFields = getActiveProjectFields();
                    var standardKeys = [
                        'title', 'industry', 'organization', 'jobtitle', 'role', 'methodology',
                        'startdate', 'enddate', 'iscurrent', 'objective', 'scope', 'responsibilities',
                        'deliverables', 'stakeholders', 'teamresources', 'challenges', 'changes',
                        'outcomes', 'measurableresults', 'closure', 'additionalinfo'
                    ];

                    var projects = [];
                    for (var i = 0; i < projectCards.length; i++) {
                        var card = projectCards[i];
                        var proj = {
                            custom_fields: []
                        };

                        // Check timeline ongoing state.
                        var isCurrentEl = card.querySelector('.project-iscurrent');
                        var isCurrent = isCurrentEl ? isCurrentEl.checked : false;
                        proj.iscurrent = isCurrent;

                        // Validate and collect each active field.
                        for (var fIdx = 0; fIdx < activeFields.length; fIdx++) {
                            var f = activeFields[fIdx];
                            if (f.key === 'iscurrent') {
                                continue;
                            }

                            var inputEl = card.querySelector('[data-field-key="' + f.key + '"]');
                            var val = '';

                            if (inputEl) {
                                if (f.type === 'checkbox') {
                                    val = inputEl.checked;
                                } else {
                                    val = inputEl.value.trim();
                                }
                            }

                            // Special timeline rule: if project is ongoing, end date is not required.
                            var isEnddateAndCurrent = (f.key === 'enddate' && isCurrent);
                            if (isEnddateAndCurrent) {
                                val = '';
                            }

                            if (f.required && !isEnddateAndCurrent) {
                                if (f.type === 'checkbox' ? !val : (val === '')) {
                                    errorAlert.textContent = 'Please complete required field "' + f.label + '" for Project #' + (i + 1) + '.';
                                    errorAlert.classList.remove('d-none');
                                    if (inputEl) {
                                        inputEl.scrollIntoView({ behavior: 'smooth' });
                                        inputEl.focus();
                                    }
                                    return;
                                }
                            }

                            if (standardKeys.indexOf(f.key) !== -1) {
                                proj[f.key] = val;
                            } else {
                                proj[f.key] = val;
                                proj.custom_fields.push({
                                    key: f.key,
                                    label: f.label,
                                    value: String(val)
                                });
                            }
                        }

                        // Ensure standard properties have safe fallbacks for Moodle external API structure.
                        if (!proj.title) {
                            proj.title = 'Project #' + (i + 1);
                        }
                        if (!proj.industry) {
                            proj.industry = 'General';
                        }
                        if (!proj.jobtitle) {
                            proj.jobtitle = 'Professional';
                        }
                        if (!proj.role) {
                            proj.role = 'Lead';
                        }
                        if (!proj.methodology) {
                            proj.methodology = 'predictive';
                        }
                        if (!proj.notes) {
                            proj.notes = (proj.objective || '') + '\n' + (proj.responsibilities || '');
                        }

                        projects.push(proj);
                    }

                    // Show loading.
                    loadingIndicator.classList.add('active');
                    submitBtn.disabled = true;

                    ajax.call([{
                        methodname: 'mod_cv_submit',
                        args: {
                            cmid: cmid,
                            profile: profile,
                            projects: projects,
                            course_info: courseInfo
                        }
                    }])[0].then(function(res) {
                        console.log('[mod_cv DEBUG] Submit response received:', res);
                        if (res.debug_json) {
                            updateDebugUi(res.debug_json, res.outputjson);
                        }
                        if (res.status && res.outputjson) {
                            loadingIndicator.classList.remove('active');
                            submitBtn.disabled = !!res.attempts_exhausted;
                            updateAttemptsUi(res);
                            try {
                                var parsed = JSON.parse(res.outputjson);
                                renderAiOutput(parsed, true);
                            } catch (e) {
                                renderAiOutput(res.outputjson, true);
                            }
                        } else if (res.status) {
                            // Asynchronous background processing: start polling.
                            startPolling(cmid);
                        } else {
                            loadingIndicator.classList.remove('active');
                            submitBtn.disabled = false;
                            errorAlert.textContent = res.message || 'Error communicating with n8n.';
                            errorAlert.classList.remove('d-none');
                        }
                    }).catch(function(err) {
                        loadingIndicator.classList.remove('active');
                        submitBtn.disabled = false;
                        var msg = err.message || err.error || 'Error occurred while processing request.';
                        if (err.debuginfo && err.debuginfo !== msg) {
                            msg += ' (' + err.debuginfo + ')';
                        }
                        errorAlert.textContent = msg;
                        errorAlert.classList.remove('d-none');
                    });
                });
            }
        }
    };
});
