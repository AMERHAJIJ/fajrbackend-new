<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'جمعية الفجر للتربية والتعليم وريادة الأعمال')</title>
    <meta name="description" content="@yield('description', 'جمعية الفجر للتربية والتعليم وريادة الأعمال - كايا شهير، باشاك شهير، إسطنبول')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;600;700&family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        .nav-link { position: relative; transition: color 0.3s; }
        .nav-link::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 0; height: 2px; background: #C5A059; transition: width 0.3s; border-radius: 1px; }
        .nav-link:hover::after, .nav-link.active::after { width: 100%; }
        .hero-gradient { background: linear-gradient(135deg, #0a1628 0%, #00667E 40%, #0a1628 100%); position: relative; overflow: hidden; }
        .hero-gradient::before { content: ''; position: absolute; inset: 0; background: url("data:image/svg+xml,%3Csvg width='60' height='60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 5L35 20H50L38 30L42 45L30 35L18 45L22 30L10 20H25Z' fill='rgba(255,255,255,0.03)'/%3E%3C/svg%3E") repeat; opacity: 0.3; }
        .hero-gradient::after { content: ''; position: absolute; top: -50%; right: -20%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(114,203,209,0.15) 0%, transparent 70%); border-radius: 50%; pointer-events: none; }
        .gold-glow { text-shadow: 0 0 40px rgba(197, 160, 89, 0.3); }
        .card-hover { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .card-hover:hover { transform: translateY(-8px); box-shadow: 0 20px 60px rgba(0, 102, 126, 0.15); }
        .section-divider { height: 1px; background: linear-gradient(90deg, transparent, rgba(197, 160, 89, 0.3), transparent); }
        .reveal { opacity: 0; transform: translateY(30px); transition: all 0.7s ease-out; }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .counter-value { display: inline-block; }
        .pattern-dots { background-image: radial-gradient(rgba(197, 160, 89, 0.15) 1px, transparent 1px); background-size: 20px 20px; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0F172A; }
        ::-webkit-scrollbar-thumb { background: #00667E; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #C5A059; }
        .program-card:nth-child(1) { --accent: #00667E; }
        .program-card:nth-child(2) { --accent: #C5A059; }
        .program-card:nth-child(3) { --accent: #72CBD1; }
        .program-card:nth-child(4) { --accent: #0a1628; }
        .team-card:hover .team-img { transform: scale(1.05); }
        .team-img { transition: transform 0.6s ease-out; }
        .phase-line { position: relative; }
        .phase-line::before { content: ''; position: absolute; right: 23px; top: 50px; bottom: -50px; width: 2px; background: linear-gradient(to bottom, #00667E, #72CBD1); }
        .phase-line:last-child::before { display: none; }
        .floating { animation: float 4s ease-in-out infinite; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-400 { animation-delay: 0.4s; }
        .delay-600 { animation-delay: 0.6s; }
    </style>
</head>
<body class="bg-cream text-dark antialiased">
    @include('site.partials.header')
    <main>
        @yield('content')
    </main>
    @include('site.partials.footer')
    <script>
        // Scroll reveal
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

        // Header scroll effect
        let lastScroll = 0;
        const header = document.querySelector('header');
        window.addEventListener('scroll', () => {
            const current = window.scrollY;
            if (current > 100) {
                header.classList.add('shadow-lg', 'bg-dark/95');
                header.classList.remove('bg-transparent');
                if (current > lastScroll && current > 200) {
                    header.style.transform = 'translateY(-100%)';
                } else {
                    header.style.transform = 'translateY(0)';
                }
            } else {
                header.classList.remove('shadow-lg', 'bg-dark/95');
                header.classList.add('bg-transparent');
            }
            lastScroll = current;
        });

        // Counter animation
        function animateCounters() {
            document.querySelectorAll('.counter-value').forEach(el => {
                const target = parseInt(el.dataset.target);
                const duration = 2000;
                const step = target / (duration / 16);
                let current = 0;
                const update = () => {
                    current += step;
                    if (current < target) {
                        el.textContent = Math.round(current);
                        requestAnimationFrame(update);
                    } else {
                        el.textContent = target;
                    }
                };
                update();
            });
        }
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    counterObserver.disconnect();
                }
            });
        });
        const counterSection = document.querySelector('.counter-section');
        if (counterSection) counterObserver.observe(counterSection);

        // Mobile menu
        document.querySelector('.menu-btn')?.addEventListener('click', function() {
            document.querySelector('.mobile-menu').classList.toggle('hidden');
            document.querySelector('.mobile-menu').classList.toggle('flex');
        });
    </script>
    @stack('scripts')
</body>
</html>