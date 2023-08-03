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
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

final class BooksAdmin extends AbstractAdmin
{
    private $imageDir;
    private $em;

    public function __construct(?string $code = null, ?string $class = null, ?string $baseControllerName = null, $dir, EntityManagerInterface $em)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->imageDir = $dir;
        $this->em = $em;
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

        $conn = $this->em->getConnection();

        $sql = '
            SELECT * FROM book_author
            WHERE book_id = ' . $object->getId() . '
            ';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $data = $resultSet->fetchAllAssociative();

        $authorIds = [];
        foreach ($data as $item) {
            $authorIds[] = $item['author_id'];
        }

        $buf = $authorIds;

        foreach ($object->getAuthors() as $key => $author) {
            if (!in_array($author->getId(), $authorIds)) {
                $author->setCountBooks($author->getCountBooks() + 1);
            } else {
                unset($buf[$key]);
            }
        }

        if (!empty($buf)) {
            foreach ($buf as $bufItem) {
                $author = $this->getModelManager()->find(Author::class, $bufItem);
                $author->setCountBooks($author->getCountBooks() - 1);
            }
        }
    }

    public function preRemove($object) {
        if ($object->getImage()) {
            unlink($object->getUploadRootDir($this->imageDir) . '/' . $object->getImage());
        }

        foreach ($object->getAuthors() as $author) {
            $author->setCountBooks($author->getCountBooks() - 1);
        }
    }

    public function saveFile($object) {
        if ($object->getFile()) {
            $object->upload($this->imageDir);
        }
    }

    public function postPersist($object)
    {
        foreach ($object->getAuthors() as $author) {
            $author->setCountBooks($author->getCountBooks() + 1);
        }

        $this->getModelManager()->update($object);
    }

    public function preBatchAction($actionName, ProxyQueryInterface $query, array & $idx, $allElements = false)
    {
        //Возможно не лучшее решение, но это единственное что я придумал с массовыми действиями и флагом $allElements
        if ('delete' === $actionName) {
            if (true === $allElements) {
                $booksRepository = $this->em->getRepository(Book::class);
                $elements = $booksRepository->findAll();

                foreach ($elements as $element) {
                    $obj = $this->getObject($element->getId());
                    $this->preRemove($obj);
                }
            } else {
                foreach ($idx as $id) {
                    $obj = $this->getObject($id);
                    $this->preRemove($obj);
                }
            }
        }
    }
}