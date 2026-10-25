<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Tag;
use App\Model\Entity\VideoGame;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

/**
 * Class VideoGameTagFixtures
 *
 * @package App\Doctrine\DataFixtures
 */
final class VideoGameTagFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator $faker
    ) {
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        // Récupère tous les jeux vidéo et tous les tags depuis la base de données
        $videoGames = $manager->getRepository(VideoGame::class)->findAll();

        // Récupère tous les tags depuis la base de données
        $tags = $manager->getRepository(Tag::class)->findAll();

        // Si aucun jeu vidéo ou aucun tag n'est trouvé, on ne fait rien
        if (empty($videoGames) || empty($tags)) {
            return;
        }

        // Pour chaque jeu vidéo, on lui attribue un nombre aléatoire de tags (entre 0 et 3)
        foreach ($videoGames as $videoGame) {

            // Déterminer un nombre aléatoire de tags entre 0 et 3
            $tagCount = $this->faker->numberBetween(0, 3);

            // Si le nombre de tags est supérieur à 0, on sélectionne aléatoirement des tags et on les associe au jeu vidéo
            if ($tagCount > 0) {

                // Mélanger une copie du tableau des tags et en extraire $tagCount éléments
                $shuffledTags = $tags;
                shuffle($shuffledTags);

                // Sélectionner les premiers $tagCount tags après le mélange
                $selectedTags = array_slice($shuffledTags, 0, min($tagCount, count($tags)));

                // Associer les tags sélectionnés au jeu vidéo
                foreach ($selectedTags as $tag) {
                    $videoGame->getTags()->add($tag);
                }
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VideoGameFixtures::class,
            TagFixtures::class,
        ];
    }
}
