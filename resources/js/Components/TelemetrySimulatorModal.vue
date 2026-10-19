<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { Radio, Send, X, AlertTriangle, CheckCircle, Wifi, WifiOff, Clock } from 'lucide-vue-next';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  }
});

const emit = defineEmits(['close']);

const page = usePage();
const simulatorAssets = computed(() => page.props.simulatorAssets || []);

const selectedAssetId = ref(simulatorAssets.value[0]?.id || '');
const pingStatus = ref('active');
const customTimestamp = ref('');
const isCustomTime = ref(false);

const isSubmitting = ref(false);
const responseLog = ref(null);
const responseError = ref(null);

const triggerPing = async (statusOverride = null) => {
  if (!selectedAssetId.value) {
    responseError.value = 'Please select a machine first.';
    return;
  }

  isSubmitting.value = true;
  responseLog.value = null;
  responseError.value = null;

  const payload = {
    asset_id: parseInt(selectedAssetId.value),
    status: statusOverride || pingStatus.value,
  };

  if (isCustomTime.value && customTimestamp.value) {
    payload.timestamp = new Date(customTimestamp.value).toISOString();
  }

  try {
    const res = await fetch('/telematics/ping', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify(payload),
    });

    const data = await res.json();

    if (res.ok) {
      responseLog.value = {
        success: true,
        message: data.message,
        interval: data.interval,
        sent_at: new Date().toLocaleTimeString(),
      };
      // Soft refresh Inertia to update lastPing and timeline charts
      router.reload({ only: ['lastPing', 'assets'] });
    } else {
      responseError.value = data.message || 'Ping rejected by gateway.';
      if (data.errors) {
        responseError.value += ' ' + Object.values(data.errors).flat().join(', ');
      }
    }
  } catch (err) {
    responseError.value = 'Network error communicating with telematics gateway: ' + err.message;
  } finally {
    isSubmitting.value = false;
  }
};

const simulateCorruptPing = async () => {
  isSubmitting.value = true;
  responseLog.value = null;
  responseError.value = null;

  try {
    const res = await fetch('/telematics/ping', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        asset_id: 99999, // Non-existent asset ID
        status: 'corrupt_canbus_reading',
      }),
    });

    const data = await res.json();
    responseLog.value = {
      success: false,
      isValidationAnomaly: true,
      status: res.status,
      message: data.message,
      errors: data.errors,
      notification_action: data.notification_action,
      is_duplicate: data.is_duplicate,
      sent_at: new Date().toLocaleTimeString(),
    };
  } catch (err) {
    responseError.value = 'Failed sending simulation payload: ' + err.message;
  } finally {
    isSubmitting.value = false;
  }
};

const simulateOffline = (assetId) => {
  // Offline state occurs when no pings have been received in > 24 hours.
  // Setting a historical ping 48 hours ago tests this cleanly.
  selectedAssetId.value = assetId;
  isCustomTime.value = true;
  const d = new Date(Date.now() - 48 * 3600 * 1000);
  customTimestamp.value = d.toISOString().slice(0, 16);
  triggerPing('ignition_off');
};
</script>

