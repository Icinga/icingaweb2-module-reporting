<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Clicommands;

use DateTime;
use Exception;
use Icinga\Application\Config;
use Icinga\Application\Logger;
use Icinga\Data\ResourceFactory;
use Icinga\Module\Reporting\Cli\Command;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Model;
use Icinga\Module\Reporting\Report;
use Icinga\Module\Reporting\Schedule;
use ipl\Scheduler\Contract\Frequency;
use ipl\Scheduler\Contract\Task;
use ipl\Scheduler\Scheduler;
use React\EventLoop\Loop;
use React\Promise\ExtendedPromiseInterface;
use Throwable;

class ScheduleCommand extends Command
{
    /**
     * Run all configured reports based on their schedule
     *
     * USAGE:
     *
     *   icingacli reporting schedule run
     */
    public function runAction()
    {
        $scheduler = new Scheduler();
        $this->attachJobsLogging($scheduler);

        /** @var Schedule[] $runningSchedules */
        $runningSchedules = [];
        // Check for configuration changes every 5 minutes to make sure new jobs are scheduled, updated and deleted
        // jobs are cancelled.
        $watchdog = function () use (&$watchdog, $scheduler, &$runningSchedules) {
            $schedules = [];
            try {
                // Since this is a long-running daemon, the resources or module config may change meanwhile.
                // Therefore, reload the resources and module config from disk each time (at 5m intervals)
                // before reconnecting to the database.
                ResourceFactory::setConfig(Config::app('resources', true));
                Config::module('reporting', 'config', true);

                $schedules = $this->fetchSchedules();
            } catch (Throwable $err) {
                Logger::error('Failed to fetch report schedules from the database: %s', $err);
                Logger::debug($err->getTraceAsString());
            }

            $outdated = array_diff_key($runningSchedules, $schedules);
            foreach ($outdated as $schedule) {
                Logger::info(
                    'Removing %s as it either no longer exists in the database or its config has been changed',
                    $schedule->getName()
                );

                $scheduler->remove($schedule);

                unset($runningSchedules[$schedule->getUuid()->toString()]);
            }

            $newSchedules = array_diff_key($schedules, $runningSchedules);
            foreach ($newSchedules as $key => $schedule) {
                $config = $schedule->getConfig();
                $frequency = $config['frequency'];

                try {
                    /** @var Frequency $type */
                    $type = $config['frequencyType'];
                    $frequency = $type::fromJson($frequency);
                } catch (Exception $err) {
                    Logger::error(
                        '%s has invalid schedule expression %s: %s',
                        $schedule->getName(),
                        $frequency,
                        $err->getMessage()
                    );

                    continue;
                }

                $scheduler->schedule($schedule, $frequency);

                $runningSchedules[$key] = $schedule;
            }

            Loop::addTimer(5 * 60, $watchdog);
        };
        Loop::futureTick($watchdog);
    }

    /**
     * Fetch schedules from the database
     *
     * @return Schedule[]
     */
    protected function fetchSchedules(): array
    {
        $schedules = [];
        $query = Model\Schedule::on(Database::get())->with(['report.timeframe', 'report']);

        foreach ($query as $schedule) {
            $schedule = Schedule::fromModel($schedule, Report::fromModel($schedule->report));
            $schedules[$schedule->getUuid()->toString()] = $schedule;
        }

        return $schedules;
    }

    protected function attachJobsLogging(Scheduler $scheduler)
    {
        $scheduler->on(Scheduler::ON_TASK_FAILED, function (Task $job, Throwable $e) {
            Logger::error('Failed to run job %s: %s', $job->getName(), $e->getMessage());
            Logger::debug($e->getTraceAsString());
        });

        $scheduler->on(Scheduler::ON_TASK_RUN, function (Task $job, ExtendedPromiseInterface $_) {
            Logger::info('Running job %s', $job->getName());
        });

        $scheduler->on(Scheduler::ON_TASK_SCHEDULED, function (Task $job, DateTime $dateTime) {
            Logger::info('Scheduling job %s to run at %s', $job->getName(), $dateTime->format('Y-m-d H:i:s'));
        });

        $scheduler->on(Scheduler::ON_TASK_EXPIRED, function (Task $task, DateTime $dateTime) {
            Logger::info(
                sprintf('Detaching expired job %s at %s', $task->getName(), $dateTime->format('Y-m-d H:i:s'))
            );
        });
    }
}
