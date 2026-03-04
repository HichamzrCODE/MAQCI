<?php include '../views/layout.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2><i class="fas fa-plus-circle"></i> Nouvelle Réception Fournisseur</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" class="card p-4">
                <?= $csrf_field ?>

                <div class="mb-3">
                    <label for="fournisseur_id" class="form-label">Fournisseur <span class="text-danger">*</span></label>
                    <select class="form-select" id="fournisseur_id" name="fournisseur_id" required>
                        <option value="">-- Sélectionner un fournisseur --</option>
                        <?php foreach ($fournisseurs as $f): ?>
                            <option value="<?= $f['id_fournisseurs'] ?>"
                                <?= ($_POST['fournisseur_id'] ?? '') == $f['id_fournisseurs'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['nom_fournisseurs']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="depot_id" class="form-label">Dépôt de Réception <span class="text-danger">*</span></label>
                    <select class="form-select" id="depot_id" name="depot_id" required>
                        <option value="">-- Sélectionner le dépôt --</option>
                        <?php foreach ($depots as $depot): ?>
                            <option value="<?= $depot['id'] ?>"
                                <?= ($_POST['depot_id'] ?? '') == $depot['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($depot['nom']) ?>
                                <?php if (!empty($depot['ville'])): ?>(<?= htmlspecialchars($depot['ville']) ?>)<?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="date_reception" class="form-label">Date de Réception <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="date_reception" name="date_reception" required
                           value="<?= htmlspecialchars($_POST['date_reception'] ?? date('Y-m-d')) ?>">
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description / Référence BL</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Créer la réception
                    </button>
                    <a href="index.php?action=receptions_fournisseur" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
