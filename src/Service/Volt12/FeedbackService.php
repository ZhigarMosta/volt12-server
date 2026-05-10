<?php

namespace App\Service\Volt12;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class FeedbackService
{
    public function __construct(
        private MailerInterface $mailer
    )
    {
    }

    public function send(string $type, string $userName, string $userPhone, string $userEmail, string $description): void
    {
        $email = (new Email())
            ->from('zhigarkirill@gmail.com')
            ->to('zhigarkirill@gmail.com')
            ->subject($type)
            ->html($this->buildHtml($userName, $userPhone, $userEmail, $description));

        $this->mailer->send($email);
    }

    private function buildHtml(string $userName, string $userPhone, string $userEmail, string $description): string
    {
        return '
                    <!DOCTYPE html>
                    <html>
                    <head><meta charset="UTF-8"></head>
                    <body>
                    <table style="border-collapse:collapse;width:100%;max-width:600px;font-family:Arial,sans-serif;">
                        <tr><td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Имя</td><td style="padding:8px;border:1px solid #ddd;">'.$userName.'</td></tr>
                        <tr><td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Телефон</td><td style="padding:8px;border:1px solid #ddd;">'.$userPhone.'</td></tr>
                        <tr><td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Email</td><td style="padding:8px;border:1px solid #ddd;">'.$userEmail.'</td></tr>
                        <tr><td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Описание</td><td style="padding:8px;border:1px solid #ddd;">'.$description.'</td></tr>
                    </table>
                    </body>
                    </html>';
    }
}
