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
        // replace this example code with whatever you need
//        return $this->render('AppBundle:Default:index.html.twig', array(
//            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
//        ));
        return $this->render('AppBundle:Default:index.html.twig', array());
    }

    /**
     * @Route("/site/{alias}")
     */
    public function siteAction($alias)
    {
        $em = $this->getDoctrine()->getManager();
        $site = $em
        ->getRepository('AppBundle:Site')
        ->findOneBy(array('alias' => $alias));

        return $this->render(
            'AppBundle:Default:site.html.twig',
            array('site' => $site)
        );
    }
}
