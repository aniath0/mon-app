<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Définition des modèles d'examens (détectés par le NOM de l'examen).
 * Doit rester cohérent avec les fonctions JS de create.blade.php.
 */
class ExamTemplates
{
    public const SDW_FIELDS = ['AO', 'AH', 'BO', 'BH', 'CO', 'CH', 'TO', 'TH', 'conclusion'];

    /** Détecte le type de tableau à partir du nom (sans accents, minuscules). */
    public static function kind(?string $name): string
    {
        $n = Str::lower(Str::ascii((string) $name));

        if (str_contains($n, 'serologi') && str_contains($n, 'hb')) return 'serologie_hb';
        if (str_contains($n, 'serologi')) return 'serologie';
        if (str_contains($n, 'biochimi')) return 'biochimie';
        if (str_contains($n, 'hemato') || str_contains($n, 'nfs') || str_contains($n, 'numerat')) return 'hemato';
        if (str_contains($n, 'antibiogramme') || str_contains($n, 'atb') || str_contains($n, 'spermogramme')) return 'atb';

        return 'default';
    }

    /** Paramètres de la sérologie, dans l'ordre du formulaire. */
    public static function serologieParams(string $kind): array
    {
        return [
            'ASLO', 'AgHBs', 'TPHA',
            $kind === 'serologie_hb' ? "φ de l'HB" : 'VDRL',
            'GS-RH', 'CRP', 'Séro test (VIH)', 'HCV',
        ];
    }

    /** Biochimie : sections avec lignes (nom, unité, norme). */
    public static function biochimie(): array
    {
        return [
            ['title' => null, 'rows' => [
                ['nom' => 'Glycémie à jeun', 'unite' => 'g/l', 'norme' => '0.74 - 1.10 g/l'],
                ['nom' => 'Glycémie PP', 'unite' => 'g/l', 'norme' => '0.80 - 1.40 g/l'],
                ['nom' => 'Urée', 'unite' => 'g/l', 'norme' => '0.10 - 0.50 g/l'],
                ['nom' => 'Créatininémie', 'unite' => 'mg/l', 'norme' => '6 - 13 mg/l'],
                ['nom' => 'Uricémie', 'unite' => 'mg/l', 'norme' => '30 - 57 mg/l'],
                ['nom' => 'Calcémie', 'unite' => 'mg/l', 'norme' => '85 - 105 mg/l'],
                ['nom' => 'Magnésémie', 'unite' => 'mg/l', 'norme' => '16 - 25 mg/l'],
                ['nom' => 'Cholestérol total', 'unite' => 'g/l', 'norme' => '1.50 - 2.60 g/l'],
                ['nom' => 'HDL Cholestérol', 'unite' => 'g/l', 'norme' => '0.35 - 0.65 g/l'],
                ['nom' => 'LDL Cholestérol', 'unite' => 'g/l', 'norme' => '< 1.50 g/l'],
                ['nom' => 'Phosphatases alcalines', 'unite' => 'U/l', 'norme' => 'Adlt: 98-279, Enf: <6'],
                ['nom' => 'Amylasémie', 'unite' => 'U/l', 'norme' => '< 90 U/l'],
            ]],
            ['title' => 'Transaminases', 'rows' => [
                ['nom' => 'TGO', 'unite' => 'U/l', 'norme' => '< 40 U/l'],
                ['nom' => 'TGP', 'unite' => 'U/l', 'norme' => '< 40 U/l'],
            ]],
            ['title' => 'Bilirubine', 'rows' => [
                ['nom' => 'Totale', 'unite' => 'mg/l', 'norme' => '< 10 mg/l'],
                ['nom' => 'Direct', 'unite' => 'mg/l', 'norme' => '< 2.50 mg/l'],
            ]],
            ['title' => null, 'rows' => [
                ['nom' => 'Protéines Totales', 'unite' => 'g/l', 'norme' => '62 - 82 g/l'],
                ['nom' => 'Triglycérides', 'unite' => 'g/l', 'norme' => '0.40 - 1.40 g/l'],
                ['nom' => 'Gamma GT', 'unite' => 'U/l', 'norme' => 'H: 11-50, F: 7-32'],
                ['nom' => 'Phosphore', 'unite' => 'mg/l', 'norme' => 'Adlt: 25-50, Enft: 40-70'],
            ]],
            ['title' => 'Ionogramme sanguin', 'rows' => [
                ['nom' => 'Na+', 'unite' => 'mEq/l', 'norme' => '135 - 145 mEq/l'],
                ['nom' => 'K+', 'unite' => 'mEq/l', 'norme' => '3.50 - 5.50 mEq/l'],
                ['nom' => 'Cl-', 'unite' => 'mEq/l', 'norme' => '98 - 108 mEq/l'],
            ]],
        ];
    }

    /** Biochimie à plat, dans l'ordre exact des index du formulaire. */
    public static function biochimieFlat(): array
    {
        $flat = [];
        foreach (self::biochimie() as $section) {
            foreach ($section['rows'] as $row) {
                $flat[] = $row;
            }
        }
        return $flat;
    }

    /**
     * Hématologie : index du formulaire => [paramètre, norme].
     * Le formulaire place gauche/droite sur la même ligne : 0-1, 2-3, 4-5...
     */
    public static function hemato(): array
    {
        return [
            0  => ['G.BLANCS', '3 à 8 G/l'],
            1  => ['NEUTROPHILE %', '-'],
            2  => ['G.ROUGES', '4 à 6 T/l'],
            3  => ['EOSINOPHILE %', '-'],
            4  => ['HEMOGLOBINE', '12 à 16 g/dl'],
            5  => ['BASOPHILE %', '-'],
            6  => ['HEMATOCRITE', '35 à 45 %'],
            7  => ['LYMPHOCYTE %', '-'],
            8  => ['VGM', '80 à 90 FL'],
            9  => ['MONOCYTE %', '-'],
            10 => ['CCMH', '30 à 36 g/dl'],
            11 => ['PLAQUETTES', '-'],
            12 => ['TCMH', '25 à 32 pg'],
            13 => ['GE', '-'],
        ];
    }

    /**
     * Nettoie le HTML venu des cellules éditables : on ne garde que
     * gras / italique / souligné / retour à la ligne.
     */
    public static function clean(mixed $value): string
    {
        $v = (string) $value;
        $v = str_replace(['&nbsp;', "\xC2\xA0"], ' ', $v);
        $v = str_ireplace('</div>', '', $v);
        $v = preg_replace('#<div[^>]*>#i', '<br>', $v);
        $v = strip_tags($v, '<b><i><u><br><strong><em>');
        $v = preg_replace('#<(b|i|u|strong|em)\s[^>]*>#i', '<$1>', $v);

        if (trim(strip_tags($v)) === '') {
            return '';
        }
        return trim($v);
    }
}