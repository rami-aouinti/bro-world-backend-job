<?php

namespace App\Resume\Infrastructure\DataFixtures\ORM;

use App\Resume\Domain\Entity\Template;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class TemplateFixtures extends Fixture
{
    public function load(ObjectManager $om): void
    {
        foreach ($this->data() as $row) {
            $e = (new Template())
                ->setKey($row['key'])
                ->setLabel($row['label'])
                ->setIsDefault($row['default'] ?? null)
                ->setFontFamily($row['fontFamily'])
                ->setBaseSize($row['baseSize'])
                ->setPreviewImg($row['previewImg'] ?? null)
                ->setPalette($row['palette'])
                ->setPhoto($row['photo'])
                ->setPhotoShadow($row['photoShadow'] ?? null)
                ->setLayout($row['layout'])
                ->setSidebar($row['sidebar'] ?? null)
                ->setCorner($row['corner'] ?? null)
                ->setVbar($row['vbar'] ?? null)
                ->setSkills($row['skills'] ?? null)
                ->setLanguages($row['languages'] ?? null)
                ->setCategory($row['category'])
                ->setTemplate($row['template'])
                ->setSrc($row['src'])
                ->setDownloads($row['downloads'] ?? 0)
                ->setViews($row['views'] ?? 0)
            ;
            $om->persist($e);
        }

        $om->flush();
    }

    private function data(): array
    {
        return [
            [
                'key' => 'stylish',
                'label' => 'Stylish (Default)',
                'default' => true,
                'fontFamily' => 'Merriweather',
                'baseSize' => '13px',
                'previewImg' => '/img/cv/cv-2.png',
                'palette' => ['primary'=>'#d94f3d','accent'=>'#03203d','paper'=>'#ffffff','text'=>'#161616'],
                'photo' => ['show'=>true,'position'=>'left','widthMm'=>28,'heightMm'=>28,'rounded'=>false],
                'photoShadow' => ['enabled'=>true,'elevation'=>8,'color'=>'rgba(0,0,0,.25)'],
                'layout' => 'stylish',
                'corner' => ['type'=>'quarter','anchor'=>'top-left','color'=>'#09143d','sizeMm'=>30,'enabled'=>true],
                'skills' => ['chipVariant'=>'tonal','chipColor'=>'#03203d','chipDensity'=>'compact','editable'=>true,'draggable'=>true],
                'languages' => ['variant'=>'bars','maxLevel'=>5,'showNote'=>true,'sizePx'=>8,'accent'=>'#03203d'],
                'category' => 'Creative',
                'template' => 'CV',
                'src' => '/samples/stylish.pdf',
                'downloads' => 0,
                'views' => 0,
            ],
            [
                'key' => 'sidebarRight',
                'label' => 'Sidebar Right',
                'fontFamily' => 'Inter',
                'baseSize' => '14px',
                'previewImg' => '/img/cv/cv-1.png',
                'palette' => ['primary'=>'#320604','accent'=>'#f29f05','paper'=>'#ffffff','text'=>'#111'],
                'photo' => ['show'=>true,'position'=>'right','widthMm'=>42,'heightMm'=>42,'rounded'=>true],
                'layout' => 'sidebar-right',
                'sidebar' => ['enabled'=>true,'widthMm'=>70,'background'=>'#3e61a6','text'=>'#0f172a','borderColor'=>'#0b0c0e'],
                'corner' => ['type'=>'quarter','anchor'=>'top-left','color'=>'#59533a','sizeMm'=>28,'enabled'=>true],
                'skills' => ['chipVariant'=>'outlined','chipColor'=>'#f29f05','chipDensity'=>'compact','editable'=>true,'draggable'=>true],
                'languages' => ['variant'=>'stars','maxLevel'=>5,'showNote'=>true,'sizePx'=>18,'accent'=>'#f29f05'],
                'category' => 'Classic',
                'template' => 'CV',
                'src' => '/samples/sidebar-right.pdf',
                'downloads' => 0,
                'views' => 0,
            ],
            [
                'key' => 'sidebarLeft',
                'label' => 'Sidebar Left',
                'fontFamily' => 'Inter',
                'baseSize' => '14px',
                'previewImg' => '/img/cv/cv-1.png',
                'palette' => ['primary'=>'#320604','accent'=>'#f29f05','paper'=>'#ffffff','text'=>'#111'],
                'photo' => ['show'=>true,'position'=>'right','widthMm'=>42,'heightMm'=>42,'rounded'=>true],
                'layout' => 'sidebar-left',
                'sidebar' => ['enabled'=>true,'widthMm'=>70,'background'=>'#3e61a6','text'=>'#0f172a','borderColor'=>'#0b0c0e'],
                'corner' => ['type'=>'quarter','anchor'=>'top-left','color'=>'#59533a','sizeMm'=>28,'enabled'=>true],
                'skills' => ['chipVariant'=>'outlined','chipColor'=>'#f29f05','chipDensity'=>'compact','editable'=>true,'draggable'=>true],
                'languages' => ['variant'=>'stars','maxLevel'=>5,'showNote'=>true,'sizePx'=>18,'accent'=>'#f29f05'],
                'category' => 'Creative',
                'template' => 'Cover',
                'src' => '/samples/sidebar-left.pdf',
                'downloads' => 0,
                'views' => 0,
            ],
            [
                'key' => 'stacked',
                'label' => 'Stacked (One column)',
                'fontFamily' => 'Inter',
                'baseSize' => '14px',
                'previewImg' => '/img/cv/cv-3.png',
                'palette' => ['primary'=>'#1f3d6d','accent'=>'#ff9e1a','paper'=>'#ffffff','text'=>'#0e0e0e'],
                'photo' => ['show'=>true,'position'=>'right','widthMm'=>30,'heightMm'=>30,'rounded'=>false],
                'photoShadow' => ['enabled'=>true,'elevation'=>4,'color'=>'rgba(0,0,0,.25)'],
                'layout' => 'stacked',
                'corner' => ['type'=>'diagonal','anchor'=>'top-left','color'=>'#26a4d3','sizeMm'=>34,'enabled'=>true],
                'skills' => ['chipVariant'=>'elevated','chipColor'=>'#ff9e1a','chipDensity'=>'comfortable','editable'=>true,'draggable'=>true],
                'languages' => ['variant'=>'stars','maxLevel'=>5,'showNote'=>true,'sizePx'=>18,'accent'=>'#ff9e1a'],
                'category' => 'Classic',
                'template' => 'CV',
                'src' => '/samples/stacked.pdf',
                'downloads' => 0,
                'views' => 0,
            ],
            [
                'key' => 'photoLeft',
                'label' => 'Photo Left (alias)',
                'fontFamily' => 'Lato',
                'baseSize' => '13.5px',
                'previewImg' => '/img/cv/cv-4.png',
                'palette' => ['primary'=>'#26a4d3','accent'=>'#ce2626','paper'=>'#ffffff','text'=>'#0e0e0e'],
                'photo' => ['show'=>true,'position'=>'left','widthMm'=>38,'heightMm'=>48,'rounded'=>false],
                'layout' => 'photo-left',
                'sidebar' => ['enabled'=>true,'widthMm'=>70,'background'=>'#f3f7ff','text'=>'#0f172a','borderColor'=>'#e6e8ec'],
                'corner' => ['type'=>'diagonal','anchor'=>'top-right','color'=>'#26a4d3','sizeMm'=>34,'enabled'=>true],
                'vbar'   => ['show'=>true,'side'=>'left','color'=>'#c23d3d','widthMm'=>3,'offsetMm'=>0],
                'skills' => ['chipVariant'=>'text','chipColor'=>'#ce2626','chipDensity'=>'compact','editable'=>true,'draggable'=>true],
                'languages' => ['variant'=>'stars','maxLevel'=>5,'showNote'=>true,'sizePx'=>18,'accent'=>'#ce2626'],
                'category' => 'Premium',
                'template' => 'Cover',
                'src' => '/samples/photo-left.pdf',
                'downloads' => 0,
                'views' => 0,
            ],
        ];
    }
}
