<?php

declare(strict_types=1);

namespace App\Resume\Application\Service;

use App\Resume\Domain\Entity\Experience;
use App\Resume\Domain\Entity\Formation;
use App\Resume\Domain\Entity\Hobby;
use App\Resume\Domain\Entity\Language;
use App\Resume\Domain\Entity\Project;
use App\Resume\Domain\Entity\Reference;
use App\Resume\Domain\Entity\Skill;
use InvalidArgumentException;

final class ResumeEntityRegistry
{
    public const RESOURCE_SKILLS = 'skills';
    public const RESOURCE_LANGUAGES = 'languages';
    public const RESOURCE_HOBBIES = 'hobbies';
    public const RESOURCE_EXPERIENCES = 'experiences';
    public const RESOURCE_EDUCATIONS = 'educations';
    public const RESOURCE_REFERENCES = 'references';
    public const RESOURCE_PROJECTS = 'projects';

    public const RESOURCE_REQUIREMENT = self::RESOURCE_SKILLS
        . '|' . self::RESOURCE_LANGUAGES
        . '|' . self::RESOURCE_HOBBIES
        . '|' . self::RESOURCE_EXPERIENCES
        . '|' . self::RESOURCE_EDUCATIONS
        . '|' . self::RESOURCE_REFERENCES
        . '|' . self::RESOURCE_PROJECTS;

    /**
     * @var array<string, ResumeEntityDefinition>
     */
    private array $definitions;

    public function __construct()
    {
        $this->definitions = [
            self::RESOURCE_SKILLS => new ResumeEntityDefinition(
                self::RESOURCE_SKILLS,
                Skill::class,
                'Skill',
                [
                    'name' => ['type' => ResumeEntityDefinition::TYPE_STRING],
                    'type' => ['type' => ResumeEntityDefinition::TYPE_STRING],
                    'level' => ['type' => ResumeEntityDefinition::TYPE_INT],
                ]
            ),
            self::RESOURCE_LANGUAGES => new ResumeEntityDefinition(
                self::RESOURCE_LANGUAGES,
                Language::class,
                'Language',
                [
                    'name' => ['type' => ResumeEntityDefinition::TYPE_STRING],
                    'level' => ['type' => ResumeEntityDefinition::TYPE_INT],
                    'flag' => ['type' => ResumeEntityDefinition::TYPE_STRING],
                ]
            ),
            self::RESOURCE_HOBBIES => new ResumeEntityDefinition(
                self::RESOURCE_HOBBIES,
                Hobby::class,
                'Hobby',
                [
                    'name' => ['type' => ResumeEntityDefinition::TYPE_STRING],
                    'icon' => ['type' => ResumeEntityDefinition::TYPE_STRING],
                ]
            ),
            self::RESOURCE_EXPERIENCES => new ResumeEntityDefinition(
                self::RESOURCE_EXPERIENCES,
                Experience::class,
                'Experience',
                [
                    'title' => ['type' => ResumeEntityDefinition::TYPE_STRING],
                    'description' => ['type' => ResumeEntityDefinition::TYPE_STRING],
                    'company' => ['type' => ResumeEntityDefinition::TYPE_STRING],
                    'startedAt' => ['type' => ResumeEntityDefinition::TYPE_DATE],
                    'endedAt' => ['type' => ResumeEntityDefinition::TYPE_DATE, 'nullable' => true],
                ]
            ),
            self::RESOURCE_EDUCATIONS => new ResumeEntityDefinition(
                self::RESOURCE_EDUCATIONS,
                Formation::class,
                'Formation',
                [
                    'name' => ['type' => ResumeEntityDefinition::TYPE_STRING],
                    'school' => ['type' => ResumeEntityDefinition::TYPE_STRING],
                    'gradeLevel' => ['type' => ResumeEntityDefinition::TYPE_INT],
                    'description' => ['type' => ResumeEntityDefinition::TYPE_STRING],
                    'startedAt' => ['type' => ResumeEntityDefinition::TYPE_DATE],
                    'endedAt' => ['type' => ResumeEntityDefinition::TYPE_DATE, 'nullable' => true],
                ]
            ),
            self::RESOURCE_REFERENCES => new ResumeEntityDefinition(
                self::RESOURCE_REFERENCES,
                Reference::class,
                'Reference',
                [
                    'title' => ['type' => ResumeEntityDefinition::TYPE_STRING],
                    'company' => ['type' => ResumeEntityDefinition::TYPE_STRING],
                    'description' => ['type' => ResumeEntityDefinition::TYPE_STRING],
                    'startedAt' => ['type' => ResumeEntityDefinition::TYPE_DATE],
                    'endedAt' => ['type' => ResumeEntityDefinition::TYPE_DATE, 'nullable' => true],
                    'medias' => ['type' => ResumeEntityDefinition::TYPE_MEDIA_COLLECTION],
                ]
            ),
            self::RESOURCE_PROJECTS => new ResumeEntityDefinition(
                self::RESOURCE_PROJECTS,
                Project::class,
                'Project',
                [
                    'name' => ['type' => ResumeEntityDefinition::TYPE_STRING],
                    'description' => ['type' => ResumeEntityDefinition::TYPE_STRING],
                    'gitLink' => ['type' => ResumeEntityDefinition::TYPE_STRING, 'nullable' => true],
                ],
                scopedByUser: false
            ),
        ];
    }

    public function getDefinition(string $resource): ResumeEntityDefinition
    {
        if (!isset($this->definitions[$resource])) {
            throw new InvalidArgumentException(sprintf('Unsupported resume resource "%s".', $resource));
        }

        return $this->definitions[$resource];
    }

    /**
     * @return string[]
     */
    public function getResources(): array
    {
        return array_keys($this->definitions);
    }
}
