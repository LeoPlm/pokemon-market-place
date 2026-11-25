# AGENT.md — Marketplace de cartes Pokémon (projet annuel)

> Contexte et règles de travail pour tout agent IA qui intervient sur ce dépôt.
> À lire **avant** toute modification. Synthèse du sujet, du cahier des charges métier, du cahier des charges fonctionnel, des parcours utilisateurs, du MCD (Looping), des maquettes Figma, du Trello et du Notion (état au 19/11/2026).

---

## 1. Le projet en bref

- **Quoi** : place de marché **peer-to-peer** d'achat/vente de cartes Pokémon d'occasion entre particuliers (pas d'enseigne vendeuse : ce sont les utilisateurs qui vendent).
- **Pourquoi** : sur Vinted, Leboncoin ou les groupes Facebook, il n'y a pas de fiche produit standardisée (état, édition, langue), pas de filtres propres aux cartes, et pas de confiance entre inconnus (carte non reçue ou non conforme).
- **Réponse** : un **catalogue de référence standardisé** + des annonces normalisées + un **paiement en séquestre** (modèle Vinted) + de la réputation (notes, avis) + de la modération.
- **Périmètre catalogue** : cartes de **1ʳᵉ génération uniquement** : Base Set, Jungle, Fossil, Base Set 2, Team Rocket, Gym Heroes, Gym Challenge.
- **Cadre** : projet annuel de 3ᵉ année Ingénierie du Web. Équipe de deux : **Léo** et **José**. Soutenance **fin décembre 2026** (préparation prête le 18/12).

### Sources de vérité (ordre de priorité en cas de contradiction)

1. **Cahier des charges fonctionnel** (Drive, 12/11/2026) : règles de gestion RG-01 à RG-31, user stories US-01 à US-27, cycles de vie, écrans, périmètre MVP. **C'est la référence.**
2. **MCD Looping** (`MCD - projet marketplace pokemon.loo`, Drive) : modèle de données.
3. **Maquettes Figma** : https://www.figma.com/design/598F85KWGrhygfKUotjdpl (page « Maquettes — piste C »).
4. **Notion** « Projet annuel — Reste à faire (Léo & José) » : ordre des tâches, responsables, jalons.
5. **Trello** : https://trello.com/b/IbGDQvPr/projet-annuel-marketplace-de-cartes-pokemon (backlog, critères d'acceptation). Certaines cartes sont **antérieures** au cahier des charges fonctionnel : voir §11.
6. Cahier des charges métier (2 pages) et sujet du projet : le cadre général.

---

## 2. Barème du sujet (ce que le jury vérifie)

Chaque décision technique doit servir un de ces critères.

| Partie | Pts | Exigence | Conséquence pour le code |
|---|---|---|---|
| Métier | 3 | Doc 2 pages (contexte, problème, solution, acteurs) | Fait |
| Gestion de projet | 1 | Agile (Kanban) + outil de suivi + Git avec workflow de branches | Gitflow strict, une branche par tâche |
| Conception | 1 | Wireframes + mockups Figma, parcours utilisateur | Fait |
| Modélisation | 1 | Schéma SQL **à défendre** en soutenance | Migrations fidèles au MCD, choix justifiés |
| Intégration Web | 2 | **React, Vue, Angular interdits.** Templates (Blade) + Livewire autorisés. Responsive, communique avec le back. **Qualité d'intégration de la maquette jugée** | Respect pixel-près des maquettes Figma |
| Intégration Métier | 2 | Logique métier côté serveur, API PHP. **Pas de GraphQL ni de WebSocket** sans validation | Règles métier côté serveur, API REST |
| Conteneurisation | 2 | Un `Dockerfile` par service + `compose.yml` dev **et** prod, tout conteneurisé en prod | Dockerfiles maison (pas Sail) |
| Déploiement | 2 | Nom de domaine public + SSL, en ligne la semaine avant **et** après la soutenance | VPS + Caddy |
| Sécurité | 1 | CSRF, injection SQL, XSS, **aucun secret exposé** | Voir §8 |
| Bonus | 5 | Auth avancée, clusterisation, observabilité, SEO, automatisation (1 pt chacun) | Seulement après le gel du 11/12 |

---

## 3. Stack technique

| Couche | Choix |
|---|---|
| Back-end | **Laravel** (PHP), Eloquent ORM |
| Front-end | **Blade** (rendu serveur) + **Tailwind CSS** + **Livewire** (filtres, formulaires, messagerie) + **Alpine.js** (petites interactions) |
| Base de données | **MySQL 8** (décidé le 19/11/2026), jeu de caractères `utf8mb4` / `utf8mb4_unicode_ci`, moteur InnoDB |
| Paiement | **Stripe Connect en mode test** (Checkout + séquestre + versements aux vendeurs) |
| Catalogue | Import depuis l'**API publique Pokémon TCG** (commande artisan relançable + seeder de secours) |
| Messagerie | **Polling Livewire** (`wire:poll`, ~5 s). Pas de WebSocket |
| Emails | Mailpit en dev, SMTP en prod. Envoi via la queue |
| Auth | Starter kit **Livewire** de Laravel (inscription, connexion, reset, vérif email). Sanctum pour l'API |
| Qualité | **Laravel Pint** (format), **Larastan** (analyse statique), **Pest** (tests), **GitHub Actions** (CI) |
| Infra | Docker + compose (dev & prod), reverse proxy **Caddy** (SSL automatique), VPS + nom de domaine |

Décisions encore ouvertes (carte Trello « Trancher les décisions techniques ») : stockage des photos (volume Docker local ou S3-compatible), back-office fait main ou avec Filament, choix du VPS et du registrar. **Ne pas trancher seul : signaler et demander.**

Services Docker prévus en dev : `app` (PHP-FPM, extension `pdo_mysql`), `web` (Nginx), `db` (image officielle `mysql:8`, données dans un volume nommé), `node` (Vite), `mailpit`, `queue` (worker Laravel). Il faudra aussi le **scheduler** (tâches planifiées du séquestre).

---

## 4. Acteurs et permissions

Un même utilisateur inscrit peut être acheteur **et** vendeur. Rôles stockés : `user`, `moderator`, `admin`.

| Action | Visiteur | Acheteur | Vendeur | Modérateur | Admin |
|---|---|---|---|---|---|
| Consulter annonces, catalogue, profils | ✅ | ✅ | ✅ | ✅ | ✅ |
| Rechercher / filtrer | ✅ | ✅ | ✅ | ✅ | ✅ |
| Commenter une annonce | ❌ | ✅ | ✅ | ✅ | ✅ |
| Contacter un vendeur (messagerie) | ❌ | ✅ | — | ❌ | ❌ |
| Acheter | ❌ | ✅ (email vérifié) | pas ses propres annonces | ❌ | ❌ |
| Publier une annonce | ❌ | — | ✅ (compte Stripe actif) | ❌ | ❌ |
| Noter un vendeur | ❌ | ✅ (commande terminée) | — | ❌ | ❌ |
| Signaler un contenu | ❌ | ✅ | ✅ | — | — |
| Masquer annonce / commentaire | ❌ | ❌ | ❌ | ✅ | ✅ |
| Trancher un litige | ❌ | ❌ | ❌ | ✅ | ✅ |
| Suspendre un utilisateur | ❌ | ❌ | ❌ | ✅ | ✅ |
| Changer le rôle d'un utilisateur | ❌ | ❌ | ❌ | ❌ | ✅ |

**Utilisateur suspendu** : garde l'accès à ses commandes en cours, mais ne peut plus publier, acheter, commenter ni écrire de message.

Le **prestataire de paiement** (Stripe) est un acteur technique : il encaisse, conserve et reverse les fonds.

Toutes ces règles passent par des **Policies / Gates Laravel** et sont **testées**.

---

## 5. Règles de gestion (RG) : à respecter dans le code

Les valeurs marquées *(paramétrable)* vont en **configuration** (ex. `config/marketplace.php` alimenté par le `.env`), **jamais en dur** dans le code.

### Comptes
- **RG-01** : pseudo unique, 3 à 20 caractères. **Email vérifié** obligatoire pour acheter, vendre, commenter ou écrire un message.
- **RG-02** : pour publier, le compte vendeur Stripe doit être activé.
- **RG-03** : suppression de compte impossible tant qu'une commande est en cours ; ensuite, le compte est **anonymisé** (l'historique des commandes est conservé).

