@extends('site.layouts.app')
@section('title', 'جمعية الفجر - الرئيسية')
@section('description', 'جمعية الفجر للتربية والتعليم وريادة الأعمال - تعليم القرآن الكريم والعلم الشرعي وريادة الأعمال في كايا شهير، إسطنبول')

@section('content')
{{-- Hero --}}
<section class="hero-gradient min-h-screen flex items-center relative overflow-hidden">
    <div class="absolute inset-0">
        <div class="absolute top-1/4 left-1/4 w-72 h-72 bg-fajr-light/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-gold/5 rounded-full blur-3xl"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-32 relative z-10 w-full">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="text-center lg:text-right">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10 text-gold text-sm mb-6 reveal">
                    <span class="w-2 h-2 bg-gold rounded-full animate-pulse"></span>
                    {{ \App\Models\Setting::getVal('site_home_hero_badge', 'Fecir Eğitim, Öğretim ve Girişimcilik Derneği') }}
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white leading-tight mb-6 reveal" style="animation-delay:0.1s">
                    {{ \App\Models\Setting::getVal('site_home_hero_title_1', 'جمعية') }} <span class="text-gold gold-glow">{{ \App\Models\Setting::getVal('site_home_hero_title_highlight', 'الفجر') }}</span>
                    <br>{!! \App\Models\Setting::getVal('site_home_hero_title_2', 'للتربية والتعليم<br>وريادة الأعمال') !!}
                </h1>
                <p class="text-white/60 text-lg leading-relaxed max-w-xl lg:mx-0 mb-8 reveal" style="animation-delay:0.2s">
                    {{ \App\Models\Setting::getVal('site_home_hero_text', 'جمعية خيرية غير ربحية، تشرف على دار إقراء للقرآن الكريم والعلم الشرعي والتربية الإيمانية، وتنمية مهارات الشباب وريادة الأعمال في كايا شهير – باشاك شهير، إسطنبول.') }}
                </p>
                <div class="flex flex-wrap gap-4 reveal" style="animation-delay:0.3s">
                    <a href="{{ route('site.about') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-l from-fajr to-fajr-light text-white font-medium hover:shadow-lg hover:shadow-fajr/25 transition-all duration-300">
                        تعرف علينا
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                    <a href="{{ route('site.contact') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl border border-white/20 text-white hover:bg-white/5 transition-all duration-300">
                        تواصل معنا
                    </a>
                </div>
            </div>
            <div class="hidden lg:flex justify-center reveal" style="animation-delay:0.4s">
                <div class="relative">
                    <div class="w-80 h-80 rounded-full bg-gradient-to-br from-fajr/30 to-gold/20 animate-float flex items-center justify-center">
                        <div class="w-64 h-64 rounded-full bg-gradient-to-br from-fajr/40 to-gold/30 flex items-center justify-center">
                            <div class="text-center">
                                <div class="text-7xl font-bold text-white font-amiri">ف</div>
                                <div class="text-gold text-sm mt-2">جمعية الفجر</div>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -top-4 -right-4 w-16 h-16 rounded-2xl bg-gold/10 border border-gold/20 flex items-center justify-center text-gold">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <div class="absolute -bottom-2 -left-2 w-20 h-20 rounded-2xl bg-fajr/10 border border-fajr-light/20 flex items-center justify-center text-fajr-light">
                        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Stats --}}
