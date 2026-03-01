<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../models/Depot.php';
require_once __DIR__ . '/../models/Article.php';

class DepotController {
    private $depotModel;
    private $articleModel;
    private $db;

    public function __construct(PDO $db) {
        $this->depotModel = new Depot($db);
        $this->articleModel = new Article($db);
        $this->db = $db;
    }

    // ================================================================
    // INDEX - LISTE DES DÉPÔTS
    // ================================================================

    public function index(array $getData = []): array {
        if (!hasPermission('depots', 'view')) {
            die("Accès refusé.");
        }

        $depots = $this->depotModel->getAll();
        $totalDepots = $this->depotModel->getTotalCount();

        return [
            'view' => 'depots/index',
            'data' => [
                'depots' => $depots,
                'totalDepots' => $totalDepots
            ]
        ];
    }

    // ================================================================
    // CRÉATION
    // ================================================================

    public function create(array $data): array {
        if (!hasPermission('depots', 'create')) {
            die("Accès refusé.");
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($data['nom'] ?? '');
            $localisation = trim($data['localisation'] ?? '');
            $responsable = trim($data['responsable'] ?? '');

            if (empty($nom)) {
                $error = "Le nom du dépôt est obligatoire.";
            }
            if (empty($localisation)) {
                $error = "La localisation est obligatoire.";
            }

            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                $error = "Utilisateur non authentifié.";
            }

            if (!$error) {
                try {
                    $depotData = [
                        'nom' => $nom,
                        'localisation' => $localisation,
                        'responsable' => $responsable,
                        'telephone' => $data['telephone'] ?? null,
                        'email' => $data['email'] ?? null,
                        'notes' => $data['notes'] ?? null
                    ];
                    $this->depotModel->create($depotData, $userId);
                    header('Location: index.php?action=depots');
                    exit();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la création : " . $e->getMessage();
                    error_log($error);
                }
            }
        }

        return [
            'view' => 'depots/create',
            'data' => ['error' => $error]
        ];
    }

    // ================================================================
    // ÉDITION
    // ================================================================

    public function edit(int $id, array $data = []): array {
        if (!hasPermission('depots', 'edit')) {
            die("Accès refusé.");
        }

        $error = null;
        $depot = $this->depotModel->findById($id);

        if (!$depot) {
            return ['view' => 'error', 'data' => ['message' => "Dépôt non trouvé."]];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($data['nom'] ?? '');
            $localisation = trim($data['localisation'] ?? '');
            $responsable = trim($data['responsable'] ?? '');

            if (empty($nom)) {
                $error = "Le nom du dépôt est obligatoire.";
            }
            if (empty($localisation)) {
                $error = "La localisation est obligatoire.";
            }

            if (!$error) {
                try {
                    $userId = $_SESSION['user_id'] ?? null;
                    $depotData = [
                        'nom' => $nom,
                        'localisation' => $localisation,
                        'responsable' => $responsable,
                        'telephone' => $data['telephone'] ?? null,
                        'email' => $data['email'] ?? null,
                        'notes' => $data['notes'] ?? null
                    ];
                    $this->depotModel->update($id, $depotData, $userId);
                    header('Location: index.php?action=depots');
                    exit();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la mise à jour : " . $e->getMessage();
                    error_log($error);
                }
            }
        }

        return [
            'view' => 'depots/edit',
            'data' => [
                'depot' => $depot,
                'error' => $error
            ]
        ];
    }

    // ================================================================
    // SUPPRESSION
    // ================================================================

    public function delete(int $id): void {
        if (!hasPermission('depots', 'delete')) {
            die("Accès refusé.");
        }

        $depot = $this->depotModel->findById($id);
        if (!$depot) {
            echo "Dépôt non trouvé.";
            return;
        }

        if (!$this->depotModel->delete($id)) {
            header('Location: index.php?action=depots&error=impossible_supprimer');
            exit();
        }

        header('Location: index.php?action=depots');
        exit();
    }

    // ================================================================
    // AFFICHAGE DÉTAILLÉ
    // ================================================================

    public function show(int $id): array {
        if (!hasPermission('depots', 'view')) {
            die("Accès refusé.");
        }

        $depot = $this->depotModel->findById($id);
        if (!$depot) {
            return ['view' => 'error', 'data' => ['message' => "Dépôt non trouvé."]];
        }

        // Récupérer tous les stocks dans ce dépôt
        $stocks = $this->depotModel->getAllStocksInDepot($id);

        return [
            'view' => 'depots/show',
            'data' => [
                'depot' => $depot,
                'stocks' => $stocks
            ]
        ];
    }

    // ================================================================
    // RECHERCHE AJAX
    // ================================================================

    public function search(array $data): void {
        $term = trim($data['term'] ?? '');
        header('Content-Type: application/json');

        if ($term === '') {
            $depots = $this->depotModel->getAll();
        } else {
            $depots = $this->depotModel->search($term);
        }

        foreach ($depots as &$depot) {
            $depot['editable'] = hasPermission('depots', 'edit');
            $depot['deletable'] = hasPermission('depots', 'delete');
        }

        echo json_encode($depots);
        exit();
    }
}