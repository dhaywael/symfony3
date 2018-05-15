<?php
// src/OC/PlatformBundle/Controller/PostController.php

namespace OC\PlatformBundle\Controller;

use OC\PlatformBundle\Entity\Advert\Post;
use OC\PlatformBundle\Form\Advert\PostEditType;
use OC\PlatformBundle\Form\Advert\PostType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostController extends Controller
{

    public function indexAction($page)
    {
        if ($page < 1) {
            throw $this->createNotFoundException("La page " . $page . " n'existe pas.");
        }

        // Ici je fixe le nombre d'annonces par page à 3
        // Mais bien sûr il faudrait utiliser un paramètre, et y accéder via $this->container->getParameter('nb_per_page')
        $nbPerPage = 5;
        // On récupère notre objet Paginator
        $listAdverts = $this->getDoctrine()
            ->getManager()
            ->getRepository(Post::class)
            ->getPosts($page, $nbPerPage)
        ;
        // On calcule le nombre total de pages grâce au count($listAdverts) qui retourne le nombre total d'annonces
        $nbPages = ceil(count($listAdverts) / $nbPerPage);

        // Si la page n'existe pas, on retourne une 404
        if ($page > $nbPages) {
            throw $this->createNotFoundException("La page " . $page . " n'existe pas.");
        }

        // On donne toutes les informations nécessaires à la vue
        return $this->render('OCPlatformBundle:Default:index.html.twig', array(
            'listAdverts' => $listAdverts,
            'nbPages' => $nbPages,
            'page' => $page,
        ));
    }

    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        // Pour récupérer une seule annonce, on utilise la méthode find($id)
        $advert = $em->getRepository(Post::class)->find($id);

        // $advert est donc une instance de OC\PlatformBundle\Entity\Advert
        // ou null si l'id $id n'existe pas, d'où ce if :
        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id " . $id . " n'existe pas.");
        }

        // Récupération de la liste des candidatures de l'annonce
        /*$listApplications = $em
        ->getRepository(Application::class)
        ->findBy(array('advert' => $advert))
        ;

        // Récupération des AdvertSkill de l'annonce
        $listAdvertSkills = $em
        ->getRepository(AdvertSkill::class)
        ->findBy(array('advert' => $advert))*/
        ;

        return $this->render('OCPlatformBundle:Default:view.html.twig', array(
            'advert' => $advert,
            /*'listApplications' => $listApplications,
        'listAdvertSkills' => $listAdvertSkills,*/
        ));
    }
    /**
     * @Security("has_role('ROLE_AUTEUR') and has_role('ROLE_AUTRE')")
     */
    public function addAction(Request $request)
    {
        $advert = new Post();
        $form = $this->get('form.factory')->create(PostType::class, $advert);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($advert);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

            return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
        }

        return $this->render('OCPlatformBundle:Default:add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $advert = $em->getRepository(Post::class)->find($id);

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id " . $id . " n'existe pas.");
        }

        $form = $this->get('form.factory')->create(PostEditType::class, $advert);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            // Inutile de persister ici, Doctrine connait déjà notre annonce
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

            return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
        }

        return $this->render('OCPlatformBundle:Default:edit.html.twig', array(
            'advert' => $advert,
            'form' => $form->createView(),
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $advert = $em->getRepository('OCPlatformBundle:Advert\Post')->find($id);

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id " . $id . " n'existe pas.");
        }

        // On crée un formulaire vide, qui ne contiendra que le champ CSRF
        // Cela permet de protéger la suppression d'annonce contre cette faille
        $form = $this->get('form.factory')->create();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->remove($advert);
            $em->flush();

            $request->getSession()->getFlashBag()->add('info', "L'annonce a bien été supprimée.");

            return $this->redirectToRoute('oc_platform_home');
        }

        return $this->render('OCPlatformBundle:Default:delete.html.twig', array(
            'advert' => $advert,
            'form' => $form->createView(),
        ));
    }

    public function menuAction($limit)
    {
        $em = $this->getDoctrine()->getManager();

        $listAdverts = $em->getRepository(post::class)->findBy(
            array(), // Pas de critère
            array('date' => 'desc'), // On trie par date décroissante
            $limit, // On sélectionne $limit annonces
            0// À partir du premier
        );

        return $this->render('OCPlatformBundle:Default:menu.html.twig', array(
            'listAdverts' => $listAdverts,
        ));
    }
}