### Annonces
- **RG-04** : 1 annonce = **1 exemplaire physique**. Champs obligatoires : carte de référence du catalogue, état, édition, langue, prix, mode d'envoi, photos.
- **RG-05** : état ∈ {comme neuf, très bon état, bon état, endommagé}. Édition ∈ {1ʳᵉ édition, Unlimited}.
- **RG-06** : langue ∈ {français, anglais, allemand, italien, espagnol, japonais, autre}.
- **RG-07** : carte gradée (optionnel) : organisme ∈ {PSA, PCA, CGC, BGS, autre} + note de 1 à 10 (demi-points acceptés). Si gradée, **la note remplace l'état**.
- **RG-08** : photos : **2 minimum** (recto + verso), **8 maximum**, JPEG/PNG/WebP, **5 Mo max** chacune.
- **RG-09** : prix entre **1 € et 10 000 €**. **Tous les montants sont stockés en centimes (entiers).**
- **RG-10** : modifiable ou retirable tant qu'elle n'est ni réservée ni vendue.

### Modération
- **RG-11** : modération **a posteriori** : une annonce est **publiée et visible dès sa création**.
- **RG-12** : un modérateur peut masquer une annonce ou un commentaire ; l'auteur reçoit le motif par notification.
- **RG-13** : un utilisateur ne signale qu'**une fois** le même élément. Au-delà de **3 signalements distincts**, l'annonce est **masquée automatiquement** en attendant un modérateur *(paramétrable)*.

