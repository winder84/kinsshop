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
            ->add('category')
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
            ->add('category')
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
            ->add('name', null, array('label' => 'Наименование'))
            ->add('model', null, array('label' => 'Модель'))
            ->add('externalId', null, array('label' => 'Внешний Id'))
            ->add('category', null, array('label' => 'Категория', 'required' => false))
            ->add('currencyId', null, array('label' => 'Валюта'))
            ->add('description', null, array('label' => 'Описание'))
            ->add('seoDescription', null, array('label' => 'SEO описание', 'required' => false))
            ->add('seoKeywords', null, array('label' => 'SEO ключевые слова', 'required' => false))
            ->add('modifiedTime', null, array('label' => 'Время обновления'))
            ->add('price', null, array('label' => 'Цена', 'required' => false))
            ->add('typePrefix', null, array('label' => 'Префикс', 'required' => false))
            ->add('url', null, array('label' => 'Url'))
            ->add('vendor', null, array('label' => 'Бренд'))
            ->add('vendorCode', null, array('label' => 'Код бренда'))
            ->add('version', null, array('label' => 'Версия'))
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('externalId')
            ->add('category')
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
