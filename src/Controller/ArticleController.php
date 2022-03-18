<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArticleController extends AbstractController
{
    /**
     * @Route("/voir/{cat_alias}/{artcle_alias}_{id}", name="show_article", methods={"GET"})
     */
    public function showArticle(Article $article, EntityManagerInterface $entityManager): Response
    {
        $commentaries = $entityManager->getRepository(Commentary::class)->findBy(['article' => $article->getId()]);
        return $this->render('article/show_article.html.twig')[
            'article' => $article,
            'commentaries' => $commentaries,

        ]
    }

}
