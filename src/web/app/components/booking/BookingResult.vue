<script setup lang="ts">
import type { Booking } from '~/types/booking'

const props = defineProps<{
  booking: Booking
}>()

const emit = defineEmits<{
  newBooking: []
}>()

function formatDate(iso: string): string {
  return new Date(iso).toLocaleString('pl-PL', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<template>
  <div class="rounded-2xl border border-green-200 bg-green-50 p-6 text-center">
    <div class="mb-3 flex justify-center">
      <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
        <svg
          class="h-6 w-6 text-green-600"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
      </div>
    </div>

    <h2 class="mb-1 text-xl font-semibold text-green-800">
      Rezerwacja potwierdzona!
    </h2>
    <p class="mb-4 text-sm text-green-700">
      Numer rezerwacji: <strong>#{{ props.booking.id }}</strong>
    </p>

    <dl class="mx-auto mb-6 max-w-xs space-y-2 text-left text-sm">
      <div class="flex justify-between gap-4">
        <dt class="text-gray-500">Klient</dt>
        <dd class="font-medium text-gray-900">{{ props.booking.customer_name }}</dd>
      </div>
      <div class="flex justify-between gap-4">
        <dt class="text-gray-500">Od</dt>
        <dd class="font-medium text-gray-900">{{ formatDate(props.booking.start_at) }}</dd>
      </div>
      <div class="flex justify-between gap-4">
        <dt class="text-gray-500">Do</dt>
        <dd class="font-medium text-gray-900">{{ formatDate(props.booking.end_at) }}</dd>
      </div>
    </dl>

    <button
      class="rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-green-700"
      @click="emit('newBooking')"
    >
      Nowa rezerwacja
    </button>
  </div>
</template>
