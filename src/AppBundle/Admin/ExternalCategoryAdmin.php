<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ExternalCategoryAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('externalId', null, array('label' => 'Внешний id'))
            ->add('parentId', null, array('label' => 'Родительский id'))
            ->add('internalParentCategory', null, array('label' => 'Внутренняя категория'))
            ->add('name', null, array('label' => 'Наименование'))
            ->add('version', null, array('label' => 'Версия'))
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('externalId', null, array('label' => 'Внешний id'))
            ->add('parentId', null, array('label' => 'Родительский id'))
            ->add('internalParentCategory', null, array('label' => 'Внутренняя категория'))
            ->add('name', null, array('label' => 'Наименование'))
            ->add('version', null, array('label' => 'Версия'))
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
            ->add('externalId', null, array('label' => 'Внешний id'))
            ->add('parentId', null, array('label' => 'Родительский id'))
            ->add('version', null, array('label' => 'Версия'))
            ->add('internalParentCategory', null, array('label' => 'Внутренняя категория'))
            ->add('products', 'sonata_type_collection', array(
                'required' => false,
                'cascade_validation' => true,
                'by_reference' => false,
                'label' => 'Продукты',
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
            ->add('externalId')
            ->add('parentId')
            ->add('name')
            ->add('version')
        ;
    }
}
