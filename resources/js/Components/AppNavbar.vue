<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutDashboard, Truck, Radio, Activity } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import TelemetrySimulatorModal from '@/Components/TelemetrySimulatorModal.vue';

const isSimulatorOpen = ref(false);

const page = usePage();
const currentUrl = computed(() => page.url);
const appName = computed(() => page.props.appName || "Telematics");
const lastPing = computed(() => page.props.lastPing);

const isDashboardActive = computed(() => currentUrl.value === '/' || currentUrl.value.startsWith('/dashboard'));
const isAssetsActive = computed(() => currentUrl.value.startsWith('/assets'));
</script>

<template>
  <nav class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        
        <!-- Left: Brand Logo & Navigation Links -->
        <div class="flex items-center gap-8">
          
          <!-- Brand Logo -->
          <Link href="/" class="flex items-center gap-3 group">
            <div class="w-9 h-9 rounded-xl bg-amber-600 flex items-center justify-center text-white shadow-sm transition-transform group-hover:scale-105">
              <Truck class="w-5 h-5" />
            </div>
            <div>
              <span class="text-base font-extrabold text-gray-900 tracking-tight flex items-center gap-1.5">
                {{ appName }}
              </span>
              <span class="text-[10px] text-gray-400 block -mt-1 font-medium tracking-wide">
                IoT Heavy Fleet Platform
              </span>
            </div>
          </Link>

          <!-- Nav Links -->
          <div class="hidden sm:flex items-center gap-1.5">
            <Link
              href="/"
              class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg transition-all"
              :class="isDashboardActive 
                ? 'bg-amber-50 text-amber-700 shadow-xs border border-amber-200/60' 
                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'"
            >
              <LayoutDashboard class="w-4 h-4" :class="isDashboardActive ? 'text-amber-600' : 'text-gray-400'" />
              Fleet Dashboard
            </Link>

            <Link
              href="/assets"
              class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg transition-all"
              :class="isAssetsActive 
                ? 'bg-amber-50 text-amber-700 shadow-xs border border-amber-200/60' 
                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'"
            >
              <Truck class="w-4 h-4" :class="isAssetsActive ? 'text-amber-600' : 'text-gray-400'" />
              Asset Inventory
            </Link>

            <!-- Simulator Trigger Button in Navigation -->
            <button
              type="button"
              @click="isSimulatorOpen = true"
              class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all cursor-pointer"
            >
              <Activity class="w-4 h-4 text-amber-500" />
              <span>IoT Simulator</span>
            </button>
          </div>

        </div>

        <!-- Right: Last Ping Telematics Status -->
        <div class="flex items-center gap-3">
          <div 
            v-if="lastPing" 
            class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200/80 text-emerald-900 text-xs font-medium shadow-2xs"
            :title="`Last telemetry received: ${lastPing.formatted_time} (${lastPing.asset_code})`"
          >
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <span class="flex items-center gap-1.5">
              <span class="text-gray-500 font-normal">Last Ping:</span>
              <span class="font-bold text-emerald-700">{{ lastPing.formatted_time }}</span>
              <span class="text-[11px] px-1.5 py-0.5 rounded bg-emerald-100/70 text-emerald-800 font-semibold font-mono">{{ lastPing.asset_code }}</span>
            </span>
          </div>

          <div 
            v-else 
            class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-gray-50 border border-gray-200 text-gray-500 text-xs font-medium"
          >
            <span class="h-2 w-2 rounded-full bg-gray-300"></span>
            <span>No ping recorded</span>
          </div>
        </div>

      </div>
    </div>
  </nav>

  <!-- Telemetry Simulator Modal -->
  <TelemetrySimulatorModal 
    :is-open="isSimulatorOpen" 
    @close="isSimulatorOpen = false" 
  />
</template>
