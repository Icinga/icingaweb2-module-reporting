<?php
// Icinga Reporting | (c) 2020 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting;

use Icinga\Module\Monitoring\Backend\MonitoringBackend;
use ipl\Sql\Select;

class Downtime
{
    use Database;

    /** @var int */
    protected $id;

    /** @var int */
    protected $object_id;

    /** @var string */
    protected $start_time;

    /** @var string */
    protected $end_time;

    public static function fromDb($id)
    {
        $downtime = new static();

        $resource = MonitoringBackend::instance()->getName();

        $db = $downtime->getDb($resource);

        $select = (new Select())
            ->from('icinga_reporting_fake_downtime')
            ->columns('*')
            ->where(['id = ?' => $id]);

        $row = $db->select($select)->fetch();

        if ($row === false) {
            throw new \Exception('Downtime not found');
        }

        $downtime
            ->setId($row->id)
            ->setObjectId($row->object_id)
            ->setStartTime($row->start_time)
            ->setEndTime($row->end_time);

        return $downtime;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->object_id;
    }

    /**
     * @param int $object_id
     *
     * @return Downtime
     */
    public function setObjectId($object_id)
    {
        $this->object_id = $object_id;

        return $this;
    }

    /**
     * @return string
     */
    public function getStartTime()
    {
        return $this->start_time;
    }

    /**
     * @param string $start_time
     *
     * @return $this
     */
    public function setStartTime($start_time)
    {
        $this->start_time = $start_time;

        return $this;
    }

    /**
     * @return string
     */
    public function getEndTime()
    {
        return $this->end_time;
    }

    /**
     * @param string $end_time
     *
     * @return $this
     */
    public function setEndTime($end_time)
    {
        $this->end_time = $end_time;

        return $this;
    }
}
