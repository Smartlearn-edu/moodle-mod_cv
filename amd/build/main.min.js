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
     * Add a project card to the container.
     *
     * @param {Object} data
     */
    function addProjectCard(data) {
        projectCounter++;
        var p = data || {};
        var idx = projectCounter;

        var html = '<div class="cv-project-card" id="project_card_' + idx + '">' +
            '<div class="cv-project-header">' +
            '   <h5 class="mb-0 text-secondary"><i class="fa fa-folder-open text-primary"></i> Project #' + idx + '</h5>' +
            '   <button type="button" class="btn btn-outline-danger btn-sm btn-remove-project" data-target="#project_card_' + idx + '">' +
            '       <i class="fa fa-trash"></i> Remove' +
            '   </button>' +
            '</div>' +
            '<div class="row">' +
            '   <div class="col-md-6 mb-3">' +
            '       <label class="form-label font-weight-bold">Project Title *</label>' +
            '       <input type="text" class="form-control project-title" required value="' + escapeHtml(p.title || '') + '" placeholder="e.g. Core Banking System Upgrade">' +
            '   </div>' +
            '   <div class="col-md-6 mb-3">' +
            '       <label class="form-label font-weight-bold">Your Role *</label>' +
            '       <input type="text" class="form-control project-role" required value="' + escapeHtml(p.role || '') + '" placeholder="e.g. Project Manager / Scrum Master">' +
            '   </div>' +
            '</div>' +
            '<div class="row">' +
            '   <div class="col-md-4 mb-3">' +
            '       <label class="form-label font-weight-bold">Methodology</label>' +
            '       <select class="form-select custom-select project-methodology">' +
            '           <option value="predictive"' + (p.methodology === 'predictive' ? ' selected' : '') + '>Predictive / Waterfall</option>' +
            '           <option value="agile"' + (p.methodology === 'agile' ? ' selected' : '') + '>Agile</option>' +
            '           <option value="hybrid"' + (p.methodology === 'hybrid' ? ' selected' : '') + '>Hybrid</option>' +
            '       </select>' +
            '   </div>' +
            '   <div class="col-md-4 mb-3">' +
            '       <label class="form-label font-weight-bold">Start Date (MM/YYYY) *</label>' +
            '       <input type="text" class="form-control project-startdate" required value="' + escapeHtml(p.startdate || '') + '" placeholder="01/2023">' +
            '   </div>' +
            '   <div class="col-md-4 mb-3">' +
            '       <label class="form-label font-weight-bold">End Date (MM/YYYY)</label>' +
            '       <input type="text" class="form-control project-enddate" value="' + escapeHtml(p.enddate || '') + '" placeholder="12/2023">' +
            '       <div class="form-check mt-1">' +
            '           <input class="form-check-input project-iscurrent" type="checkbox" id="iscurrent_' + idx + '"' + (p.iscurrent ? ' checked' : '') + '>' +
            '           <label class="form-check-label small text-muted" for="iscurrent_' + idx + '">Project is ongoing</label>' +
            '       </div>' +
            '   </div>' +
            '</div>' +
            '<div class="mb-2">' +
            '   <label class="form-label font-weight-bold">Tasks & Notes *</label>' +
            '   <textarea class="form-control project-notes" rows="4" required placeholder="Describe project objectives, key deliverables, what you personally managed, tools used, and final results...">' + escapeHtml(p.notes || '') + '</textarea>' +
            '</div>' +
            '</div>';

        document.getElementById('projects_container').insertAdjacentHTML('beforeend', html);
    }

    /**
     * Render AI response inside the preview container.
     *
     * @param {Object} data
     */
    function renderAiOutput(data) {
        var container = document.getElementById('cv_output_display');
        if (!container) {
            return;
        }

        var html = '';

        // Executive Summary if present.
        if (data.summary) {
            html += '<div class="cv-output-box">' +
                '<div class="cv-output-header">' +
                '   <h5 class="mb-0 font-weight-bold text-primary"><i class="fa fa-id-badge"></i> Professional Summary</h5>' +
                '   <button type="button" class="btn btn-outline-secondary btn-sm cv-copy-btn" data-copy-target="#summary_content">' +
                '       <i class="fa fa-clipboard"></i> Copy' +
                '   </button>' +
                '</div>' +
                '<div id="summary_content" class="text-dark">' + escapeHtml(data.summary) + '</div>' +
                '</div>';
        }

        // Projects list if present.
        if (data.projects && Array.isArray(data.projects)) {
            data.projects.forEach(function(proj, i) {
                var pId = 'proj_out_' + (i + 1);
                var formattedText = proj.formatted_description || proj.description || JSON.stringify(proj, null, 2);

                html += '<div class="cv-output-box">' +
                    '<div class="cv-output-header">' +
                    '   <h5 class="mb-0 font-weight-bold text-dark"><i class="fa fa-check-square text-success"></i> ' + escapeHtml(proj.title || ('Project #' + (i + 1))) + '</h5>' +
                    '   <button type="button" class="btn btn-outline-secondary btn-sm cv-copy-btn" data-copy-target="#' + pId + '">' +
                    '       <i class="fa fa-clipboard"></i> Copy for PMI.org' +
                    '   </button>' +
                    '</div>' +
                    (proj.role ? '<p class="text-muted small mb-2"><strong>Role:</strong> ' + escapeHtml(proj.role) + '</p>' : '') +
                    '<div id="' + pId + '" style="white-space: pre-wrap; font-family: inherit; line-height: 1.6;" class="bg-white p-3 border rounded">' +
                    escapeHtml(formattedText) +
                    '</div>' +
                    '</div>';
            });
        } else if (typeof data === 'string') {
            html += '<div class="cv-output-box">' +
                '<div id="raw_out" style="white-space: pre-wrap;" class="bg-white p-3 border rounded">' + escapeHtml(data) + '</div>' +
                '</div>';
        }

        container.innerHTML = html;
        var reviewSection = document.getElementById('cv_review_section');
        if (reviewSection) {
            reviewSection.classList.remove('d-none');
            reviewSection.scrollIntoView({ behavior: 'smooth' });
        }
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
        if (loadingIndicator) {
            loadingIndicator.classList.add('active');
        }
        if (submitBtn) {
            submitBtn.disabled = true;
        }

        function check() {
            ajax.call([{
                methodname: 'mod_cv_check_status',
                args: { cmid: cmid }
            }])[0].then(function(res) {
                if (res.has_output && res.outputjson) {
                    if (loadingIndicator) {
                        loadingIndicator.classList.remove('active');
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                    }
                    try {
                        var parsed = JSON.parse(res.outputjson);
                        renderAiOutput(parsed);
                    } catch (e) {
                        // Ignore parse error.
                    }
                } else if (res.status === 'pending') {
                    pollTimer = setTimeout(check, 4000);
                } else {
                    if (loadingIndicator) {
                        loadingIndicator.classList.remove('active');
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                    }
                }
            }).catch(function() {
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
                renderAiOutput(initialData.ai_output);
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

            // Event delegation for Copy buttons.
            document.addEventListener('click', function(e) {
                var copyBtn = e.target.closest('.cv-copy-btn');
                if (copyBtn) {
                    var targetId = copyBtn.getAttribute('data-copy-target');
                    var targetEl = document.querySelector(targetId);
                    if (targetEl) {
                        var textToCopy = targetEl.innerText || targetEl.textContent;
                        navigator.clipboard.writeText(textToCopy).then(function() {
                            var originalHtml = copyBtn.innerHTML;
                            copyBtn.innerHTML = '<i class="fa fa-check text-success"></i> Copied!';
                            copyBtn.classList.remove('btn-outline-secondary');
                            copyBtn.classList.add('btn-success');
                            setTimeout(function() {
                                copyBtn.innerHTML = originalHtml;
                                copyBtn.classList.remove('btn-success');
                                copyBtn.classList.add('btn-outline-secondary');
                            }, 2000);
                        }).catch(function(err) {
                            notification.exception(err);
                        });
                    }
                }
            });

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

                    // Collect candidate profile.
                    var profile = {
                        name: document.getElementById('candidate_name').value.trim(),
                        email: document.getElementById('candidate_email').value.trim(),
                        phone: document.getElementById('candidate_phone').value.trim(),
                        country: document.getElementById('candidate_country').value.trim(),
                        degree: document.getElementById('degree_level').value
                    };

                    // Collect project cards.
                    var projectCards = document.querySelectorAll('.cv-project-card');
                    if (projectCards.length === 0) {
                        errorAlert.textContent = 'Please add at least one project before processing.';
                        errorAlert.classList.remove('d-none');
                        return;
                    }

                    var projects = [];
                    for (var i = 0; i < projectCards.length; i++) {
                        var card = projectCards[i];
                        var title = card.querySelector('.project-title').value.trim();
                        var role = card.querySelector('.project-role').value.trim();
                        var methodology = card.querySelector('.project-methodology').value;
                        var startdate = card.querySelector('.project-startdate').value.trim();
                        var enddate = card.querySelector('.project-enddate').value.trim();
                        var iscurrent = card.querySelector('.project-iscurrent').checked;
                        var notes = card.querySelector('.project-notes').value.trim();

                        if (!title || !role || !startdate || !notes) {
                            errorAlert.textContent = 'Please complete all required fields for Project #' + (i + 1);
                            errorAlert.classList.remove('d-none');
                            return;
                        }

                        projects.push({
                            title: title,
                            role: role,
                            methodology: methodology,
                            startdate: startdate,
                            enddate: enddate,
                            iscurrent: iscurrent,
                            notes: notes
                        });
                    }

                    // Show loading.
                    loadingIndicator.classList.add('active');
                    submitBtn.disabled = true;

                    ajax.call([{
                        methodname: 'mod_cv_submit',
                        args: {
                            cmid: cmid,
                            profile: profile,
                            projects: projects
                        }
                    }])[0].then(function(res) {
                        if (res.status && res.outputjson) {
                            loadingIndicator.classList.remove('active');
                            submitBtn.disabled = false;
                            var parsed = JSON.parse(res.outputjson);
                            renderAiOutput(parsed);
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
