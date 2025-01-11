<?php

namespace App\Form;

use App\Entity\PageSectionAIPrompt;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageSectionAIPromptForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        // can only set prompt when updating / creating a page section AI prompt
        $builder->add('prompt', options: [
            'required' => false,
            'empty_data' => null,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => PageSectionAIPrompt::class,
        ]);
    }
}
