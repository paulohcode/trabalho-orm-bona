<?php
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\Service\BlogManager;
use Application\Controller\BlogController;

/**
 * This is the factory for BlogController. Its purpose is to instantiate the
 * controller.
 */
class BlogControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $blogManager = $container->get(BlogManager::class);
        
        // Instantiate the controller and inject dependencies
        return new BlogController($entityManager, $blogManager);
    }
}


