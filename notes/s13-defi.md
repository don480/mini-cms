# Session 13 - Défi

/api/posts/3 affiche tags avec des noms : oui
/api/posts ne contient aucun body, /api/posts/3 en contient un : oui
Aucune LazyLoadingViolationException : oui

Pourquoi ne pas envoyer body dans la liste, et qui en profite ?
La liste reste légère : moins d'octets sont transmis et le téléphone ou l'application cliente charge le contenu complet uniquement lorsqu'une personne ouvre un article.

Un brouillon renvoie 403. Quel avantage aurait un 404 pour un brouillon ?
Un 404 ne révèle pas qu'un brouillon existe avec ce numéro, alors qu'un 403 confirme son existence. Mini-CMS utilise volontairement la même règle 403 sur le site et l'API grâce à PostPolicy.
