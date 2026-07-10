<?php

namespace App\Form\Type;

use App\Entity\SeoMetadata;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Url;

class SeoMetadataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('metaTitle', TextType::class, [
                'label' => 'Meta Title',
                'required' => false,
                'constraints' => [
                    new Length(['max' => 255]),
                ],
                'help' => 'Заголовок в поисковой выдаче, ~50–60 символов. Если пусто — используется название.',
            ])
            ->add('metaDescription', TextareaType::class, [
                'label' => 'Meta Description',
                'required' => false,
                'help' => 'Описание сниппета в выдаче, ~150–160 символов. Если пусто — используется краткое описание.',
                'attr' => ['rows' => 3],
            ])
            ->add('metaKeywords', TextType::class, [
                'label' => 'Meta Keywords',
                'required' => false,
                'constraints' => [
                    new Length(['max' => 255]),
                ],
                'help' => 'Ключевые слова через запятую. Поисковиками практически не учитываются.',
            ])
            ->add('noindex', CheckboxType::class, [
                'label' => 'Noindex — скрыть страницу из поисковой выдачи',
                'required' => false,
            ])
            ->add('nofollow', CheckboxType::class, [
                'label' => 'Nofollow — не передавать вес по ссылкам со страницы',
                'required' => false,
            ])
            ->add('canonicalUrl', TextType::class, [
                'label' => 'Canonical URL',
                'required' => false,
                'constraints' => [
                    new Length(['max' => 512]),
                    new Url([
                        'requireTld' => true,
                        'message' => 'Укажите абсолютный URL, например https://site.ru/catalog/akkumulyatory',
                    ]),
                ],
                'help' => 'Абсолютный URL канонической версии страницы. Если пусто — генерируется автоматически.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SeoMetadata::class,
        ]);
    }
}
