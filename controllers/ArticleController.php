<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../models/article.php';

class ArticleController {
    private $articleModel;
    private $db;

    public function __construct(PDO $db) {
        $this->articleModel = new Article($db);
        $this->db = $db;
    }

    public function index(array $getData = []): array {
        if (!hasPermission('articles', 'view')) {
            die("Accès refusé.");
        }

        $articles = $this->articleModel->getLimited(50);
        $totalArticles = $this->articleModel->getTotalCount();

        return [
            'view' => 'articles/index',
            'data' => [
                'articles' => $articles,
                'totalArticles' => $totalArticles
            ]
        ];
    }

    public function search(array $data): void {
        $term = trim($data['term'] ?? '');
        header('Content-Type: application/json');
        if ($term === '') {
            $articles = $this->articleModel->getLimited(50);
        } else {
            $articles = $this->articleModel->searchFull($term, 50);
        }
        foreach ($articles as &$article) {
            $article['editable'] = hasPermission('articles', 'edit');
            $article['deletable'] = hasPermission('articles', 'delete');
        }
        echo json_encode($articles);
        exit();
    }

    public function create(array $data): array {
        if (!hasPermission('articles', 'create')) {
            die("Accès refusé.");
        }

        $error = null;
        $fournisseurs = $this->getFournisseurs();
        $categories = $this->getCategories();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom_art = trim($data['nom_art'] ?? '');
            $pr = floatval($data['pr'] ?? 0);
            $prix_vente = floatval($data['prix_vente'] ?? 0);
            $fournisseur_id = trim($data['fournisseur_id'] ?? '');
            $sku = trim($data['sku'] ?? '');
            $categorie_id = $data['categorie_id'] ?? null;

            if (empty($nom_art)) {
                $error = "Le nom de l'article est obligatoire.";
            }
            if ($pr <= 0) {
                $error = "Le prix de revient doit être supérieur à zéro.";
            }
            if (empty($fournisseur_id)) {
                $error = "Veuillez sélectionner un fournisseur.";
            }
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                $error = "Utilisateur non authentifié.";
            }
            if (!$error) {
                try {
                    $articleData = [
                        'nom_art' => $nom_art,
                        'sku' => $sku ?: null,
                        'pr' => $pr,
                        'prix_vente' => $prix_vente,
                        'fournisseur_id' => $fournisseur_id,
                        'categorie_id' => $categorie_id,
                        'statut' => 'actif'
                    ];
                    $articleId = $this->articleModel->create($articleData, $userId);
                    header('Location: index.php?action=articles');
                    exit();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la création de l'article : " . $e->getMessage();
                    error_log($error);
                }
            }
        }
        return [
            'view' => 'articles/create',
            'data' => [
                'error' => $error,
                'fournisseurs' => $fournisseurs,
                'categories' => $categories
            ]
        ];
    }

    private function getFournisseurs(): array {
        try {
            $stmt = $this->db->query("SELECT id_fournisseurs, nom_fournisseurs FROM fournisseurs ORDER BY nom_fournisseurs");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur récupération fournisseurs : " . $e->getMessage());
            return [];
        }
    }

    private function getCategories(): array {
        try {
            $stmt = $this->db->query("SELECT id, nom FROM categories ORDER BY nom");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur récupération catégories : " . $e->getMessage());
            return [];
        }
    }

    public function delete(int $id): void {
        if (!hasPermission('articles', 'delete')) {
            die("Accès refusé.");
        }

        $article = $this->articleModel->findById($id);
        if (!$article) {
            echo "Article non trouvé.";
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        $this->articleModel->softDelete($id, $userId);
        header('Location: index.php?action=articles');
        exit();
    }

    public function edit(int $id, array $data = []): array {
        if (!hasPermission('articles', 'edit')) {
            die("Accès refusé.");
        }

        $error = null;
        $article = $this->articleModel->findById($id);
        $fournisseurs = $this->getFournisseurs();
        $categories = $this->getCategories();

        if (!$article) {
            return ['view' => 'error', 'data' => ['message' => "Article non trouvé."]];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom_art = trim($data['nom_art'] ?? '');
            $pr = floatval($data['pr'] ?? 0);
            $prix_vente = floatval($data['prix_vente'] ?? 0);
            $fournisseur_id = trim($data['fournisseur_id'] ?? '');
            $sku = trim($data['sku'] ?? '');
            $categorie_id = $data['categorie_id'] ?? null;

            if (empty($nom_art)) {
                $error = "Le nom de l'article est obligatoire.";
            }
            if ($pr <= 0) {
                $error = "Le prix de revient doit être supérieur à zéro.";
            }
            if (empty($fournisseur_id)) {
                $error = "Veuillez sélectionner un fournisseur.";
            }
            if (!$error) {
                try {
                    $userId = $_SESSION['user_id'] ?? null;
                    $articleData = [
                        'nom_art' => $nom_art,
                        'sku' => $sku ?: null,
                        'pr' => $pr,
                        'prix_vente' => $prix_vente,
                        'fournisseur_id' => $fournisseur_id,
                        'categorie_id' => $categorie_id,
                        'statut' => $article['statut'] ?? 'actif'
                    ];
                    $this->articleModel->update($id, $articleData, $userId);
                    header('Location: index.php?action=articles');
                    exit();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la mise à jour : " . $e->getMessage();
                    error_log($error);
                }
            }
        }

        return [
            'view' => 'articles/edit',
            'data' => [
                'article' => $article,
                'error' => $error,
                'fournisseurs' => $fournisseurs,
                'categories' => $categories
            ]
        ];
    }

    public function show(int $id): array {
        if (!hasPermission('articles', 'view')) {
            die("Accès refusé.");
        }

        $article = $this->articleModel->findById($id);
        if (!$article) {
            return ['view' => 'error', 'data' => ['message' => "Article non trouvé."]];
        }
        return ['view' => 'articles/show', 'data' => ['article' => $article]];
    }

    // ============================================================
    // EXPORT CSV (temporaire - à améliorer en Excel plus tard)
    // ============================================================

    public function exportCSV(): void {
        if (!hasPermission('articles', 'view')) {
            die("Accès refusé.");
        }

        try {
            $articles = $this->articleModel->getAll();

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="articles_' . date('Y-m-d') . '.csv"');

            $output = fopen('php://output', 'w');
            
            // BOM UTF-8 pour Excel
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // En-têtes
            fputcsv($output, [
                'ID',
                'SKU',
                'Nom Article',
                'Prix Revient',
                'Prix Vente',
                'Quantité Total',
                'Statut',
                'Unité Mesure',
                'Stock Min',
                'Stock Max',
                'Poids (kg)',
                'Couleur',
                'Catégorie',
                'Fournisseur',
                'Notes'
            ], ';');

            // Données
            foreach ($articles as $article) {
                fputcsv($output, [
                    $article['id_articles'],
                    $article['sku'] ?? '',
                    $article['nom_art'],
                    $article['pr'] ?? 0,
                    $article['prix_vente'] ?? 0,
                    $article['quantite_totale'] ?? 0,
                    $article['statut'] ?? 'actif',
                    $article['unite_mesure'] ?? 'Piece',
                    $article['stock_minimal'] ?? 0,
                    $article['stock_maximal'] ?? 0,
                    $article['poids_kg'] ?? '',
                    $article['couleur'] ?? '',
                    $article['nom_categorie'] ?? '',
                    $article['nom_fournisseurs'] ?? '',
                    $article['notes_internes'] ?? ''
                ], ';');
            }

            fclose($output);
            exit();
        } catch (Exception $e) {
            error_log("Erreur export CSV : " . $e->getMessage());
            die("Erreur lors de l'export.");
        }
    }

    // ============================================================
    // IMPORT CSV (basique - à améliorer)
    // ============================================================

    public function importCSV(array $files): array {
        if (!hasPermission('articles', 'create')) {
            die("Accès refusé.");
        }

        $error = null;
        $imported = 0;
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            $error = "Utilisateur non authentifié.";
            return ['view' => 'articles/import', 'data' => ['error' => $error]];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($files['csv_file'])) {
            if ($files['csv_file']['error'] !== UPLOAD_ERR_OK) {
                $error = "Erreur lors du téléchargement du fichier.";
            } else {
                try {
                    $file = fopen($files['csv_file']['tmp_name'], 'r');
                    
                    // Ignorer BOM si présent
                    $bom = fread($file, 3);
                    if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
                        rewind($file);
                    }

                    // Ignorer l'en-tête
                    fgetcsv($file, 1000, ';');

                    while (($row = fgetcsv($file, 1000, ';')) !== false) {
                        if (count($row) < 3 || empty($row[1])) continue;

                        $articleData = [
                            'nom_art' => $row[2] ?? '',
                            'sku' => $row[1] ?? null,
                            'pr' => floatval($row[3] ?? 0),
                            'prix_vente' => floatval($row[4] ?? 0),
                            'fournisseur_id' => 1, // À adapter selon import
                            'quantite_totale' => intval($row[5] ?? 0),
                            'statut' => $row[6] ?? 'actif',
                            'unite_mesure' => $row[7] ?? 'Piece',
                            'stock_minimal' => intval($row[8] ?? 0),
                            'stock_maximal' => intval($row[9] ?? 0),
                            'poids_kg' => $row[10] ?? null,
                            'couleur' => $row[11] ?? null,
                            'notes_internes' => $row[14] ?? null
                        ];

                        try {
                            $this->articleModel->create($articleData, $userId);
                            $imported++;
                        } catch (PDOException $e) {
                            error_log("Erreur import ligne : " . $e->getMessage());
                        }
                    }

                    fclose($file);
                } catch (Exception $e) {
                    $error = "Erreur lors du traitement du fichier : " . $e->getMessage();
                    error_log($error);
                }
            }
        }

        return [
            'view' => 'articles/import',
            'data' => [
                'error' => $error,
                'imported' => $imported
            ]
        ];
    }
}