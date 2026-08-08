<?php
/**
 * Nextcloud - dashboardlauncher
 *
 * @author DPFPIC
 * @copyright 2026
 */

// Load admin scripts and styles
script('dashboardlauncher', 'admin');
style('dashboardlauncher', 'admin');

// JS translation catalog (Nextcloud's automatic JS l10n injection is unreliable
// inside admin settings forms, so we pass the catalog explicitly via data-attribute)
$dashboardLauncherL10n = [
    'Small' => $l->t('Small'),
    'Medium' => $l->t('Medium'),
    'Large' => $l->t('Large'),
    'Extra large' => $l->t('Extra large'),
    'All Users' => $l->t('All Users'),
    'Yes' => $l->t('Yes'),
    'No' => $l->t('No'),
    'Edit' => $l->t('Edit'),
    'Delete' => $l->t('Delete'),
    'Add a shortcut' => $l->t('Add a shortcut'),
    'Edit shortcut' => $l->t('Edit shortcut'),
    'Error refreshing the list' => $l->t('Error refreshing the list'),
    'Icon upload error: {error}' => $l->t('Icon upload error: {error}'),
    'unknown' => $l->t('unknown'),
    'Icon upload error' => $l->t('Icon upload error'),
    'No icons uploaded yet' => $l->t('No icons uploaded yet'),
    'Delete this icon' => $l->t('Delete this icon'),
    'Permanently delete this icon?' => $l->t('Permanently delete this icon?'),
    'Error deleting icon' => $l->t('Error deleting icon'),
    'Error loading icons' => $l->t('Error loading icons'),
    'Library is empty' => $l->t('Library is empty'),
    'Error loading the library' => $l->t('Error loading the library'),
    'Are you sure you want to delete this shortcut?' => $l->t('Are you sure you want to delete this shortcut?'),
    'Shortcut deleted' => $l->t('Shortcut deleted'),
    'Error deleting the shortcut' => $l->t('Error deleting the shortcut'),
    'HTTP error {status}' => $l->t('HTTP error {status}'),
    'Shortcut saved successfully' => $l->t('Shortcut saved successfully'),
    'Error: {message}' => $l->t('Error: {message}'),
    'Unknown error' => $l->t('Unknown error'),
    'Error loading settings' => $l->t('Error loading settings'),
    'Settings saved' => $l->t('Settings saved'),
    'Error: {error}' => $l->t('Error: {error}'),
    'Error while saving' => $l->t('Error while saving'),
    'Drag to reorder' => $l->t('Drag to reorder'),
	'Order updated' => $l->t('Order updated'),
	'Error saving the new order' => $l->t('Error saving the new order'),
	'Drag rows to reorder' => $l->t('Drag rows to reorder'),
];
?>

<div class="section" id="dashboardlauncher-admin" data-l10n="<?php p(json_encode($dashboardLauncherL10n)); ?>">
    <h2><?php p($l->t('Dashboard Launcher - Administration')); ?></h2>

    <div class="site-settings-panel">
        <h3><?php p($l->t('Dashboard settings')); ?></h3>
        <form id="site-settings-form">
            <p>
                <label for="site-title"><?php p($l->t('Main title')); ?></label>
                <input type="text" id="site-title" name="site_title" class="input-wide" />
            </p>
            <p>
                <label for="welcome-text"><?php p($l->t('Welcome text (use {displayName} for the user\'s name)')); ?></label>
                <input type="text" id="welcome-text" name="welcome_text" class="input-wide" />
            </p>
            <p>
                <label for="footer-text"><?php p($l->t('Footer text')); ?></label>
                <input type="text" id="footer-text" name="footer_text" class="input-wide" />
            </p>
            <div class="form-actions">
                <button type="submit" class="button button-primary"><?php p($l->t('Save settings')); ?></button>
            </div>
        </form>
    </div>

    <div class="dashboardlauncher-header">
        <p class="settings-hint"><?php p($l->t('Manage the shortcut buttons displayed on users\' dashboards.')); ?></p>
        <button id="add-button" class="button button-primary">
            <span class="icon icon-add"></span> <?php p($l->t('Add a new shortcut')); ?>
        </button>
    </div>

    <table class="grid" id="dashboardlauncher-buttons-table">
        <thead>
            <tr>
                <th class="col-drag">
    <span class="reorder-hint" title="<?php p($l->t('Drag rows to reorder')); ?>">↕</span>
