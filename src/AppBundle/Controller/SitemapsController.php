<?php

namespace AppBundle\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class SitemapsController extends Controller
{

    /**
     * @Route("/sitemap.{_format}", name="sample_sitemaps_sitemap", Requirements={"_format" = "xml"})
     * @Template("AppBundle:Sitemaps:sitemap.xml.twig")
     */
    public function sitemapAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $urls = array();
        $hostname = $this->getRequest()->getHost();

        // add some urls homepage
        $urls[] = array('loc' => $this->get('router')->generate('homepage'), 'changefreq' => 'weekly', 'priority' => '1.0');

        // multi-lang pages
//        foreach($languages as $lang) {
//            $urls[] = array('loc' => $this->get('router')->generate('home_contact', array('_locale' => $lang)), 'changefreq' => 'monthly', 'priority' => '0.3');
//        }

//        for ($i = 1; $i <= 10 ; $i++) {
//            $urls[] = array('loc' => $this->get('router')->generate('home_page', array('page' => $i)), 'changefreq' => 'weekly', 'priority' => '1.0');
//        }
        // urls from database
//        $urls[] = array('loc' => $this->get('router')->generate('product_route', array('_locale' => 'ru')), 'changefreq' => 'weekly', 'priority' => '0.7');
        // service
        foreach ($em->getRepository('AppBundle:Site')->findAll() as $site) {
            $urls[] = array('loc' => $this->get('router')->generate('shop_description_route',
                    array('alias' => $site->getAlias())), 'changefreq' => 'weekly', 'priority' => '0.7');

            $qb = $em->createQueryBuilder();
            $qb->select('Product')
                ->from('AppBundle:Product', 'Product')
                ->where('Product.site = :site')
                ->setParameter('site', $site);
            $query = $qb->getQuery();
            $products = new Paginator($query, $fetchJoinCollection = true);

            $productsCount = count($products);
            $paginatorPagesCount = floor($productsCount / 28);
            if ($paginatorPagesCount > 0) {
                for ($page = 0; $page <= $paginatorPagesCount; $page++) {
                    $urls[] = array('loc' => $this->get('router')->generate('shop_route',
                        array('alias' => $site->getAlias(), 'page' => $page)), 'changefreq' => 'weekly', 'priority' => '0.7');
                }
            }
        }

//        foreach ($em->getRepository('AppBundle:Vendor')->findAll() as $vendor) {
//            $qb = $em->createQueryBuilder();
//            $qb->select('Product')
//                ->from('AppBundle:Product', 'Product')
//                ->where('Product.vendor = :vendor')
//                ->setParameter('vendor', $vendor);
//            $query = $qb->getQuery()
//                ->setFirstResult(28 * $page)
//                ->setMaxResults(28);
//            $products = new Paginator($query, $fetchJoinCollection = true);
//
//            $productsCount = count($products);
//            $paginatorPagesCount = floor($productsCount / 28);
//            if ($paginatorPagesCount > 0) {
//                for ($page = 0; $page <= $paginatorPagesCount; $page++) {
//                    $urls[] = array('loc' => $this->get('router')->generate('vendor_route',
//                        array('alias' => $vendor->getAlias(), 'page' => $page)), 'changefreq' => 'weekly', 'priority' => '0.7');
//                }
//            }
//        }
//
//        foreach ($em->getRepository('AppBundle:ExternalCategory')->findAll() as $exCategory) {
//            $qb = $em->createQueryBuilder();
//            $qb->select('Product')
//                ->from('AppBundle:Product', 'Product')
//                ->where('Product.category = :category')
//                ->setParameter('category', $exCategory);
//            $query = $qb->getQuery()
//                ->setFirstResult(28 * $page)
//                ->setMaxResults(28);
//            $products = new Paginator($query, $fetchJoinCollection = true);
//
//            $productsCount = count($products);
//            $paginatorPagesCount = floor($productsCount / 28);
//            if ($paginatorPagesCount > 0) {
//                for ($page = 0; $page <= $paginatorPagesCount; $page++) {
//                    $urls[] = array('loc' => $this->get('router')->generate('ex_category_route',
//                        array('id' => $exCategory->getId(), 'page' => $page)), 'changefreq' => 'weekly', 'priority' => '0.7');
//                }
//            }
//        }

        return array('urls' => $urls, 'hostname' => $hostname);
    }
}