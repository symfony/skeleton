<?php

namespace App\Service;

use App\Repository\KenoZiehungenRepository;

class KenoGenerator
{
    public function __construct(
        private KenoZiehungenRepository $repository
    ) {}

    /**
     * Generiert 20 einzigartige Keno-Zahlen (1-70), basierend auf historischen Wochentag-Statistiken.
     * Seltener gezogene Zahlen an diesem Wochentag haben eine höhere Wahrscheinlichkeit.
     * * @param \DateTimeInterface $zielDatum Das Datum in der Zukunft, für das die Prognose gilt.
     * @return int[]
     */
    public function generateUniqueKenoNumbers(\DateTimeInterface $zielDatum): array
    {
        // 1. Wochentag ermitteln (1 = Montag, 7 = Sonntag)
        $wochentag = (int) $zielDatum->format('N');

        // 2. Historische Häufigkeiten für diesen Wochentag laden
        $stats = $this->repository->getStatsForWeekday($wochentag);

        // 3. Gewichte berechnen: Seltenere Zahlen erhalten ein höheres Gewicht
        $maxHaeufigkeit = max($stats);
        $gewichte = [];
        foreach ($stats as $zahl => $haeufigkeit) {
            // Umgekehrte Gewichtung + 1 (damit auch die am häufigsten gezogene Zahl eine minimale Chance hat)
            $gewichte[$zahl] = ($maxHaeufigkeit - $haeufigkeit) + 1;
        }

        $maxAttempts = 100;
        $attempts = 0;

        do {
            $zahlen = $this->drawWeightedNumbers($gewichte, 20);
            sort($zahlen);
            $attempts++;

            if ($attempts > $maxAttempts) {
                throw new \RuntimeException('Es konnte trotz gewichteter Analyse keine historisch einzigartige Kombination generiert werden.');
            }

        // Kollisionsprüfung mit der Datenbank
        } while ($this->repository->combinationExists($zahlen));

        return $zahlen;
    }

    /**
     * Zieht $count eindeutige Zahlen basierend auf den definierten Gewichten.
     *
     * @param array<int, int> $gewichte [Zahl => Gewicht]
     * @param int $count Anzahl der zu ziehenden Zahlen (z.B. 20)
     * @return int[]
     */
    private function drawWeightedNumbers(array $gewichte, int $count): array
    {
        $gezogeneZahlen = [];
        $aktuelleGewichte = $gewichte; // Lokale Kopie, um gezogene Zahlen zu entfernen

        for ($i = 0; $i < $count; $i++) {
            $gesamtGewicht = array_sum($aktuelleGewichte);
            if ($gesamtGewicht <= 0) {
                break;
            }

            $rand = random_int(1, $gesamtGewicht);
            $summe = 0;

            foreach ($aktuelleGewichte as $zahl => $gewicht) {
                $summe += $gewicht;
                if ($rand <= $summe) {
                    $gezogeneZahlen[] = $zahl;
                    // Zahl entfernen, damit sie nicht doppelt gezogen wird
                    unset($aktuelleGewichte[$zahl]);
                    break;
                }
            }
        }

        return $gezogeneZahlen;
    }
}