### Prix, frais et commission
- **RG-14** : **frais de protection acheteur = 0,70 € + 5 %** du prix des cartes de la commande, arrondis au centime *(paramétrable)*. C'est la rémunération de la plateforme.
- **RG-15** : frais de port forfaitaires, **une fois par commande** : lettre suivie **2,00 €** ou colis suivi assuré **6,00 €** *(paramétrable)*. Pour un lot, le mode **le plus protecteur** s'applique ; **colis assuré obligatoire si le total des cartes dépasse 100 €**.
- **RG-16** : **total acheteur = Σ prix des cartes + frais de port + frais de protection**. Calculé **côté serveur** et **figé dans la commande** au moment de l'achat.
- **RG-17** : **montant versé au vendeur = prix + frais de port**. Le vendeur touche 100 % de son prix ; la plateforme garde les frais de protection.

### Achat et séquestre
- **RG-18** : impossible d'acheter sa propre annonce. Une commande ne regroupe que des annonces d'**un même vendeur**. Panier en **session** ; un panier multi-vendeurs donne **une commande par vendeur**.
- **RG-19** : au passage au paiement, les annonces sont **réservées 30 minutes** ; sans paiement réussi, elles redeviennent disponibles.
- **RG-20** : le paiement n'est validé **que par le webhook Stripe**, jamais par la redirection du navigateur. Les fonds sont alors conservés par la plateforme.
- **RG-21** : le vendeur a **5 jours** pour expédier et saisir un numéro de suivi ; sinon annulation automatique et remboursement intégral.
- **RG-22** : le vendeur peut annuler **avant expédition** (remboursement intégral). L'acheteur **ne peut pas** annuler après paiement.
- **RG-23** : l'acheteur confirme la réception ou ouvre un litige. Sans action **7 jours après l'expédition**, la réception est confirmée automatiquement.
- **RG-24** : à la confirmation de réception, le montant RG-17 est versé au vendeur et la commande est terminée.

### Litiges et SAV
- **RG-25** : litige (carte non reçue / non conforme) possible **entre l'expédition et la confirmation**. Il **gèle les fonds** et **suspend la confirmation automatique**.
- **RG-26** : le modérateur tranche : remboursement intégral de l'acheteur (frais de protection compris) **ou** versement au vendeur. Décision tracée.
- **RG-27** : email SAV dédié + formulaire de contact accessibles à tous ; une réclamation peut être rattachée à une commande.

### Notation, commentaires, messagerie
- **RG-28** : seul l'acheteur note le vendeur : note entière **1 à 5**, **une par commande terminée**, avis optionnel ≤ 500 caractères, dans les **14 jours** suivant la fin de la commande.
- **RG-29** : le vendeur répond **une fois** à chaque avis. Note moyenne (au dixième) et nombre d'avis affichés sur le profil et les annonces.
- **RG-30** : commentaires publics : connectés uniquement, ≤ 500 caractères, supprimables par leur auteur.
- **RG-31** : messagerie : **une conversation par couple (annonce, acheteur potentiel)**, entre lui et le vendeur uniquement ; ≤ 2 000 caractères par message.

