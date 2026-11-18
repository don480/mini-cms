# Session 08 - Défi

La route `posts.tag` utilise `{tag:slug}` : oui.
La page d'un tag n'affiche ni brouillon ni article d'un autre tag : oui.
Les tags des cartes sont des liens : oui.
L'accueil n'affiche que les tags qui ont au moins un article publié : oui.
Les trois nouveaux tests passent et `php artisan test` est vert : oui.

Pourquoi filtrer les tags vides après `get()` plutôt que d'afficher (0) ?

Un tag sans article publié mènerait à une page vide. Le filtrer évite de proposer une page sans contenu.

Combien de requêtes ajoute la liste des tags populaires, et pourquoi pas une par tag ?

Une seule requête récupère les tags avec `withCount`. La base calcule le nombre d'articles publiés pour chaque tag dans cette requête, donc il n'y a pas une requête supplémentaire par tag.
