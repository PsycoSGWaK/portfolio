<?php

namespace App\Service;

use App\Entity\Certificate;
use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Project;
use App\Entity\ProjectImage;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Replaces every admin-editable content entity with the contents of a
 * previously exported array (see ContentExporter). This is destructive:
 * existing projects/certificates/experiences/educations are wiped before
 * the imported rows are inserted. It does not touch the uploaded files
 * themselves (images/videos/logos) — those must be copied to the target
 * server separately; this only restores the database rows that reference
 * them by filename.
 */
final class ContentImporter
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function import(array $data): void
    {
        $this->entityManager->wrapInTransaction(function () use ($data): void {
            $this->importProjects($data['projects'] ?? []);
            $this->importCertificates($data['certificates'] ?? []);
            $this->importExperiences($data['experiences'] ?? []);
            $this->importEducations($data['educations'] ?? []);
        });
    }

    private function importProjects(array $rows): void
    {
        $this->entityManager->createQuery('DELETE FROM App\Entity\ProjectImage')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Project')->execute();

        foreach ($rows as $row) {
            $project = new Project();
            $project->setTitle($row['title'] ?? '');
            $project->setSlug($row['slug'] ?? null);
            $project->setDescription($row['description'] ?? '');
            $project->setStack($row['stack'] ?? '');
            $project->setSourceUrl($row['sourceUrl'] ?? null);
            $project->setDemoUrl($row['demoUrl'] ?? null);
            $project->setCoverVideoName($row['coverVideoName'] ?? null);
            $project->setFeatured($row['featured'] ?? false);
            $project->setPublished($row['published'] ?? false);
            $project->setPosition($row['position'] ?? 0);

            foreach ($row['images'] ?? [] as $imageRow) {
                $image = new ProjectImage();
                $image->setImageName($imageRow['imageName'] ?? null);
                $image->setPosition($imageRow['position'] ?? 0);
                $project->addImage($image);
            }

            $this->entityManager->persist($project);
        }
    }

    private function importCertificates(array $rows): void
    {
        $this->entityManager->createQuery('DELETE FROM App\Entity\Certificate')->execute();

        foreach ($rows as $row) {
            $certificate = new Certificate();
            $certificate->setTitle($row['title'] ?? '');
            $certificate->setIssuer($row['issuer'] ?? '');
            $certificate->setIssueDate(isset($row['issueDate']) ? new \DateTimeImmutable($row['issueDate']) : null);
            $certificate->setCredentialUrl($row['credentialUrl'] ?? null);
            $certificate->setBadgeImageName($row['badgeImageName'] ?? null);
            $certificate->setPublished($row['published'] ?? false);
            $certificate->setPosition($row['position'] ?? 0);

            $this->entityManager->persist($certificate);
        }
    }

    private function importExperiences(array $rows): void
    {
        $this->entityManager->createQuery('DELETE FROM App\Entity\Experience')->execute();

        foreach ($rows as $row) {
            $experience = new Experience();
            $experience->setCompany($row['company'] ?? '');
            $experience->setRole($row['role'] ?? '');
            $experience->setPeriod($row['period'] ?? '');
            $experience->setLocation($row['location'] ?? null);
            $experience->setLogoName($row['logoName'] ?? null);
            $experience->setLogoUrl($row['logoUrl'] ?? null);
            $experience->setDescription($row['description'] ?? null);
            $experience->setPublished($row['published'] ?? false);
            $experience->setPosition($row['position'] ?? 0);

            $this->entityManager->persist($experience);
        }
    }

    private function importEducations(array $rows): void
    {
        $this->entityManager->createQuery('DELETE FROM App\Entity\Education')->execute();

        foreach ($rows as $row) {
            $education = new Education();
            $education->setSchool($row['school'] ?? '');
            $education->setDegree($row['degree'] ?? '');
            $education->setPeriod($row['period'] ?? '');
            $education->setLocation($row['location'] ?? null);
            $education->setLogoName($row['logoName'] ?? null);
            $education->setLogoUrl($row['logoUrl'] ?? null);
            $education->setDescription($row['description'] ?? null);
            $education->setPublished($row['published'] ?? false);
            $education->setPosition($row['position'] ?? 0);

            $this->entityManager->persist($education);
        }
    }
}
