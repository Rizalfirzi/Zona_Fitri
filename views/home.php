<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#042558">
    <meta name="description" content="Zona Fitri — Mau Ngopi? Ingat Fitri. Jasa order Kopi Kenangan, Tomoro Coffee, Fore Coffee & Janji Jiwa lebih hemat via WhatsApp.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Zona Fitri - Mau Ngopi? Ingat Fitri">
    <meta property="og:description" content="Pesan Kopi Kenangan, Tomoro, Fore & Janji Jiwa lebih hemat langsung via WhatsApp.">
    <meta property="og:image" content="/img/brand/zonafitri-logo.png">
    <title>Zona Fitri - Mau Ngopi? Ingat Fitri</title>
    <link rel="icon" href="/img/brand/zonafitri-logo.png" type="image/png">
    <link rel="apple-touch-icon" href="/img/brand/zonafitri-logo.png">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #06152d;
            -webkit-tap-highlight-color: transparent;
        }

        /* Custom Gradients matching the UI design */
        .hero-blue-gradient {
            background: radial-gradient(circle at 85% 20%, #1d63b8 0%, #06357a 55%, #031e49 100%);
        }
        
        .header-blue-gradient {
            background: linear-gradient(135deg, #042558 0%, #094293 60%, #1565c0 100%);
        }

        .banner-blue-gradient {
            background: linear-gradient(100deg, #042b69 0%, #0c4ea8 55%, #2b82e3 100%);
        }

        .btn-navy-gradient {
            background: linear-gradient(90deg, #042861 0%, #0d4ea6 100%);
        }

        /* Hide scrollbars */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Subtle floating animation for coffee cup */
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(6deg); }
            50% { transform: translateY(-6px) rotate(8deg); }
        }
        .animate-float {
            animation: float 4s ease-in-out infinite;
        }

        /* Card Shadow */
        .soft-card-shadow {
            box-shadow: 0 8px 24px -4px rgba(13, 62, 138, 0.08), 0 2px 6px -1px rgba(13, 62, 138, 0.04);
        }
    </style>
</head>
<body class="flex justify-center min-h-screen bg-slate-200">

    <!-- Mobile App Container -->
    <div id="app-container" class="w-full max-w-[430px] bg-[#f4f8fe] min-h-screen relative shadow-2xl overflow-x-hidden flex flex-col justify-between">

        <!-- ========================================================= -->
        <!-- HALAMAN 1: BERANDA (HOME SCREEN)                          -->
        <!-- ========================================================= -->
        <div id="home-view" class="flex-1 flex flex-col justify-between transition-opacity duration-300">
            
            <!-- Top Hero Section -->
            <div class="relative hero-blue-gradient pt-6 pb-20 px-5 text-white overflow-hidden">
                <!-- Decorative background wave overlays -->
                <div class="absolute -top-10 -right-10 w-64 h-64 rounded-full bg-blue-400/10 blur-xl pointer-events-none"></div>
                <div class="absolute bottom-4 left-10 w-48 h-48 rounded-full bg-sky-400/10 blur-lg pointer-events-none"></div>

                <!-- Brand Logo Pill -->
                <div class="relative z-10 inline-flex items-center bg-white rounded-full px-5 py-2.5 shadow-lg mb-5">
                    <div class="flex items-center text-2xl font-extrabold tracking-tight">
                        <span class="text-[#c59b6d]">z</span>
                        <!-- Custom stylized 'o' with pink swirl -->
                        <span class="relative inline-flex items-center justify-center mx-0.5 text-[#e91e63]">
                            o
                            <svg class="w-6 h-6 absolute -top-0.5 -left-0.5 text-[#e91e63]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <path d="M19 5c-3-2-9-1-13 4s-4 11-1 13c3 2 9 1 13-4" stroke-linecap="round"/>
                                <path d="M16 3l3 2-2 3" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="text-[#c59b6d]">na</span>
                        <span class="text-[#e91e63]">fitri</span>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="relative z-10 mb-3">
                    <span class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full border border-emerald-400/50 bg-emerald-950/40 backdrop-blur-sm text-emerald-300 text-xs font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Sedang Buka
                    </span>
                </div>

                <!-- Main Headline -->
                <div class="relative z-10 max-w-[62%]">
                    <h1 class="text-[34px] leading-[1.1] font-extrabold tracking-tight text-white">
                        Mau <span class="text-[#7cc4fa] italic font-extrabold relative inline-block">
                            Ngopi?
                            <!-- Underline stroke -->
                            <svg class="w-full h-2.5 absolute -bottom-2 left-0 text-[#3b9eff]" viewBox="0 0 100 12" fill="none">
                                <path d="M3 8C30 3 70 3 97 8" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </h1>
                    <p class="mt-3 text-lg font-medium text-blue-100/95">Ingat Fitri</p>
                </div>

                <!-- Floating Iced Coffee Cup Graphic (Right Side) -->
                <div class="absolute right-[-12px] bottom-2 w-[185px] h-[230px] z-20 pointer-events-none animate-float">
                    <!-- Splash lines -->
                    <svg class="w-full h-full overflow-visible" viewBox="0 0 200 240">
                        <!-- Splash droplets top left -->
                        <path d="M25 45 Q15 35 20 30 Q28 32 25 45 Z" fill="none" stroke="white" stroke-width="2"/>
                        <path d="M15 62 Q3 56 6 50 Q14 50 15 62 Z" fill="none" stroke="white" stroke-width="2"/>
                        <path d="M18 78 Q8 78 9 72 Q15 71 18 78 Z" fill="none" stroke="white" stroke-width="2"/>
                        
                        <!-- Coffee Bean Top Right -->
                        <g transform="translate(135, 10) rotate(25)">
                            <ellipse cx="14" cy="10" rx="13" ry="9" fill="#5c3317" stroke="#3b1e0a" stroke-width="1.5"/>
                            <path d="M3 10 Q14 14 25 10" stroke="#2a1204" stroke-width="2" fill="none"/>
                        </g>

                        <!-- Plastic Dome Lid -->
                        <path d="M42 52 C42 25, 148 25, 148 52 Z" fill="rgba(255,255,255,0.45)" stroke="rgba(255,255,255,0.85)" stroke-width="2"/>
                        <ellipse cx="95" cy="30" rx="18" ry="5" fill="rgba(255,255,255,0.6)"/>
                        <!-- Lid Rim -->
                        <rect x="35" y="50" width="120" height="8" rx="4" fill="rgba(255,255,255,0.85)"/>

                        <!-- Cup Body (Tapered) -->
                        <defs>
                            <linearGradient id="coffeeGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#e5b887"/>
                                <stop offset="65%" stop-color="#c68b53"/>
                                <stop offset="88%" stop-color="#6b3819"/>
                                <stop offset="100%" stop-color="#3d1c08"/>
                            </linearGradient>
                            <clipPath id="cupClip">
                                <polygon points="40,58 150,58 134,218 56,218"/>
                            </clipPath>
                        </defs>

                        <g clip-path="url(#cupClip)">
                            <!-- Coffee Liquid -->
                            <rect x="35" y="58" width="120" height="165" fill="url(#coffeeGrad)"/>
                            <!-- Ice cubes -->
                            <rect x="52" y="62" width="26" height="22" rx="5" fill="rgba(255,255,255,0.35)" transform="rotate(12 52 62)"/>
                            <rect x="85" y="60" width="28" height="24" rx="5" fill="rgba(255,255,255,0.3)" transform="rotate(-8 85 60)"/>
                            <rect x="112" y="66" width="24" height="20" rx="5" fill="rgba(255,255,255,0.35)" transform="rotate(18 112 66)"/>
                            <!-- Aren Brown Sugar Swirls at bottom -->
                            <path d="M50 185 Q75 170 95 190 T140 180 L140 225 L50 225 Z" fill="#3a1906" opacity="0.8"/>
                            <circle cx="72" cy="195" r="4" fill="#260e02"/>
                            <circle cx="110" cy="202" r="5" fill="#260e02"/>
                            <circle cx="90" cy="208" r="3" fill="#260e02"/>
                            <!-- Cup Highlight -->
                            <polygon points="46,58 58,58 68,218 60,218" fill="rgba(255,255,255,0.22)"/>
                        </g>

                        <!-- Cup Outline -->
                        <polygon points="40,58 150,58 134,218 56,218" fill="none" stroke="rgba(255,255,255,0.6)" stroke-width="1.5"/>

                        <!-- Zona Fitri Cup Label -->
                        <text x="95" y="142" text-anchor="middle" font-family="Plus Jakarta Sans, sans-serif" font-weight="800" font-size="16" fill="#e91e63" transform="rotate(-3 95 142)">
                            <tspan fill="#b67d46">zo</tspan>nafitri
                        </text>

                        <!-- Coffee Bean Bottom Left -->
                        <g transform="translate(10, 195) rotate(-20)">
                            <ellipse cx="15" cy="11" rx="14" ry="9.5" fill="#5c3317" stroke="#2d1506" stroke-width="1.5"/>
                            <path d="M3 11 Q15 15 27 11" stroke="#240f03" stroke-width="2" fill="none"/>
                        </g>

                        <!-- Coffee Bean Bottom Right -->
                        <g transform="translate(132, 182) rotate(32)">
                            <ellipse cx="15" cy="11" rx="14" ry="9.5" fill="#683a1b" stroke="#2d1506" stroke-width="1.5"/>
                            <path d="M3 11 Q15 15 27 11" stroke="#240f03" stroke-width="2" fill="none"/>
                        </g>
                    </svg>
                </div>

                <!-- Wavy Bottom Divider -->
                <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-none z-10">
                    <svg class="relative block w-full h-[38px]" viewBox="0 0 430 40" preserveAspectRatio="none">
                        <path d="M0,22 C110,4 220,38 330,20 C380,12 410,16 430,22 L430,40 L0,40 Z" fill="#f4f8fe"></path>
                    </svg>
                </div>
            </div>

            <!-- Brand Selection Section -->
            <div class="px-5 pt-2 pb-6 flex-1 z-20">
                <!-- Section Title with flanking lines -->
                <div class="flex items-center justify-center gap-3 mb-5">
                    <div class="h-[1.5px] flex-1 bg-blue-200/80 rounded-full"></div>
                    <div class="flex items-center gap-2 text-[#062d6b] font-extrabold text-[17px]">
                        <!-- Coffee Cup Icon -->
                        <svg class="w-5 h-5 text-[#0a439c]" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M4 6h13v7a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5V6zm13 2h2a2 2 0 0 1 2 2v1a2 2 0 0 1-2 2h-2V8zM3 19h15v2H3v-2z"/>
                        </svg>
                        <span>Pilih Brand Kopi</span>
                    </div>
                    <div class="h-[1.5px] flex-1 bg-blue-200/80 rounded-full"></div>
                </div>

                <!-- Brand Cards List -->
                <div class="space-y-3.5">
                    <!-- 1. Kopi Kenangan -->
                    <a href="/kopikenangan" class="group relative bg-white rounded-[26px] p-3.5 pl-6 soft-card-shadow flex items-center justify-between cursor-pointer overflow-hidden border border-white hover:border-blue-200 active:scale-[0.98] transition-all">
                        <!-- Left Colored Crescent Accent -->
                        <div class="absolute left-0 top-0 bottom-0 w-9 bg-gradient-to-r from-rose-200/80 to-rose-100/20 rounded-r-full pointer-events-none"></div>
                        
                        <div class="relative z-10 flex items-center gap-4">
                            <!-- Brand Logo Box -->
                            <div class="w-14 h-14 flex items-center justify-center shrink-0">
                                <svg class="w-11 h-11" viewBox="0 0 60 60" fill="none">
                                    <path d="M30 48C30 48 10 35 10 21C10 14 15.5 9 22 9C26 9 28.5 11 30 13.5C31.5 11 34 9 38 9C44.5 9 50 14 50 21C50 35 30 48 30 48Z" stroke="#e13b44" stroke-width="2.5" fill="none"/>
                                    <path d="M13 18L47 16M11 23L49 21M13 28L46 26M17 33L42 31M22 38L37 37M30 48V53" stroke="#e13b44" stroke-width="2.2" stroke-linecap="round"/>
                                </svg>
                            </div>
                            
                            <!-- Brand Info -->
                            <div>
                                <h3 class="font-extrabold text-[17px] text-[#052963] leading-tight group-hover:text-[#0b57d0] transition-colors">Kopi Kenangan</h3>
                                <p class="text-[12px] text-slate-500 font-medium mt-0.5">Untuk kamu, yang selalu spesial</p>
                            </div>
                        </div>

                        <!-- Right Blue Circle Chevron -->
                        <div class="relative z-10 w-10 h-10 rounded-full bg-[#e3effd] group-hover:bg-[#0b57d0] text-[#052963] group-hover:text-white flex items-center justify-center transition-colors shrink-0">
                            <i data-lucide="chevron-right" class="w-5 h-5 stroke-[2.5]"></i>
                        </div>
                    </a>

                    <!-- 2. Tomoro Coffee -->
                    <a href="/tomoro" class="group relative bg-white rounded-[26px] p-3.5 pl-6 soft-card-shadow flex items-center justify-between cursor-pointer overflow-hidden border border-white hover:border-blue-200 active:scale-[0.98] transition-all">
                        <div class="absolute left-0 top-0 bottom-0 w-9 bg-gradient-to-r from-orange-300/80 to-orange-100/20 rounded-r-full pointer-events-none"></div>
                        
                        <div class="relative z-10 flex items-center gap-4">
                            <div class="w-14 h-14 flex items-center justify-center shrink-0">
                                <svg class="w-11 h-11" viewBox="0 0 60 60" fill="none">
                                    <path d="M18 14L24 22H36L42 14V44C42 46 40 48 38 48H33V38C33 36 31.5 34 30 34C28.5 34 27 36 27 38V48H22C20 48 18 46 18 44V14Z" fill="#ff6a13"/>
                                    <circle cx="25" cy="27" r="1.5" fill="white"/>
                                    <circle cx="35" cy="27" r="1.5" fill="white"/>
                                    <path d="M42 44C46 44 48 40 47 36" stroke="#ff6a13" stroke-width="3" stroke-linecap="round"/>
                                </svg>
                            </div>
                            
                            <div>
                                <h3 class="font-extrabold text-[17px] text-[#052963] leading-tight group-hover:text-[#0b57d0] transition-colors">Tomoro Coffee</h3>
                                <p class="text-[12px] text-slate-500 font-medium mt-0.5">Good Coffee, Good Mood</p>
                            </div>
                        </div>

                        <div class="relative z-10 w-10 h-10 rounded-full bg-[#e3effd] group-hover:bg-[#0b57d0] text-[#052963] group-hover:text-white flex items-center justify-center transition-colors shrink-0">
                            <i data-lucide="chevron-right" class="w-5 h-5 stroke-[2.5]"></i>
                        </div>
                    </a>

                    <!-- 3. Fore Coffee -->
                    <a href="/fore" class="group relative bg-white rounded-[26px] p-3.5 pl-6 soft-card-shadow flex items-center justify-between cursor-pointer overflow-hidden border border-white hover:border-blue-200 active:scale-[0.98] transition-all">
                        <div class="absolute left-0 top-0 bottom-0 w-9 bg-gradient-to-r from-emerald-300/75 to-emerald-100/20 rounded-r-full pointer-events-none"></div>
                        
                        <div class="relative z-10 flex items-center gap-4">
                            <div class="w-14 h-14 flex items-center justify-center shrink-0">
                                <svg class="w-11 h-11" viewBox="0 0 60 60" fill="none">
                                    <rect x="20" y="14" width="20" height="32" rx="10" stroke="#1b7a43" stroke-width="3"/>
                                    <path d="M30 10V50M30 24L37 19M30 34L23 29" stroke="#1b7a43" stroke-width="2.5" stroke-linecap="round"/>
                                </svg>
                            </div>
                            
                            <div>
                                <h3 class="font-extrabold text-[17px] text-[#052963] leading-tight group-hover:text-[#0b57d0] transition-colors">Fore Coffee</h3>
                                <p class="text-[12px] text-slate-500 font-medium mt-0.5">Live Green, Live Better</p>
                            </div>
                        </div>

                        <div class="relative z-10 w-10 h-10 rounded-full bg-[#e3effd] group-hover:bg-[#0b57d0] text-[#052963] group-hover:text-white flex items-center justify-center transition-colors shrink-0">
                            <i data-lucide="chevron-right" class="w-5 h-5 stroke-[2.5]"></i>
                        </div>
                    </a>

                    <!-- 4. Janji Jiwa -->
                    <a href="/janjiw" class="group relative bg-white rounded-[26px] p-3.5 pl-6 soft-card-shadow flex items-center justify-between cursor-pointer overflow-hidden border border-white hover:border-blue-200 active:scale-[0.98] transition-all">
                        <div class="absolute left-0 top-0 bottom-0 w-9 bg-gradient-to-r from-rose-300/80 to-rose-100/20 rounded-r-full pointer-events-none"></div>
                        
                        <div class="relative z-10 flex items-center gap-4">
                            <div class="w-14 h-14 flex items-center justify-center shrink-0">
                                <div class="border-2 border-[#d92d20] rounded-md px-1.5 py-0.5 text-center leading-none">
                                    <div class="text-[10px] font-black text-[#d92d20] tracking-tighter">JANJI</div>
                                    <div class="text-[11px] font-black text-[#d92d20] tracking-tighter">JIWA</div>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="font-extrabold text-[17px] text-[#052963] leading-tight group-hover:text-[#0b57d0] transition-colors">Janji Jiwa</h3>
                                <p class="text-[12px] text-slate-500 font-medium mt-0.5">Teman Sejati di Setiap Cerita</p>
                            </div>
                        </div>

                        <div class="relative z-10 w-10 h-10 rounded-full bg-[#e3effd] group-hover:bg-[#0b57d0] text-[#052963] group-hover:text-white flex items-center justify-center transition-colors shrink-0">
                            <i data-lucide="chevron-right" class="w-5 h-5 stroke-[2.5]"></i>
                        </div>
                    </a>
                </div>

                <!-- Gabung Saluran Button -->
                <div class="mt-7 px-3">
                    <button onclick="openChannelModal()" class="w-full btn-navy-gradient text-white rounded-full py-3.5 px-6 shadow-lg shadow-blue-900/25 flex items-center justify-between active:scale-[0.98] transition-transform">
                        <span class="w-6"></span>
                        <span class="flex items-center gap-2.5 font-bold text-[16px]">
                            <span class="w-6 h-6 rounded-full bg-white text-[#062d6b] flex items-center justify-center">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 fill-current"></i>
                            </span>
                            Gabung Saluran
                        </span>
                        <i data-lucide="chevron-right" class="w-5 h-5 text-blue-200"></i>
                    </button>
                </div>
            </div>

            <!-- Decorative Bottom Wave Footer -->
            <div class="relative h-12 w-full overflow-hidden pointer-events-none mt-auto">
                <svg class="absolute bottom-0 w-full h-12" viewBox="0 0 430 50" preserveAspectRatio="none">
                    <path d="M0,15 Q90,50 215,48 T430,10 L430,50 L0,50 Z" fill="#1b74d8" opacity="0.85"/>
                    <path d="M0,30 Q120,55 260,45 T430,22 L430,50 L0,50 Z" fill="#06347a"/>
                </svg>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- MODAL GABUNG SALURAN                                      -->
        <!-- ========================================================= -->
        <div id="channel-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-5">
            <div class="bg-white w-full max-w-[360px] rounded-[28px] p-6 text-center shadow-2xl">
                <div class="w-16 h-16 rounded-full bg-blue-50 text-[#062d6b] flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="bell-ring" class="w-8 h-8 text-[#0b57d0]"></i>
                </div>
                <h3 class="font-extrabold text-xl text-[#062d6b]">Gabung Saluran Zona Fitri</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    Dapatkan info voucher diskon kopi harian, promo beli 1 gratis 1, dan kode promo eksklusif langsung di WhatsApp kamu!
                </p>
                <div class="mt-5 space-y-2.5">
                    <a href="https://whatsapp.com/channel/0029VbAzAXsI1rcbcchmmj2F" target="_blank" rel="noopener" onclick="closeChannelModal()" class="w-full btn-navy-gradient text-white font-bold text-sm py-3 rounded-full flex items-center justify-center gap-2 shadow-md">
                        <span>Gabung Saluran WhatsApp</span>
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                    </a>
                    <button onclick="closeChannelModal()" class="w-full py-2.5 text-xs font-bold text-slate-400 hover:text-slate-600">
                        Nanti Saja
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- JavaScript Initialization -->
    <script>
        function openChannelModal() {
            document.getElementById('channel-modal').classList.remove('hidden');
        }

        function closeChannelModal() {
            document.getElementById('channel-modal').classList.add('hidden');
        }

        window.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>