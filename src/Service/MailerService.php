<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

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
        array $context
    ): void {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@monSite.fr', 'monSite'))
            ->to($to)
            ->subject($subject)
            ->htmlTemplate("mail/$templateTwig")
            ->context($context);
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            // Log the error or handle it as needed
            // Example: Log::error($e->getMessage());
            throw new \RuntimeException('Failed to send email: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