### Cycles de vie
Toute transition de statut absente du schéma est **refusée côté serveur** (implémenter une machine à états explicite, testée).
- **Annonce** : disponible (publiée) → réservée (30 min) → vendue ; disponible → retirée ; masquée (modération) ; réservée → disponible si paiement échoué ou expiré.
- **Commande** : naît **quand le paiement est validé** (webhook), ses annonces passent « vendue ». Payée → expédiée → terminée (fonds versés) ; payée → annulée / remboursée (vendeur annule ou délai de 5 j dépassé) ; expédiée → en litige → remboursée **ou** terminée.
- Les fonds ne quittent la plateforme que vers le vendeur (terminée) ou vers l'acheteur (remboursée).
- Chaque transition est **tracée** (date, auteur).

---

## 6. Modèle de données (MCD Looping)

Point clé à défendre en soutenance : **séparer la carte de référence (`Carte`, catalogue standardisé) de l'annonce (`Annonce`, exemplaire physique vendu)**. C'est ce qui résout le problème des fiches non standardisées et permet les filtres par extension et rareté.

**Le schéma est implémenté** (migrations `database/migrations/2026_11_25_*`, modèles `app/Models`, Enums `app/Enums`). La correspondance complète MCD → tables et colonnes, les contraintes et la justification des choix sont dans **[docs/base-de-donnees.md](docs/base-de-donnees.md)** : à tenir à jour à chaque nouvelle migration. Toute colonne ou table ajoutée doit aussi l'être dans le MCD Looping, pour que le schéma défendu en soutenance reste identique à la base.

Entités et attributs du MCD (noms en français dans le MCD, tables et colonnes en anglais) :

