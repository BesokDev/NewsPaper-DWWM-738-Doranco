<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleFormType;
use App\Repository\ArticleRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin')]
class ArticleController extends AbstractController
{
    #[Route('/ajouter-un-article', name: 'create_article', methods: ['GET', 'POST'])]
    public function createArticle(ArticleRepository $repository, Request $request, SluggerInterface $slugger): Response
    {
        $article = new Article();

        $form = $this->createForm(ArticleFormType::class, $article)
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $article->setCreatedAt(new DateTime());
            $article->setUpdatedAt(new DateTime());
            $article->setAlias($slugger->slug($article->getTitle()));

            # Set de la relation entre Article et User
            $article->setAuthor($this->getUser());

            /** @var UploadedFile $photo */
            $photo = $form->get('photo')->getData();

            if($photo) {
                
            } //end if($photo)
        } // end if($form)

        return $this->render('admin/article/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

} // end class