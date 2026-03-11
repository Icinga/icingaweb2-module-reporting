<?php

// SPDX-FileCopyrightText: 2019 Icinga GmbH <https://icinga.com>
// SPDX-License-Identifier: GPL-3.0-or-later

namespace Icinga\Module\Reporting;

class Str
{
    public static function putcsv(array $data, $delimiter = ',', $enclosure = '"', $escape = '\\')
    {
        /** @var resource $fp */
        $fp = fopen('php://temp', 'r+b');

        foreach ($data as $row) {
            fputcsv($fp, $row, $delimiter, $enclosure, $escape);
        }

        rewind($fp);

        /** @var string $csv */
        $csv = stream_get_contents($fp);

        fclose($fp);

        $csv = rtrim($csv, "\n"); // fputcsv adds a newline

        return $csv;
    }

    public static function contains($haystack, $needle)
    {
        foreach ((array) $needle as $n) {
            if (\strpos($haystack, $n) !== false) {
                return true;
            }
        }

        return false;
    }
}
