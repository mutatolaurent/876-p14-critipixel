<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class TagFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $tagData = [
            ['name' => 'Action', 'code' => 'action'],
            ['name' => 'Aventure', 'code' => 'aventure'],
            ['name' => 'RPG', 'code' => 'rpg'],
            ['name' => 'Stratégie', 'code' => 'strategie'],
            ['name' => 'Simulation', 'code' => 'simulation'],
            ['name' => 'Sport', 'code' => 'sport'],
            ['name' => 'Course', 'code' => 'course'],
            ['name' => 'Indépendant', 'code' => 'independant'],
            ['name' => 'FPS', 'code' => 'fps'],
            ['name' => 'Horreur', 'code' => 'horreur'],
        ];

        foreach ($tagData as $data) {
            $tag = (new Tag())
                ->setName($data['name'])
                ->setCode($data['code']);

            $manager->persist($tag);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            VideoGameFixtures::class,
        ];
    }
}
