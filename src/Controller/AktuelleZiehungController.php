<?php

namespace App\Controller;

use App\Repository\KenoZiehungenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AktuelleZiehungController extends AbstractController
{
    #[Route('/aktuelle/ziehung', name: 'app_aktuelle_ziehung')]
    public function index(KenoZiehungenRepository $repository): Response
    {
        // Holt die neueste Ziehung
    $letzteZiehung = $repository->findOneBy([], ['ziehungs_datum' => 'DESC']);

    if (!$letzteZiehung) {
        throw $this->createNotFoundException('Keine Ziehungen gefunden.');
    }

    // Dank MartinGeorgiev-Bundle kriegst du hier ein echtes PHP-Array!
    $zahlen = $letzteZiehung->getZahlen();

    return $this->render('aktuelle_ziehung/index.html.twig', [
        'datum' => $letzteZiehung->getZiehungsDatum(),
        'zahlen' => $zahlen,
    ]);
    }
}
