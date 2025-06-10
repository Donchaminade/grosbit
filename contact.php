<?php
// Votre code contact.php SANS PHPMailer et SANS ini_set() pour HOSTINGER

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $message = htmlspecialchars(trim($_POST['message']));

    $errors = [];
    if (empty($name)) { $errors[] = "Le champ 'Nom et Prénom' est requis."; }
    if (empty($email)) { $errors[] = "Le champ 'Votre adresse e-mail' est requis."; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "L'adresse e-mail fournie n'est pas valide."; }
    if (empty($message)) { $errors[] = "Le champ 'Votre message' est requis."; }

    if (empty($errors)) {
        // !!! TRÈS IMPORTANT : Remplacez par l'adresse où vous voulez recevoir les messages sur HOSTINGER !!!
        $to = "contact@grosbit.com"; 

        $subject = "Nouveau message depuis votre formulaire de contact";
        
        $body = "Nom et Prénom: " . $name . "\n";
        $body .= "Email: " . $email . "\n\n";
        $body .= "Message:\n" . $message;

        $headers = "From: " . $name . " <" . $email . ">\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        if (mail($to, $subject, $body, $headers)) {
            echo "<p style='color: green; text-align: center; padding: 20px; font-size: 1.2em;'>Votre message a été envoyé avec succès ! Nous vous répondrons bientôt.</p>";
            header("Location: /grosbit/index.html"); // Redirige vers la page d'accueil ou une autre page
        } else {
            echo "<p style='color: red; text-align: center; padding: 20px; font-size: 1.2em;'>Désolé, une erreur est survenue lors de l'envoi de votre message.</p>";
            header("Location: /grosbit/index.html"); // Redirige vers la page d'accueil ou une autre page
        }
    } else {
        echo "<div style='color: red; text-align: center; padding: 20px;'>";
        echo "<p style='font-weight: bold;'>Le formulaire contient les erreurs suivantes :</p>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>" . $error . "</li>";
        }
        echo "</ul>";
        echo "<p>Veuillez corriger ces erreurs et réessayer.</p>";
        echo "</div>";
    }
} else {
    header("Location: /");
    exit();
}
?>