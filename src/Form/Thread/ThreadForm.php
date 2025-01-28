<?php

namespace App\Form\Thread;

use App\Entity\Page\PageSection;
use App\Entity\Thread\Thread;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThreadForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ?Thread */
        $thread = $builder->getData();

        // if the thread is new we need to specify the page section as a start
        if (null === $thread) {
            // we cannot map the needed entity 'pageSectionContext' directly as it has to be created first. we can do this in the ThreadApiController
            $builder->add('pageSection', EntityType::class, [
                'class' => PageSection::class,
                'choice_label' => 'id',
                'mapped' => false,
                'required' => true,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Thread::class,
            'csrf_protection' => false,
        ]);
    }
}
