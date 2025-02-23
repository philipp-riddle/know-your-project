<?php

namespace App\Form\Page;

use App\Entity\Calendar\CalendarEvent;
use App\Entity\Page\PageSectionCalendarEvent;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageSectionCalendarEventForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->add('calendarEvent', EntityType::class, [
            'class' => CalendarEvent::class,
            'choice_label' => 'id',
            'required' => false,
            'empty_data' => null,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => PageSectionCalendarEvent::class,
        ]);
    }
}
