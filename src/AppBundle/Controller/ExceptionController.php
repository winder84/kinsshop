<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ExceptionController extends Controller
{
    private $metaTags;
    private $categories;
    private $sites;

    /**
     * @Route("/404")
     * @Template()
     */
    public function show404Action()
    {
        $this->getMenuItems();
        $this->getMetaItems();
        $this->metaTags['metaRobots'] = 'NOINDEX, NOFOLLOW';
        return $this->render('AppBundle:Exception:show404.html.twig', array(
            'sites' => $this->sites,
            'categories' => $this->categories,
            'metaTags' => $this->metaTags,
        ));
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

    private function getMetaItems()
    {
        $this->metaTags['metaTitle'] = 'Всё для вашего ребенка!';
        $this->metaTags['metaDescription'] = 'У нас Вы найдете всё самое лучшее для Вашего ребенка!';
        $this->metaTags['metaKeywords'] = 'ребенок, дети, детё, сын, дочь, игрушки, книжки, кроватки, детская еда';
        $this->metaTags['metaRobots'] = 'all';
    }
}
