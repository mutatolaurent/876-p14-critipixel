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

    // Tests de tris
    #[DataProvider('provideSortingScenarios')]
    public function testShouldSort(string $sort, string $direction, string $expectedFirstGameTitle): void
    {

        // 1. Charger la page d'accueil et vérifier qu'elle est accessible
        $crawler = $this->client->request('GET', '/', ['limit' => 100, 'sorting' => $sort, 'direction' => $direction]);    // On charge plus de jeux pour s'assurer que le filtrage fonctionne correctement
        self::assertResponseIsSuccessful();

        // 2. Vérifier que le nombre de jeux affichés est correct
        self::assertSelectorCount(50, 'article.game-card');

        // 3. Vérifier que le premier jeu est bien celui qui a le titre le plus élevé
        $firstGameTitle = $crawler->filter('h5.game-card-title')->first()->text();
        // dump($firstGameTitle);
        self::assertSame($expectedFirstGameTitle, $firstGameTitle);
    }

    // Test de filtrage avec un seul tag
    public function testShouldFilterVideoGamesByOneTag(): void
    {
        // 1. Charger la page et récupérer le Crawler
        $crawler = $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');

        // 2. Extraire le formulaire via son bouton de soumission
        $form = $crawler->selectButton('Filtrer')->form();

        // 3. Désactiver la validation interne du DomCrawler pour autoriser le tableau de checkboxes
        $form->disableValidation();

        // 4. Soumettre le formulaire en injectant directement le tableau de tags souhaité
        $this->client->submit($form, [
            'filter' => [
            'tags' => ['1'], // On peut aussi tester plusieurs tags : ['2', '3']
            ],
        ]);

        // 5. Vérifier le résultat
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
    }

    // Test de filtrage avec plusieurs tags
    #[DataProvider('provideTagFilteringScenarios')]
    public function testShouldFilterVideoGamesByTags(array $tags, int $expectedGameCount): void
    {
        // 1. Charger la page d'accueil et vérifier qu'elle est accessible
        $crawler = $this->client->request('GET', '/', ['limit' => 100]);    // On charge plus de jeux pour s'assurer que le filtrage fonctionne correctement
        self::assertResponseIsSuccessful();

        // 2. Récupérer et configurer le formulaire
        $form = $crawler->selectButton('Filtrer')->form();
        $form->disableValidation(); // On Désactive la validation interne du DomCrawler pour autoriser le tableau de checkboxes

        // 3. Soumettre le formulaire avec le jeu de tags du scénario courant
        $this->client->submit($form, [
            'filter' => [
                'tags' => $tags,
            ],
        ]);

        // 4. Vérifications
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
                'expectedGameCount' => 13,
            ],
            'Filtrer par le tag 2 uniquement' => [
                'tags' => ['2'],
                'expectedGameCount' => 9,
            ],
            'Filtrer par les tags 1 et 2 cumulés' => [
                'tags' => ['1', '2'],
                'expectedGameCount' => 3,
            ],
            'Aucun tag sélectionné (tous les jeux)' => [
                'tags' => [],
                'expectedGameCount' => 50,
            ],
            'Tag inexistant' => [
                'tags' => ['999'],
                'expectedGameCount' => 50,
            ],
            'Aucun résultat trouvé' => [
                'tags' => ['1','2','3','4','5'],
                'expectedGameCount' => 0,
            ],
        ];
    }

    /**
     * Fournit différents jeux de données pour tester les critères de tri
     * Chaque ligne représente un scénario : [ 'critère_de_tri', 'direction', 'titre_du_premier_jeu_attendu' ]
     */
    public static function provideSortingScenarios(): array
    {
        return [
            'Trier par titre (ascendant)' => [
                'sort' => 'Title',
                'direction' => 'Ascending',
                'expectedFirstGameTitle' => 'Jeu vidéo 0',
            ],
            'Trier par titre (descendant)' => [
                'sort' => 'Title',
                'direction' => 'Descending',
                'expectedFirstGameTitle' => 'Jeu vidéo 9',
            ],
            'Trier par date de sortie (plus récent)' => [
                'sort' => 'ReleaseDate',
                'direction' => 'desc',
                'expectedFirstGameTitle' => 'Jeu vidéo 0',
            ],
            'Trier par note (la meilleure)' => [
                'sort' => 'Rating',
                'direction' => 'desc',
                'expectedFirstGameTitle' => 'Jeu vidéo 24',
            ],
            'Trier par note moyenne (la meilleure)' => [
                'sort' => 'AverageRating',
                'direction' => 'desc',
                'expectedFirstGameTitle' => 'Jeu vidéo 8',
            ],

        ];
    }

}
