import { defineConfig } from 'vite'
import { resolve } from 'path'
import { viteStaticCopy } from 'vite-plugin-static-copy'

export default defineConfig({
  plugins: [
    viteStaticCopy({
      targets: [
        {
          src: 'node_modules/bootstrap-italia/dist/fonts/*',
          dest: 'fonts'
        },
        {
          src: 'node_modules/bootstrap-italia/dist/svg/*',
          dest: 'images'
        },
        {
          src: 'node_modules/bootstrap-italia/dist/assets/*',
          dest: 'images'
        },
        {
          src: 'node_modules/leaflet/dist/images/*',
          dest: 'images'
        }
      ]
    })
  ],
  resolve: {
    alias: {
      '~bootstrap-italia': resolve(__dirname, 'node_modules/bootstrap-italia'),
    }
  },
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'src/js/main.js'),
      },
      output: {
        entryFileNames: 'js/[name].js',
        chunkFileNames: 'js/[name].js',
        assetFileNames: ({ name }) => {
          if (/\.(woff2?|ttf|eot|otf)$/.test(name)) return 'fonts/[name][extname]'
          if (/\.(png|jpe?g|svg|gif|ico)$/.test(name)) return 'images/[name][extname]'
          return 'css/[name][extname]'
        },
      },
    },
  },
})