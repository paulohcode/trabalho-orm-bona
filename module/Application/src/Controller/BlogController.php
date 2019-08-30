<?php
namespace Application\Controller;

use Application\Repository\BlogRepository;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Form\BlogForm;
use Application\Entity\Blog;

/**
 * This is the Blog controller class of the Blog application. 
 * This controller is used for managing blogs (adding/editing/viewing/deleting).
 */
class BlogController extends AbstractActionController 
{
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager 
     */
    private $entityManager;
    
    /**
     * Blog manager.
     * @var \Application\Service\BlogManager
     */
    private $blogManager;
    
    /**
     * Constructor is used for injecting dependencies into the controller.
     */
    public function __construct($entityManager, $blogManager) 
    {
        $this->entityManager = $entityManager;
        $this->blogManager = $blogManager;
    }
    
    /**
     * This action displays the "New Blog" page. The page contains a form allowing
     * to enter blog title, content and tags. When the user clicks the Submit button,
     * a new Blog entity will be created.
     */
    public function addAction() 
    {     
        // Create the form.
        $form = new BlogForm();
        
        // Check whether this blog is a POST request.
        if ($this->getRequest()->isPost()) {
            
            // Get POST data.
            $data = $this->params()->fromPost();
            
            // Fill form with data.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Get validated form data.
                $data = $form->getData();
                
                // Use blog manager service to add new blog to database.                
                $this->blogManager->addNewBlog($data);
                
                // Redirect the user to "index" page.
                return $this->redirect()->toRoute('application');
            }
        }
        
        // Render the view template.
        return new ViewModel([
            'form' => $form
        ]);
    }    
    
    /**
     * This action displays the "View Blog" page allowing to see the blog title
     * and content. The page also contains a form allowing
     * to add a comment to blog. 
     */
    public function viewAction() 
    {
        $blogId = (int) $this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($blogId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the blog by ID
        /* @var $repository BlogRepository */
        $repository = $this->entityManager->getRepository(Blog::class);
        $blog = $repository->findOneById($blogId);

        if (! $blog instanceof Blog) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }

        // Render the view template.
        return new ViewModel([
            'blog' => $blog,
            'blogManager' => $this->blogManager
        ]);
    }  
    
    /**
     * This action displays the page allowing to edit a blog.
     */
    public function editAction() 
    {
        // Create form.
        $form = new BlogForm();
        
        // Get blog ID.
        $blogId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($blogId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the existing blog in the database.
        $blog = $this->entityManager->getRepository(Blog::class)
                ->findOneById($blogId);        
        if ($blog == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        } 
        
        // Check whether this blog is a POST request.
        if ($this->getRequest()->isPost()) {
            
            // Get POST data.
            $data = $this->params()->fromPost();
            
            // Fill form with data.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Get validated form data.
                $data = $form->getData();
                
                // Use blog manager service update existing blog.                
                $this->blogManager->updateBlog($blog, $data);
                
                // Redirect the user to "admin" page.
                return $this->redirect()->toRoute('blogs', ['action'=>'admin']);
            }
        } else {
            $data = [
                'title' => $blog->getTitle(),
            ];
            
            $form->setData($data);
        }
        
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'blog' => $blog
        ]);  
    }
    
    /**
     * This "delete" action deletes the given blog.
     */
    public function deleteAction()
    {
        $blogId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($blogId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $blog = $this->entityManager->getRepository(Blog::class)
                ->findOneById($blogId);        
        if ($blog == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->blogManager->removeBlog($blog);
        
        // Redirect the user to "admin" page.
        return $this->redirect()->toRoute('blogs', ['action'=>'admin']);        
                
    }
    
    /**
     * This "admin" action displays the Manage Blogs page. This page contains
     * the list of blogs with an ability to edit/delete any blog.
     */
    public function adminAction()
    {
        // Get recent blogs
        $blogs = $this->entityManager->getRepository(Blog::class)
                ->findBy([], ['id'=>'DESC']);
        
        // Render the view template
        return new ViewModel([
            'blogs' => $blogs,
            'blogManager' => $this->blogManager
        ]);        
    }
}
