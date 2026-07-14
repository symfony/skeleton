<?php

namespace App\Controller;

use App\Service\KenoGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class KenoGeneratorController extends AbstractController
{
    #[Route('/keno/prognose', name: 'app_keno_prognose')]
    public function prognose(KenoGenerator $generator): Response
    {
        // Wir prognostizieren für die nächste anstehende Ziehung (morgen)
        $prognoseDatum = new \DateTimeImmutable('tomorrow');

        // Übersetzungs-Array für Wochentage im Template
        $wochentage = [
            1 => 'Montag',
            2 => 'Dienstag',
            3 => 'Mittwoch',
            4 => 'Donnerstag',
            5 => 'Freitag',
            6 => 'Samstag',
            7 => 'Sonntag'
        ];
        $wochentagName = $wochentage[(int)$prognoseDatum->format('N')];

        try {
            // Generiert die historisch einzigartigen, gewichteten Zahlen
            $prognoseZahlen = $generator->generateUniqueKenoNumbers($prognoseDatum);
        } catch (\RuntimeException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        return $this->render('keno_generator/index.html.twig', [
            'zahlen' => $prognoseZahlen,
            'prognose_datum' => $prognoseDatum,
            'wochentag_name' => $wochentagName,
            'generiert_am' => new \DateTimeImmutable(),
        ]);
    }
}
