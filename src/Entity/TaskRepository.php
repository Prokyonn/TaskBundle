<?php

/*
 * This file is part of php-task library.
 *
 * (c) php-task
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Task\TaskBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Task\Storage\TaskRepositoryInterface;
use Task\TaskInterface;

/**
 * Repository for task.
 */
class TaskRepository extends EntityRepository implements TaskRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create($handlerClass, $workload = null)
    {
        return new Task($handlerClass, $workload);
    }

    /**
     * {@inheritdoc}
     */
    public function findByUuid($uuid)
    {
        return $this->find($uuid);
    }

    /**
     * {@inheritdoc}
     */
    public function save(TaskInterface $task)
    {
        $this->_em->persist($task);
        $this->_em->flush($task);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(TaskInterface $task)
    {
        $this->_em->remove($task);
        $this->_em->flush($task);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll($page = 1, $pageSize = null)
    {
        $query = $this->createQueryBuilder('t')
            ->getQuery();

        if ($pageSize) {
            $query->setMaxResults($pageSize);
            $query->setFirstResult(($page - 1) * $pageSize);
        }

        return $query->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findEndBeforeNow()
    {
        return $this->findEndBefore(new \DateTime());
    }

    /**
     * Returns task where last-execution is before given date-time.
     *
     * @param \DateTime $dateTime
     *
     * @return TaskInterface[]
     */
    public function findEndBefore(\DateTime $dateTime)
    {
        return $this->createQueryBuilder('t')
            ->where('t.lastExecution IS NULL OR t.lastExecution > :dateTime')
            ->setParameter('dateTime', $dateTime)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns task identified by system-key.
     *
     * @param string $systemKey
     *
     * @return TaskInterface
     */
    public function findBySystemKey($systemKey)
    {
        try {
            return $this->createQueryBuilder('t')
                ->where('t.systemKey = :systemKey')
                ->setParameter('systemKey', $systemKey)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $exception) {
            return;
        }
    }

    /**
     * Returns all system-task.
     *
     * @return TaskInterface[]
     */
    public function findSystemTasks()
    {
        return $this->createQueryBuilder('t')
            ->where('t.systemKey IS NOT NULL')
            ->getQuery()
            ->getResult();
    }
}
