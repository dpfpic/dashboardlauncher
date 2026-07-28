<?php

namespace OCA\DashboardLauncher\Controller;

use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCA\DashboardLauncher\Service\ButtonService;

class ButtonApiController extends ApiController {

    public function __construct(
        string $appName,
        IRequest $request,
        private ButtonService $buttonService
    ) {
        parent::__construct($appName, $request);
    }

    /**
     * Save or update a shortcut button
     *
     * @NoCSRFRequired
     */
    public function save(): DataResponse {
        $id = $this->request->getParam('id');
        $titre = trim((string)$this->request->getParam('titre', ''));
        $icone = trim((string)$this->request->getParam('icone', ''));
        $route = trim((string)$this->request->getParam('route', ''));
        $ordre = (int)$this->request->getParam('ordre', 10);
        $actifParam = $this->request->getParam('actif', true);

        // Robust handling for 'groupes' (supports raw array, JSON string, or null)
        $rawGroupes = $this->request->getParam('groupes');
        if (is_array($rawGroupes)) {
            $groupes = json_encode($rawGroupes);
        } elseif (is_string($rawGroupes) && !empty($rawGroupes)) {
            $groupes = $rawGroupes;
        } else {
            $groupes = '[]';
        }

        $actif = ($actifParam === true || $actifParam === 1 || $actifParam === '1' || $actifParam === 'true');
        $idVal = ($id !== null && $id !== '' && $id !== 0) ? (int)$id : null;

        if (empty($titre) || empty($route)) {
            return new DataResponse([
                'status' => 'error',
                'message' => 'Title and Route are required fields.'
            ], 400);
        }

        try {
            $savedButton = $this->buttonService->saveButton(
                $idVal,
                $titre,
                $icone,
                $route,
                $groupes,
                $ordre,
                $actif
            );

            return new DataResponse([
                'status' => 'success',
                'button' => $savedButton
            ]);
        } catch (\Throwable $e) {
            return new DataResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a shortcut button
     *
     * @NoCSRFRequired
     */
    public function delete(int $id): DataResponse {
        try {
            $this->buttonService->deleteButton($id);

            return new DataResponse([
                'status' => 'success'
            ]);
        } catch (\Throwable $e) {
            return new DataResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}