</th> 
                <th><?php p($l->t('Order')); ?></th>
                <th><?php p($l->t('Title')); ?></th>
                <th><?php p($l->t('Icon')); ?></th>
                <th><?php p($l->t('Size')); ?></th>
                <th><?php p($l->t('Route / App ID')); ?></th>
                <th><?php p($l->t('Allowed groups')); ?></th>
                <th><?php p($l->t('Active')); ?></th>
                <th><?php p($l->t('Actions')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_['buttons'] as $button): ?>
                <?php
                    // Guard against null elements
                    if (!$button) {
                        continue;
                    }

                    // Extract properties regardless of Entity object or array format
                    $id = is_object($button) ? $button->getId() : ($button['id'] ?? null);
                    $titre = is_object($button) ? $button->getTitre() : ($button['titre'] ?? '');
                    $icone = is_object($button) ? $button->getIcone() : ($button['icone'] ?? '');
                    $route = is_object($button) ? $button->getRoute() : ($button['route'] ?? '');
                    $groupes = is_object($button) ? $button->getGroupes() : ($button['groupes'] ?? '[]');
                    $ordre = is_object($button) ? $button->getOrdre() : ($button['ordre'] ?? 10);
                    $actif = is_object($button) ? $button->getActif() : ($button['actif'] ?? true);
                    $taille = is_object($button) ? $button->getTaille() : ($button['taille'] ?? 'medium');

                    // Process and format allowed groups
                    $decodedGroups = is_string($groupes) ? json_decode($groupes, true) : $groupes;
                    $displayGroups = (is_array($decodedGroups) && !empty($decodedGroups)) ? implode(', ', $decodedGroups) : $l->t('All Users');
                ?>
<tr draggable="true"
    data-id="<?php p($id); ?>"
    data-title="<?php p($titre); ?>"
    data-icon="<?php p($icone); ?>"
    data-route="<?php p($route); ?>"
    data-groups="<?php p(is_array($decodedGroups) ? json_encode($decodedGroups) : '[]'); ?>"
    data-order="<?php p($ordre); ?>"
    data-active="<?php p($actif ? '1' : '0'); ?>"
    data-taille="<?php p($taille); ?>">

    <td class="col-drag">
        <span class="drag-handle" title="<?php p($l->t('Drag to reorder')); ?>">⠿</span>
    </td>
    <td class="col-order"><?php p($ordre); ?></td>
 
                    <td class="col-title"><strong><?php p($titre); ?></strong></td>
<td class="col-icon">
    <?php if (strpos($icone, 'icon_') === 0): ?>
        <img src="<?php print_unescaped(\OC::$server->get(\OCP\IURLGenerator::class)->linkToRoute('dashboardlauncher.admin.getIcon', ['filename' => $icone])); ?>" alt="" class="icon-thumb" />
    <?php elseif (strpos($icone, 'lib_') === 0): ?>
        <img src="<?php print_unescaped(\OC::$server->get(\OCP\IURLGenerator::class)->linkToRoute('dashboardlauncher.admin.getLibraryIcon', ['filename' => substr($icone, 4)])); ?>" alt="" class="icon-thumb" />
    <?php elseif (strpos($icone, '.') !== false): ?>
        <img src="<?php print_unescaped(image_path('dashboardlauncher', $icone)); ?>" alt="" class="icon-thumb" />
    <?php else: ?>
        <span class="icon-thumb-emoji"><?php print_unescaped($icone); ?></span>
    <?php endif; ?>
</td>
<td class="col-taille">
    <?php
        $tailleLabels = [
            'small'  => $l->t('Small'),
            'medium' => $l->t('Medium'),
            'large'  => $l->t('Large'),
            'xlarge' => $l->t('Extra large'),
        ];
        p($tailleLabels[$taille] ?? $l->t('Medium'));
    ?>
</td>
                    <td class="col-route"><code><?php p($route); ?></code></td>
                    <td class="col-groups"><?php p($displayGroups); ?></td>
                    <td class="col-active">
                        <span class="status-badge <?php echo $actif ? 'active' : 'inactive'; ?>">
                            <?php p($actif ? $l->t('Yes') : $l->t('No')); ?>
                        </span>
                    </td>
                    <td class="col-actions">
                        <button class="button edit-button" title="<?php p($l->t('Edit')); ?>"><?php p($l->t('Edit')); ?></button>
                        <button class="butto button-danger delete-button" title="<?php p($l->t('Delete')); ?>"><?php p($l->t('Delete')); ?></button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Form for Adding/Editing Buttons -->
