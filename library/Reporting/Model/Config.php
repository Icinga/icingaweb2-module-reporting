<?php

/* Icinga Reporting | (c) 2023 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Reporting\Model;

use ipl\Orm\Behavior\MillisecondTimestamp;
use ipl\Orm\Behaviors;
use ipl\Orm\Model;
use ipl\Orm\Relations;

class Config extends Model
{
    public function getTableName()
    {
        return 'config';
    }

    public function getKeyName()
    {
        return 'id';
    }

    public function getColumns()
    {
        return [
            'reportlet_id',
            'name',
            'value',
            'ctime',
            'mtime'
        ];
    }

    public function createBehaviors(Behaviors $behaviors)
    {
        $behaviors->add(new MillisecondTimestamp([
            'ctime',
            'mtime'
        ]));
    }

    public function createRelations(Relations $relations)
    {
        $relations->belongsTo('reportlet', Reportlet::class);
    }
}
