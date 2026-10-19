<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Plus, Edit2, Trash2, X, AlertCircle, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
  assets: Object,
});

// Modal State
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingAssetId = ref(null);

// Form handling with Inertia useForm
const form = useForm({
  code: '',
  name: '',
  type: 'loader',
  timezone: 'Asia/Dubai',
});

const openCreateModal = () => {
  isEditing.value = false;
  editingAssetId.value = null;
  form.reset();
  form.clearErrors();
  isModalOpen.value = true;
};

const openEditModal = (asset) => {
  isEditing.value = true;
  editingAssetId.value = asset.id;
  form.clearErrors();
  form.code = asset.code;
  form.name = asset.name;
  form.type = asset.type;
  form.timezone = asset.timezone || 'Asia/Dubai';
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  form.reset();
  form.clearErrors();
};

const submitForm = () => {
  if (isEditing.value) {
    form.put(`/assets/${editingAssetId.value}`, {
      onSuccess: () => closeModal(),
    });
  } else {
    form.post('/assets', {
      onSuccess: () => closeModal(),
    });
  }
};

const deleteAsset = (asset) => {
  if (confirm(`Are you sure you want to decommission/delete machine ${asset.code}? All associated activity records will be deleted.`)) {
    router.delete(`/assets/${asset.id}`);
  }
};
</script>

