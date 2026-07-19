<?php

namespace App\Service;

use App\Entity\Certificate;
use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Project;
use App\Repository\CertificateRepository;
use App\Repository\EducationRepository;
use App\Repository\ExperienceRepository;
use App\Repository\ProfileRepository;
use App\Repository\ProjectRepository;

/**
 * Dumps every admin-editable content entity (not the uploaded files
 * themselves) to a plain array, so it can be re-imported into another
 * database with the same schema — typically local dev content into a
 * freshly-deployed, empty production database.
 */
final class ContentExporter
{
    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly CertificateRepository $certificateRepository,
        private readonly ExperienceRepository $experienceRepository,
        private readonly EducationRepository $educationRepository,
        private readonly ProfileRepository $profileRepository,
    ) {
    }

    public function export(): array
    {
        return [
            'exportedAt' => (new \DateTimeImmutable())->format(\DATE_ATOM),
            'projects' => $this->exportProjects(),
            'certificates' => $this->exportCertificates(),
            'experiences' => $this->exportExperiences(),
            'educations' => $this->exportEducations(),
            'profile' => $this->exportProfile(),
        ];
    }

    private function exportProfile(): ?array
    {
        $profile = $this->profileRepository->find(1);

        if (!$profile) {
            return null;
        }

        return [
            'photoName' => $profile->getPhotoName(),
        ];
    }

    private function exportProjects(): array
    {
        return array_map(static function (Project $project): array {
            return [
                'title' => $project->getTitle(),
                'slug' => $project->getSlug(),
                'description' => $project->getDescription(),
                'context' => $project->getContext(),
                'role' => $project->getRole(),
                'objectives' => $project->getObjectives(),
                'features' => $project->getFeatures(),
                'stack' => $project->getStack(),
                'tools' => $project->getTools(),
                'sourceUrl' => $project->getSourceUrl(),
                'demoUrl' => $project->getDemoUrl(),
                'coverVideoName' => $project->getCoverVideoName(),
                'techDocName' => $project->getTechDocName(),
                'featured' => $project->isFeatured(),
                'published' => $project->isPublished(),
                'position' => $project->getPosition(),
                'createdAt' => $project->getCreatedAt()?->format(\DATE_ATOM),
                'images' => array_map(static fn ($image) => [
                    'imageName' => $image->getImageName(),
                    'position' => $image->getPosition(),
                ], $project->getImages()->toArray()),
            ];
        }, $this->projectRepository->findAll());
    }

    private function exportCertificates(): array
    {
        return array_map(static function (Certificate $certificate): array {
            return [
                'title' => $certificate->getTitle(),
                'issuer' => $certificate->getIssuer(),
                'issueDate' => $certificate->getIssueDate()?->format('Y-m-d'),
                'credentialUrl' => $certificate->getCredentialUrl(),
                'badgeImageName' => $certificate->getBadgeImageName(),
                'published' => $certificate->isPublished(),
                'position' => $certificate->getPosition(),
            ];
        }, $this->certificateRepository->findAll());
    }

    private function exportExperiences(): array
    {
        return array_map(static function (Experience $experience): array {
            return [
                'company' => $experience->getCompany(),
                'role' => $experience->getRole(),
                'period' => $experience->getPeriod(),
                'location' => $experience->getLocation(),
                'logoName' => $experience->getLogoName(),
                'logoUrl' => $experience->getLogoUrl(),
                'description' => $experience->getDescription(),
                'published' => $experience->isPublished(),
                'position' => $experience->getPosition(),
            ];
        }, $this->experienceRepository->findAll());
    }

    private function exportEducations(): array
    {
        return array_map(static function (Education $education): array {
            return [
                'school' => $education->getSchool(),
                'degree' => $education->getDegree(),
                'period' => $education->getPeriod(),
                'location' => $education->getLocation(),
                'logoName' => $education->getLogoName(),
                'logoUrl' => $education->getLogoUrl(),
                'description' => $education->getDescription(),
                'published' => $education->isPublished(),
                'position' => $education->getPosition(),
            ];
        }, $this->educationRepository->findAll());
    }
}
