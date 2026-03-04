<?php include '../views/layout.php'; ?>

<div class="container mt-4" style="max-width:700px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 style="font-size:1.3rem;">➕ Nouveau mouvement de stock</h1>
        <a href="index.php?action=stock_movements" class="btn btn-secondary btn-sm">← Retour</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" action="index.php?action=stock_movements/create">
                <?= $csrf_field ?? ''; ?>

                <?php
                    $preArticle = htmlspecialchars($_POST['article_id']    ?? $_GET['article_id']    ?? '');
                    $preDepot   = htmlspecialchars($_POST['depot_id']      ?? $_GET['depot_id']      ?? '');
                    $preType    = htmlspecialchars($_POST['type_mouvement'] ?? $_GET['type_mouvement'] ?? '');
                ?>
                <div class="form-group">
                    <label for="article_id">Article <span class="text-danger">*</span></label>
                    <select class="form-control" id="article_id" name="article_id" required>
                        <option value="">Sélectionner un article</option>
                        <?php foreach ($articles as $a): ?>
                            <option value="<?= $a['id_articles']; ?>"
                                <?= $preArticle == $a['id_articles'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($a['nom_art']); ?>
                                <?php if (!empty($a['sku'])): ?>(<?= htmlspecialchars($a['sku']); ?>)<?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="depot_id">Dépôt <span class="text-danger">*</span></label>
                    <select class="form-control" id="depot_id" name="depot_id" required>
                        <option value="">Sélectionner un dépôt</option>
                        <?php foreach ($depots as $d): ?>
                            <option value="<?= $d['id']; ?>"
                                <?= $preDepot == $d['id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($d['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="type_mouvement">Type de mouvement <span class="text-danger">*</span></label>
                    <select class="form-control" id="type_mouvement" name="type_mouvement" required>
                        <option value="">Sélectionner un type</option>
                        <?php
                            $types = [
                                'entree'     => '📥 Entrée (réception)',
                                'sortie'     => '📤 Sortie (expédition)',
                                'ajustement' => '🔧 Ajustement (inventaire)',
                                'retour'     => '↩ Retour',
                                'transfert'  => '🔀 Transfert (sortie dépôt)',
                            ];
                        ?>
                        <?php foreach ($types as $val => $lbl): ?>
                            <option value="<?= $val; ?>"
                                <?= $preType === $val ? 'selected' : ''; ?>>
                                <?= $lbl; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted" id="type_help"></small>
                </div>

                <div class="form-group">
                    <label for="quantite">Quantité <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="quantite" name="quantite" min="1"
                           value="<?= htmlspecialchars($_POST['quantite'] ?? ''); ?>" required>
                    <small class="form-text text-muted">
                        Pour un ajustement, indiquer la nouvelle quantité totale dans ce dépôt.
                    </small>
                </div>

                <div class="form-group">
                    <label for="reference">Référence</label>
                    <input type="text" class="form-control" id="reference" name="reference"
                           maxlength="100"
                           value="<?= htmlspecialchars($_POST['reference'] ?? ''); ?>"
                           placeholder="N° BL, facture, bon de transfert…">
                </div>

                <div class="form-group">
                    <label for="description">Description / Notes</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">Enregistrer</button>
                    <a href="index.php?action=stock_movements" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('type_mouvement').addEventListener('change', function() {
    var helps = {
        'entree':     'Augmente le stock du dépôt.',
        'sortie':     'Diminue le stock du dépôt.',
        'ajustement': 'Fixe la quantité à la valeur saisie (inventaire physique).',
        'retour':     'Augmente le stock (article retourné).',
        'transfert':  'Diminue le stock de ce dépôt (sortie pour transfert).'
    };
    document.getElementById('type_help').textContent = helps[this.value] || '';
});
</script>
