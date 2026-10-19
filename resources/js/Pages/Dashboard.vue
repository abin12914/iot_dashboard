<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import VueApexCharts from 'vue3-apexcharts';
import AppNavbar from '@/Components/AppNavbar.vue';
import { 
  Radio, 
  Cpu, 
  Truck, 
  MessageSquare, 
  Calendar, 
  Filter,
  Layers
} from 'lucide-vue-next';

const props = defineProps({
  assets: {
    type: Array,
    default: () => []
  },
  dailyMetrics: {
    type: Array,
    default: () => []
  },
  summary: {
    type: Object,
    default: () => ({
      total_distance_km: 431,
      distance_change_percentage: '+ 45%',
      total_hours: 1246,
      activity_breakdown: {
        ignition_off: 65,
        ignition: 25,
        idling: 10
      }
    })
  },
  selectedDate: {
    type: String,
    default: '2026-03-23'
  }
});

// All 15 date labels for categories
const dateCategories = computed(() => props.dailyMetrics.map(m => m.date_label));

// Chart 1: Traveled Distance (Comparative Area / Line Chart)
const distanceChart = computed(() => ({
  series: [
    {
      name: 'Current Period',
      data: props.dailyMetrics.map(m => m.distance_current_km)
    },
    {
      name: 'Previous Period',
      data: props.dailyMetrics.map(m => m.distance_previous_km)
    }
  ],
  options: {
    chart: {
      type: 'area',
      height: 230,
      toolbar: { show: false },
      zoom: { enabled: false },
      fontFamily: 'inherit'
    },
    colors: ['#C25E1A', '#CCA380'],
    fill: {
      type: ['solid', 'solid'],
      opacity: [0.55, 0.0]
    },
    stroke: {
      curve: 'smooth',
      width: [3, 2],
      dashArray: [0, 5]
    },
    markers: {
      size: [4.5, 3],
      colors: ['#C25E1A', '#CCA380'],
      strokeWidth: 2,
      hover: { size: 6 }
    },
    dataLabels: { enabled: false },
    xaxis: {
      categories: dateCategories.value,
      labels: {
        style: { colors: '#9CA3AF', fontSize: '11px' },
        rotate: -45,
        formatter: function(val) {
          if (!val) return "";
          const idx = dateCategories.value.indexOf(val);
          // Show alternating dates starting from index 2 (22 Mar, 24 Mar, 26 Mar, ...)
          if (idx !== -1 && idx >= 2 && idx % 2 === 0) {
            return val;
          }
          return "";
        }
      },
      axisBorder: { show: false },
      axisTicks: { show: false },
    },
    yaxis: {
      min: 0,
      max: 40,
      tickAmount: 2,
      labels: {
        style: { colors: '#9CA3AF', fontSize: '11px' }
      }
    },
    grid: {
      borderColor: '#F3F4F6',
      strokeDashArray: 4,
    },
    legend: { show: false },
    tooltip: {
      custom: function({ series, seriesIndex, dataPointIndex }) {
        const item = props.dailyMetrics[dataPointIndex];
        if (!item) return '';
        return `
          <div class="bg-white text-gray-800 p-2.5 rounded-lg shadow-lg border border-gray-100 text-xs">
            <div class="font-medium text-gray-400 mb-1.5">${item.date_label}</div>
            <div class="flex items-center gap-1.5 font-semibold text-gray-800">
              <span class="w-2 h-2 rounded-full bg-amber-600 inline-block"></span>
              Current Period: <span class="font-bold">${item.distance_current_km} Km</span>
            </div>
            <div class="flex items-center gap-1.5 text-gray-500 mt-1">
              <span class="w-2 h-2 rounded-full bg-amber-300 inline-block"></span>
              Previous Period: <span class="font-semibold">${item.distance_previous_km} Km</span>
            </div>
          </div>
        `;
      }
    }
  }
}));

// Chart 2: Engine Hours (Stacked Bar Chart)
const engineHoursChart = computed(() => ({
  series: [
    {
      name: 'Working / Active',
      data: props.dailyMetrics.map(m => m.engine_hours_active)
    },
    {
      name: 'Idling',
      data: props.dailyMetrics.map(m => m.engine_hours_idle)
    }
  ],
  options: {
    chart: {
      type: 'bar',
      height: 230,
      stacked: true,
      toolbar: { show: false },
      fontFamily: 'inherit'
    },
    colors: ['#00875A', '#E07A28'],
    plotOptions: {
      bar: {
        columnWidth: '42%',
        borderRadius: 2,
      }
    },
    dataLabels: { enabled: false },
    xaxis: {
      categories: dateCategories.value,
      labels: {
        style: { colors: '#9CA3AF', fontSize: '11px' },
        rotate: -45,
        formatter: function(val) {
          if (!val) return "";
          const idx = dateCategories.value.indexOf(val);
          // Show alternating dates starting from index 0 (20 Mar, 22 Mar, 24 Mar, ...)
          if (idx !== -1 && idx % 2 === 0) {
            return val;
          }
          return "";
        }
      },
      axisBorder: { show: false },
    },
    yaxis: {
      min: 0,
      max: 40,
      tickAmount: 2,
      labels: {
        style: { colors: '#9CA3AF', fontSize: '11px' }
      }
    },
    grid: {
      borderColor: '#F3F4F6',
      strokeDashArray: 4,
    },
    legend: { show: false }
  }
}));

