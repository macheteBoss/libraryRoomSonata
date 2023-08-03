<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="app_index", methods={"GET"})
     */
    public function index(): Response
    {
        $connection = $this->getDoctrine()->getConnection();

        $sql = '
            SELECT b.title, COUNT(a.id) AS countAuthors FROM book b 
                    INNER JOIN book_author ba ON b.id = ba.book_id 
                    INNER JOIN author a ON a.id = ba.author_id
                    GROUP BY b.title
                    HAVING countAuthors > 2
            ';
        $stmt = $connection->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $data = $resultSet->fetchAllAssociative();

        $items = [];
        foreach ($data as $item) {
            $items[$item['title']] = $item['countAuthors'];
        }

        return $this->render('Main/index.html.twig', [
            'items' => $items,
        ]);
    }
}