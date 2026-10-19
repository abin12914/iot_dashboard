<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
  assets: Object,
});
</script>

<template>
  <Head title="Fleet Assets Directory" />

  <div class="min-h-screen bg-gray-50 p-6 lg:p-8">
    <div class="max-w-6xl mx-auto space-y-6">
      
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Fleet Assets Directory</h1>
          <p class="text-sm text-gray-500 mt-1">Manage registered heavy machinery and telematics devices</p>
        </div>
        <Link 
          href="/"
          class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors"
        >
          &larr; Back to Telematics Dashboard
        </Link>
      </div>

      <!-- Assets Table Card -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50 text-gray-500 font-semibold">
            <tr>
              <th scope="col" class="px-6 py-3.5 text-left">Code</th>
              <th scope="col" class="px-6 py-3.5 text-left">Name</th>
              <th scope="col" class="px-6 py-3.5 text-left">Type</th>
              <th scope="col" class="px-6 py-3.5 text-left">Timezone</th>
              <th scope="col" class="px-6 py-3.5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 text-gray-700">
            <tr v-for="asset in assets.data" :key="asset.id" class="hover:bg-gray-50/50">
              <td class="px-6 py-4 font-bold text-amber-600">
                {{ asset.code }}
              </td>
              <td class="px-6 py-4 font-medium text-gray-900">
                {{ asset.name }}
              </td>
              <td class="px-6 py-4 capitalize">
                {{ asset.type }}
              </td>
              <td class="px-6 py-4 text-gray-500 font-mono text-xs">
                {{ asset.timezone }}
              </td>
              <td class="px-6 py-4 text-right space-x-3 text-xs font-semibold">
                <Link :href="'/assets/' + asset.id" class="text-teal-600 hover:underline">View</Link>
              </td>
            </tr>
            <tr v-if="!assets.data || assets.data.length === 0">
              <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                No assets registered yet.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</template>
