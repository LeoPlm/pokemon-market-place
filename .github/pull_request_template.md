## Quoi

<!-- Ce que change cette PR, en 1 à 3 phrases. -->

## Carte Trello / tâche Notion

<!-- Lien vers la carte Trello (une PR = une carte). -->

## Règles de gestion et user stories concernées

<!-- Ex. RG-14, RG-16, US-05 (voir AGENT.md). -->

## Comment tester

1. `docker compose up -d`
2. …

## Checklist

- [ ] La branche suit le Gitflow (`feature/…` → `dev`, ou `dev` / `release/*` / `hotfix/*` → `main`)
- [ ] Le titre de la PR suit les Conventional Commits (`feat: …`, `fix: …`)
- [ ] `vendor/bin/pint`, `vendor/bin/phpstan analyse` et `vendor/bin/pest` passent en local
- [ ] Tests ajoutés pour les règles métier touchées
- [ ] Aucun secret (mot de passe, clé API, clé Stripe) dans le code ou les fichiers de config
- [ ] Captures d'écran jointes si l'interface change (comparées à la maquette Figma)
