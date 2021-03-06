<?php
namespace Vivo\CMS\UI\Content;

use Vivo\CMS\UI\Component;
use Vivo\CMS\Api\Content\Gallery as GalleryApi;

/**
 * UI component for content gallery.
 */
class Gallery extends Component
{
    /**
     * @var \Vivo\CMS\Api\Content\Gallery
     */
    private $galleryApi;

    /**
     * @var array
     */
    private $files = array();

    /**
     * @var array
     */
    private $info = array();

    /**
     * Constructor
     */
    public function __construct(GalleryApi $galleryApi)
    {
        $this->galleryApi = $galleryApi;
    }

    public function init()
    {
        parent::init();

        $this->files = $this->galleryApi->getList($this->content);
        $this->info = $this->galleryApi->getInformationsAsArray($this->content);
    }

    public function view()
    {
        $view = parent::view();
        $view->info = $this->info;
        $view->files = $this->files;

        return $view;
    }
}
