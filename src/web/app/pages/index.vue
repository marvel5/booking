<script setup lang="ts">
useSeoMeta({
  title: 'System Rezerwacji',
  description: 'Zarezerwuj apartament online',
})
</script>

<template>
  <main class="min-h-screen bg-gray-50 px-4 py-12">
    <div class="mx-auto max-w-lg">
      <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">
          System Rezerwacji
        </h1>
        <p class="mt-2 text-gray-500">
          Wybierz apartament i termin pobytu
        </p>
      </div>

      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <BookingForm />
      </div>
    </div>
  </main>
</template>
