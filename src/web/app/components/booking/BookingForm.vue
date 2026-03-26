<script setup lang="ts">
import type { Resource } from '~/types/booking'
import { Alert, AlertDescription } from '~/components/ui/alert'
import { Button } from '~/components/ui/button'
import { Input } from '~/components/ui/input'
import { Label } from '~/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '~/components/ui/select'

const emit = defineEmits<{
  submitted: []
}>()

const { booking, pending, conflictMessage, validationErrors, submit, reset } = useBooking()

const { data: resourcesData } = await useFetch<{ data: Resource[] }>('/api/v1/resources')
const resources = computed(() => resourcesData.value?.data ?? [])

const form = reactive({
  resource_id: '' as string,
  start_at: '',
  end_at: '',
  customer_name: '',
})

interface LocalErrors {
  resource_id?: string
  start_at?: string
  end_at?: string
  customer_name?: string
}

const localErrors = ref<LocalErrors>({})

function validate(): boolean {
  const errors: LocalErrors = {}

  if (!form.resource_id) {
    errors.resource_id = 'Wybierz apartament.'
  }
  if (!form.start_at) {
    errors.start_at = 'Podaj datę przyjazdu.'
  }
  if (!form.end_at) {
    errors.end_at = 'Podaj datę wyjazdu.'
  }
  else if (form.start_at && form.end_at <= form.start_at) {
    errors.end_at = 'Data wyjazdu musi być późniejsza niż przyjazdu.'
  }
  if (!form.customer_name.trim()) {
    errors.customer_name = 'Podaj imię i nazwisko.'
  }

  localErrors.value = errors
  return Object.keys(errors).length === 0
}

function fieldError(field: keyof LocalErrors): string | undefined {
  return localErrors.value[field] ?? validationErrors.value[field]?.[0]
}

async function handleSubmit() {
  if (!validate()) {
    return
  }

  const success = await submit({
    resource_id: Number(form.resource_id),
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
  localErrors.value = {}
  Object.assign(form, { resource_id: '', start_at: '', end_at: '', customer_name: '' })
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
      novalidate
      @submit.prevent="handleSubmit"
    >
      <!-- Conflict error -->
      <Alert v-if="conflictMessage" variant="destructive">
        <AlertDescription>{{ conflictMessage }}</AlertDescription>
      </Alert>

      <!-- Resource -->
      <div class="space-y-2">
        <Label for="resource_id">Apartament / zasób</Label>
        <Select v-model="form.resource_id">
          <SelectTrigger
            id="resource_id"
            :class="fieldError('resource_id') ? 'border-destructive' : ''"
          >
            <SelectValue placeholder="Wybierz apartament..." />
          </SelectTrigger>
          <SelectContent>
            <SelectItem
              v-for="resource in resources"
              :key="resource.id"
              :value="String(resource.id)"
            >
              {{ resource.name }}
              <span v-if="resource.capacity" class="text-muted-foreground">
                ({{ resource.capacity }} os.)
              </span>
            </SelectItem>
          </SelectContent>
        </Select>
        <p v-if="fieldError('resource_id')" class="text-sm text-destructive">
          {{ fieldError('resource_id') }}
        </p>
      </div>

      <!-- Dates -->
      <div class="grid gap-4 sm:grid-cols-2">
        <div class="space-y-2">
          <Label for="start_at">Data przyjazdu</Label>
          <Input
            id="start_at"
            v-model="form.start_at"
            type="datetime-local"
            required
            :class="fieldError('start_at') ? 'border-destructive' : ''"
          />
          <p v-if="fieldError('start_at')" class="text-sm text-destructive">
            {{ fieldError('start_at') }}
          </p>
        </div>

        <div class="space-y-2">
          <Label for="end_at">Data wyjazdu</Label>
          <Input
            id="end_at"
            v-model="form.end_at"
            type="datetime-local"
            required
            :class="fieldError('end_at') ? 'border-destructive' : ''"
          />
          <p v-if="fieldError('end_at')" class="text-sm text-destructive">
            {{ fieldError('end_at') }}
          </p>
        </div>
      </div>

      <!-- Customer name -->
      <div class="space-y-2">
        <Label for="customer_name">Imię i nazwisko</Label>
        <Input
          id="customer_name"
          v-model="form.customer_name"
          type="text"
          placeholder="Jan Kowalski"
          maxlength="255"
          required
          :class="fieldError('customer_name') ? 'border-destructive' : ''"
        />
        <p v-if="fieldError('customer_name')" class="text-sm text-destructive">
          {{ fieldError('customer_name') }}
        </p>
      </div>

      <!-- Submit -->
      <Button type="submit" class="w-full" :disabled="pending">
        {{ pending ? 'Rezerwuję...' : 'Zarezerwuj' }}
      </Button>
    </form>
  </div>
</template>
