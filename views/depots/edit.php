<?php include '../views/layout.php'; ?>

<?php
$pageTitle = "Modifier Dépôt : " . htmlspecialchars($depot['nom']);
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2><i class="fas fa-edit"></i> Modifier Dépôt</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" class="card p-4">
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nom" name="nom" 
                           value="<?= htmlspecialchars($depot['nom']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="localisation" class="form-label">Localisation <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="localisation" name="localisation" 
                           value="<?= htmlspecialchars($depot['localisation'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="responsable" class="form-label">Responsable</label>
                    <input type="text" class="form-control" id="responsable" name="responsable"
                           value="<?= htmlspecialchars($depot['responsable'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="telephone" class="form-label">Téléphone</label>
                    <input type="tel" class="form-control" id="telephone" name="telephone"
                           value="<?= htmlspecialchars($depot['telephone'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= htmlspecialchars($depot['email'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3">
                        <?= htmlspecialchars($depot['notes'] ?? '') ?>
                    </textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Modifier
                    </button>
                    <a href="index.php?action=depots" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>