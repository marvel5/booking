<script setup lang="ts">
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '~/components/ui/card'

useSeoMeta({
  title: 'System Rezerwacji',
  description: 'Zarezerwuj apartament online',
})
</script>

<template>
  <main class="bg-muted/40 min-h-screen px-4 py-12">
    <div class="mx-auto max-w-lg">
      <Card>
        <CardHeader class="text-center">
          <CardTitle class="text-2xl">
            System Rezerwacji
          </CardTitle>
          <CardDescription>
            Wybierz apartament i termin pobytu
          </CardDescription>
        </CardHeader>
        <CardContent>
          <BookingForm />
        </CardContent>
      </Card>
    </div>
  </main>
</template>
