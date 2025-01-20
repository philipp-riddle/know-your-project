<?php

namespace App\Form\Thread;

use App\Entity\Thread\Thread;
use App\Entity\Thread\ThreadItemComment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThreadItemCommentForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ?ThreadItemComment */
        $threadItemComment = $builder->getData();

        // comment can always be added, i.e. updated or created
        $builder->add('comment', options: [
            'required' => true,
        ]);

        if (null === $threadItemComment) {
            // we cannot map the ThreadItem directly here as it does not exist yet; it will be created with the new prompt
            $builder->add('thread', EntityType::class, [
                'class' => Thread::class,
                'mapped' => false,
                'required' => true,
            ]);

        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ThreadItemComment::class,
            'csrf_protection' => false,
        ]);
    }
}
