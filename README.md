### Le problème

Le clone du projet récupéré depuis le dépôt Github de OpenclassRooms est destiné à une version 6.4 de Symfony.
Je dispose d'une version plus récente , la 7.4, et je suis sur PHP 8.2.35, il y a donc des adaptations à prévoir notamment au niveau de l'installation des composants via composer.

Le fichier composer.lock présent dans le dépôt Git a été généré avec une version de PHP antérieure à la mienne, ce qui coince avec des paquets comme laminas/laminas-code et phpspec/prophecy.

Puisque composer install lit le fichier composer.lock figé et refuse d'avancer, il faut forcer Composer à mettre à jour les versions pour qu'elles s'adaptent à PHP 8.2.35.

De plus ce clone est configuré pour une installation avec PostgreSQL , mais dans mon cas je préfère utiliser MySQL.

### La solution

1. Désactivez le blocage de sécurité pour éviter les erreurs d'audit :

**composer config audit.block-insecure false**

2. Lancez la mise à jour des dépendances en excluant la référence à PostGreSQL:

**composer update --ignore-platform-req=ext-pgsql**

=> Composer va réécrire le fichier composer.lock avec des versions compatibles PHP 8.2 et installer tout le dossier vendor/ sans encombre.

3. Configurez le fichier .env ou .env.local pour qu'il pointe vers la BD mysql

Sur mon poste, MySQL est lancé sur le port 3307.

DATABASE_URL="mysql://username!:password!@127.0.0.1:3307/criti-pixel?serverVersion=8.0.32&charset=utf8mb4"

4. Videz le dossier des migrations

5. Générez une nouvelle migration propre adaptée à la configuration active :

**symfony console make:migration**

6. Créez la base de données configurée dans .env ou .env.local (selon le cas)
   **symfony console doctrine:database:create**

7. Une fois la base de données créée, on peut lancer la commande de migration :

**symfony console doctrine:migrations:migrate**

8. A ce stade la base de données est créée, on peut lancer le chargement des fixtures :

**php bin/console doctrine:fixtures:load -n**

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
