<?php

/* Icinga Reporting | (c) 2023 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Reporting\Model;

use ipl\Orm\Behavior\MillisecondTimestamp;
use ipl\Orm\Behaviors;
use ipl\Orm\Model;
use ipl\Orm\Relations;

class Report extends Model
{
    public function getTableName()
    {
        return 'report';
    }

    public function getKeyName()
    {
        return 'id';
    }

    public function getColumns()
    {
        return [
            'timeframe_id',
            'template_id',
            'author',
            'name',
            'ctime',
            'mtime'
        ];
    }

    public function getDefaultSort()
    {
        return ['name'];
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
        $relations->belongsTo('timeframe', Timeframe::class);
        $relations->belongsTo('template', Template::class)
            ->setJoinType('LEFT');

        $relations->hasOne('schedule', Schedule::class)
            ->setJoinType('LEFT');
        $relations->hasMany('reportlets', Reportlet::class);
    }
}
