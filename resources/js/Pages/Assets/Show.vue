<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
  asset: Object,
});
</script>

<template>
  <Head :title="'Asset: ' + asset.code" />

  <div class="min-h-screen bg-gray-50 p-6 lg:p-8">
    <div class="max-w-4xl mx-auto space-y-6">
      
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <span class="text-3xl">🚜</span>
          <div>
            <h1 class="text-2xl font-bold text-amber-600">{{ asset.code }}</h1>
            <p class="text-sm text-gray-500">{{ asset.name }} ({{ asset.type }})</p>
          </div>
        </div>
        <Link 
          href="/assets" 
          class="text-sm font-medium text-gray-600 hover:text-gray-900"
        >
          &larr; Back to Assets
        </Link>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
        <h2 class="text-base font-bold text-gray-800">Specifications</h2>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <span class="text-gray-400 block text-xs">Operational Timezone</span>
            <span class="font-medium text-gray-800">{{ asset.timezone }}</span>
          </div>
          <div>
            <span class="text-gray-400 block text-xs">Machine Category</span>
            <span class="font-medium text-gray-800 capitalize">{{ asset.type }}</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>
