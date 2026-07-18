@extends('site.layouts.app')
@section('title', 'معرض الصور - جمعية الفجر')
@section('description', 'معرض صور جمعية الفجر: نشاطات، رحلات، فعاليات، حلقات تحفيظ')

@section('content')
<section class="hero-gradient pt-32 pb-20 relative overflow-hidden">
    <div class="absolute inset-0"><div class="absolute top-1/2 left-1/3 w-80 h-80 bg-gold/5 rounded-full blur-3xl"></div></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10 text-gold text-sm mb-6">معرض الصور</div>
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-4">معرض الصور</h1>
        <p class="text-white/50 text-lg max-w-2xl mx-auto">لقطات من نشاطاتنا وفعالياتنا في جمعية الفجر</p>
    </div>
</section>

<section class="py-24 bg-cream">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 reveal">
            <h2 class="text-3xl sm:text-4xl font-bold text-dark">ألبوم الصور</h2>
            <p class="text-dark/50 mt-4 max-w-2xl mx-auto">مجموعة من الصور والفيديوهات التي توثق نشاطات الجمعية وفعالياتها المختلفة</p>
        </div>

        {{-- Categories --}}
        <div class="flex flex-wrap justify-center gap-3 mb-12 reveal">
            @php $categories = ['الكل', 'حلقات التحفيظ', 'الفعاليات', 'الرحلات', 'المحاضرات', 'الأطفال', 'الأنشطة']; @endphp
            @foreach ($categories as $i => $cat)
            <button class="px-5 py-2 rounded-xl border transition-all duration-300 text-sm {{ $i === 0 ? 'bg-fajr text-white border-fajr' : 'bg-white text-dark/60 border-dark/10 hover:border-fajr/30 hover:text-fajr' }}">
                {{ $cat }}
            </button>
            @endforeach
        </div>

        {{-- Gallery Grid --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
                $gallery = [
                    ['حلقة تحفيظ القرآن', 'book'],
                    ['فعالية ثقافية', 'heart'],
                    ['رحلة ترفيهية', 'globe'],
                    ['محاضرة علمية', 'users'],
                    ['نشاط أطفال', 'book'],
                    ['فعالية مجتمعية', 'heart'],
                ];
            @endphp
            @foreach ($gallery as $i => $g)
            <div class="group relative aspect-[4/3] rounded-3xl overflow-hidden bg-gradient-to-br from-fajr/20 to-gold/10 border border-white/50 card-hover reveal cursor-pointer" style="animation-delay:{{ $i * 0.08 }}s">
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-16 h-16 rounded-2xl bg-white/80 flex items-center justify-center text-fajr">
                        @if ($g[1] === 'book')
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        @elseif ($g[1] === 'heart')
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        @else
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                    </div>
                </div>
                <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-dark/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <div class="absolute bottom-4 right-4 text-white text-sm font-medium">{{ $g[0] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Videos Section --}}
<section class="py-24 bg-dark relative overflow-hidden">
    <div class="absolute inset-0 pattern-dots opacity-10"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16 reveal">
            <span class="text-gold text-sm font-medium tracking-widest uppercase">فيديوهات</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-white mt-3">فيديوهات تعليمية وتربوية</h2>
        </div>
        <div class="grid sm:grid-cols-2 gap-6">
            @php $videos = [
                ['اللقاء الرمضاني مع الشيخ عبد القادر السقا', 'رمضان'],
                ['درس في تفسير القرآن', 'تفسير'],
                ['محاضرة التربية الإيمانية', 'تربية'],
                ['فعالية اليوم المفتوح للأطفال', 'فعاليات'],
            ]; @endphp
            @foreach ($videos as $i => $v)
            <div class="bg-white/5 border border-white/10 rounded-3xl overflow-hidden card-hover reveal group" style="animation-delay:{{ $i * 0.1 }}s">
                <div class="aspect-video bg-gradient-to-br from-fajr/20 to-dark/50 flex items-center justify-center relative">
                    <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white mr-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </div>
                    <span class="absolute bottom-3 left-3 px-2 py-1 rounded-md bg-dark/60 text-white/80 text-xs">{{ $v[1] }}</span>
                </div>
                <div class="p-4">
                    <h4 class="text-white font-medium text-sm">{{ $v[0] }}</h4>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection