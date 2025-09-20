import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

export default defineConfig(({ mode }) => {
  const isAdmin = mode === 'admin';
  const isFrontend = mode === 'frontend';

  return {
    plugins: [react()],
    root: 'src',
    build: {
      outDir: isAdmin ? '../dist/admin' : '../dist/frontend',
      emptyOutDir: true,
      rollupOptions: {
        input: isAdmin 
          ? resolve(__dirname, 'src/admin/index.tsx')
          : resolve(__dirname, 'src/frontend/index.tsx'),
        output: {
          entryFileNames: '[name].js',
          chunkFileNames: '[name]-[hash].js',
          assetFileNames: '[name]-[hash].[ext]',
          format: 'iife', // Build as Immediately Invoked Function Expression for WordPress compatibility
          name: 'MedX360', // Global variable name
        },
      },
    },
    resolve: {
      alias: {
        '@': resolve(__dirname, 'src'),
        '@components': resolve(__dirname, 'src/components'),
        '@pages': resolve(__dirname, 'src/pages'),
        '@hooks': resolve(__dirname, 'src/hooks'),
        '@services': resolve(__dirname, 'src/services'),
        '@store': resolve(__dirname, 'src/store'),
        '@types': resolve(__dirname, 'src/types'),
        '@utils': resolve(__dirname, 'src/utils'),
        '@assets': resolve(__dirname, 'src/assets'),
      },
    },
    server: {
      port: 3000,
      host: true,
    },
    define: {
      'process.env.NODE_ENV': JSON.stringify(mode === 'development' ? 'development' : 'production'),
    },
  };
});