<?php

namespace App\Doctrine\DataFixtures;

// use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
// use App\Rating\CalculateAverageRating;
// use App\Rating\CountRatingsPerValue;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

use function array_fill_callback;

/**
 * Class VideoGameFixtures
 *
 * @package App\Doctrine\DataFixtures
 */
final class VideoGameFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator $faker,
        // private readonly CalculateAverageRating $calculateAverageRating,
        // private readonly CountRatingsPerValue $countRatingsPerValue
    ) {
    }

    /**
     * Charge les fixtures pour les jeux vidéo.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        // $users = $manager->getRepository(User::class)->findAll();

        // Crée 50 jeux vidéo avec des données aléatoires
        $videoGames = array_fill_callback(
            0,
            50,
            fn (int $index): VideoGame => (new VideoGame())
            ->setTitle(sprintf('Jeu vidéo %d', $index))
            ->setSlug(sprintf('jeu-video-%d', $index))
            ->setDescription($this->faker->paragraphs(10, true))
            ->setReleaseDate(new DateTimeImmutable())
            ->setTest($this->faker->paragraphs(6, true))
            ->setRating(($index % 5) + 1)
            ->setImageName(sprintf('video_game_%d.png', $index))
            ->setImageSize(2_098_872)
        );

        // TODO : Ajouter les tags aux jeux vidéo
        // Les Tags sont créés dans TagFixtures.
        // Les Tags sont associés aux jeux vidéo dans VideoGameTagFixtures.

        // Pour chaque jeu vidéo du tableau, appelle la méthode persist du manager
        array_walk($videoGames, [$manager, 'persist']);

        $manager->flush();

        // TODO : Ajouter des reviews aux jeux vidéo
        // Les Reviews sont créées dans ReviewFixtures.

    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
