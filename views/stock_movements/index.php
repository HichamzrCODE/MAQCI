<?php include '../views/layout.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h1 style="font-size:1.3rem;">📦 Mouvements de stock</h1>
        <div class="d-flex gap-2 flex-wrap">
            <?php if (hasPermission('stock_movements', 'create')): ?>
                <a href="index.php?action=stock_movements/create" class="btn btn-success btn-sm">+ Nouveau mouvement</a>
            <?php endif; ?>
            <a href="index.php?action=stock_movements/alerts" class="btn btn-warning btn-sm">⚠ Alertes</a>
            <a href="index.php?action=stock_movements/historique" class="btn btn-outline-info btn-sm">📋 Historique</a>
            <a href="index.php?action=stock_movements/seuils" class="btn btn-outline-secondary btn-sm">⚙ Seuils</a>
        </div>
    </div>

    <!-- Filtres -->
    <form method="get" action="index.php" class="card mb-3">
        <div class="card-body py-2">
            <input type="hidden" name="action" value="stock_movements">
            <div class="form-row align-items-end">
                <div class="form-group col-md-3 mb-2">
                    <label class="small mb-1">Article</label>
                    <select name="article_id" class="form-control form-control-sm">
                        <option value="">Tous les articles</option>
                        <?php foreach ($articles as $a): ?>
                            <option value="<?= $a['id_articles']; ?>"
                                <?= ($filters['article_id'] ?? '') == $a['id_articles'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($a['nom_art']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-2 mb-2">
                    <label class="small mb-1">Dépôt</label>
                    <select name="depot_id" class="form-control form-control-sm">
                        <option value="">Tous les dépôts</option>
                        <?php foreach ($depots as $d): ?>
                            <option value="<?= $d['id']; ?>"
                                <?= ($filters['depot_id'] ?? '') == $d['id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($d['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-2 mb-2">
                    <label class="small mb-1">Type</label>
                    <select name="type_mouvement" class="form-control form-control-sm">
                        <option value="">Tous les types</option>
                        <?php foreach (['entree' => 'Entrée', 'sortie' => 'Sortie', 'ajustement' => 'Ajustement', 'retour' => 'Retour', 'transfert' => 'Transfert'] as $val => $label): ?>
                            <option value="<?= $val; ?>" <?= ($filters['type_mouvement'] ?? '') === $val ? 'selected' : ''; ?>>
                                <?= $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-2 mb-2">
                    <label class="small mb-1">Du</label>
                    <input type="date" name="date_debut" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($filters['date_debut'] ?? ''); ?>">
                </div>
                <div class="form-group col-md-2 mb-2">
                    <label class="small mb-1">Au</label>
                    <input type="date" name="date_fin" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($filters['date_fin'] ?? ''); ?>">
                </div>
                <div class="form-group col-md-1 mb-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">🔎</button>
                </div>
            </div>
        </div>
    </form>

    <p class="text-muted small">Total : <strong><?= (int)$total; ?></strong> mouvement(s)</p>

    <div class="table-responsive">
        <table class="table table-sm table-hover" style="font-size:0.97rem;">
            <thead style="background:#e9f6e8;">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Article</th>
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
                <?php if (empty($mouvements)): ?>
                    <tr><td colspan="11" class="text-center text-muted py-3">Aucun mouvement trouvé</td></tr>
                <?php endif; ?>
                <?php foreach ($mouvements as $m): ?>
                    <?php
                        $typeBadges = [
                            'entree'      => 'badge-success',
                            'sortie'      => 'badge-danger',
                            'ajustement'  => 'badge-info',
                            'retour'      => 'badge-warning',
                            'transfert'   => 'badge-secondary',
                        ];
                        $typeLabels = [
                            'entree'      => 'Entrée',
                            'sortie'      => 'Sortie',
                            'ajustement'  => 'Ajustement',
                            'retour'      => 'Retour',
                            'transfert'   => 'Transfert',
                        ];
                        $badge = $typeBadges[$m['type_mouvement']] ?? 'badge-secondary';
                        $label = $typeLabels[$m['type_mouvement']] ?? $m['type_mouvement'];
                    ?>
                    <tr>
                        <td><?= (int)$m['id']; ?></td>
                        <td style="white-space:nowrap;"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($m['created_at']))); ?></td>
                        <td>
                            <span style="text-transform:uppercase;"><?= htmlspecialchars($m['nom_art']); ?></span>
                            <?php if (!empty($m['sku'])): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($m['sku']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($m['depot_nom']); ?></td>
                        <td><span class="badge <?= $badge; ?>"><?= $label; ?></span></td>
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

    <?php if ($total > $limit): ?>
        <nav>
            <ul class="pagination pagination-sm">
                <?php $pages = ceil($total / $limit); ?>
                <?php for ($p = 1; $p <= $pages; $p++): ?>
                    <?php
                        $q = array_merge($filters, ['page' => $p]);
                        $qs = http_build_query(array_merge(['action' => 'stock_movements'], $q));
                    ?>
                    <li class="page-item <?= $p === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="index.php?<?= $qs; ?>"><?= $p; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>
