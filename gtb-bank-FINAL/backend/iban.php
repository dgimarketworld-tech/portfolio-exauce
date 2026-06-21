<?php
/**
 * ════════════════════════════════════════════════════════════════
 *  GTB BANK — Générateur et validateur d'IBAN
 *  Implémente l'algorithme MOD-97 (ISO 13616 / norme bancaire).
 * ════════════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/config.php';

final class IBAN
{
    /** Longueurs IBAN par pays (les plus courants). */
    private const LENGTHS = [
        'FR' => 27, 'BE' => 16, 'DE' => 22, 'ES' => 24, 'IT' => 27,
        'PT' => 25, 'NL' => 18, 'LU' => 20, 'CH' => 21, 'GB' => 22,
        'MC' => 27, 'PL' => 28, 'AT' => 20, 'GR' => 27, 'IE' => 22,
    ];

    /**
     * Génère un IBAN français complet à partir du numéro de compte.
     *
     * Structure FR :
     * FR + 2 (clé) + 5 (banque) + 5 (guichet) + 11 (compte) + 2 (RIB key)
     *
     * @param string $accountNumber Numéro de compte (11 caractères, alpha-numérique)
     */
    public static function generateFR(string $accountNumber): string
    {
        $accountNumber = str_pad(strtoupper($accountNumber), 11, '0', STR_PAD_LEFT);
        $accountNumber = substr($accountNumber, 0, 11);

        $bankCode   = GTB_BANK_CODE;    // 5 chiffres
        $branchCode = GTB_BRANCH_CODE;  // 5 chiffres

        // Clé RIB (norme française, 2 chiffres)
        $ribKey = self::computeRibKey($bankCode, $branchCode, $accountNumber);

        $bban = $bankCode . $branchCode . $accountNumber . str_pad($ribKey, 2, '0', STR_PAD_LEFT);

        // Clé IBAN (MOD-97)
        $ibanCheck = self::computeIbanCheck('FR', $bban);

        return 'FR' . str_pad($ibanCheck, 2, '0', STR_PAD_LEFT) . $bban;
    }

    /**
     * Calcule la clé RIB (norme française, 97 - (X mod 97)).
     */
    private static function computeRibKey(string $bank, string $branch, string $account): int
    {
        // Convertit les lettres en chiffres selon table RIB
        $convert = function(string $s): string {
            $map = [
                'A'=>'1','B'=>'2','C'=>'3','D'=>'4','E'=>'5','F'=>'6','G'=>'7','H'=>'8','I'=>'9',
                'J'=>'1','K'=>'2','L'=>'3','M'=>'4','N'=>'5','O'=>'6','P'=>'7','Q'=>'8','R'=>'9',
                'S'=>'2','T'=>'3','U'=>'4','V'=>'5','W'=>'6','X'=>'7','Y'=>'8','Z'=>'9',
            ];
            return strtr(strtoupper($s), $map);
        };

        $num = $convert($bank) . $convert($branch) . $convert($account) . '00';
        return 97 - self::bcmod97($num);
    }

    /**
     * Calcule les 2 chiffres de contrôle IBAN selon ISO 13616.
     * IBAN check = 98 - (BBAN+CountryCode+'00' MOD 97)
     */
    private static function computeIbanCheck(string $country, string $bban): int
    {
        $rearranged = $bban . self::countryToDigits($country) . '00';
        return 98 - self::bcmod97($rearranged);
    }

    /** Convertit les lettres (A=10, B=11, …, Z=35). */
    private static function countryToDigits(string $country): string
    {
        $out = '';
        foreach (str_split(strtoupper($country)) as $ch) {
            $out .= ctype_alpha($ch) ? (string)(ord($ch) - 55) : $ch;
        }
        return $out;
    }

    /**
     * Calcule MOD 97 d'un grand nombre (en string).
     * Compatible sans extension BC math.
     */
    private static function bcmod97(string $number): int
    {
        $remainder = 0;
        $len = strlen($number);
        for ($i = 0; $i < $len; $i++) {
            $remainder = ($remainder * 10 + (int)$number[$i]) % 97;
        }
        return $remainder;
    }

    /**
     * Valide un IBAN (longueur + checksum).
     */
    public static function validate(string $iban): bool
    {
        $iban = self::normalize($iban);
        if (strlen($iban) < 15 || strlen($iban) > 34) return false;

        $country = substr($iban, 0, 2);
        if (isset(self::LENGTHS[$country]) && strlen($iban) !== self::LENGTHS[$country]) {
            return false;
        }
        if (!preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]+$/', $iban)) return false;

        $rearranged = substr($iban, 4) . self::countryToDigits($country) . substr($iban, 2, 2);
        $digits = '';
        foreach (str_split($rearranged) as $ch) {
            $digits .= ctype_alpha($ch) ? (string)(ord($ch) - 55) : $ch;
        }
        return self::bcmod97($digits) === 1;
    }

    /** Supprime espaces et passe en majuscules. */
    public static function normalize(string $iban): string
    {
        return strtoupper(preg_replace('/\s+/', '', $iban));
    }

    /** Affiche un IBAN avec espacement standard (groupes de 4). */
    public static function format(string $iban): string
    {
        $iban = self::normalize($iban);
        return trim(chunk_split($iban, 4, ' '));
    }

    /**
     * Masque un IBAN pour affichage public (4 premiers + 4 derniers).
     * Ex: FR76 **** **** **** **** **** 123
     */
    public static function mask(string $iban): string
    {
        $iban = self::normalize($iban);
        if (strlen($iban) < 8) return $iban;
        $start = substr($iban, 0, 4);
        $end   = substr($iban, -4);
        $stars = str_repeat('•', strlen($iban) - 8);
        return trim(chunk_split($start . $stars . $end, 4, ' '));
    }

    /**
     * Génère un numéro de compte interne unique (11 caractères).
     * Format : 8 chiffres aléatoires + 3 chiffres timestamp.
     */
    public static function generateAccountNumber(): string
    {
        $rand = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        $ts   = substr((string) time(), -3);
        return $rand . $ts;
    }
}