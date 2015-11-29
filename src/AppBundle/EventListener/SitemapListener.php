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

        $sites = $this->em->getRepository('AppBundle:Site')->findAll();
        foreach ($sites as $site) {
            $urls[] = $this->router->generate('shop_description_route', array('alias' => $site->getAlias()), true);
        }
        $sites = null;

        $vendors = $this->em->getRepository('AppBundle:Vendor')->findAll();
        foreach ($vendors as $vendor) {
            $urls[] = $this->router->generate('vendor_route', array('alias' => $vendor->getAlias()), true);
        }
        $vendors= null;

        $exCategories = $this->em->getRepository('AppBundle:ExternalCategory')->findAll();
        foreach ($exCategories as $exCategory) {
            $urls[] = $this->router->generate('ex_category_route', array('id' => $exCategory->getId()), true);
        }
        $exCategories = null;

        $categories = $this->em->getRepository('AppBundle:Category')->findAll();
        foreach ($categories as $category) {
            $urls[] = $this->router->generate('category_route', array('alias' => $category->getAlias()), true);
        }
        $categories = null;

        $filterPages = array();
        $products = $this->em->getRepository('AppBundle:Product')->findAll();
        foreach ($products as $product) {
            if (!$product->getIsDelete()) {
                $urls[] = $this->router->generate('product_route', array('id' => $product->getId()), true);
                $vendor = $product->getVendor();
                $exCategory = $product->getCategory();
                if ($vendor && $exCategory) {
                    $path = $this->router->generate('filter_route', array(
                        'vendorAlias' => $vendor->getAlias(),
                        'categoryId' => $exCategory->getId(),
                    ), true);
                    $filterPages[$path] = $path;
                }
            }
        }
        $urls = array_merge($urls, array_values($filterPages));
        $products = null;

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