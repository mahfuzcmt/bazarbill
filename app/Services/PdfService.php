<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\Response;

/**
 * Renders Blade views to PDF with mPDF.
 *
 * mPDF applies OpenType shaping, so Bengali conjuncts and reordered vowel
 * signs render correctly. DomPDF has no complex-script support at all.
 */
class PdfService
{
    private function __construct(private readonly string $html)
    {
    }

    public static function fromView(string $view, array $data = []): self
    {
        return new self(view($view, $data)->render());
    }

    public function download(string $filename): Response
    {
        return $this->respond($filename, 'attachment');
    }

    public function stream(string $filename): Response
    {
        return $this->respond($filename, 'inline');
    }

    public function output(): string
    {
        $mpdf = $this->makeRenderer();
        $mpdf->WriteHTML($this->html);

        return $mpdf->Output('', Destination::STRING_RETURN);
    }

    private function respond(string $filename, string $disposition): Response
    {
        return response($this->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
        ]);
    }

    private function makeRenderer(): Mpdf
    {
        $tempDir = storage_path('app/mpdf');
        File::ensureDirectoryExists($tempDir);

        $fontDirs = (new ConfigVariables())->getDefaults()['fontDir'];
        $fontData = (new FontVariables())->getDefaults()['fontdata'];

        return new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'tempDir' => $tempDir,
            'fontDir' => array_merge($fontDirs, [public_path('fonts')]),
            'fontdata' => $fontData + [
                'hindsiliguri' => [
                    'R' => 'HindSiliguri-Regular.ttf',
                    'B' => 'HindSiliguri-Bold.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ],
            ],
            'default_font' => 'hindsiliguri',
            'autoScriptToLang' => true,
            'autoLangToFont' => false,
        ]);
    }
}
