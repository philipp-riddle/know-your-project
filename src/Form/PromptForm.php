<?php

namespace App\Form;

use App\Entity\Prompt;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PromptForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        // can only set prompt when updating / creating a prompt
        $builder->add('promptText', options: [
            'required' => false,
            'empty_data' => null,
            'constraints' => [
                new Length(['max' => 255]),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => Prompt::class,
        ]);
    }
}
