# Projet 14 du programme de formation OpenclassRooms pour Symphony PHP

## Configuration de l'environnement de travail

### Le problème

Le clone du projet récupéré depuis le dépôt Github de OpenclassRooms est destiné à une version 6.4 de Symfony.
Je suis sur PHP 8.2.35, mais le fichier composer.lock présent dans le dépôt Git a été généré avec une version de PHP antérieure à la mienne, ce qui coince avec des paquets comme laminas/laminas-code et phpspec/prophecy.

Puisque composer install lit le fichier composer.lock figé et refuse d'avancer, il faut forcer Composer à mettre à jour les versions pour qu'elles s'adaptent à PHP 8.2.35.

De plus ce clone est configuré pour une installation avec PostgreSQL , mais dans mon cas je préfère utiliser MySQL.

### La solution

1. Désactivez le blocage de sécurité pour éviter les erreurs d'audit :

`composer config audit.block-insecure false`

2. Lancez la mise à jour des dépendances en excluant la référence à PostGreSQL:

`composer update --ignore-platform-req=ext-pgsql`

=> Composer va réécrire le fichier composer.lock avec des versions compatibles PHP 8.2 et installer tout le dossier vendor/ sans encombre.

3. Configurez le fichier .env ou .env.local pour qu'il pointe vers la BD mysql

Sur mon poste, MySQL est lancé sur le port 3307.

`DATABASE_URL="mysql://username!:password!@127.0.0.1:3307/criti-pixel?serverVersion=8.0.32&charset=utf8mb4"`

4. Videz le dossier des migrations

5. Générez une nouvelle migration propre adaptée à la configuration active :

`symfony console make:migration`

6. Créez la base de données configurée dans .env ou .env.local (selon le cas)
   `symfony console doctrine:database:create`

7. Une fois la base de données créée, on peut lancer la commande de migration :

`symfony console doctrine:migrations:migrate`

8. A ce stade la base de données est créée, on peut lancer le chargement des fixtures :

`php bin/console doctrine:fixtures:load -n`

9. Mise à jour de la version de PHPUnit
   La version de PHPUnit du projet Cloné de OpenclassRooms est ancienne , j'ai fait une Upgrade en V11.5
   `composer require --dev phpunit/phpunit:"^11.5" --with-all-dependencies --ignore-platform-req=ext-pgsql`

## Exo 1 : Implémenter des tests avec PHPUnit

### Tests unitaires

Les tests unitaires sont dans le dossier tests/Unit/rating
on peut y trouver 2 classes, avec leurs méthodes de tests :

- CalculateAverageTest.php
- CountRatingsPerValueTest.php

### Test fonctionnels

Les tests fonctionnels pour la partie authentification sont dans le dossier tests/Unit/Auth :

- LoginTest.php
- RegisterTest.php

Les tests fonctionnels pour la partie avis sont dans le dossier tests/Unit/Review :

- AddReviewTest.php

Les tests fonctionnels pour la partie jeu video sont dans le dossier tests/Unit/VideoGame :

- FilterTest.php
- ShowTest.php

### Lancement des tests

Pour lancer tous les tests :
`vendor/bin/phpunit`

Pour lancer les tests d'une méthode en particulier :
`vendor/bin/phpunit --filter=testSomething`

## Exo 2 : Déployer votre site

### Identifiez les outils d'analyse statique adaptés à votre projet.

J’ai choisi :

- **PHPStan** : outil d'analyse statique : il détecte les erreurs de types, les bugs logiques, les variables non définies et les failles de conception.
- **PHP CS Fixer** : outil standard de l'industrie pour formater et contrôler le respect des normes PSR et du style Symfony

#### PHPStan

Commandes d'installation :

`composer require --dev phpstan/phpstan phpstan/phpstan-symfony`

`composer require --dev phpstan/phpstan-doctrine`

Configuration dans phpstan.dist.neon qui est situé à la racine du projet :

```
includes:
    - vendor/phpstan/phpstan-symfony/extension.neon
    - vendor/phpstan/phpstan-doctrine/extension.neon
    - vendor/phpstan/phpstan-doctrine/rules.neon
    parameters:
    level: 6
    paths:
        - config/
        - public/
        - src/
        - tests/
    symfony:
        containerXmlPath: var/cache/dev/App_KernelDevDebugContainer.xml
```

C’est la ligne : level: 6 qui fixe le niveau de contrôle , ici il est fixé à 6 mais il peut aller jusqu’à 9.

Lancer une vérification PHPStan :
`vendor/bin/phpstan analyse`

#### PHP CS Fixer

Commande d'installation :
`composer require --dev friendsofphp/php-cs-fixer`

