<?php

namespace App\Controller;

use DateTime;
use App\Entity\Article;
use App\Form\ArticleFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/tableau-de-bord", name="show_dashboard", methods={"GET"})
     */

    public function showDashboard(EntityManagerInterface $entityManager): Response
    {
        $articles = $entityManager->getRepository(Article::class)->findBy([
            'deletedAt' => null
        ]);

        return $this->render('admin/show_dashboard.html.twig', [
            'articles' => $articles,
        ]);
    }
    /**
     * @Route("/creer-un-article", name="create_article", methods={"GET|POST"})
     */
    public function createArticle(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $article = new Article();

        $form = $this->createForm(ArticleFormType::class, $article)
            ->handleRequest($request);

        // Traitement du formulaire
        if ($form->isSubmitted() && $form->isValid()) {

            // Pour accéder à une valeur d'un input de $form, on fait :
            // $form->get('title')->getData()

            // Setting des propriétés non mappées dans le formulaire
            $article->setAlias($slugger->slug($article->getTitle()));
            $article->setCreatedAt(new DateTime());
            $article->setUpdatedAt(new DateTime());

            // Variabilisation du fichier 'photo' uploadé.
            /** @var UploadedFile $file */
            $file = $form->get('photo')->getData();

            // if (isset($file) === true)
            // Si un fichier est uploadé (depuis le formulaire)
            if ($file) {
                // Maintenant il s'agit de reconstruire le nom du fichier pour le sécuriser.

                // 1ère ÉTAPE : on déconstruit le nom du fichier et on variabilise.
                $extension = '.' . $file->guessExtension();
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                // Assainissement du nom de fichier (du filename)
                // $safeFilename = $slugger->slug($originalFilename);

                $safeFilename = $article->getAlias();

                // 2ème ÉTAPE : on reconstruit le nom du fichier maintenant qu'il est safe.
                // uniqid() est une fonction native de PHP, elle permet d'ajouter une valeur numérique (id) unique et auto-générée.
                $newFilename = $safeFilename . '_' . uniqid() . $extension;

                // try/catch fait partie de PHP nativement.
                try {
                    // On a configuré un paramètre 'uploads_dir' dans le ffichier services.yaml
                    // ce paramètres contient le chemin absolu de notre dossier d'upload de photo
                    $file->move($this->getParameter('uplaods_dir'), $newFilename);
                    // On set le NOM de la photo, pas le CHEMIN 
                    $article->setphoto($newFilename);
                } catch (FileException $exception) {
                } // END catch()




            } // END if($file)
            $entityManager->persist($article);
            $entityManager->flush();


            //  Ici on ajoute un message qu'on affichera en twig dans le fichier twig

            $this->addFlash('success', 'Votre article est bien en ligne! ');

            // return $this->redirectToRoute('default_home');
        } // END if($form)

        return $this->render('admin/form/form_article.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     *@Route ("/modifier-un-article/{id}", name="update_article", methods={"GET|POST"}) //get et post car utilisation d'un formulaire
     *
        // L'action est exécutée 2 fois et accessible par les deux méthods (GET|POST)
     */


    public function updateArticle(Article $article, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        # Condition ternaire : $article->getPhoto() ?? ''
        # => est égal à: isset($article->getPhoto()) ? $article->getPhoto(): '';
        $originalPhoto = $article->getPhoto() ?? '';
        // 1er tour en méthode GET 
        $form = $this->createForm(ArticleFormType::class, $article, [
            'photo' => $originalPhoto
        ])->handleRequest($request);

        // 2ème TOUR de l'action en méthode POST 

        if ($form->isSubmitted() && $form->isValid()) {

            $article->setAlias($slugger->slug($article->getTitle()));
            $article->setUpdatedAt(new DateTime());

            $file = $form->get('photo')->getData();

            if ($file) { {

                    $extension = '.' . $file->guessExtension();
                    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                    $safeFilename = $article->getAlias();


                    $newFilename = $safeFilename . '_' . uniqid() . $extension;


                    try {

                        $file->move($this->getParameter('uplaods_dir'), $newFilename);

                        $article->setphoto($newFilename);
                    } catch (FileException $exception)// END catch()
                    # code à exécuteer si une erreur est attrapée
                    
                    else {
                        $article->setPhoto($originalPhoto);

                    }





                } // END if($file)
                $entityManager->persist($article);
                $entityManager->flush();

                $this ->addFladh('success', "L'article". $article->getTitle()."à bien été modifié !" );
                return $this->redirectToRoute("show_dashboard");
            }//END if ($form)
        }


        // On retourne la vue pour la méthode GET
        return $this->render('admin/form/form_article.html.twig', [
            'form' => $form->createView(),
            'article' => $article
        ]);
    }

    /**
     * @Route ("/archiver-un-article",name ="soft_delete_article", methods={"GET"})
     */
    public function softDeleteArticle (Article $article, EntityManagerInterface $entityManager): Response
    // on set la propriété deletedAt pour archiver l'article.
    //  de l'autre côté on affichera les articles où deletedAt == null
    {
           $article->setDeletedAt(new DateTime());

           $entityManager->persist($article);
           $entityManager->flush();

           $this->addFlash('success', "L'article". $article->getTitle()."a bien été archivé");
           return $this->redirectToRoute('show_dashboard');

    }

    /**
     * @Route("/supprimer-un-article", name="hard_delete_article", methods={"GET"})
     * 
     */
    public function hardDeleteArticle(Article $article, EntityManagerInterface $entityManager): Response
    {
    $entityManager->remove($article);
    $this->addflash('success', "L'article". $article->getTitle(). "a bien été supprimé de la base de donné ");
    return $this->redirectToRoute("show_dashboard");
    }

    /**
     * @Route("/restaurer-un-article/{id}", name="restore_article", methods={"GET"})
     */
    public function restoreArticle(Article $article, EntityManagerInterface $entityManager): Response
    {
         $article->setdeletedAt();
         
         $entityManager->persist($article);
         $entityManager->flush();

         $this->addflash('success', "L'article". $article->getTitle(). "a bien été restauré ");
         return $this->redirectToRoute("show_dashboard");

    }
}  //END CLASS
