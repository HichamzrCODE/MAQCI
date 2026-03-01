<?php include '../views/layout.php'; ?>

<div class="container mt-4">
    <h1 class="mb-3" style="font-size:1.3rem;">Gestion des dépôts</h1>
    <div class="mb-3">
        <?php if (hasPermission('articles', 'create')): ?>
            <a href="index.php?action=depots/create" class="btn btn-success btn-sm">+ Nouveau dépôt</a>
        <?php endif; ?>
        <a href="index.php?action=articles" class="btn btn-secondary btn-sm ml-2">← Articles</a>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-hover" style="font-size:0.97rem;">
            <thead class="thead-light">
                <tr>
                    <th>Nom</th>
                    <th>Ville</th>
                    <th>Responsable</th>
                    <th>Téléphone</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($depots as $depot): ?>
                    <tr>
                        <td><?= htmlspecialchars($depot['nom']); ?></td>
                        <td><?= htmlspecialchars($depot['ville'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($depot['responsable_nom'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($depot['telephone'] ?? '-'); ?></td>
                        <td>
                            <span class="badge badge-<?= $depot['statut'] === 'actif' ? 'success' : 'secondary'; ?>">
                                <?= htmlspecialchars(ucfirst($depot['statut'])); ?>
                            </span>
                        </td>
                        <td>
                            <?php if (hasPermission('articles', 'edit')): ?>
                                <a href="index.php?action=depots/edit&id=<?= $depot['id']; ?>" class="btn btn-primary btn-sm px-2 py-0">✎</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($depots)): ?>
                    <tr><td colspan="6" class="text-center text-muted">Aucun dépôt enregistré.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
