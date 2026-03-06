# File Converter

Biblioteca PHP para conversão e manipulação de arquivos entre diferentes formatos, compatível com Laravel 7+ e PHP 7.3+.  

Desenvolvida com Arquitetura Hexagonal (Ports & Adapters) / Clean Architecture, permite extensibilidade por meio de drivers plugáveis e gerenciamento inteligente de dependências do sistema operacional.

---

## 🚀 Características

- ✅ Compatível com PHP 7.3+
- ✅ Compatível com Laravel 7+
- ✅ Arquitetura Hexagonal / Clean
- ✅ Suporte multiplataforma (Linux, macOS, Windows)
- ✅ Detecção automática de sistema operacional
- ✅ Suporte a múltiplos gerenciadores de pacotes:
  - apt
  - dnf
  - yum
  - apk
  - brew
  - choco
- ✅ Sistema de instalação automática de dependências
- ✅ Estrutura preparada para drivers extensíveis
- ✅ API fluente e desacoplada de framework

---

## 📦 Instalação

Via Composer:

```bash
composer require fragosoftware/file-converter
```
A biblioteca funciona como um Conversion Kernel, semelhante ao Kernel do Laravel.

Você apenas chama:

```php

use FragosoSoftware\FileConverter\Core\Conversion\ConverterManager;

$manager = new ConverterManager();

$manager->convert('arquivo.docx', 'arquivo.pdf');
```
## 📦 Conversão usando Binário

Você pode converter diretamente o conteúdo binário do arquivo sem precisar salvar em disco.

```php
use FragosoSoftware\FileConverter\Core\Conversion\ConverterManager;

$manager = new ConverterManager();

// Conteúdo binário do arquivo (ex: vindo do Storage, upload, etc)
$binary = file_get_contents('arquivo.docx');

$pdfBinary = $manager->convertBinary($binary, 'docx', 'pdf');

// Agora você pode salvar
file_put_contents('arquivo.pdf', $pdfBinary);
```

## 🔹 Exemplo: RTF → PDF (binário)
```php
$binary = file_get_contents('arquivo.rtf');

$pdfBinary = $manager->convertBinary($binary, 'rtf', 'pdf');

file_put_contents('arquivo.pdf', $pdfBinary);
```

## 📦 Conversão usando Base64

Ideal para: APIs, Microserviços, Comunicação HTTP, Upload via JSON

## 🔹 Exemplo: DOCX → PDF (base64)

```php
use FragosoSoftware\FileConverter\Core\Conversion\ConverterManager;

$manager = new ConverterManager();

// Base64 do arquivo
$base64 = base64_encode(file_get_contents('arquivo.docx'));

$pdfBase64 = $manager->convertBase64($base64, 'docx', 'pdf');

// Salvar como arquivo
file_put_contents(
    'arquivo.pdf',
    base64_decode($pdfBase64)
);
```

---

## 📄 Formatos Suportados

A biblioteca suporta conversão de diversos formatos de documentos para PDF utilizando diferentes engines especializadas (LibreOffice, PhpOffice, Dompdf, etc).

### Documentos
| Formato | Conversão |
|--------|-----------|
| DOC | → PDF |
| DOCX | → PDF |
| DOT | → PDF |
| DOTX | → PDF |
| RTF | → PDF |
| ODT | → PDF |

### Apresentações
| Formato | Conversão |
|--------|-----------|
| PPT | → PDF |
| PPTX | → PDF |
| ODP | → PDF |

### Planilhas
| Formato | Conversão |
|--------|-----------|
| XLS | → PDF |
| XLSX | → PDF |
| CSV | → PDF |

### Texto
| Formato | Conversão |
|--------|-----------|
| TXT | → PDF |

### Markdown
| Formato | Conversão |
|--------|-----------|
| MD | → PDF |
| MARKDOWN | → PDF |

### Imagens
| Formato | Conversão |
|--------|-----------|
| JPG | → PDF |
| JPEG | → PDF |
| PNG | → PDF |
| GIF | → PDF |
| BMP | → PDF |
| WEBP | → PDF |
| TIFF | → PDF |
| TIF | → PDF |

---

## ⚙️ Engines de Conversão

A biblioteca utiliza diferentes engines dependendo do tipo de arquivo para garantir melhor compatibilidade e performance.

| Engine | Utilizado para |
|------|----------------|
LibreOffice | DOC, DOCX, DOT, DOTX, ODT, PPT, PPTX, ODP |
PhpSpreadsheet | XLS, XLSX |
PhpWord | fallback para documentos Word |
CommonMark | Markdown → HTML |
Dompdf | HTML → PDF |
Spatie Image | Imagens → PDF |

Sempre que possível a biblioteca utiliza uma **engine principal** e mantém um **fallback automático** para aumentar a confiabilidade da conversão.

## Dependências do Sistema

Algumas conversões utilizam ferramentas externas para melhor compatibilidade e desempenho, como LibreOffice e ImageMagick.

Dependendo do formato do arquivo, estas ferramentas podem ser necessárias.

### Ferramentas utilizadas

| Ferramenta             | Utilização                                                     |
| ---------------------- | -------------------------------------------------------------- |
| LibreOffice            | Conversão de documentos Office (DOC, DOCX, ODT, PPT, XLS etc.) |
| ImageMagick            | Conversão e manipulação de imagens                             |
| Imagick (extensão PHP) | Integração do PHP com ImageMagick                              |

## Instalação em Docker

Exemplo de Dockerfile com todas as dependências necessárias:

```bash
RUN apk add --no-cache \
    libreoffice \
    fontconfig \
    ttf-dejavu \
    font-noto \
    font-noto-cjk \
    font-noto-emoji 
```

## Instalação no Ubuntu / Debian

```bash
sudo apt install -y libreoffice
```
```bash
sudo apt install -y imagemagick 
```    
```bash
sudo apt install -y ghostscript  
```
```bash
sudo apt install -y php-imagick  
```   

## Instalação no Alpine Linux
```bash
apk add --no-cache \
    libreoffice \
    fontconfig \
    ttf-dejavu \
    font-noto \
    font-noto-cjk \
    font-noto-emoji
```
## Instalação no MacOS

Usando Homebrew:
```bash
brew install libreoffice
```
```bash
brew install imagemagick
```

## Instalação no Windows

Instale o LibreOffice

https://www.libreoffice.org/download/download/

Instale o ImageMagick

https://imagemagick.org/script/download.php#windows

Durante a instalação marque a opção:

```bash
Install legacy utilities (convert)
```

