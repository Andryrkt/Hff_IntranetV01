<?php


namespace App\Controller\docs\technique;

use App\Controller\Controller;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Annotation\Route;

class DocumentationController extends Controller
{
    /**
     * @Route("/doc/technique", name="app_doc_tech_index")
     */
    public function index()
    {
        $baseDocDir = 'C:/wamp64/www/Hffintranet/docs/technique/';

        $finder = new Finder();
        $finder->files()->in($baseDocDir)->name('*.md')->sortByName();

        $groupedDocuments = [];
        foreach ($finder as $file) {
            $relativePath = $file->getRelativePathname();
            $directory = $file->getRelativePath();

            $metadata = $this->getDocumentMetadata($file->getPathname()); // Get metadata

            // Use title from metadata if available, otherwise generate from filename
            $title = $metadata['title'] ?? $this->getTitleFromFilename(pathinfo($relativePath, PATHINFO_FILENAME));
            // Use category from metadata if available, otherwise generate from directory
            $category = $metadata['category'] ?? ($directory === '' ? 'Général' : ucwords(str_replace(['_', '-'], ' ', $directory)));

            $groupedDocuments[$category][] = [
                'filename' => $relativePath,
                'title' => $title,
                'metadata' => $metadata, // Pass full metadata
            ];
        }

        ksort($groupedDocuments);

        return $this->render('doc/technique/index.html.twig', [
            'groupedDocuments' => $groupedDocuments,
        ]);
    }

    private function getTitleFromFilename(string $filename): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $filename));
    }

    /**
     * @Route("/doc/technique/{filename}", name="app_doc_tech_show")
     */
    public function show(string $filename)
    {
        // SECURITY: Sanitize filename to prevent directory traversal attacks.
        // Ensure no '..' or absolute paths are present.
        $safeFilename = str_replace(['..', './', '\\'], '', $filename); // Remove '..' and path separators
        $safeFilename = ltrim($safeFilename, '/'); // Remove leading slash if any

        $filePath = 'C:/wamp64/www/Hffintranet/docs/technique/' . $safeFilename;

        if (!file_exists($filePath)) {
            throw new \Exception("Fichier de documentation non trouvé.");
        }

        $markdownContent = file_get_contents($filePath);
        $metadata = $this->getDocumentMetadata($filePath); // Get metadata

        // Remove front matter from content before parsing Markdown
        $markdownContent = preg_replace('/^---\\s*\\n(.*?)\\n---\\s*\\n/s', '', $markdownContent, 1);

        $parser = new \Parsedown();
        $htmlContent = $parser->text($markdownContent);

        // Use title from metadata if available, otherwise generate from filename
        $title = $metadata['title'] ?? $this->getTitleFromFilename(pathinfo($safeFilename, PATHINFO_FILENAME));

        return $this->render('doc/technique/show.html.twig', [
            'title' => $title,
            'content' => $htmlContent,
            'metadata' => $metadata, // Pass metadata to template
        ]);
    }

    private function getDocumentMetadata(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $metadata = [];

        // Check for front matter (YAML block between ---)
        if (preg_match('/^---\s*\n(.*?)\n---\s*\n/s', $content, $matches)) {
            try {
                $yaml = $matches[1];
                $metadata = Yaml::parse($yaml);
            } catch (\Exception $e) {
                // Log error or handle invalid YAML
                error_log("Error parsing YAML front matter in {$filePath}: " . $e->getMessage());
            }
        }

        return $metadata;
    }

    private function generateMiniToc(string $markdownContent): array
    {
        $toc = [];
        // Regex to find Markdown headings (H1 to H6)
        // Captures the level (number of #) and the heading text
        preg_match_all('/^(#+)\s*(.+)$/m', $markdownContent, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $level = strlen($match[1]); // Number of '#' characters
            $text = trim($match[2]);    // Heading text

            // Generate a simple slug for the anchor
            $slug = strtolower(str_replace([' ', '.', ',', '!', '?', '(', ')', '[', ']', '{', '}', '/', '\\', '&', '%', ',', '#', '@', '^', '*', '+', '=', '|', '<', '>', '~', '`', '\''], '-', $text));
            $slug = preg_replace('/-+/', '-', $slug); // Replace multiple hyphens with single
            $slug = trim($slug, '-'); // Trim leading/trailing hyphens

            $toc[] = [
                'level' => $level,
                'text' => $text,
                'slug' => $slug,
            ];
        }

        return $toc;
    }
}
