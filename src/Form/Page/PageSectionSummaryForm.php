<?php

namespace App\Form\Page;

use App\Entity\Page\PageSectionSummary;
use App\Form\PromptForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageSectionSummaryForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        /**
         * @var ?PageSectionSummary
         */
        $pageSectionSummary = $builder->getData();

        // can only set prompt
        $builder->add('prompt', PromptForm::class, [
            'required' => null === $pageSectionSummary,
            'data' => $pageSectionSummary?->getPrompt(),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'sanitize_html' => false,
            'csrf_protection' => false,
            'data_class' => PageSectionSummary::class,
        ]);
    }
}
