<?php

namespace App\Rating;

use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;

final readonly class RatingHandler implements CalculateAverageRating, CountRatingsPerValue
{
    public function calculateAverage(VideoGame $videoGame): void
    {
        if (0 === \count($videoGame->getReviews())) {
            $videoGame->setAverageRating(null);

            return;
        }

        // Calculer la somme des notes et la moyenne à partir de la collection de critiques
        $ratingsSum = array_sum(
            array_map(
                static fn (Review $review): int => $review->getRating(),
                $videoGame->getReviews()->toArray() // Tableau d'objets Review qui sera parcouru par array_map pour extraire les notes
            )
        );

        $videoGame->setAverageRating((int) ceil($ratingsSum / \count($videoGame->getReviews())));
    }

    public function countRatingsPerValue(VideoGame $videoGame): void
    {
        $videoGame->getNumberOfRatingsPerValue()->clear();

        if (0 === \count($videoGame->getReviews())) {
            return;
        }

        foreach ($videoGame->getReviews() as $review) {
            match ($review->getRating()) {
                1 => $videoGame->getNumberOfRatingsPerValue()->increaseOne(),
                2 => $videoGame->getNumberOfRatingsPerValue()->increaseTwo(),
                3 => $videoGame->getNumberOfRatingsPerValue()->increaseThree(),
                4 => $videoGame->getNumberOfRatingsPerValue()->increaseFour(),
                default => $videoGame->getNumberOfRatingsPerValue()->increaseFive(),
            };
        }
    }
}
