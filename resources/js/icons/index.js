/**
 * Registrasi collection ikon lokal.
 *
 * PENTING: import dari `@iconify/vue/offline`, BUKAN `@iconify/vue`.
 *
 * Entry point `offline` berisi komponen yang sama persis (ekspor `Icon`,
 * `addCollection`, `addIcon`) tetapi TIDAK memuat satu pun kode jaringan —
 * tidak ada fetch, XMLHttpRequest, atau daftar API host. Entry point utama
 * memuat semuanya (sekitar 29 KB) dan akan memanggil
 * https://api.iconify.design saat runtime kalau ada ikon yang belum
 * di-register.
 *
 * Karena itu aplikasi ini tidak bergantung pada CDN pihak ketiga, tidak
 * perlu host Iconify di CSP, dan IP user tidak terkirim ke Iconify.
 *
 * File `mdi.json` dihasilkan oleh `npm run icons` — jangan diedit manual.
 */
import { addCollection } from '@iconify/vue/offline'
import mdiIcons from './mdi.json'

addCollection(mdiIcons)

export { mdiIcons }
