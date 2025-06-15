<?php

declare(strict_types=1);

namespace App\Resume\Application;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;


/**
 * Class PdfGenerator
 *
 * @package App\Resume\Application
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class PdfGenerator
{
    private Environment $twig;
    private Dompdf $dompdf;
    private Filesystem $filesystem;
    private string $uploadDir;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', realpath(__DIR__.'/../../../public'));
        $this->dompdf = new Dompdf($options);
        $this->filesystem = new Filesystem();
        $this->uploadDir = __DIR__.'/../../../public/resume/pdf';
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function generatePdf(string $template, array $data, string $fileName): void
    {
        $html = $this->twig->render($template, $data);
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();

        $pdfContent = $this->dompdf->output();

        if (!$this->filesystem->exists($this->uploadDir)) {
            $this->filesystem->mkdir($this->uploadDir);
        }

        file_put_contents($this->uploadDir.'/'.$fileName, $pdfContent);
    }
}
