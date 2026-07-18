@extends('site.layouts.app')
@section('title', 'اتصل بنا - جمعية الفجر')
@section('description', 'تواصل مع جمعية الفجر: العنوان، الهاتف، البريد الإلكتروني، وطرق التواصل')

@section('content')
<section class="hero-gradient pt-32 pb-20 relative overflow-hidden">
    <div class="absolute inset-0"><div class="absolute top-1/3 left-1/4 w-96 h-96 bg-fajr-light/5 rounded-full blur-3xl"></div></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10 text-gold text-sm mb-6">اتصل بنا</div>
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-4">تواصل معنا</h1>
        <p class="text-white/50 text-lg max-w-2xl mx-auto">نحن هنا للإجابة على استفساراتكم واستقبال اقتراحاتكم</p>
    </div>
</section>

<section class="py-24 bg-cream">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16">
            {{-- Info --}}
            <div class="space-y-8 reveal">
                <div>
                    <span class="text-gold text-sm font-medium tracking-widest uppercase">معلومات الاتصال</span>
                    <h2 class="text-3xl sm:text-4xl font-bold text-dark mt-3">تفضل بزيارتنا أو اتصل بنا</h2>
                </div>

                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-fajr/10 flex items-center justify-center text-fajr shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-dark">العنوان</h4>
                            <p class="text-dark/50 text-sm mt-1">{!! \App\Models\Setting::getVal('site_contact_address', 'Kayabaşı Mah, Ulubatlı Hasan Cd, Dış Kapı No 2, C8-8 Dükkan<br>34480 Başakşehir/İstanbul') !!}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-gold/10 flex items-center justify-center text-gold shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-dark">الهاتف</h4>
                            <a href="tel:{{ \App\Models\Setting::getVal('site_contact_phone', '+905461500552') }}" dir="ltr" class="text-dark/50 text-sm mt-1 block hover:text-fajr transition">{{ \App\Models\Setting::getVal('site_contact_phone', '+90 546 150 05 52') }}</a>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-fajr-light/10 flex items-center justify-center text-fajr-light shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-dark">وسائل التواصل</h4>
                            <div class="flex flex-col gap-1 mt-1">
                                <span class="text-dark/50 text-sm">إنستغرام: {{ \App\Models\Setting::getVal('site_contact_instagram', '@_fecir1') }}</span>
                                <span class="text-dark/50 text-sm">تلغرام: {{ \App\Models\Setting::getVal('site_contact_telegram', 't.me/Fecir1') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-dark/5 flex items-center justify-center text-dark/60 shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-dark">ساعات العمل</h4>
                            <div class="text-dark/50 text-sm mt-1">
                                <p>{!! \App\Models\Setting::getVal('site_contact_hours', 'الإثنين – الجمعة: 10:00 – 18:00<br>السبت: 10:00 – 22:00<br>الأحد: مغلق') !!}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form --}}
            <div class="reveal" style="animation-delay:0.2s">
                <div class="bg-white rounded-3xl p-8 sm:p-10 border border-dark/5 shadow-sm">
                    <h3 class="text-xl font-bold text-dark mb-6">أرسل لنا رسالة</h3>
                    <form class="space-y-5" id="contactForm">
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-dark/70 mb-2">الاسم الكامل</label>
                                <input type="text" name="name" class="w-full px-4 py-3 rounded-xl border border-dark/10 bg-cream text-dark placeholder:text-dark/30 focus:outline-none focus:border-fajr/50 focus:ring-2 focus:ring-fajr/10 transition" placeholder="أدخل اسمك" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark/70 mb-2">البريد الإلكتروني</label>
                                <input type="email" name="email" class="w-full px-4 py-3 rounded-xl border border-dark/10 bg-cream text-dark placeholder:text-dark/30 focus:outline-none focus:border-fajr/50 focus:ring-2 focus:ring-fajr/10 transition" placeholder="بريدك الإلكتروني" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark/70 mb-2">رقم الهاتف</label>
                            <input type="tel" name="phone" class="w-full px-4 py-3 rounded-xl border border-dark/10 bg-cream text-dark placeholder:text-dark/30 focus:outline-none focus:border-fajr/50 focus:ring-2 focus:ring-fajr/10 transition" dir="ltr" placeholder="رقم هاتفك">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark/70 mb-2">الموضوع</label>
                            <input type="text" name="subject" class="w-full px-4 py-3 rounded-xl border border-dark/10 bg-cream text-dark placeholder:text-dark/30 focus:outline-none focus:border-fajr/50 focus:ring-2 focus:ring-fajr/10 transition" placeholder="عنوان الرسالة" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark/70 mb-2">الرسالة</label>
                            <textarea name="message" rows="5" class="w-full px-4 py-3 rounded-xl border border-dark/10 bg-cream text-dark placeholder:text-dark/30 focus:outline-none focus:border-fajr/50 focus:ring-2 focus:ring-fajr/10 transition resize-none" placeholder="اكتب رسالتك هنا..." required></textarea>
                        </div>
                        <button type="submit" class="w-full px-6 py-3 rounded-xl bg-gradient-to-l from-fajr to-fajr-light text-white font-medium hover:shadow-lg hover:shadow-fajr/25 transition-all duration-300">
                            إرسال الرسالة
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Map --}}
<section class="h-96 bg-dark relative overflow-hidden">
    <div class="absolute inset-0 flex items-center justify-center text-white/30">
        <div class="text-center">
            <svg class="w-12 h-12 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="text-sm">{!! nl2br(e(strip_tags(\App\Models\Setting::getVal('site_contact_address', 'Kayabaşı Mah, Ulubatlı Hasan Cd, No 2<br>Başakşehir/İstanbul')))) !!}</p>
            <a href="https://maps.google.com/?q=Kayabaşı+Mah+Ulubatlı+Hasan+Cd+Başakşehir+İstanbul" target="_blank" rel="noopener" class="inline-flex items-center gap-2 mt-4 px-4 py-2 rounded-xl bg-fajr text-white text-sm hover:bg-fajr/80 transition">
                فتح في Google Maps
            </a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.getElementById('contactForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    alert('شكراً لتواصلك معنا! سيتم الرد عليك في أقرب وقت ممكن.');
    this.reset();
});
</script>
@endpush