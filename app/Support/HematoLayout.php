<?php

namespace App\Support;

/**
 * Disposition de la fiche Hématologie / NFS.
 * Utilisée par le contrôleur (enregistrement) et par le PDF (affichage).
 * Les index 0..13 sont les mêmes que dans create.blade.php (getHematoTable) :
 * index pair = colonne de gauche, index impair = colonne de droite.
 *
 * kind : 'single' = un seul résultat
 *        'split'  = deux résultats (Pourcentage % dans "resultat", Absolues G/L dans "observation")
 *        'text'   = ligne GE / DP en bas de tableau (texte libre)
 */
class HematoLayout
{
    public static function all(): array
    {
        return [
            0  => ['G.BLANCS',    '3 à 8 G/l',        'single'],
            1  => ['P.Neu',       '1.5 - 6 G/l',      'split'],
            2  => ['G.ROUGES',    '4 à 6 T/l',        'single'],
            3  => ['P.Eos',       '0.15 - 0.4 G/l',   'split'],
            4  => ['HB', '12 à 16 g/dl',     'single'],
            5  => ['P.Baso',      '0.05 - 0.15 G/l',  'split'],
            6  => ['HT', '35 à 45 %',        'single'],
            7  => ['Lympho',      '1.5 - 4 G/l',      'split'],
            8  => ['VGM',         '80 à 90 FL',       'single'],
            9  => ['Mono',        '0.2 - 0.8 G/l',    'split'],
            10 => ['CCMH',        '30 à 36 g/dl',     'single'],
            11 => ['PLAQ.',       '',                 'single'],
            12 => ['TCMH',        '25 à 32 pg',       'single'],
            13 => ['GE/DP',       '',                 'text'],
        ];
    }
}