Configuration dans .php-cs-fixer.dist.php qui est situé à la racine du projet :

```
<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = (new Finder())
->in(**DIR**)
->exclude('var')
->exclude('vendor')
->notPath('public/index.php'); // Optionnel : pour préserver des fichiers spécifiques si besoin

return (new Config())
->setRiskyAllowed(true) //autorise les règles risquées globalement
->setRules([
'@Symfony' => true,
'@Symfony:risky' => true,
// Vous pouvez ajouter d'autres règles spécifiques si besoin
])
->setFinder($finder);
```

Lancer une vérification PHP CS Fixer sur tout le projet, sans modifier les fichiers (idéal pour la CI) :
`vendor/bin/php-cs-fixer fix --dry-run --diff`

Lancer une vérification PHP CS Fixer sur tout le projet, et appliquer les corrections :
`vendor/bin/php-cs-fixer fix`
Note : La règle d'or avant de lancer une commande de formatage global est de s'assurer que le dépôt Git est propre (pas de modifications en cours non commitées).

Lancer une vérification PHP CS Fixer sur un seul ficheir, sans appliquer les corrections :
`vendor/bin/php-cs-fixer fix src/Twig/Components/Alert.php --dry-run --diff --allow-risky=yes`

### Mettez en place l'intégration continue

Pour l'intégration continue j'ai choisi d'utiliser la technique des workflow Github Actions de Github.
L’intégration continue se configure grâce à des fichiers .yml ou .yaml à placer dans le dossier .github/.workflows
Dans le cadre de ce projet j'ai créé un fichier nommé ci.yml

La CI se déclenche à chaque commit sur la branche main
Elle commence par créé un job dédié à la CI : php-tests
Ce job enchaîne les actions suivantes :

- Indique à GitHub Actions de louer et d'utiliser un ordinateur virtuel (runner) sous la dernière version stable d'Ubuntu pour exécuter ce job.
- Lance un conteneur Docker MySQL en arrière-plan sur la machine virtuelle pour les besoins de tes tests fonctionnels.
- Indique à GA de tester en PHP 8.2
- Récupére le code du projet.
- Installe PHP et ses extensions
- Installe les dépendances du projet via composer
- Prépare l'environnement de tests :
  - Vide et régénère le cache de test.
  - Compile les fichiers SASS/CSS requis par les templates Twig.
  - Crée la base de données de test (criti-pixel_test).
  - Exécute les migrations Doctrine.
  - Charge les fixtures pour injecter les jeux de données initiaux.
- Contrôle de la qualité du code (PHP CS Fixer)
- Contrôle de la qualité du code (PHP Stan)
- Exécute les tests unitaires et fonctionnels

# README initial : celui du clone d'OPenclassRooms

<img src="assets/images/logo.png" alt="CritiPixel" width="200" />
# CritiPixel

## Pré-requis

- PHP >= 8.2
- Composer
- Extension PHP Xdebug
- Symfony (binaire)

## Installation

### Composer

Dans un premier temps, installer les dépendances :

```bash
composer install
```

### Docker (optionnel)

Si vous souhaitez utiliser Docker Compose, il vous suffit de lancer la commande suivante :

```bash
docker compose up -d
```

## Configuration

### Base de données

Actuellement, le fichier `.env` est configuré pour la base de données PostgreSQL mise en place dans `docker-compose.yml`.
Cependant, vous pouvez créer un fichier `.env.local` si nécessaire pour configurer l'accès à la base de données.
Exemple :

```dotenv
DATABASE_URL=mysql://root:Password123!@host:3306/criti-pixel
```

### PHP (optionnel)

Vous pouvez surcharger la configuration PHP en créant un fichier `php.local.ini`.

De même pour la version de PHP que vous pouvez spécifier dans un fichier `.php-version`.

## Usage

### Base de données

#### Supprimer la base de données

```bash
symfony console doctrine:database:drop --force --if-exists
```

#### Créer la base de données

```bash
symfony console doctrine:database:create
```

#### Exécuter les migrations

```bash
symfony console doctrine:migrations:migrate -n
```

#### Charger les fixtures

```bash
symfony console doctrine:fixtures:load -n --purge-with-truncate
```

_Note : Vous pouvez exécuter ces commandes avec l'option `--env=test` pour les exécuter dans l'environnement de test._

### SASS

#### Compiler les fichiers SASS

```bash
symfony console sass:build
```

_Note : le fichier `.symfony.local.yaml` est configuré pour surveiller les fichiers SASS et les compiler automatiquement quand vous lancez le serveur web de Symfony._

### Tests

```bash
symfony php bin/phpunit
```

_Note : Penser à charger les fixtures avant chaque éxécution des tests._

### Serveur web

```bash
symfony serve
```
