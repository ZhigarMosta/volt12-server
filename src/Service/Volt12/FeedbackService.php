<?php

namespace App\Service\Volt12;

use App\Exception\EmailLimitExceededException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class FeedbackService
{
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

    public function send(string $type, string $userName, string $userPhone, string $userEmail, string $description): void
    {
        $this->assertOutgoingEmailAllowed();

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

    public function sendEmailVerification(string $toEmail, string $code): void
    {
        $this->assertOutgoingEmailAllowed();

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
                Ваш код для подтверждения email:
              </p>
              <p style="margin:0 0 24px;font-size:40px;font-weight:700;color:#e63535;letter-spacing:10px;text-align:center;">$code</p>
              <p style="margin:0;font-size:13px;color:#999;line-height:1.6;">
                Введите этот код на сайте, чтобы подтвердить почту. Если вы не запрашивали подтверждение — проигнорируйте это письмо.
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

        $email = (new Email())
            ->from($this->mailerFrom)
            ->to($toEmail)
            ->subject('Код подтверждения email — Мастер 12 Вольт')
            ->html($html);

        $this->mailer->send($email);
    }

    public function sendPasswordResetCode(string $toEmail, string $code): void
    {
        $this->assertOutgoingEmailAllowed();

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
              <h1 style="margin:6px 0 0;color:#ffffff;font-size:22px;font-weight:700;">Восстановление пароля</h1>
            </td>
          </tr>
          <tr>
            <td style="padding:32px 36px;">
              <p style="margin:0 0 24px;font-size:15px;color:#333;line-height:1.6;">
                Ваш код для сброса пароля:
              </p>
              <p style="margin:0 0 24px;font-size:48px;font-weight:700;color:#e63535;letter-spacing:12px;text-align:center;">$code</p>
              <p style="margin:0;font-size:13px;color:#999;line-height:1.6;">
                Код действителен в течение 15 минут. Если вы не запрашивали сброс пароля — проигнорируйте это письмо.
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

        $email = (new Email())
            ->from($this->mailerFrom)
            ->to($toEmail)
            ->subject('Код сброса пароля — Мастер 12 Вольт')
            ->html($html);

        $this->mailer->send($email);
    }

    public function sendPasswordChangeCode(string $toEmail, string $code): void
    {
        $this->assertOutgoingEmailAllowed();

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
              <h1 style="margin:6px 0 0;color:#ffffff;font-size:22px;font-weight:700;">Смена пароля</h1>
            </td>
          </tr>
          <tr>
            <td style="padding:32px 36px;">
              <p style="margin:0 0 24px;font-size:15px;color:#333;line-height:1.6;">
                Ваш код для смены пароля:
              </p>
              <p style="margin:0 0 24px;font-size:48px;font-weight:700;color:#e63535;letter-spacing:12px;text-align:center;">$code</p>
              <p style="margin:0;font-size:13px;color:#999;line-height:1.6;">
                Код действителен в течение 15 минут. Если вы не запрашивали смену пароля — проигнорируйте это письмо.
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

        $email = (new Email())
            ->from($this->mailerFrom)
            ->to($toEmail)
            ->subject('Код смены пароля — Мастер 12 Вольт')
            ->html($html);

        $this->mailer->send($email);
    }

    public function sendOrderConfirmation(string $toEmail, array $orderData): void
    {
        $this->assertOutgoingEmailAllowed();

        $html = $this->buildOrderHtml($orderData);

        foreach ([$toEmail, $this->mailerFrom] as $recipient) {
            $email = (new Email())
                ->from($this->mailerFrom)
                ->to($recipient)
                ->subject('Заказ №' . $orderData['id'] . ' — Мастер 12 Вольт')
                ->html($html);

            $this->mailer->send($email);
        }
    }

    private function buildOrderHtml(array $order): string
    {
        $id        = htmlspecialchars((string) $order['id'], ENT_QUOTES);
        $firstName = htmlspecialchars($order['first_name'], ENT_QUOTES);
        $lastName  = htmlspecialchars($order['last_name'], ENT_QUOTES);
        $phone     = htmlspecialchars($order['phone'], ENT_QUOTES);
        $email     = htmlspecialchars($order['email'], ENT_QUOTES);
        $city      = htmlspecialchars($order['city'], ENT_QUOTES);
        $region    = htmlspecialchars($order['region'], ENT_QUOTES);
        $postal    = htmlspecialchars($order['postal_code'], ENT_QUOTES);
        $address   = htmlspecialchars(implode(', ', array_filter([
            $order['street'] ?? null,
            $order['house'] ? 'д. ' . $order['house'] : null,
            $order['entrance'] ? 'подъезд ' . $order['entrance'] : null,
            $order['apartment'] ? 'кв. ' . $order['apartment'] : null,
        ])), ENT_QUOTES);
        $comment   = $order['comment'] ? htmlspecialchars($order['comment'], ENT_QUOTES) : '—';
        $total     = number_format($order['total_price'] / 100, 2, '.', ' ') . ' ₽';

        $itemsHtml = '';
        foreach ($order['items'] as $item) {
            $name     = htmlspecialchars($item['name'], ENT_QUOTES);
            $qty      = (int) $item['quantity'];
            $price    = number_format($item['price'] / 100, 2, '.', ' ');
            $itemTotal = number_format($item['total_price'] / 100, 2, '.', ' ');
            $itemsHtml .= "
                <tr>
                  <td style='padding:10px 0;border-bottom:1px solid #f0f0f0;font-size:14px;color:#333;'>$name</td>
                  <td style='padding:10px 8px;border-bottom:1px solid #f0f0f0;font-size:14px;color:#333;text-align:center;'>$qty</td>
                  <td style='padding:10px 0;border-bottom:1px solid #f0f0f0;font-size:14px;color:#333;text-align:right;'>$price ₽</td>
                  <td style='padding:10px 0 10px 16px;border-bottom:1px solid #f0f0f0;font-size:14px;font-weight:600;color:#1a1a1a;text-align:right;'>$itemTotal ₽</td>
                </tr>";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:40px 0;">
    <tr><td align="center">
      <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">

        <tr>
          <td style="background:#e63535;padding:28px 36px;">
            <p style="margin:0;color:#ffffff;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;">Мастер 12 Вольт</p>
            <h1 style="margin:6px 0 0;color:#ffffff;font-size:22px;font-weight:700;">Заказ №$id оформлен</h1>
          </td>
        </tr>

        <tr>
          <td style="padding:32px 36px 0;">
            <p style="margin:0 0 4px;font-size:11px;color:#999;text-transform:uppercase;letter-spacing:1px;">Покупатель</p>
            <p style="margin:0 0 20px;font-size:16px;font-weight:600;color:#1a1a1a;">$firstName $lastName</p>

            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="padding-bottom:12px;width:50%;vertical-align:top;">
                  <p style="margin:0 0 2px;font-size:11px;color:#999;text-transform:uppercase;letter-spacing:1px;">Телефон</p>
                  <p style="margin:0;font-size:14px;color:#333;"><a href="tel:$phone" style="color:#e63535;text-decoration:none;">$phone</a></p>
                </td>
                <td style="padding-bottom:12px;vertical-align:top;">
                  <p style="margin:0 0 2px;font-size:11px;color:#999;text-transform:uppercase;letter-spacing:1px;">Email</p>
                  <p style="margin:0;font-size:14px;color:#333;"><a href="mailto:$email" style="color:#e63535;text-decoration:none;">$email</a></p>
                </td>
              </tr>
              <tr>
                <td style="padding-bottom:12px;vertical-align:top;">
                  <p style="margin:0 0 2px;font-size:11px;color:#999;text-transform:uppercase;letter-spacing:1px;">Город / Регион</p>
                  <p style="margin:0;font-size:14px;color:#333;">$city, $region, $postal</p>
                </td>
                <td style="padding-bottom:12px;vertical-align:top;">
                  <p style="margin:0 0 2px;font-size:11px;color:#999;text-transform:uppercase;letter-spacing:1px;">Адрес</p>
                  <p style="margin:0;font-size:14px;color:#333;">$address</p>
                </td>
              </tr>
              <tr>
                <td colspan="2" style="padding-bottom:24px;">
                  <p style="margin:0 0 2px;font-size:11px;color:#999;text-transform:uppercase;letter-spacing:1px;">Комментарий</p>
                  <p style="margin:0;font-size:14px;color:#333;">$comment</p>
                </td>
              </tr>
            </table>

            <p style="margin:0 0 12px;font-size:11px;color:#999;text-transform:uppercase;letter-spacing:1px;">Состав заказа</p>
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <th style="padding-bottom:8px;font-size:11px;color:#999;font-weight:600;text-align:left;">Товар</th>
                <th style="padding-bottom:8px;font-size:11px;color:#999;font-weight:600;text-align:center;">Кол-во</th>
                <th style="padding-bottom:8px;font-size:11px;color:#999;font-weight:600;text-align:right;">Цена</th>
                <th style="padding-bottom:8px;font-size:11px;color:#999;font-weight:600;text-align:right;padding-left:16px;">Сумма</th>
              </tr>
              $itemsHtml
            </table>
          </td>
        </tr>

        <tr>
          <td style="padding:20px 36px;">
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="font-size:16px;font-weight:700;color:#1a1a1a;">Итого</td>
                <td style="font-size:20px;font-weight:700;color:#e63535;text-align:right;">$total</td>
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
    </td></tr>
  </table>
</body>
</html>
HTML;
    }
}