<section class="counter-section py-20 bg-dark relative overflow-hidden">
    <div class="absolute inset-0 pattern-dots opacity-30"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            @php $stats = json_decode(\App\Models\Setting::getVal('site_stats', '[{"value":"12+","label":"عاماً من العطاء"},{"value":"500+","label":"طالب وطالبة"},{"value":"30+","label":"برنامج تعليمي"},{"value":"5.0","label":"تقييم الجمعية"}]'), true); @endphp
            @foreach ($stats as $i => $s)
            <div class="text-center reveal" style="animation-delay:{{ $i * 0.1 }}s">
                <div class="text-4xl sm:text-5xl font-bold text-gold font-amiri counter-value" data-target="{{ preg_replace('/[^0-9.]/', '', $s['value']) }}">{{ $s['value'] }}</div>
                <div class="text-white/50 text-sm mt-2">{{ $s['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- About Preview --}}
<section class="py-24 bg-cream">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <div class="reveal">
                <span class="text-gold text-sm font-medium tracking-widest uppercase">عن الجمعية</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-dark mt-3 mb-6">نحو جيل متعلم<br>مؤهل ومبدع</h2>
                <p class="text-dark/60 leading-relaxed mb-6">
                    {{ \App\Models\Setting::getVal('site_about_intro_1', 'جمعية الفجر للتربية والتعليم وريادة الأعمال هي جمعية خيرية غير ربحية، تجمع في هويتها بين التربية والتعليم وريادة الأعمال، إيماناً بأن بناء الفرد المتكامل علمياً وإيمانياً ومهارياً هو أساس النهضة المجتمعية.') }}
                </p>
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-fajr/10 flex items-center justify-center text-fajr shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-dark/70 text-sm">تعليم القرآن</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gold/10 flex items-center justify-center text-gold shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-dark/70 text-sm">العلم الشرعي</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-fajr-light/10 flex items-center justify-center text-fajr-light shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-dark/70 text-sm">تربية إيمانية</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-dark/5 flex items-center justify-center text-dark/60 shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-dark/70 text-sm">ريادة الأعمال</span>
                    </div>
                </div>
                <a href="{{ route('site.about') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-dark/20 text-dark hover:bg-dark hover:text-white transition-all duration-300 text-sm">
                    اقرأ المزيد
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
            </div>
            <div class="relative reveal" style="animation-delay:0.2s">
                <div class="aspect-[4/3] rounded-3xl bg-gradient-to-br from-fajr/20 to-gold/10 border border-white/50 flex items-center justify-center">
                    <div class="text-center p-8">
                        <div class="w-20 h-20 mx-auto rounded-2xl bg-fajr/10 flex items-center justify-center text-fajr mb-4">
                            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-dark">رسالتنا</h3>
                        <p class="text-dark/50 text-sm mt-2 max-w-sm mx-auto">{{ \App\Models\Setting::getVal('site_about_mission', 'تقديم برامج تعليمية وتربوية وتنموية متكاملة تساعد الأطفال والشباب على تطوير قدراتهم العلمية والشخصية والمهنية.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Programs Preview --}}
<section class="py-24 bg-dark relative overflow-hidden">
    <div class="absolute inset-0 pattern-dots opacity-10"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16 reveal">
            <span class="text-gold text-sm font-medium tracking-widest uppercase">برامجنا</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-white mt-3">برامجنا التعليمية والتربوية</h2>
            <p class="text-white/50 mt-4 max-w-2xl mx-auto">نقدم مجموعة متكاملة من البرامج التعليمية والتربوية والريادية التي تلبي احتياجات الأطفال والشباب.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @php $programs = [
                ['القرآن الكريم', 'تعليم القرآن الكريم تحفيظاً وتجويداً وتفسيراً', 'fas fa-book-quran'],
                ['العلم الشرعي', 'دروس في الفقه والعقيدة والسيرة والحديث', 'fas fa-mosque'],
                ['التربية الإيمانية', 'برامج تربوية للأطفال والشباب والفتيات', 'fas fa-heart'],
                ['ريادة الأعمال', 'تدريب على القيادة والإدارة وتطوير المشاريع', 'fas fa-chart-line'],
            ]; @endphp
            @foreach ($programs as $i => $p)
            <div class="program-card group relative bg-white/5 border border-white/10 rounded-3xl p-8 card-hover cursor-default" style="animation-delay:{{ $i * 0.1 }}s">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl mb-5" style="background: color-mix(in srgb, var(--accent) 15%, transparent); color: var(--accent);">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        @switch($i)
                            @case(0) <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/> @break
                            @case(1) <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/> @break
                            @case(2) <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/> @break
                            @case(3) <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/> @break
                        @endswitch
                    </svg>
                </div>
                <h3 class="text-white font-bold text-lg mb-3">{{ $p[0] }}</h3>
                <p class="text-white/50 text-sm leading-relaxed">{{ $p[1] }}</p>
                <div class="absolute bottom-0 left-0 right-0 h-1 rounded-b-3xl" style="background: var(--accent); opacity: 0; transition: opacity 0.3s;"></div>
                <div class="group-hover:opacity-100 opacity-0 transition-opacity duration-300" style="position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: var(--accent); border-radius: 0 0 24px 24px;"></div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-12 reveal">
            <a href="{{ route('site.programs') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-l from-fajr to-fajr-light text-white font-medium hover:shadow-lg hover:shadow-fajr/25 transition-all duration-300">
                عرض جميع البرامج
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- Call to Action --}}
<section class="py-24 bg-cream">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center reveal">
        <div class="bg-gradient-to-br from-fajr to-dark rounded-3xl p-12 sm:p-16 relative overflow-hidden">
            <div class="absolute -top-20 -right-20 w-60 h-60 bg-gold/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 w-60 h-60 bg-fajr-light/10 rounded-full blur-3xl"></div>
            <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4 relative z-10">انضم إلى جمعية الفجر</h2>
            <p class="text-white/60 max-w-lg mx-auto mb-8 relative z-10">كن جزءاً من رحلتنا في بناء جيل متعلم ومؤهل يجمع بين القيم الإسلامية الراسخة ومهارات العصر.</p>
            <div class="flex flex-wrap justify-center gap-4 relative z-10">
                <a href="{{ route('site.contact') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gold text-dark font-medium hover:bg-gold/90 transition-all duration-300">
                    تواصل معنا
                </a>
                <a href="{{ route('site.about') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl border border-white/20 text-white hover:bg-white/5 transition-all duration-300">
                    تعرف علينا أكثر
                </a>
            </div>
        </div>
    </div>
</section>
@endsection