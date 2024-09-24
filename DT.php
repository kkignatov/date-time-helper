<?php

namespace App\Common;

use App\Exceptions\ServerException;
use DateTimeImmutable;
use Exception;
use Carbon\Carbon;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use function config;
use function now;
use function today;

/**
 * Date/Time helper class
 */
class DT
{
    const DATE_TIME_FORMAT = 'Y-m-d\TH:i:s.u\Z';
    const LEGACY_DATE_TIME_FORMAT = 'Y-m-d\TH:i:s\Z';
    const DATE_FORMAT = 'Y-m-d';
    const LOCAL_TIME_ZONE = "Europe/Amsterdam";
    const TIME_FORMAT = "H:i";

    /**
     * Returns the current date and time in the application's default timezone (e.g. from config/app.timezone).
     *
     * @return Carbon
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function now(): Carbon
    {
        return now(config('app.timezone'));
    }

    /**
     * Returns the current date in the application's default timezone (e.g. from config/app.timezone).
     * @return Carbon
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function today(): Carbon
    {
        return today(config('app.timezone'));
    }

    /**
     * Creates a copy of the $date parameter and adds the number of days given by the $days parameter.
     *
     * @param Carbon $date The date to initialize the new Carbon object from.
     * @param int $days The number of days to add.
     * @return Carbon Returns a new Carbon instance.
     */
    public static function addDaysToCopy(Carbon $date, int $days): Carbon
    {
        return $date->copy()->addDays($days);
    }

    /**
     * Creates a new Carbon instance from a timestamp.
     *
     * @param int $timestamp The timestamp.
     * @return Carbon Returns a new Carbon instance.
     */
    public static function fromTimestamp(int $timestamp): Carbon
    {
        return Carbon::createFromTimestamp($timestamp);
    }

    /**
     * Parses a date string in the format Y-m-d and returns a new Carbon object in the application's timezone.
     * @param string $string The string to parse.
     * @return Carbon
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public static function fromDateString(string $string): Carbon
    {
        $date = self::fromString($string, self::DATE_FORMAT);

        if ($date) {
            return self::fromString($string, self::DATE_FORMAT)->startOfDay();
        }

        throw new Exception("Invalid date/time string value. Can not parse date string.");
    }

    /**
     * Parses a date-time string given in the format Y-m-d\TH:i:s.u\Z in the application's timezone.
     *
     * @param string $string The string to parse.
     * @return Carbon|false
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @noinspection PhpUnused
     */
    public static function fromISOString(string $string): bool|Carbon
    {
        return self::fromString($string, self::DATE_TIME_FORMAT);
    }

    /**
     * @param string $string
     * @return false|Carbon
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function fromLegacyISOString(string $string): bool|Carbon
    {
        return self::fromString($string, self::LEGACY_DATE_TIME_FORMAT);
    }

    /**
     * Parses a date-time string given in the format Y-m-d\TH:i:s.u\Z in the local timezone defined by the
     * LOCAL_TIME_ZONE constant. Then converts the value to the application's timezone.
     *
     * @param string $string The string to parse.
     * @return Carbon|false
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function fromLocalISOString(string $string): bool|Carbon
    {
        return Carbon::createFromFormat(self::DATE_TIME_FORMAT, $string, self::LOCAL_TIME_ZONE)->timezone(config('app.timezone'));
    }

    /**
     * Creates a Carbon object from a string. If the string contains no timezone information this method assumes
     * local timezone.
     *
     * @param string $time The string representation of the date/time value
     * @return Carbon
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function fromLocalString(string $time): Carbon
    {
        $result = new Carbon($time, self::LOCAL_TIME_ZONE);
        $result->setTimezone(config('app.timezone'));
        return $result;
    }

    /**
     * Parses a localized string, e.g. Feb 02, 2022 CET 00:00 and returns a Carbon object in the application's time zone (UTC).
     *
     * @param string $string The string to be parsed.
     * @return Carbon Returns a Carbon object if the parsing was successful.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function parseLocalString(string $string): Carbon
    {
        return Carbon::parseFromLocale($string)->timezone(config('app.timezone'));
    }

    /**
     * Create a new Carbon instance from an existing Carbon instance
     * @param Carbon $dateTime
     * @return Carbon
     */
    public static function createFromCarbon(Carbon $dateTime): Carbon
    {
        $result = new Carbon(null, $dateTime->getTimezone());
        $result->setTimestamp($dateTime->getTimestamp());
        return $result;
    }

