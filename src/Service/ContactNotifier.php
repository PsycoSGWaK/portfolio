<?php

namespace App\Service;

use App\Entity\ContactMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Notifie par mail l'arrivée d'un message de contact.
 *
 * L'envoi est volontairement "best effort" : le message est déjà persisté en base
 * et consultable dans l'admin quand cette méthode est appelée. Un SMTP indisponible
 * ou un mail classé en indésirables ne doit donc jamais faire échouer la soumission
 * ni afficher une erreur au visiteur.
 */
class ContactNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        #[Autowire('%env(CONTACT_FROM_EMAIL)%')]
        private readonly string $fromEmail,
    ) {
    }

    public function notify(ContactMessage $message, ?string $recipient): bool
    {
        if (!$recipient) {
            $this->logger->warning('Message de contact reçu mais aucune adresse de notification configurée.', [
                'messageId' => $message->getId(),
            ]);

            return false;
        }

        $email = (new Email())
            // L'expéditeur doit rester une adresse du domaine, autorisée par le SPF.
            // Mettre l'adresse du visiteur ici ferait rejeter ou spammer le mail.
            ->from(new Address($this->fromEmail, 'Portfolio Guillaume Hurard'))
            ->to($recipient)
            // ... mais on répond quand même au visiteur en un clic.
            ->replyTo(new Address((string) $message->getEmail(), (string) $message->getName()))
            ->subject(sprintf('Nouveau message de %s', $message->getName()))
            ->text($this->buildBody($message));

        try {
            $this->mailer->send($email);

            return true;
        } catch (\Throwable $e) {
            $this->logger->error('Échec de l\'envoi de la notification de contact.', [
                'messageId' => $message->getId(),
                'exception' => $e,
            ]);

            return false;
        }
    }

    private function buildBody(ContactMessage $message): string
    {
        $lines = [
            sprintf('De : %s <%s>', $message->getName(), $message->getEmail()),
        ];

        if ($message->getCompany()) {
            $lines[] = sprintf('Société : %s', $message->getCompany());
        }

        $lines[] = sprintf('Reçu le : %s', $message->getCreatedAt()->format('d/m/Y à H:i'));
        $lines[] = '';
        $lines[] = (string) $message->getMessage();
        $lines[] = '';
        $lines[] = '— Ce message est également consultable dans l\'admin du portfolio.';

        return implode("\n", $lines);
    }
}
