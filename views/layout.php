<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../models/Settings.php';

// $db est disponible depuis index.php via include (pas de nouvelle scope)
$settingsModel = new Settings($db);
$appName = $settingsModel->get('app_name', 'MAQCI');
$appIcon = $settingsModel->get('app_icon', 'fa-cube');
$logoUrl = $settingsModel->getLogoUrl();

// Vérification session unique
if (!(isset($_GET['action']) && $_GET['action'] === 'login')) {
    if (isset($_SESSION['user_id'], $_SESSION['session_token'])) {
        require_once __DIR__ . '/../models/User.php';
        $userModel = new User($db);
        $token = $userModel->getSessionToken($_SESSION['user_id']);
        if ($token !== $_SESSION['session_token']) {
            session_destroy();
            header('Location: index.php?action=login&message=Déconnecté car une autre session est active');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($appName); ?></title>
    <link rel="icon" type="image/x-icon" href="/maqci/public/img/images.ico">
    <link rel="stylesheet" href="/maqci/public/css/bootstrap.min.css">
    <link rel="stylesheet" href="/maqci/public/css/autocomplete.css">
    <link rel="stylesheet" href="/maqci/public/css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          integrity="sha384-OLBgp1GsljhM2TJ+sbHjaiH9txEUvgdDTAzHv2P24donTt6/529l+9Ua0vFImLlb"
          crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body>

<!-- ============== NAVBAR ============== -->
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php?action=home">
            <?php if ($logoUrl): ?>
                <img src="<?= htmlspecialchars($logoUrl); ?>" alt="Logo" style="height:32px;width:auto;margin-right:8px;">
            <?php else: ?>
                <i class="fas <?= htmlspecialchars($appIcon); ?>"></i>
            <?php endif; ?>
            <span><?= htmlspecialchars($appName); ?></span>
        </a>
        <button class="navbar-toggler" type="button" id="sidebarToggle" aria-label="Ouvrir le menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-nav navbar-right-group">
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="user-info">
                <i class="fas fa-circle-user"></i>
                <span><?= htmlspecialchars($_SESSION['username'] ?? 'Utilisateur'); ?></span>
            </div>
            <a class="nav-link" href="index.php?action=logout" title="Déconnexion">
                <i class="fas fa-sign-out-alt"></i>
            </a>
            <?php else: ?>
            <a class="nav-link" href="index.php?action=login">Connexion</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- ============== SIDEBAR ============== -->
<?php if (isset($_SESSION['user_id'])): ?>
<aside class="sidebar" id="sidebar">

    <div class="sidebar-section">
        <div class="sidebar-title">📊 Menu Principal</div>
        <nav class="nav flex-column">
            <a class="nav-link" href="index.php?action=home">
                <i class="fas fa-home"></i><span>Accueil</span>
            </a>
            <?php if (hasPermission('dashboard', 'view')): ?>
            <a class="nav-link" href="index.php?action=dashboard">
                <i class="fas fa-chart-line"></i><span>Tableau de bord</span>
            </a>
            <?php endif; ?>
        </nav>
    </div>

    <?php if (hasPermission('clients', 'view') || hasPermission('devis', 'view') || hasPermission('credit', 'view')): ?>
    <div class="sidebar-section">
        <div class="sidebar-title">💼 Ventes</div>
        <nav class="nav flex-column">
            <?php if (hasPermission('clients', 'view')): ?>
            <a class="nav-link" href="index.php?action=clients">
                <i class="fas fa-users"></i><span>Clients</span>
            </a>
            <?php endif; ?>
            <?php if (hasPermission('devis', 'view')): ?>
            <a class="nav-link" href="index.php?action=devis">
                <i class="fas fa-file-invoice-dollar"></i><span>Devis/Factures</span>
            </a>
            <?php endif; ?>
            <?php if (hasPermission('credit', 'view')): ?>
            <a class="nav-link" href="index.php?action=credit">
                <i class="fas fa-credit-card"></i><span>Crédits Clients</span>
            </a>
            <?php endif; ?>
        </nav>
    </div>
    <?php endif; ?>

    <?php if (hasPermission('articles', 'view') || hasPermission('stock_movements', 'view')): ?>
    <div class="sidebar-section">
        <div class="sidebar-title">📦 Stock</div>
        <nav class="nav flex-column">
            <?php if (hasPermission('articles', 'view')): ?>
            <a class="nav-link" href="index.php?action=articles">
                <i class="fas fa-boxes"></i><span>Articles</span>
            </a>
            <?php endif; ?>
            <?php if (hasPermission('depots', 'view')): ?>
            <a class="nav-link" href="index.php?action=depots">
                <i class="fas fa-warehouse"></i><span>Dépôts</span>
            </a>
            <?php endif; ?>
            <?php if (hasPermission('stock_movements', 'view')): ?>
            <a class="nav-link" href="index.php?action=stock_movements">
                <i class="fas fa-boxes-stacked"></i><span>Mouvements</span>
            </a>
            <a class="nav-link" href="index.php?action=stock_movements/alerts">
                <i class="fas fa-triangle-exclamation"></i><span>Alertes stock</span>
            </a>
            <?php endif; ?>
        </nav>
    </div>
    <?php endif; ?>

    <?php if (hasPermission('fournisseurs', 'view') || hasPermission('fs', 'view')): ?>
    <div class="sidebar-section">
        <div class="sidebar-title">🤝 Partenaires</div>
        <nav class="nav flex-column">
            <?php if (hasPermission('fournisseurs', 'view')): ?>
            <a class="nav-link" href="index.php?action=fournisseurs">
                <i class="fas fa-truck"></i><span>Fournisseurs</span>
            </a>
            <?php endif; ?>
            <?php if (hasPermission('fs', 'view')): ?>
            <a class="nav-link" href="index.php?action=fs">
                <i class="fas fa-receipt"></i><span>Relevés Fournisseurs</span>
            </a>
            <?php endif; ?>
        </nav>
    </div>
    <?php endif; ?>

    <?php if (hasPermission('voiture', 'view') || hasPermission('releve', 'view')): ?>
    <div class="sidebar-section">
        <div class="sidebar-title">🚚 Logistique</div>
        <nav class="nav flex-column">
            <?php if (hasPermission('voiture', 'view')): ?>
            <a class="nav-link" href="index.php?action=voiture">
                <i class="fas fa-car"></i><span>Véhicules</span>
            </a>
            <?php endif; ?>
            <?php if (hasPermission('releve', 'view')): ?>
            <a class="nav-link" href="index.php?action=releve">
                <i class="fas fa-list-check"></i><span>Relevés</span>
            </a>
            <?php endif; ?>
        </nav>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <div class="sidebar-section">
        <div class="sidebar-title">⚙️ Admin</div>
        <nav class="nav flex-column">
            <a class="nav-link" href="index.php?action=users">
                <i class="fas fa-users-cog"></i><span>Utilisateurs</span>
            </a>
            <a class="nav-link" href="index.php?action=sauvegarde">
                <i class="fas fa-database"></i><span>Sauvegardes</span>
            </a>
            <a class="nav-link" href="index.php?action=settings">
                <i class="fas fa-cog"></i><span>Paramètres</span>
            </a>
        </nav>
    </div>
    <?php endif; ?>

</aside>
<?php endif; ?>

<!-- ============== MAIN CONTENT ============== -->
<div class="main-wrapper">
    <main class="main-content">

    <!-- scripts -->
    <script src="/maqci/public/js/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.3/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="/maqci/public/js/script.js"></script>
    <script>
    // Sidebar toggle (mobile)
    document.getElementById('sidebarToggle')?.addEventListener('click', function() {
        document.getElementById('sidebar')?.classList.toggle('sidebar-open');
    });
    // Fermer sidebar si clic sur le contenu (mobile)
    document.querySelector('.main-wrapper')?.addEventListener('click', function(e) {
        var sidebar = document.getElementById('sidebar');
        if (sidebar && sidebar.classList.contains('sidebar-open') && !sidebar.contains(e.target)) {
            sidebar.classList.remove('sidebar-open');
        }
    });
    // Décaler le contenu si pas de sidebar (utilisateur non connecté)
    if (!document.getElementById('sidebar')) {
        document.body.classList.add('no-sidebar');
    }
    </script>