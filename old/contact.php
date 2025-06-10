<?php
// Inclure les classes PHPMailer nécessaires
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP; // Pour le débogage SMTP

// Adaptez ces chemins à l'emplacement réel de PHPMailer dans votre projet
// Par exemple, si votre dossier "includes" est à la racine de votre projet
require 'includes/PHPMailer/Exception.php';
require 'includes/PHPMailer/PHPMailer.php';
require 'includes/PHPMailer/SMTP.php'; // Nécessaire pour utiliser SMTP

// Vérifie si la requête est bien de type POST (formulaire soumis)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Récupération et nettoyage des données du formulaire
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $message = htmlspecialchars(trim($_POST['message']));

    // Tableau pour stocker les messages d'erreur de validation
    $errors = [];

    // 2. Validation des données
    if (empty($name)) {
        $errors[] = "Le champ 'Nom et Prénom' est requis.";
    }
    if (empty($email)) {
        $errors[] = "Le champ 'Votre adresse e-mail' est requis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // Valide le format de l'adresse e-mail
        $errors[] = "L'adresse e-mail fournie n'est pas valide.";
    }
    if (empty($message)) {
        $errors[] = "Le champ 'Votre message' est requis.";
    }

    // 3. Traitement de l'envoi de l'e-mail si aucune erreur de validation
    if (empty($errors)) {
        $mail = new PHPMailer(true); // Passer 'true' active les exceptions pour un meilleur débogage

        try {
            // PARAMÈTRES DE DÉBOGAGE (À RETIRER EN PRODUCTION !)
            // Décommenter ces lignes pour voir les détails de la communication SMTP
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER; 
            // $mail->Debugoutput = 'html'; 

            // Paramètres du serveur SMTP Gmail
            $mail->isSMTP();                                    // Utiliser SMTP
            $mail->Host       = 'smtp.gmail.com';               // Serveur SMTP de Gmail
            $mail->SMTPAuth   = true;                           // Activer l'authentification SMTP

            // Votre adresse Gmail complète (celle pour laquelle le mot de passe d'application a été généré)
            // $mail->Username   = 'tusviamuzun@gmail.com'; 
            $mail->Username   = 'chaminade.d.adjolou@gmail.com'; 
            // Votre mot de passe d'application Google de 16 caractères (sans espaces)
            $mail->Password   = 'gdqnrsjoqphmihma'; 

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Activer le chiffrement TLS explicite (STARTTLS)
            $mail->Port       = 587;                            // Port SMTP pour STARTTLS

            // Alternative : Si le port 587/STARTTLS ne fonctionne pas, essayez SSL implicite sur le port 465
            // $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Activer le chiffrement SSL implicite
            // $mail->Port       = 465;                            // Port SMTP pour SMTPS

            // Expéditeur du mail tel qu'il apparaîtra dans la boîte de réception
            // Il est recommandé d'utiliser une adresse Gmail valide ici, idéalement la même que $mail->Username
            $mail->setFrom('chaminade.d.adjolou@gmail.com', 'grosbit.com'); // L'adresse email qui envoie

            // Destinataire : où vous voulez recevoir le message du formulaire
            // Cela peut être la même adresse Gmail ou une autre.
            $mail->addAddress('chaminade.d.adjolou@gmail.com', 'chaminade.d.adjolou'); // L'adresse email qui reçoit

            // Pour permettre de répondre directement au visiteur du formulaire
            $mail->addReplyTo($email, $name); 

            // Contenu de l'e-mail
            $mail->isHTML(false); // Définir le format de l'e-mail sur texte brut (true pour HTML)
            $mail->Subject = "Nouveau message de contact de " . $name; // Sujet de l'e-mail
            $mail->Body    = "Nom et Prénom: " . $name . "\n";
            $mail->Body   .= "Email: " . $email . "\n\n";
            $mail->Body   .= "Message:\n" . $message;
            $mail->CharSet = 'UTF-8'; // S'assurer que les caractères spéciaux sont bien encodés

            $mail->send(); // Tente d'envoyer l'e-mail
            echo "<p style='color: green; text-align: center; padding: 20px; font-size: 1.2em;'>Votre message a été envoyé avec succès à votre boîte Gmail !</p>";
            header("Location: /grosbit/index.html"); // Redirige vers la page d'accueil ou une autre page
        } catch (Exception $e) {
            // En cas d'erreur, affiche un message générique et les détails pour le débogage
            echo "<p style='color: red; text-align: center; padding: 20px; font-size: 1.2em;'>Une erreur est survenue lors de l'envoi de votre message.</p>";
            echo "<p style='color: red; text-align: center; padding: 20px; font-size: 0.9em;'>Détails de l'erreur: {$mail->ErrorInfo}</p>";
        }
    } else {
        // Affichage des erreurs de validation si le formulaire est mal rempli
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
    // Redirige si la page est accédée directement sans soumission de formulaire
    header("Location: /grosbit/index.html"); // Redirige vers la page d'accueil ou une autre page
    exit();
}
?>