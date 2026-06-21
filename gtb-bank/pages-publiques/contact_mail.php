<?php
/**
 * GTB BANK — Traitement formulaire de contact
 * Reçoit les données POST et envoie un email au support
 */

require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/helpers.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

// Récupération et nettoyage des données
$name    = htmlspecialchars(strip_tags(trim($_POST['name']    ?? '')));
$email   = filter_var(trim($_POST['email']   ?? ''), FILTER_SANITIZE_EMAIL);
$phone   = htmlspecialchars(strip_tags(trim($_POST['phone']   ?? '')));
$subject = htmlspecialchars(strip_tags(trim($_POST['subject'] ?? '')));
$message = htmlspecialchars(strip_tags(trim($_POST['message'] ?? '')));

// Validation
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Tous les champs obligatoires doivent être remplis.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Adresse email invalide.']);
    exit;
}

// Sujets lisibles
$subjects = [
    'account'     => 'Problème de compte',
    'card'        => 'Carte bancaire',
    'transaction' => 'Transaction',
    'security'    => 'Sécurité',
    'other'       => 'Autre',
];
$subjectLabel = $subjects[$subject] ?? $subject;

// Construction de l'email HTML
$mailSubject = "[GTB Contact] {$subjectLabel} - {$name}";
$html = "
<div style='font-family:Arial,sans-serif;max-width:600px;margin:auto;padding:32px;border:1px solid #e5e7eb;border-radius:8px'>
  <h2 style='color:#1a3c5e'>Nouveau message — Formulaire de contact</h2>
  <table style='width:100%;border-collapse:collapse;margin:16px 0'>
    <tr><td style='padding:8px;color:#6b7280;width:120px'>Nom</td><td style='padding:8px;font-weight:600'>" . htmlspecialchars($name) . "</td></tr>
    <tr style='background:#f9fafb'><td style='padding:8px;color:#6b7280'>Email</td><td style='padding:8px'><a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a></td></tr>
    <tr><td style='padding:8px;color:#6b7280'>Téléphone</td><td style='padding:8px'>" . ($phone ? htmlspecialchars($phone) : 'Non renseigné') . "</td></tr>
    <tr style='background:#f9fafb'><td style='padding:8px;color:#6b7280'>Sujet</td><td style='padding:8px'>" . htmlspecialchars($subjectLabel) . "</td></tr>
    <tr><td style='padding:8px;color:#6b7280'>Date</td><td style='padding:8px'>" . date('d/m/Y H:i:s') . "</td></tr>
  </table>
  <div style='background:#f3f4f6;padding:16px;border-radius:6px;margin-top:16px'>
    <p style='color:#374151;margin:0'>" . nl2br(htmlspecialchars($message)) . "</p>
  </div>
  <p style='margin-top:16px;color:#6b7280;font-size:13px'>Répondre directement à : <a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a></p>
</div>";

// Envoi via Brevo vers ton Gmail
$sent = send_email(MAIL_SUPPORT, 'Global Trust Bank', $mailSubject, $html);

if ($sent) {
    echo json_encode(['success' => true, 'message' => 'Message envoyé avec succès !']);
} else {
    error_log("[GTB Contact] Échec envoi Brevo depuis {$email}");
    echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'envoi. Veuillez réessayer.']);
}
