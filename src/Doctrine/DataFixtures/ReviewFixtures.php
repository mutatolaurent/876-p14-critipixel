<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Review;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use App\Rating\CalculateAverageRating;
use App\Rating\CountRatingsPerValue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

final class ReviewFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator $faker,
        private readonly CalculateAverageRating $calculateAverageRating,
        private readonly CountRatingsPerValue $countRatingsPerValue
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();
        $videoGames = $manager->getRepository(VideoGame::class)->findAll();

        if (empty($users) || empty($videoGames)) {
            return;
        }

        foreach ($videoGames as $videoGame) {
            // Choisir un nombre aléatoire d'utilisateurs pour noter ce jeu (entre 3 et 10)
            $numberOfReviewers = $this->faker->numberBetween(3, min(10, count($users)));
            $shuffledUsers = $users;
            shuffle($shuffledUsers);
            $reviewers = array_slice($shuffledUsers, 0, $numberOfReviewers);

            foreach ($reviewers as $user) {
                $review = new Review();
                $review->setUser($user)
                    ->setVideoGame($videoGame)
                    ->setRating($this->faker->numberBetween(1, 5))
                    ->setComment($this->faker->optional(0.8)->paragraph());

                $manager->persist($review);

                // Important : on ajoute l'avis à la collection en mémoire du jeu
                // pour que le RatingHandler puisse les compter immédiatement.
                $videoGame->getReviews()->add($review);
            }

            // Utilisation des services du RatingHandler via leurs interfaces
            $this->countRatingsPerValue->countRatingsPerValue($videoGame);
            $this->calculateAverageRating->calculateAverage($videoGame);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            VideoGameFixtures::class,
            TagFixtures::class,
            VideoGameTagFixtures::class,
        ];
    }
}
