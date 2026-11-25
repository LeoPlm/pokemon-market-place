# Base de données : du MCD aux migrations Laravel

Ce document explique comment le MCD Looping (`MCD - projet marketplace pokemon.loo`, sur le Drive) a été traduit en tables MySQL, et pourquoi. Il sert de support pour défendre le schéma en soutenance.

- Migrations : [database/migrations/](../database/migrations/)
- Modèles Eloquent : [app/Models/](../app/Models/)
- Valeurs autorisées (statuts, états…) : [app/Enums/](../app/Enums/)
- Tests du schéma : [tests/Feature/Database/SchemaTest.php](../tests/Feature/Database/SchemaTest.php)

---

## 1. L'idée centrale du modèle

**On sépare la carte de référence (`Carte`) de l'annonce (`Annonce`).**

- `Carte` est le catalogue standardisé : « Dracaufeu, Base Set, n° 4, Rare Holo ». Il existe une seule ligne par carte, importée depuis l'API Pokémon TCG.
- `Annonce` est un exemplaire physique mis en vente par un particulier, avec son état, son édition, sa langue, ses photos et son prix.

C'est ce qui résout le problème métier : sur Vinted ou Leboncoin, chaque vendeur décrit sa carte à sa façon. Ici, toute annonce pointe vers une fiche de référence commune. On peut donc filtrer par extension et par rareté, et comparer les prix d'une même carte.

---

## 2. Règles de passage MCD → MLD appliquées

| Cas dans le MCD | Traduction | Exemple |
|---|---|---|
| Association avec une cardinalité **1,1** d'un côté | Clé étrangère **NOT NULL** du côté 1,1 | Annonce 1,1 — *Publier* → `listings.seller_id` |
| Association avec une cardinalité **0,1** d'un côté | Clé étrangère **nullable** du côté 0,1 | Annonce 0,1 — *DONNER LIEU* → `listings.order_id` (nul tant que l'annonce n'est pas vendue) |
| Côté 0,1 / 1,1 vers une entité qui n'en a qu'**une au plus** | Clé étrangère + **UNIQUE** | Commande 0,1 — *NOTER* — Avis 1,1 → `reviews.order_id` unique |
| Association **réflexive** | Clé étrangère vers la même table | Commentaire *Répondre* → `comments.parent_id` |
| Contrainte **XT** (exclusion + totalité) | Contrainte `CHECK` | Signalement vise une annonce **ou** un commentaire |
| Association **n,n** | Table de liaison | Aucune dans ce MCD : pas de table pivot |

Il n'y a pas de table `order_items`. Dans le MCD, une annonce est un exemplaire unique, donc elle ne peut être achetée qu'une fois (Annonce 0,1). La commande est alors portée par l'annonce (`listings.order_id`). Le prix payé reste fiable, car une annonce vendue n'est plus modifiable (RG-10) et le montant total est de toute façon figé dans la commande.

---

## 3. Correspondance entités → tables

Les noms du MCD sont en français. Les tables et colonnes sont en anglais, pour suivre les conventions de Laravel (`email`, `password`, `created_at`… sont attendus par le framework). Chaque migration indique en commentaire l'attribut MCD de chaque colonne.

