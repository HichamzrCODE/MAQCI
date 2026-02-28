<?php

class Article {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getTotalCount(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM articles");
        return $stmt->fetchColumn();
    }

    public function getLimited($limit = 50): array {
        $stmt = $this->db->prepare(
            "SELECT articles.*, fournisseurs.nom_fournisseurs
             FROM articles
             INNER JOIN fournisseurs ON articles.fournisseur_id = fournisseurs.id_fournisseurs
             ORDER BY articles.nom_art ASC LIMIT ?"
        );
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchFull(string $term, $limit = 50): array {
        $searchTerm = '%' . $term . '%';
        $stmt = $this->db->prepare(
            "SELECT articles.*, fournisseurs.nom_fournisseurs
             FROM articles
             INNER JOIN fournisseurs ON articles.fournisseur_id = fournisseurs.id_fournisseurs
             WHERE articles.nom_art LIKE ? OR fournisseurs.nom_fournisseurs LIKE ?
             ORDER BY articles.nom_art ASC LIMIT ?"
        );
        $stmt->bindValue(1, $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(2, $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(3, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Jointure fournisseurs
    public function getAll(): array {
        $stmt = $this->db->query(
            "SELECT articles.*, fournisseurs.nom_fournisseurs
             FROM articles
             INNER JOIN fournisseurs ON articles.fournisseur_id = fournisseurs.id_fournisseurs
             ORDER BY articles.nom_art ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(string $nom_art, float $pr, string $fournisseur_id, int $userId): int {
        $stmt = $this->db->prepare("INSERT INTO articles (nom_art, pr, fournisseur_id, created_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom_art, $pr, $fournisseur_id, $userId]);
        return (int)$this->db->lastInsertId();
    }

    // Jointure fournisseurs aussi pour l’affichage détaillé
    public function findById(int $id_articles): ?array {
        $stmt = $this->db->prepare(
            "SELECT articles.*, fournisseurs.nom_fournisseurs
             FROM articles
             INNER JOIN fournisseurs ON articles.fournisseur_id = fournisseurs.id_fournisseurs
             WHERE articles.id_articles = ?"
        );
        $stmt->execute([$id_articles]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);
        return $article ?: null;
    }

    public function update(int $id, string $nom_art, float $pr, string $fournisseur_id): void {
        $stmt = $this->db->prepare("UPDATE articles SET nom_art = ?, pr = ?, fournisseur_id = ? WHERE id_articles = ? ");
        $stmt->execute([$nom_art, $pr, $fournisseur_id, $id]);
    }

    public function delete(int $id_articles): void {
        $stmt = $this->db->prepare("DELETE FROM articles WHERE id_articles = ?");
        $stmt->execute([$id_articles]);
    }

    public function searchByName(string $term): array {
    $term = '%' . $term . '%';
    $stmt = $this->db->prepare(
        "SELECT articles.id_articles, articles.nom_art, articles.pr, fournisseurs.nom_fournisseurs
         FROM articles
         INNER JOIN fournisseurs ON articles.fournisseur_id = fournisseurs.id_fournisseurs
         WHERE articles.nom_art LIKE ?
         ORDER BY articles.nom_art ASC"
    );
    $stmt->execute([$term]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}