<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../includes/Validator.php';
require_once __DIR__ . '/../includes/AuditLogger.php';
require_once __DIR__ . '/../includes/CsrfMiddleware.php';
require_once __DIR__ . '/../models/StockManager.php';

class DepotController {
    private StockManager $stockManager;
    private AuditLogger  $auditLogger;
    private PDO $db;

    public function __construct(PDO $db) {
        $this->stockManager = new StockManager($db);
        $this->auditLogger  = new AuditLogger($db);
        $this->db = $db;
    }

    public function index(): array {
        if (!hasPermission('articles', 'view')) {
            die("Accès refusé.");
        }
        $depots = $this->stockManager->getAllDepots();
        return [
            'view' => 'depots/index',
            'data' => [
                'depots'     => $depots,
                'csrf_field' => CsrfMiddleware::field(),
            ],
        ];
    }

    public function create(array $data): array {
        if (!hasPermission('articles', 'create')) {
            die("Accès refusé.");
        }
        $error = null;
        $users = $this->getUsers();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();

            $v = new Validator($data);
            $v->required('nom', 'Nom du dépôt')
              ->maxLength('nom', 100, 'Nom du dépôt')
              ->email('email', 'Email');

            if ($v->fails()) {
                $error = $v->getFirstError();
            } else {
                try {
                    $depotId = $this->stockManager->createDepot(
                        trim($data['nom']),
                        trim($data['adresse'] ?? '') ?: null,
                        trim($data['ville'] ?? '') ?: null,
                        ($data['responsable_id'] ?? '') !== '' ? (int)$data['responsable_id'] : null,
                        trim($data['telephone'] ?? '') ?: null,
                        trim($data['email'] ?? '') ?: null
                    );
                    $userId = (int)($_SESSION['user_id'] ?? 0);
                    $this->auditLogger->log('depots', $depotId, 'CREATE', $userId, null, $data);
                    header('Location: index.php?action=depots');
                    exit();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la création du dépôt : " . $e->getMessage();
                    error_log($error);
                }
            }
        }

        return [
            'view' => 'depots/create',
            'data' => [
                'error'      => $error,
                'users'      => $users,
                'csrf_field' => CsrfMiddleware::field(),
            ],
        ];
    }

    public function edit(int $id, array $data = []): array {
        if (!hasPermission('articles', 'edit')) {
            die("Accès refusé.");
        }
        $depot = $this->stockManager->findDepotById($id);
        if (!$depot) {
            return ['view' => 'error', 'data' => ['message' => "Dépôt non trouvé."]];
        }
        $error = null;
        $users = $this->getUsers();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();

            $v = new Validator($data);
            $v->required('nom', 'Nom du dépôt')
              ->maxLength('nom', 100, 'Nom du dépôt')
              ->email('email', 'Email')
              ->inList('statut', ['actif', 'inactif'], 'Statut');

            if ($v->fails()) {
                $error = $v->getFirstError();
            } else {
                try {
                    $this->stockManager->updateDepot(
                        $id,
                        trim($data['nom']),
                        trim($data['adresse'] ?? '') ?: null,
                        trim($data['ville'] ?? '') ?: null,
                        ($data['responsable_id'] ?? '') !== '' ? (int)$data['responsable_id'] : null,
                        trim($data['telephone'] ?? '') ?: null,
                        trim($data['email'] ?? '') ?: null,
                        $data['statut'] ?? 'actif'
                    );
                    $userId = (int)($_SESSION['user_id'] ?? 0);
                    $this->auditLogger->log('depots', $id, 'UPDATE', $userId, $depot, $data);
                    header('Location: index.php?action=depots');
                    exit();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la modification : " . $e->getMessage();
                    error_log($error);
                }
            }
        }

        return [
            'view' => 'depots/edit',
            'data' => [
                'depot'      => $depot,
                'error'      => $error,
                'users'      => $users,
                'csrf_field' => CsrfMiddleware::field(),
            ],
        ];
    }

    private function getUsers(): array {
        try {
            $stmt = $this->db->query("SELECT id_users, username FROM users ORDER BY username ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
