<?php

declare(strict_types=1);

namespace App\Resume\Transport\Form;

use App\Resume\Domain\Entity\Skill;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @package App\Form
 */
class SkillType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'attr' => [
                    'placeholder' => 'Skill name...',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Languages' => 'Programmiersprachen',
                    'Frontend' => 'Frontend Technologien',
                    'Frameworks' => 'Frameworks',
                    'Cms' => 'Cms',
                    'Database' => 'Database',
                    'Agile' => 'Agile',
                    'Api' => 'Api Entwicklung',
                    'Tools' => 'Entwicklungstools',
                    'Security' => 'Sicherheit',
                    'Unit Testing' => 'Unit Testing',
                    'Architektur' => 'Architecture',
                    'Code Quality' => 'Qualität Code',
                ],
            ])
            ->add('level', RangeType::class, [
                'label' => 'Level',
                'attr' => [
                    'min' => 1,
                    'max' => 10,
                ],
            ])
        ;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Skill::class);
    }
}
