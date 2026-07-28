<?php
/**
 * Nextcloud - dashboardlauncher
 *
 * @author DPFPIC
 * @copyright 2026
 */

style('dashboardlauncher', 'style');
script('dashboardlauncher', 'dashboardlauncher-loader');

$displayName = isset($_['displayName']) ? $_['displayName'] : $l->t('User');
$buttons = isset($_['buttons']) ? $_['buttons'] : [];
$siteTitle = isset($_['siteTitle']) ? $_['siteTitle'] : $l->t('My Dashboard');
$welcomeText = isset($_['welcomeText']) ? $_['welcomeText'] : '';
$footerText = isset($_['footerText']) ? $_['footerText'] : '';

$urlGenerator = \OC::$server->get(\OCP\IURLGenerator::class);
?>
<meta charset="utf-8">
<div id="app" style="width: 100% !important; min-height: 100% !important; display: block !important;">
    <div id="app-content" style="margin-left: 0 !important; width: 100% !important;">
        <div id="app-content-wrapper" class="dashboard-launcher-container">

            <div class="dashboard-card">
                <header class="portal-header">
                    <h1><?php p($siteTitle); ?></h1>
                    <p><?php p($welcomeText); ?></p>
                </header>

                <main class="button-grid">
<?php foreach ($buttons as $button): ?>
    <?php
        if (!$button) {
            continue;
        }
        $titre = $button['titre'] ?? '';
        $icone = $button['icone'] ?? '';
        $route = ltrim($button['route'] ?? '', '/');
        $taille = $button['taille'] ?? 'medium';

        $tailleMap = [
            'small'  => '32px',
            'medium' => '48px',
            'large'  => '64px',
            'xlarge' => '96px',
        ];
        $iconSize = $tailleMap[$taille] ?? $tailleMap['medium'];
    ?>
    <a href="../../<?php p($route); ?>" class="nav-button">
<span class="portal-emoji" style="--icon-size: <?php p($iconSize); ?>;">
    <?php if (strpos($icone, 'icon_') === 0): ?>
        <img src="<?php print_unescaped($urlGenerator->linkToRoute('dashboardlauncher.admin.getIcon', ['filename' => $icone])); ?>" alt="" class="button-icon" />
    <?php elseif (strpos($icone, 'lib_') === 0): ?>
        <img src="<?php print_unescaped($urlGenerator->linkToRoute('dashboardlauncher.admin.getLibraryIcon', ['filename' => substr($icone, 4)])); ?>" alt="" class="button-icon" />
    <?php elseif (strpos($icone, '.') !== false): ?>
        <img src="<?php print_unescaped(image_path('dashboardlauncher', $icone)); ?>" alt="" class="button-icon" />
    <?php else: ?>
        <?php print_unescaped($icone); ?>
    <?php endif; ?>
</span>
        <span class="label"><?php p($titre); ?></span>
        <span class="button-spinner"></span>
    </a>
<?php endforeach; ?>
                </main>

                <footer class="portal-footer">
                    <p>
                        <?php p($footerText); ?>
                    </p>
                </footer>
            </div>

        </div>
    </div>
</div>
