<?php include '..\views\layout.php'; ?>

<?php
$sql_fournisseurs = "SELECT id_fournisseurs, nom_fournisseurs FROM fournisseurs ORDER BY nom_fournisseurs ASC";
try {
    $stmt_fournisseurs = $db->prepare($sql_fournisseurs);
    $stmt_fournisseurs->execute();
    $fournisseurs = $stmt_fournisseurs->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur lors de la récupération des fournisseurs: " . $e->getMessage());
}
?>

<div class="container">
    <h1 style="margin-top: 30px;">Créer un nouvel article</h1>

    <?php if (isset($data['error'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($data['error']); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="index.php?action=articles/create">
        <div class="form-group">
            <label for="nom_art">Nom de l'article:</label>
            <input type="text" class="form-control" id="nom_art" name="nom_art" style="text-transform: uppercase;" required>
        </div>

        <div class="form-group">
            <label for="pr">Prix de revient:</label>
            <input type="number" step="0.01" class="form-control" id="pr" name="pr" required>
        </div>

        <div class="form-group">
            <label for="fournisseur_id">Fournisseur:</label>
            <select class="form-control" id="fournisseur_id" name="fournisseur_id" required>
                <option value="">Sélectionner un fournisseur</option>
                <?php foreach ($fournisseurs as $fournisseur): ?>
                    <option value="<?php echo htmlspecialchars($fournisseur['id_fournisseurs']); ?>">
                        <?php echo htmlspecialchars($fournisseur['nom_fournisseurs']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Créer</button>
        <a href="index.php?action=articles" class="btn btn-secondary">Annuler</a>
    </form>
</div>