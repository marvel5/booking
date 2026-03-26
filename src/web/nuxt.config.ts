import tailwindcss from '@tailwindcss/vite'

export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  css: ['~/assets/css/main.css'],

  vite: {
    plugins: [tailwindcss()],
  },

  runtimeConfig: {
    // Server-side only — used by routeRules proxy
    apiUrl: process.env.NUXT_API_URL ?? 'http://localhost',
  },

  nitro: {
    routeRules: {
      '/api/v1/**': {
        proxy: `${process.env.NUXT_API_URL ?? 'http://localhost'}/api/v1/**`,
      },
    },
  },
})
