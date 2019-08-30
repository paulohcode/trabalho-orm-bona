<?php
namespace Application\Service;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Application\Entity\Blog;
use Application\Entity\Comment;
use Application\Entity\Tag;
use Zend\Filter\StaticFilter;

/**
 * The BlogManager service is responsible for adding new blogs, updating existing
 * blogs, adding tags to blog, etc.
 */
class BlogManager
{
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager;
     */
    private $entityManager;
    
    /**
     * Constructor.
     */
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * This method adds a new blog.
     */
    public function addNewBlog($data) 
    {
        // Create new Blog entity.
        $blog = new Blog();
        $blog->setTitle($data['title']);
        
        // Add the entity to entity manager.
        $this->entityManager->persist($blog);

        // Apply changes to database.
        $this->entityManager->flush();
    }
    
    /**
     * This method allows to update data of a single blog.
     */
    public function updateBlog($blog, $data) 
    {
        $blog->setTitle($data['title']);
        
        // Apply changes to database.
        $this->entityManager->flush();
    }

    /**
     * Removes blog
     */
    public function removeBlog($blog) 
    {
        $this->entityManager->remove($blog);
        
        $this->entityManager->flush();
    }
    
    /**
     * Calculates frequencies of tag usage.
     */
    public function getTagCloud()
    {
        /**
         * @TODO: Arrumar para pegar as tags dos posts do blog
         */
        $tagCloud = [];
                
        $blogs = $this->entityManager->getRepository(Blog::class)
                    ->findBlogsHavingAnyTag();
        $totalBlogCount = count($blogs);
        
        $tags = $this->entityManager->getRepository(Tag::class)
                ->findAll();
        foreach ($tags as $tag) {
            
            $blogsByTag = $this->entityManager->getRepository(Blog::class)
                    ->findBlogsByTag($tag->getName())->getResult();
            
            $blogCount = count($blogsByTag);
            if ($blogCount > 0) {
                $tagCloud[$tag->getName()] = $blogCount;
            }
        }
        
        $normalizedTagCloud = [];
        
        // Normalize
        foreach ($tagCloud as $name=>$blogCount) {
            $normalizedTagCloud[$name] =  $blogCount/$totalBlogCount;
        }
        
        return $normalizedTagCloud;
    }
}
