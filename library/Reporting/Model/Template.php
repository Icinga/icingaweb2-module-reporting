<?php

/* Icinga Reporting | (c) 2023 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Reporting\Model;

use ipl\Orm\Behavior\MillisecondTimestamp;
use ipl\Orm\Behaviors;
use ipl\Orm\Model;
use ipl\Orm\Relations;

class Template extends Model
{
    public function getTableName()
    {
        return 'template';
    }

    public function getKeyName()
    {
        return 'id';
    }

    public function getColumns()
    {
        return [
            'author',
            'name',
            'settings',
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
        $relations->hasMany('report', Report::class)
            ->setJoinType('LEFT');
    }
}
