<?php

namespace GrumPHP\Runner;

use GrumPHP\Exception\FailureException;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Finder\FinderFactory;
use GrumPHP\Task\TaskInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class TaskRunner
 *
 * @package GrumPHP\Runner
 */
class TaskRunner
{

    /**
     * @var FinderFactory
     */
    protected $finderFactory;

    /**
     * @var array
     */
    protected $tasks = array();

    /**
     * @param FinderFactory $finderFactory
     */
    public function __construct(FinderFactory $finderFactory)
    {
        $this->finderFactory = $finderFactory;
    }

    /**
     * @param TaskInterface $task
     *
     * @return $this
     */
    public function addTask(TaskInterface $task)
    {
        if (in_array($task, $this->tasks)) {
            return $this;
        }

        $this->tasks[] = $task;

        return $this;
    }

    /**
     * @return TaskInterface[]
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @param Finder $files
     *
     * @throws FailureException if any of the tasks fail
     */
    public function run(Finder $files)
    {
        $failures = false;
        $messages = array();

        foreach ($this->getTasks() as $task) {
            try {
                $finder = $this->finderFactory->create($files);
                $task->run($finder);
            } catch (RuntimeException $e) {
                $failures = true;
                $messages[] = $e->getMessage();
            }
        }

        if ($failures) {
            throw new FailureException(implode(PHP_EOL, $messages));
        }
    }
}