@extends('site.layouts.app')
@section('title', 'فريقنا - جمعية الفجر')
@section('description', 'تعرف على فريق جمعية الفجر: الشيخ عبد القادر السقا والكوادر التعليمية والإدارية')

@section('content')
<section class="hero-gradient pt-32 pb-20 relative overflow-hidden">
    <div class="absolute inset-0"><div class="absolute top-1/4 left-1/3 w-80 h-80 bg-fajr-light/5 rounded-full blur-3xl"></div></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10 text-gold text-sm mb-6">فريقنا</div>
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-4">فريق جمعية الفجر</h1>
        <p class="text-white/50 text-lg max-w-2xl mx-auto">كوادر مؤهلة ومخلصة تعمل على تحقيق رسالة الجمعية</p>
    </div>
</section>

{{-- Key Person --}}
<section class="py-24 bg-cream">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <div class="grid md:grid-cols-3 gap-10 items-center reveal">
                <div class="md:col-span-1">
                    <div class="aspect-square rounded-3xl bg-gradient-to-br from-fajr/20 to-gold/10 border border-white/50 flex items-center justify-center">
                        <svg class="w-20 h-20 text-fajr/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <span class="text-gold text-sm font-medium tracking-widest uppercase">الشيخ</span>
                    <h2 class="text-3xl font-bold text-dark mt-2 mb-3">عبد القادر السقا</h2>
                    <p class="text-fajr font-medium mb-4">إمام وخطيب في مركز الفجر – باشاك شهير</p>
                    <p class="text-dark/50 leading-relaxed">شيخ فاضل له حضور دعوي وتعليمي بارز في منطقة كايا شهير. يشرف على البرامج الشرعية والدروس العلمية في الجمعية، وله لقاءات ومجالس علمية ودروس في الفقه والعقيدة والتفسير.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Committees --}}
<section class="py-24 bg-dark relative overflow-hidden">
    <div class="absolute inset-0 pattern-dots opacity-10"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16 reveal">
            <span class="text-gold text-sm font-medium tracking-widest uppercase">اللجان العاملة</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-white mt-3">الكوادر الإدارية والتعليمية</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @php $team = [
                ['الشؤون التعليمية', 'الإشراف على دار الإقراء والحلقات والدورات التعليمية', 'book'],
                ['الشؤون التربوية', 'تنظيم الأنشطة والفعاليات للأطفال والشباب', 'heart'],
                ['ريادة الأعمال', 'تطوير برامج المهارات الريادية ودعم المشاريع', 'chart'],
                ['العلاقات العامة', 'التواصل مع المجتمع والإعلام والتسويق', 'globe'],
                ['الشؤون المالية', 'إدارة الموارد المالية والتبرعات', 'cash'],
                ['لجنة المرأة', 'برامج وأنشطة خاصة بالنساء والفتيات', 'users'],
            ]; @endphp
            @foreach ($team as $i => $t)
            <div class="bg-white/5 border border-white/10 rounded-3xl p-8 card-hover reveal text-center" style="animation-delay:{{ $i * 0.08 }}s">
                <div class="w-14 h-14 rounded-2xl mx-auto mb-5 flex items-center justify-center" style="background: color-mix(in srgb, var(--accent, #00667E) 15%, transparent); color: var(--accent, #00667E);">
                    @switch($t[2])
                        @case('book') <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg> @break
                        @case('heart') <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg> @break
                        @case('chart') <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg> @break
                        @case('globe') <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> @break
                        @case('cash') <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> @break
                        @case('users') <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg> @break
                    @endswitch
                </div>
                <h3 class="text-white font-bold text-lg mb-2">{{ $t[0] }}</h3>
                <p class="text-white/50 text-sm leading-relaxed">{{ $t[1] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection