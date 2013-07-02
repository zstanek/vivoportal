<?php
namespace Vivo\View\Helper;

use Vivo\CMS\Api;
use Vivo\CMS\Model\Entity;
use Vivo\View\Helper\Exception\InvalidArgumentException;
use Vivo\Module\ResourceManager\ResourceManager as ModuleResourceManager;

use Zend\View\Helper\AbstractHelper;

/**
 * View helper for getting resource url.
 */
class Resource extends AbstractHelper
{
    /**
     * Helper options
     * @var array
     */
    private $options = array(
        'check_resource'        => false, // useful for debugging sites
        //Path where Vivo resources are found
        'vivo_resource_path'    => null,
        //This maps current request route name to an appropriate route name for resources
        'resource_route_map'    => array(
            'vivo/cms'          => 'vivo/resource',
            'backend/cms'       => 'backend/resource',
            'backend/modules'   => 'backend/backend_resource',
            'backend/other'     => 'backend/backend_resource',
            'backend/default'   => 'backend/backend_resource',
        ),
    );

    /**
     * CMS Api
     * @var Api\CMS
     */
    private $cmsApi;

    /**
     * Route name used for resources
     * @var string
     */
    protected $resourceRouteName;

    /**
     * Module Resource Manager
     * @var ModuleResourceManager
     */
    protected $moduleResourceManager;

    /**
     * Constructor.
     * @param \Vivo\CMS\Api\CMS $cmsApi
     * @param \Vivo\Module\ResourceManager\ResourceManager $moduleResourceManager
     * @param string $currentRouteName
     * @param array $options
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(Api\CMS $cmsApi,
                                ModuleResourceManager $moduleResourceManager,
                                $currentRouteName,
                                $options = array())
    {
        $this->cmsApi                   = $cmsApi;
        $this->moduleResourceManager    = $moduleResourceManager;
        $this->options                  = array_merge($this->options, $options);
        $this->resourceRouteName        = isset($this->options['resource_route_map'][$currentRouteName])
                                            ? $this->options['resource_route_map'][$currentRouteName] : '';
        if (!$this->options['vivo_resource_path']) {
            throw new Exception\InvalidArgumentException(sprintf("%s: 'vivo_resource_path' option not set",
                __METHOD__));
        }
    }

    /**
     * Builds resource URL
     * Adds mtime as query string param to enable correct reverse proxy cache invalidation
     * @param string $resourcePath
     * @param string|Entity $source
     * @param string|null $type Resource type (for module resources)
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public function __invoke($resourcePath, $source, $type = null)
    {
        if ($this->options['check_resource'] == true) {
            $this->checkResource($resourcePath, $source);
        }
        $urlHelper = $this->view->plugin('url');
        if ($source instanceof Entity) {
            $entityUrl          = $this->cmsApi->getEntityRelPath($source);
            $resourceRouteName  = $this->resourceRouteName . '_entity';
            $urlParams  = array(
                'path'      => $resourcePath,
                'entity'    => $entityUrl,
            );
            $mtime      = $this->cmsApi->getResourceMtime($source, $resourcePath);
            $urlOptions         = array(
                'query' => array(
                    'mtime' => $mtime,
                ),
            );
            $reuseMatchedParams = true;
        } elseif (is_string($source)) {
            if ($source == 'Vivo') {
                //It is a Vivo resource
                $mtime  = $this->getVivoResourceMtime($resourcePath);
            } else {
                //It is a module resource
                $mtime  = $this->moduleResourceManager->getResourceMtime($source, $resourcePath, $type);
            }
            $resourceRouteName  = $this->resourceRouteName;
            $urlParams          = array(
                'source'    => $source,
                'path'      => $resourcePath,
                'type'      => 'resource',
            );
            $urlOptions         = array(
                'query' => array(
                    'mtime' => $mtime,
                ),
            );
            $reuseMatchedParams = true;
        } else {
            throw new InvalidArgumentException(sprintf("%s: Invalid value for parameter 'source'.", __METHOD__));
        }
        $url = $urlHelper($resourceRouteName, $urlParams, $urlOptions, $reuseMatchedParams);
        //Replace encoded slashes in the url.
        //It's needed because apache returns 404 when the url contains encoded slashes
        //This behaviour could be changed in apache config, but it is not possible to do that in .htaccess context.
        //@see http://httpd.apache.org/docs/current/mod/core.html#allowencodedslashes
        $url = str_replace('%2F', '/', $url);
        return $url;
    }

    /**
     * Returns Vivo resource mtime or false when the resource does not exist
     * @param string $resourcePath Relative path to a Vivo resource
     * @return int|bool
     */
    protected function getVivoResourceMtime($resourcePath)
    {
        $vivoResourcePath   = $this->options['vivo_resource_path'] . '/' . $resourcePath;
        if (file_exists($vivoResourcePath)) {
            $mtime  = filemtime($vivoResourcePath);
        } else {
            $mtime  = false;
            //Log nonexistent resource
            $events = new \Zend\EventManager\EventManager();
            $events->trigger('log', $this,  array(
                'message'   => sprintf("Vivo resource '%s' not found", $vivoResourcePath),
                'priority'  => \VpLogger\Log\Logger::ERR,
            ));
        }
        return $mtime;
    }

    public function checkResource($resourcePath, $source)
    {
        //TODO check resource and throw exception if it doesn't exist.
    }
}
