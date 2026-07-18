@extends('site.layouts.app')
@section('title', 'برامجنا - جمعية الفجر')
@section('description', 'برامج جمعية الفجر: تعليم القرآن الكريم، العلوم الشرعية، التربية الإيمانية، الأنشطة الشبابية، وريادة الأعمال')

@section('content')
<section class="hero-gradient pt-32 pb-20 relative overflow-hidden">
    <div class="absolute inset-0"><div class="absolute top-1/3 left-1/4 w-96 h-96 bg-fajr-light/5 rounded-full blur-3xl"></div></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10 text-gold text-sm mb-6">برامجنا</div>
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-4">برامجنا التعليمية</h1>
        <p class="text-white/50 text-lg max-w-2xl mx-auto">مجموعة متكاملة من البرامج التعليمية والتربوية والريادية التي تلبي احتياجات الأطفال والشباب</p>
    </div>
</section>

<section class="py-24 bg-cream">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-20">
        {{-- Quran --}}
        <div class="grid lg:grid-cols-2 gap-12 items-center reveal">
            <div>
                <span class="text-gold text-sm font-medium tracking-widest uppercase">البرنامج الأول</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-dark mt-3 mb-6">القرآن الكريم والعلوم الشرعية</h2>
                <ul class="space-y-4">
                    <li class="flex items-start gap-3"><div class="w-6 h-6 rounded-lg bg-fajr/10 flex items-center justify-center text-fajr shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div><span class="text-dark/60">دار إقراء لتحفيظ القرآن الكريم للأطفال والكبار</span></li>
                    <li class="flex items-start gap-3"><div class="w-6 h-6 rounded-lg bg-fajr/10 flex items-center justify-center text-fajr shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div><span class="text-dark/60">دروس في العلوم الشرعية: الفقه، التفسير، الحديث، السيرة</span></li>
                    <li class="flex items-start gap-3"><div class="w-6 h-6 rounded-lg bg-fajr/10 flex items-center justify-center text-fajr shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div><span class="text-dark/60">دورات تقوية في المواد الأكاديمية (لغة عربية، رياضيات، علوم)</span></li>
                    <li class="flex items-start gap-3"><div class="w-6 h-6 rounded-lg bg-fajr/10 flex items-center justify-center text-fajr shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div><span class="text-dark/60">تعليم اللغة العربية للناطقين بغيرها</span></li>
                </ul>
            </div>
            <div class="aspect-[4/3] rounded-3xl bg-gradient-to-br from-fajr/20 to-gold/10 border border-white/50 flex items-center justify-center">
                <div class="text-8xl text-fajr/30 font-amiri">﷽</div>
            </div>
        </div>

        <div class="section-divider"></div>

        {{-- Tarbiya --}}
        <div class="grid lg:grid-cols-2 gap-12 items-center reveal">
            <div class="order-last lg:order-first aspect-[4/3] rounded-3xl bg-gradient-to-br from-gold/20 to-fajr/10 border border-white/50 flex items-center justify-center">
                <div class="w-20 h-20 rounded-2xl bg-gold/20 flex items-center justify-center text-gold">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
            </div>
            <div>
                <span class="text-gold text-sm font-medium tracking-widest uppercase">البرنامج الثاني</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-dark mt-3 mb-6">التربية الإيمانية والأخلاقية</h2>
                <ul class="space-y-4">
                    <li class="flex items-start gap-3"><div class="w-6 h-6 rounded-lg bg-gold/10 flex items-center justify-center text-gold shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div><span class="text-dark/60">حلقات تحفيظ القرآن ومجالس العلم</span></li>
                    <li class="flex items-start gap-3"><div class="w-6 h-6 rounded-lg bg-gold/10 flex items-center justify-center text-gold shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div><span class="text-dark/60">أنشطة أسبوعية للأطفال (ترفيهية + تعليمية)</span></li>
                    <li class="flex items-start gap-3"><div class="w-6 h-6 rounded-lg bg-gold/10 flex items-center justify-center text-gold shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div><span class="text-dark/60">برامج تربية إيمانية للشباب والفتيات</span></li>
                    <li class="flex items-start gap-3"><div class="w-6 h-6 rounded-lg bg-gold/10 flex items-center justify-center text-gold shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div><span class="text-dark/60">ندوات ومحاضرات أسبوعية كل خميس مع نخبة من المشايخ</span></li>
                </ul>
            </div>
        </div>

        <div class="section-divider"></div>

        {{-- Social --}}
        <div class="grid lg:grid-cols-2 gap-12 items-center reveal">
            <div>
                <span class="text-gold text-sm font-medium tracking-widest uppercase">البرنامج الثالث</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-dark mt-3 mb-6">الأنشطة الاجتماعية والثقافية</h2>
                <ul class="space-y-4">
                    <li class="flex items-start gap-3"><div class="w-6 h-6 rounded-lg bg-fajr-light/20 flex items-center justify-center text-fajr-light shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div><span class="text-dark/60">رحلات ترفيهية وتعليمية شهرية</span></li>
                    <li class="flex items-start gap-3"><div class="w-6 h-6 rounded-lg bg-fajr-light/20 flex items-center justify-center text-fajr-light shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div><span class="text-dark/60">مخيمات صيفية للأطفال والشباب</span></li>
                    <li class="flex items-start gap-3"><div class="w-6 h-6 rounded-lg bg-fajr-light/20 flex items-center justify-center text-fajr-light shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div><span class="text-dark/60">فعاليات ثقافية ومناسبات إسلامية</span></li>
                    <li class="flex items-start gap-3"><div class="w-6 h-6 rounded-lg bg-fajr-light/20 flex items-center justify-center text-fajr-light shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div><span class="text-dark/60">أيام مفتوحة وأنشطة رياضية ومنافسات ثقافية</span></li>
                </ul>
            </div>
            <div class="aspect-[4/3] rounded-3xl bg-gradient-to-br from-fajr-light/20 to-fajr/10 border border-white/50 flex items-center justify-center">
                <div class="w-20 h-20 rounded-2xl bg-fajr-light/20 flex items-center justify-center text-fajr-light">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="section-divider"></div>

        {{-- Entrepreneurship --}}
        <div class="grid lg:grid-cols-2 gap-12 items-center reveal">
            <div class="order-last lg:order-first aspect-[4/3] rounded-3xl bg-gradient-to-br from-dark/5 to-gold/10 border border-white/50 flex items-center justify-center">
                <div class="w-20 h-20 rounded-2xl bg-dark/10 flex items-center justify-center text-dark/60">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <div>
                <span class="text-gold text-sm font-medium tracking-widest uppercase">البرنامج الرابع</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-dark mt-3 mb-6">ريادة الأعمال</h2>
                <p class="text-dark/50 leading-relaxed mb-6">برنامج متكامل لتدريب الشباب على ريادة الأعمال، يشمل التفكير الإبداعي، كتابة خطط العمل، التسويق الرقمي، إدارة المشاريع الصغيرة، ودعم الأفكار الريادية لتحويلها إلى مشاريع حقيقية.</p>
                <a href="{{ route('site.entrepreneurship') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-dark text-white hover:bg-dark/80 transition-all duration-300 text-sm">
                    تفاصيل البرنامج
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection