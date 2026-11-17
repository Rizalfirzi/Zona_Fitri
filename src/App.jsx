import { LogoZonafitri, KenanganLogo, TomoroLogo, ForeLogo, JanjiJiwaLogo, CoffeeCupIcon, LocationPinIcon } from './components/Icons';

export default function App() {
  return (
    /* Tambahkan overflow-hidden agar ombak bawah tidak membuat scroll menyamping */
    <div className="min-h-screen bg-[#F4F7FB] flex flex-col items-center pb-32 relative font-sans overflow-hidden">
      
      {/* 1. HEADER BIRU DENGAN CUSTOM WAVE */}
      <div className="w-full max-w-md bg-gradient-to-b from-[#003471] to-[#005B9F] pt-8 pb-20 px-6 text-white relative">
        
        {/* Lencana Zonafitri Kiri Atas */}
        <div className="bg-white rounded-full py-1.5 px-4 inline-flex items-center w-fit mb-6">
          <LogoZonafitri />
        </div>

        {/* Status Sedang Buka */}
        <div className="bg-[#004282] text-[#00E5C0] px-3 py-1.5 rounded-full text-xs font-medium flex items-center w-fit gap-2 border border-white/10 mb-4">
          <span className="w-2 h-2 rounded-full bg-[#00E5C0]"></span>
          Sedang Buka
        </div>
        
        {/* Teks Utama */}
        <h1 className="text-[34px] font-bold mt-2 tracking-tight leading-tight">Mau Ngopi?</h1>
        <p className="text-[17px] mt-1 font-light opacity-90">Ingat Fitri</p>
        
        {/* SVG Ombak Pemisah (Wave Divider) - Mengubah lengkungan kaku menjadi ombak dinamis */}
        <div className="absolute bottom-0 left-0 w-full overflow-hidden leading-[0]">
          <svg viewBox="0 0 1200 120" preserveAspectRatio="none" className="relative block w-full h-[40px] md:h-[50px]">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V0C50,22,100.92,44.75,150.43,56.44,206.87,69.75,265.41,66.56,321.39,56.44Z" fill="#F4F7FB"></path>
          </svg>
        </div>
      </div>

      {/* 2. KONTEN MENU KOPI */}
      <div className="w-full max-w-md px-5 -mt-6 relative z-10">
        
        {/* Garis & Judul Pilih Brand */}
        <div className="flex items-center justify-center gap-3 text-[#004A8F] font-bold mb-6">
          <div className="h-[1px] bg-[#004A8F]/20 flex-1"></div>
          <div className="flex items-center gap-2">
            <CoffeeCupIcon />
            <h2 className="text-[15px]">Pilih Brand Kopi</h2>
          </div>
          <div className="h-[1px] bg-[#004A8F]/20 flex-1"></div>
        </div>

        {/* Kartu 1: Kopi Kenangan */}
        {/* Perhatikan penambahan shadow halus dan rounded-[28px] */}
        <button className="relative overflow-hidden w-full bg-white rounded-[28px] p-3.5 mb-3.5 flex items-center shadow-[0_8px_20px_rgba(0,0,0,0.04)] border border-white active:scale-[0.98] transition-transform">
          {/* Splash Merah yang Membaur Halus */}
          <div className="absolute left-0 top-0 bottom-0 w-32 bg-gradient-to-r from-red-100/90 via-red-50/40 to-transparent pointer-events-none"></div>
          <div className="relative z-10 w-12 h-12 flex items-center justify-center mr-3">
             <KenanganLogo />
          </div>
          <div className="relative z-10 text-left flex-1">
            <h3 className="font-bold text-[#1E293B] text-[16px]">Kopi Kenangan</h3>
            <p className="text-[12px] text-[#64748B] mt-0.5">Untuk kamu, yang selalu spesial</p>
          </div>
          <div className="relative z-10 w-8 h-8 bg-[#EDF2F7] rounded-full flex items-center justify-center text-[#004A8F] font-bold text-lg">
            ›
          </div>
        </button>

        {/* Kartu 2: Tomoro Coffee */}
        <button className="relative overflow-hidden w-full bg-white rounded-[28px] p-3.5 mb-3.5 flex items-center shadow-[0_8px_20px_rgba(0,0,0,0.04)] border border-white active:scale-[0.98] transition-transform">
          <div className="absolute left-0 top-0 bottom-0 w-32 bg-gradient-to-r from-orange-100/90 via-orange-50/40 to-transparent pointer-events-none"></div>
          <div className="relative z-10 w-12 h-12 flex items-center justify-center mr-3">
             <TomoroLogo />
          </div>
          <div className="relative z-10 text-left flex-1">
            <h3 className="font-bold text-[#1E293B] text-[16px]">Tomoro Coffee</h3>
            <p className="text-[12px] text-[#64748B] mt-0.5">Good Coffee, Good Mood</p>
          </div>
          <div className="relative z-10 w-8 h-8 bg-[#EDF2F7] rounded-full flex items-center justify-center text-[#004A8F] font-bold text-lg">
            ›
          </div>
        </button>

        {/* Kartu 3: Fore Coffee */}
        <button className="relative overflow-hidden w-full bg-white rounded-[28px] p-3.5 mb-3.5 flex items-center shadow-[0_8px_20px_rgba(0,0,0,0.04)] border border-white active:scale-[0.98] transition-transform">
          <div className="absolute left-0 top-0 bottom-0 w-32 bg-gradient-to-r from-green-100/90 via-green-50/40 to-transparent pointer-events-none"></div>
          <div className="relative z-10 w-12 h-12 flex items-center justify-center mr-3">
             <ForeLogo />
          </div>
          <div className="relative z-10 text-left flex-1">
            <h3 className="font-bold text-[#1E293B] text-[16px]">Fore Coffee</h3>
            <p className="text-[12px] text-[#64748B] mt-0.5">Live Green, Live Better</p>
          </div>
          <div className="relative z-10 w-8 h-8 bg-[#EDF2F7] rounded-full flex items-center justify-center text-[#004A8F] font-bold text-lg">
            ›
          </div>
        </button>

        {/* Kartu 4: Janji Jiwa */}
        <button className="relative overflow-hidden w-full bg-white rounded-[28px] p-3.5 mb-3.5 flex items-center shadow-[0_8px_20px_rgba(0,0,0,0.04)] border border-white active:scale-[0.98] transition-transform">
          <div className="absolute left-0 top-0 bottom-0 w-32 bg-gradient-to-r from-rose-100/90 via-rose-50/40 to-transparent pointer-events-none"></div>
          <div className="relative z-10 w-12 h-12 flex items-center justify-center mr-3">
             <JanjiJiwaLogo />
          </div>
          <div className="relative z-10 text-left flex-1">
            <h3 className="font-bold text-[#1E293B] text-[16px]">Janji Jiwa</h3>
            <p className="text-[12px] text-[#64748B] mt-0.5">Teman Sejati di Setiap Cerita</p>
          </div>
          <div className="relative z-10 w-8 h-8 bg-[#EDF2F7] rounded-full flex items-center justify-center text-[#004A8F] font-bold text-lg">
            ›
          </div>
        </button>
      </div>

      {/* 3. GELOMBANG BACKGROUND BAWAH & TOMBOL */}
      {/* Container untuk menahan gelombang di bagian bawah */}
      <div className="fixed bottom-0 w-full max-w-md h-40 pointer-events-none">
        {/* SVG Gelombang Layer 1 (Biru Tua) */}
        <svg viewBox="0 0 1440 320" className="absolute bottom-0 w-full h-[150px]" preserveAspectRatio="none">
          <path fill="#003471" fillOpacity="1" d="M0,256L60,229.3C120,203,240,149,360,144C480,139,600,181,720,202.7C840,224,960,224,1080,202.7C1200,181,1320,139,1380,117.3L1440,96L1440,320L1380,320C1320,320,1200,320,1080,320C960,320,840,320,720,320C600,320,480,320,360,320C240,320,120,320,60,320L0,320Z"></path>
        </svg>
        {/* SVG Gelombang Layer 2 (Biru Terang) */}
        <svg viewBox="0 0 1440 320" className="absolute bottom-0 w-full h-[100px]" preserveAspectRatio="none">
          <path fill="#0062AA" fillOpacity="1" d="M0,160L80,181.3C160,203,320,245,480,240C640,235,800,181,960,165.3C1120,149,1280,171,1360,181.3L1440,192L1440,320L1360,320C1280,320,1120,320,960,320C800,320,640,320,480,320C320,320,160,320,80,320L0,320Z"></path>
        </svg>
      </div>

      {/* Tombol Gabung Saluran (Z-Index di atas gelombang) */}
      <div className="fixed bottom-6 w-full max-w-md px-5 z-20">
        <button className="w-full bg-[#002A5C] text-white rounded-full py-3.5 flex items-center justify-center gap-3 font-semibold text-[16px] shadow-[0_10px_25px_rgba(0,42,92,0.4)] pointer-events-auto">
          <LocationPinIcon />
          Gabung Seluran
          <span className="ml-1 text-xl font-normal">›</span>
        </button>
      </div>

    </div>
  );
}
