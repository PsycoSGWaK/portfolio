<?php

namespace App\Service;

use App\Entity\ContactMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Notifie par mail l'arrivée d'un message de contact.
 *
 * L'envoi est volontairement "best effort" : le message est déjà persisté en base
 * et consultable dans l'admin quand ces méthodes sont appelées. Un SMTP indisponible
 * ou un mail classé en indésirables ne doit donc jamais faire échouer la soumission
 * ni afficher une erreur au visiteur.
 *
 * Les deux envois (notification à Guillaume, accusé au visiteur) sont indépendants :
 * l'échec de l'un ne doit pas empêcher l'autre.
 */
class ContactNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly TranslatorInterface $translator,
        #[Autowire('%env(CONTACT_FROM_EMAIL)%')]
        private readonly string $fromEmail,
        #[Autowire('%env(CONTACT_ACK_FROM_EMAIL)%')]
        private readonly string $ackFromEmail,
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

    /**
     * Envoie au visiteur un accusé de réception dans sa langue (locale de la page
     * utilisée). Depuis l'adresse no-reply, indépendamment de la notification :
     * même si la notification à Guillaume échoue, cet accusé doit partir.
     */
    public function acknowledge(ContactMessage $message, string $locale): bool
    {
        $recipient = $message->getEmail();
        if (!$recipient) {
            return false;
        }

        $t = fn (string $key, array $params = []): string => $this->translator->trans($key, $params, null, $locale);

        $email = (new Email())
            ->from(new Address($this->ackFromEmail, 'Guillaume Hurard'))
            ->to($recipient)
            ->subject($t('Votre message a bien été reçu'))
            ->text($t("Bonjour %name%,", ['%name%' => (string) $message->getName()]) . "\n\n"
                . $t("Merci pour votre message, je l'ai bien reçu et je vous répondrai dès que possible.") . "\n\n"
                . $t("Ceci est une confirmation automatique : merci de ne pas répondre à cette adresse. Je vous recontacterai directement.") . "\n\n"
                . '— Guillaume Hurard');

        try {
            $this->mailer->send($email);

            return true;
        } catch (\Throwable $e) {
            $this->logger->error('Échec de l\'envoi de l\'accusé de réception au visiteur.', [
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
