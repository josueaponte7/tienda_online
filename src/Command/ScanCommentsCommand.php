<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name       : 'app:scan-comments',
    description: 'Escanea el proyecto en busca de comentarios TODO, FIXME, BUG, etc.'
)]
class ScanCommentsCommand extends Command
{
    // Etiquetas a buscar en los comentarios
    private array $tags = [
        'TODO',
        'FIXME',
        'BUG',
        'OPTIMIZE',
        'DEPRECATED',
        'NOTE',
        'HACK',
        'WARNING',
        'REVIEW',
        'DOC',
        'TEST',
        'REFACTOR',
    ];

    protected function configure(): void
    {
        $this
            ->addArgument(
                'directory',
                InputArgument::OPTIONAL,
                'Directorio que se debe escanear (por defecto: src/)',
                'src',
            )
            ->setHelp(
                'Este comando busca comentarios relevantes (TODO, FIXME, BUG, etc.) en los archivos PHP de tu proyecto.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Obtener el directorio de escaneo desde el argumento
        $directory = $input->getArgument('directory');

        // Ajustar la ruta base del proyecto
        $directory = realpath(dirname(__DIR__, 2) . '/' . $directory);

        if (!$directory || !is_dir($directory)) {
            $output->writeln('<error>El directorio especificado no es válido.</error>');
            return Command::FAILURE;
        }

        $output->writeln("<info>Escaneando el directorio: $directory</info>");

        $comments = $this->scanDirectory($directory);

        if (empty($comments)) {
            $output->writeln('<info>No se encontraron comentarios con etiquetas pendientes.</info>');
            return Command::SUCCESS;
        }

        // Mostrar los resultados en una tabla
        $table = new Table($output);
        $table->setHeaders(['Etiqueta', 'Archivo', 'Línea', 'Comentario']);
        $table->setRows($comments);
        $table->render();

        return Command::SUCCESS;
    }

    /**
     * Escanea un directorio en busca de comentarios con etiquetas.
     */
    private function scanDirectory(string $directory): array
    {
        $comments = [];
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $comments = array_merge($comments, $this->scanFile($file->getRealPath()));
            }
        }

        return $comments;
    }

    /**
     * Escanea un archivo PHP en busca de etiquetas definidas.
     */
    private function scanFile(string $filePath): array
    {
        $comments = [];
        $lines = file($filePath);

        foreach ($lines as $lineNumber => $line) {
            foreach ($this->tags as $tag) {
                if (stripos($line, $tag) !== false) {
                    $comments[] = [$tag, $filePath, $lineNumber + 1, trim($line)];
                }
            }
        }

        return $comments;
    }
}
