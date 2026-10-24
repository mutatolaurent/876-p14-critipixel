<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Tag;
use App\Model\Entity\VideoGame;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

final class VideoGameTagFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator $faker
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $videoGames = $manager->getRepository(VideoGame::class)->findAll();
        $tags = $manager->getRepository(Tag::class)->findAll();

        if (empty($videoGames) || empty($tags)) {
            return;
        }

        foreach ($videoGames as $videoGame) {
            // Déterminer un nombre aléatoire de tags entre 0 et 3
            $tagCount = $this->faker->numberBetween(0, 3);

            if ($tagCount > 0) {
                // Mélanger une copie du tableau des tags et en extraire $tagCount éléments
                $shuffledTags = $tags;
                shuffle($shuffledTags);
                $selectedTags = array_slice($shuffledTags, 0, min($tagCount, count($tags)));

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
