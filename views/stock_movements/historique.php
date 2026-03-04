<?php include '../views/layout.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 style="font-size:1.3rem;">📋 Historique des mouvements</h1>
        <a href="index.php?action=stock_movements" class="btn btn-secondary btn-sm">← Retour</a>
    </div>

    <!-- Filtres -->
    <form method="get" action="index.php" class="card mb-3">
        <div class="card-body py-2">
            <input type="hidden" name="action" value="stock_movements/historique">
            <div class="form-row align-items-end">
                <div class="form-group col-md-5 mb-2">
                    <label class="small mb-1">Article <span class="text-danger">*</span></label>
                    <select name="article_id" class="form-control form-control-sm" required>
                        <option value="">Sélectionner un article</option>
                        <?php foreach ($articles as $a): ?>
                            <option value="<?= $a['id_articles']; ?>"
                                <?= ($filters['article_id'] ?? 0) == $a['id_articles'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($a['nom_art']); ?>
                                <?php if (!empty($a['sku'])): ?>(<?= htmlspecialchars($a['sku']); ?>)<?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4 mb-2">
                    <label class="small mb-1">Dépôt (optionnel)</label>
                    <select name="depot_id" class="form-control form-control-sm">
                        <option value="">Tous les dépôts</option>
                        <?php foreach ($depots as $d): ?>
                            <option value="<?= $d['id']; ?>"
                                <?= ($filters['depot_id'] ?? 0) == $d['id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($d['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-2 mb-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Afficher</button>
                </div>
            </div>
        </div>
    </form>

    <?php if ($article): ?>
        <div class="card mb-3">
            <div class="card-body py-2">
                <strong style="text-transform:uppercase;"><?= htmlspecialchars($article['nom_art']); ?></strong>
                <?php if (!empty($article['sku'])): ?>
                    <span class="text-muted ml-2"><code><?= htmlspecialchars($article['sku']); ?></code></span>
                <?php endif; ?>
                <?php if ($depot): ?>
                    <span class="ml-3 text-secondary">— Dépôt : <?= htmlspecialchars($depot['nom']); ?></span>
                <?php endif; ?>
                <span class="ml-3 badge badge-light"><?= count($historique); ?> mouvement(s)</span>
            </div>
        </div>

        <?php if (empty($historique)): ?>
            <div class="alert alert-info">Aucun mouvement enregistré pour cet article<?= $depot ? ' dans ce dépôt' : ''; ?>.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover" style="font-size:0.97rem;">
                    <thead style="background:#e9f6e8;">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Dépôt</th>
                            <th>Type</th>
                            <th>Qté</th>
                            <th>Avant</th>
                            <th>Après</th>
                            <th>Référence</th>
                            <th>Utilisateur</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $typeBadges = [
                                'entree'     => 'badge-success',
                                'sortie'     => 'badge-danger',
                                'ajustement' => 'badge-info',
                                'retour'     => 'badge-warning',
                                'transfert'  => 'badge-secondary',
                            ];
                            $typeLabels = [
                                'entree'     => 'Entrée',
                                'sortie'     => 'Sortie',
                                'ajustement' => 'Ajust.',
                                'retour'     => 'Retour',
                                'transfert'  => 'Transfert',
                            ];
                        ?>
                        <?php foreach ($historique as $m): ?>
                            <?php
                                $badge = $typeBadges[$m['type_mouvement']] ?? 'badge-secondary';
                                $lbl   = $typeLabels[$m['type_mouvement']] ?? $m['type_mouvement'];
                            ?>
                            <tr>
                                <td><?= (int)$m['id']; ?></td>
                                <td style="white-space:nowrap;"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($m['created_at']))); ?></td>
                                <td><?= htmlspecialchars($m['depot_nom']); ?></td>
                                <td><span class="badge <?= $badge; ?>"><?= $lbl; ?></span></td>
                                <td><strong><?= (int)$m['quantite']; ?></strong></td>
                                <td class="text-muted"><?= $m['quantite_avant'] !== null ? (int)$m['quantite_avant'] : '-'; ?></td>
                                <td><?= $m['quantite_apres'] !== null ? (int)$m['quantite_apres'] : '-'; ?></td>
                                <td><?= htmlspecialchars($m['reference'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($m['user_nom'] ?? ''); ?></td>
                                <td>
                                    <a href="index.php?action=stock_movements/show&id=<?= $m['id']; ?>" class="btn btn-info btn-sm px-2 py-0" title="Détails">👁</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-light text-muted">Sélectionnez un article pour afficher son historique.</div>
    <?php endif; ?>
</div>
