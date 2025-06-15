<?php

declare(strict_types=1);

namespace App\Resume\Transport\Form;

use App\Resume\Domain\Entity\Language;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LanguageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('flag', TextType::class)
            ->add('level', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'A1' => 1,
                    'A2' => 2,
                    'B1' => 3,
                    'B2' => 4,
                    'C1' => 5,
                    'C2' => 6,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Language::class,
        ]);
    }
}