// Chart 3: Activity Breakdown (Donut Chart)
const activityDonutChart = computed(() => ({
  series: [
    props.summary.activity_breakdown.ignition_off,
    props.summary.activity_breakdown.ignition,
    props.summary.activity_breakdown.idling
  ],
  options: {
    chart: {
      type: 'donut',
      height: 220,
      fontFamily: 'inherit'
    },
    colors: ['#881337', '#00875A', '#E07A28'],
    labels: ['Ignition Off', 'Ignition', 'Idling'],
    stroke: { width: 3, colors: ['#FFFFFF'] },
    plotOptions: {
      pie: {
        donut: {
          size: '72%',
          labels: {
            show: true,
            total: {
              show: true,
              label: 'Hours',
              fontSize: '12px',
              fontWeight: 500,
              color: '#6B7280',
              formatter: () => '1,246'
            },
            value: {
              fontSize: '24px',
              fontWeight: 700,
              color: '#111827',
              offsetY: -2
            }
          }
        }
      }
    },
    dataLabels: { enabled: false },
    legend: { show: false }
  }
}));

// Chart 4: Messages Received (Bar Chart)
const messagesChart = computed(() => ({
  series: [
    {
      name: 'Messages',
      data: props.dailyMetrics.map(m => m.messages_received)
    }
  ],
  options: {
    chart: {
      type: 'bar',
      height: 230,
      toolbar: { show: false },
      fontFamily: 'inherit'
    },
    colors: ['#C2410C'],
    plotOptions: {
      bar: {
        columnWidth: '55%',
        borderRadius: 2,
      }
    },
    dataLabels: { enabled: false },
    xaxis: {
      categories: dateCategories.value,
      labels: {
        style: { colors: '#9CA3AF', fontSize: '11px' },
        rotate: -45,
        formatter: function(val) {
          if (!val) return "";
          const idx = dateCategories.value.indexOf(val);
          // Show alternating dates starting from index 0 (20 Mar, 22 Mar, 24 Mar, ...)
          if (idx !== -1 && idx % 2 === 0) {
            return val;
          }
          return "";
        }
      },
      axisBorder: { show: false },
    },
    yaxis: {
      labels: {
        style: { colors: '#9CA3AF', fontSize: '11px' },
        formatter: (val) => val >= 1000 ? `${(val/1000).toFixed(1)}k` : val
      }
    },
    grid: {
      borderColor: '#F3F4F6',
      strokeDashArray: 4,
    }
  }
}));

