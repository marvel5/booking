export interface Resource {
  id: number
  name: string
  description: string | null
  capacity: number
}

export interface Booking {
  id: number
  resource_id: number
  start_at: string
  end_at: string
  customer_name: string
  created_at: string
  updated_at: string
}

export interface ValidationErrors {
  [field: string]: string[]
}

export interface ApiErrorResponse {
  message: string
  errors?: ValidationErrors
}
