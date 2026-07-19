<?php

namespace App\Controller\Admin;

use App\Service\ContentExporter;
use App\Service\ContentImporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class DataController extends AbstractController
{
    #[Route('/admin/data/export', name: 'admin_data_export', methods: ['GET'])]
    public function export(ContentExporter $exporter): Response
    {
        $filename = 'portfolio-export-' . (new \DateTimeImmutable())->format('Y-m-d-His') . '.json';

        $response = new Response(json_encode(
            $exporter->export(),
            \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES
        ));
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    #[Route('/admin/data/import', name: 'admin_data_import', methods: ['GET', 'POST'])]
    public function import(Request $request, ContentImporter $importer, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        if ($request->isMethod('POST')) {
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('admin_data_import', (string) $request->request->get('_token')))) {
                $this->addFlash('error', 'Jeton de sécurité invalide, réessaie.');

                return $this->redirectToRoute('admin_data_import');
            }

            if (!$request->request->getBoolean('confirm')) {
                $this->addFlash('error', 'Tu dois cocher la case de confirmation pour importer — cette action remplace toutes les données existantes.');

                return $this->redirectToRoute('admin_data_import');
            }

            $file = $request->files->get('export_file');
            if (!$file) {
                $this->addFlash('error', 'Choisis un fichier JSON à importer.');

                return $this->redirectToRoute('admin_data_import');
            }

            $data = json_decode(file_get_contents($file->getPathname()), true);
            if (!\is_array($data)) {
                $this->addFlash('error', 'Ce fichier n\'est pas un export JSON valide.');

                return $this->redirectToRoute('admin_data_import');
            }

            try {
                $importer->import($data);
                $this->addFlash('success', 'Import terminé. Pense à copier les fichiers uploadés (images, vidéos, logos) séparément si besoin.');
            } catch (\Throwable $e) {
                $this->addFlash('error', 'Échec de l\'import : ' . $e->getMessage());
            }

            return $this->redirectToRoute('admin_data_import');
        }

        return $this->render('admin/data_import.html.twig');
    }
}
