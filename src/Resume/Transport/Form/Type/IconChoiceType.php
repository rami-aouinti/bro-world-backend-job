<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Rami Aouinti <rami.aouinti@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Resume\Transport\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the custom form field type used to manipulate tags values across
 * Bootstrap-tagsinput javascript plugin.
 *
 * See https://symfony.com/doc/current/form/create_custom_field_type.html
 *
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
final class IconChoiceType extends AbstractType
{
    /**
     * Cache for multiple icon fields or sub-requests.
     */
    private array $choices;

    private $kernelRootDir;

    public function __construct($kernelRootDir)
    {
        $this->kernelRootDir = $kernelRootDir;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        // Pass this flag is necessary to render the label as raw.
        // See below the twig field template for more details.
        $view->vars['raw_label'] = true;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => [
                'style' => "font-family: 'FontAwesome';",
            ],
            'choices' => $this->getFontAwesomeIconChoices(),
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    protected function getFontAwesomeIconChoices(): array
    {
        if ($this->choices !== null) {
            // don't to load again for optimal performance.
            // useful for multi-icon fields and sub-requests.
            return $this->choices;
        }

        // BTW we could configure the path to the "font-awesome.css".
        $fontAwesome = file_get_contents($this->kernelRootDir . '/public/portfolio/css/font-awesome.css');

        // this regular expression only works with uncompressed version (not works with "font-awesome.min.css")
        $pattern = '/\.(fa-(?:\w+(?:-)?)+):before\s+{\s*content:\s*"\\\\(.+)";\s+}/';

        if (preg_match_all($pattern, $fontAwesome, $matches, PREG_SET_ORDER)) {
            foreach ($matches as [, $class, $code]) {
                // this may vary depending on the version of Symfony,
                // if the class name is displayed instead of the icon then swap the key/value
                $this->choices['&#x' . $code . ';'] = $class;
            }
        }

        return $this->choices;
    }
}
