<?php
// Activer le rapport d'erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Détails de connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "enquêteur";

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Connexion à la base de données échouée']);
    exit;
}

// Récupérer les données POST
$numcin = $_POST['numcin'] ?? '';
$project = htmlspecialchars($_POST['project'] ?? '', ENT_QUOTES, 'UTF-8');

// Valider numcin : doit être exactement de 8 chiffres
if (empty($numcin) || !ctype_digit($numcin) || strlen($numcin) != 8) {
    echo json_encode(['status' => 'error', 'message' => 'Le numéro CIN est requis et doit être exactement de 8 chiffres.']);
    exit;
}

// Valider le nom du projet
if (empty($project)) {
    echo json_encode(['status' => 'error', 'message' => 'Le nom du projet est requis.']);
    exit;
}

// Vérifier si ce CIN a déjà soumis ce projet
$sql = "SELECT * FROM submissions WHERE cin = ? AND project = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $numcin, $project);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Ce CIN a déjà soumis ce projet.']);
    exit;
}

// Créer un dossier unique pour le projet
$userDir = 'uploads/' . $project . '/';
if (!is_dir($userDir)) {
    mkdir($userDir, 0777, true);
}

// Traiter les fichiers téléchargés et les enregistrer dans le dossier utilisateur
$cinPath = '';
$contractPath = '';

if (isset($_FILES['cin']) && $_FILES['cin']['error'] === UPLOAD_ERR_OK) {
    $cinPath = $userDir . basename($_FILES['cin']['name']);
    move_uploaded_file($_FILES['cin']['tmp_name'], $cinPath);
}

if (isset($_FILES['contract']) && $_FILES['contract']['error'] === UPLOAD_ERR_OK) {
    $contractPath = $userDir . basename($_FILES['contract']['name']);
    move_uploaded_file($_FILES['contract']['tmp_name'], $contractPath);
}

// Enregistrer les informations de l'utilisateur dans un fichier texte
$userInfo = "CIN: $numcin\nProjet: $project\nValidé: " . (isset($_POST['validated']) ? 'Oui' : 'Non') . "\n";
file_put_contents($userDir . 'info.txt', $userInfo, FILE_APPEND);

// Préparer et exécuter la requête SQL pour insérer les données dans la base de données
$stmt = $conn->prepare("INSERT INTO submissions (numcin, cin, contract, project, validated) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur de préparation de la requête: ' . $conn->error]);
    exit;
}

$validated = isset($_POST['validated']) ? 1 : 0;
$stmt->bind_param("ssssi", $numcin, $cinPath, $contractPath, $project, $validated);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Nouveau dossier créé ou mis à jour avec succès']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'exécution de la requête: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
