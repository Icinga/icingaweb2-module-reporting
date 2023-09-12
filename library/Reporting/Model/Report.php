<?php

/* Icinga Reporting | (c) 2023 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Reporting\Model;

use DateTime;
use ipl\Orm\Behavior\MillisecondTimestamp;
use ipl\Orm\Behaviors;
use ipl\Orm\Model;
use ipl\Orm\Relations;

/**
 * A Report database model
 *
 * @property int $id Unique identifier of this model
 * @property int $timeframe_id The timeframe id used by this report
 * @property int $template_id The template id used by this report
 * @property string $author The author of this report
 * @property string $name The name of this report
 * @property DateTime $ctime The creation time of this report
 * @property DateTime $mtime Modify time of this report
 */
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
