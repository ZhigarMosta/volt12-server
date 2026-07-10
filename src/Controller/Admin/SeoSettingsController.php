<?php

namespace App\Controller\Admin;

use App\Entity\SiteSetting;
use App\Service\SiteSettingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Length;

#[Route('/admin/seo')]
class SeoSettingsController extends AbstractController
{
    public function __construct(
        private SiteSettingService $settings,
        private string $frontendUrl,
    ) {}

    #[Route('/robots-txt', name: 'app_admin_robots_txt', methods: ['GET', 'POST'])]
    public function robotsTxt(Request $request): Response
    {
        $form = $this->createFormBuilder(['content' => $this->settings->getRobotsTxt()])
            ->add('content', TextareaType::class, [
                'label' => false,
                'required' => false,
                'constraints' => [
                    new Length(['max' => 20000, 'maxMessage' => 'Слишком длинный robots.txt (макс. {{ limit }} символов).']),
                ],
                'attr' => [
                    'rows' => 18,
                    'spellcheck' => 'false',
                    'class' => 'form-control font-monospace',
                    'data-role' => 'robots-editor',
                    'style' => 'resize: vertical; min-height: 320px; font-size: 14px; line-height: 1.6;',
                ],
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $content = (string) $form->get('content')->getData();
            $this->settings->set(SiteSetting::ROBOTS_TXT, $content);

            // «Disallow: /» без пути закрывает от роботов весь сайт — сохранение не блокируем, но предупреждаем
            if (preg_match('~^\s*Disallow:\s*/\s*$~mi', $content)) {
                $this->addFlash('error', 'robots.txt сохранён, но в нём есть «Disallow: /» — это закрывает от поисковиков ВЕСЬ сайт. Убедитесь, что так и задумано.');
            } else {
                $this->addFlash('success', 'robots.txt сохранён — на сайте обновится в течение ~5 минут.');
            }

            return $this->redirectToRoute('app_admin_robots_txt');
        }

        return $this->render('admin/seo/robots_txt.html.twig', [
            'form' => $form->createView(),
            'updatedAt' => $this->settings->getUpdatedAt(SiteSetting::ROBOTS_TXT),
            'frontendUrl' => rtrim($this->frontendUrl, '/'),
            'defaultContent' => SiteSettingService::DEFAULT_ROBOTS_TXT,
        ]);
    }
}
