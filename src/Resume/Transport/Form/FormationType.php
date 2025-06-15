<?php

declare(strict_types=1);

namespace App\Resume\Transport\Form;

use App\Resume\Domain\Entity\Formation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @package App\Form
 */
class FormationType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la formation',
                'attr' => [
                    'placeholder' => 'Entrez le nom de votre formation...',
                ],
            ])
            ->add('school', TextType::class, [
                'label' => 'University',
                'attr' => [
                    'placeholder' => 'Name of university...',
                ],
            ])
            ->add('gradeLevel', ChoiceType::class, [
                'label' => 'Degree',
                'choices' => [
                    'High School' => 0,
                    "Bachelor's Degree" => 1,
                    "Master's Degree" => 2,
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description de la formation',
                'attr' => [
                    'placeholder' => 'Entrez la description de votre formation...',
                ],
            ])
            ->add('startedAt', DateType::class, [
                'label' => 'Début de la formation',
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
            ])
            ->add('endedAt', DateType::class, [
                'label' => 'Fin de la formation',
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
                'required' => false,
            ])
        ;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Formation::class);
    }
}
