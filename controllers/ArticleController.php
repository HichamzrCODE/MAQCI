<?php
require_once __DIR__ . '/../includes/permissions.php'; // Chemin universel et portable
require_once __DIR__ . '/../models/article.php';

class ArticleController {
    private $articleModel;
    private $db;

    public function __construct(PDO $db) {
        $this->articleModel = new article($db);
        $this->db = $db;
    }

    public function index(array $getData = []): array {
        if (!hasPermission('articles', 'view')) {
            die("Accès refusé.");
        }

        // On affiche les 50 premiers au chargement initial pour éviter de saturer le navigateur
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

    // Recherche AJAX serveur (rapide même avec 7000+ articles)
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom_art = trim($data['nom_art'] ?? '');
            $pr = floatval($data['pr'] ?? 0);
            $fournisseur_id = trim($data['fournisseur_id'] ?? '');

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
                    $articleId = $this->articleModel->create($nom_art, $pr, $fournisseur_id, $userId);
                    header('Location: index.php?action=articles');
                    exit();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la création de l'article : " . $e->getMessage();
                    error_log($error);
                }
            }
        }
        return ['view' => 'articles/create', 'data' => ['error' => $error, 'fournisseurs' => $fournisseurs]];
    }

    private function getFournisseurs(): array {
        try {
            $stmt = $this->db->query("SELECT id_fournisseurs, nom_fournisseurs FROM fournisseurs");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des fournisseurs : " . $e->getMessage());
            return [];
        }
    }

    public function delete(int $id): void {
        if (!hasPermission('articles', 'delete')) {
            die("Accès refusé.");
        }

        $articles = $this->articleModel->findById($id);
        if (!$articles) {
            echo "Article non trouvé.";
            return;
        }

        $this->articleModel->delete($id);
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

        if (!$article) {
            return ['view' => 'error', 'data' => ['message' => "Article non trouvé."]];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom_art = trim($data['nom_art'] ?? '');
            $pr = floatval($data['pr'] ?? 0);
            $fournisseur_id = trim($data['fournisseur_id'] ?? '');

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
                    $this->articleModel->update($id, $nom_art, $pr, $fournisseur_id);
                    header('Location: index.php?action=articles');
                    exit();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la mise à jour de l'article : " . $e->getMessage();
                    error_log($error);
                }
            }
        }

        return ['view' => 'articles/edit', 'data' => ['article' => $article, 'error' => $error, 'fournisseurs' => $fournisseurs]];
    }
    
    public function update(int $id, string $nom_art, float $pr, string $fournisseur_id): bool {
        $sql = "UPDATE articles SET nom_art = :nom_art, pr = :pr, fournisseur_id = :fournisseur_id WHERE id_articles = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':nom_art', $nom_art, PDO::PARAM_STR);
        $stmt->bindValue(':pr', $pr, PDO::PARAM_STR);
        $stmt->bindValue(':fournisseur_id', $fournisseur_id, PDO::PARAM_STR);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de l'article : " . $e->getMessage());
            return false;
        }
    }

    public function show(int $id): array {
        if (!hasPermission('articles', 'view')) {
            die("Accès refusé.");
        }

        $articles = $this->articleModel->findById($id);
        if (!$articles) {
            return ['view' => 'error', 'data' => ['message' => "Article non trouvé."]];
        }
        return ['view' => 'articles/show', 'data' => ['article' => $articles]];
    }
}