    /**
     * Creates a copy in the local timezone, takes the start of the day and returns it in the application's timezone.
     *
     * @param Carbon $value A Carbon object (may include time info) indicating a date in the local timezone and convert
     * it to the application's timezone.
     *
     * @return Carbon Returns a new Carbon instance in the application's timezone.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function startOfDayUtc(Carbon $value): Carbon
    {
        return $value->copy()->timezone(self::LOCAL_TIME_ZONE)->startOfDay()->timezone(config('app.timezone'));
    }

    /**
     * Creates a DateTime object in the INTERNAL_TIME_ZONE.
     *
     * @param string $time A string defining the time for initialization. Could
     * be a formatted date/time string or any of the string literals supported
     * by the Carbon/DateTime constructor. Must be provided in UTC. If the timezone is
     * specified in the string it should be either "Z", "+00:00" or "UTC".
     * Otherwise, an exception is thrown.
     *
     * @return Carbon
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ServerException Throws an exception if the $time parameter
     * contains the timezone and if it differs from "Z", "+00:00" or "UTC".
     */
    public static function fromUTCString(string $time): Carbon
    {
        $result = new Carbon($time, config('app.timezone'));
        $timezone = $result->getTimezone()->getName();

        if (!empty($timezone) && ($timezone !== "Z" && $timezone !== "UTC" && $timezone !== "+00:00")) {
            throw new ServerException("The date/time provided must be in the UTC but is in '$timezone' timezone.");
        }

        return $result;
    }

    /**
     * @return int
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function timestampNowInMilliseconds(): int
    {
        $now = new Carbon("now", config('app.timezone'));
        $time = $now->setDate(1970, 01, 01);
        return $time->getTimestamp() * 1000;
    }

    /**
     * @return int
     */
    public static function localTimestampNowInMilliseconds(): int
    {
        $now = new Carbon("now", self::LOCAL_TIME_ZONE);
        $time = $now->setDate(1970, 01, 01);
        return $time->getTimestamp() * 1000;
    }

    /**
     * Parses string date-time info.
     *
     * @param string $string The string to parse.
     * @param string $format The date/time format that is expected.
     * @return Carbon|false
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function fromString(string $string, string $format): bool|Carbon
    {
        return Carbon::createFromFormat($format, $string, config('app.timezone'));
    }

    /**
     * Formats a Carbon instance based on the DATE_TIME_FORMAT.
     *
     * @param Carbon $dateTime The object to be formatted.
     * @return string
     */
    public static function toString(Carbon $dateTime): string
    {
        return $dateTime->format(self::DATE_TIME_FORMAT);
    }

    /**
     * Formats a Carbon instance based on the LEGACY_DATE_TIME_FORMAT.
     *
     * @param Carbon $dateTime The object to be formatted.
     * @return string
     */
    public static function toLegacyString(Carbon $dateTime): string
    {
        return $dateTime->format(self::LEGACY_DATE_TIME_FORMAT);
    }

    /**
     * Formats a date according to the DATE_FORMAT format. Does not include the time in the output string.
     * @param Carbon $dateTime The date/time to be formatted.
     * @return string Returns a string containing the date in the DATE_FORMAT format ("Y-m-d").
     */
    public static function toDateString(Carbon $dateTime): string
    {
        return $dateTime->format(self::DATE_FORMAT);
    }

    /**
     * Formats a date/time Carbon object to a local time string as 'H:i' (e.g. '10:23').
     * @param Carbon $dateTime The date/time to be formatted.
     * @return string
     */
    public static function toLocalTimeString(Carbon $dateTime): string
    {
        return self::formatToLocal( $dateTime, self::TIME_FORMAT);
    }

    public static function toStringForPlanon(Carbon $dateTime): string
    {
        return self::formatToLocal($dateTime, "c");
    }

    public static function isToday(Carbon $value): bool
    {
        return $value->isToday();
    }

    /**
     * @param Carbon $value
     * @param int $atLeastDays
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function isFuture(Carbon $value, int $atLeastDays): bool
    {
        $d = DateTimeImmutable::createFromMutable($value)->setTime(0, 0);
        $now = self::now()->setTime(0, 0);

        if ($value->isPast()) {
            return false;
        }

        return $d->diff($now)->days > $atLeastDays;
    }

    private static function formatToLocal(Carbon $dateTime, string $format): string
    {
        $c = new Carbon($dateTime);
        $c->setTimezone(self::LOCAL_TIME_ZONE);
        return $c->format($format);
    }
}
