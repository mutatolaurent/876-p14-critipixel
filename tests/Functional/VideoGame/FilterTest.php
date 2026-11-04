<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Model\Entity\Tag;
use App\Model\Entity\VideoGame;
use App\Tests\Functional\FunctionalTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class FilterTest extends FunctionalTestCase
{
    public function testShouldListTenVideoGames(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card'); // On doit avoir 10 cartes de jeux vidéo
        $this->client->clickLink('2');  // On clique sur le lien de la page 2
        self::assertResponseIsSuccessful();
    }

    public function testShouldFilterVideoGamesBySearch(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->submitForm('Filtrer', ['filter[search]' => 'Jeu vidéo 49'], 'GET');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(1, 'article.game-card');
    }

    public function testShouldFilterVideoGamesByOneTag(): void
    {
        // 1. Charger la page et récupérer le Crawler
        $crawler = $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');

        // 2. Extraire le formulaire via son bouton de soumission
        $form = $crawler->selectButton('Filtrer')->form();

        // 2. Désactiver la validation interne du DomCrawler pour autoriser le tableau de checkboxes
        $form->disableValidation();

        // 2. Soumettre le formulaire en injectant directement le tableau de tags souhaité
        $this->client->submit($form, [
            'filter' => [
            'tags' => ['1'], // On peut aussi tester plusieurs tags : ['2', '3']
            ],
        ]);

        // 5. Vérifier le résultat
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(0, 'article.game-card');
    }

    #[DataProvider('provideTagFilteringScenarios')]
    public function testShouldFilterVideoGamesByTags(array $tags, int $expectedGameCount): void
    {
        $crawler = $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();

        // 1. Récupérer et configurer le formulaire
        $form = $crawler->selectButton('Filtrer')->form();
        $form->disableValidation();

        // 2. Soumettre le formulaire avec le jeu de tags du scénario courant
        $this->client->submit($form, [
            'filter' => [
                'tags' => $tags,
            ],
        ]);

        // 3. Vérifications
        self::assertResponseIsSuccessful();
        self::assertSelectorCount($expectedGameCount, 'article.game-card');
    }

    /**
     * Fournit différents jeux de données pour tester les filtres de tags.
     * Chaque ligne représente un scénario : [ ['tags_ids'], nombre_de_jeux_attendus ]
     */
    public static function provideTagFilteringScenarios(): array
    {
        return [
            'Filtrer par le tag 1 uniquement' => [
                'tags' => ['1'],
                'expectedGameCount' => 10, // Remplacez par le nombre réel attendu
            ],
            'Filtrer par le tag 2 uniquement' => [
                'tags' => ['2'],
                'expectedGameCount' => 9,  // Le cas qui posait problème
            ],
            'Filtrer par les tags 1 et 2 cumulés' => [
                'tags' => ['1', '2'],
                'expectedGameCount' => 3,  // Exemple de cumul
            ],
            'Aucun tag sélectionné (tous les jeux)' => [
                'tags' => [],
                'expectedGameCount' => 10,
            ],
        ];
    }
}
