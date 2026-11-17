// src/components/Icons.jsx

export const LogoZonafitri = () => (
  <svg viewBox="0 0 110 30" width="110" height="30" fill="none" xmlns="http://www.w3.org/2000/svg">
    <text x="2" y="21" fill="#B0835D" fontSize="18" fontWeight="bold" fontFamily="serif">z</text>
    {/* Biji kopi untuk huruf o */}
    <circle cx="17" cy="15" r="6" fill="#E21B38"/>
    <path d="M13 18Q17 15 21 12" stroke="white" strokeWidth="1.5" strokeLinecap="round"/>
    <text x="25" y="21" fill="#B0835D" fontSize="18" fontWeight="bold" fontFamily="serif">na</text>
    <text x="50" y="21" fill="#D81B60" fontSize="18" fontWeight="bold" fontFamily="serif">fitri</text>
  </svg>
);

export const KenanganLogo = () => (
  <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
    {/* Hati utama */}
    <path d="M20 30C20 30 8 20 8 12C8 7.5 11.5 4 16 4C18.5 4 20 5.5 20 5.5C20 5.5 21.5 4 24 4C28.5 4 32 7.5 32 12C32 20 20 30 20 30Z" fill="#E21B38"/>
    {/* Garis-garis putih di dalam hati */}
    <path d="M10 11H30 M11 15H29 M13 19H27 M16 23H24 M19 27H21" stroke="white" strokeWidth="2.5" strokeLinecap="round"/>
    {/* Tangkai/Pin bawah */}
    <path d="M20 30V36" stroke="#E21B38" strokeWidth="3.5" strokeLinecap="round"/>
  </svg>
);

export const TomoroLogo = () => (
  <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
    {/* Siluet kucing/cangkir */}
    <path d="M28 32C28 20 28 16 24 12C21.5 9.5 17 9.5 15 11L12 6L11 12C9 14 8 18 8 24C8 28.5 11 32 15 32H28Z" fill="#F36F21"/>
    {/* Ekor melengkung */}
    <path d="M34 32C34 26 31 23 28 23" stroke="#F36F21" strokeWidth="3" strokeLinecap="round"/>
  </svg>
);

export const ForeLogo = () => (
  <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
    {/* Lingkaran luar */}
    <circle cx="20" cy="20" r="16" stroke="#235D3A" strokeWidth="2"/>
    {/* Tangkai vertikal */}
    <path d="M16 10V30" stroke="#235D3A" strokeWidth="2" strokeLinecap="round"/>
    {/* Daun atas membentuk F */}
    <path d="M16 16C22 16 26 12 26 8" stroke="#235D3A" strokeWidth="2" strokeLinecap="round"/>
    {/* Daun tengah membentuk F */}
    <path d="M16 22C20 22 24 18 24 14" stroke="#235D3A" strokeWidth="2" strokeLinecap="round"/>
  </svg>
);

export const JanjiJiwaLogo = () => (
  <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
    {/* Kotak merah */}
    <rect x="4" y="10" width="32" height="20" rx="3" fill="#E21B38"/>
    <text x="20" y="19" fill="white" fontSize="8" fontWeight="900" fontFamily="sans-serif" textAnchor="middle" letterSpacing="0.5">JANJI</text>
    <text x="20" y="27" fill="white" fontSize="8" fontWeight="900" fontFamily="sans-serif" textAnchor="middle" letterSpacing="0.5">JIWA</text>
  </svg>
);

export const CoffeeCupIcon = () => (
  <svg width="24" height="24" viewBox="0 0 24 24" fill="#004A8F" xmlns="http://www.w3.org/2000/svg">
    <path d="M17 8H21C22.1046 8 23 8.89543 23 10V11C23 13.2091 21.2091 15 19 15H17M3 17V6C3 4.89543 3.89543 4 5 4H15C16.1046 4 17 4.89543 17 6V17C17 19.2091 15.2091 21 13 21H7C4.79086 21 3 19.2091 3 17Z" stroke="#004A8F" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
    <path d="M8 2L8 4M12 2L12 4" stroke="#004A8F" strokeWidth="2" strokeLinecap="round"/>
  </svg>
);

export const LocationPinIcon = () => (
  <svg width="24" height="24" viewBox="0 0 24 24" fill="white" stroke="white" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
    <circle cx="12" cy="10" r="4" fill="#004A8F" stroke="none"></circle>
  </svg>
);
