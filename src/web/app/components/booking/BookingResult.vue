<script setup lang="ts">
import type { Booking } from '~/types/booking'
import { Button } from '~/components/ui/button'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '~/components/ui/card'

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
  <Card class="border-green-200 bg-green-50 text-center">
    <CardHeader>
      <div class="mb-2 flex justify-center">
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
      <CardTitle class="text-green-800">
        Rezerwacja potwierdzona!
      </CardTitle>
      <CardDescription class="text-green-700">
        Numer rezerwacji: <strong>#{{ props.booking.id }}</strong>
      </CardDescription>
    </CardHeader>

    <CardContent>
      <dl class="mx-auto max-w-xs space-y-2 text-left text-sm">
        <div class="flex justify-between gap-4">
          <dt class="text-muted-foreground">Klient</dt>
          <dd class="font-medium">{{ props.booking.customer_name }}</dd>
        </div>
        <div class="flex justify-between gap-4">
          <dt class="text-muted-foreground">Od</dt>
          <dd class="font-medium">{{ formatDate(props.booking.start_at) }}</dd>
        </div>
        <div class="flex justify-between gap-4">
          <dt class="text-muted-foreground">Do</dt>
          <dd class="font-medium">{{ formatDate(props.booking.end_at) }}</dd>
        </div>
      </dl>
    </CardContent>

    <CardFooter class="justify-center">
      <Button variant="outline" @click="emit('newBooking')">
        Nowa rezerwacja
      </Button>
    </CardFooter>
  </Card>
</template>