| Entité MCD | Attributs | Table Laravel |
|---|---|---|
| **Extension** | id_extension, nom, date_sortie, logo, slug | `sets` |
| **Carte** | id_carte, nom, numero, rarete, image, id_externe (id API TCG) | `cards` |
| **Utilisateur** | id_utilisateur, pseudo, prenom, nom, adresse_mail, mot_de_passe, adresse, code_postal, ville, pays, date_inscription, role, date_verif_email, suspension, id_stripe, compte_active | `users` |
| **Annonce** | id_annonce, description, date, prix, etat, edition, langue, gradee, organisme_grading, note_si_gradee, mode_envoi, statut | `listings` |
| **Photo** | id_photo, chemin, position, date_ajout (2..8 par annonce) | `listing_photos` |
| **Commande** | id_commande, date_paiement, date_expedition, date_reception, prix, frais_port, frais_protection, total, statut, numero_suivi, id_paiement_stripe, prenom, nom, adresse, ville, code_postal, pays (adresse de livraison **recopiée**) | `orders` (pas de table de liaison : l'annonce porte `order_id`, car Annonce 0,1 — *DONNER LIEU* — Commande 1,n) |
| **Avis** | id_avis, Note, commentaire, date_avis, reponse_vendeur, date_reponse_vendeur | `reviews` (unique par commande) |
| **Commentaire** | id_commentaire, date, contenu, masque, motif_masquage | `comments` |
| **Conversation** | id_conversation, date_debut | `conversations` (unique par annonce + acheteur) |
| **Message** | id_message, contenu, date_envoi, date_lecture | `messages` |
| **Signalement** | id_signalement, date_debut, motif, description, statut | `reports` |
| **Litige** | id_litige, date_ouverture, motif, description, statut, decision, justification, date_resolution | `disputes` |

Associations du MCD : Extension *contenir* Carte ; Carte *Vendre* Annonce ; Utilisateur *Publier* Annonce ; Photo *Illustrer* Annonce (2..8) ; Commande *DONNER LIEU* à Annonce (une annonce appartient à 0 ou 1 commande) ; Utilisateur *PASSER* Commande ; Commande *NOTER* Avis (0..1) ; Commande *DONNER* Litige (0..1), *Trancher* par un modérateur ; Annonce *Recevoir* Commentaire, Utilisateur *Ecrire* Commentaire, Commentaire *Répondre* (réflexive) ; Annonce *OUVRIR* Conversation, Utilisateur *Initier* Conversation ; Conversation *AVOIR* Message, Utilisateur *Envoyer* Message ; Utilisateur *Rediger* Signalement, qui *Viser* une Annonce ou *Concerner* un Commentaire (contrainte XT).

Contrainte explicite du MCD :
```sql
ALTER TABLE SIGNALEMENT ADD CONSTRAINT chk_signalement_cible CHECK (
  (id_annonce IS NOT NULL AND id_commentaire IS NULL)
  OR (id_annonce IS NULL AND id_commentaire IS NOT NULL)
); -- un signalement vise exactement une annonce OU un commentaire
```

Règles d'implémentation :
- Montants en **centimes** (`unsignedInteger` / `bigInteger`), jamais de float.
- Statuts et énumérations (état, édition, langue, rôle, statuts) en **Enums PHP** castés par Eloquent.
- Contraintes d'unicité en BDD quand une règle le dit : 1 avis par commande, 1 signalement par (utilisateur, élément), 1 conversation par (annonce, acheteur), pseudo unique.
- **MySQL** : les contraintes `CHECK` sont appliquées depuis MySQL 8.0.16. Dans les migrations Laravel, les ajouter avec `DB::statement('ALTER TABLE … ADD CONSTRAINT … CHECK (…)')`. Les `ENUM` MySQL sont à éviter : stocker les valeurs en `string` et valider avec les Enums PHP (plus simple à faire évoluer).
- **Index** sur les colonnes filtrées par la recherche (carte, extension, rareté, prix, état, édition, langue, statut).
- Les montants de la commande sont **figés** à l'achat (copie, pas de recalcul).
- Traçabilité Stripe : stocker les ids Stripe (paiement, transfert, remboursement) et les événements de webhook traités (idempotence).

---

## 7. Écrans, maquettes et design system

Fichier Figma (clé `598F85KWGrhygfKUotjdpl`) avec 3 pages :
- **Wireframes** (page `0:1`, basse fidélité, structure et hiérarchie) : https://www.figma.com/design/598F85KWGrhygfKUotjdpl/Wireframes-%E2%80%94-Marketplace-cartes-Pok%C3%A9mon?node-id=0-1
- **Pistes graphiques** (page `11:236`, exploration ; seule la piste C est retenue)
- **Maquettes — piste C** (page `26:236`, haute fidélité, **référence à intégrer**) : https://www.figma.com/design/598F85KWGrhygfKUotjdpl/Wireframes-%E2%80%94-Marketplace-cartes-Pok%C3%A9mon?node-id=26-236

### Utiliser le connecteur Figma (MCP)
Un connecteur Figma est disponible. Pour intégrer un écran :
1. Appeler `create_session` avec la clé `598F85KWGrhygfKUotjdpl` et une courte description de la tâche, puis réutiliser le `sessionId` renvoyé.
2. Charger le skill `figma-design-to-code` (obligatoire avant `get_design_context`).
3. Appeler `get_design_context` sur le node id de la **maquette** (tableau ci-dessous) et `get_screenshot` pour comparer visuellement. `get_variable_defs` donne les tokens (couleurs, typo) à reporter dans Tailwind.
4. Le code renvoyé par Figma est une **référence**, souvent en React/HTML : le **traduire** en Blade + Tailwind + composants Blade/Livewire existants. Ne jamais introduire de React.
5. Si un écran n'a pas de maquette HF, partir du **wireframe** correspondant et appliquer le style de la piste C.

Astuce : `get_metadata` sur une page entière dépasse la taille maximale de réponse ; viser directement le node id d'un écran.

**Piste graphique C retenue** :
- Couleur primaire **sarcelle `#0F766E`**, accent **ambre `#F59E0B`**, neutres **ardoise** (palette `slate` de Tailwind)
- Titres **Space Grotesk**, texte **Inter**
- Reporter tous les tokens dans la config Tailwind (couleurs, polices, espacements, rayons, ombres). Ne pas inventer de couleurs hors palette.

Node ids Figma par écran (format `x:y` pour l'API, `x-y` dans les URLs) :

| Écran | Maquette desktop (1440) | Maquette mobile (390) | Wireframe desktop | Wireframe mobile |
|---|---|---|---|---|
| 01 Accueil | `26:240` | `26:919` | `3:2` | `7:220` |
| 02 Recherche et filtres | `26:302` | `26:982` | `3:108` | `7:289` |
| 03 Fiche annonce | `26:425` | `26:1048` | `3:289` | `7:361` |
| 04 Créer une annonce | `26:506` | — | `3:414` | — |
| 05 Panier et récapitulatif | `26:610` | `26:1101` | `4:164` | `7:420` |
| 06 Détail de commande (acheteur) | `26:675` | — | `4:245` | — |
| 07 Messagerie | `26:742` | — | `4:328` | — |
| 08 Back-office modération | `26:806` | — | `4:408` | — |
| Composant Header | `26:237` | `26:917` | `1:2` | `7:212` |
| Composant Footer | `26:238` | — | `1:11` | — |
| Composant Carte annonce | `26:239` | — | `1:20` | — |

Les 18 écrans du cahier des charges fonctionnel (ceux sans maquette HF suivent le style de la piste C et les wireframes) : Accueil · Recherche et filtres · Fiche annonce (+ commentaires) · Extensions et page carte · Profil public vendeur · Inscription / connexion / mot de passe oublié · Créer / modifier une annonce · Tunnel d'achat (panier, récap, paiement, confirmation) · Mes achats · Mes ventes et solde · Mes annonces · Détail d'une commande (timeline, actions, litige, notation) · Messagerie · Paramètres du compte et activation vendeur · Back-office signalements et contenus masqués · Back-office litiges · Back-office tableau de bord et rôles · Contact / SAV, FAQ, pages légales, erreurs.

Exigences d'intégration :
- **Responsive** : mobile ≥ 360 px, tablette, desktop.
- Composants récurrents en **composants Blade** réutilisables (`resources/views/components`) : bouton, champ, badges (état, édition, rareté), carte d'annonce, étoiles, pagination, modale, toasts.
- Gérer les états vide / chargement / erreur / succès.
- Accessibilité : navigation clavier, `alt` sur les images, contrastes, HTML sémantique.
- URLs lisibles avec slugs, ex. `/extensions/base-set/charizard-4`.
- Filtres de recherche **reflétés dans l'URL** (`#[Url]` Livewire).

### Parcours utilisateurs (Drive)
- **Acheteur** : accueil → recherche et filtres → fiche annonce → (question ? contacter le vendeur, connexion si besoin) → ajouter au panier → autre carte du même vendeur ? → panier et récapitulatif → paiement Stripe (KO : retour panier) → commande confirmée → suivi → carte reçue et conforme ? oui : confirmer la réception puis noter le vendeur ; non : ouvrir un litige.
- **Vendeur** : connecté → compte vendeur actif ? (sinon activation via Stripe) → créer une annonce (carte du catalogue → état, édition, langue, gradée → photos recto/verso → prix et mode d'envoi) → annonce publiée → notification de vente → expédiée sous 5 j ? (non : annulée et remboursée) → saisir le n° de suivi → réception confirmée (ou auto à J+7) → fonds versés ; si litige : décision du modérateur.

---

## 8. Sécurité (non négociable)

- **CSRF** : `@csrf` sur tous les formulaires (Livewire le gère). Seule exception : la route **webhook Stripe** (`POST /api/webhooks/stripe`), authentifiée par **vérification de signature**.
- **Injection SQL** : Eloquent / Query Builder avec bindings. **Jamais** de concaténation de saisie utilisateur dans `DB::raw`, `whereRaw`, `orderByRaw` ; les colonnes de tri et filtres passent par une **liste blanche**.
- **XSS** : toujours `{{ }}` dans Blade. `{!! !!}` interdit sur du contenu utilisateur. Commentaires, messages, descriptions, avis : texte brut échappé.
- **Secrets** : **aucun** mot de passe, clé API ou clé Stripe dans le dépôt, les Dockerfiles, le compose, Notion ou Trello. Tout passe par `.env` (non versionné), `.env.example` avec des valeurs vides, et les **secrets GitHub** pour la CI/CD. Images Docker de prod **sans secrets embarqués**.
- **Autorisations** : Policies sur chaque ressource (un utilisateur ne modifie que ses annonces, commentaires, messages ; seuls les participants lisent une conversation ; back-office réservé modérateur/admin).
- **Validation** : Form Requests / règles Livewire **côté serveur** pour toute saisie (le front ne fait que du confort).
- **Uploads** : vérifier type MIME réel, extension, poids (RG-08) ; nom de fichier généré ; stockage hors du webroot public direct ou via `storage:link`.
- **Rate limiting** : connexion, inscription, commentaires, messages, signalements, API.
- **Paiement** : montants **toujours recalculés côté serveur** ; ne jamais faire confiance à un prix venant du client. Webhooks **idempotents**. Gestion de la **concurrence** (deux acheteurs sur la même annonce : verrou / transaction / `lockForUpdate`).
- Pour la soutenance, chaque faille doit pouvoir être expliquée : ce qui la provoque et comment le projet s'en protège.

---

## 9. Conventions de travail

### Git (Gitflow, exigé par le barème)
- `main` : production. Mise à jour **uniquement par PR** depuis `dev` (ou `release/*`, `hotfix/*`).
- `dev` : branche d'intégration (branche par défaut).
- `feature/<nom-court>` : **une tâche = une carte Trello = une branche = une PR vers `dev`**, relue par l'autre membre avant le merge.
- Commits en **Conventional Commits** : `feat:`, `fix:`, `chore:`, `docs:`, `test:`, `refactor:`, `ci:`…
- **Ne jamais committer directement sur `main` ni sur `dev`.** Ne jamais committer de secrets. Ne pas pousser, merger ni ouvrir de PR sans demande explicite.

### Définition de « terminé »
Une tâche est terminée seulement si : la **CI est verte** (Pint, Larastan, Pest) et les **tests de la fonctionnalité passent**. Priorité des tests : paiement, séquestre, autorisations, transitions de statut, calculs de frais.

### Code Laravel
- Logique métier **hors des contrôleurs et des composants Livewire** : dans des classes dédiées (Actions / Services), des Enums et des Policies. Les composants Livewire orchestrent, ils ne contiennent pas les règles.
- Calculs d'argent dans un service unique et testé (frais de protection, port, total, versement).
- Tâches planifiées (scheduler) pour : expiration des réservations de 30 min, annulation à J+5 sans expédition, confirmation automatique à J+7, fenêtre de notation de 14 j.
- Emails et notifications via la **queue**.
- Messages, libellés et contenu du site en **français**.
- API REST versionnée `/api/v1` : API Resources, pagination, codes HTTP corrects, Sanctum, rate limiting, documentation. **Pas de GraphQL.**
- Lancer `./vendor/bin/pint` et les tests avant de proposer une modification comme terminée.

### Ce qu'un agent ne doit pas faire
- Ajouter React, Vue, Angular, Inertia ou un framework JS lourd (**interdit par le sujet**).
- Ajouter des WebSockets (Reverb, Pusher…) ou GraphQL.
- Ajouter des fonctionnalités **hors périmètre** (voir §10).
- Trancher seul une décision encore ouverte (§3) ou contredire une RG : **demander**.
- Coder des montants, délais ou seuils en dur alors que la RG dit *paramétrable*.

---

## 10. Périmètre MVP

Le MVP est le **parcours complet d'une vente** : publier → trouver → acheter → expédier → confirmer → noter. Il passe **avant** toute fonctionnalité de confort.

- **MVP (obligatoire pour la soutenance)** : US-01 à US-16, US-18, US-20 à US-22, US-26.
- **Si le temps le permet** : US-17 (annulation vendeur), US-19 (réponses du vendeur), US-23 (suspension), US-24 (rôles), US-25 (dashboard admin), US-27 (profil et suppression), masquage auto RG-13, notifications in-app.
- **Hors périmètre** : enchères, offres de prix, échange carte contre carte, annonces multi-exemplaires, notation de l'acheteur par le vendeur, cartes d'autres générations, étiquettes d'envoi générées, gestion du retour d'une carte non conforme.

Liste des user stories : US-01 parcourir extensions et cartes · US-02 rechercher/filtrer · US-03 fiche annonce · US-04 créer un compte · US-05 acheter (panier) · US-06 suivre mes achats · US-07 confirmer la réception · US-08 ouvrir un litige · US-09 noter le vendeur · US-10 messagerie · US-11 commenter · US-12 signaler · US-13 activer le compte vendeur · US-14 publier une annonce · US-15 modifier/retirer mes annonces · US-16 expédier · US-17 annuler une vente · US-18 suivre mes ventes et mon solde · US-19 répondre aux commentaires et avis · US-20 traiter les signalements · US-21 masquer un contenu · US-22 trancher un litige · US-23 suspendre un utilisateur · US-24 gérer les rôles · US-25 tableau de bord admin · US-26 contacter le SAV · US-27 gérer mon profil / supprimer mon compte.

---

## 11. Contradictions connues entre documents

Appliquer le **cahier des charges fonctionnel**, plus récent que les cartes Trello concernées :

| Sujet | Trello (ancien) | Référence à appliquer |
|---|---|---|
| Modération des annonces | Statuts « brouillon → en attente de modération → publiée / refusée » | **RG-11 : modération a posteriori**, publication immédiate |
| Rémunération | « Transfert au vendeur = prix − commission » | **RG-14/17** : l'acheteur paie des frais de protection ; le vendeur touche prix + port |
| Cibles d'un signalement | Annonce, commentaire, profil, message | **Tranché : on suit le MCD**, un signalement vise une annonce OU un commentaire (contrainte XT). Le profil et le message ne sont pas signalables |

---

## 12. Planning et répartition

| Date | Jalon |
|---|---|
| 20/11 | Setup terminé : Docker de dev qui tourne, CI verte |
| 24/11 | Coquille vide en ligne en HTTPS |
| 27/11 | Socle : auth, rôles, catalogue, annonces, recherche |
| 04/12 | Paiement et séquestre fonctionnels |
| 08/12 | Préprod en ligne avec la vraie application |
| **11/12** | **Gel des fonctionnalités** (ensuite, uniquement des corrections) |
| 14/12 | Application en production |
| 18/12 | Soutenance prête |

- **Léo** : GitHub (Gitflow, protections, CI), Docker dev et prod, cœur métier (BDD, auth et rôles, annonces, recherche, Stripe, séquestre, messagerie, back-office, litiges, API, tests critiques, audit de sécurité).
- **José** : déploiement (VPS, domaine, SSL, préprod, prod, sauvegardes, disponibilité), pages d'affichage et de contenu (composants Blade, profils, catalogue, fiche annonce, tableau de bord, accueil, avis, commentaires, signalements, emails, pages légales, données de démo).

Ordre des tâches (Notion, colonne « Ordre ») : 1 SGBD (✅ MySQL) → 2 VPS/domaine → 3 dépôt GitHub + Gitflow → 4 init Laravel (starter kit Livewire) → 5 Docker dev → 6 CI → 7 préparation VPS → 8 coquille HTTPS → 9 migrations et modèles → 10 tokens Figma + layout + composants Blade → 11 auth → 12 rôles et Policies → 13 import catalogue → 14 profils → 15 pages catalogue → 16 créer une annonce → 17 gérer mes annonces → 18 fiche annonce → 19 recherche → 20 Stripe Connect → 21 panier + Checkout → 22 webhooks → 23 cycle de vie commande et séquestre → 24 tableau de bord → 25 Docker prod v1 → 26 préprod → 27 accueil → 28 messagerie → 29 notation → 30 commentaires → 31 back-office → 32 signalements → 33 litiges et SAV → 34 emails et notifications → 35 pages légales et erreurs → 36 API REST v1 → 37 tests critiques → 38 données de démo → 39 audit sécurité → 40 Docker prod final → 41 mise en prod → 42 sauvegardes et uptime → 43 recette → 44-46 soutenance → 47-50 bonus (OAuth/2FA, SEO, déploiement auto, Sentry/GlitchTip).

**État au 25/11/2026** : conception terminée. Tâches 1, 3, 4, 5 et 6 faites (MySQL, GitHub + Gitflow, Laravel avec le starter kit Livewire, Docker de dev, CI). Tâche 9 (migrations, modèles, Enums, factories) en cours de relecture. Le scheduler n'est pas encore dans le `compose.yml` : il faudra l'ajouter pour la tâche 23.

---

## 13. Données de démo

Prévoir des seeders crédibles pour la soutenance : vraies cartes Gen 1 (catalogue importé), profils variés (acheteurs, vendeurs notés, un modérateur, un admin), annonces dans tous les états, **commandes à tous les stades** (payée, expédiée, en litige, terminée, remboursée), avis, conversations. Le catalogue doit pouvoir se charger **sans dépendre de l'API** le jour de la démo.

---

## 14. Liens

- Trello : https://trello.com/b/IbGDQvPr/projet-annuel-marketplace-de-cartes-pokemon
- Notion (reste à faire) : https://app.notion.com/p/41a8a451e43581adb673e69bc03ac81d
- Figma, wireframes : https://www.figma.com/design/598F85KWGrhygfKUotjdpl/Wireframes-%E2%80%94-Marketplace-cartes-Pok%C3%A9mon?node-id=0-1
- Figma, maquettes piste C : https://www.figma.com/design/598F85KWGrhygfKUotjdpl/Wireframes-%E2%80%94-Marketplace-cartes-Pok%C3%A9mon?node-id=26-236
- Drive (documents du projet) : https://drive.google.com/drive/folders/16yjPFtPW0i5NEcoo2bcJbR7hoOj4tvZR
  (cahier des charges métier, cahier des charges fonctionnel, parcours acheteur, parcours vendeur, MCD Looping, sujet)
