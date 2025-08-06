<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire d'inscription</title>
    <link rel="stylesheet" href="styles.css"> <!-- Fichier CSS optionnel -->
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background: #f5f5f5;
        }
        .container {
            max-width: 500px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }
        input, select, button {
            display: block;
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
        }
        h4 {
            margin-bottom: 10px;
        }
        p {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <form method="post" action="inscription.php">
            <h4>Créer un compte</h4>
            <hr>

            <label for="email">Email :</label>
            <input type="email" name="email" id="email" required>

            <label for="nom">Nom :</label>
            <input type="text" name="nom" id="nom" required>

            <label for="motdepasse">Mot de passe :</label>
            <input type="password" name="motdepasse" id="motdepasse" required>

            <label for="date_de_naissance">Date de naissance :</label>
            <input type="date" name="date_de_naissance" id="date_de_naissance">

            <label for="genre">Genre :</label>
            <select name="genre" id="genre">
                <option value="Homme">Homme</option>
                <option value="Femme">Femme</option>
                <option value="Autre">Autre</option>
            </select>

            <label for="telephone">Téléphone :</label>
            <input type="text" name="telephone" id="telephone">

            <button type="submit">S'inscrire</button>
        </form>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Récupération des données avec sécurité minimale
    $email = trim($_POST["email"] ?? '');
    $nom = trim($_POST["nom"] ?? '');
    $motdepasse = $_POST["motdepasse"] ?? '';
    $date_de_naissance = !empty($_POST["date_de_naissance"]) ? $_POST["date_de_naissance"] : null;
    $genre = !empty($_POST["genre"]) ? $_POST["genre"] : "Autre";
    $telephone = !empty($_POST["telephone"]) ? trim($_POST["telephone"]) : "";

    // Vérification des champs obligatoires
    if (empty($email) || empty($nom) || empty($motdepasse)) {
        echo "<p style='color: red;'>Veuillez remplir tous les champs obligatoires.</p>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<p style='color: red;'>Email invalide.</p>";
    } else {
        // Connexion à la base
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "agrismart";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("<p style='color: red;'>Connexion échouée : " . $conn->connect_error . "</p>");
        }

        // Vérifier si l'email existe déjà
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo "<p style='color: red;'>Cet email est déjà utilisé.</p>";
        } else {
            // Hasher mot de passe
            $hash = password_hash($motdepasse, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (email, nom, motdepasse, date_de_naissance, genre, telephone) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $email, $nom, $hash, $date_de_naissance, $genre, $telephone);

            if ($stmt->execute()) {
                echo "<p style='color: green;'>Inscription réussie !</p>";
            } else {
                echo "<p style='color: red;'>Erreur : " . $stmt->error . "</p>";
            }

            $stmt->close();
        }

        $check->close();
        $conn->close();
    }
}
?>
    </div>
</body>
</html>
