<?php 

require_once __DIR__ . '/../includes/permissions.php';

// ====== CONFIGURATION CENTRALISÉE ======
$appConfig = require __DIR__ . '/../config/app.php';

// Vérification session unique
if (!(isset($_GET['action']) && $_GET['action'] === 'login')) {
    if (isset($_SESSION['user_id'], $_SESSION['session_token'])) {
        require_once __DIR__ . '/../models/User.php';
        $db = getPDO();
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
    <title><?= htmlspecialchars($appConfig['name']) ?> - <?= htmlspecialchars($pageTitle ?? 'Accueil') ?></title>
    <link rel="icon" type="image/x-icon" href="<?= $appConfig['paths']['img'] ?>images.ico">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?= $appConfig['paths']['css'] ?>bootstrap.min.css">
    <link rel="stylesheet" href="<?= $appConfig['paths']['css'] ?>autocomplete.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #0d6efd;
            --sidebar-bg: #f8f9fa;
            --sidebar-border: #dee2e6;
            --hover-bg: #e9ecef;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            display: flex;
            min-height: 100vh;
            background-color: #fff;
        }

        /* ====== NAVBAR ====== */
        .navbar {
            background-color: white !important;
            border-bottom: 1px solid var(--sidebar-border);
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            padding: 12px 20px !important;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.3rem;
            color: var(--primary) !important;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .navbar-brand i {
            font-size: 1.5rem;
        }

        .nav-link {
            color: #495057 !important;
            transition: color 0.3s ease !important;
            border-radius: 5px;
            margin: 0 5px;
        }

        .nav-link:hover {
            color: var(--primary) !important;
            background-color: var(--hover-bg);
        }

        .nav-link.active {
            color: var(--primary) !important;
            font-weight: 500;
        }

        /* ====== SIDEBAR DROIT ====== */
        #right-sidebar {
            position: fixed;
            top: 56px;
            right: 0;
            width: 100px;
            height: calc(100vh - 56px);
            background: var(--sidebar-bg);
            border-left: 1px solid var(--sidebar-border);
            display: flex;
            flex-direction: column;
            align-items: stretch;
            padding-top: 30px;
            z-index: 100;
            overflow-y: auto;
        }

        #right-sidebar .nav-link {
            margin: 8px 10px;
            padding: 15px 0;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            color: #000;
            background: var(--hover-bg);
            border: none;
            transition: all 0.3s ease;
            font-size: 0.85rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }

        #right-sidebar .nav-link:hover {
            background: var(--primary);
            color: #fff;
            cursor: pointer;
        }

        #right-sidebar .nav-link i {
            font-size: 1.2rem;
        }

        .sidebar-backup-link {
            background: #ffeeba !important;
            color: #856404 !important;
            border: 1px solid #ffeeba;
            margin-top: auto;
            margin-bottom: 16px;
        }

        .sidebar-backup-link:hover {
            background: #ffc107 !important;
            color: #212529 !important;
        }

        /* ====== MAIN CONTENT ====== */
        .main-content {
            margin-right: 130px;
            margin-top: 56px;
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .page-header {
            margin-bottom: 30px;
            border-bottom: 1px solid var(--sidebar-border);
            padding-bottom: 20px;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 5px;
        }

        .page-header p {
            color: #6c757d;
            margin: 0;
        }

        /* ====== UTILITAIRES ====== */
        .cell-facture {
            max-width: 120px;
            white-space: normal;
            word-break: break-all;
        }

        .cell-montant,
        .cell-versement,
        .cell-total {
            white-space: nowrap;
        }

        /* ====== PRINT ====== */
        @media print {
            body * {
                visibility: hidden !important;
            }

            .container,
            .container * {
                visibility: visible !important;
            }

            .container {
                position: absolute !important;
                left: 0;
                top: 0;
                width: 100% !important;
                z-index: 1000;
                background: #fff !important;
            }

            .btn,
            .mb-3,
            .float-right,
            .ml-2,
            .mb-3 a,
            .mb-3 button {
                display: none !important;
            }

            #right-sidebar,
            .sidebar,
            #sidebar,
            nav,
            .vertical-menu,
            .menu-lateral,
            .navbar,
            .page-header {
                display: none !important;
            }

            body.bl-print .prix-unitaire,
            body.bl-print .total-ligne,
            body.bl-print .footer .total-general {
                display: none !important;
            }

            body.bl-print td.prix-unitaire,
            body.bl-print td.total-ligne {
                display: none !important;
            }

            body.bl-print th[style*="width:20%"]:not(.prix-unitaire):not(.total-ligne) {
                width: 30% !important;
            }

            body.bl-print th[style*="width:40%"] {
                width: 70% !important;
            }
        }

        /* ====== RESPONSIVE ====== */
        @media (max-width: 768px) {
            #right-sidebar {
                width: 80px;
                padding-top: 10px;
            }

            #right-sidebar .nav-link {
                margin: 5px 5px;
                padding: 10px 0;
                font-size: 0.75rem;
            }

            .main-content {
                margin-right: 110px;
                padding: 10px;
            }

            .navbar-brand {
                font-size: 1.1rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }
        }

        /* ====== SCROLL BAR ====== */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #999;
        }
    </style>
</head>
<body>
    <!-- ====== NAVBAR ====== -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <a class="navbar-brand" href="index.php?action=home">
            <i class="fas fa-boxes"></i> <?= htmlspecialchars($appConfig['name']) ?>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if (hasPermission('dashboard', 'view')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['action'] ?? '') === 'dashboard' ? 'active' : '' ?>" 
                               href="index.php?action=dashboard">
                                <i class="fas fa-chart-line"></i> Tableau de bord
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (hasPermission('articles', 'view')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_GET['action'] ?? '', 'articles') === 0 ? 'active' : '' ?>" 
                               href="index.php?action=articles">
                                <i class="fas fa-boxes"></i> Articles
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (hasPermission('clients', 'view')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_GET['action'] ?? '', 'clients') === 0 ? 'active' : '' ?>" 
                               href="index.php?action=clients">
                                <i class="fas fa-user-tie"></i> Clients
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (hasPermission('fournisseurs', 'view')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_GET['action'] ?? '', 'fournisseurs') === 0 ? 'active' : '' ?>" 
                               href="index.php?action=fournisseurs">
                                <i class="fas fa-handshake"></i> Fournisseurs
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (hasPermission('devis', 'view')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_GET['action'] ?? '', 'devis') === 0 ? 'active' : '' ?>" 
                               href="index.php?action=devis">
                                <i class="fas fa-file-alt"></i> Devis
                            </a>
                        </li>
                    <?php endif; ?>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=login">
                            <i class="fas fa-sign-in-alt"></i> Connexion
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav ml-auto">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_GET['action'] ?? '', 'users') === 0 ? 'active' : '' ?>" 
                           href="index.php?action=users">
                            <i class="fas fa-user-cog"></i> Utilisateurs
                        </a>
                    </li>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=logout">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- ====== SIDEBAR DROIT ====== -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <div id="right-sidebar">
        <?php if (hasPermission('depots', 'view')): ?>
            <a class="nav-link <?= strpos($_GET['action'] ?? '', 'depots') === 0 ? 'active' : '' ?>" 
               href="index.php?action=depots" title="Dépôts">
                <i class="fas fa-warehouse"></i> <span>Dépôts</span>
            </a>
        <?php endif; ?>
        
        <?php if (hasPermission('credit', 'view')): ?>
            <a class="nav-link <?= strpos($_GET['action'] ?? '', 'credit') === 0 ? 'active' : '' ?>" 
               href="index.php?action=credit" title="Crédits">
                <i class="fas fa-credit-card"></i> <span>Crédit</span>
            </a>
        <?php endif; ?>
        
        <?php if (hasPermission('releve', 'view')): ?>
            <a class="nav-link <?= strpos($_GET['action'] ?? '', 'releve') === 0 ? 'active' : '' ?>" 
               href="index.php?action=releve" title="Relevés Clients">
                <i class="fas fa-list"></i> <span>Relevé</span>
            </a>
        <?php endif; ?>
        
        <?php if (hasPermission('fs', 'view')): ?>
            <a class="nav-link <?= strpos($_GET['action'] ?? '', 'fs') === 0 ? 'active' : '' ?>" 
               href="index.php?action=fs" title="Relevés Fournisseurs">
                <i class="fas fa-file-invoice"></i> <span>F/S</span>
            </a>
        <?php endif; ?>
        
        <?php if (hasPermission('voiture', 'view')): ?>
            <a class="nav-link <?= strpos($_GET['action'] ?? '', 'voiture') === 0 ? 'active' : '' ?>" 
               href="index.php?action=voiture" title="Voitures">
                <i class="fas fa-car"></i> <span>Voiture</span>
            </a>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a class="nav-link sidebar-backup-link" href="index.php?action=sauvegarde" 
               title="Sauvegarder ou télécharger les sauvegardes">
                <i class="fas fa-database"></i> <span>Backup</span>
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ====== MAIN CONTENT ====== -->
    <div class="main-content">
        <?php if (isset($pageTitle)): ?>
        <div class="page-header">
            <h1><?= htmlspecialchars($pageTitle) ?></h1>
            <?php if (isset($pageDescription)): ?>
                <p><?= htmlspecialchars($pageDescription) ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="<?= $appConfig['paths']['js'] ?>popper.min.js"></script>
    <script src="<?= $appConfig['paths']['js'] ?>bootstrap.min.js"></script>
    <script src="<?= $appConfig['paths']['js'] ?>script.js"></script>
</body>
</html>