# Session 10 - Robustesse

TagAdminTest contient 5 tests verts : oui
/posts/categorie/inconnue affiche Catégorie introuvable avec le statut 404 : oui
php artisan test est vert : à vérifier

1. Pourquoi le message de messages() l'emporte-t-il sur celui de lang/fr/validation.php ?
   Laravel recherche d'abord le message le plus précis pour le champ et la règle ; messages() définit la clé slug.unique avant le message général du fichier de langue.

2. Pourquoi phpunit.xml fixe-t-il APP_LOCALE ?
   Les tests doivent vérifier les mêmes textes français sur tous les postes et dans un clone propre, indépendamment du fichier .env local.

3. Pourquoi CategoryController n'a-t-il besoin d'aucun try/catch ?
   La Form Request traite les données invalides avant l'action, la liaison de modèle gère les modèles absents, le middleware traite les écritures en lecture seule et le gestionnaire d'exceptions transforme ces réponses en pages adaptées.
