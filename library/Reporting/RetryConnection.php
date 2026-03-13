<?php

// SPDX-FileCopyrightText: 2019 Icinga GmbH <https://icinga.com>
// SPDX-License-Identifier: GPL-3.0-or-later

namespace Icinga\Module\Reporting;

use ipl\Sql\Connection;
use PDOStatement;

class RetryConnection extends Connection
{
    public function prepexec($stmt, $values = null): false|PDOStatement
    {
        try {
            $sth = parent::prepexec($stmt, $values);
        } catch (\Exception $e) {
            $lostConnection = Str::contains($e->getMessage(), [
                'server has gone away',
                'no connection to the server',
                'Lost connection',
                'Error while sending',
                'is dead or not enabled',
                'decryption failed or bad record mac',
                'server closed the connection unexpectedly',
                'SSL connection has been closed unexpectedly',
                'Error writing data to the connection',
                'Resource deadlock avoided',
                'Transaction() on null',
                'child connection forced to terminate due to client_idle_limit',
                'query_wait_timeout',
                'reset by peer',
                'Physical connection is not usable',
                'TCP Provider: Error code 0x68',
                'ORA-03114',
                'Packets out of order. Expected',
                'Adaptive Server connection failed',
                'Communication link failure',
            ]);

            if (! $lostConnection) {
                throw $e;
            }

            $this->disconnect();

            try {
                $this->connect();
            } catch (\Exception $e) {
                $noConnection = Str::contains($e->getMessage(), [
                    'No such file or directory',
                    'Connection refused'
                ]);

                if (! $noConnection) {
                    throw $e;
                }

                \sleep(10);

                $this->connect();
            }

            $sth = parent::prepexec($stmt, $values);
        }

        return $sth;
    }
}