const timeSlots = ['11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];
</script>

<template>
  <Head title="Fleet Telematics Analytics Dashboard" />

  <div class="min-h-screen bg-[#F3F4F6]">
    <AppNavbar />
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="max-w-7xl mx-auto space-y-6">
      
        <!-- Top Section: 4 Analytics Cards (Image 1 - 2x2 Grid) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Card 1: Traveled Distance -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2.5">
              <Radio class="w-5 h-5 text-gray-700" />
              <h2 class="text-base font-bold text-gray-800">
                Traveled distance ({{ summary.total_distance_km }}km)
              </h2>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-600 border border-emerald-100">
              {{ summary.distance_change_percentage }}
            </span>
          </div>
          <div class="h-60">
            <VueApexCharts
              type="area"
              height="230"
              :options="distanceChart.options"
              :series="distanceChart.series"
            />
          </div>
        </div>

        <!-- Card 2: Engine Hours -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2.5">
              <Cpu class="w-5 h-5 text-gray-700" />
              <h2 class="text-base font-bold text-gray-800">Engine hours</h2>
            </div>
          </div>
          <div class="h-60">
            <VueApexCharts
              type="bar"
              height="230"
              :options="engineHoursChart.options"
              :series="engineHoursChart.series"
            />
          </div>
        </div>

        <!-- Card 3: Activity Breakdown Donut -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2.5">
              <Truck class="w-5 h-5 text-gray-700" />
              <h2 class="text-base font-bold text-gray-800">Activity breakdown (Hours)</h2>
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 items-center gap-4 h-60">
            <div class="flex justify-center">
              <VueApexCharts
                type="donut"
                height="220"
                :options="activityDonutChart.options"
                :series="activityDonutChart.series"
              />
            </div>
            <div class="space-y-3 pl-4">
              <div class="flex items-center gap-2 text-sm text-gray-700 font-medium">
                <span class="w-3.5 h-3.5 rounded-full bg-[#881337]"></span>
                <span>Ignition Off</span>
              </div>
              <div class="flex items-center gap-2 text-sm text-gray-700 font-medium">
                <span class="w-3.5 h-3.5 rounded-full bg-[#00875A]"></span>
                <span>Ignition</span>
              </div>
              <div class="flex items-center gap-2 text-sm text-gray-700 font-medium">
                <span class="w-3.5 h-3.5 rounded-full bg-[#E07A28]"></span>
                <span>Idling</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 4: Messages Received -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2.5">
              <MessageSquare class="w-5 h-5 text-gray-700" />
              <h2 class="text-base font-bold text-gray-800">Messages received</h2>
            </div>
          </div>
          <div class="h-60">
            <VueApexCharts
              type="bar"
              height="230"
              :options="messagesChart.options"
              :series="messagesChart.series"
            />
          </div>
        </div>

      </div>

      <!-- Bottom Section: Day Overview Gantt / Timeline Card (Image 2) -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100">
          <div class="flex items-center gap-2.5">
            <Calendar class="w-5 h-5 text-gray-700" />
            <h2 class="text-base font-bold text-gray-800">Day overview</h2>
          </div>
          <button class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
            <Filter class="w-4 h-4" />
          </button>
        </div>

        <!-- Timeline Grid -->
        <div class="overflow-x-auto">
          <div class="min-w-[760px]">
            <!-- Header Row with Hour Marks -->
            <div class="flex items-center text-xs text-gray-500 font-medium pb-4 border-b border-gray-100">
              <div class="w-64 pl-2 font-semibold">Assets ({{ assets.length }})</div>
              <div class="flex-1 grid grid-cols-8 text-center text-[11px] text-gray-500 font-medium">
                <span v-for="time in timeSlots" :key="time">{{ time }}</span>
              </div>
            </div>

            <!-- Asset Timeline Rows -->
            <div class="divide-y divide-gray-100">
              <div 
                v-for="asset in assets" 
                :key="asset.id"
                class="flex items-center py-5 hover:bg-gray-50/50 transition-colors"
              >
                <!-- Asset Info & Badges Column -->
                <div class="w-64 pr-4">
                  <div class="flex items-center gap-2.5 mb-2">
                    <span class="text-2xl">🚜</span>
                    <span class="font-bold text-amber-600 text-sm tracking-wide">{{ asset.code }}</span>
                  </div>
                  <div class="flex items-center gap-2 text-[11px] text-gray-600 font-medium">
                    <div 
                      v-for="(badge, bIdx) in asset.badges" 
                      :key="bIdx"
                      class="flex items-center gap-1"
                    >
                      <span 
                        class="w-2.5 h-2.5 rounded-sm"
                        :class="{
                          'bg-sky-100 border border-sky-300': badge.color === 'sky',
                          'bg-amber-500': badge.color === 'orange',
                          'bg-emerald-500': badge.color === 'teal',
                          'bg-yellow-700': badge.color === 'bronze',
                        }"
                      ></span>
                      <span>{{ badge.value }}%</span>
                    </div>
                  </div>
                </div>

                <!-- Horizontal Gantt Ribbon Strip -->
                <div class="flex-1 relative h-9 bg-gray-50/80 rounded border border-gray-100 overflow-hidden">
                  <!-- Hour Grid Guides -->
                  <div class="absolute inset-0 grid grid-cols-8 pointer-events-none divide-x divide-gray-200/40">
                    <div></div><div></div><div></div><div></div>
                    <div></div><div></div><div></div><div></div>
                  </div>

                  <!-- Color-Coded Activity Segments -->
                  <div
                    v-for="(seg, idx) in asset.timelines"
                    :key="idx"
                    class="absolute top-1 bottom-1 rounded-sm shadow-sm transition-all hover:brightness-110 cursor-pointer"
                    :style="{
                      left: seg.left_percent + '%',
                      width: Math.max(seg.width_percent, 1.2) + '%',
                    }"
                    :class="{
                      'bg-[#0D9488] border border-teal-600': seg.status === 'active',
                      'bg-[#EA580C] border border-orange-600': seg.status === 'idling',
                      'bg-[#78350F] border border-amber-900': seg.status === 'ignition_off' || seg.status === 'off'
                    }"
                    :title="`${seg.status.toUpperCase()} (${seg.start_time} - ${seg.end_time}, ${seg.duration_minutes} mins)`"
                  ></div>
                </div>

              </div>

              <div v-if="assets.length === 0" class="py-8 text-center text-sm text-gray-400">
                No active asset timelines recorded for {{ selectedDate }}.
              </div>
            </div>

          </div>
        </div>
      </div>

      </div>
    </div>
  </div>
</template>
