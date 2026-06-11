<?php

namespace App\Service\Volt12;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class FeedbackService
{
    public function __construct(
        private MailerInterface $mailer,
        private string $mailerFrom
    )
    {
    }

    public function send(string $type, string $userName, string $userPhone, string $userEmail, string $description): void
    {
        $email = (new Email())
            ->from($this->mailerFrom)
            ->to($this->mailerFrom)
            ->subject($type)
            ->html($this->buildHtml($userName, $userPhone, $userEmail, $description));

        $this->mailer->send($email);
    }

    private function buildHtml(string $userName, string $userPhone, string $userEmail, string $description): string
    {
        $escapedName        = htmlspecialchars($userName,    ENT_QUOTES);
        $escapedPhone       = htmlspecialchars($userPhone,   ENT_QUOTES);
        $escapedEmail       = htmlspecialchars($userEmail,   ENT_QUOTES);
        $escapedDescription = nl2br(htmlspecialchars($description, ENT_QUOTES));

        return <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:40px 0;">
    <tr>
      <td align="center">
        <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">

          <!-- Header -->
          <tr>
            <td style="background:#e63535;padding:28px 36px;">
              <p style="margin:0;color:#ffffff;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;">Мастер 12 Вольт</p>
              <h1 style="margin:6px 0 0;color:#ffffff;font-size:22px;font-weight:700;">Новая заявка на констультантц</h1>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:32px 36px;">

              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="padding-bottom:20px;border-bottom:1px solid #f0f0f0;">
                    <p style="margin:0 0 4px;font-size:11px;color:#999;text-transform:uppercase;letter-spacing:1px;">Имя</p>
                    <p style="margin:0;font-size:16px;color:#1a1a1a;font-weight:600;">$escapedName</p>
                  </td>
                </tr>
                <tr>
                  <td style="padding:20px 0;border-bottom:1px solid #f0f0f0;">
                    <p style="margin:0 0 4px;font-size:11px;color:#999;text-transform:uppercase;letter-spacing:1px;">Телефон</p>
                    <p style="margin:0;font-size:16px;color:#1a1a1a;font-weight:600;">
                      <a href="tel:$escapedPhone" style="color:#e63535;text-decoration:none;">$escapedPhone</a>
                    </p>
                  </td>
                </tr>
                <tr>
                  <td style="padding:20px 0;border-bottom:1px solid #f0f0f0;">
                    <p style="margin:0 0 4px;font-size:11px;color:#999;text-transform:uppercase;letter-spacing:1px;">Email</p>
                    <p style="margin:0;font-size:16px;color:#1a1a1a;font-weight:600;">
                      <a href="mailto:$escapedEmail" style="color:#e63535;text-decoration:none;">$escapedEmail</a>
                    </p>
                  </td>
                </tr>
                <tr>
                  <td style="padding-top:20px;">
                    <p style="margin:0 0 8px;font-size:11px;color:#999;text-transform:uppercase;letter-spacing:1px;">Сообщение</p>
                    <p style="margin:0;font-size:15px;color:#333;line-height:1.6;background:#f8f8f8;border-radius:8px;padding:16px;">$escapedDescription</p>
                  </td>
                </tr>
              </table>

            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding:20px 36px;background:#fafafa;border-top:1px solid #f0f0f0;">
              <p style="margin:0;font-size:12px;color:#bbb;text-align:center;">Это автоматическое письмо — отвечать на него не нужно</p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

    public function sendEmailVerification(string $toEmail, string $verifyUrl): void
    {
        $escapedUrl = htmlspecialchars($verifyUrl, ENT_QUOTES);

        $html = <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:40px 0;">
    <tr>
      <td align="center">
        <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
          <tr>
            <td style="background:#e63535;padding:28px 36px;">
              <p style="margin:0;color:#ffffff;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;">Мастер 12 Вольт</p>
              <h1 style="margin:6px 0 0;color:#ffffff;font-size:22px;font-weight:700;">Подтверждение почты</h1>
            </td>
          </tr>
          <tr>
            <td style="padding:32px 36px;">
              <p style="margin:0 0 24px;font-size:15px;color:#333;line-height:1.6;">
                Нажмите на кнопку ниже, чтобы подтвердить ваш email-адрес.
              </p>
              <table cellpadding="0" cellspacing="0">
                <tr>
                  <td style="border-radius:8px;background:#e63535;">
                    <a href="$escapedUrl" style="display:inline-block;padding:14px 32px;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;font-family:Arial,sans-serif;">
                      Подтвердить почту
                    </a>
                  </td>
                </tr>
              </table>
              <p style="margin:24px 0 0;font-size:13px;color:#999;line-height:1.6;">
                Если кнопка не работает, скопируйте эту ссылку в браузер:<br>
                <a href="$escapedUrl" style="color:#e63535;word-break:break-all;">$escapedUrl</a>
              </p>
            </td>
          </tr>
          <tr>
            <td style="padding:20px 36px;background:#fafafa;border-top:1px solid #f0f0f0;">
              <p style="margin:0;font-size:12px;color:#bbb;text-align:center;">Если вы не запрашивали подтверждение — просто проигнорируйте это письмо</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

        $email = (new Email())
            ->from($this->mailerFrom)
            ->to($toEmail)
            ->subject('Подтверждение email — Мастер 12 Вольт')
            ->html($html);

        $this->mailer->send($email);
    }
}
