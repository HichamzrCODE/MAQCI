<?php include '../views/layout.php'; ?>

<div class="container mt-4">
    <h1 class="mb-3" style="font-size:1.3rem;">Liste des articles</h1>
    <div class="mb-2 d-flex justify-content-between align-items-center">
        <?php if (hasPermission('articles', 'create')): ?>
            <a href="index.php?action=articles/create" class="btn btn-success btn-sm">+ Article</a>
        <?php endif; ?>
        <span class="text-secondary" style="font-size:0.97rem;">Nombre total : <b><?= htmlspecialchars($totalArticles ?? 0); ?></b></span>
    </div>

    <div class="row mb-2">
        <div class="col-12 col-sm-8 col-md-6">
            <input type="text" id="article-search" class="form-control form-control-sm" placeholder="🔎 Rechercher article ou fournisseur...">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-hover compact-table" id="articles-table" style="font-size:0.98rem;">
            <thead>
                <tr style="background:#e9f6e8;">
                    <th style="min-width:120px;">Nom de l'article</th>
                    <th style="width:75px;">Prix rev.</th>
                    <th style="min-width:110px;">Fournisseur</th>
                    <th style="width:1%;white-space:nowrap;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $alt = false; ?>
                <?php foreach ($articles as $article): ?>
                    <tr class="<?= $alt ? 'table-row-alt' : '' ?>">
                        <td style="text-transform:uppercase;word-break:break-word;"><?= htmlspecialchars($article['nom_art']); ?></td>
                        <td><?= htmlspecialchars(number_format($article['pr'], 0, '', ' ')); ?></td>
                        <td><?= htmlspecialchars($article['nom_fournisseurs'] ?? ''); ?></td>
                        <td class="actions-cell">
                            <div class="d-flex flex-nowrap gap-1">
                                <?php if (hasPermission('articles', 'edit')): ?>
                                    <a href="index.php?action=articles/edit&id=<?= $article['id_articles']; ?>" class="btn btn-primary btn-sm px-2 py-0">✎</a>
                                <?php endif; ?>
                                <?php if (hasPermission('articles', 'delete')): ?>
                                    <a href="index.php?action=articles/delete&id=<?= $article['id_articles']; ?>" class="btn btn-danger btn-sm px-2 py-0">X</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php $alt = !$alt; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.compact-table th, .compact-table td {
    vertical-align: middle;
    padding: 5px 8px;
    font-size: 0.97rem;
    background: #fff;
}
.compact-table thead th { background: #e9f6e8; color: #295b33; font-weight: 600; }
.compact-table tbody tr.table-row-alt td { background: #f8fdf7 !important; }
.compact-table tbody tr td, .compact-table tbody tr.table-row-alt td { border-bottom: 1px solid #e1e1e1; }
.compact-table .actions-cell { white-space: nowrap; }
.compact-table .btn-sm { font-size: 0.92rem; min-width: 28px; }
input#article-search { font-size:0.98rem; }
@media (max-width: 600px) {
    .compact-table th, .compact-table td { font-size:0.92rem; }
}
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
function formatPrix(val) {
    let n = parseFloat(val);
    if (isNaN(n)) return '';
    return n.toLocaleString('fr-FR', {minimumFractionDigits: 0, maximumFractionDigits: 0});
}

$('#article-search').on('input', function() {
    var search = $(this).val();
    $.ajax({
        url: 'index.php?action=articles/search',
        type: 'GET',
        data: { term: search },
        dataType: 'json',
        success: function(data) {
            var $tbody = $('#articles-table tbody');
            $tbody.empty();
            if(data.length === 0) {
                $tbody.append('<tr><td colspan="4" class="text-center text-muted">Aucun article trouvé</td></tr>');
            } else {
                var alt = false;
                $.each(data, function(i, article) {
                    var actions = '<div class="d-flex flex-nowrap gap-1">';
                    if(article.editable)
                        actions += '<a href="index.php?action=articles/edit&id='+article.id_articles+'" class="btn btn-primary btn-sm px-2 py-0">✎</a>';
                    if(article.deletable)
                        actions += '<a href="index.php?action=articles/delete&id='+article.id_articles+'" class="btn btn-danger btn-sm px-2 py-0">X</a>';
                    actions += '</div>';
                    $tbody.append(
                        '<tr'+(alt?' class="table-row-alt"':'')+'>' +
                            '<td style="text-transform:uppercase;word-break:break-word;">'+$('<div>').text(article.nom_art).html()+'</td>' +
                            '<td>' + (article.pr ? formatPrix(article.pr) : '') + '</td>' +
                            '<td>' + (article.nom_fournisseurs ? $('<div>').text(article.nom_fournisseurs).html() : '') + '</td>' +
                            '<td class="actions-cell">' + actions + '</td>' +
                        '</tr>'
                    );
                    alt = !alt;
                });
            }
        }
    });
});
</script>