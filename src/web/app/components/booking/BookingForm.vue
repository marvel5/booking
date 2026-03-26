<script setup lang="ts">
import type { Resource } from '~/types/booking'

const emit = defineEmits<{
  submitted: []
}>()

const { booking, pending, conflictMessage, validationErrors, submit, reset } = useBooking()

const { data: resourcesData } = await useFetch<{ data: Resource[] }>('/api/v1/resources')
const resources = computed(() => resourcesData.value?.data ?? [])

const form = reactive({
  resource_id: null as number | null,
  start_at: '',
  end_at: '',
  customer_name: '',
})

function fieldError(field: string): string | undefined {
  return validationErrors.value[field]?.[0]
}

async function handleSubmit() {
  if (!form.resource_id) {
    return
  }

  const success = await submit({
    resource_id: form.resource_id,
    start_at: form.start_at,
    end_at: form.end_at,
    customer_name: form.customer_name,
  })

  if (success) {
    emit('submitted')
  }
}

function handleNewBooking() {
  reset()
  Object.assign(form, {
    resource_id: null,
    start_at: '',
    end_at: '',
    customer_name: '',
  })
}
</script>

<template>
  <div>
    <BookingResult
      v-if="booking"
      :booking="booking"
      @new-booking="handleNewBooking"
    />

    <form
      v-else
      class="space-y-5"
      @submit.prevent="handleSubmit"
    >
      <!-- Conflict / server error -->
      <div
        v-if="conflictMessage"
        class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
        role="alert"
      >
        {{ conflictMessage }}
      </div>

      <!-- Resource -->
      <div class="space-y-1.5">
        <label class="block text-sm font-medium text-gray-700" for="resource_id">
          Apartament / zasób
        </label>
        <select
          id="resource_id"
          v-model="form.resource_id"
          class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2"
          :class="fieldError('resource_id') ? 'border-red-400 focus:ring-red-300' : 'border-gray-300 focus:ring-blue-300'"
          required
        >
          <option :value="null" disabled>
            Wybierz apartament...
          </option>
          <option
            v-for="resource in resources"
            :key="resource.id"
            :value="resource.id"
          >
            {{ resource.name }}
            <template v-if="resource.capacity">
              ({{ resource.capacity }} os.)
            </template>
          </option>
        </select>
        <p v-if="fieldError('resource_id')" class="text-xs text-red-600">
          {{ fieldError('resource_id') }}
        </p>
      </div>

      <!-- Dates -->
      <div class="grid gap-4 sm:grid-cols-2">
        <div class="space-y-1.5">
          <label class="block text-sm font-medium text-gray-700" for="start_at">
            Data przyjazdu
          </label>
          <input
            id="start_at"
            v-model="form.start_at"
            class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2"
            :class="fieldError('start_at') ? 'border-red-400 focus:ring-red-300' : 'border-gray-300 focus:ring-blue-300'"
            type="datetime-local"
            required
          >
          <p v-if="fieldError('start_at')" class="text-xs text-red-600">
            {{ fieldError('start_at') }}
          </p>
        </div>

        <div class="space-y-1.5">
          <label class="block text-sm font-medium text-gray-700" for="end_at">
            Data wyjazdu
          </label>
          <input
            id="end_at"
            v-model="form.end_at"
            class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2"
            :class="fieldError('end_at') ? 'border-red-400 focus:ring-red-300' : 'border-gray-300 focus:ring-blue-300'"
            type="datetime-local"
            required
          >
          <p v-if="fieldError('end_at')" class="text-xs text-red-600">
            {{ fieldError('end_at') }}
          </p>
        </div>
      </div>

      <!-- Customer name -->
      <div class="space-y-1.5">
        <label class="block text-sm font-medium text-gray-700" for="customer_name">
          Imię i nazwisko
        </label>
        <input
          id="customer_name"
          v-model="form.customer_name"
          class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2"
          :class="fieldError('customer_name') ? 'border-red-400 focus:ring-red-300' : 'border-gray-300 focus:ring-blue-300'"
          type="text"
          placeholder="Jan Kowalski"
          maxlength="255"
          required
        >
        <p v-if="fieldError('customer_name')" class="text-xs text-red-600">
          {{ fieldError('customer_name') }}
        </p>
      </div>

      <!-- Submit -->
      <button
        class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
        type="submit"
        :disabled="pending"
      >
        <span v-if="pending">Rezerwuję...</span>
        <span v-else>Zarezerwuj</span>
      </button>
    </form>
  </div>
</template>
