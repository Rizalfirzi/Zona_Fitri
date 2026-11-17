import { LogoZonafitri, KenanganLogo, TomoroLogo, ForeLogo, JanjiJiwaLogo, CoffeeCupIcon, LocationPinIcon } from './components/Icons';

export default function App() {
  return (
    <div className="min-h-screen bg-[#F0F5FA] flex flex-col items-center pb-28 relative font-sans">
      
      {/* HEADER BIRU MELENGKUNG */}
      <div className="w-full max-w-md bg-gradient-to-b from-[#003874] to-[#0062AA] pt-8 pb-32 px-6 rounded-b-[50px] text-white relative overflow-hidden">
        
        {/* Lencana Zonafitri Kiri Atas */}
        <div className="bg-white rounded-full py-1.5 px-4 inline-flex items-center w-fit mb-6">
          <LogoZonafitri />
        </div>

        {/* Status Sedang Buka */}
        <div className="bg-[#004A8F] text-[#00E5C0] px-3 py-1.5 rounded-full text-xs font-medium flex items-center w-fit gap-2 border border-blue-400/30">
          <span className="w-2 h-2 rounded-full bg-[#00E5C0]"></span>
          Sedang Buka
        </div>
        
        {/* Teks Utama */}
        <h1 className="text-4xl font-bold mt-4 tracking-tight">Mau Ngopi?</h1>
        <p className="text-lg mt-1 font-light opacity-90">Ingat Fitri</p>
        
        {/* Catatan: Untuk meletakkan gambar gelas kopi, gunakan tag <img src="/img/hero_cup_splash.png" className="absolute right-[-20px] top-10 w-48" /> di sini nantinya */}
      </div>

      {/* KONTEN MENU KOPI */}
      <div className="w-full max-w-md px-6 -mt-20 relative z-10">
        
        {/* Garis & Judul Pilih Brand */}
        <div className="flex items-center justify-center gap-4 text-[#004A8F] font-bold mb-6">
          <div className="h-[2px] bg-blue-100 flex-1 rounded"></div>
          <div className="flex items-center gap-2">
            <CoffeeCupIcon />
            <h2 className="text-lg">Pilih Brand Kopi</h2>
          </div>
          <div className="h-[2px] bg-blue-100 flex-1 rounded"></div>
        </div>

        {/* 1. Kopi Kenangan */}
        <button className="relative overflow-hidden w-full bg-white rounded-3xl p-4 mb-4 flex items-center shadow-[0_4px_15px_rgba(0,0,0,0.03)] border border-gray-50 active:scale-[0.98] transition-transform">
          {/* Gradient Splash Kiri */}
          <div className="absolute left-0 top-0 bottom-0 w-24 bg-gradient-to-r from-red-100/80 to-transparent"></div>
          <div className="relative z-10 w-12 h-12 flex items-center justify-center mr-4">
             <KenanganLogo />
          </div>
          <div className="relative z-10 text-left flex-1">
            <h3 className="font-bold text-[#1E293B] text-[17px]">Kopi Kenangan</h3>
            <p className="text-[13px] text-gray-500 mt-0.5">Untuk kamu, yang selalu spesial</p>
          </div>
          <div className="relative z-10 w-8 h-8 bg-[#F1F5F9] rounded-full flex items-center justify-center text-blue-900 font-bold text-xl">
            ›
          </div>
        </button>

        {/* 2. Tomoro Coffee */}
        <button className="relative overflow-hidden w-full bg-white rounded-3xl p-4 mb-4 flex items-center shadow-[0_4px_15px_rgba(0,0,0,0.03)] border border-gray-50 active:scale-[0.98] transition-transform">
          <div className="absolute left-0 top-0 bottom-0 w-24 bg-gradient-to-r from-orange-100/80 to-transparent"></div>
          <div className="relative z-10 w-12 h-12 flex items-center justify-center mr-4">
             <TomoroLogo />
          </div>
          <div className="relative z-10 text-left flex-1">
            <h3 className="font-bold text-[#1E293B] text-[17px]">Tomoro Coffee</h3>
            <p className="text-[13px] text-gray-500 mt-0.5">Good Coffee, Good Mood</p>
          </div>
          <div className="relative z-10 w-8 h-8 bg-[#F1F5F9] rounded-full flex items-center justify-center text-blue-900 font-bold text-xl">
            ›
          </div>
        </button>

        {/* 3. Fore Coffee */}
        <button className="relative overflow-hidden w-full bg-white rounded-3xl p-4 mb-4 flex items-center shadow-[0_4px_15px_rgba(0,0,0,0.03)] border border-gray-50 active:scale-[0.98] transition-transform">
          <div className="absolute left-0 top-0 bottom-0 w-24 bg-gradient-to-r from-green-100/80 to-transparent"></div>
          <div className="relative z-10 w-12 h-12 flex items-center justify-center mr-4">
             <ForeLogo />
          </div>
          <div className="relative z-10 text-left flex-1">
            <h3 className="font-bold text-[#1E293B] text-[17px]">Fore Coffee</h3>
            <p className="text-[13px] text-gray-500 mt-0.5">Live Green, Live Better</p>
          </div>
          <div className="relative z-10 w-8 h-8 bg-[#F1F5F9] rounded-full flex items-center justify-center text-blue-900 font-bold text-xl">
            ›
          </div>
        </button>

        {/* 4. Janji Jiwa */}
        <button className="relative overflow-hidden w-full bg-white rounded-3xl p-4 mb-4 flex items-center shadow-[0_4px_15px_rgba(0,0,0,0.03)] border border-gray-50 active:scale-[0.98] transition-transform">
          <div className="absolute left-0 top-0 bottom-0 w-24 bg-gradient-to-r from-pink-100/80 to-transparent"></div>
          <div className="relative z-10 w-12 h-12 flex items-center justify-center mr-4">
             <JanjiJiwaLogo />
          </div>
          <div className="relative z-10 text-left flex-1">
            <h3 className="font-bold text-[#1E293B] text-[17px]">Janji Jiwa</h3>
            <p className="text-[13px] text-gray-500 mt-0.5">Teman Sejati di Setiap Cerita</p>
          </div>
          <div className="relative z-10 w-8 h-8 bg-[#F1F5F9] rounded-full flex items-center justify-center text-blue-900 font-bold text-xl">
            ›
          </div>
        </button>
      </div>

      {/* TOMBOL BAWAH */}
      <div className="fixed bottom-6 w-full max-w-md px-6 z-20">
        <button className="w-full bg-[#004A8F] text-white rounded-full py-4 flex items-center justify-center gap-3 font-bold text-lg shadow-[0_10px_25px_rgba(0,74,143,0.3)] hover:bg-[#003874] transition-colors">
          <LocationPinIcon />
          Gabung Seluran
          <span className="ml-1 text-xl">›</span>
        </button>
      </div>

    </div>
  );
}
