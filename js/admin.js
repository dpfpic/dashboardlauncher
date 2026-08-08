document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('button-modal');
    const form = document.getElementById('button-form');
    const addButton = document.getElementById('add-button');
    const cancelButton = document.getElementById('modal-cancel');
    const modalCloseX = document.getElementById('modal-close-x');
    const modalTitle = document.getElementById('modal-title');
    const groupsHiddenInput = document.getElementById('button-groups');
    const iconFileInput = document.getElementById('button-icon-file');
    const iconTextInput = document.getElementById('button-icon');
    const iconPreview = document.getElementById('icon-preview');
    const browseIconsBtn = document.getElementById('browse-icons-btn');
    const iconGallery = document.getElementById('icon-gallery');
    const browseLibraryBtn = document.getElementById('browse-library-btn');
    const libraryGallery = document.getElementById('library-gallery');
    const tailleSelect = document.getElementById('button-taille');
    const tableBody = document.querySelector('#dashboardlauncher-buttons-table tbody');
    const adminRoot = document.getElementById('dashboardlauncher-admin');

    // Local translation catalog, injected server-side via data-l10n attribute
    // (Nextcloud's automatic JS l10n injection is unreliable inside admin settings forms)
    const l10nData = (() => {
        try {
            return JSON.parse((adminRoot && adminRoot.dataset.l10n) || '{}');
        } catch (e) {
            return {};
        }
    })();

    function tr(key, params) {
        let template = l10nData[key] || key;
        if (params) {
            Object.keys(params).forEach(function (k) {
                template = template.replace('{' + k + '}', params[k]);
            });
        }
        return template;
    }

    if (!modal || !form || !addButton || !tableBody) {
        console.error('[DashboardLauncher] Missing critical DOM elements for admin interface.');
        return;
    }

    function notify(message, isError = false) {
        if (window.OC && window.OC.Notification && typeof window.OC.Notification.showTemporary === 'function') {
            window.OC.Notification.showTemporary(message);
        } else if (window.OCP && window.OCP.Toast) {
            window.OCP.Toast[isError ? 'error' : 'success'](message);
        } else {
            alert(message);
        }
    }

    function showIconPreview(filename) {
        if (!iconPreview) return;
        if (!filename) {
            iconPreview.style.display = 'none';
            return;
        }
        if (filename.startsWith('icon_')) {
            iconPreview.src = OC.generateUrl('/apps/dashboardlauncher/icon/' + filename);
            iconPreview.style.display = 'inline-block';
        } else if (filename.startsWith('lib_')) {
            iconPreview.src = OC.generateUrl('/apps/dashboardlauncher/library-icon/' + filename.substring(4));
            iconPreview.style.display = 'inline-block';
        } else {
            iconPreview.style.display = 'none';
        }
    }

    function updateGroupsPayload() {
        const selected = [];
        document.querySelectorAll('.group-checkbox:checked').forEach(cb => {
            selected.push(cb.value);
        });
        if (groupsHiddenInput) {
            groupsHiddenInput.value = JSON.stringify(selected);
        }
    }

    function setGroupCheckboxes(groupsData) {
        let activeGroups = [];
        try {
            activeGroups = typeof groupsData === 'string' ? JSON.parse(groupsData) : (groupsData || []);
        } catch (e) {
            activeGroups = [];
        }

        document.querySelectorAll('.group-checkbox').forEach(cb => {
            cb.checked = Array.isArray(activeGroups) && activeGroups.includes(cb.value);
        });
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    function renderIconThumb(icone) {
        const safeIcone = escapeHtml(icone);
        if (!icone) return '';
        if (icone.startsWith('icon_')) {
            const url = OC.generateUrl('/apps/dashboardlauncher/icon/' + icone);
            return `<img src="${url}" alt="" class="icon-thumb" />`;
        }
        if (icone.startsWith('lib_')) {
            const url = OC.generateUrl('/apps/dashboardlauncher/library-icon/' + icone.substring(4));
            return `<img src="${url}" alt="" class="icon-thumb" />`;
        }
        if (icone.indexOf('.') !== -1) {
            const url = OC.generateUrl('/apps/dashboardlauncher/img/' + icone);
            return `<img src="${url}" alt="" class="icon-thumb" />`;
        }
        return `<span class="icon-thumb-emoji">${safeIcone}</span>`;
    }

    function renderRow(button) {
        const tailleLabels = {
            small: tr('Small'),
            medium: tr('Medium'),
            large: tr('Large'),
            xlarge: tr('Extra large')
        };
        const tailleLabel = tailleLabels[button.taille] || tr('Medium');
        const groupsRaw = button.groupes;
        let decodedGroups = [];
        try {
            decodedGroups = typeof groupsRaw === 'string' ? JSON.parse(groupsRaw) : (groupsRaw || []);
        } catch (e) {
            decodedGroups = [];
        }
        const displayGroups = (Array.isArray(decodedGroups) && decodedGroups.length > 0)
            ? decodedGroups.join(', ')
            : tr('All Users');

        const actif = button.actif == 1 || button.actif === true;
        const groupsAttr = escapeHtml(JSON.stringify(Array.isArray(decodedGroups) ? decodedGroups : []));

        const tr_ = document.createElement('tr');
        tr_.dataset.id = button.id;
        tr_.dataset.title = button.titre || '';
        tr_.dataset.icon = button.icone || '';
        tr_.dataset.route = button.route || '';
        tr_.dataset.groups = groupsAttr;
        tr_.dataset.order = button.ordre;
        tr_.dataset.active = actif ? '1' : '0';
        tr_.dataset.taille = button.taille || 'medium';

     tr_.setAttribute('draggable', 'true');

    tr_.innerHTML = `
        <td class="col-drag"><span class="drag-handle" title="${escapeHtml(tr('Drag to reorder'))}"></span></td>
        <td class="col-order">${escapeHtml(button.ordre)}</td>
        <td class="col-title"><strong>${escapeHtml(button.titre)}</strong></td>
        <td class="col-icon">${renderIconThumb(button.icone)}</td>
        <td class="col-taille">${escapeHtml(tailleLabel)}</td>
        <td class="col-route"><code>${escapeHtml(button.route)}</code></td>
        <td class="col-groups">${escapeHtml(displayGroups)}</td>
        <td class="col-active">
            <span class="status-badge ${actif ? 'active' : 'inactive'}">${actif ? escapeHtml(tr('Yes')) : escapeHtml(tr('No'))}</span>
        </td>
        <td class="col-actions">
            <button class="button edit-button" title="${escapeHtml(tr('Edit'))}">${escapeHtml(tr('Edit'))}</button>
            <button class="button button-danger delete-button" title="${escapeHtml(tr('Delete'))}">${escapeHtml(tr('Delete'))}</button>
        </td>
    `;
    return tr_;
}

    function renderButtonsTable(buttons) {
        tableBody.innerHTML = '';
        buttons.forEach(button => {
            tableBody.appendChild(renderRow(button));
        });
    }

    function refreshButtonsTable() {
        return fetch(OC.generateUrl('/apps/dashboardlauncher/api/admin/buttons'), {
            method: 'GET',
            headers: { 'requesttoken': OC.requestToken }
        })
        .then(r => r.json())
        .then(data => {
            if (Array.isArray(data)) {
                renderButtonsTable(data);
            }
        })
        .catch(() => {
            notify(tr('Error refreshing the list'), true);
        });
    }

    if (iconFileInput) {
        iconFileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('icon', file);

            fetch(OCgenerateUrl('/apps/dashboardlauncher/api/admin/icon'), {
                method: 'POST',
                headers: { 'requesttoken': OC.requestToken },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.status === 'success') {
                    iconTextInput.value = data.filename;
                    showIconPreview(data.filename);
                } else {
                    notify(tr('Icon upload error: {error}', { error: data.error || tr('unknown') }), true);
                }
            })
            .catch(() => notify(tr('Icon upload error'), true));
        });
    }

    if (browseIconsBtn) {
        browseIconsBtn.addEventListener('click', function () {
            if (!iconGallery.classList.contains('hidden')) {
                iconGallery.classList.add('hidden');
                return;
            }
            if (libraryGallery) libraryGallery.classList.add('hidden');

            fetch(OC.generateUrl('/apps/dashboardlauncher/api/admin/icons'), {
                method: 'GET',
                headers: { 'requesttoken': OC.requestToken }
            })
            .then(r => r.json())
            .then(data => {
                if (!Array.isArray(data)) return;
                iconGallery.innerHTML = '';

                if (data.length === 0) {
                    iconGallery.innerHTML = `<p class="icon-gallery-empty">${escapeHtml(tr('No icons uploaded yet'))}</p>`;
                }

                data.forEach(filename => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'icon-gallery-item';

                    const thumb = document.createElement('img');
                    thumb.src = OC.generateUrl('/apps/dashboardlauncher/icon/' + filename);
                    thumb.alt = filename;
                    thumb.title = filename;
                    thumb.className = 'icon-gallery-thumb';
                    thumb.addEventListener('click', function () {
                        iconTextInput.value = filename;
                        showIconPreview(filename);
                        iconGallery.classList.add('hidden');
                    });

                    const delBtn = document.createElement('button');
                    delBtn.type = 'button';
                    delBtn.className = 'icon-gallery-delete';
                    delBtn.innerHTML = '&times;';
                    delBtn.title = tr('Delete this icon');
                    delBtn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        if (!confirm(tr('Permanently delete this icon?'))) return;

                        fetch(OC.generateUrl('/apps/dashboardlauncher/api/admin/icon/' + filename), {
                            method: 'DELETE',
                            headers: { 'requesttoken': OC.requestToken }
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data && data.status === 'success') {
                                wrapper.remove();
                            } else {
                                notify(tr('Error deleting icon'), true);
                            }
                        })
                        .catch(() => notify(tr('Error deleting icon'), true));
                    });

                    wrapper.appendChild(thumb);
                    wrapper.appendChild(delBtn);
                    iconGallery.appendChild(wrapper);
                });

                iconGallery.classList.remove('hidden');
            })
            .catch(() => notify(tr('Error loading icons'), true));
        });
    }

    if (browseLibraryBtn) {
        browseLibraryBtn.addEventListener('click', function () {
            if (!libraryGallery.classList.contains('hidden')) {
                libraryGallery.classList.add('hidden');
                return;
            }
            if (iconGallery) iconGallery.classList.add('hidden');

            fetch(OC.generateUrl('/apps/dashboardlauncher/api/admin/library-icons'), {
                method: 'GET',
                headers: { 'requesttoken': OC.requestToken }
            })
            .then(r => r.json())
            .then(data => {
                if (!Array.isArray(data)) return;
                libraryGallery.innerHTML = '';

                if (data.length === 0) {
                    libraryGallery.innerHTML = `<p class="icon-gallery-empty">${escapeHtml(tr('Library is empty'))}</p>`;
                }

                data.forEach(filename => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'icon-gallery-item';

                    const thumb = document.createElement('img');
                    thumb.src = OC.generateUrl('/apps/dashboardlauncher/library-icon/' + filename);
                    thumb.alt = filename;
                    thumb.title = filename;
                    thumb.className = 'icon-gallery-thumb';
                    thumb.addEventListener('click', function () {
                        iconTextInput.value = 'lib_' + filename;
                        iconPreview.src = OC.generateUrl('/apps/dashboardlauncher/library-icon/' + filename);
                        iconPreview.style.display = 'inline-block';
                        libraryGallery.classList.add('hidden');
                    });

                    wrapper.appendChild(thumb);
                    libraryGallery.appendChild(wrapper);
                });

                libraryGallery.classList.remove('hidden');
            })
            .catch(() => notify(tr('Error loading the library'), true));
        });
    }

    addButton.addEventListener('click', function () {
        modalTitle.textContent = tr('Add a shortcut');
        form.reset();
        document.getElementById('button-id').value = '';
        setGroupCheckboxes([]);
        showIconPreview('');
        if (tailleSelect) tailleSelect.value = 'medium';
        modal.classList.remove('hidden');
    });

    cancelButton.addEventListener('click', function () {
        modal.classList.add('hidden');
    });

    if (modalCloseX) {
        modalCloseX.addEventListener('click', function () {
            modal.classList.add('hidden');
        });
    }

    function openEditModal(row) {
    modalTitle.textContent = tr('Edit shortcut');
    document.getElementById('button-id').value = row.dataset.id || '';
    document.getElementById('button-title').value = row.dataset.title || '';
    document.getElementById('button-icon').value = row.dataset.icon || '';
    document.getElementById('button-route').value = row.dataset.route || '';
    document.getElementById('button-order').value = row.dataset.order || '10';
    document.getElementById('button-active').checked = row.dataset.active === '1';
    if (tailleSelect) tailleSelect.value = row.dataset.taille || 'medium';

    setGroupCheckboxes(row.dataset.groups || '[]');
    showIconPreview(row.dataset.icon || '');

    modal.classList.remove('hidden');
}

