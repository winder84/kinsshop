<?php

namespace AppBundle\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    private $exCategoriesIds;
    private $categories;
    private $sites;

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $page = 0;
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
                'path' => '/page/',
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

        $this->getMenuItems();
        return $this->render('AppBundle:Default:index.html.twig', array(
            'products' => $resultProducts,
            'sites' => $this->sites,
            'vendors' => $vendors,
            'categories' => $this->categories,
            'paginator' => $paginator,
        ));
    }

    /**
     * @Route("/page/{page}", name="home_page")
     */
    public function indexPageAction(Request $request, $page = 0)
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
                'path' => '/page/',
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

        $this->getMenuItems();
        return $this->render('AppBundle:Default:index.html.twig', array(
            'products' => $resultProducts,
            'sites' => $this->sites,
            'vendors' => $vendors,
            'categories' => $this->categories,
            'paginator' => $paginator,
        ));
    }

    /**
     * @Route("/shop/description/{alias}", name="shop_description_route")
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

        $this->getMenuItems();
        return $this->render('AppBundle:Default:site.description.html.twig', array(
                'site' => $site,
                'sites' => $this->sites,
                'categories' => $this->categories,
                'vendors' => $vendors
            )
        );
    }

    /**
     * @Route("/shop/{alias}/{page}", name="shop_route")
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

        $qb = $em->createQueryBuilder();
        $qb->select('Product')
            ->from('AppBundle:Product', 'Product')
            ->where('Product.site = :site')
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

        $this->getMenuItems();
        return $this->render('AppBundle:Default:site.html.twig', array(
                'site' => $site,
                'sites' => $this->sites,
                'categories' => $this->categories,
                'products' => $products,
                'paginator' => $paginator,
                'vendors' => $vendors
            )
        );
    }

    /**
     * @Route("/vendor/{alias}/{page}", name="vendor_route")
     */
    public function vendorAction($alias, $page = 0)
    {
        $em = $this->getDoctrine()->getManager();
        $vendor = $em
            ->getRepository('AppBundle:Vendor')
            ->findOneBy(array('alias' => $alias));
        $qb = $em->createQueryBuilder();
        $qb->select('Product')
            ->from('AppBundle:Product', 'Product')
            ->where('Product.vendor = :vendor')
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
        $this->getMenuItems();
        return $this->render('AppBundle:Default:vendor.html.twig', array(
                'products' => $products,
                'paginator' => $paginator,
                'sites' => $this->sites,
                'vendor' => $vendor,
                'categories' => $this->categories
            )
        );
    }

    /**
     * @Route("/category/{alias}/{page}", name="category_route")
     */
    public function categoryAction($alias, $page = 0)
    {
        $em = $this->getDoctrine()->getManager();
        $category = $em
            ->getRepository('AppBundle:Category')
            ->findOneBy(array('alias' => $alias));

        $externalCategories = $category->getExternalCategories();
        foreach ($externalCategories as $externalCategory ) {
            $this->exCategoriesIds[] = $externalCategory->getExternalId();
        }
        $this->getCategoriesIdsRecursive();
        $qb = $em->createQueryBuilder();
        $qb->select('Product')
            ->from('AppBundle:Product', 'Product')
            ->where('Product.category IN (:exCategoriesIds)')
            ->setParameter('exCategoriesIds', $this->exCategoriesIds);
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
                'path' => '/category/' . $alias . '/',
            )
        );
        $qb->select('ExCategory')
            ->from('AppBundle:ExternalCategory', 'ExCategory')
            ->where('ExCategory.id IN (:exCategoriesIds)')
            ->setParameter('exCategoriesIds', $this->exCategoriesIds);
        $query = $qb->getQuery()
            ->setMaxResults(18);
        $exCategories = $query->getResult();

        $this->getMenuItems();
        return $this->render('AppBundle:Default:category.html.twig', array(
                'products' => $products,
                'paginator' => $paginator,
                'category' => $category,
                'exCategories' => $exCategories,
                'sites' => $this->sites,
                'categories' => $this->categories
            )
        );
    }

    /**
     * @Route("/exCategory/{id}/{page}", name="ex_category_route")
     */
    public function exCategoryAction($id, $page = 0)
    {
        $em = $this->getDoctrine()->getManager();
        $category = $em
            ->getRepository('AppBundle:ExternalCategory')
            ->findOneBy(array('id' => $id));
        $qb = $em->createQueryBuilder();
        $qb->select('Product')
            ->from('AppBundle:Product', 'Product')
            ->where('Product.category = :category')
            ->setParameter('category', $category);
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
                'path' => '/exCategory/' . $id . '/',
            )
        );

        $this->getMenuItems();
        return $this->render('AppBundle:Default:exCategory.html.twig', array(
                'products' => $products,
                'paginator' => $paginator,
                'category' => $category,
                'sites' => $this->sites,
                'categories' => $this->categories
            )
        );
    }

    /**
     * @Route("/product/{id}", name="product_route")
     */
    public function productAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em
            ->getRepository('AppBundle:Product')
            ->findOneBy(array('id' => $id));

        $this->getMenuItems();
        return $this->render('AppBundle:Default:product.description.html.twig', array(
                'sites' => $this->sites,
                'product' => $product,
                'categories' => $this->categories
            )
        );
    }

    private function getCategoriesIdsRecursive()
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('ExCategory.id')
            ->from('AppBundle:ExternalCategory', 'ExCategory')
            ->where('ExCategory.parentId IN (:exCategoriesIds)')
            ->andWhere('ExCategory.id NOT IN (:exCategoriesIds)')
            ->setParameter('exCategoriesIds', $this->exCategoriesIds);
        $query = $qb->getQuery();
        $resultExCategories = $query->getResult();
        if (empty($resultExCategories)) {
            return $this->exCategoriesIds;
        } else {
            foreach ($resultExCategories as $externalCategory ) {
                $this->exCategoriesIds[] = $externalCategory['id'];
            }
            $this->getCategoriesIdsRecursive();
        }
    }

    private function getMenuItems()
    {
        $em = $this->getDoctrine()->getManager();
        $this->categories = $em
            ->getRepository('AppBundle:Category')
            ->findAll();
        $this->sites = $em
            ->getRepository('AppBundle:Site')
            ->findAll();
    }
}
