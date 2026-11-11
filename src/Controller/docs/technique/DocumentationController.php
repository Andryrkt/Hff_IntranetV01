<?php


namespace App\Controller\docs\technique;

use App\Controller\Controller;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Documentation technique (fichiers Markdown de docs/technique) : réservée aux administrateurs HFF-ADMIN.
 * Tout fichier .md ajouté dans ce dossier apparaît automatiquement dans le sommaire.
 */
class DocumentationController extends Controller
{
    /**
     * @Route("/doc/technique", name="app_doc_tech_index")
     */
    public function index()
    {
        $this->exigerAdmin();

        $finder = new Finder();
        $finder->files()->in($this->dossierDocs())->name('*.md')->sortByName();

        $groupedDocuments = [];
        foreach ($finder as $file) {
            // séparateur "/" : sous Windows le "\" du chemin relatif donnerait une URL invalide
            $relativePath = str_replace('\\', '/', $file->getRelativePathname());
            $directory = str_replace('\\', '/', $file->getRelativePath());

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
     * @Route("/doc/technique/{filename}", name="app_doc_tech_show", requirements={"filename"=".+\.md"})
     */
    public function show(string $filename)
    {
        $this->exigerAdmin();

        // refus de tout chemin hors du dossier de documentation (../, chemin absolu...)
        $dossierDocs = $this->dossierDocs();
        $filePath = realpath($dossierDocs . DIRECTORY_SEPARATOR . ltrim($filename, '/\\'));

        if ($filePath === false || strpos($filePath, $dossierDocs . DIRECTORY_SEPARATOR) !== 0 || !is_file($filePath)) {
            throw new \Exception("Fichier de documentation non trouvé.");
        }

        $safeFilename = $filename;
        $markdownContent = file_get_contents($filePath);
        $metadata = $this->getDocumentMetadata($filePath); // Get metadata

        // Remove front matter from content before parsing Markdown
        $markdownContent = preg_replace('/^---\\s*\\n(.*?)\\n---\\s*\\n/s', '', $markdownContent, 1);

        $parser = new \Parsedown();
        $htmlContent = $parser->text($markdownContent);
        // les liens relatifs visent les fichiers du dépôt (src/...), inaccessibles depuis l'application : on garde le texte
        $htmlContent = preg_replace('#<a href="(?!https?:|\#)[^"]*">(.*?)</a>#s', '$1', $htmlContent);

        // Use title from metadata if available, otherwise generate from filename
        $title = $metadata['title'] ?? $this->getTitleFromFilename(pathinfo($safeFilename, PATHINFO_FILENAME));

        return $this->render('doc/technique/show.html.twig', [
            'title' => $title,
            'content' => $htmlContent,
            'metadata' => $metadata, // Pass metadata to template
        ]);
    }

    private function exigerAdmin(): void
    {
        if (!$this->estAdmin()) {
            $this->redirectToRoute('profil_acceuil');
        }
    }

    private function dossierDocs(): string
    {
        return dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'technique';
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
