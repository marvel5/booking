import type { ApiErrorResponse, Booking, ValidationErrors } from '~/types/booking'

interface BookingPayload {
  resource_id: number
  start_at: string
  end_at: string
  customer_name: string
}

export function useBooking() {
  const booking = ref<Booking | null>(null)
  const pending = ref(false)
  const conflictMessage = ref<string | null>(null)
  const validationErrors = ref<ValidationErrors>({})

  function clearErrors() {
    conflictMessage.value = null
    validationErrors.value = {}
  }

  async function submit(payload: BookingPayload): Promise<boolean> {
    clearErrors()
    pending.value = true

    try {
      const data = await $fetch<{ data: Booking }>('/api/v1/bookings', {
        method: 'POST',
        body: payload,
      })

      if (!data?.data?.id) {
        conflictMessage.value = 'Wystąpił nieoczekiwany błąd. Spróbuj ponownie.'
        return false
      }

      booking.value = data.data
      return true
    }
    catch (error: unknown) {
      const err = error as { data?: ApiErrorResponse, status?: number, message?: string }

      if (err.data?.errors) {
        validationErrors.value = err.data.errors
      }
      else if (err.data?.message) {
        conflictMessage.value = err.data.message
      }
      else {
        conflictMessage.value = 'Wystąpił błąd sieci. Sprawdź połączenie i spróbuj ponownie.'
      }

      return false
    }
    finally {
      pending.value = false
    }
  }

  function reset() {
    booking.value = null
    clearErrors()
  }

  return { booking, pending, conflictMessage, validationErrors, submit, reset }
}