tableBody.addEventListener('click', function (e) {
    const editBtn = e.target.closest('.edit-button');
    const deleteBtn = e.target.closest('.delete-button');
    const dragHandle = e.target.closest('.drag-handle');
    const row = e.target.closest('tr');

    if (!row) return;

    // Ignore les clics sur la poignée de drag : ne doit pas ouvrir le modal
    if (dragHandle) return;

    if (deleteBtn) {
        const id = row.dataset.id;

        if (confirm(tr('Are you sure you want to delete this shortcut?'))) {
            const url = OC.generateUrl(`/apps/dashboardlauncher/api/admin/button/${id}`);

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'requesttoken': OC.requestToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.status === 'success') {
                    notify(tr('Shortcut deleted'));
                    refreshButtonsTable();
                } else {
                    notify(tr('Error deleting the shortcut'), true);
                }
            })
            .catch(error => {
                console.error('[DashboardLauncher] Error deleting:', error);
                notify(tr('Error deleting the shortcut'), true);
            });
        }
        return;
    }

    // Clic sur "Edit" OU n'importe où ailleurs sur la ligne (hors poignée/delete) : ouvre le modal
    openEditModal(row);
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        updateGroupsPayload();

        const idVal = document.getElementById('button-id').value;

        const payload = {
            id: idVal ? parseInt(idVal, 10) : null,
            titre: document.getElementById('button-title').value,
            icone: document.getElementById('button-icon').value,
            route: document.getElementById('button-route').value,
            groupes: groupsHiddenInput ? groupsHiddenInput.value : '[]',
            ordre: parseInt(document.getElementById('button-order').value, 10) || 10,
            actif: document.getElementById('button-active').checked ? 1 : 0,
            taille: tailleSelect ? tailleSelect.value : 'medium'
        };

        const url = OC.generateUrl('/apps/dashboardlauncher/api/admin/button');

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'requesttoken': OC.requestToken
            },
            body: JSON.stringify(payload)
        })
        .then(async response => {
            const data = await response.json().catch(() => null);
            if (!response.ok) {
                const errorMsg = (data && data.message) ? data.message : tr('HTTP error {status}', { status: response.status });
                throw new Error(errorMsg);
            }
            return data;
        })
        .then(data => {
            if (data && data.status === 'success') {
                notify(tr('Shortcut saved successfully'));
                modal.classList.add('hidden');
                refreshButtonsTable();
            } else {
                notify(tr('Error: {message}', { message: (data && data.message) || tr('Unknown error') }), true);
            }
        })
        .catch(error => {
            console.error('[DashboardLauncher] Error saving shortcut:', error);
            notify(tr('Error: {message}', { message: error.message }), true);
        });
    });

    const siteSettingsForm = document.getElementById('site-settings-form');

    if (siteSettingsForm) {
        fetch(OC.generateUrl('/apps/dashboardlauncher/api/admin/site-settings'), {
            method: 'GET',
            headers: { 'requesttoken': OC.requestToken }
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('site-title').value = data.site_title || '';
            document.getElementById('welcome-text').value = data.welcome_text || '';
            document.getElementById('footer-text').value = data.footer_text || '';
        })
        .catch(() => notify(tr('Error loading settings'), true));

        siteSettingsForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const payload = {
                site_title: document.getElementById('site-title').value,
                welcome_text: document.getElementById('welcome-text').value,
                footer_text: document.getElementById('footer-text').value
            };

            fetch(OC.generateUrl('/apps/dashboardlauncher/api/admin/site-settings'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'requesttoken': OC.requestToken
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.status === 'success') {
                    notify(tr('Settings saved'));
                } else {
                    notify(tr('Error: {error}', { error: data.error || tr('unknown') }), true);
                }
            })
            .catch(() => notify(tr('Error while saving'), true));
        });
    }


