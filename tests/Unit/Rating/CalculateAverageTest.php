<?php

declare(strict_types=1);

namespace App\Tests\Rating;

use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CalculateAverageTest extends TestCase
{
    /**
     * Teste le calcul de la moyenne des notes pour un jeu vidéo sans avis.
     * @param array $ratings Tableau de notes pour le jeu vidéo
     * @param int|null $expectedAverage Moyenne attendue (arrondie avec ceil)
     *
     */
    #[DataProvider('provideVideoGameRatingsData')]
    public function testCalculateAverageWithReviews(array $ratings, int $expectedAverage): void
    {
        // 1. Arrange : Création du jeu de données
        $videoGame = new VideoGame();

        foreach ($ratings as $ratingValue) {
            // On mock l'entité Review pour simuler différentes notes
            $review = $this->createMock(Review::class);
            $review->method('getRating')->willReturn($ratingValue);

            // On ajoute l'avis à la collection du VideoGame
            $videoGame->getReviews()->add($review);
        }

        $handler = new RatingHandler();

        // 2. Act : Exécution de la méthode à tester
        $handler->calculateAverage($videoGame);

        // 3. Assert : Vérification du résultat attendu (avec le ceil)
        $this->assertSame($expectedAverage, $videoGame->getAverageRating());
    }

    /**
     * Fournit 5 cas de test différents (notes -> moyenne attendue avec ceil)
     */
    public static function provideVideoGameRatingsData(): array
    {
        return [
            'Cas 1 : Moyenne avec arrondi supérieur' => [
                'ratings' => [3, 4], // Somme = 7, Moyenne = 3.5 -> ceil(3.5) = 4
                'expectedAverage' => 4,
            ],
            'Cas 2 : Moyenne entière exacte' => [
                'ratings' => [1, 2, 3], // Somme = 6, Moyenne = 2.0 -> ceil(2.0) = 2
                'expectedAverage' => 2,
            ],
            'Cas 3 : Un seul avis unique' => [
                'ratings' => [3], // Somme = 3, Moyenne = 3.0 -> ceil(3.0) = 3
                'expectedAverage' => 3,
            ],
            'Cas 4 : Moyenne fractionnaire complexe' => [
                'ratings' => [2, 2, 3], // Somme = 7, Moyenne = 2.33... -> ceil = 3
                'expectedAverage' => 3,
            ],
            'Cas 5 : Notes majoritairement hautes' => [
                'ratings' => [5, 5, 5, 4], // Somme = 19, Moyenne = 4.75 -> ceil = 5
                'expectedAverage' => 5,
            ],
        ];
    }

    /**
     * Teste le comptage des avis par valeur pour un jeu vidéo.
     *
     * @param array $ratings Tableau de notes pour le jeu vidéo
     * @param array $expectedCounts Tableau associatif des compteurs attendus [1 => nb, 2 => nb, ...]
     */
    #[DataProvider('provideRatingsForCountingData')]
    public function testCountRatingsPerValue(array $ratings, array $expectedCounts): void
    {
        // 1. Arrange : Création du VideoGame et ajout des avis factices (mocks)
        $videoGame = new VideoGame();

        foreach ($ratings as $ratingValue) {
            $review = $this->createMock(Review::class);
            $review->method('getRating')->willReturn($ratingValue);
            $videoGame->getReviews()->add($review);
        }

        $handler = new RatingHandler();

        // 2. Act : Exécution de la méthode de comptage
        $handler->countRatingsPerValue($videoGame);

        // 3. Assert : Vérification de chaque compteur dans l'objet imbriqué
        $counters = $videoGame->getNumberOfRatingsPerValue();

        $this->assertSame($expectedCounts[1], $counters->getNumberOfOne(), 'Le nombre de notes 1 est incorrect');
        $this->assertSame($expectedCounts[2], $counters->getNumberOfTwo(), 'Le nombre de notes 2 est incorrect');
        $this->assertSame($expectedCounts[3], $counters->getNumberOfThree(), 'Le nombre de notes 3 est incorrect');
        $this->assertSame($expectedCounts[4], $counters->getNumberOfFour(), 'Le nombre de notes 4 est incorrect');
        $this->assertSame($expectedCounts[5], $counters->getNumberOfFive(), 'Le nombre de notes 5 est incorrect');
    }

    /**
     * Fournit différents scénarios de notes et les résultats de comptage attendus [1 => nb, 2 => nb, ...]
     */
    public static function provideRatingsForCountingData(): array
    {
        return [
            'Cas 1 : Aucun avis' => [
                'ratings' => [],
                'expectedCounts' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
            ],
            'Cas 2 : Notes variées' => [
                'ratings' => [1, 3, 3, 4, 5, 5, 5],
                'expectedCounts' => [1 => 1, 2 => 0, 3 => 2, 4 => 1, 5 => 3],
            ],
            'Cas 3 : Uniquement des notes maximales' => [
                'ratings' => [5, 5],
                'expectedCounts' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 2],
            ],
            'Cas 4 : Notes de chaque valeur (1 à 5)' => [
                'ratings' => [1, 2, 3, 4, 5],
                'expectedCounts' => [1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1],
            ],
        ];
    }
}
