<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting;

use Exception;
use Icinga\Module\Reporting\Hook\ActionHook;
use Icinga\Util\Json;
use ipl\Scheduler\Common\TaskProperties;
use ipl\Scheduler\Contract\Task;
use Ramsey\Uuid\Uuid;
use React\EventLoop\Loop;
use React\Promise;
use React\Promise\ExtendedPromiseInterface;

use function md5;

class Schedule implements Task
{
    use TaskProperties;

    /** @var int */
    protected $id;

    /** @var string */
    protected $action;

    /** @var array */
    protected $config = [];

    /** @var Report */
    protected $report;

    public function __construct(string $name, string $action, array $config, Report $report)
    {
        $this->action = $action;
        $this->config = $config;
        ksort($this->config);

        $this
            ->setName($name)
            ->setReport($report)
            ->setUuid(Uuid::fromBytes($this->getChecksum()));
    }

    /**
     * Create schedule from the given model
     *
     * @param Model\Schedule $scheduleModel
     *
     * @return static
     */

    public static function fromModel(Model\Schedule $scheduleModel, Report $report): self
    {
        $config = Json::decode($scheduleModel->config ?? [], true);
        $schedule = new static("Schedule{$scheduleModel->id}", $scheduleModel->action, $config, $report);
        $schedule->id = $scheduleModel->id;

        return $schedule;
    }

    /**
     * Get the id of this schedule
     *
     * @return  int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the action hook class of this schedule
     *
     * @return  string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Get the config of this schedule
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get the report this schedule belongs to
     *
     * @return Report
     */
    public function getReport(): Report
    {
        return $this->report;
    }

    /**
     * Set the report this schedule belongs to
     *
     * @param Report $report
     *
     * @return $this
     */
    public function setReport(Report $report): self
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get the checksum of this schedule
     *
     * @return  string
     */
    public function getChecksum(): string
    {
        return md5(
            $this->getName() . $this->getReport()->getName() . $this->getAction() . Json::encode($this->getConfig()),
            true
        );
    }

    public function run(): ExtendedPromiseInterface
    {
        $deferred = new Promise\Deferred();
        Loop::futureTick(function () use ($deferred) {
            $action = $this->getAction();
            /** @var ActionHook $actionHook */
            $actionHook = new $action();

            try {
                $actionHook->execute($this->getReport(), $this->getConfig());
            } catch (Exception $err) {
                $deferred->reject($err);

                return;
            }

            $deferred->resolve();
        });

        /** @var ExtendedPromiseInterface $promise */
        $promise = $deferred->promise();

        return $promise;
    }
}
