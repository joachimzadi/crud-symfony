<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\Stagiaire;
use App\Services\AppUtils;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", name="stagiaire_")
 * Class StagiaireController
 * @package App\Controller
 */
class StagiaireController extends AbstractController
{
    /**
     * @Route("/", name="liste")
     */
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $stagiaires = $em->getRepository(Stagiaire::class)->findAll();
        return $this->render('stagiaire/liste.html.twig', [
            "stagiaires" => $stagiaires
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit", methods={"GET"})
     * @param null $id
     * @return Response
     */
    public function editForm($id = null): Response
    {
        $stagiaire = new Stagiaire();
        $adresse = new Adresse();
        $stagiaire->setAdresse($adresse);

        $titre = empty($id) ? "Ajouter un stagiaire" : "Modifier un stagiaire";

        if (!empty($id)) {
            $em = $this->getDoctrine()->getManager();
            $stagiaire = $em->getRepository(Stagiaire::class)->find($id);
        }

        return $this->render("stagiaire/_form.html.twig", [
            "id" => $id,
            "titre" => $titre,
            "stagiaire" => $stagiaire
        ]);
    }

    /**
     * @Route("/merge", name="merge", methods={"POST"})
     * @param Request $request
     * @param AppUtils $utils
     * @return RedirectResponse
     * @throws Exception
     */
    public function createOrUpdate(Request $request, AppUtils $utils): RedirectResponse
    {
        $adresse = new Adresse();
        $codePostal = $request->get('codePostal');
        $adresse->setCodePostale($codePostal);
        $ville = $utils->capitalize($request->get('ville'));
        $adresse->setVille($ville);

        $em = $this->getDoctrine()->getManager();
        $adresse_db = $em->getRepository(Adresse::class)->findOneBy(array('ville' => $ville));

        $stagiaire = new Stagiaire();

        if (empty($adresse_db)) {
            $em->persist($adresse);
            $stagiaire->setAdresse($adresse);
        } else {
            $stagiaire->setAdresse($adresse_db);
        }

        $prenom = $utils->capitalize($request->get('prenom'));
        $stagiaire->setPrenom($prenom);

        $email = mb_strtolower($request->get('email'));
        $stagiaire->setEmail($email);
        $ddn = new DateTime($request->get('ddn'));
        $stagiaire->setDdn($ddn);

        $id = $request->get('id');

        if (empty($id)) {
            $em->persist($stagiaire);
        } else {
            $stagiaire_db = $em->getRepository(Stagiaire::class)->find($id);
            if (isset($stagiaire_db)) {
                $stagiaire_db->setPrenom($stagiaire->getPrenom());
                $stagiaire_db->setEmail($stagiaire->getEmail());
                $stagiaire_db->setDdn($stagiaire->getDdn());
                $stagiaire_db->setAdresse($stagiaire->getAdresse());
                $em->merge($stagiaire_db);
            }
        }

        $em->flush();
        return $this->redirectToRoute("stagiaire_liste");
    }

    /**
     * @Route("/delete/{id}", name="delete", methods={"GET"})
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function delete(Request $request, $id): RedirectResponse
    {
        $em = $this->getDoctrine()->getManager();
        $stagiaire = $em->getRepository(Stagiaire::class)->find($id);
        if (isset($stagiaire)) {
            $em->remove($stagiaire);
            $em->flush();
            $this->addFlash('success', "Le stagiaire d'id $id a été supprimer avec succès");
        }
        return $this->redirectToRoute("stagiaire_liste");
    }
}
