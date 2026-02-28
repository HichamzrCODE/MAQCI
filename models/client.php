<?php

class Client {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // 1. Récupérer tous les clients (triés par nom)
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM clients ORDER BY nom ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Créer un client
    public function create(string $nom, string $ville, string $telephone, int $userId, string $typeClient): int {
        $stmt = $this->db->prepare(
            "INSERT INTO clients (nom, ville, telephone, created_by, type_client, created_at) VALUES (?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([$nom, $ville, $telephone, $userId, $typeClient]);
        return (int)$this->db->lastInsertId();
    }

    // 3. Trouver un client par ID
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM clients WHERE id_clients = ?");
        $stmt->execute([$id]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        return $client ?: null;
    }

    // 4. Mettre à jour un client
    public function update(int $id, string $nom, string $ville, string $telephone, string $typeClient): void {
        $stmt = $this->db->prepare(
            "UPDATE clients SET nom = ?, ville = ?, telephone = ?, type_client = ? WHERE id_clients = ?"
        );
        $stmt->execute([$nom, $ville, $telephone, $typeClient, $id]);
    }

    // 5. Supprimer un client
    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM clients WHERE id_clients = ?");
        $stmt->execute([$id]);
    }

    // 6. Recherche par nom (autocomplete, recherche rapide)
    public function searchByName(string $term): array {
        $stmt = $this->db->prepare("SELECT id_clients, nom FROM clients WHERE nom LIKE ? ORDER BY nom ASC LIMIT 15");
        $stmt->execute(['%' . $term . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 7. Recherche complète (affichage liste clients)
    public function searchFull(string $term): array {
        $stmt = $this->db->prepare("SELECT * FROM clients WHERE nom LIKE ? ORDER BY nom ASC LIMIT 30");
        $stmt->execute(['%' . $term . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 8. Vérifie l'existence d'un client par nom (insensible à la casse)
    public function existsByName(string $nom): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM clients WHERE LOWER(nom) = LOWER(?)");
        $stmt->execute([$nom]);
        return $stmt->fetchColumn() > 0;
    }

    // 9. Récupère les articles de devis pour un client (exemple)
    public function getArticlesDevis(int $clientId): array {
        $stmt = $this->db->prepare("
            SELECT 
                a.nom_art AS article,
                dl.prix_unitaire,
                d.numero AS devis_numero,
                d.date AS devis_date
            FROM devis_lignes dl
            JOIN devis d ON dl.devis_id = d.id
            JOIN articles a ON dl.article_id = a.id_articles
            WHERE d.client_id = ?
            ORDER BY a.nom_art ASC, d.numero DESC
        ");
        $stmt->execute([$clientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 10. Clients cash en retard de paiement (> 30 jours depuis dernier versement)
    public function getCashRetard() {
        $sql = "SELECT c.id_clients, c.nom, MAX(cl.date_operation) AS last_versement
                FROM credit_lignes cl
                JOIN credit_releves cr ON cl.releve_id = cr.id
                JOIN clients c ON cr.client_id = c.id_clients
                WHERE c.type_client = 'cash'
                  AND cl.versement > 0
                  AND (
                        cl.numero_facture IS NULL OR 
                        (
                            UPPER(cl.numero_facture) NOT LIKE '%RETOUR%' AND 
                            UPPER(cl.numero_facture) NOT LIKE '%AVOIR%'
                        )
                  )
                GROUP BY c.id_clients, c.nom
                HAVING DATEDIFF(NOW(), last_versement) > 30
                ORDER BY last_versement ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // 11. Clients cash inactifs (> 2 semaines sans commande)
    public function getCashSansCommande() {
        $sql = "SELECT c.id_clients, c.nom, MAX(cl.date_operation) AS last_operation
                FROM credit_lignes cl
                JOIN credit_releves cr ON cl.releve_id = cr.id
                JOIN clients c ON cr.client_id = c.id_clients
                WHERE c.type_client = 'cash'
                  AND cl.montant > 0
                  AND (
                        cl.numero_facture IS NULL OR 
                        (
                            UPPER(cl.numero_facture) NOT LIKE '%RETOUR%' AND 
                            UPPER(cl.numero_facture) NOT LIKE '%AVOIR%'
                        )
                  )
                GROUP BY c.id_clients, c.nom
                HAVING DATEDIFF(NOW(), last_operation) > 14
                ORDER BY last_operation ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

public function getFacturesImpayeesParReleveSansTotalVersement() {
    // On récupère tous les relevés entreprise (type facture) avec leur total de versement
    $sql = "
        SELECT cr.*, c.nom,
            (SELECT SUM(cl.versement)
             FROM credit_lignes cl
             WHERE cl.releve_id = cr.id
               AND cl.versement > 0
               AND (cl.numero_facture IS NULL OR (UPPER(cl.numero_facture) NOT LIKE '%RETOUR%' AND UPPER(cl.numero_facture) NOT LIKE '%AVOIR%'))
            ) AS total_versements
        FROM credit_releves cr
        JOIN clients c ON cr.client_id = c.id_clients
        WHERE c.type_client = 'facture'
          AND cr.total_general > 0
        ORDER BY cr.created_at ASC";
    $stmt = $this->db->query($sql);
    $releves = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($releves as $releve) {
        $totalVersements = $releve['total_versements'] ?? 0;
        $reste = $releve['total_general'] - $totalVersements;
        if ($reste <= 0) continue; // Relevé soldé : on n’affiche rien

        // On récupère les factures du relevé, plus vieilles que 90 jours
        $sqlFactures = "SELECT cl.*, c.nom, cr.client_id
                        FROM credit_lignes cl
                        JOIN credit_releves cr ON cl.releve_id = cr.id
                        JOIN clients c ON cr.client_id = c.id_clients
                        WHERE cl.releve_id = ?
                          AND cl.montant > 0
                          AND cl.date_operation <= DATE_SUB(NOW(), INTERVAL 90 DAY)
                          AND (cl.numero_facture IS NULL OR (UPPER(cl.numero_facture) NOT LIKE '%RETOUR%' AND UPPER(cl.numero_facture) NOT LIKE '%AVOIR%'))
                        ORDER BY cl.date_operation ASC";
        $stmtFact = $this->db->prepare($sqlFactures);
        $stmtFact->execute([$releve['id']]);
        $factures = $stmtFact->fetchAll(PDO::FETCH_ASSOC);

        // On prend les factures jusqu’à épuisement du reste dû
        foreach ($factures as $facture) {
            if ($reste <= 0) break;
            if ($facture['montant'] <= $reste) {
                $facture['reste_a_payer'] = $facture['montant'];
                $result[] = $facture;
                $reste -= $facture['montant'];
            } else {
                $facture['reste_a_payer'] = $reste;
                $result[] = $facture;
                $reste = 0;
            }
        }
    }
    return $result;
}
    // 13. Entreprises inactives (> 1 mois sans commande)
    public function getEntSansCommande() {
        $sql = "SELECT c.id_clients, c.nom, MAX(cl.date_operation) AS last_operation
                FROM credit_lignes cl
                JOIN credit_releves cr ON cl.releve_id = cr.id
                JOIN clients c ON cr.client_id = c.id_clients
                WHERE c.type_client = 'facture'
                  AND cl.montant > 0
                  AND (
                        cl.numero_facture IS NULL OR 
                        (
                            UPPER(cl.numero_facture) NOT LIKE '%RETOUR%' AND 
                            UPPER(cl.numero_facture) NOT LIKE '%AVOIR%'
                        )
                  )
                GROUP BY c.id_clients, c.nom
                HAVING DATEDIFF(NOW(), last_operation) > 30
                ORDER BY last_operation ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}