<template>
  <div 
    v-if="isOpen" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
  >
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 max-w-lg w-full overflow-hidden transition-all">
      
      <!-- Modal Header -->
      <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-slate-900 to-slate-800 text-white">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg bg-amber-500/20 border border-amber-500/30 flex items-center justify-center text-amber-400">
            <Radio class="w-4 h-4 animate-pulse" />
          </div>
          <div>
            <h2 class="text-sm font-bold tracking-wide">IoT Telemetry Simulator</h2>
            <p class="text-[11px] text-slate-400">Fire live hardware CAN-bus pings or test edge anomalies</p>
          </div>
        </div>
        <button 
          @click="emit('close')" 
          class="p-1 text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/60 transition-colors"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-6 space-y-5">
        
        <!-- Target Asset Selection -->
        <div>
          <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
            Target Machine
          </label>
          <select 
            v-model="selectedAssetId"
            class="w-full text-sm rounded-lg border-gray-300 focus:border-amber-500 focus:ring focus:ring-amber-200"
          >
            <option v-for="a in simulatorAssets" :key="a.id" :value="a.id">
              {{ a.code }} - {{ a.name }} ({{ a.type }})
            </option>
          </select>
        </div>

        <!-- Telemetry Operational State Selection -->
        <div>
          <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
            Telemetry Status
          </label>
          <div class="grid grid-cols-3 gap-2">
            <button
              type="button"
              @click="pingStatus = 'active'"
              :class="pingStatus === 'active' 
                ? 'bg-emerald-50 border-emerald-500 text-emerald-800 ring-2 ring-emerald-200' 
                : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'"
              class="flex flex-col items-center justify-center py-2.5 px-3 rounded-lg border text-xs font-semibold transition-all"
            >
              <span class="w-2.5 h-2.5 rounded-full bg-[#00875A] mb-1.5"></span>
              Active (Working)
            </button>

            <button
              type="button"
              @click="pingStatus = 'idling'"
              :class="pingStatus === 'idling' 
                ? 'bg-amber-50 border-amber-500 text-amber-800 ring-2 ring-amber-200' 
                : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'"
              class="flex flex-col items-center justify-center py-2.5 px-3 rounded-lg border text-xs font-semibold transition-all"
            >
              <span class="w-2.5 h-2.5 rounded-full bg-[#E07A28] mb-1.5"></span>
              Idling
            </button>

            <button
              type="button"
              @click="pingStatus = 'ignition_off'"
              :class="pingStatus === 'ignition_off' 
                ? 'bg-rose-50 border-rose-500 text-rose-800 ring-2 ring-rose-200' 
                : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'"
              class="flex flex-col items-center justify-center py-2.5 px-3 rounded-lg border text-xs font-semibold transition-all"
            >
              <span class="w-2.5 h-2.5 rounded-full bg-[#881337] mb-1.5"></span>
              Ignition Off
            </button>
          </div>
        </div>

        <!-- Optional Custom Timestamp -->
        <div class="pt-2 border-t border-gray-100">
          <div class="flex items-center justify-between mb-2">
            <label class="text-xs font-medium text-gray-700 flex items-center gap-1.5">
              <Clock class="w-3.5 h-3.5 text-gray-400" />
              Custom Packet Timestamp
            </label>
            <button 
              type="button" 
              @click="isCustomTime = !isCustomTime" 
              class="text-xs text-amber-600 hover:text-amber-700 font-semibold"
            >
              {{ isCustomTime ? 'Use Current Time' : 'Specify Past Time' }}
            </button>
          </div>
          <input 
            v-if="isCustomTime"
            v-model="customTimestamp"
            type="datetime-local" 
            class="w-full text-xs rounded-lg border-gray-300 focus:border-amber-500 focus:ring focus:ring-amber-200"
          />
        </div>

        <!-- Feedback & Log Alert -->
        <div v-if="responseLog" class="p-3.5 rounded-xl text-xs space-y-1.5" :class="responseLog.success ? 'bg-emerald-50 text-emerald-900 border border-emerald-200' : 'bg-rose-50 text-rose-900 border border-rose-200'">
          <div class="flex items-center gap-2 font-bold">
            <CheckCircle v-if="responseLog.success" class="w-4 h-4 text-emerald-600" />
            <AlertTriangle v-else class="w-4 h-4 text-rose-600" />
            <span>{{ responseLog.success ? 'Telemetry Packet Ingested' : 'Validation Anomaly Caught (HTTP 422)' }}</span>
            <span class="ml-auto text-[10px] font-mono text-gray-500">{{ responseLog.sent_at }}</span>
          </div>
          <p class="text-[11px]">{{ responseLog.message }}</p>
          <div v-if="responseLog.interval" class="font-mono text-[11px] bg-white/70 p-2 rounded border border-emerald-200/60 mt-1">
            Status: <strong>{{ responseLog.interval.status }}</strong> | Duration: <strong>{{ responseLog.interval.duration_minutes }} min(s)</strong> | Date: <strong>{{ responseLog.interval.local_date }}</strong>
          </div>
          <div v-if="responseLog.errors" class="space-y-1.5 mt-1.5">
            <div class="font-mono text-[11px] bg-white/70 p-2 rounded border border-rose-200/60 text-rose-700">
              Payload Errors: {{ JSON.stringify(responseLog.errors) }}
            </div>

            <!-- Email Delivery Status Badge -->
            <div 
              class="p-2.5 rounded-lg border text-xs flex items-start gap-2"
              :class="responseLog.is_duplicate 
                ? 'bg-amber-50/90 border-amber-200 text-amber-900' 
                : 'bg-blue-50/90 border-blue-200 text-blue-900'"
            >
              <span class="text-base leading-none">
                {{ responseLog.is_duplicate ? "🛡️" : "📧" }}
              </span>
              <div>
                <div class="font-bold">
                  {{ responseLog.is_duplicate ? "DUPLICATE ANOMALY THROTTLED" : "ADMIN ALERT EMAIL QUEUED" }}
                </div>
                <div class="text-[11px] mt-0.5 opacity-90">
                  {{ responseLog.notification_action }}
                </div>
                <div class="text-[10px] text-gray-500 mt-1">
                  {{ responseLog.is_duplicate 
                    ? "Subsequent identical anomaly pings within 60 minutes are deduplicated to protect admin inboxes." 
                    : "First anomaly encountered: dispatched immediately to Queue [notifications]." }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="responseError" class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 flex items-center gap-2">
          <AlertTriangle class="w-4 h-4 shrink-0 text-red-500" />
          <span>{{ responseError }}</span>
        </div>

        <!-- Action Buttons -->
        <div class="pt-3 border-t border-gray-100 flex flex-col sm:flex-row gap-2.5">
          <button 
            type="button" 
            :disabled="isSubmitting"
            @click="triggerPing()"
            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white text-xs font-bold rounded-lg shadow-sm transition-colors"
          >
            <Send class="w-3.5 h-3.5" />
            Fire Telematics Ping
          </button>

          <button 
            type="button" 
            :disabled="isSubmitting"
            @click="simulateCorruptPing()"
            class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold rounded-lg transition-colors"
            title="Sends corrupt payload to test admin alert notification queue"
          >
            <AlertTriangle class="w-3.5 h-3.5 text-rose-600" />
            Test Anomaly Ping
          </button>
        </div>

      </div>

    </div>
  </div>
</template>
