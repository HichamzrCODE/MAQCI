<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = 'articles';
    protected $primaryKey = 'id_articles';
    protected $fillable = [
        'nom_art',
        'pr',
        'fournisseur_id',
        'created_by',
        'created_at',
        'sku',
        'prix_vente',
        'fournisseur_alternatif_id',
        'poids_kg',
        'longueur_cm',
        'largeur_cm',
        'hauteur_cm',
        'couleur',
        'unite_mesure',
        'stock_minimal',
        'stock_maximal',
        'quantite_totale',
        'image_path',
        'categorie_id',
        'statut',
        'updated_by',
        'updated_at',
        'deleted_at',
        'notes_internes',
    ];
    
    // Other model methods and relationships
}