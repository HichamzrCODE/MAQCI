<?php require_once __DIR__ . '/../layout.php'; ?>

<div class="container mt-4" style="max-width: 600px;">
    <h2><i class="fas fa-cog"></i> Paramètres de l'application</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="card mt-3">
        <div class="card-body">
            <form method="POST" action="index.php?action=settings" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nom de l'application</label>
                    <input type="text" name="app_name" class="form-control"
                           value="<?= htmlspecialchars($appName) ?>" maxlength="80" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Icône (classe Font Awesome, ex: fa-cube)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas <?= htmlspecialchars($appIcon) ?>"></i></span>
                        <input type="text" name="app_icon" class="form-control"
                               value="<?= htmlspecialchars($appIcon) ?>" maxlength="60">
                    </div>
                    <small class="text-muted">Laisser vide pour utiliser l'icône par défaut.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Logo (PNG, JPG, GIF, SVG, WebP)</label>
                    <?php if (!empty($logoPath)): ?>
                        <div class="mb-2">
                            <img src="<?= htmlspecialchars($logoPath) ?>" alt="Logo actuel"
                                 style="height:40px;border:1px solid #dee2e6;border-radius:4px;padding:4px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                    <small class="text-muted">Taille recommandée : 200 × 50 px.</small>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </form>
        </div>
    </div>
</div>