<template>
  <Head title="Fleet Assets Directory" />

  <div class="min-h-screen bg-gray-50 p-6 lg:p-8">
    <div class="max-w-6xl mx-auto space-y-6">
      
      <!-- Top Action Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <Link 
              href="/"
              class="p-1.5 text-gray-500 hover:text-gray-800 rounded-lg hover:bg-gray-200 transition-colors"
              title="Return to Telematics Dashboard"
            >
              <ArrowLeft class="w-5 h-5" />
            </Link>
            <h1 class="text-2xl font-bold text-gray-900">Fleet Asset Inventory</h1>
          </div>
          <p class="text-sm text-gray-500 mt-1 ml-9">Manage heavy equipment machinery, operational statuses, and telematics configurations</p>
        </div>

        <button 
          @click="openCreateModal"
          class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors"
        >
          <Plus class="w-4 h-4" />
          Register New Asset
        </button>
      </div>

      <!-- Assets Table Card -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-gray-500 font-semibold">
              <tr>
                <th scope="col" class="px-6 py-3.5 text-left">Equipment</th>
                <th scope="col" class="px-6 py-3.5 text-left">Machine Code</th>
                <th scope="col" class="px-6 py-3.5 text-left">Category</th>
                <th scope="col" class="px-6 py-3.5 text-left">Current Telematics Status</th>
                <th scope="col" class="px-6 py-3.5 text-left">Timezone</th>
                <th scope="col" class="px-6 py-3.5 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
              <tr v-for="asset in assets.data" :key="asset.id" class="hover:bg-gray-50/50 transition-colors">
                <td class="px-6 py-4 flex items-center gap-3">
                  <span class="text-2xl">🚜</span>
                  <div>
                    <div class="font-semibold text-gray-900">{{ asset.name }}</div>
                    <div class="text-xs text-gray-400">ID: #{{ asset.id }}</div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    {{ asset.code }}
                  </span>
                </td>
                <td class="px-6 py-4 capitalize font-medium text-gray-700">
                  {{ asset.type.replace('_', ' ') }}
                </td>
                
                <!-- Last Active Status Column -->
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <span class="relative flex h-2.5 w-2.5">
                      <span 
                        v-if="asset.last_status === 'active'"
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"
                      ></span>
                      <span 
                        class="relative inline-flex rounded-full h-2.5 w-2.5"
                        :class="{
                          'bg-teal-500': asset.last_status === 'active',
                          'bg-orange-500': asset.last_status === 'idling',
                          'bg-amber-900': asset.last_status === 'ignition_off' || asset.last_status === 'off',
                          'bg-gray-400': asset.last_status === 'offline'
                        }"
                      ></span>
                    </span>

                    <span 
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider"
                      :class="{
                        'bg-teal-50 text-teal-700 border border-teal-200': asset.last_status === 'active',
                        'bg-orange-50 text-orange-700 border border-orange-200': asset.last_status === 'idling',
                        'bg-amber-50 text-amber-900 border border-amber-200': asset.last_status === 'ignition_off' || asset.last_status === 'off',
                        'bg-gray-100 text-gray-600 border border-gray-300': asset.last_status === 'offline'
                      }"
                    >
                      {{ asset.last_status === 'offline' ? 'Offline' : asset.last_status.replace('_', ' ') }}
                    </span>
                  </div>
                  <div class="text-[11px] text-gray-400 mt-1 pl-4.5">
                    {{ asset.is_offline ? 'Last ping: ' + asset.last_active_diff : 'Last active: ' + asset.last_active_time }}
                  </div>
                </td>

                <td class="px-6 py-4 text-gray-500 font-mono text-xs">
                  {{ asset.timezone }}
                </td>
                <td class="px-6 py-4 text-right space-x-2">
                  <button 
                    @click="openEditModal(asset)"
                    class="p-1.5 text-gray-400 hover:text-amber-600 rounded hover:bg-amber-50 transition-colors"
                    title="Edit Asset"
                  >
                    <Edit2 class="w-4 h-4" />
                  </button>
                  <button 
                    @click="deleteAsset(asset)"
                    class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-red-50 transition-colors"
                    title="Decommission Asset"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </td>
              </tr>
              <tr v-if="!assets.data || assets.data.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                  <AlertCircle class="w-8 h-8 mx-auto mb-2 text-gray-300" />
                  No assets currently registered in fleet.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- Create / Edit Modal -->
    <div 
      v-if="isModalOpen" 
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/40 backdrop-blur-sm"
    >
      <div class="bg-white rounded-2xl shadow-xl border border-gray-100 max-w-md w-full overflow-hidden">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <h2 class="text-base font-bold text-gray-900">
            {{ isEditing ? 'Edit Asset' : 'Register New Heavy Equipment' }}
          </h2>
          <button @click="closeModal" class="p-1 text-gray-400 hover:text-gray-600 rounded-lg">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Form Body -->
        <form @submit.prevent="submitForm" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
              Asset Code (e.g. BL-08)
            </label>
            <input 
              v-model="form.code"
              type="text" 
              placeholder="e.g. BL-09, Q-47900"
              class="w-full text-sm rounded-lg border-gray-300 focus:border-amber-500 focus:ring focus:ring-amber-200"
              required
            />
            <div v-if="form.errors.code" class="text-xs text-red-600 mt-1">{{ form.errors.code }}</div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
              Equipment Name
            </label>
            <input 
              v-model="form.name"
              type="text" 
              placeholder="e.g. Backhoe Loader 09"
              class="w-full text-sm rounded-lg border-gray-300 focus:border-amber-500 focus:ring focus:ring-amber-200"
              required
            />
            <div v-if="form.errors.name" class="text-xs text-red-600 mt-1">{{ form.errors.name }}</div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
              Category / Type
            </label>
            <select 
              v-model="form.type"
              class="w-full text-sm rounded-lg border-gray-300 focus:border-amber-500 focus:ring focus:ring-amber-200"
            >
              <option value="loader">Loader</option>
              <option value="wheel_loader">Wheel Loader</option>
              <option value="bulldozer">Bulldozer</option>
              <option value="excavator">Excavator</option>
            </select>
            <div v-if="form.errors.type" class="text-xs text-red-600 mt-1">{{ form.errors.type }}</div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
              Deployment Timezone
            </label>
            <input 
              v-model="form.timezone"
              type="text" 
              placeholder="e.g. Asia/Dubai, UTC, America/New_York"
              class="w-full text-sm rounded-lg border-gray-300 focus:border-amber-500 focus:ring focus:ring-amber-200"
              required
            />
            <div v-if="form.errors.timezone" class="text-xs text-red-600 mt-1">{{ form.errors.timezone }}</div>
          </div>

          <!-- Modal Actions -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <button 
              type="button" 
              @click="closeModal"
              class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="form.processing"
              class="px-5 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors"
            >
              {{ isEditing ? 'Update Asset' : 'Create Asset' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>
