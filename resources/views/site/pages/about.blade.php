@extends('site.layouts.app')
@section('title', 'من نحن - جمعية الفجر')
@section('description', 'تعرف على جمعية الفجر للتربية والتعليم وريادة الأعمال: رؤيتنا، رسالتنا، أهدافنا، وهيكلنا التنظيمي')

@section('content')
{{-- Hero --}}
<section class="hero-gradient pt-32 pb-20 relative overflow-hidden">
    <div class="absolute inset-0">
        <div class="absolute top-1/3 right-1/4 w-96 h-96 bg-gold/5 rounded-full blur-3xl"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10 text-gold text-sm mb-6">من نحن</div>
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-4">من نحن</h1>
        <p class="text-white/50 text-lg max-w-2xl mx-auto">جمعية الفجر للتربية والتعليم وريادة الأعمال – جمعية خيرية غير ربحية في كايا شهير، باشاك شهير، إسطنبول</p>
    </div>
</section>

{{-- About Content --}}
<section class="py-24 bg-cream">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <div class="reveal">
                <span class="text-gold text-sm font-medium tracking-widest uppercase">نبذة عنا</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-dark mt-3 mb-8">جمعية الفجر للتربية والتعليم وريادة الأعمال</h2>
                <div class="prose prose-lg max-w-none text-dark/60 leading-relaxed space-y-4">
                    <p>{{ \App\Models\Setting::getVal('site_about_intro_1', 'جمعية الفجر للتربية والتعليم وريادة الأعمال هي جمعية خيرية غير ربحية، مسجلة رسمياً في تركيا، تتخذ من منطقة كايا شهير – باشاك شهير في إسطنبول مقراً لها.') }}</p>
                    <p>{{ \App\Models\Setting::getVal('site_about_intro_2', 'تعمل الجمعية تحت إشراف "مركز الفجر" الذي يشرف على دار إقراء للقرآن الكريم، وتعليم العلم الشرعي، والتربية الإيمانية، بالإضافة إلى تنمية مهارات الشباب والأطفال من خلال برامج تعليمية وتربوية وتطويرية متكاملة.') }}</p>
                    <p>{{ \App\Models\Setting::getVal('site_about_intro_3', 'تجمع الجمعية في هويتها بين التربية والتعليم وريادة الأعمال، إيماناً بأن بناء الفرد المتكامل علمياً وإيمانياً ومهارياً هو أساس النهضة المجتمعية.') }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Vision & Mission --}}
<section class="py-24 bg-dark relative overflow-hidden">
    <div class="absolute inset-0 pattern-dots opacity-10"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid md:grid-cols-2 gap-8">
            <div class="bg-white/5 border border-white/10 rounded-3xl p-10 reveal">
                <div class="w-14 h-14 rounded-2xl bg-gold/20 flex items-center justify-center text-gold mb-6">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">رؤيتنا</h3>
                <p class="text-white/50 leading-relaxed">{{ \App\Models\Setting::getVal('site_about_vision', 'بناء جيل متعلم ومؤهل علمياً وأخلاقياً، قادر على الإبداع والمساهمة الفاعلة في مجتمعه، يجمع بين القيم الإسلامية الراسخة ومهارات العصر وريادة الأعمال.') }}</p>
            </div>
            <div class="bg-white/5 border border-white/10 rounded-3xl p-10 reveal" style="animation-delay:0.2s">
                <div class="w-14 h-14 rounded-2xl bg-fajr/20 flex items-center justify-center text-fajr-light mb-6">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">رسالتنا</h3>
                <p class="text-white/50 leading-relaxed">{{ \App\Models\Setting::getVal('site_about_mission', 'تقديم برامج تعليمية وتربوية وتنموية متكاملة تساعد الأطفال والشباب على تطوير قدراتهم العلمية والشخصية والمهنية، في بيئة إيمانية آمنة، وبإشراف كوادر مؤهلة، مع تعزيز روح المبادرة والابتكار وثقافة العمل الحر.') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- Goals --}}
