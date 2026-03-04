<?php

class SettingsController {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function index() {
        require_once __DIR__ . '/../models/Settings.php';
        $settingsModel = new Settings($this->db);

        $message = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $appName = trim($_POST['app_name'] ?? '');
            $appIcon = trim($_POST['app_icon'] ?? 'fa-cube');
            if ($appName !== '') {
                $settingsModel->set('app_name', $appName);
                $settingsModel->set('app_icon', $appIcon);
                $message = 'Paramètres sauvegardés.';
            }

            // Handle logo upload
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $mimeToExt = [
                    'image/png'     => 'png',
                    'image/jpeg'    => 'jpg',
                    'image/gif'     => 'gif',
                    'image/svg+xml' => 'svg',
                    'image/webp'    => 'webp',
                ];
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($_FILES['logo']['tmp_name']);
                if (isset($mimeToExt[$mimeType])) {
                    $ext = $mimeToExt[$mimeType];
                    $filename = 'logo_' . bin2hex(random_bytes(8)) . '.' . $ext;
                    $dest = __DIR__ . '/../public/img/' . $filename;
                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $dest)) {
                        $settingsModel->set('logo_path', '/maqci/public/img/' . $filename);
                        $message = 'Logo et paramètres sauvegardés.';
                    }
                }
            }
        }

        return [
            'view' => 'settings/index',
            'data' => [
                'appName'  => $settingsModel->get('app_name', 'MAQCI'),
                'appIcon'  => $settingsModel->get('app_icon', 'fa-cube'),
                'logoPath' => $settingsModel->get('logo_path', ''),
                'message'  => $message,
            ]
        ];
    }
}
