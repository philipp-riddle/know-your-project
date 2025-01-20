<?php

namespace App\Form\Page;

use App\Entity\PageSectionAIPrompt;
use App\Form\PromptForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageSectionAIPromptForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        /**
         * @var ?PageSectionAIPrompt
         */
        $pageSectionAIPrompt = $builder->getData();

        // can only set prompt
        $builder->add('prompt', PromptForm::class, [
            'required' => null === $pageSectionAIPrompt,
            'data' => $pageSectionAIPrompt?->getPrompt(),
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
