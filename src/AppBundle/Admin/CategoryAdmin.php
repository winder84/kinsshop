<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class CategoryAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('name', null, array('label' => 'Наименование'))
            ->add('description', null, array('label' => 'Описание'))
            ->add('seoDescription', null, array('label' => 'SEO описание'))
            ->add('seoKeywords', null, array('label' => 'SEO ключевые слова'))
            ->add('site', null, array('label' => 'Магазин'))
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('name', null, array('label' => 'Наименование'))
            ->add('seoKeywords', null, array('label' => 'SEO ключевые слова'))
            ->add('site', null, array('label' => 'Магазин'))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                ),
                'label' => 'Действия'
            ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', null, array('label' => 'Наименование'))
            ->add('description', null, array('label' => 'Описание'))
            ->add('seoDescription', null, array('label' => 'SEO описание'))
            ->add('seoKeywords', null, array('label' => 'SEO ключевые слова'))
            ->add('site', null, array('label' => 'Магазин'))
            ->add('externalCategories', 'sonata_type_collection', array(
                'required' => false,
                'cascade_validation' => true,
                'by_reference' => false,
                'label' => 'Категории магазинов',
            ), array(
                'edit' => 'inline',
                'inline' => 'table',
            ))
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('name')
            ->add('description')
            ->add('seoDescription')
            ->add('seoKeywords')
        ;
    }
}
