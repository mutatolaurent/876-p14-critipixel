<?php

declare(strict_types=1);

namespace App\Tests\Rating;

use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CountRatingsPerValueTest extends TestCase
{
    #[DataProvider('provideRatingsForCountingData')]
    public function testCountRatingsPerValueWithReviews(array $ratings, array $expectedCounts): void
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
