<?php

namespace App\Tests\Functional\Auth;

use App\Model\Entity\User;
use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegisterTest extends FunctionalTestCase
{
    public function testThatRegistrationShouldSucceeded(): void
    {
        // Nettoie un éventuel utilisateur créé lors d'un run précédent
        $this->removeTestUser();

        $this->get('/auth/register');

        $this->client->submitForm('S\'inscrire', self::getFormData());

        self::assertResponseRedirects('/auth/login');

        $user = $this->getEntityManager()->getRepository(User::class)->findOneByEmail('user@email.com');

        $userPasswordHasher = $this->service(UserPasswordHasherInterface::class);

        self::assertNotNull($user);
        self::assertSame('username', $user->getUsername());
        self::assertSame('user@email.com', $user->getEmail());
        self::assertTrue($userPasswordHasher->isPasswordValid($user, 'SuperPassword123!'));
    }

    /**
     * @dataProvider provideInvalidFormData
     *
     * @param array<string, string> $formData
     */
    public function testThatRegistrationShouldFailed(array $formData): void
    {
        $this->get('/auth/register');

        $this->client->submitForm('S\'inscrire', $formData);

        self::assertResponseIsUnprocessable();
    }

    /**
     * Fournit des données de formulaire invalides pour les tests de validation.
     *
     * @return iterable<string, array{0: array<string, string>}>
     */
    public static function provideInvalidFormData(): iterable
    {
        yield 'empty username' => [self::getFormData(['register[username]' => ''])];
        yield 'non unique username' => [self::getFormData(['register[username]' => 'user+1'])];
        yield 'too long username' => [self::getFormData(['register[username]' => 'Lorem ipsum dolor sit amet orci aliquam'])];
        yield 'empty email' => [self::getFormData(['register[email]' => ''])];
        yield 'non unique email' => [self::getFormData(['register[email]' => 'user+1@email.com'])];
        yield 'invalid email' => [self::getFormData(['register[email]' => 'fail'])];
    }

    /**
     * Fournit les données de formulaire par défaut pour l'inscription, avec possibilité de les surcharger.
     *
     * @param array<string, string> $overrideData
     *
     * @return array<string, string>
     */
    public static function getFormData(array $overrideData = []): array
    {
        return [
            'register[username]' => 'username',
            'register[email]' => 'user@email.com',
            'register[plainPassword]' => 'SuperPassword123!',
        ] + $overrideData;
    }

    // Méthode pour supprimer le User de test après l'exécution du test
    private function removeTestUser(): void
    {
        $entityManager = $this->getEntityManager();
        $user = $entityManager->getRepository(User::class)->findOneBy([
            'email' => 'user@email.com',
        ]);

        if (null !== $user) {
            $entityManager->remove($user);
            $entityManager->flush();
        }
    }
}
