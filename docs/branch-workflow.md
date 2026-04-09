# Workflow des branches

## Objectif

Le depot maintient deux circuits distincts :

- un circuit `PostgreSQL`
- un circuit `MySQL`

Les merges entre ces deux circuits ne doivent pas se faire par reflexe. Ils ne sont autorises qu'en cas de migration ou de decision explicite.

## Branches de reference

### Circuit PostgreSQL

- `develop-postgres` : branche de developpement
- `main` : branche de production

Flux attendu :

- les branches de fonctionnalite PostgreSQL partent de `develop-postgres`
- les merges valides vont vers `develop-postgres`
- les releases PostgreSQL se font de `develop-postgres` vers `main`

### Circuit MySQL

- `develop` : branche de developpement
- `preprod` : branche de preproduction / production cible du circuit MySQL

Flux attendu :

- les branches de fonctionnalite MySQL partent de `develop`
- les merges valides vont vers `develop`
- les releases MySQL se font de `develop` vers `preprod`

## Regles d'equipe

- ne pas developper directement sur `main`
- ne pas developper directement sur `preprod`
- creer chaque branche de travail depuis la bonne branche de developpement
- ne pas merger `develop-postgres` vers `develop`
- ne pas merger `develop` vers `develop-postgres`
- ne pas merger `main` vers `preprod`
- ne pas merger `preprod` vers `main`

## Nommage conseille

- `feature/...` pour une nouvelle fonctionnalite
- `fix/...` pour une correction standard
- `hotfix/...` pour une correction urgente liee a la branche de prod concernee

## Raccourci pratique

Si la fonctionnalite concerne la base `PostgreSQL`, travailler dans le circuit :

- `develop-postgres` -> `main`

Si la fonctionnalite concerne la base `MySQL`, travailler dans le circuit :

- `develop` -> `preprod`
