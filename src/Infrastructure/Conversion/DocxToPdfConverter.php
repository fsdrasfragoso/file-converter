<?php

namespace FragosoSoftware\FileConverter\Infrastructure\Conversion;

use PhpOffice\PhpWord\IOFactory;
use FragosoSoftware\FileConverter\Core\Conversion\AbstractConverter;
use PhpOffice\PhpWord\Settings;
use Dompdf\Dompdf;

/**
 * Converte arquivos DOCX para PDF usando LibreOffice (preferencial) ou PhpWord + Dompdf (fallback).
 * A classe detecta automaticamente a localização do LibreOffice em diferentes sistemas operacionais
 * e verifica se a função exec() está disponível antes de tentar usá-la.
 */
class DocxToPdfConverter extends AbstractConverter
{
    /**
     * {@inheritdoc}
     */
    public function convert(string $sourcePath, string $destinationPath): void
    {
        $this->ensureFileReadable($sourcePath);
        $this->ensureDirectoryWritable(dirname($destinationPath));

        if ($this->canUseLibreOffice()) {
            try {
                $this->convertWithLibreOffice($sourcePath, $destinationPath);
                return;
            } catch (\RuntimeException $e) {
                // Se a conversão com LibreOffice falhar, tenta fallback com PhpWord
                error_log('LibreOffice conversion failed, falling back to PhpWord: ' . $e->getMessage());
            }
        }

        $this->convertWithPhpWord($sourcePath, $destinationPath);
    }

    /**
     * Converte usando PhpWord + Dompdf (fallback).
     *
     * @param string $sourcePath
     * @param string $destinationPath
     * @throws \RuntimeException
     */
    private function convertWithPhpWord(string $sourcePath, string $destinationPath): void
    {
        try {
            // Configura o renderizador de PDF para Dompdf
            Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
            // Localiza o caminho do Dompdf (assumindo que está instalado via Composer)
            $dompdfReflection = new \ReflectionClass(Dompdf::class);
            $dompdfPath = dirname($dompdfReflection->getFileName(), 2); // sobe dois níveis para a raiz do pacote
            Settings::setPdfRendererPath($dompdfPath);

            // Carrega o documento DOCX (o PhpWord detecta automaticamente pelo formato)
            $phpWord = IOFactory::load($sourcePath);

            // Salva como PDF
            $writer = IOFactory::createWriter($phpWord, 'PDF');
            $writer->save($destinationPath);
        } catch (\Exception $e) {
            throw new \RuntimeException('PhpWord conversion failed: ' . $e->getMessage(), 0, $e);
        }
    }
}