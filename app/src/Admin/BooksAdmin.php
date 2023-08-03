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
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;

final class BooksAdmin extends AbstractAdmin
{
    private $imageDir;

    public function __construct(?string $code = null, ?string $class = null, ?string $baseControllerName = null, $dir)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->imageDir = $dir;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $image = $this->getSubject();

        $fileFormOptions = [
            'required' => false,
            'label' => 'Image',
            'constraints' => [
                new File([
                    'maxSize' => '1024k',
                    'mimeTypes' => [
                        'image/png',
                        'image/jpeg',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid image',
                ])
            ],
        ];

        if ($image && $image->getImage()) {
            $request = $this->getRequest();
            $fullPath = $request->getBasePath() . '/' . $image->getUploadDir() . '/' . $image->getImage();

            $fileFormOptions['help'] = '<img src="' . $fullPath . '" class="admin-preview" style = "width:200px;height:200px;"/>';
            $fileFormOptions['help_html'] = true;
        }

        $form->add('title', TextType::class);
        $form->add('year', IntegerType::class);
        $form->add('authors', EntityType::class, [
            'class' => Author::class,
            'choice_label' => 'name',
            'multiple' => true,
            'required' => true
        ]);
        $form->add('file', FileType::class, $fileFormOptions);
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

    public function prePersist($object) {
        $this->saveFile($object);
    }

    public function preUpdate($object) {
        $this->saveFile($object);
    }

    public function preRemove($object) {
        if ($object->getImage()) {
            unlink($object->getUploadRootDir($this->imageDir) . '/' . $object->getImage());
        }
    }

    public function saveFile($object) {
        if ($object->getFile()) {
            $object->upload($this->imageDir);
        }
    }
}