<?php
namespace Application\Controller;

use Application\Repository\PostRepository;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;
use Application\Entity\Post;

/**
 * This is the main controller class of the Blog application. The 
 * controller class is used to receive user input,  
 * pass the data to the models and pass the results returned by models to the 
 * view for rendering.
 */
class IndexController extends AbstractActionController 
{
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Post manager.
     * @var \Application\Service\PostManager
     */
    private $postManager;
    
    /**
     * Constructor is used for injecting dependencies into the controller.
     */
    public function __construct($entityManager, $postManager) 
    {
        $this->entityManager = $entityManager;
        $this->postManager = $postManager;
    }
    
    /**
     * This is the default "index" action of the controller. It displays the 
     * Recent Posts page containing the recent blog posts.
     */
    public function indexAction() 
    {
        $tagCloud = $this->postManager->getTagCloud();

        $page = $this->params()->fromQuery('page', 1);
        $tagFilter = $this->params()->fromQuery('tag', null);

        /* @var $repository PostRepository */
        $repository = $this->entityManager->getRepository(Post::class);
        $repository->page = $page;
        $repository->tagFilter = $tagFilter;

        $paginator1 = $repository->findPostsSite();

        $repository->defaultOrder = ['views', 'DESC'];
        $paginator2 = $repository->findPostsSite();

        // Render the view template.
        return new ViewModel([
            'postsOrderCreated' => $paginator1,
            'postsOrderViews' => $paginator2,
            'postManager' => $this->postManager,
            'tagCloud' => $tagCloud
        ]);
    }
    
    /**
     * This action displays the About page.
     */
    public function aboutAction() 
    {   
        $appName = 'Blog';
        $appDescription = 'A simple blog application for the Using Zend Framework 3 book';
        
        return new ViewModel([
            'appName' => $appName,
            'appDescription' => $appDescription
        ]);
    }
}
