<?php include '../views/layout.php'; ?>

<div class="container mt-4" style="max-width:600px;">
    <h1 class="mb-3" style="font-size:1.3rem;">Modifier le dépôt</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?action=depots/edit&id=<?= $depot['id']; ?>">
        <?= $csrf_field ?? ''; ?>
        <div class="form-group">
            <label for="nom">Nom du dépôt <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nom" name="nom"
                   value="<?= htmlspecialchars($depot['nom']); ?>" required>
        </div>
        <div class="form-row">
            <div class="form-group col-md-8">
                <label for="adresse">Adresse</label>
                <input type="text" class="form-control" id="adresse" name="adresse"
                       value="<?= htmlspecialchars($depot['adresse'] ?? ''); ?>">
            </div>
            <div class="form-group col-md-4">
                <label for="ville">Ville</label>
                <input type="text" class="form-control" id="ville" name="ville"
                       value="<?= htmlspecialchars($depot['ville'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="telephone">Téléphone</label>
                <input type="text" class="form-control" id="telephone" name="telephone"
                       value="<?= htmlspecialchars($depot['telephone'] ?? ''); ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?= htmlspecialchars($depot['email'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="responsable_id">Responsable</label>
                <select class="form-control" id="responsable_id" name="responsable_id">
                    <option value="">Aucun</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id_users']; ?>"
                            <?= (($depot['responsable_id'] ?? '') == $u['id_users']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($u['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="statut">Statut</label>
                <select class="form-control" id="statut" name="statut">
                    <option value="actif" <?= (($depot['statut'] ?? 'actif') === 'actif') ? 'selected' : ''; ?>>Actif</option>
                    <option value="inactif" <?= (($depot['statut'] ?? '') === 'inactif') ? 'selected' : ''; ?>>Inactif</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">✔ Enregistrer</button>
        <a href="index.php?action=depots" class="btn btn-secondary ml-2">Annuler</a>
    </form>
</div>
