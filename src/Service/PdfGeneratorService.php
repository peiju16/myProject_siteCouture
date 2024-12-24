<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

class PdfGeneratorService
{
    private Environment $twig;
    private Filesystem $filesystem;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
        $this->filesystem = new Filesystem();
    }

    public function generatePdf(
        string $template, 
        array $data, 
        string $outputDir, 
        string $fileName
    ): string {
        $options = new Options();
        $options->set(['defaultFont' => 'Arial', 'enable_remote' => true]);
        $dompdf = new Dompdf($options);

        // Dynamically render the provided template with data
        $html = $this->twig->render($template, $data);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Ensure the output directory exists
        if (!$this->filesystem->exists($outputDir)) {
            $this->filesystem->mkdir($outputDir, 0755);
        }

        // Save the PDF file to the specified directory
        $filePath = rtrim($outputDir, '/') . '/' . $fileName;
        file_put_contents($filePath, $dompdf->output());

        return $filePath;
    }
}
