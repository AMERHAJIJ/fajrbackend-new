<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Actions\Action;
use App\Models\Setting;
use Filament\Notifications\Notification;

class SiteSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    
    public static function getNavigationLabel(): string { return 'إعدادات الموقع'; }
    
    public function getTitle(): string { return 'إعدادات الموقع التعريفي'; }
    
    protected static ?string $navigationGroup = 'Sistem Yönetimi';
    
    protected static ?int $navigationSort = 110;

    protected static string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            // Home
            'site_home_hero_badge' => Setting::getVal('site_home_hero_badge', 'Fecir Eğitim, Öğretim ve Girişimcilik Derneği'),
            'site_home_hero_title_1' => Setting::getVal('site_home_hero_title_1', 'جمعية'),
            'site_home_hero_title_highlight' => Setting::getVal('site_home_hero_title_highlight', 'الفجر'),
            'site_home_hero_title_2' => Setting::getVal('site_home_hero_title_2', 'للتربية والتعليم<br>وريادة الأعمال'),
            'site_home_hero_text' => Setting::getVal('site_home_hero_text', 'جمعية خيرية غير ربحية، تشرف على دار إقراء للقرآن الكريم والعلم الشرعي والتربية الإيمانية، وتنمية مهارات الشباب وريادة الأعمال في كايا شهير – باشاك شهير، إسطنبول.'),
            // About
            'site_about_intro_1' => Setting::getVal('site_about_intro_1', 'جمعية الفجر للتربية والتعليم وريادة الأعمال هي جمعية خيرية غير ربحية، مسجلة رسمياً في تركيا، تتخذ من منطقة كايا شهير – باشاك شهير في إسطنبول مقراً لها.'),
            'site_about_intro_2' => Setting::getVal('site_about_intro_2', 'تعمل الجمعية تحت إشراف "مركز الفجر" الذي يشرف على دار إقراء للقرآن الكريم، وتعليم العلم الشرعي، والتربية الإيمانية، بالإضافة إلى تنمية مهارات الشباب والأطفال من خلال برامج تعليمية وتربوية وتطويرية متكاملة.'),
            'site_about_intro_3' => Setting::getVal('site_about_intro_3', 'تجمع الجمعية في هويتها بين التربية والتعليم وريادة الأعمال، إيماناً بأن بناء الفرد المتكامل علمياً وإيمانياً ومهارياً هو أساس النهضة المجتمعية.'),
            'site_about_vision' => Setting::getVal('site_about_vision', 'بناء جيل متعلم ومؤهل علمياً وأخلاقياً، قادر على الإبداع والمساهمة الفاعلة في مجتمعه، يجمع بين القيم الإسلامية الراسخة ومهارات العصر وريادة الأعمال.'),
            'site_about_mission' => Setting::getVal('site_about_mission', 'تقديم برامج تعليمية وتربوية وتنموية متكاملة تساعد الأطفال والشباب على تطوير قدراتهم العلمية والشخصية والمهنية، في بيئة إيمانية آمنة، وبإشراف كوادر مؤهلة، مع تعزيز روح المبادرة والابتكار وثقافة العمل الحر.'),
            // Contact
            'site_contact_address' => Setting::getVal('site_contact_address', 'Kayabaşı Mah, Ulubatlı Hasan Cd, Dış Kapı No 2, C8-8 Dükkan<br>34480 Başakşehir/İstanbul'),
            'site_contact_phone' => Setting::getVal('site_contact_phone', '+90 546 150 05 52'),
            'site_contact_instagram' => Setting::getVal('site_contact_instagram', '@_fecir1'),
            'site_contact_telegram' => Setting::getVal('site_contact_telegram', 't.me/Fecir1'),
            'site_contact_hours' => Setting::getVal('site_contact_hours', 'الإثنين – الجمعة: 10:00 – 18:00<br>السبت: 10:00 – 22:00<br>الأحد: مغلق'),
            // Stats
            'site_stats' => Setting::getVal('site_stats', '[{"value":"12+","label":"عاماً من العطاء"},{"value":"500+","label":"طالب وطالبة"},{"value":"30+","label":"برنامج تعليمي"},{"value":"5.0","label":"تقييم الجمعية"}]'),
            // Programs
            'site_programs_quran_items' => Setting::getVal('site_programs_quran_items', '["دار إقراء لتحفيظ القرآن الكريم للأطفال والكبار","دروس في العلوم الشرعية: الفقه، التفسير، الحديث، السيرة","دورات تقوية في المواد الأكاديمية (لغة عربية، رياضيات، علوم)","تعليم اللغة العربية للناطقين بغيرها"]'),
            'site_programs_tarbiya_items' => Setting::getVal('site_programs_tarbiya_items', '["حلقات تحفيظ القرآن ومجالس العلم","أنشطة أسبوعية للأطفال (ترفيهية + تعليمية)","برامج تربية إيمانية للشباب والفتيات","ندوات ومحاضرات أسبوعية كل خميس مع نخبة من المشايخ"]'),
            'site_programs_social_items' => Setting::getVal('site_programs_social_items', '["رحلات ترفيهية وتعليمية شهرية","مخيمات صيفية للأطفال والشباب","فعاليات ثقافية ومناسبات إسلامية","أيام مفتوحة وأنشطة رياضية ومنافسات ثقافية"]'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('site_settings')
                    ->tabs([
                        Tab::make('الرئيسية')
                            ->icon('heroicon-o-home')
                            ->schema([
                                Section::make('قسم البطل (Hero)')
                                    ->schema([
                                        TextInput::make('site_home_hero_badge')->label('الشارة العلوية')->required(),
                                        TextInput::make('site_home_hero_title_1')->label('عنوان الجزء الأول')->required(),
                                        TextInput::make('site_home_hero_title_highlight')->label('عنوان الكلمة المميزة (الذهبية)')->required(),
                                        Textarea::make('site_home_hero_title_2')->label('عنوان الجزء الثاني')->required()->rows(3),
                                        Textarea::make('site_home_hero_text')->label('النص الوصفي')->required()->rows(4),
                                    ])->columns(2),
                                Section::make('الإحصائيات')
                                    ->schema([
                                        Textarea::make('site_stats')->label('الإحصائيات (JSON)')->rows(5)->helperText('تنسيق: [{"value":"12+","label":"عاماً من العطاء"},...]'),
                                    ]),
                            ]),
                        Tab::make('من نحن')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make('المحتوى')
                                    ->schema([
                                        Textarea::make('site_about_intro_1')->label('الفقرة الأولى')->required()->rows(3),
                                        Textarea::make('site_about_intro_2')->label('الفقرة الثانية')->required()->rows(3),
                                        Textarea::make('site_about_intro_3')->label('الفقرة الثالثة')->required()->rows(3),
                                        Textarea::make('site_about_vision')->label('الرؤية')->required()->rows(4),
                                        Textarea::make('site_about_mission')->label('الرسالة')->required()->rows(4),
                                    ]),
                            ]),
                        Tab::make('البرامج')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Section::make('القرآن الكريم والعلوم الشرعية')
                                    ->schema([
                                        Textarea::make('site_programs_quran_items')->label('النقاط (JSON)')->rows(5)->helperText('مصفوفة JSON من النصوص'),
                                    ]),
                                Section::make('التربية الإيمانية')
                                    ->schema([
                                        Textarea::make('site_programs_tarbiya_items')->label('النقاط (JSON)')->rows(5),
                                    ]),
                                Section::make('الأنشطة الاجتماعية')
                                    ->schema([
                                        Textarea::make('site_programs_social_items')->label('النقاط (JSON)')->rows(5),
                                    ]),
                            ]),
                        Tab::make('اتصل بنا')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                Section::make('معلومات الاتصال')
                                    ->schema([
                                        Textarea::make('site_contact_address')->label('العنوان')->required()->rows(3),
                                        TextInput::make('site_contact_phone')->label('رقم الهاتف')->required(),
                                        TextInput::make('site_contact_instagram')->label('إنستغرام'),
                                        TextInput::make('site_contact_telegram')->label('تلغرام'),
                                        Textarea::make('site_contact_hours')->label('ساعات العمل')->rows(4),
                                    ])->columns(2),
                            ]),
                    ])->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save()
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            Setting::setVal($key, $value);
        }

        Notification::make()
            ->title('تم حفظ إعدادات الموقع بنجاح')
            ->success()
            ->send();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole('admin');
    }
}
