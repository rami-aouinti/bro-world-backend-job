<?php

declare(strict_types=1);

namespace App\Resume\Transport\Form;

use App\Resume\Domain\Entity\Experience;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExperienceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('description', TextareaType::class)
            ->add('company', TextType::class)
            ->add('startedAt', DateType::class, [
                'label' => 'Start',
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
            ])
            ->add('endedAt', DateType::class, [
                'label' => 'End',
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Experience::class,
        ]);
    }
}
