<?php

namespace AppBundle\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/{page}", name="homepage")
     */
    public function indexAction(Request $request, $page = 0)
    {
        $em = $this->getDoctrine()->getManager();
        $products = $em
            ->getRepository('AppBundle:Product')
            ->findBy(
                array(),
                array(),
                160,
                0
            );
        $productsCount = count($products);
        $paginatorPagesCount = floor($productsCount / 16);
        $paginatorData = new \AppBundle\Helpers\Paginator($paginatorPagesCount, $page, 1, 5);
        $engine = $this->container->get('templating');
        $paginator =  $engine->render('AppBundle:Default:paginator.html.twig', array(
                'paginator' => $paginatorData,
                'path' => '/',
            )
        );
        $products = $em
            ->getRepository('AppBundle:Product')
            ->findBy(
                array(),
                array(),
                16,
                16 * $page
            );
        $qb = $em->createQueryBuilder();
        $qb->select('Vendor, count(Vendor) as cnt')
            ->from('AppBundle:Product', 'Product')
            ->leftJoin('AppBundle:Vendor', 'Vendor')
            ->where('Vendor = Product.vendor')
            ->groupBy('Vendor')
            ->orderBy('cnt', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(12);
        $query = $qb->getQuery();
        $vendors = $query->getResult();
        $sites = $em
            ->getRepository('AppBundle:Site')
            ->findAll();
        $categories = $em
            ->getRepository('AppBundle:Category')
            ->findAll();
        foreach ($products as $product) {
            $resultProducts[] = array(
                'name' => $product->getName(),
                'model' => $product->getModel(),
                'pictures' => $product->getPictures(),
                'id' => $product->getId(),
            );
        }
        // replace this example code with whatever you need
//        return $this->render('AppBundle:Default:index.html.twig', array(
//            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
//        ));

        return $this->render('AppBundle:Default:index.html.twig', array(
            'products' => $resultProducts,
            'sites' => $sites,
            'vendors' => $vendors,
            'categories' => $categories,
            'paginator' => $paginator,
        ));
    }

    /**
     * @Route("/shop/description/{alias}")
     */
    public function siteDescriptionAction($alias)
    {
        $em = $this->getDoctrine()->getManager();
        $site = $em
            ->getRepository('AppBundle:Site')
            ->findOneBy(array('alias' => $alias));

        $qb = $em->createQueryBuilder();
        $qb->select('Vendor, count(Vendor) as cnt')
            ->from('AppBundle:Product', 'Product')
            ->leftJoin('AppBundle:Vendor', 'Vendor')
            ->where('Vendor = Product.vendor')
            ->andWhere('Vendor.site = :site')
            ->setParameter('site', $site)
            ->groupBy('Vendor')
            ->orderBy('cnt', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(12);
        $query = $qb->getQuery();
        $vendors = $query->getResult();

        $categories = $em
            ->getRepository('AppBundle:Category')
            ->findAll();
        $sites = $em
            ->getRepository('AppBundle:Site')
            ->findAll();
        return $this->render('AppBundle:Default:site.description.html.twig', array(
                'site' => $site,
                'sites' => $sites,
                'categories' => $categories,
                'vendors' => $vendors
            )
        );
    }

    /**
     * @Route("/shop/{alias}/{page}")
     */
    public function siteAction($alias, $page = 0)
    {
        $em = $this->getDoctrine()->getManager();
        $site = $em
            ->getRepository('AppBundle:Site')
            ->findOneBy(array('alias' => $alias));

        $qb = $em->createQueryBuilder();
        $qb->select('Vendor, count(Vendor) as cnt')
            ->from('AppBundle:Product', 'Product')
            ->leftJoin('AppBundle:Vendor', 'Vendor')
            ->where('Vendor = Product.vendor')
            ->andWhere('Vendor.site = :site')
            ->setParameter('site', $site)
            ->groupBy('Vendor')
            ->orderBy('cnt', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(12);
        $query = $qb->getQuery();
        $vendors = $query->getResult();

        $sites = $em
            ->getRepository('AppBundle:Site')
            ->findAll();
        $qb = $em->createQueryBuilder();
        $qb->select('Product')
            ->from('AppBundle:Product', 'Product')
            ->andWhere('Product.site = :site')
            ->setParameter('site', $site);
        $query = $qb->getQuery()
            ->setFirstResult(28 * $page)
            ->setMaxResults(28);
        $products = new Paginator($query, $fetchJoinCollection = true);

        $productsCount = count($products);
        $paginatorPagesCount = floor($productsCount / 28);
        $paginatorData = new \AppBundle\Helpers\Paginator($paginatorPagesCount, $page, 1, 5);
        $engine = $this->container->get('templating');
        $paginator =  $engine->render('AppBundle:Default:paginator.html.twig', array(
                'paginator' => $paginatorData,
                'path' => '/shop/' . $alias . '/',
            )
        );

        $categories = $em
            ->getRepository('AppBundle:Category')
            ->findAll();
        return $this->render('AppBundle:Default:site.html.twig', array(
                'site' => $site,
                'sites' => $sites,
                'categories' => $categories,
                'products' => $products,
                'paginator' => $paginator,
                'vendors' => $vendors
            )
        );
    }

    /**
     * @Route("/vendor/{alias}/{page}")
     */
    public function vendorAction($alias, $page = 0)
    {
        $em = $this->getDoctrine()->getManager();
        $vendor = $em
            ->getRepository('AppBundle:Vendor')
            ->findOneBy(array('alias' => $alias));

        $categories = $em
            ->getRepository('AppBundle:Category')
            ->findAll();

        $sites = $em
            ->getRepository('AppBundle:Site')
            ->findAll();
        $qb = $em->createQueryBuilder();
        $qb->select('Product')
            ->from('AppBundle:Product', 'Product')
            ->andWhere('Product.vendor = :vendor')
            ->setParameter('vendor', $vendor);
        $query = $qb->getQuery()
            ->setFirstResult(28 * $page)
            ->setMaxResults(28);
        $products = new Paginator($query, $fetchJoinCollection = true);

        $productsCount = count($products);
        $paginatorPagesCount = floor($productsCount / 28);
        $paginatorData = new \AppBundle\Helpers\Paginator($paginatorPagesCount, $page, 1, 5);
        $engine = $this->container->get('templating');
        $paginator =  $engine->render('AppBundle:Default:paginator.html.twig', array(
                'paginator' => $paginatorData,
                'path' => '/vendor/' . $alias . '/',
            )
        );
        return $this->render('AppBundle:Default:vendor.html.twig', array(
                'products' => $products,
                'paginator' => $paginator,
                'sites' => $sites,
                'vendor' => $vendor,
                'categories' => $categories
            )
        );
    }
}