// ==========================================================================
// Drag & drop reordering
// ==========================================================================

let dragSrcRow = null;

tableBody.addEventListener('dragstart', function (e) {
    const row = e.target.closest('tr');
    if (!row || !row.closest('#dashboardlauncher-buttons-table tbody')) return;
    dragSrcRow = row;
    e.dataTransfer.effectAllowed = 'move';
    // Certains navigateurs exigent des données pour autoriser le drag
    e.dataTransfer.setData('text/plain', row.dataset.id || '');
    row.classList.add('dragging');
});

tableBody.addEventListener('dragend', function (e) {
    const row = e.target.closest('tr');
    if (row) row.classList.remove('dragging');
    tableBody.querySelectorAll('tr').forEach(r => {
        r.classList.remove('drag-over-top', 'drag-over-bottom');
    });
    dragSrcRow = null;
});

tableBody.addEventListener('dragover', function (e) {
    if (!dragSrcRow) return;
    const row = e.target.closest('tr');
    if (!row || row === dragSrcRow) return;
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';

    const rect = row.getBoundingClientRect();
    const isAfter = (e.clientY - rect.top) > (rect.height / 2);

    tableBody.querySelectorAll('tr').forEach(r => {
        r.classList.remove('drag-over-top', 'drag-over-bottom');
    });
    row.classList.add(isAfter ? 'drag-over-bottom' : 'drag-over-top');
});

