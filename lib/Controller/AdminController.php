<?php
/**
 * Nextcloud - dashboardlauncher
 *
 * @author DPFPIC
 * @copyright 2026
 */

namespace OCA\DashboardLauncher\Controller;

use OCA\DashboardLauncher\Service\ButtonService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\Attribute\AdminRequired;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\Files\AppData\IAppDataFactory;
use OCP\Files\IAppData;
use OCP\Files\NotFoundException;
use OCP\IRequest;
use OCP\IConfig;
use OCP\IL10N;
use OCP\App\IAppManager;

class AdminController extends Controller {

    private IAppData $appData;

    public function __construct(
        string $appName,
        IRequest $request,
        private ButtonService $buttonService,
        IAppDataFactory $appDataFactory,
        private IConfig $config,
        private IAppManager $appManager,
        private IL10N $l
    ) {
        parent::__construct($appName, $request);
        $this->appData = $appDataFactory->get($appName);
    }

    private function getIconFolder() {
        try {
            return $this->appData->getFolder('icons');
        } catch (NotFoundException $e) {
            return $this->appData->newFolder('icons');
        }
    }

    #[AdminRequired]
    public function saveButton(): DataResponse {
        try {
            $params = $this->request->getParams();

            $actifVal = $params['actif'] ?? true;
            $actifBool = filter_var($actifVal, FILTER_VALIDATE_BOOLEAN);

            $groupes = $params['groupes'] ?? '[]';
            if (is_array($groupes)) {
                $groupes = json_encode($groupes);
            }

            $data = [
                'id'      => !empty($params['id']) ? (int)$params['id'] : null,
                'titre'   => (string)($params['titre'] ?? ''),
                'icone'   => (string)($params['icone'] ?? 'icon-link'),
                'route'   => (string)($params['route'] ?? ''),
                'ordre'   => (int)($params['ordre'] ?? 10),
                'groupes' => $groupes,
                'actif'   => $actifBool,
                'taille'  => (string)($params['taille'] ?? 'medium'),
            ];

            $this->buttonService->saveButton($data);

            return new DataResponse(['status' => 'success'], 200);
        } catch (\Throwable $e) {
            return new DataResponse([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    #[AdminRequired]
    public function listLibraryIcons(): DataResponse {
        try {
            $appPath = $this->appManager->getAppPath($this->appName);
            $libraryPath = $appPath . '/img/library';

            if (!is_dir($libraryPath)) {
                return new DataResponse([]);
            }

            $files = array_values(array_diff(scandir($libraryPath), ['.', '..']));
            sort($files);

            return new DataResponse($files);
        } catch (\Throwable $e) {
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[AdminRequired]
    public function listButtons(): DataResponse {
        try {
            $buttons = $this->buttonService->getAllButtons();
            return new DataResponse($buttons);
        } catch (\Throwable $e) {
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[AdminRequired]
    public function getSiteSettings(): DataResponse {
        return new DataResponse([
            'site_title'   => $this->config->getAppValue($this->appName, 'site_title', $this->l->t('My Dashboard')),
            'welcome_text' => $this->config->getAppValue($this->appName, 'welcome_text', $this->l->t('Hello {displayName}, select a service below to access your tools and shared folders')),
            'footer_text'  => $this->config->getAppValue($this->appName, 'footer_text', $this->l->t('Secure space powered by Nextcloud')),
        ]);
    }

    #[AdminRequired]
    public function saveSiteSettings(): DataResponse {
        try {
            $params = $this->request->getParams();

            $this->config->setAppValue($this->appName, 'site_title', (string)($params['site_title'] ?? ''));
            $this->config->setAppValue($this->appName, 'welcome_text', (string)($params['welcome_text'] ?? ''));
            $this->config->setAppValue($this->appName, 'footer_text', (string)($params['footer_text'] ?? ''));

            return new DataResponse(['status' => 'success']);
        } catch (\Throwable $e) {
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[AdminRequired]
    public function deleteButton(int $id): DataResponse {
        try {
            $this->buttonService->deleteButton($id);
            return new DataResponse(['status' => 'success']);
        } catch (\Throwable $e) {
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[AdminRequired]
    public function uploadIcon(): DataResponse {
        try {
            $file = $this->request->getUploadedFile('icon');

            if ($file === null || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
                return new DataResponse(['error' => $this->l->t('No file received')], 400);
            }

            $allowedTypes = ['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp'];
            $mime = mime_content_type($file['tmp_name']);

            if (!in_array($mime, $allowedTypes, true)) {
                return new DataResponse(['error' => $this->l->t('File type not allowed')], 400);
            }

            $content = file_get_contents($file['tmp_name']);
            $hash = substr(sha1($content), 0, 16);

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'icon_' . $hash . '.' . $ext;

            $folder = $this->getIconFolder();

            try {
                $folder->getFile($filename);
            } catch (NotFoundException $e) {
                $folder->newFile($filename, $content);
            }

            return new DataResponse(['status' => 'success', 'filename' => $filename]);
        } catch (\Throwable $e) {
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[AdminRequired]
    public function listIcons(): DataResponse {
        try {
            $folder = $this->getIconFolder();
            $files = $folder->getDirectoryListing();
            $names = array_map(fn($f) => $f->getName(), $files);
            sort($names);
            return new DataResponse(array_values($names));
        } catch (\Throwable $e) {
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[AdminRequired]
    public function deleteIcon(string $filename): DataResponse {
        try {
            $folder = $this->getIconFolder();
            $file = $folder->getFile($filename);
            $file->delete();
            return new DataResponse(['status' => 'success']);
        } catch (NotFoundException $e) {
            return new DataResponse(['status' => 'success']);
        } catch (\Throwable $e) {
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function getIcon(string $filename): DataDisplayResponse {
        try {
            $folder = $this->getIconFolder();
            $file = $folder->getFile($filename);

            $response = new DataDisplayResponse(
                $file->getContent(),
                200,
                ['Content-Type' => $file->getMimeType()]
            );
            $response->cacheFor(3600);
            return $response;
        } catch (\Throwable $e) {
            return new DataDisplayResponse('', 404);
        }
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function getLibraryIcon(string $filename): DataDisplayResponse {
        try {
            $appPath = $this->appManager->getAppPath($this->appName);
            $filePath = $appPath . '/img/library/' . basename($filename);

            if (!file_exists($filePath)) {
                return new DataDisplayResponse('', 404);
            }

            $mime = mime_content_type($filePath);
            $response = new DataDisplayResponse(file_get_contents($filePath), 200, ['Content-Type' => $mime]);
            $response->cacheFor(3600);
            return $response;
        } catch (\Throwable $e) {
            return new DataDisplayResponse('', 404);
        }
    }
}
