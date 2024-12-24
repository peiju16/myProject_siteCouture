<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\MixedPart;

/**
 * MailerService pour générer un mail
 */
class MailerService
{
    public function __construct(private readonly MailerInterface $mailer) {}

    /**
     * send
     *
     * @return void
     * @throws TransportExceptionInterface
     */
    public function send(
        string $to,
        string $subject,
        string $templateTwig,
        array $context,
        array $attachments = [] // New parameter for attachments
    ): void {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@monSite.fr', 'monSite'))
            ->to($to)
            ->subject($subject)
            ->htmlTemplate("mail/$templateTwig")
            ->context($context);

        // Attach files if any
        foreach ($attachments as $attachment) {
            if (is_array($attachment) && isset($attachment['path'])) {
                $email->attachFromPath(
                    $attachment['path'],
                    $attachment['name'] ?? null,
                    $attachment['contentType'] ?? null
                );
            }
        }
        

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            // Log the error or handle it as needed
            // Example: Log::error($e->getMessage());
            throw new \RuntimeException('Failed to send email: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
