<?php

class MouvementStock {

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Crée un mouvement de stock (ENTREE ou SORTIE) et met à jour le stock.
     *
     * @param int    $articleId     ID de l'article
     * @param int    $depotId       ID du dépôt concerné
     * @param string $type          'ENTREE' ou 'SORTIE'
     * @param int    $quantite      Quantité à mouvoir (positive)
     * @param int    $userId        ID de l'utilisateur
     * @param string $referenceType Type de référence ('transfert', 'reception', ...)
     * @param int|null $referenceId ID de la référence
     * @param string $notes         Notes optionnelles
     * @return int ID du mouvement créé
     */
    public function create(
        int    $articleId,
        int    $depotId,
        string $type,
        int    $quantite,
        int    $userId,
        string $referenceType = '',
        ?int   $referenceId = null,
        string $notes = ''
    ): int {
        // Enregistrer le mouvement
        $stmt = $this->db->prepare(
            "INSERT INTO mouvements_stock
                (article_id, depot_id, type, quantite, reference_type, reference_id, user_id, notes)
             VALUES
                (:article_id, :depot_id, :type, :quantite, :reference_type, :reference_id, :user_id, :notes)"
        );
        $stmt->execute([
            ':article_id'     => $articleId,
            ':depot_id'       => $depotId,
            ':type'           => $type,
            ':quantite'       => $quantite,
            ':reference_type' => $referenceType ?: null,
            ':reference_id'   => $referenceId,
            ':user_id'        => $userId,
            ':notes'          => $notes ?: null,
        ]);
        $mouvementId = (int)$this->db->lastInsertId();

        // Mettre à jour le stock dans le dépôt
        if ($type === 'ENTREE') {
            $this->incrementStock($articleId, $depotId, $quantite);
        } else {
            $this->decrementStock($articleId, $depotId, $quantite);
        }

        // Recalculer la quantité totale dans articles
        $this->updateTotalQuantite($articleId);

        return $mouvementId;
    }

    /**
     * Récupère tous les mouvements d'un article.
     */
    public function getByArticle(int $articleId): array {
        $stmt = $this->db->prepare(
            "SELECT m.*, d.nom AS depot_nom, u.username AS user_nom
             FROM mouvements_stock m
             LEFT JOIN depots d ON m.depot_id = d.id
             LEFT JOIN users u ON m.user_id = u.id_users
             WHERE m.article_id = ?
             ORDER BY m.created_at DESC"
        );
        $stmt->execute([$articleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les mouvements liés à une référence.
     */
    public function getByReference(string $referenceType, int $referenceId): array {
        $stmt = $this->db->prepare(
            "SELECT m.*, a.nom_art, d.nom AS depot_nom, u.username AS user_nom
             FROM mouvements_stock m
             LEFT JOIN articles a ON m.article_id = a.id_articles
             LEFT JOIN depots d ON m.depot_id = d.id
             LEFT JOIN users u ON m.user_id = u.id_users
             WHERE m.reference_type = ? AND m.reference_id = ?
             ORDER BY m.created_at ASC"
        );
        $stmt->execute([$referenceType, $referenceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retourne le stock disponible d'un article dans un dépôt.
     */
    public function getStockDisponible(int $articleId, int $depotId): int {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(quantite, 0) FROM stock_par_depot
             WHERE article_id = ? AND depot_id = ?"
        );
        $stmt->execute([$articleId, $depotId]);
        return (int)$stmt->fetchColumn();
    }

    // ----------------------------------------------------------------
    // Méthodes privées
    // ----------------------------------------------------------------

    private function incrementStock(int $articleId, int $depotId, int $quantite): void {
        $stmt = $this->db->prepare(
            "INSERT INTO stock_par_depot (article_id, depot_id, quantite)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE quantite = quantite + ?"
        );
        $stmt->execute([$articleId, $depotId, $quantite, $quantite]);
    }

    private function decrementStock(int $articleId, int $depotId, int $quantite): void {
        $stmt = $this->db->prepare(
            "UPDATE stock_par_depot SET quantite = GREATEST(0, quantite - ?)
             WHERE article_id = ? AND depot_id = ?"
        );
        $stmt->execute([$quantite, $articleId, $depotId]);
    }

    private function updateTotalQuantite(int $articleId): void {
        $stmt = $this->db->prepare(
            "UPDATE articles
             SET quantite_totale = (
                 SELECT COALESCE(SUM(quantite), 0)
                 FROM stock_par_depot
                 WHERE article_id = ?
             )
             WHERE id_articles = ?"
        );
        $stmt->execute([$articleId, $articleId]);
    }
}
