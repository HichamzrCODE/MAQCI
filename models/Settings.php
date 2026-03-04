<?php

class Settings {

    private PDO $db;
    private array $cache = [];

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Récupère la valeur d'un paramètre.
     * Retourne $default si la table n'existe pas ou si la clé est absente.
     */
    public function get(string $key, string $default = ''): string {
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        try {
            $stmt = $this->db->prepare(
                "SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1"
            );
            $stmt->execute([$key]);
            $row = $stmt->fetchColumn();
            $value = ($row !== false && $row !== null) ? (string)$row : $default;
        } catch (PDOException $e) {
            $value = $default;
        }
        $this->cache[$key] = $value;
        return $value;
    }

    /**
     * Retourne l'URL publique du logo ou null si non configuré.
     */
    public function getLogoUrl(): ?string {
        $logo = $this->get('logo_path', '');
        if ($logo === '') {
            return null;
        }
        // Si le chemin est déjà une URL absolue, le retourner tel quel.
        if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://') || str_starts_with($logo, '/')) {
            return $logo;
        }
        return '/maqci/public/' . ltrim($logo, '/');
    }

    /**
     * Définit ou met à jour un paramètre.
     */
    public function set(string $key, string $value): void {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO settings (setting_key, setting_value)
                 VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = ?"
            );
            $stmt->execute([$key, $value, $value]);
            $this->cache[$key] = $value;
        } catch (PDOException $e) {
            error_log("Settings::set error: " . $e->getMessage());
        }
    }
}
