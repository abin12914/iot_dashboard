<template>
  <svg class="w-full h-full" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
    <defs>
      <linearGradient id="logoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
        <stop offset="0%" stop-color="#D97706"/>
        <stop offset="100%" stop-color="#B45309"/>
      </linearGradient>
    </defs>
    <!-- Rounded App Tile -->
    <rect width="64" height="64" rx="16" fill="url(#logoGrad)"/>
    
    <!-- Tractor / Heavy Machinery Silhouette in Clean White -->
    <g fill="#FFFFFF" transform="translate(6, 6) scale(0.8125)">
      <!-- Main Cabin -->
      <path d="M12 28h24v16H12z" opacity="0.95"/>
      <path d="M22 14h14l8 14H22z" fill="#FFFFFF"/>
      <!-- Cabin Window Cutout -->
      <path d="M25 18h9l4 8H25z" fill="#D97706"/>
      <!-- Exhaust Stack & Engine Hood -->
      <path d="M42 22h4v6h-4z"/>
      <path d="M38 32h16c2 0 4 2 4 4v8H38z"/>
      <!-- Front Loader Arm / Bucket Accent -->
      <path d="M54 36l6-6v14h-6z"/>
      
      <!-- Heavy Wheels -->
      <!-- Large Rear Wheel -->
      <circle cx="20" cy="46" r="12" fill="#1E293B"/>
      <circle cx="20" cy="46" r="8" fill="#F8FAFC"/>
      <circle cx="20" cy="46" r="4" fill="#D97706"/>
      
      <!-- Front Wheel -->
      <circle cx="50" cy="48" r="8" fill="#1E293B"/>
      <circle cx="50" cy="48" r="5" fill="#F8FAFC"/>
      <circle cx="50" cy="48" r="2.5" fill="#D97706"/>

      <!-- Telematics Pulse Wave Signal -->
      <circle cx="54" cy="14" r="3" fill="#10B981"/>
      <path d="M50 10a8 8 0 0 1 8 0" stroke="#10B981" stroke-width="2.5" stroke-linecap="round" fill="none"/>
      <path d="M47 6a13 13 0 0 1 14 0" stroke="#10B981" stroke-width="2.5" stroke-linecap="round" fill="none" opacity="0.75"/>
    </g>
  </svg>
</template>
