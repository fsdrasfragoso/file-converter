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