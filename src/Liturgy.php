<?php

declare(strict_types=1);

namespace App;

use DateTimeImmutable;

/**
 * Règles liturgiques indépendantes de l'API.
 */
final class Liturgy
{
    /**
     * Date à interroger dans l'API AELF pour une messe donnée.
     *
     * - Samedi à partir de 15h00 : messe anticipée du dimanche -> on prend le lendemain.
     * - Sinon : le jour de la messe, quelle que soit l'heure.
     */
    public static function dateLiturgique(DateTimeImmutable $messe): DateTimeImmutable
    {
        $estSamedi = (int) $messe->format('N') === 6;
        $apres15h = ((int) $messe->format('H')) >= 15;

        if ($estSamedi && $apres15h) {
            return $messe->modify('+1 day');
        }

        return $messe;
    }
}
