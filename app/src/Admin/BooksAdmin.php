<?php

namespace App\Admin;

use App\Entity\Author;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class BooksAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('title', TextType::class);
        $form->add('year', IntegerType::class);
        $form->add('authors', EntityType::class, [
            'class' => Author::class,
            'choice_label' => 'name',
            'multiple' => true,
            'required' => true
        ]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagrid): void
    {
        $datagrid->add('title');
        $datagrid->add('year');
        $datagrid->add('authors', null, [
            'field_type' => EntityType::class,
            'field_options' => [
                'class' => Author::class,
                'choice_label' => 'name',
                'multiple' => true,
            ],
        ]);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('title');
        $list->add('year');
        $list->add('authors', null, [
            'associated_property' => 'name',
        ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('title');
        $show->add('year');
        $show->add('authors', null, [
            'associated_property' => 'name',
        ]);
    }
}