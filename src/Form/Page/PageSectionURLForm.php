<?php

namespace App\Form\Page;

use App\Entity\Page\PageSectionURL;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class PageSectionURLForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('url', UrlType::class, [
                'default_protocol' => 'https',
                'constraints' => [
                    new Length([
                        'min' => 0,
                        'max' => 255,
                    ])
                ]
            ])
        ;

        if (null !== $builder->getData()) {
            $builder
                ->add('name', TextType::class, options: [
                    'empty_data' => '',
                    'required' => false,
                    'constraints' => [
                        new Length([
                            'min' => 0,
                            'max' => 255,
                        ])
                    ]
                ])
                ->add('description', TextType::class, options: [
                    'required' => false,
                    'constraints' => [
                        new Length([
                            'min' => 0,
                            'max' => 512,
                        ])
                    ]
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => PageSectionURL::class,
        ]);
    }
}