Toutes les tables ont `created_at` et `updated_at`. Quand le MCD a une date de création (date d'inscription, date d'ajout, date d'envoi…), c'est `created_at` qui la porte.

### Extension → `sets`

| MCD | Colonne | Type |
|---|---|---|
| id_extension | id | bigint, PK |
| nom | name | varchar |
| date_sortie | released_at | date, nullable |
| logo | logo | varchar, nullable |
| slug | slug | varchar, **unique** |

### Carte → `cards`

| MCD | Colonne | Type |
|---|---|---|
| id_carte | id | PK |
| *contenir* | set_id | FK → sets |
| nom | name | varchar, indexé |
| numero | number | varchar(10) (unique avec set_id) |
| rarete | rarity | varchar, indexé |
| image | image | varchar, nullable |
| id_externe | external_id | varchar, **unique** (id de l'API, ex. `base1-4`) |

### Utilisateur → `users`

| MCD | Colonne | Type |
|---|---|---|
| id_utilisateur | id | PK |
| pseudo | username | varchar(20), **unique** (RG-01) |
| prenom / nom | first_name / last_name | varchar, nullable |
| adresse_mail | email | varchar, **unique** |
| mot_de_passe | password | varchar (haché en bcrypt) |
| adresse, code_postal, ville | address, postal_code, city | varchar, nullable |
| pays | country | char(2), code ISO (FR, BE…) |
| date_inscription | created_at | timestamp |
| role | role | varchar → `UserRole` (user, moderator, admin) |
| date_verif_email | email_verified_at | timestamp, nullable |
| suspension | suspended_at | timestamp, nullable (nul = compte actif) |
| id_stripe | stripe_account_id | varchar, unique, nullable |
| compte_active | stripe_account_active | boolean (RG-02) |

L'identité et l'adresse sont nullables. À l'inscription, on ne demande que le pseudo, l'email et le mot de passe. L'adresse devient nécessaire au moment d'acheter.

### Annonce → `listings`

| MCD | Colonne | Type |
|---|---|---|
| id_annonce | id | PK |
| *Publier* | seller_id | FK → users |
| *Vendre* | card_id | FK → cards |
| *DONNER LIEU* | order_id | FK → orders, nullable |
| description | description | text, nullable |
| date | created_at | timestamp |
| prix | price | **entier, en centimes** |
| etat | card_condition | varchar → `CardCondition`, nul si gradée |
| edition | edition | varchar → `Edition` |
| langue | language | varchar → `Language` |
| gradee | is_graded | boolean |
| organisme_grading | grading_company | varchar → `GradingCompany`, nullable |
| note_si_gradee | grade | decimal(3,1), nullable |
| mode_envoi | shipping_method | varchar → `ShippingMethod` |
| statut | status | varchar → `ListingStatus` |

La colonne s'appelle `card_condition` et non `condition`, car `CONDITION` est un mot réservé de MySQL.

### Photo → `listing_photos`

| MCD | Colonne | Type |
|---|---|---|
| id_photo | id | PK |
| *Illustrer* | listing_id | FK → listings |
| chemin | path | varchar |
| position | position | tinyint, 1 à 8 (unique avec listing_id) |
| date_ajout | created_at | timestamp |

### Commande → `orders`

| MCD | Colonne | Type |
|---|---|---|
| id_commande | id | PK |
| *PASSER* | buyer_id | FK → users |
| prix | subtotal | entier (centimes) |
| frais_port | shipping_fee | entier (centimes) |
| frais_protection | protection_fee | entier (centimes) |
| total | total | entier (centimes) |
| statut | status | varchar → `OrderStatus` |
| numero_suivi | tracking_number | varchar, nullable |
| id_paiement_stripe | stripe_payment_intent_id | varchar, unique |
| date_paiement | paid_at | timestamp, **non nul** (la commande naît payée, RG-20) |
| date_expedition | shipped_at | timestamp, nullable |
| date_reception | received_at | timestamp, nullable |
| prenom, nom, adresse, ville, code_postal, pays | shipping_first_name, shipping_last_name, shipping_address, shipping_city, shipping_postal_code, shipping_country | adresse **recopiée** |

### Avis → `reviews`

| MCD | Colonne | Type |
|---|---|---|
| id_avis | id | PK |
| *NOTER* | order_id | FK → orders, **unique** |
| Note | rating | tinyint, 1 à 5 |
| commentaire | comment | varchar(500), nullable |
| reponse_vendeur | seller_reply | varchar(500), nullable |
| date_reponse_vendeur | seller_replied_at | timestamp, nullable |
| date_avis | created_at | timestamp |

### Commentaire → `comments`

| MCD | Colonne | Type |
|---|---|---|
| id_commentaire | id | PK |
| *Recevoir* | listing_id | FK → listings |
| *Ecrire* | user_id | FK → users |
| *Répondre* | parent_id | FK → comments, nullable |
| contenu | content | varchar(500) |
| masque | is_hidden | boolean |
| motif_masquage | hidden_reason | varchar, nullable |
| date | created_at | timestamp |

### Conversation → `conversations`

| MCD | Colonne | Type |
|---|---|---|
| id_conversation | id | PK |
| *OUVRIR* | listing_id | FK → listings |
| *Initier* | buyer_id | FK → users |
| date_debut | created_at | timestamp |

Le vendeur n'est pas stocké : c'est celui de l'annonce (`listing.seller`). Le stocker en plus créerait une redondance qui pourrait se contredire.

### Message → `messages`

| MCD | Colonne | Type |
|---|---|---|
| id_message | id | PK |
| *AVOIR* | conversation_id | FK → conversations |
| *Envoyer* | sender_id | FK → users |
| contenu | content | varchar(2000) |
| date_envoi | created_at | timestamp |
| date_lecture | read_at | timestamp, nullable (nul = non lu) |

### Litige → `disputes`

| MCD | Colonne | Type |
|---|---|---|
| id_litige | id | PK |
| *DONNER* | order_id | FK → orders, **unique** |
| *Trancher* | moderator_id | FK → users, nullable |
| motif | reason | varchar → `DisputeReason` |
| description | description | text |
| statut | status | varchar → `DisputeStatus` |
| decision | decision | varchar → `DisputeDecision`, nullable |
| justification | justification | text, nullable |
| date_ouverture | created_at | timestamp |
| date_resolution | resolved_at | timestamp, nullable |

### Signalement → `reports`

| MCD | Colonne | Type |
|---|---|---|
| id_signalement | id | PK |
| *Rediger* | reporter_id | FK → users |
| *Viser* | listing_id | FK → listings, nullable |
| *Concerner* | comment_id | FK → comments, nullable |
| motif | reason | varchar |
| description | description | text, nullable |
| statut | status | varchar → `ReportStatus` |
| date_debut | created_at | timestamp |

---

## 4. Contraintes posées en base

La validation côté serveur reste la première ligne de défense. Les contraintes en base sont le **dernier filet de sécurité** : même un bug ou une requête directe ne peut pas enregistrer une donnée incohérente.

### Unicité

| Contrainte | Règle |
|---|---|
| `users.username`, `users.email` | RG-01 |
| `cards (set_id, number)`, `cards.external_id` | une carte n'existe qu'une fois dans le catalogue |
| `listing_photos (listing_id, position)` | pas deux photos à la même place |
| `reviews.order_id` | un avis par commande (RG-28) |
| `disputes.order_id` | un litige au plus par commande |
| `conversations (listing_id, buyer_id)` | une conversation par couple annonce / acheteur (RG-31) |
| `reports (reporter_id, listing_id)` et `(reporter_id, comment_id)` | un signalement par utilisateur et par élément (RG-13). Un index unique accepte plusieurs NULL, donc les deux contraintes cohabitent |

### CHECK (MySQL 8)

| Nom | Règle |
|---|---|
| `chk_reports_target` | contrainte XT du MCD : annonce **ou** commentaire, jamais les deux, jamais aucun |
| `chk_listings_price` | prix entre 100 et 1 000 000 centimes (RG-09) |
| `chk_listings_grading` | gradée ⇒ organisme + note, sans état ; non gradée ⇒ état, sans organisme ni note (RG-07) |
| `chk_listings_grade` | note de 1 à 10, par demi-points (RG-07) |
| `chk_listing_photos_position` | position de 1 à 8 |
| `chk_orders_total` | total = prix + port + frais de protection (RG-16) |
| `chk_reviews_rating` | note de 1 à 5 (RG-28) |
| `chk_disputes_resolution` | litige tranché ⇒ décision + modérateur + date (RG-26, décision tracée) |

Ces CHECK ne sont créées que sous MySQL. Les tests en local tournent sur SQLite en mémoire, qui ne sait pas ajouter une contrainte à une table existante. La CI, elle, lance les tests sur MySQL : les CHECK y sont vérifiées par [SchemaTest.php](../tests/Feature/Database/SchemaTest.php).

### Ce qui ne peut pas s'écrire en SQL (vérifié par le code)

- 2 à 8 photos par annonce (RG-08) : une contrainte ne peut pas compter des lignes d'une autre table.
- Une commande ne contient que des annonces d'un même vendeur (RG-18).
- On ne note qu'une commande terminée, dans les 14 jours (RG-28).
- Les transitions de statut autorisées (machine à états).

### Suppressions (ON DELETE)

- **CASCADE** pour ce qui n'a pas de sens sans son parent : photos d'une annonce, messages d'une conversation, réponses à un commentaire, avis et litige d'une commande.
- **RESTRICT** pour tout ce qui touche aux utilisateurs, au catalogue et aux commandes. Un utilisateur n'est jamais supprimé, il est **anonymisé** (RG-03), pour garder l'historique des commandes. Une carte du catalogue ne peut pas disparaître si des annonces la référencent.
- **RESTRICT** aussi pour les signalements : un élément signalé ne disparaît pas avant que le modérateur l'ait traité. MySQL interdit de toute façon une action CASCADE sur une colonne utilisée dans une contrainte CHECK.

---

## 5. Choix techniques à savoir justifier

- **Montants en centimes (entiers).** Un `float` ne représente pas exactement 0,10 € : 0,1 + 0,2 donne 0,30000000000000004. Avec des entiers, les calculs de frais sont exacts.
- **Montants figés dans la commande.** Les frais sont paramétrables et peuvent changer. Une commande garde le montant réellement payé : on le copie, on ne le recalcule jamais.
- **Adresse de livraison recopiée dans la commande.** Si l'acheteur déménage, ses anciennes commandes gardent l'adresse où elles ont été envoyées.
- **Statuts en `varchar` + Enums PHP, pas en `ENUM` MySQL.** Ajouter une valeur à un `ENUM` MySQL oblige à modifier la table. Avec une chaîne, la liste des valeurs vit dans le code (`app/Enums`), et Eloquent convertit automatiquement la valeur en objet PHP typé.
- **`suspended_at` plutôt qu'un booléen `suspension`.** Il contient la même information (nul = actif), avec en plus la date de la suspension.
- **`$fillable` limité dans les modèles.** Seuls les champs qu'un utilisateur a le droit de saisir sont remplissables en masse. `role`, `suspended_at`, `seller_id`, `status`, les montants… ne le sont pas. Ça bloque l'attaque par *mass assignment*, où quelqu'un ajouterait `role=admin` dans un formulaire.
- **Index.** MySQL indexe déjà les clés étrangères. On ajoute des index sur les colonnes filtrées par la recherche (US-02) : statut + prix, statut + date, état, édition, langue, gradée, rareté et nom de carte.

---

## 6. Ce qui viendra avec les prochaines tâches

Le schéma actuel est **exactement** le MCD. Certaines règles demanderont des colonnes ou des tables en plus. Il faudra les ajouter **dans le MCD Looping aussi**, pour que le schéma défendu en soutenance reste identique à la base.

| Besoin | Règle | Tâche Notion |
|---|---|---|
| Date de fin de réservation de l'annonce, et acheteur qui la réserve | RG-19 (réservation 30 min) | n° 21 panier + Checkout |
| Événements de webhook Stripe déjà traités | RG-20, idempotence | n° 22 webhooks |
| Ids Stripe du versement au vendeur et du remboursement | RG-24, RG-26, traçabilité | n° 23 séquestre |
| Historique daté des changements de statut | « chaque transition est tracée », US-06 | n° 23 séquestre |
| Photos jointes à un litige | US-08 | n° 33 litiges |
| Réclamations SAV rattachables à une commande | RG-27 | n° 33 SAV |
| Avatar et bio de l'utilisateur | US-27 | n° 14 profil |
| Notifications in-app | RG-12 | n° 34 notifications |

### Points d'attention

- La suppression de compte du starter kit (Paramètres) supprime réellement l'utilisateur. Avec les clés en RESTRICT, elle échouera dès que l'utilisateur aura une annonce ou une commande. Il faudra la remplacer par l'anonymisation de RG-03 (tâche n° 14).
- Un commentaire signalé ne peut pas être supprimé par son auteur tant que le signalement existe. Il faudra décider du comportement (masquer plutôt que supprimer, par exemple) lors de la tâche commentaires.
