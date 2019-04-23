<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting;

use ipl\Sql\Select;

class Timeframe
{
    use Database;

    /** @var int */
    protected $id;

    /** @var string */
    protected $name;

    /** @var string */
    protected $title;

    /** @var string */
    protected $start;

    /** @var string */
    protected $end;

    /**
     * @param   int $id
     *
     * @return  static
     *
     * @throws  \Exception
     */
    public static function fromDb($id)
    {
        $timeframe = new static();

        $db = $timeframe->getDb();

        $select = (new Select())
            ->from('timeframe')
            ->columns('*')
            ->where(['id = ?' => $id]);

        $row = $db->select($select)->fetch();

        if ($row === false) {
            throw new \Exception('Timeframe not found');
        }

        $timeframe
            ->setId($row->id)
            ->setName($row->name)
            ->setTitle($row->title)
            ->setStart($row->start_time)
            ->setEnd($row->end_time);

        return $timeframe;
    }

    /**
     * @return  int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param   int $id
     *
     * @return  $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param   string  $name
     *
     * @return  $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return  string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param   string  $title
     *
     * @return  $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return  string
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param   string  $start
     *
     * @return  $this
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * @return  string
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param   string  $end
     *
     * @return  $this
     */
    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }

    public function getTimerange()
    {
        $start = new \DateTime($this->getStart());
        $end = new \DateTime($this->getEnd());

        return new Timerange($start, $end);
    }
}
