import tailwindcss from '@tailwindcss/vite'

export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  css: ['~/assets/css/main.css'],

  vite: {
    plugins: [tailwindcss()],
  },

  runtimeConfig: {
    public: {
      // Override in production via NUXT_PUBLIC_API_BASE env var
      apiBase: process.env.NUXT_PUBLIC_API_BASE ?? 'http://localhost',
    },
  },

  nitro: {
    routeRules: {
      '/api/**': {
        proxy: `${process.env.NUXT_PUBLIC_API_BASE ?? 'http://localhost'}/api/**`,
      },
    },
  },
})
