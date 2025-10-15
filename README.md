# ILC_BACK — Backend (Laravel)

Backend de l’application ILC. Ce dépôt contient l’API et la logique métier.

## ⚙️ Installation

> Prérequis : PHP 8.x, Composer, une base de données (MySQL/PostgreSQL), et (optionnel) Node.js si vous utilisez des assets.

#### 1) Cloner le dépôt

```
git clone https://github.com/Akinaru/ILC_BACK.git
cd ILC_BACK
```

#### 2) Installer les dépendances PHP

```
composer install
```

#### 3) Initialiser l'environnement (voir la section Environnements)

```
cp .env.example .env
php artisan key:generate
```

#### 4) Configurer la base de données dans .env

```
DB_CONNECTION=mysql
DB_HOST=IP_HOST
DB_PORT=3306
DB_DATABASE=DATABASE
DB_USERNAME=USERNAME
DB_PASSWORD=MOT_DE_PASSE
```

#### 5) Migrer (et éventuellement peupler) la base

```
php artisan migrate --seed
```

#### 6) Lancer le serveur de dev

```
php artisan serve
📚 Liste des routes
La liste des routes est disponible à la racine du projet (un fichier dédié y est présent).
```

Ouvrez le fichier à la racine pour consulter l’inventaire des endpoints exposés par l’API.

## 🌱 Système d’environnements

Le projet supporte plusieurs environnements : production, staging et local.

Fichiers fournis :

-   `.env` — fichier d’environnement courant (non versionné)

-   `.env.example` — modèle générique

-   `.env.production.example` — modèle pour la production

-   `.env.staging.example` — modèle pour la staging (pré-prod)

Mise en place rapide

```
# Local
cp .env.example .env

# Staging
cp .env.staging.example .env

# Production
cp .env.production.example .env
```

Clés importantes dans .env :

```
APP_NAME="ILC"
APP_ENV=local            # local | staging | production
APP_URL=http://localhost (ou adresse publique en fonction de l'environnement)
Config DB...
```

🏷️ Version applicative
Le backend utilise un fichier .version à la racine pour stocker la version de base (ex. 1.1.2).
La version effective exposée par l’API/les pages d’info est composée comme suit :

```
<contenu_de_.version>-<APP_ENV>
exemples :
1.1.2-local
1.1.2-staging
1.1.2-production
```

Pour mettre à jour la version, modifiez simplement le contenu de .version.

L’environnement est déterminé par APP_ENV dans votre .env (`production`, `staging`, `local`).