<div id="button-modal" class="modal-dialog hidden" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title"><?php p($l->t('Add a shortcut')); ?></h3>
            <button type="button" id="modal-close-x" class="modal-close-btn" aria-label="<?php p($l->t('Close')); ?>">&times;</button>
        </div>
        <form id="button-form" class="modal-scroll-area">
            <input type="hidden" id="button-id" name="id" value="" />

            <div class="modal-body-grid">

                <!-- Fields column: comes FIRST in the DOM so "Title" is the first
                     field reached when tabbing through the form or reading it
                     with a screen reader. Visually it's still shown on the right
                     (see .modal-column-fields / .modal-column-icon "order" in CSS). -->
                <div class="modal-column modal-column-fields">

                    <fieldset class="form-section">
                        <legend><?php p($l->t('General information')); ?></legend>
                        <p>
                            <label for="button-title"><?php p($l->t('Title')); ?></label>
                            <input type="text" id="button-title" name="titre" class="input-wide" required />
                        </p>
                        <p>
                            <label for="button-route"><?php p($l->t('Route / App ID / URL')); ?></label>
                            <input type="text" id="button-route" name="route" placeholder="e.g. files or dashboardlauncher.page.index" class="input-wide" required />
                        </p>
                    </fieldset>

                    <fieldset class="form-section">
                        <legend><?php p($l->t('Visibility & display')); ?></legend>

                        <p>
                            <label><?php p($l->t('Allowed groups (leave empty for all users)')); ?></label>
                            <div id="groups-checkbox-list" class="groups-chip-list">
                                <?php foreach ($_['allGroups'] as $group): ?>
                                    <?php
                                        // Handles both object (Group), array, and string representations of Nextcloud groups
                                        $groupId = is_object($group) ? $group->getGID() : (is_array($group) ? ($group['id'] ?? $group['gid'] ?? '') : $group);
                                        $groupName = is_object($group) ? $group->getDisplayName() : (is_array($group) ? ($group['name'] ?? $group['gid'] ?? '') : $group);
                                    ?>
                                    <label class="group-chip">
                                        <input type="checkbox" class="group-checkbox" value="<?php p($groupId); ?>">
                                        <span><?php p($groupName); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" id="button-groups" name="groupes" value="[]" />
                        </p>

                        <div class="inline-fields">
                            <p class="order-field">
                                <label for="button-order"><?php p($l->t('Display order')); ?></label>
                                <input type="number" id="button-order" name="ordre" value="10" min="0" />
                            </p>

                            <p class="active-field">
                                <label class="checkbox-label" for="button-active">
                                    <input type="checkbox" id="button-active" name="actif" value="1" checked />
                                    <?php p($l->t('Active')); ?>
                                </label>
                            </p>
                        </div>
                    </fieldset>

                </div>

                <!-- Icon column -->
                <div class="modal-column modal-column-icon">
                    <span class="section-label"><?php p($l->t('Icon')); ?></span>

                    <div class="icon-preview-card">
                        <img id="icon-preview" src="" alt="" />
                    </div>

                    <input type="text" id="button-icon" name="icone"
                           class="icon-filename-display"
                           placeholder="<?php p($l->t('No icon selected')); ?>"
                           aria-label="<?php p($l->t('Selected icon filename')); ?>"
                           required readonly />

                    <div class="icon-actions">
                        <input type="file" id="button-icon-file" class="icon-file-input"
                               accept="image/png,image/jpeg,image/svg+xml,image/webp" />
                        <button type="button" id="browse-icons-btn" class="button"><?php p($l->t('My icons')); ?></button>
                        <button type="button" id="browse-library-btn" class="button"><?php p($l->t('Library')); ?></button>
                    </div>

                    <div id="icon-gallery" class="icon-gallery hidden"></div>
                    <div id="library-gallery" class="icon-gallery hidden"></div>

                    <p class="taille-field">
                        <label for="button-taille"><?php p($l->t('Icon size')); ?></label>
                        <select id="button-taille" name="taille" class="input-wide">
                            <option value="small"><?php p($l->t('Small (32px)')); ?></option>
                            <option value="medium" selected><?php p($l->t('Medium (48px)')); ?></option>
                            <option value="large"><?php p($l->t('Large (64px)')); ?></option>
                            <option value="xlarge"><?php p($l->t('Extra large (96px)')); ?></option>
                        </select>
                    </p>
                </div>

            </div>
        </form>

        <div class="modal-actions">
            <button type="button" id="modal-cancel" class="button"><?php p($l->t('Cancel')); ?></button>
            <button type="submit" id="modal-save" class="button button-primary" form="button-form"><?php p($l->t('Save')); ?></button>
        </div>
    </div>
</div>
