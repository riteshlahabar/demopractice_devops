<?php

namespace App\Support;

/**
 * SRP: one place that decides what a mobile number looks like.
 *
 * Firebase hands back E.164 (+919876543210) while the apps and every existing
 * users.mobile row hold a bare ten digit number, so without a single
 * normaliser the same person would be matched as two different accounts.
 */
final class MobileNumber
{
    public const DEFAULT_COUNTRY_CODE = '91';

    private const LOCAL_LENGTH = 10;

    /**
     * The storage form: the last ten digits, country code and punctuation
     * stripped.
     */
    public static function normalise(?string $mobile): string
    {
        $digits = preg_replace('/\D+/', '', (string) $mobile) ?? '';

        if (strlen($digits) <= self::LOCAL_LENGTH) {
            return $digits;
        }

        return substr($digits, -self::LOCAL_LENGTH);
    }

    /**
     * The form Firebase and any SMS provider expect.
     */
    public static function e164(?string $mobile, string $countryCode = self::DEFAULT_COUNTRY_CODE): string
    {
        $local = self::normalise($mobile);

        return $local === '' ? '' : '+'.$countryCode.$local;
    }

    /**
     * Every spelling of a number that may already sit in users.mobile. Legacy
     * rows were written straight from the app, so a lookup has to match them
     * all rather than assume one format.
     */
    public static function lookupVariants(?string $mobile, string $countryCode = self::DEFAULT_COUNTRY_CODE): array
    {
        $local = self::normalise($mobile);

        if ($local === '') {
            return [];
        }

        return array_values(array_unique([
            $local,
            $countryCode.$local,
            '+'.$countryCode.$local,
        ]));
    }

    public static function matches(?string $first, ?string $second): bool
    {
        $left = self::normalise($first);

        return $left !== '' && $left === self::normalise($second);
    }
}
