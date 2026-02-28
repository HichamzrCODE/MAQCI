<?php include '..\views\layout.php'; 


$sql_fournisseurs = "SELECT id_fournisseurs, nom_fournisseurs FROM fournisseurs";
try { $stmt_fournisseurs = $db->prepare($sql_fournisseurs); $stmt_fournisseurs->execute();$fournisseurs = $stmt_fournisseurs->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {die("Erreur lors de la récupération des fournisseurs: " . $e->getMessage());}?>



<div class="container">
    <h1>Modifier un article</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post" action="index.php?action=articles/edit&id=<?php echo $article['id_articles']; ?>">
        <div class="form-group">
            <label for="nom">Nom de l'article:</label>
            <input type="text" class="form-control" id="nom_art" name="nom_art" value="<?php echo htmlspecialchars($article['nom_art']); ?>">
        </div>
        <div class="form-group">
            <label for="pr">prix de reviens:</label>
            <input type="text" class="form-control" id="pr" name="pr" value="<?php echo htmlspecialchars(number_format($article['pr'],0,'',' ')); ?>">
        </div>
        <div class="form-group">
            <label for="fournisseur">Fournisseur:</label>
            <select name="fournisseur_id" id="fournisseur_id"  style="height: 38px; width:100%; padding: .375rem .75rem; border: 1px solid #ced4da; color: #495057; border-radius: .25rem;"  required>

            <option value="">Sélectionner un fournisseur</option>
            <?php
            // Afficher les options du menu déroulant
            foreach ($fournisseurs as $fournisseurs) {
                echo "<option value='" . $fournisseurs['id_fournisseurs'] . "'>" . $fournisseurs['nom_fournisseurs'] . "</option>";
            }
            ?>
        </select><br>

        </div>
        <button type="submit" class="btn btn-primary">Modifier</button>
    </form>
</div>