import path from 'path';
import { defineConfig, loadEnv } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, '.', '');
    return {
      // Base URL for assets (relative to domain root)
      // Must match where the dist files are served from
      base: '/admin/dist/',

      // Development server config
      server: {
        port: 3000,
        host: '0.0.0.0',
      },

      // Build configuration for production
      build: {
        // Output directory (relative to this config file)
        outDir: 'dist',

        // Empty the output directory before building
        emptyOutDir: true,

        // Generate sourcemaps for debugging (set to false for production)
        sourcemap: mode === 'development',

        // Minify for production
        minify: mode === 'production' ? 'esbuild' : false,

        // Rollup options
        rollupOptions: {
          output: {
            // Manual chunks for better caching
            manualChunks: {
              'react-vendor': ['react', 'react-dom'],
            },
          },
        },

        // Chunk size warning limit (in KB)
        chunkSizeWarningLimit: 1000,
      },

      // Plugins
      plugins: [react()],

      // Environment variables
      define: {
        'process.env.API_KEY': JSON.stringify(env.GEMINI_API_KEY),
        'process.env.GEMINI_API_KEY': JSON.stringify(env.GEMINI_API_KEY)
      },

      // Path aliases
      resolve: {
        alias: {
          '@': path.resolve(__dirname, '.'),
        }
      }
    };
});
