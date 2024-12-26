<?php

namespace App\Form;

use App\Entity\PageSection;
use App\Entity\PageTab;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PageSectionUploadForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('file', FileType::class, [
            'constraints' => [
                new File([
                    'maxSize' => '1024k',
                    'mimeTypes' => [
                        'application/pdf',
                        'application/x-pdf',
                        'image/*',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid format (PDF, JPG, PNG, GIF)',
                ])
            ],
            'required' => false,
            'mapped' => false,
        ]);

        $isNewObject = null === $builder->getData();

        if ($isNewObject) {
            $builder->add('pageTab', EntityType::class, [
                'class' => PageTab::class,
                'choice_label' => 'id',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PageSection::class,
            'csrf_protection' => false,
        ]);
    }
}
