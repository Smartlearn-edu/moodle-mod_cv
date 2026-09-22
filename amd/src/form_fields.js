define([], function() {
    'use strict';

    var currentFields = [];
    var sectionDefs = {};
    var typeDefs = {};
    var defaultFieldsBackup = [];
    var modalElement = null;

    /**
     * Escape HTML helper.
     *
     * @param {String} str
     * @return {String}
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
     * Handle Escape key to close modal.
     *
     * @param {KeyboardEvent} e
     */
    function onEscapeKey(e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            hideModal();
        }
    }

    /**
     * Show modal dialog safely across themes without stacking context conflicts.
     */
    function showModal() {
        if (!modalElement) {
            modalElement = document.getElementById('cvFieldModal');
        }
        if (!modalElement) {
            return;
        }

        // Relocate modal to document.body to escape form/collapsible stacking contexts.
        if (modalElement.parentElement !== document.body) {
            document.body.appendChild(modalElement);
        }

        // Clean up any stale backdrops.
        var staleBackdrops = document.querySelectorAll('.cv-modal-backdrop, .modal-backdrop');
        staleBackdrops.forEach(function(b) {
            b.remove();
        });

        // Create backdrop on body.
        var backdrop = document.createElement('div');
        backdrop.className = 'cv-modal-backdrop';
        backdrop.id = 'cv_modal_backdrop';
        backdrop.addEventListener('click', function(e) {
            e.preventDefault();
            hideModal();
        });
        document.body.appendChild(backdrop);

        // Display modal.
        modalElement.style.display = 'block';
        modalElement.classList.add('show');
        modalElement.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');

        // Dismiss if user clicks outside dialog on the modal backdrop container.
        modalElement.onclick = function(e) {
            if (e.target === modalElement) {
                hideModal();
            }
        };

        // Listen for Escape key.
        document.removeEventListener('keydown', onEscapeKey);
        document.addEventListener('keydown', onEscapeKey);

        // Focus the first input field smoothly.
        setTimeout(function() {
            var labelInput = document.getElementById('modal_field_label');
            if (labelInput) {
                labelInput.focus();
            }
        }, 100);
    }

    /**
     * Hide modal dialog safely.
     */
    function hideModal() {
        if (!modalElement) {
            modalElement = document.getElementById('cvFieldModal');
        }
        if (modalElement) {
            modalElement.style.display = 'none';
            modalElement.classList.remove('show');
            modalElement.setAttribute('aria-hidden', 'true');
            modalElement.onclick = null;
        }

        document.body.classList.remove('modal-open');
        document.removeEventListener('keydown', onEscapeKey);

        var backdrops = document.querySelectorAll('.cv-modal-backdrop, .modal-backdrop');
        backdrops.forEach(function(b) {
            b.remove();
        });
    }

    /**
     * Update hidden form input and re-render table.
     */
    function syncAndRender() {
        // Re-index sortorder sequentially.
        for (var i = 0; i < currentFields.length; i++) {
            currentFields[i].sortorder = i + 1;
        }

        var hiddenInput = document.querySelector('input[name="projectfields"]');
        if (hiddenInput) {
            hiddenInput.value = JSON.stringify(currentFields);
        }

        renderTable();
    }

    /**
     * Render the fields management table.
     */
    function renderTable() {
        var tbody = document.getElementById('cv_fields_table_body');
        var countBadge = document.getElementById('cv_fields_count_badge');
        if (!tbody) {
            return;
        }

        if (countBadge) {
            countBadge.textContent = currentFields.length + ' fields';
        }

        tbody.innerHTML = '';

        if (currentFields.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No fields configured. Click "Reset to Default Fields" or "Add Field".</td></tr>';
            return;
        }

        currentFields.forEach(function(field, index) {
            var tr = document.createElement('tr');
            if (!field.enabled) {
                tr.classList.add('table-secondary', 'text-muted');
            }

            var sectionObj = sectionDefs[field.section] || { title: field.section, icon: 'fa fa-folder' };
            var typeLabel = typeDefs[field.type] || field.type;

            var html = '';
            html += '<td class="text-center font-weight-bold text-muted">' + (index + 1) + '</td>';
            html += '<td>';
            html += '   <strong>' + escapeHtml(field.label) + '</strong>';
            if (field.is_standard) {
                html += ' <span class="badge badge-light border text-muted ms-1" style="font-size: 0.65rem;">Standard</span>';
            } else {
                html += ' <span class="badge badge-primary bg-primary text-white ms-1" style="font-size: 0.65rem;">Custom</span>';
            }
            if (field.helptext) {
                html += '<br><small class="text-muted">' + escapeHtml(field.helptext) + '</small>';
            }
            html += '</td>';

            html += '<td><code>' + escapeHtml(field.key) + '</code></td>';
            html += '<td><span class="badge bg-secondary text-white">' + escapeHtml(typeLabel) + '</span></td>';
            html += '<td><span class="badge bg-light text-dark border"><i class="' + sectionObj.icon + ' me-1"></i> ' + escapeHtml(sectionObj.title) + '</span></td>';

            html += '<td class="text-center">';
            if (field.required) {
                html += '<span class="text-danger font-weight-bold"><i class="fa fa-check-circle"></i> Required</span>';
            } else {
                html += '<span class="text-muted">Optional</span>';
            }
            html += '</td>';

            html += '<td class="text-center">';
            if (field.enabled) {
                html += '<span class="badge bg-success text-white"><i class="fa fa-check"></i> Active</span>';
            } else {
                html += '<span class="badge bg-secondary text-white"><i class="fa fa-eye-slash"></i> Hidden</span>';
            }
            html += '</td>';

            html += '<td class="text-end text-nowrap">';
            html += '   <div class="btn-group btn-group-sm" role="group">';
            html += '       <button type="button" class="btn btn-outline-secondary btn-move-up" data-index="' + index + '" title="Move Up"' + (index === 0 ? ' disabled' : '') + '><i class="fa fa-arrow-up"></i></button>';
            html += '       <button type="button" class="btn btn-outline-secondary btn-move-down" data-index="' + index + '" title="Move Down"' + (index === currentFields.length - 1 ? ' disabled' : '') + '><i class="fa fa-arrow-down"></i></button>';
            html += '       <button type="button" class="btn btn-outline-info btn-toggle-visibility" data-index="' + index + '" title="Toggle Visibility"><i class="fa ' + (field.enabled ? 'fa-eye-slash' : 'fa-eye') + '"></i></button>';
            html += '       <button type="button" class="btn btn-outline-primary btn-edit-field" data-index="' + index + '" title="Edit"><i class="fa fa-pencil"></i></button>';
            html += '       <button type="button" class="btn btn-outline-danger btn-delete-field" data-index="' + index + '" title="Delete"><i class="fa fa-trash"></i></button>';
            html += '   </div>';
            html += '</td>';

            tr.innerHTML = html;
            tbody.appendChild(tr);
        });
    }

    /**
     * Open the field modal in Edit or Add mode.
     *
     * @param {Object|null} field Field object if editing, null if adding.
     */
    function openModal(field) {
        var modalTitle = document.getElementById('cvFieldModalLabel');
        var origKeyInput = document.getElementById('modal_field_original_key');
        var labelInput = document.getElementById('modal_field_label');
        var keyInput = document.getElementById('modal_field_key');
        var typeSelect = document.getElementById('modal_field_type');
        var sectionSelect = document.getElementById('modal_field_section');
        var optionsGroup = document.getElementById('modal_field_options_group');
        var optionsTextarea = document.getElementById('modal_field_options');
        var placeholderInput = document.getElementById('modal_field_placeholder');
        var helptextInput = document.getElementById('modal_field_helptext');
        var requiredCheck = document.getElementById('modal_field_required');
        var enabledCheck = document.getElementById('modal_field_enabled');
        var errorAlert = document.getElementById('modal_field_error');

        if (errorAlert) {
            errorAlert.classList.add('d-none');
            errorAlert.textContent = '';
        }

        // Dynamically ensure all configured sections exist in the select dropdown.
        if (sectionSelect && sectionDefs && Object.keys(sectionDefs).length > 0) {
            var existingKeys = [];
            for (var optIdx = 0; optIdx < sectionSelect.options.length; optIdx++) {
                existingKeys.push(sectionSelect.options[optIdx].value);
            }
            Object.keys(sectionDefs).forEach(function(secKey) {
                if (existingKeys.indexOf(secKey) === -1) {
                    var newOpt = document.createElement('option');
                    newOpt.value = secKey;
                    newOpt.textContent = sectionDefs[secKey].title || secKey;
                    sectionSelect.appendChild(newOpt);
                }
            });
        }

        if (field) {
            // Edit mode.
            if (modalTitle) {
                modalTitle.textContent = 'Edit Field: ' + field.label;
            }
            if (origKeyInput) {
                origKeyInput.value = field.key;
            }
            if (labelInput) {
                labelInput.value = field.label;
            }
            if (keyInput) {
                keyInput.value = field.key;
                keyInput.disabled = !!field.is_standard; // Keep standard keys fixed for backward compatibility.
            }
            if (typeSelect) {
                typeSelect.value = field.type;
                typeSelect.disabled = !!field.is_standard;
            }
            if (sectionSelect) {
                sectionSelect.value = field.section;
            }
            if (placeholderInput) {
                placeholderInput.value = field.placeholder || '';
            }
            if (helptextInput) {
                helptextInput.value = field.helptext || '';
            }
            if (requiredCheck) {
                requiredCheck.checked = !!field.required;
            }
            if (enabledCheck) {
                enabledCheck.checked = (field.enabled !== false);
            }

            // Options for select type.
            var optLines = [];
            if (field.options && Array.isArray(field.options)) {
                field.options.forEach(function(o) {
                    if (typeof o === 'object' && o.value) {
                        optLines.push(o.value === o.label ? o.value : o.value + ' | ' + o.label);
                    } else if (typeof o === 'string') {
                        optLines.push(o);
                    }
                });
            }
            if (optionsTextarea) {
                optionsTextarea.value = optLines.join('\n');
            }
            if (optionsGroup) {
                optionsGroup.style.display = (field.type === 'select') ? 'block' : 'none';
            }
        } else {
            // Add mode.
            if (modalTitle) {
                modalTitle.textContent = 'Add Custom Field';
            }
            if (origKeyInput) {
                origKeyInput.value = '';
            }
            if (labelInput) {
                labelInput.value = '';
            }
            if (keyInput) {
                keyInput.value = '';
                keyInput.disabled = false;
            }
            if (typeSelect) {
                typeSelect.value = 'text';
                typeSelect.disabled = false;
            }
            if (sectionSelect) {
                sectionSelect.value = 'custom';
            }
            if (placeholderInput) {
                placeholderInput.value = '';
            }
            if (helptextInput) {
                helptextInput.value = '';
            }
            if (requiredCheck) {
                requiredCheck.checked = false;
            }
            if (enabledCheck) {
                enabledCheck.checked = true;
            }
            if (optionsTextarea) {
                optionsTextarea.value = '';
            }
            if (optionsGroup) {
                optionsGroup.style.display = 'none';
            }
        }

        showModal();
    }

    /**
     * Save field from modal dialog.
     */
    function saveModalField() {
        var origKey = document.getElementById('modal_field_original_key').value.trim();
        var label = document.getElementById('modal_field_label').value.trim();
        var rawKey = document.getElementById('modal_field_key').value.trim();
        var type = document.getElementById('modal_field_type').value;
        var section = document.getElementById('modal_field_section').value;
        var placeholder = document.getElementById('modal_field_placeholder').value.trim();
        var helptext = document.getElementById('modal_field_helptext').value.trim();
        var required = document.getElementById('modal_field_required').checked;
        var enabled = document.getElementById('modal_field_enabled').checked;
        var optionsText = document.getElementById('modal_field_options').value;
        var errorAlert = document.getElementById('modal_field_error');

        errorAlert.classList.add('d-none');
        errorAlert.textContent = '';

        if (!label) {
            errorAlert.textContent = 'Please enter a field label.';
            errorAlert.classList.remove('d-none');
            return;
        }

        var key = rawKey.toLowerCase().replace(/[^a-z0-9_]/g, '');
        if (!key) {
            key = label.toLowerCase().replace(/[^a-z0-9_]/g, '_').replace(/_+/g, '_').replace(/^_|_$/g, '');
            if (!key) {
                key = 'custom_field_' + (currentFields.length + 1);
            }
        }

        // Check for duplicate keys.
        for (var i = 0; i < currentFields.length; i++) {
            if (currentFields[i].key === key && currentFields[i].key !== origKey) {
                errorAlert.textContent = 'A field with identifier "' + key + '" already exists. Please choose a unique identifier.';
                errorAlert.classList.remove('d-none');
                return;
            }
        }

        // Parse options if select.
        var parsedOptions = [];
        if (type === 'select' && optionsText.trim()) {
            var lines = optionsText.split(/\r\n|\r|\n/);
            lines.forEach(function(line) {
                line = line.trim();
                if (line) {
                    if (line.indexOf('|') !== -1) {
                        var parts = line.split('|');
                        parsedOptions.push({
                            value: parts[0].trim(),
                            label: (parts[1] || parts[0]).trim()
                        });
                    } else {
                        parsedOptions.push({
                            value: line,
                            label: line
                        });
                    }
                }
            });
        }

        if (origKey) {
            // Updating existing field.
            for (var j = 0; j < currentFields.length; j++) {
                if (currentFields[j].key === origKey) {
                    currentFields[j].label = label;
                    if (!currentFields[j].is_standard) {
                        currentFields[j].key = key;
                        currentFields[j].type = type;
                    }
                    currentFields[j].section = section;
                    currentFields[j].placeholder = placeholder;
                    currentFields[j].helptext = helptext;
                    currentFields[j].required = required;
                    currentFields[j].enabled = enabled;
                    if (type === 'select') {
                        currentFields[j].options = parsedOptions;
                    }
                    break;
                }
            }
        } else {
            // Adding new custom field.
            currentFields.push({
                key: key,
                label: label,
                type: type,
                section: section,
                required: required,
                placeholder: placeholder,
                helptext: helptext,
                options: parsedOptions,
                enabled: enabled,
                sortorder: currentFields.length + 1,
                is_standard: false
            });
        }

        hideModal();
        syncAndRender();
    }

    return {
        /**
         * Initialize the form fields manager.
         *
         * @param {Array} fields Initial field configurations.
         * @param {Object} sections Available sections dictionary.
         * @param {Object} types Available types dictionary.
         */
        init: function(fields, sections, types) {
            var metaEl = document.getElementById('cv_project_fields_metadata');
            var meta = {};
            if (metaEl) {
                try {
                    meta = JSON.parse(metaEl.textContent || '{}');
                } catch (e) {
                    meta = {};
                }
            }

            sectionDefs = (sections && Object.keys(sections).length > 0) ? sections : (meta.sections || {});
            typeDefs = (types && Object.keys(types).length > 0) ? types : (meta.types || {});

            var initialList = [];
            if (Array.isArray(fields) && fields.length > 0) {
                initialList = fields;
            } else {
                var hiddenInput = document.querySelector('input[name="projectfields"]');
                if (hiddenInput && hiddenInput.value) {
                    try {
                        initialList = JSON.parse(hiddenInput.value);
                    } catch (err) {
                        initialList = [];
                    }
                }
            }

            currentFields = Array.isArray(initialList) ? JSON.parse(JSON.stringify(initialList)) : [];
            defaultFieldsBackup = JSON.parse(JSON.stringify(currentFields));
            modalElement = document.getElementById('cvFieldModal');
            if (modalElement && modalElement.parentElement !== document.body) {
                document.body.appendChild(modalElement);
            }

            // Render initial table.
            syncAndRender();

            // Add Custom Field button.
            var btnAdd = document.getElementById('btn_add_custom_field');
            if (btnAdd) {
                btnAdd.addEventListener('click', function(e) {
                    e.preventDefault();
                    openModal(null);
                });
            }

            // Reset to Defaults button.
            var btnReset = document.getElementById('btn_reset_default_fields');
            if (btnReset) {
                btnReset.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (confirm('Are you sure you want to reset all project fields to the default configuration? Any custom fields will be removed.')) {
                        currentFields = JSON.parse(JSON.stringify(defaultFieldsBackup));
                        syncAndRender();
                    }
                });
            }

            // Save Field in Modal button.
            var btnSaveModal = document.getElementById('btn_save_modal_field');
            if (btnSaveModal) {
                btnSaveModal.addEventListener('click', function(e) {
                    e.preventDefault();
                    saveModalField();
                });
            }

            // Type dropdown change in modal.
            var typeSelect = document.getElementById('modal_field_type');
            if (typeSelect) {
                typeSelect.addEventListener('change', function() {
                    var optionsGroup = document.getElementById('modal_field_options_group');
                    if (optionsGroup) {
                        optionsGroup.style.display = (typeSelect.value === 'select') ? 'block' : 'none';
                    }
                });
            }

            // Auto-generate key from label in modal if adding new field.
            var labelInput = document.getElementById('modal_field_label');
            var keyInput = document.getElementById('modal_field_key');
            if (labelInput && keyInput) {
                labelInput.addEventListener('input', function() {
                    var origKey = document.getElementById('modal_field_original_key').value;
                    if (!origKey && !keyInput.disabled) {
                        keyInput.value = labelInput.value.toLowerCase().replace(/[^a-z0-9_]/g, '_').replace(/_+/g, '_').replace(/^_|_$/g, '');
                    }
                });
            }

            // Close modal events via delegation.
            if (modalElement) {
                modalElement.addEventListener('click', function(e) {
                    var closeBtn = e.target.closest('[data-bs-dismiss="modal"], [data-dismiss="modal"], .btn-close, .close');
                    if (closeBtn) {
                        e.preventDefault();
                        hideModal();
                    }
                });
            }

            // Table actions delegation.
            var tbody = document.getElementById('cv_fields_table_body');
            if (tbody) {
                tbody.addEventListener('click', function(e) {
                    var btn = e.target.closest('button');
                    if (!btn) {
                        return;
                    }
                    var index = parseInt(btn.getAttribute('data-index'), 10);
                    if (isNaN(index) || index < 0 || index >= currentFields.length) {
                        return;
                    }

                    if (btn.classList.contains('btn-move-up')) {
                        if (index > 0) {
                            var tempUp = currentFields[index];
                            currentFields[index] = currentFields[index - 1];
                            currentFields[index - 1] = tempUp;
                            syncAndRender();
                        }
                    } else if (btn.classList.contains('btn-move-down')) {
                        if (index < currentFields.length - 1) {
                            var tempDown = currentFields[index];
                            currentFields[index] = currentFields[index + 1];
                            currentFields[index + 1] = tempDown;
                            syncAndRender();
                        }
                    } else if (btn.classList.contains('btn-toggle-visibility')) {
                        currentFields[index].enabled = !currentFields[index].enabled;
                        syncAndRender();
                    } else if (btn.classList.contains('btn-edit-field')) {
                        openModal(currentFields[index]);
                    } else if (btn.classList.contains('btn-delete-field')) {
                        var f = currentFields[index];
                        var promptMsg = f.is_standard ?
                            'This is a standard field. Do you want to remove it from this activity?' :
                            'Are you sure you want to delete the custom field "' + f.label + '"?';
                        if (confirm(promptMsg)) {
                            currentFields.splice(index, 1);
                            syncAndRender();
                        }
                    }
                });
            }
        }
    };
});
