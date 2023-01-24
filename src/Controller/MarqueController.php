<?php

namespace App\Controller;

use App\Entity\Marque;
use App\Entity\Modele;
use App\Form\MarqueType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MarqueController extends AbstractController
{
    #[Route('/', name: 'app_marque')]
    public function index(EntityManagerInterface $em): Response
    {
        $marques = $em->getRepository(Marque::class)->findAll();
        $modele = $em->getRepository(Modele::class)->findAll();

        return $this->render('marque/index.html.twig', [
            "marques"=>$marques,
            "modele"=>$modele,
        ]);
    }

    #[Route('/marque_ajouter', name: 'marque')]
    public function marque(EntityManagerInterface $em, Request $request): Response
    {
        $marque= new Marque;

        $form = $this->createForm(MarqueType::class, $marque);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $logoFile = $form->get('logo')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($logoFile) {
                $newFilename = uniqid().'.'.$logoFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $logoFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    return $this->redirectToRoute('app_marque');
                }

                // updates the 'logoFilename' property to store the PDF file name
                // instead of its contents
                $marque->setlogo($newFilename);
            }
            $em->persist($marque);
            $em->flush();
        }

        return $this->render('marque/marques.html.twig', [
            'ajout'=> $form->createView(),
        ]);
    }

    #[Route('/marque/{id}', name: 'marque_produit')]
    public function marqueProduit( Marque $marque=null, EntityManagerInterface $em): Response
    {
        if($marque == null){
            return $this->redirectToRoute('app_marque');
        }
        // $modele = $em->getRepository(Modele::class)->findAll();
        return $this->render('marque/marque_produit.html.twig', [
            'marque' => $marque,
            // 'modele' => $modele->getMarque(),
        ]);
    }

    #[Route('/modeledelete/{id}', name: 'modele_delete')]
    public function delete(Marque $marque= null, EntityManagerInterface $em): Response
    {
        if($marque == null){
            
        }else {
            $em->remove($marque);
            $em->flush();
        }

        return $this->redirectToRoute('app_marque');
    }
}
