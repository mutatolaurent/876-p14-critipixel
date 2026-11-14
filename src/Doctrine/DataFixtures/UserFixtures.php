<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Crée 10 utilisateurs avec des emails et mots de passe différents
        $users = array_map(
            static fn (int $index): User => (new User())
                ->setEmail(\sprintf('user+%d@email.com', $index))
                ->setPlainPassword('password')
                ->setUsername(\sprintf('user+%d', $index)),
            range(0, 9)
        );

        // Persiste les utilisateurs dans la base de données
        array_walk($users, [$manager, 'persist']);

        $manager->flush();
    }
}
