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

/**
 * Class ReviewFixtures
 *
 * @package App\Doctrine\DataFixtures
 */
final class ReviewFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator $faker,
        private readonly CalculateAverageRating $calculateAverageRating,
        private readonly CountRatingsPerValue $countRatingsPerValue
    ) {
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        // Récupère tous les utilisateurs et tous les jeux vidéo depuis la base de données
        $users = $manager->getRepository(User::class)->findAll();

        // Récupère tous les jeux vidéo depuis la base de données
        $videoGames = $manager->getRepository(VideoGame::class)->findAll();

        // Si aucun utilisateur ou aucun jeu vidéo n'est trouvé, on ne fait rien
        if (empty($users) || empty($videoGames)) {
            return;
        }

        //
        foreach ($videoGames as $videoGame) {

            // Choisir un nombre aléatoire d'utilisateurs pour noter ce jeu (entre 3 et 10)
            $numberOfReviewers = $this->faker->numberBetween(3, min(10, count($users)));

            // Mélanger les utilisateurs pour choisir aléatoirement
            $shuffledUsers = $users;
            shuffle($shuffledUsers); // Mélanger les utilisateurs pour choisir aléatoirement

            // Sélectionner les premiers utilisateurs après le mélange
            $reviewers = array_slice($shuffledUsers, 0, $numberOfReviewers);

            // Pour chaque utilisateur sélectionné, créer un avis pour le jeu vidéo
            foreach ($reviewers as $user) {

                // Créer un nouvel avis
                $review = new Review();
                $review->setUser($user)
                    ->setVideoGame($videoGame)
                    ->setRating($this->faker->numberBetween(1, 5))
                    ->setComment($this->faker->optional(0.8)->paragraph());

                $manager->persist($review);

                // On ajoute l'avis à la collection en mémoire du jeu
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
