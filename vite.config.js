import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
  plugins: [react()],
  server: {
    port: 3000,
    open: true,
    cors: true,
    host: true
  },
  build: {
    outDir: 'build',
    emptyOutDir: true,
    rollupOptions: {
      input: './src/index.jsx',
      output: {
        entryFileNames: 'index.js',
        assetFileNames: 'index.css'
      }
    }
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
});
