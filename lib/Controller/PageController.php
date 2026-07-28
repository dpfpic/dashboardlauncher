<?php
/**
 * Nextcloud - dashboardlauncher
 *
 * @author DPFPIC
 * @copyright 2026
 */

namespace OCA\DashboardLauncher\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IUserSession;
use OCP\IGroupManager;
use OCP\IConfig;
use OCP\IL10N;
use OCA\DashboardLauncher\Service\ButtonService;

class PageController extends Controller {

    public function __construct(
        string $appName,
        IRequest $request,
        private IUserSession $userSession,
        private IGroupManager $groupManager,
        private ButtonService $buttonService,
        private IConfig $config,
        private IL10N $l
    ) {
        parent::__construct($appName, $request);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index() {
        $user = $this->userSession->getUser();
        $displayName = $user !== null ? $user->getDisplayName() : '';
        $isAdmin = $user !== null && $this->groupManager->isAdmin($user->getUID());

        $buttons = $this->buttonService->getAuthorizedButtonsForUser();

        $siteTitle = $this->config->getAppValue($this->appName, 'site_title', $this->l->t('My Dashboard'));
        $welcomeTextRaw = $this->config->getAppValue($this->appName, 'welcome_text', $this->l->t('Hello {displayName}, select a service below to access your tools and shared folders'));
        $footerText = $this->config->getAppValue($this->appName, 'footer_text', $this->l->t('Secure space powered by Nextcloud'));

        $welcomeText = str_replace('{displayName}', $displayName, $welcomeTextRaw);

        \OCP\Util::addTranslations($this->appName);

        return new TemplateResponse('dashboardlauncher', 'main', [
            'displayName' => $displayName,
            'isAdmin'     => $isAdmin,
            'buttons'     => $buttons,
            'siteTitle'   => $siteTitle,
            'welcomeText' => $welcomeText,
            'footerText'  => $footerText,
        ]);
    }
}
