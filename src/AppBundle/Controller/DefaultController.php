<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $products = $em
            ->getRepository('AppBundle:Product')
            ->findBy(
                array(),
                array(),
                16,
                0
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
            'categories' => $categories
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
        return $this->render('AppBundle:Default:site.html.twig', array(
                'site' => $site,
                'sites' => $sites,
                'categories' => $categories,
                'vendors' => $vendors
            )
        );
    }

    /**
     * @Route("/shop/{alias}")
     */
    public function siteAction($alias)
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
        return $this->render('AppBundle:Default:site.html.twig', array(
                'site' => $site,
                'sites' => $sites,
                'categories' => $categories,
                'vendors' => $vendors
            )
        );
    }
}
