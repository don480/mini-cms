# Session 03 - Routes, contrôleurs et layout

## Défi

URL produite par route('about') :
http://localhost:8000/a-propos

URL produite par route('posts.show', 'routes-et-controleurs') :
http://localhost:8000/posts/routes-et-controleurs

URL produite par route('posts.archive', 2025) :
http://localhost:8000/posts/archive/2025

Pourquoi /posts/archive/abc et /posts/article-inconnu répondent toutes deux 404, et quelle ligne de code en est responsable dans chaque cas ?
/posts/archive/abc : la contrainte whereNumber('year') dans routes/web.php rejette la valeur "abc" (pas un nombre), donc aucune route ne correspond et Laravel répond 404.
/posts/article-inconnu : la route posts.show correspond bien, mais le abort(404) dans PostController::show se déclenche car l'article n'existe pas.

Que se passe-t-il si une vue appelle route('posts.show') sans paramètre ?
Laravel lève une exception : "Missing required parameter for [Route: posts.show]", car le paramètre de l'URI (le slug) est obligatoire pour générer l'URL.