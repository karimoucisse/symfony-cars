<?php

namespace App\Controller;

use App\Entity\Modele;
use App\Form\ModeleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/modele')]
class ModeleController extends AbstractController
{
    // ajouter des modele
    #[Route('/modele_ajouter', name: 'app_modele')]
    public function index(EntityManagerInterface $em, Request $request): Response
    {

        $modele= new Modele;

        $form = $this->createForm(ModeleType::class, $modele);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $imageFile = $form->get('image')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    return $this->redirectToRoute('app_produit');
                }

                // updates the 'imageFilename' property to store the PDF file name
                // instead of its contents
                $modele->setimage($newFilename);
            }

            $em->persist($modele);
            $em->flush();
        }

        return $this->render('modele/index.html.twig', [
            'ajout'=> $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'modele')]
    public function marqueProduit( Modele $modele=null): Response
    {
        if($modele == null){
            return $this->redirectToRoute('app_modele');
        }

        return $this->render('modele/modele.html.twig', [
            'modele' => $modele,
        ]);
    }

    #[Route('/modifier/{id}', name:'modeleForm')]
    public function category(Modele $modele = null, EntityManagerInterface $em, Request $request): Response
    {
        if($modele == null){
            return $this->redirectToRoute('app_modele');
        }
        
        $form = $this->createForm(ModeleType::class, $modele);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($modele); // permet l'ajout et la maj
            $em->flush(); // execute la sauvegarde
        }

        return $this->render('modele/modeleForm.html.twig', [
            'modele' => $modele,
            'modifier' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'modele_delete')]
    public function delete(Modele $modele= null, EntityManagerInterface $em): Response
    {
        if($modele == null){
            
        }else {
            $em->remove($modele);
            $em->flush();
        }

        return $this->redirectToRoute('app_modele');
    }
}
