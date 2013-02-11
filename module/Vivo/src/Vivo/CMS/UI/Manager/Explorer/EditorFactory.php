<?php
namespace Vivo\CMS\UI\Manager\Explorer;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * Editor factory.
 */
class EditorFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm                 = $serviceLocator->get('service_manager');
        $cms                = $sm->get('Vivo\CMS\Api\CMS');
        $metadataManager    = $sm->get('metadata_manager');
        $documentApi        = $sm->get('Vivo\CMS\Api\Document');
        $editor             = new Editor($cms, $metadataManager, $documentApi);
        $editor->setTabContainer($sm->create('Vivo\UI\TabContainer'), 'contentTab');
        return $editor;
    }
}
