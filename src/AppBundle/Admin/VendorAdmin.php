<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class VendorAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('code', null, array('label' => 'Код бренда'))
            ->add('name', null, array('label' => 'Наименование бренда'))
            ->add('version', null, array('label' => 'Версия'))
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
            ->add('code', null, array('label' => 'Код бренда'))
            ->add('name', null, array('label' => 'Наименование бренда'))
            ->add('alias', null, array('label' => 'Alias', 'required' => false))
            ->add('site', null, array('label' => 'Магазин'))
            ->add('ourChoice', null, array(
                    'label'    => 'Наш выбор',
                    'required' => false,
                )
            )
            ->add('version', null, array('label' => 'Версия'))
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
            ->add('ourChoice', null, array(
                    'label'    => 'Наш выбор',
                    'required' => false,
                )
            )
            ->add('code', null, array('label' => 'Код бренда'))
            ->add('name', null, array('label' => 'Наименование бренда'))
            ->add('alias', null, array('label' => 'Alias', 'required' => false))
            ->add('version', null, array('label' => 'Версия'))
            ->add('site', null, array('label' => 'Магазин'))
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('code')
            ->add('name')
            ->add('version')
        ;
    }
}
