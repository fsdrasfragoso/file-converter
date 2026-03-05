<?php

namespace FragosoSoftware\FileConverter\Infrastructure\Conversion;

use FragosoSoftware\FileConverter\Core\Conversion\AbstractConverter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Converte arquivos XLSX para PDF usando PhpSpreadsheet + Dompdf.
 */
class XlsxToPdfConverter extends AbstractConverter
{
    /**
     * {@inheritdoc}
     */
    public function convert(string $sourcePath, string $destinationPath): void
    {
        $this->ensureFileReadable($sourcePath);
        $this->ensureDirectoryWritable(dirname($destinationPath));

        $spreadsheet = IOFactory::load($sourcePath);

        $html = $this->spreadsheetToHtml($spreadsheet);

        $dompdf = new Dompdf($this->getOptions());
        $dompdf->loadHtml($html);
        $dompdf->render();

        file_put_contents($destinationPath, $dompdf->output());
    }

    protected function spreadsheetToHtml($spreadsheet): string
    {
        $html = '<html><head><meta charset="UTF-8"><style>
        table{border-collapse:collapse;width:100%}
        td,th{border:1px solid #000;padding:4px;font-size:12px}
        h2{margin-top:20px}
        </style></head><body>';

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {

            $html .= '<h2>' . htmlspecialchars($sheet->getTitle()) . '</h2>';
            $html .= '<table>';

            foreach ($sheet->toArray() as $row) {
                $html .= '<tr>';

                foreach ($row as $cell) {
                    $html .= '<td>' . htmlspecialchars((string) $cell) . '</td>';
                }

                $html .= '</tr>';
            }

            $html .= '</table>';
        }

        $html .= '</body></html>';

        return $html;
    }

    protected function getOptions(): Options
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);

        return $options;
    }
}