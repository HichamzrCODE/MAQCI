<?php include '..\views\layout.php'; ?>

<div class="container">
    <h1 style="margin-top: 30PX;">Ajouter un fournisseur</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post" action="index.php?action=fournisseurs/create">
        <div class="form-group">
            <label for="nom">Nom:</label>
            <input type="text" class="form-control" id="nom_fournisseurs" name="nom_fournisseurs">
        </div>
       
        <button type="submit" class="btn btn-primary">Ajouter</button>
    </form>
</div>