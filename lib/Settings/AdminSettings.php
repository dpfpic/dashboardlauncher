<?php
/**
 * Nextcloud - dashboardlauncher
 *
 * @author DPFPIC
 * @copyright 2026
 */

namespace OCA\DashboardLauncher\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;
use OCP\IGroupManager;
use OCP\Util;
use OCA\DashboardLauncher\Service\ButtonService;

class AdminSettings implements ISettings {

    public function __construct(
        private ButtonService $buttonService,
        private IGroupManager $groupManager
    ) {}

    public function getSection(): string {
        return 'dashboardlauncher';
    }

    public function getForm(): TemplateResponse {
        Util::addTranslations('dashboardlauncher');

        $buttons = [];
        try {
            $buttons = $this->buttonService->getAllButtons();
        } catch (\Throwable $e) {
            $buttons = [];
        }

        $allGroups = [];
        try {
            foreach ($this->groupManager->search('') as $group) {
                $allGroups[] = [
                    'id' => $group->getGID(),
                    'name' => $group->getDisplayName(),
                ];
            }
        } catch (\Throwable $e) {
            $allGroups = [];
        }

        return new TemplateResponse('dashboardlauncher', 'admin_settings', [
            'buttons' => $buttons,
            'allGroups' => $allGroups,
        ]);
    }

    public function getPriority(): int {
        return 50;
    }
}
