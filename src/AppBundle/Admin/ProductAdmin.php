<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ProductAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('externalId')
            ->add('categoryId')
            ->add('currencyId')
            ->add('description')
            ->add('seoDescription')
            ->add('seoKeywords')
            ->add('model')
            ->add('modifiedTime')
            ->add('name')
            ->add('price')
            ->add('typePrefix')
            ->add('url')
            ->add('vendor')
            ->add('vendorCode')
            ->add('version')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('name')
            ->add('model')
            ->add('externalId')
            ->add('categoryId')
            ->add('price')
            ->add('vendor')
            ->add('vendorCode')
            ->add('version')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('id')
            ->add('externalId')
            ->add('categoryId')
            ->add('currencyId')
            ->add('description')
            ->add('seoDescription')
            ->add('seoKeywords')
            ->add('model')
            ->add('modifiedTime')
            ->add('name')
            ->add('price')
            ->add('typePrefix')
            ->add('url')
            ->add('vendor')
            ->add('vendorCode')
            ->add('version')
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('externalId')
            ->add('categoryId')
            ->add('currencyId')
            ->add('description')
            ->add('seoDescription')
            ->add('seoKeywords')
            ->add('model')
            ->add('modifiedTime')
            ->add('name')
            ->add('price')
            ->add('typePrefix')
            ->add('url')
            ->add('vendor')
            ->add('vendorCode')
            ->add('version')
        ;
    }
}
