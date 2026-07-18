@extends('site.layouts.app')
@section('title', 'ريادة الأعمال - جمعية الفجر')
@section('description', 'برنامج ريادة الأعمال في جمعية الفجر: تدريب الشباب على تحويل الأفكار إلى مشاريع حقيقية')

@section('content')
<section class="hero-gradient pt-32 pb-20 relative overflow-hidden">
    <div class="absolute inset-0"><div class="absolute top-1/3 right-1/4 w-96 h-96 bg-gold/5 rounded-full blur-3xl"></div></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10 text-gold text-sm mb-6">ريادة الأعمال</div>
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-4 gold-glow">ريادة الأعمال</h1>
        <p class="text-white/50 text-lg max-w-2xl mx-auto">برنامج متكامل لتمكين الشباب من تحويل أفكارهم إلى مشاريع حقيقية</p>
    </div>
</section>

{{-- Intro --}}
<section class="py-24 bg-cream">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <div class="reveal">
                <span class="text-gold text-sm font-medium tracking-widest uppercase">ريادة الأعمال</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-dark mt-3 mb-6">لماذا ريادة الأعمال؟</h2>
                <p class="text-dark/60 leading-relaxed mb-6">وجود كلمة "ريادة الأعمال" ضمن اسم جمعيتنا يدل على اهتمامنا بتطوير الشباب وتأهيلهم لسوق العمل والمشاريع المستقبلية. نؤمن بأن كل شاب يملك فكرة يمكن أن تصبح مشروعاً ناجحاً، ودورنا هو توفير البيئة والتدريب والدعم اللازمين لتحقيق ذلك.</p>
                <div class="grid grid-cols-2 gap-4">
                    @php $items = [['تحويل الأفكار', 'إلى مشاريع'], ['تطوير القيادة', 'والإدارة'], ['التخطيط المالي', 'وإدارة المشاريع'], ['تشجيع', 'الابتكار والإبداع']]; @endphp
                    @foreach ($items as $item)
                    <div class="bg-white rounded-2xl p-5 border border-dark/5 text-center card-hover">
                        <div class="text-lg font-bold text-fajr">{{ $item[0] }}</div>
                        <div class="text-dark/40 text-sm">{{ $item[1] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="reveal" style="animation-delay:0.2s">
                <div class="aspect-square rounded-3xl bg-gradient-to-br from-gold/20 to-fajr/10 border border-white/50 flex items-center justify-center p-12">
                    <svg class="w-full h-full text-gold/30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 3 Phases --}}
<section class="py-24 bg-dark relative overflow-hidden">
    <div class="absolute inset-0 pattern-dots opacity-10"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16 reveal">
            <span class="text-gold text-sm font-medium tracking-widest uppercase">خطة العمل</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-white mt-3">خطة ريادة الأعمال 2026-2027</h2>
        </div>
        <div class="max-w-4xl mx-auto space-y-8">
            @php $phases = [
                ['1', 'التوعية والتحفيز', '3 أشهر', 'التعريف بريادة الأعمال وأهميتها للشباب. استضافة رواد أعمال ناجحين. تنظيم مسابقة "فكرة مشروعي"', 'bg-gold/20 text-gold border-gold/20'],
                ['2', 'التدريب والتأهيل', '6 أشهر', 'دورات في التفكير الإبداعي، كتابة خطط العمل، التسويق الرقمي، المحاسبة الأساسية. ورش عمل عملية.', 'bg-fajr/20 text-fajr-light border-fajr/20'],
                ['3', 'الاحتضان والدعم', 'مستمر', 'توفير الاستشارات والإرشاد المهني. ربط الشباب بفرص التمويل. متابعة وتقييم دوري.', 'bg-white/5 text-gold border-white/10'],
            ]; @endphp
            @foreach ($phases as $i => $p)
            <div class="phase-line bg-white/5 border border-white/10 rounded-3xl p-8 card-hover reveal" style="animation-delay:{{ $i * 0.15 }}s">
                <div class="flex items-start gap-6">
                    <div class="w-12 h-12 rounded-2xl {{ $p[4] }} flex items-center justify-center font-bold text-lg shrink-0">{{ $p[0] }}</div>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                            <h3 class="text-white font-bold text-xl">{{ $p[1] }}</h3>
                            <span class="px-3 py-1 rounded-full bg-white/5 border border-white/10 text-white/50 text-xs">{{ $p[2] }}</span>
                        </div>
                        <p class="text-white/50 leading-relaxed">{{ $p[3] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-24 bg-cream">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center reveal">
        <div class="bg-gradient-to-br from-fajr to-dark rounded-3xl p-12 sm:p-16 relative overflow-hidden">
            <div class="absolute -top-20 -left-20 w-60 h-60 bg-gold/10 rounded-full blur-3xl"></div>
            <h2 class="text-3xl font-bold text-white mb-4 relative z-10">عندك فكرة مشروع؟</h2>
            <p class="text-white/60 max-w-lg mx-auto mb-8 relative z-10">نحن هنا لنساعدك على تحويل فكرتك إلى مشروع ناجح. تواصل معنا وكن جزءاً من برنامج ريادة الأعمال.</p>
            <a href="{{ route('site.contact') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gold text-dark font-medium hover:bg-gold/90 transition-all duration-300 relative z-10">
                تواصل معنا الآن
            </a>
        </div>
    </div>
</section>
@endsection