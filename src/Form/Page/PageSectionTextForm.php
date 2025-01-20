<?php

namespace App\Form\Page;

use App\Entity\Page\PageSectionText;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class PageSectionTextForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('content', TextType::class, [
                'empty_data' => '', // by explicitly setting this to an empty string we can also create page section text entities with empty content
                'constraints' => [
                    new Length([
                        'min' => 0,
                        'max' => PageSectionText::MAX_CONTENT_LENGTH,
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'sanitize_html' => false,
            'csrf_protection' => false,
            'data_class' => PageSectionText::class,
        ]);
    }
}
