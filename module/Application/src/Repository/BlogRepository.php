<?php
namespace Application\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * This is the custom repository class for Blog entity.
 */
class BlogRepository extends EntityRepository
{
    public function findTest()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        var_dump($queryBuilder->getQuery());
        exit;

        echo $queryBuilder->getQuery()->getSQL();
    }
}