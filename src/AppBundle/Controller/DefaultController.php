<?php

namespace AppBundle\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    private $exCategoriesIds = array();
    private $menuItems = array();
    private $metaTags = array();
    private $productsPerPage = 28;

    public function __construct()
    {
        $this->getMetaItems();
    }

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $resultProducts = array();
        $notNeedArray = array(0);
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $products = $em
            ->getRepository('AppBundle:Product')
            ->findBy(
                array(
                    'ourChoice' => true,
                    'isDelete' => false
                ),
                array(),
                12
            );
        if (count($products) < 12) {
            foreach ($products as $product) {
                $notNeedArray[] = $product->getId();
            }
            $needCount = 12 - count($products);
            $qb->select('Product')
                ->from('AppBundle:Product', 'Product')
                ->where('Product.id NOT IN (:notNeedArray)')
                ->andWhere('Product.isDelete = 0')
                ->setParameter('notNeedArray', $notNeedArray)
                ->setMaxResults($needCount);
            $query = $qb->getQuery();
            $moreProducts = $query->getResult();
            $products = array_merge($products, $moreProducts);
        }
        $qb = $em->createQueryBuilder();
        $qb->select('Vendor, count(Vendor) as cnt')
            ->from('AppBundle:Product', 'Product')
            ->leftJoin('AppBundle:Vendor', 'Vendor')
            ->where('Vendor = Product.vendor')
            ->andWhere('Product.isDelete = 0')
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
                'url' => $product->getUrl(),
                'price' => $product->getPrice(),
            );
        }

        $this->getMenuItems();
        return $this->render('AppBundle:Default:index.html.twig', array(
            'products' => $resultProducts,
            'vendors' => $vendors,
            'menuItems' => $this->menuItems,
            'metaTags' => $this->metaTags,
            'paginatorData' => null,
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
        $this->metaTags['metaTitle'] = 'Описание магазина ' . $site->getTitle() . '. Купить товары "' . $site->getTitle() . '" с доставкой по России.';

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
                'metaTags' => $this->metaTags,
                'menuItems' => $this->menuItems,
                'vendors' => $vendors
            )
        );
    }

    /**
     * @Route("/shop/{alias}/{page}", name="shop_route")
     */
    public function siteAction($alias, $page = 1)
    {
        $this->metaTags['metaRobots'] = 'NOINDEX';
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
            ->andWhere('Product.isDelete = 0')
            ->setParameter('site', $site);
        $query = $qb->getQuery()
            ->setFirstResult($this->productsPerPage * ($page - 1))
            ->setMaxResults($this->productsPerPage);
        $products = new Paginator($query, $fetchJoinCollection = true);

        $productsCount = count($products);
        $paginatorPagesCount = ceil($productsCount / $this->productsPerPage);
        $path = "/shop/$alias/";
        if ($productsCount <= $this->productsPerPage) {
            $paginatorData = null;
        } else {
            $paginatorData = $this->getPaginatorData($paginatorPagesCount, $page, 1, 5, $path);
        }

        $this->getMenuItems();
        return $this->render('AppBundle:Default:site.html.twig', array(
                'site' => $site,
                'metaTags' => $this->metaTags,
                'menuItems' => $this->menuItems,
                'products' => $products,
                'paginatorData' => $paginatorData,
                'vendors' => $vendors
            )
        );
    }

    /**
     * @Route("/vendor/{alias}/{page}", name="vendor_route")
     */
    public function vendorAction($alias, $page = 1)
    {
        $this->metaTags['metaRobots'] = 'NOFOLLOW';
        $em = $this->getDoctrine()->getManager();
        $vendors = $em
            ->getRepository('AppBundle:Vendor')
            ->findBy(array('alias' => $alias));
        foreach ($vendors as $vendor) {
            $vendorIds[] = $vendor->getId();
            $this->metaTags['metaTitle'] = 'Купить ' . $vendor->getName() . ' со скидкой в интернет-магазине. Доставка по РФ';
        }
        $qb = $em->createQueryBuilder();
        $qb->select('Product')
            ->from('AppBundle:Product', 'Product')
            ->where('Product.vendor IN (:vendorIds)')
            ->andWhere('Product.isDelete = 0')
            ->setParameter('vendorIds', $vendorIds);
        $query = $qb->getQuery()
            ->setFirstResult($this->productsPerPage * ($page - 1))
            ->setMaxResults($this->productsPerPage);
        $products = new Paginator($query, $fetchJoinCollection = true);

        $productsCount = count($products);
        $paginatorPagesCount = ceil($productsCount / $this->productsPerPage);
        $path = "/vendor/$alias/";
        if ($productsCount <= $this->productsPerPage) {
            $paginatorData = null;
        } else {
            $paginatorData = $this->getPaginatorData($paginatorPagesCount, $page, 1, 5, $path);
        }
        $this->getMenuItems();
        return $this->render('AppBundle:Default:vendor.html.twig', array(
                'products' => $products,
                'paginatorData' => $paginatorData,
                'vendor' => $vendors[0],
                'metaTags' => $this->metaTags,
                'menuItems' => $this->menuItems
            )
        );
    }

    /**
     * @Route("/category/{alias}/{page}", name="category_route")
     */
    public function categoryAction($alias, $page = 1)
    {
        $this->metaTags['metaRobots'] = 'NOFOLLOW';
        $em = $this->getDoctrine()->getManager();
        $category = $em
            ->getRepository('AppBundle:Category')
            ->findOneBy(array('alias' => $alias));
        $this->metaTags['metaTitle'] = 'Купить ' . mb_strtolower($category->getName(), 'UTF-8') . ' с доставкой по России.';

        $externalCategories = $category->getExternalCategories();
        foreach ($externalCategories as $externalCategory ) {
            $this->exCategoriesIds[] = $externalCategory->getExternalId();
        }
        $this->getCategoriesIdsRecursive();
        $qb = $em->createQueryBuilder();
        $qb->select('Product')
            ->from('AppBundle:Product', 'Product')
            ->where('Product.category IN (:exCategoriesIds)')
            ->andWhere('Product.isDelete = 0')
            ->setParameter('exCategoriesIds', $this->exCategoriesIds);
        $query = $qb->getQuery()
            ->setFirstResult($this->productsPerPage * ($page - 1))
            ->setMaxResults($this->productsPerPage);
        $products = new Paginator($query, $fetchJoinCollection = true);

        $productsCount = count($products);
        $paginatorPagesCount = ceil($productsCount / $this->productsPerPage);
        $path = "/category/$alias/";
        if ($productsCount <= $this->productsPerPage) {
            $paginatorData = null;
        } else {
            $paginatorData = $this->getPaginatorData($paginatorPagesCount, $page, 1, 5, $path);
        }
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
                'paginatorData' => $paginatorData,
                'category' => $category,
                'exCategories' => $exCategories,
                'metaTags' => $this->metaTags,
                'menuItems' => $this->menuItems
            )
        );
    }

    /**
     * @Route("/exCategory/{id}/{page}", name="ex_category_route")
     */
    public function exCategoryAction($id, $page = 1)
    {
        $this->metaTags['metaRobots'] = 'NOFOLLOW';
        $em = $this->getDoctrine()->getManager();
        $category = $em
            ->getRepository('AppBundle:ExternalCategory')
            ->findOneBy(array('id' => $id));
        $this->metaTags['metaTitle'] = 'Купить ' . mb_strtolower($category->getName(), 'UTF-8') . ' с доставкой по России.';
        $qb = $em->createQueryBuilder();
        $qb->select('Product')
            ->from('AppBundle:Product', 'Product')
            ->where('Product.category = :category')
            ->andWhere('Product.isDelete = 0')
            ->setParameter('category', $category);
        $query = $qb->getQuery()
            ->setFirstResult($this->productsPerPage * ($page - 1))
            ->setMaxResults($this->productsPerPage);
        $products = new Paginator($query, $fetchJoinCollection = true);

        $productsCount = count($products);
        $paginatorPagesCount = ceil($productsCount / $this->productsPerPage);
        $path = "/exCategory/$id/";
        if ($productsCount <= $this->productsPerPage) {
            $paginatorData = null;
        } else {
            $paginatorData = $this->getPaginatorData($paginatorPagesCount, $page, 1, 5, $path);
        }

        $this->getMenuItems();
        return $this->render('AppBundle:Default:exCategory.html.twig', array(
                'products' => $products,
                'paginatorData' => $paginatorData,
                'category' => $category,
                'metaTags' => $this->metaTags,
                'menuItems' => $this->menuItems
            )
        );
    }

    /**
     * @Route("/product/{id}", name="product_route")
     */
    public function productAction($id)
    {
        $likeProducts = array();
        $em = $this->getDoctrine()->getManager();
        $product = $em
            ->getRepository('AppBundle:Product')
            ->findOneBy(array('id' => $id));
        if (!$product) {
            throw $this->createNotFoundException('The product does not exist');
        }
        if ($product->getIsDelete()) {
            $this->metaTags['metaRobots'] = 'NOINDEX, NOFOLLOW';
            $product->deleted = true;
        }
        $productCategory = $product->getCategory();
        $productCategoryName = '';
        if ($productCategory) {
            $productCategoryName = $productCategory->getName();
        }
        $productVendor = $product->getVendor();
        $productVendorName = '';
        if ($productVendor) {
            $productVendorName = $productVendor->getName();
        }
        $categoryProducts = $product->getCategory()->getProducts();
        foreach ($categoryProducts as $categoryProduct) {
            if (count($likeProducts) < 4) {
                if ($categoryProduct->getId() != $id && !$categoryProduct->getIsDelete()) {
                    $likeProducts[] = $categoryProduct;
                }
            }
        }

        if (!empty($productCategoryName)) {
            $productKeywords[] =  $productCategoryName . ' купить';
            $productFullName[] = $productCategoryName;
        }
        if (!empty($productVendorName)) {
            $productKeywords[] =  $productVendorName . ' купить';
            $productFullName[] = $productVendorName;
        }
        $productFullName[] = $product->getModel();
        $productFullName = array_filter($productFullName);
        $this->getMenuItems();
        $this->metaTags['metaTitle'] = 'Описание и цена ' . mb_strtolower($product->getName(), 'UTF-8') . '. Купить ' . mb_strtolower(implode(' | ', $productFullName), 'UTF-8') . ' с доставкой по России.';
        $this->metaTags['metaDescription'] = substr($product->getDescription(), 0, 400);
        $productKeywords[] =  $product->getName() . ' ' . $product->getModel() . ' купить';
        $this->metaTags['metaKeywords'] .= ',' . implode(',', $productKeywords);
        return $this->render('AppBundle:Default:product.description.html.twig', array(
                'product' => $product,
                'metaTags' => $this->metaTags,
                'likeProducts' => $likeProducts,
                'paginatorData' => null,
                'menuItems' => $this->menuItems
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
        $this->menuItems['categories'] = $em
            ->getRepository('AppBundle:Category')
            ->findAll();

        $this->menuItems['sites'] = $em
            ->getRepository('AppBundle:Site')
            ->findAll();
        $qb = $em->createQueryBuilder();

        $qb->select('Vendor.alias, Vendor.name, count(p.id) as cnt')
            ->from('AppBundle:Vendor', 'Vendor')
            ->leftJoin('Vendor.products', 'p')
            ->having('cnt > 450')
            ->groupBy('Vendor.alias')
            ->orderBy('cnt', 'DESC')
            ->setMaxResults(25);
        $query = $qb->getQuery();
        $resultVendors = $query->getResult();
        foreach ($resultVendors as $resultVendor) {
            $this->menuItems['vendors'][] = $resultVendor;
        }
    }

    private function getMetaItems()
    {
        $this->metaTags['metaTitle'] = 'Купить детские товары с доставкой. Детские коляски, обучающие материалы, развивающие игры.';
        $this->metaTags['metaDescription'] = 'У нас Вы найдете всё самое лучшее для Вашего ребенка!';
        $this->metaTags['metaKeywords'] = 'ребенок, дети, детё, сын, дочь, игрушки, книжки, кроватки, детская еда';
        $this->metaTags['metaRobots'] = 'all';
    }

    private function getPaginatorData($itemsCount, $currentPage, $limit, $midRange, $path = '/page/')
    {
        $paginator = new \AppBundle\Helpers\Paginator($itemsCount, $currentPage, $limit, $midRange);
        return array(
            'paginator' => $paginator,
            'path' => $path,
        );
    }
}
