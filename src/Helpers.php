<?php

namespace SoapBox\SignedRequests;

use DateTime;

class Helpers
{
    /**
     * Verifies if the provided date string is in the given format
     *
     * @param string $datetime
     * @param string $format
     *
     * @return boolean
     */
    public static function verifyDateTime(string $datetime, string $format): bool
    {
        $formatted = DateTime::createFromFormat($format, $datetime);

        $errors = DateTime::getLastErrors();

        if (!empty($errors['warning_count'])) {
            return false;
        }

        return $formatted !== false;
    }
}
