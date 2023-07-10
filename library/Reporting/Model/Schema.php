<?php

/* Icinga Reporting | (c) 2023 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Reporting\Model;

use ipl\Orm\Behavior\BoolCast;
use ipl\Orm\Behavior\MillisecondTimestamp;
use ipl\Orm\Behaviors;
use ipl\Orm\Model;

class Schema extends Model
{
    public function getTableName(): string
    {
        return 'reporting_schema';
    }

    public function getKeyName()
    {
        return 'id';
    }

    public function getColumns(): array
    {
        return [
            'version',
            'timestamp',
            'success',
            'reason'
        ];
    }

    public function createBehaviors(Behaviors $behaviors): void
    {
        $behaviors->add(new BoolCast(['success']));
        $behaviors->add(new MillisecondTimestamp(['timestamp']));
    }
}
