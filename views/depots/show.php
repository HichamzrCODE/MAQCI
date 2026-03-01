<?php include '../views/layout.php'; ?>

<?php
$pageTitle = "Dépôt : " . htmlspecialchars($depot['nom']);
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-warehouse"></i> <?= htmlspecialchars($depot['nom']) ?></h2>
            <p class="text-muted"><?= htmlspecialchars($depot['localisation'] ?? '') ?></p>
        </div>
        <div class="col-md-4 text-end">
            <?php if (hasPermission('depots', 'edit')): ?>
                <a href="index.php?action=depots/edit&id=<?= $depot['id'] ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Modifier
                </a>
            <?php endif; ?>
            <a href="index.php?action=depots" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <p><strong>Responsable :</strong> <?= htmlspecialchars($depot['responsable'] ?? 'Non spécifié') ?></p>
                    <p><strong>Téléphone :</strong> <?= htmlspecialchars($depot['telephone'] ?? 'Non spécifié') ?></p>
                    <p><strong>Email :</strong> <?= htmlspecialchars($depot['email'] ?? 'Non spécifié') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <p><strong>Notes :</strong> <?= htmlspecialchars($depot['notes'] ?? 'Aucune note') ?></p>
                </div>
            </div>
        </div>
    </div>

    <h3>Stock dans ce dépôt</h3>
    <?php if (count($stocks) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Article</th>
                        <th>SKU</th>
                        <th>Quantité</th>
                        <th>En Transit</th>
                        <th>Bloquée</th>
                        <th>Prix Revient</th>
                        <th>Prix Vente</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stocks as $stock): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($stock['nom_art']) ?></strong></td>
                            <td><?= htmlspecialchars($stock['sku'] ?? '') ?></td>
                            <td><?= $stock['quantite'] ?></td>
                            <td><?= $stock['quantite_en_transit'] ?? 0 ?></td>
                            <td><?= $stock['quantite_bloquee'] ?? 0 ?></td>
                            <td><?= number_format($stock['pr'], 2, ',', ' ') ?> F</td>
                            <td><?= number_format($stock['prix_vente'], 2, ',', ' ') ?> F</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Aucun stock dans ce dépôt pour le moment.</div>
    <?php endif; ?>
</div>