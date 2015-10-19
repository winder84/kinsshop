<?php
namespace AppBundle\EventListener;

use Symfony\Component\Routing\RouterInterface;

use Presta\SitemapBundle\Service\SitemapListenerInterface;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Doctrine\ORM\EntityManager;

class SitemapListener implements SitemapListenerInterface
{
    private $router;
    protected $em;

    public function __construct(RouterInterface $router, EntityManager $em)
    {
        $this->em = $em;
        $this->router = $router;
    }

    public function populateSitemap(SitemapPopulateEvent $event)
    {
        $section = $event->getSection();
        if (is_null($section) || $section == 'default') {
            //get absolute homepage url
            $urls[] = $this->router->generate('homepage', array(), true);
        }

        foreach ($this->em->getRepository('AppBundle:Site')->findAll() as $site) {
            $urls[] = $this->router->generate('shop_description_route', array('alias' => $site->getAlias()), true);
        }
        foreach ($this->em->getRepository('AppBundle:Vendor')->findAll() as $vendor) {
            $urls[] = $this->router->generate('vendor_route', array('alias' => $vendor->getAlias()), true);
        }

        foreach ($this->em->getRepository('AppBundle:ExternalCategory')->findAll() as $exCategory) {
            $urls[] = $this->router->generate('ex_category_route', array('id' => $exCategory->getId()), true);
        }

        foreach ($this->em->getRepository('AppBundle:Category')->findAll() as $category) {
            $urls[] = $this->router->generate('category_route', array('alias' => $category->getAlias()), true);
        }

        foreach ($this->em->getRepository('AppBundle:Product')->findAll() as $product) {
            $urls[] = $this->router->generate('product_route', array('id' => $product->getId()), true);
        }

        foreach ($urls as $url) {
            $event->getGenerator()->addUrl(
                new UrlConcrete(
                    $url,
                    new \DateTime(),
                    UrlConcrete::CHANGEFREQ_WEEKLY,
                    0.7
                ),
                'default'
            );
        }
    }
}