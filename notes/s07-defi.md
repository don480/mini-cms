# Session 07 - Défi : articles de la même catégorie

La section apparaît sous un article publié qui a une catégorie et des articles voisins : oui

L'article affiché n'apparaît pas dans sa propre section : oui

Un brouillon de la même catégorie n'apparaît pas : oui

La page s'ouvre sans `LazyLoadingViolationException` : oui

Le nouveau test passe et `php artisan test` est vert : oui

Combien de requêtes SQL charge la section, et pourquoi pas une par carte ?
Quatre requêtes : une pour les articles de la catégorie, puis une pour leurs auteurs, une pour leurs catégories et une pour leurs tags. Le `with()` regroupe les relations, donc il ne lance pas une requête par carte.
