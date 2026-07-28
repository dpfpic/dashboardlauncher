<?php

namespace OCA\DashboardLauncher\Service;

use OCP\IDBConnection;
use OCP\App\IAppManager;
use OCP\IUserSession;
use OCP\IGroupManager;

class ButtonService {

    private const TABLE = 'dashboardlauncher_buttons';

    public function __construct(
        private IDBConnection $db,
        private IAppManager $appManager,
        private IUserSession $userSession,
        private IGroupManager $groupManager
    ) {}

    public function getAuthorizedButtonsForUser(): array {
        $user = $this->userSession->getUser();
        if (!$user) {
            return [];
        }

        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
           ->from(self::TABLE)
           ->where($qb->expr()->eq('actif', $qb->createNamedParameter(true, \PDO::PARAM_BOOL)))
           ->orderBy('ordre', 'ASC');

        $cursor = $qb->executeQuery();
        $buttons = $cursor->fetchAll();
        $cursor->closeCursor();

        if ($this->groupManager->isAdmin($user->getUID())) {
            return $buttons;
        }

        $userGroups = $this->groupManager->getUserGroupIds($user);

        return array_values(array_filter($buttons, function ($button) use ($userGroups) {
            if (empty($button['groupes'])) {
                return true;
            }
            $allowedGroups = json_decode($button['groupes'], true) ?: [];
            if (empty($allowedGroups)) {
                return true;
            }
            return count(array_intersect($allowedGroups, $userGroups)) > 0;
        }));
    }

    public function getCttCompatibleApps(): array {
        $installedApps = $this->appManager->getInstalledApps();
        $compatibleApps = [];

        foreach ($installedApps as $appId) {
            if ($this->appManager->isInstalled($appId)) {
                $appPath = $this->appManager->getAppPath($appId);
                $infoXml = $appPath . '/appinfo/info.xml';

                if (file_exists($infoXml)) {
                    $xml = simplexml_load_file($infoXml);
                    if (isset($xml->ctt_compatible) && (string)$xml->ctt_compatible === 'true') {
                        $compatibleApps[] = [
                            'id' => $appId,
                            'name' => (string)$xml->name,
                            'version' => (string)$xml->version,
                        ];
                    }
                }
            }
        }

        return $compatibleApps;
    }

    public function getAllButtons(): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
           ->from(self::TABLE)
           ->orderBy('ordre', 'ASC');

        $cursor = $qb->executeQuery();
        $buttons = $cursor->fetchAll();
        $cursor->closeCursor();

        return $buttons;
    }

    public function saveButton(array $data): void {
        $qb = $this->db->getQueryBuilder();
        $taille = $data['taille'] ?? 'medium';

        if (isset($data['id']) && !empty($data['id'])) {
            $qb->update(self::TABLE)
               ->set('titre', $qb->createNamedParameter($data['titre']))
               ->set('icone', $qb->createNamedParameter($data['icone']))
               ->set('route', $qb->createNamedParameter($data['route']))
               ->set('ordre', $qb->createNamedParameter((int)$data['ordre']))
               ->set('groupes', $qb->createNamedParameter($data['groupes']))
               ->set('actif', $qb->createNamedParameter((bool)$data['actif'], \PDO::PARAM_BOOL))
               ->set('taille', $qb->createNamedParameter($taille))
               ->where($qb->expr()->eq('id', $qb->createNamedParameter((int)$data['id'])));
        } else {
            $qb->insert(self::TABLE)
               ->values([
                   'titre' => $qb->createNamedParameter($data['titre']),
                   'icone' => $qb->createNamedParameter($data['icone']),
                   'route' => $qb->createNamedParameter($data['route']),
                   'ordre' => $qb->createNamedParameter((int)$data['ordre']),
                   'groupes' => $qb->createNamedParameter($data['groupes']),
                   'actif' => $qb->createNamedParameter((bool)$data['actif'], \PDO::PARAM_BOOL),
                   'taille' => $qb->createNamedParameter($taille),
               ]);
        }

        $qb->executeStatement();
    }

    public function deleteButton(int $id): void {
        $qb = $this->db->getQueryBuilder();
        $qb->delete(self::TABLE)
           ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, \PDO::PARAM_INT)));

        $qb->executeStatement();
    }
}