tableBody.addEventListener('drop', function (e) {
    if (!dragSrcRow) return;
    const row = e.target.closest('tr');
    if (!row || row === dragSrcRow) return;
    e.preventDefault();

    const rect = row.getBoundingClientRect();
    const isAfter = (e.clientY - rect.top) > (rect.height / 2);

    if (isAfter) {
        row.parentNode.insertBefore(dragSrcRow, row.nextSibling);
    } else {
        row.parentNode.insertBefore(dragSrcRow, row);
    }

    row.classList.remove('drag-over-top', 'drag-over-bottom');
    persistNewOrder();
});

function persistNewOrder() {
    const rows = Array.from(tableBody.querySelectorAll('tr'));
    const updates = [];

    rows.forEach((row, index) => {
        const newOrdre = (index + 1) * 10;
        if (parseInt(row.dataset.order, 10) !== newOrdre) {
            row.dataset.order = String(newOrdre);
            const orderCell = row.querySelector('.col-order');
            if (orderCell) orderCell.textContent = newOrdre;
            updates.push(row);
        }
    });

    if (updates.length === 0) return;

    const savePromises = updates.map(row => {
        let groupes = '[]';
        try {
            groupes = JSON.stringify(JSON.parse(row.dataset.groups || '[]'));
        } catch (e) {
            groupes = '[]';
        }

        const payload = {
            id: parseInt(row.dataset.id, 10),
            titre: row.dataset.title || '',
            icone: row.dataset.icon || '',
            route: row.dataset.route || '',
            groupes: groupes,
            ordre: parseInt(row.dataset.order, 10),
            actif: row.dataset.active === '1' ? 1 : 0,
            taille: row.dataset.taille || 'medium'
        };

        return fetch(OC.generateUrl('/apps/dashboardlauncher/api/admin/button'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'requesttoken': OC.requestToken
            },
            body: JSON.stringify(payload)
        }).then(r => r.json());
    });

    Promise.all(savePromises)
        .then(results => {
            const allOk = results.every(r => r && r.status === 'success');
            if (allOk) {
                notify(tr('Order updated'));
            } else {
                notify(tr('Error saving the new order'), true);
            }
        })
        .catch(() => notify(tr('Error saving the new order'), true));
}
});