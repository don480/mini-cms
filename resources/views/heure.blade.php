<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Heure</title>
</head>
<body>
    <h1>Il est {{ now('Africa/Tunis')->format('H:i') }}</h1>
    <p>Nous sommes le {{ now()->format('d/m/Y') }}</p>
</body>
</html>