<?php include '..\views\layout.php'; ?>

<div class="container">
    <h1>Modifier un fournisseur</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post" action="index.php?action=fournisseurs/edit&id=<?php echo $fournisseur['id_fournisseurs']; ?>">
        <div class="form-group">
            <label for="nom">Nom:</label>
         <input type="text" class="form-control" id="nom_fournisseurs" name="nom_fournisseurs" value="<?php echo $fournisseur['nom_fournisseurs']; ?>">
        </div>
        
        <button type="submit" class="btn btn-primary">Modifier</button>
    </form>
</div>