<section class="py-24 bg-cream">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 reveal">
            <span class="text-gold text-sm font-medium tracking-widest uppercase">أهدافنا</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-dark mt-3">أهداف الجمعية</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
            @php $goals = [
                ['تعليم القرآن الكريم', 'تحفيظ القرآن وتجويده وتفسيره، وتعليم الفقه والعقيدة والسيرة النبوية', 'book'],
                ['التربية الإيمانية', 'غرس القيم الإسلامية والأخلاق الحميدة في نفوس الناشئة', 'heart'],
                ['دعم التفوق العلمي', 'تقديم دورات تعليمية وتقوية في المواد الأكاديمية', 'academic'],
                ['تنمية ريادة الأعمال', 'تدريب الشباب على تحويل الأفكار إلى مشاريع والتخطيط المالي', 'chart'],
                ['الأنشطة الشبابية', 'تنظيم رحلات ومخيمات وأنشطة رياضية وثقافية واجتماعية', 'users'],
                ['تعزيز العمل التطوعي', 'تشجيع الشباب على المشاركة المجتمعية والعمل التطوعي', 'globe'],
            ]; @endphp
            @foreach ($goals as $i => $g)
            <div class="bg-white rounded-2xl p-6 border border-dark/5 card-hover reveal" style="animation-delay:{{ $i * 0.05 }}s">
                <div class="w-10 h-10 rounded-xl bg-fajr/10 flex items-center justify-center text-fajr mb-4">
                    @switch($g[2])
                        @case('book') <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg> @break
                        @case('heart') <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg> @break
                        @case('academic') <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2"/></svg> @break
                        @case('chart') <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg> @break
                        @case('users') <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg> @break
                        @case('globe') <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> @break
                    @endswitch
                </div>
                <h4 class="font-bold text-dark mb-2">{{ $g[0] }}</h4>
                <p class="text-dark/50 text-sm">{{ $g[1] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Organizational Structure --}}
<section class="py-24 bg-dark relative overflow-hidden">
    <div class="absolute inset-0 pattern-dots opacity-10"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16 reveal">
            <span class="text-gold text-sm font-medium tracking-widest uppercase">الهيكل التنظيمي</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-white mt-3">الهيكل التنظيمي للجمعية</h2>
        </div>
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-12 reveal">
                <div class="inline-block px-8 py-4 rounded-2xl bg-gold/10 border border-gold/20 text-gold font-bold text-lg">رئيس الجمعية</div>
            </div>
            <div class="grid md:grid-cols-3 gap-4 mb-8 reveal">
                <div class="bg-white/5 border border-white/10 rounded-2xl p-6 text-center">
                    <div class="text-gold text-sm font-medium mb-1">نائب الرئيس</div>
                    <div class="text-white/40 text-xs">المساعد والإدارة</div>
                </div>
                <div class="bg-white/5 border border-white/10 rounded-2xl p-6 text-center">
                    <div class="text-gold text-sm font-medium mb-1">أمين السر</div>
                    <div class="text-white/40 text-xs">التوثيق والسجلات</div>
                </div>
                <div class="bg-white/5 border border-white/10 rounded-2xl p-6 text-center">
                    <div class="text-gold text-sm font-medium mb-1">أمين المال</div>
                    <div class="text-white/40 text-xs">الشؤون المالية</div>
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4 reveal">
                @php $committees = [
                    ['اللجنة التعليمية', 'الإشراف على دار الإقراء والحلقات والدورات'],
                    ['اللجنة التربوية', 'تنظيم الأنشطة والفعاليات للأطفال والشباب'],
                    ['لجنة ريادة الأعمال', 'تطوير برامج المهارات الريادية ودعم المشاريع'],
                    ['لجنة العلاقات العامة', 'التواصل مع المجتمع والإعلام والتسويق'],
                    ['لجنة الشؤون المالية', 'إدارة الموارد المالية والتبرعات'],
                ]; @endphp
                @foreach ($committees as $c)
                <div class="bg-white/5 border border-white/10 rounded-2xl p-5 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-fajr/20 flex items-center justify-center text-fajr-light shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-white font-medium text-sm">{{ $c[0] }}</h4>
                        <p class="text-white/40 text-xs mt-1">{{ $c[1] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection