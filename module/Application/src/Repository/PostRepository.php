<?php
namespace Application\Repository;

use Application\Entity\Post;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Zend\Paginator\Paginator;

/**
 * This is the custom repository class for Post entity.
 */
class PostRepository extends EntityRepository
{
    public $defaultOrder = ['dateCreated', 'DESC'];
    public $tagFilter = [];
    public $page = null;

    public function findPostsSite()
    {
        if ($this->tagFilter) {
            $query = $this->findPostsByTag($this->tagFilter);
        } else {
            $query = $this->findPosts();
        }

        //$query->andWhere($query->expr()->eq('p.status', ':status'));
        //$query->setParameter(':status', Post::STATUS_PUBLISHED, \Doctrine\DBAL\Types\Type::INTEGER);

        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);
        if ($this->page > 0) {
            $paginator->setCurrentPageNumber($this->page);
        }

        return $paginator;
    }

    /**
     * Retrieves all published posts in descending date order.
     * @return Query
     */
    public function findPosts()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('p');
        $queryBuilder->from(Post::class, 'p');

        if ($this->defaultOrder) {
            $queryBuilder->orderBy(
                'p.' . $this->defaultOrder[0],
                $this->defaultOrder[1]
            );
        }

        return $queryBuilder->getQuery();
    }
    
    /**
     * Finds all published posts having the given tag.
     * @param string $tagName Name of the tag.
     * @return Query
     */
    public function findPostsByTag($tagName)
    {
        $entityManager = $this->getEntityManager();
        
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('p')
            ->from(Post::class, 'p')
            ->join('p.tags', 't')
            ->andWhere('t.name = ?2')
            ->orderBy('p.dateCreated', 'DESC')
            ->setParameter('2', $tagName);
        
        return $queryBuilder->getQuery();
    }

    /**
     * Finds all published posts having any tag.
     * @return array
     */
    public function findPostsHavingAnyTag()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('p')
            ->from(Post::class, 'p')
            ->join('p.tags', 't')
            ->where('p.status = ?1')
            ->orderBy('p.dateCreated', 'DESC')
            ->setParameter('1', Post::STATUS_PUBLISHED);

        $posts = $queryBuilder->getQuery()->getResult();

        return $posts;
    }
}