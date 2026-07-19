<?php

namespace App\Service;

use App\Entity\Certificate;
use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Profile;
use App\Entity\Project;
use App\Entity\ProjectImage;
use App\Entity\Skill;
use App\Entity\SkillCategory;
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
            $this->importProfile($data['profile'] ?? null);
            $this->importSkillCategories($data['skillCategories'] ?? []);
        });
    }

    private function importSkillCategories(array $rows): void
    {
        $this->entityManager->createQuery('DELETE FROM App\Entity\Skill')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\SkillCategory')->execute();

        foreach ($rows as $row) {
            $category = new SkillCategory();
            $category->setLabel($row['label'] ?? '');
            $category->setIcon($row['icon'] ?? null);
            $category->setPublished($row['published'] ?? false);
            $category->setPosition($row['position'] ?? 0);

            foreach ($row['skills'] ?? [] as $skillRow) {
                $skill = new Skill();
                $skill->setLabel($skillRow['label'] ?? '');
                $skill->setPosition($skillRow['position'] ?? 0);
                $category->addSkill($skill);
            }

            $this->entityManager->persist($category);
        }
    }

    private function importProfile(?array $row): void
    {
        if (!$row) {
            return;
        }

        $profile = $this->entityManager->getRepository(Profile::class)->find(1);
        if (!$profile) {
            $profile = new Profile();
            $this->entityManager->persist($profile);
        }
        $profile->setPhotoName($row['photoName'] ?? null);
        $profile->setAboutText($row['aboutText'] ?? null);
        $profile->setHighlights($row['highlights'] ?? null);
        $profile->setStats($row['stats'] ?? null);
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
            $project->setDescriptionEn($row['descriptionEn'] ?? null);
            $project->setContext($row['context'] ?? null);
            $project->setContextEn($row['contextEn'] ?? null);
            $project->setRole($row['role'] ?? null);
            $project->setObjectives($row['objectives'] ?? null);
            $project->setObjectivesEn($row['objectivesEn'] ?? null);
            $project->setFeatures($row['features'] ?? null);
            $project->setFeaturesEn($row['featuresEn'] ?? null);
            $project->setStack($row['stack'] ?? '');
            $project->setTools($row['tools'] ?? null);
            $project->setSourceUrl($row['sourceUrl'] ?? null);
            $project->setDemoUrl($row['demoUrl'] ?? null);
            $project->setCoverVideoName($row['coverVideoName'] ?? null);
            $project->setTechDocName($row['techDocName'] ?? null);
            $project->setFeatured($row['featured'] ?? false);
            $project->setPublished($row['published'] ?? false);
            $project->setWip($row['wip'] ?? false);
            $project->setPosition($row['position'] ?? 0);
            if (isset($row['createdAt'])) {
                $project->setCreatedAt(new \DateTimeImmutable($row['createdAt']));
            }

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
