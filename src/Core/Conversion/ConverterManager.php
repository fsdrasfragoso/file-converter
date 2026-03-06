<?php

namespace FragosoSoftware\FileConverter\Core\Conversion;

use FragosoSoftware\FileConverter\Contracts\Conversion\ConverterInterface;
use FragosoSoftware\FileConverter\Infrastructure\Conversion\DocxToPdfConverter;
use FragosoSoftware\FileConverter\Infrastructure\Conversion\RtfToPdfConverter;
use FragosoSoftware\FileConverter\Infrastructure\Conversion\TxtToPdfConverter;
use FragosoSoftware\FileConverter\Infrastructure\Conversion\XlsxToPdfConverter;
use FragosoSoftware\FileConverter\Infrastructure\Conversion\OdtToPdfConverter; 
use FragosoSoftware\FileConverter\Infrastructure\Conversion\PptxToPdfConverter;
use FragosoSoftware\FileConverter\Infrastructure\Conversion\MarkdownToPdfConverter;
use FragosoSoftware\FileConverter\Infrastructure\Conversion\ImageToPdfConverter;
use FragosoSoftware\FileConverter\Infrastructure\Conversion\CsvToPdfConverter;
use FragosoSoftware\FileConverter\Infrastructure\Conversion\HtmlToPdfConverter;
use FragosoSoftware\FileConverter\Infrastructure\Conversion\CsvToJsonConverter;

use Symfony\Component\Process\Exception\LogicException;

class ConverterManager
{
    protected ConversionRegistry $registry;

    public function __construct()
    {
        $this->registry = new ConversionRegistry();

        $this->registerDefaultConverters();
    }

    protected function registerDefaultConverters(): void
    {
        $this->registry->register('docx', 'pdf', DocxToPdfConverter::class);
        $this->registry->register('doc', 'pdf', DocxToPdfConverter::class);
        $this->registry->register('dot', 'pdf', DocxToPdfConverter::class);
        $this->registry->register('dotx', 'pdf', DocxToPdfConverter::class);
        $this->registry->register('rtf', 'pdf', RtfToPdfConverter::class);
        $this->registry->register('txt', 'pdf', TxtToPdfConverter::class);
        $this->registry->register('xlsx', 'pdf', XlsxToPdfConverter::class);
        $this->registry->register('xls', 'pdf', XlsxToPdfConverter::class);
        $this->registry->register('odt', 'pdf', OdtToPdfConverter::class);
        $this->registry->register('ods', 'pdf', XlsxToPdfConverter::class);
        $this->registry->register('pptx', 'pdf', PptxToPdfConverter::class);
        $this->registry->register('ppt', 'pdf', PptxToPdfConverter::class);
        $this->registry->register('odp', 'pdf', PptxToPdfConverter::class);
        $this->registry->register('md', 'pdf', MarkdownToPdfConverter::class);
        $this->registry->register('markdown', 'pdf', MarkdownToPdfConverter::class);
        $this->registry->register('jpg', 'pdf', ImageToPdfConverter::class);
        $this->registry->register('jpeg', 'pdf', ImageToPdfConverter::class);
        $this->registry->register('png', 'pdf', ImageToPdfConverter::class);
        $this->registry->register('gif', 'pdf', ImageToPdfConverter::class);
        $this->registry->register('bmp', 'pdf', ImageToPdfConverter::class);
        $this->registry->register('webp', 'pdf', ImageToPdfConverter::class);
        $this->registry->register('tiff', 'pdf', ImageToPdfConverter::class);
        $this->registry->register('tif', 'pdf', ImageToPdfConverter::class);
        $this->registry->register('csv', 'pdf', CsvToPdfConverter::class);
        $this->registry->register('html', 'pdf', HtmlToPdfConverter::class);
        $this->registry->register('htm', 'pdf', HtmlToPdfConverter::class); 
        $this->registry->register('csv', 'json', CsvToJsonConverter::class); 
        
    }

    public function convert(string $source, string $destination): void
    {
        $from = pathinfo($source, PATHINFO_EXTENSION);
        $to   = pathinfo($destination, PATHINFO_EXTENSION);

         try {
            $converterClass = $this->registry->resolve($from, $to);
        } catch (LogicException  $e) {
            throw new LogicException("Não é possível converter de $from para $to.", 0, $e);
        }  

        $converter = new $converterClass($source, $destination);

        if (!$converter instanceof ConverterInterface) {
            throw new LogicException("Conversor inválido.");
        }

        $converter->convert($source, $destination);
    }

    public function convertBinary(string $binary, string $from, string $to): string
    {
        $converterClass = $this->registry->resolve($from, $to);
    
        $converter = new $converterClass();
    
        return $converter->convertFromBinary($binary);
    }
    
    public function convertBase64(string $base64, string $from, string $to): string
    {
        $converterClass = $this->registry->resolve($from, $to);
    
        $converter = new $converterClass();
    
        return $converter->convertFromBase64($base64);
    }
}