<header class="fixed top-0 left-0 right-0 z-50 transition-all duration-500 bg-transparent" dir="rtl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <a href="{{ route('site.home') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-fajr to-fajr-light flex items-center justify-center text-white font-bold text-lg group-hover:scale-110 transition-transform duration-300">ف</div>
                <div class="hidden sm:block">
                    <span class="text-white font-bold text-lg block leading-tight">جمعية الفجر</span>
                    <span class="text-gold text-xs block">للتربية والتعليم وريادة الأعمال</span>
                </div>
            </a>
            <nav class="hidden lg:flex items-center gap-8">
                <a href="{{ route('site.home') }}" class="nav-link text-white/80 hover:text-white text-sm font-medium {{ request()->routeIs('site.home') ? 'active text-gold' : '' }}">الرئيسية</a>
                <a href="{{ route('site.about') }}" class="nav-link text-white/80 hover:text-white text-sm font-medium {{ request()->routeIs('site.about') ? 'active text-gold' : '' }}">من نحن</a>
                <a href="{{ route('site.programs') }}" class="nav-link text-white/80 hover:text-white text-sm font-medium {{ request()->routeIs('site.programs') ? 'active text-gold' : '' }}">برامجنا</a>
                <a href="{{ route('site.entrepreneurship') }}" class="nav-link text-white/80 hover:text-white text-sm font-medium {{ request()->routeIs('site.entrepreneurship') ? 'active text-gold' : '' }}">ريادة الأعمال</a>
                <a href="{{ route('site.team') }}" class="nav-link text-white/80 hover:text-white text-sm font-medium {{ request()->routeIs('site.team') ? 'active text-gold' : '' }}">فريقنا</a>
                <a href="{{ route('site.gallery') }}" class="nav-link text-white/80 hover:text-white text-sm font-medium {{ request()->routeIs('site.gallery') ? 'active text-gold' : '' }}">معرض الصور</a>
                <a href="{{ route('site.contact') }}" class="nav-link text-white/80 hover:text-white text-sm font-medium {{ request()->routeIs('site.contact') ? 'active text-gold' : '' }}">اتصل بنا</a>
            </nav>
            <div class="flex items-center gap-4">
                <a href="{{ url('/admin') }}" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-white/20 text-white/80 hover:text-white hover:border-gold/50 text-sm transition-all duration-300">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    دخول
                </a>
                <button class="menu-btn lg:hidden w-10 h-10 flex items-center justify-center rounded-xl border border-white/20 text-white">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
    </div>
    <div class="mobile-menu hidden lg:hidden flex-col bg-dark/98 backdrop-blur-xl border-t border-white/10">
        <div class="px-4 py-4 space-y-2">
            <a href="{{ route('site.home') }}" class="block px-4 py-3 rounded-xl text-white/80 hover:text-white hover:bg-white/5 transition text-sm">الرئيسية</a>
            <a href="{{ route('site.about') }}" class="block px-4 py-3 rounded-xl text-white/80 hover:text-white hover:bg-white/5 transition text-sm">من نحن</a>
            <a href="{{ route('site.programs') }}" class="block px-4 py-3 rounded-xl text-white/80 hover:text-white hover:bg-white/5 transition text-sm">برامجنا</a>
            <a href="{{ route('site.entrepreneurship') }}" class="block px-4 py-3 rounded-xl text-white/80 hover:text-white hover:bg-white/5 transition text-sm">ريادة الأعمال</a>
            <a href="{{ route('site.team') }}" class="block px-4 py-3 rounded-xl text-white/80 hover:text-white hover:bg-white/5 transition text-sm">فريقنا</a>
            <a href="{{ route('site.gallery') }}" class="block px-4 py-3 rounded-xl text-white/80 hover:text-white hover:bg-white/5 transition text-sm">معرض الصور</a>
            <a href="{{ route('site.contact') }}" class="block px-4 py-3 rounded-xl text-white/80 hover:text-white hover:bg-white/5 transition text-sm">اتصل بنا</a>
            <a href="{{ url('/admin') }}" class="block px-4 py-3 rounded-xl bg-fajr/20 text-gold text-sm mt-2">دخول الإدارة</a>
        </div>
    </div>
</header>