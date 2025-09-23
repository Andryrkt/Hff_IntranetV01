<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/**
 * @Route("/documentation-technique")
 */
class DocumentationController
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @Route("/", name="documentation_index")
     */
    public function index(): Response
    {
        $guides = $this->getGuidesStructure();

        return new Response($this->twig->render('documentation/index.html.twig', [
            'guides' => $guides,
            'title' => 'Documentation Technique'
        ]));
    }

    /**
     * @Route("/{category}", name="documentation_category")
     */
    public function category(string $category): Response
    {
        $guides = $this->getGuidesStructure();

        if (!isset($guides[$category])) {
            throw new \Exception("Catégorie de documentation non trouvée: $category");
        }

        return new Response($this->twig->render('documentation/category.html.twig', [
            'category' => $category,
            'categoryData' => $guides[$category],
            'guides' => $guides,
            'title' => "Documentation - " . ucfirst($category)
        ]));
    }

    /**
     * @Route("/{category}/{file}", name="documentation_file")
     */
    public function file(string $category, string $file): Response
    {
        $guides = $this->getGuidesStructure();

        if (!isset($guides[$category])) {
            throw new \Exception("Catégorie de documentation non trouvée: $category");
        }

        $filePath = $guides[$category]['path'] . '/' . $file . '.md';

        if (!file_exists($filePath)) {
            throw new \Exception("Fichier de documentation non trouvé: $file");
        }

        $content = file_get_contents($filePath);
        $parsedContent = $this->parseMarkdown($content);

        return new Response($this->twig->render('documentation/file.html.twig', [
            'category' => $category,
            'file' => $file,
            'content' => $parsedContent,
            'guides' => $guides,
            'title' => $this->getFileTitle($content)
        ]));
    }

    private function getGuidesStructure(): array
    {
        $guidesPath = __DIR__ . '/../../guides';

        return [
            'configuration' => [
                'name' => 'Configuration',
                'description' => 'Guides de configuration du système',
                'path' => $guidesPath . '/configuration',
                'icon' => '⚙️',
                'files' => $this->getMarkdownFiles($guidesPath . '/configuration')
            ],
            'fonctionnel' => [
                'name' => 'Fonctionnel',
                'description' => 'Documentation fonctionnelle et métier',
                'path' => $guidesPath . '/fonctionnel',
                'icon' => '📋',
                'files' => $this->getMarkdownFiles($guidesPath . '/fonctionnel')
            ],
            'migrations' => [
                'name' => 'Migrations',
                'description' => 'Guides des migrations de base de données',
                'path' => $guidesPath . '/migrations',
                'icon' => '🔄',
                'files' => $this->getMarkdownFiles($guidesPath . '/migrations')
            ],
            'technique' => [
                'name' => 'Technique',
                'description' => 'Documentation technique et architecture',
                'path' => $guidesPath . '/technique',
                'icon' => '🔧',
                'files' => $this->getMarkdownFiles($guidesPath . '/technique')
            ]
        ];
    }

    private function getMarkdownFiles(string $path): array
    {
        $files = [];

        if (!is_dir($path)) {
            return $files;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'md') {
                $relativePath = str_replace($path . '/', '', $file->getPathname());
                $fileName = $file->getBasename('.md');

                $files[] = [
                    'name' => $fileName,
                    'path' => $relativePath,
                    'title' => $this->getFileTitle(file_get_contents($file->getPathname())),
                    'size' => $file->getSize(),
                    'modified' => $file->getMTime()
                ];
            }
        }

        // Trier par nom
        usort($files, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $files;
    }

    private function getFileTitle(string $content): string
    {
        // Extraire le titre du premier # dans le markdown
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }

        return 'Documentation';
    }

    private function parseMarkdown(string $content): string
    {
        // Simple parsing markdown (vous pouvez utiliser une librairie comme Parsedown)
        $content = htmlspecialchars($content);

        // Headers
        $content = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $content);
        $content = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $content);
        $content = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $content);

        // Bold
        $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);

        // Italic
        $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);

        // Code blocks
        $content = preg_replace('/```(\w+)?\n(.*?)\n```/s', '<pre><code class="language-$1">$2</code></pre>', $content);
        $content = preg_replace('/`(.*?)`/', '<code>$1</code>', $content);

        // Links
        $content = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $content);

        // Line breaks
        $content = nl2br($content);

        return $content;
    }
}
