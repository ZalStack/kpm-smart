/**
 * Generator collection ikon lokal.
 *
 * Masalah yang diselesaikan:
 * Aplikasi memakai <Icon icon="mdi:..."> dari @iconify/vue. Kalau collection
 * tidak di-register, komponen itu mengambil JSON ikon dari
 * https://api.iconify.design saat runtime. Consequences:
 *   - CSP harus mengizinkan host tersebut (connect-src + img-src)
 *   - Aplikasi bergantung pada CDN pihak ketiga: kalau API down/slow, semua
 *     ikon hilang
 *   - IP user terkirim ke Iconify
 *
 * Script ini memindai seluruh sumber `resources/js`, mengambil nama ikon yang
 * benar-benar dipakai, lalu menulis collection Iconify berisi HANYA ikon-ikon
 * itu ke `resources/js/icons/mdi.json`. Collection itu di-register di app.js
 * lewat addCollection(), sehingga @iconify/vue resolving-nya dari memori dan
 * tidak pernah menyentuh jaringan.
 *
 * Alias (mis. `graduation-cap` -> `school`) ikut di-resolve supaya nama yang
 * dipakai di template tetap sama.
 *
 * Jalankan ulang setiap kali ada nama ikon baru:
 *     npm run icons
 *
 * Test `IconBundleTest` akan gagal kalau ada nama ikon di source yang belum
 * ada di collection hasil generate.
 */

import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))

const ROOT = path.resolve(__dirname, '..')
const SOURCE_DIR = path.join(ROOT, 'resources/js')
const SOURCE_PKG = path.join(ROOT, 'node_modules/@iconify-json/mdi/icons.json')
const OUT_FILE = path.join(ROOT, 'resources/js/icons/mdi.json')

/** Direktori yang tidak perlu dipindai. */
const IGNORED_DIRS = new Set(['node_modules', '.git', 'icons'])

/**
 * Buang komentar sebelum memindai nama ikon.
 *
 * Tanpa ini, nama ikon yang sengaja disebut di dalam catatan penjelas ikut
 * terhitung. Contohnya `mdi:hands-up` — ikon yang tidak pernah ada di MDI,
 * sudah diganti `mdi:hand-wave`, tapi masih disebut di komentar. Kalau ikut
 * dihitung, `npm run icons` akan selalu gagal.
 *
 * Hanya tiga bentuk komentar yang dihapus, supaya string biasa (mis. URL
 * `https://...`) tidak ikut terpotong.
 *
 * @param {string} content
 * @returns {string}
 */
function stripComments(content) {
    return content
        .replace(/\/\*[\s\S]*?\*\//g, ' ') // /* ... */
        .replace(/<!--[\s\S]*?-->/g, ' ') // <!-- ... -->
        .replace(/^[ \t]*(?:\/\/|\*)[^\n]*$/gm, ' ') // baris yang diawali // atau *
}

/**
 * Kumpulkan seluruh file sumber yang mungkin memuat nama ikon.
 * @returns {string[]}
 */
function collectSourceFiles(dir) {
    const found = []

    for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
        const full = path.join(dir, entry.name)

        if (entry.isDirectory()) {
            if (IGNORED_DIRS.has(entry.name)) continue
            found.push(...collectSourceFiles(full))
            continue
        }

        if (/\.(vue|js|ts|jsx|tsx)$/.test(entry.name)) {
            found.push(full)
        }
    }

    return found
}

/**
 * Ambil semua nama ikon bertipe `mdi:` dari isi file.
 *
 * Sengaja memindai SELURUH isi file, bukan hanya atribut `icon="..."`, karena
 * ada pemakaian dinamis seperti `:icon="cond ? 'mdi:a' : 'mdi:b'"` dan
 * `:icon="group.icon"` yang nilainya berasal dari literal di dalam JS.
 *
 * @returns {Set<string>}
 */
function collectIconNames(files) {
    const names = new Set()
    const pattern = /\bmdi:([a-zA-Z0-9-]+)/g

    for (const file of files) {
        const content = stripComments(fs.readFileSync(file, 'utf8'))
        let match

        while ((match = pattern.exec(content)) !== null) {
            names.add(match[1])
        }
    }

    return names
}

/**
 * Resolve alias ke nama ikon induk, mengikuti rantai alias.
 * @returns {string|null}
 */
function resolveAlias(name, icons, aliases, seen = new Set()) {
    if (icons[name]) return name
    if (seen.has(name)) return null

    const alias = aliases[name]
    if (!alias) return null

    seen.add(name)
    return resolveAlias(alias.parent, icons, aliases, seen)
}

function main() {
    if (!fs.existsSync(SOURCE_PKG)) {
        console.error(
            'ERROR: node_modules/@iconify-json/mdi/icons.json tidak ditemukan.\n' +
            '       Jalankan: npm install\n' +
            '       (@iconify-json/mdi hanya dipakai saat generate, bukan saat runtime.)'
        )
        process.exit(1)
    }

    const collection = JSON.parse(fs.readFileSync(SOURCE_PKG, 'utf8'))
    const sourceFiles = collectSourceFiles(SOURCE_DIR)
    const names = [...collectIconNames(sourceFiles)].sort()

    const picked = {}
    const unresolved = []
    const aliasCount = []

    for (const name of names) {
        const resolved = resolveAlias(name, collection.icons, collection.aliases)

        if (!resolved) {
            unresolved.push(name)
            continue
        }

        if (resolved !== name) aliasCount.push(`${name} -> ${resolved}`)

        // Di-map kembali ke nama yang dipakai template.
        picked[name] = collection.icons[resolved]
    }

    if (unresolved.length) {
        console.error('ERROR: ikon berikut tidak ada di Material Design Icons:')
        for (const name of unresolved) console.error(`  - mdi:${name}`)
        console.error('\nIkon MDI tidak bisa ditebak. Ganti dengan nama yang valid, ')
        console.error('lalu jalankan ulang `npm run icons`.')
        process.exit(1)
    }

    const output = {
        prefix: 'mdi',
        width: collection.width ?? 24,
        height: collection.height ?? 24,
        icons: picked,
    }

    fs.mkdirSync(path.dirname(OUT_FILE), { recursive: true })
    fs.writeFileSync(OUT_FILE, JSON.stringify(output), 'utf8')

    const bytes = fs.statSync(OUT_FILE).size
    const aliases = aliasCount.length
        ? `\n  alias di-resolve : ${aliasCount.length} (${aliasCount.slice(0, 3).join(', ')}${aliasCount.length > 3 ? ', ...' : ''})`
        : ''

    console.log(`Collection ikon dibuat: ${path.relative(ROOT, OUT_FILE)}`)
    console.log(`  file sumber dipindai : ${sourceFiles.length}`)
    console.log(`  ikon dibundel        : ${Object.keys(picked).length}${aliases}`)
    console.log(`  ukuran               : ${(bytes / 1024).toFixed(1)} KB`)
    console.log(`\nIkon ini kini dilayani lokal — tidak ada request ke api.iconify.design.`)
}

main()
