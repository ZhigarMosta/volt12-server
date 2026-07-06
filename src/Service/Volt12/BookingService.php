<?php

namespace App\Service\Volt12;

use App\Exception\EmailLimitExceededException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class BookingService
{
    public const TYPE_ALARM_SELECTION = 'alarm_selection';
    public const TYPE_ALARM_INSTALLATION = 'alarm_installation';

    public const ALL_TYPES = [
        self::TYPE_ALARM_SELECTION,
        self::TYPE_ALARM_INSTALLATION,
    ];

    public const TYPE_LABELS = [
        self::TYPE_ALARM_SELECTION => 'Бесплатный подбор сигнализации',
        self::TYPE_ALARM_INSTALLATION => 'Установка сигнализации',
    ];

    public function __construct(
        private MailerInterface $mailer,
        private string $mailerFrom,
        private RateLimiterFactory $outgoingEmailLimiter
    )
    {
    }

    private function assertOutgoingEmailAllowed(): void
    {
        if (!$this->outgoingEmailLimiter->create('global')->consume(1)->isAccepted()) {
            throw new EmailLimitExceededException('Достигнут дневной лимит отправки писем. Попробуйте позже.');
        }
    }

    public function send(string $type, string $userName, string $userPhone, string $userEmail, string $message): void
    {
        $this->assertOutgoingEmailAllowed();

        [$subject, $html] = match ($type) {
            self::TYPE_ALARM_SELECTION => [
                'Заявка на бесплатный подбор сигнализации принята — Мастер 12 Вольт',
                $this->buildAlarmSelectionClientHtml($userName, $userPhone),
            ],
            self::TYPE_ALARM_INSTALLATION => [
                'Заявка на установку сигнализации принята — Мастер 12 Вольт',
                $this->buildAlarmInstallationClientHtml($userName, $userPhone),
            ],
        };

        $clientEmail = (new Email())
            ->from($this->mailerFrom)
            ->to($userEmail)
            ->subject($subject)
            ->html($html);

        $this->mailer->send($clientEmail);

        $adminEmail = (new Email())
            ->from($this->mailerFrom)
            ->to($this->mailerFrom)
            ->subject('Новая заявка: ' . self::TYPE_LABELS[$type] . ' — Мастер 12 Вольт')
            ->html($this->buildAdminNotificationHtml($type, $userName, $userPhone, $userEmail, $message));

        $this->mailer->send($adminEmail);
    }

    private function buildAlarmSelectionClientHtml(string $userName, string $userPhone): string
    {
        $escapedName = htmlspecialchars($userName, ENT_QUOTES);
        $escapedPhone = htmlspecialchars($userPhone, ENT_QUOTES);

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
          <tr>
            <td style="background:#e63535;padding:28px 36px;">
              <p style="margin:0;color:#ffffff;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;">Мастер 12 Вольт</p>
              <h1 style="margin:6px 0 0;color:#ffffff;font-size:22px;font-weight:700;">Заявка на бесплатный подбор сигнализации принята</h1>
            </td>
          </tr>
          <tr>
            <td style="padding:32px 36px;">
              <p style="margin:0 0 12px;font-size:15px;color:#333;line-height:1.6;">
                Здравствуйте, $escapedName!
              </p>
              <p style="margin:0 0 24px;font-size:15px;color:#333;line-height:1.6;">
                Ваша заявка на бесплатный подбор сигнализации к автомобилю обрабатывается в ближайшее время.
                Наш специалист свяжется с вами по номеру
                <a href="tel:$escapedPhone" style="color:#e63535;text-decoration:none;font-weight:600;">$escapedPhone</a>,
                чтобы подобрать оптимальный вариант сигнализации под ваш автомобиль.
              </p>
              <p style="margin:0;font-size:13px;color:#999;line-height:1.6;">
                Если у вас появятся вопросы до звонка, вы всегда можете написать нам в ответ на это письмо.
              </p>
            </td>
          </tr>
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

    private function buildAlarmInstallationClientHtml(string $userName, string $userPhone): string
    {
        $escapedName = htmlspecialchars($userName, ENT_QUOTES);
        $escapedPhone = htmlspecialchars($userPhone, ENT_QUOTES);

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
          <tr>
            <td style="background:#e63535;padding:28px 36px;">
              <p style="margin:0;color:#ffffff;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;">Мастер 12 Вольт</p>
              <h1 style="margin:6px 0 0;color:#ffffff;font-size:22px;font-weight:700;">Заявка на установку сигнализации принята</h1>
            </td>
          </tr>
          <tr>
            <td style="padding:32px 36px;">
              <p style="margin:0 0 12px;font-size:15px;color:#333;line-height:1.6;">
                Здравствуйте, $escapedName!
              </p>
              <p style="margin:0 0 24px;font-size:15px;color:#333;line-height:1.6;">
                Ваша заявка на установку сигнализации обрабатывается в ближайшее время.
                Наш специалист свяжется с вами по номеру
                <a href="tel:$escapedPhone" style="color:#e63535;text-decoration:none;font-weight:600;">$escapedPhone</a>,
                чтобы согласовать удобное время установки.
              </p>
              <p style="margin:0;font-size:13px;color:#999;line-height:1.6;">
                Если у вас появятся вопросы до звонка, вы всегда можете написать нам в ответ на это письмо.
              </p>
            </td>
          </tr>
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

    private function buildAdminNotificationHtml(string $type, string $userName, string $userPhone, string $userEmail, string $message): string
    {
        $escapedType = htmlspecialchars(self::TYPE_LABELS[$type], ENT_QUOTES);
        $escapedName = htmlspecialchars($userName, ENT_QUOTES);
        $escapedPhone = htmlspecialchars($userPhone, ENT_QUOTES);
        $escapedEmail = htmlspecialchars($userEmail, ENT_QUOTES);
        $escapedMessage = $message !== '' ? nl2br(htmlspecialchars($message, ENT_QUOTES)) : '—';

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
          <tr>
            <td style="background:#e63535;padding:28px 36px;">
              <p style="margin:0;color:#ffffff;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;">Мастер 12 Вольт</p>
              <h1 style="margin:6px 0 0;color:#ffffff;font-size:22px;font-weight:700;">Новая заявка: $escapedType</h1>
            </td>
          </tr>
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
                    <p style="margin:0;font-size:15px;color:#333;line-height:1.6;background:#f8f8f8;border-radius:8px;padding:16px;">$escapedMessage</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
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
}
