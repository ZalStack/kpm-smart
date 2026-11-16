<script setup>
import { computed } from 'vue';
import katex from 'katex';
import 'katex/dist/katex.min.css';

const props = defineProps({
    text: { type: String, default: '' },
    displayMode: { type: Boolean, default: false },
});

const renderedHtml = computed(() => {
    if (!props.text) return '';
    
    try {
        // Replace LaTeX delimiters with KaTeX-compatible format
        let processedText = props.text;
        
        // Handle $...$ (inline math)
        processedText = processedText.replace(/\$([^$]+)\$/g, (match, formula) => {
            try {
                return katex.renderToString(formula, {
                    displayMode: false,
                    throwOnError: false,
                });
            } catch (e) {
                return match;
            }
        });
        
        // Handle $$...$$ (display math)
        processedText = processedText.replace(/\$\$([^$]+)\$\$/g, (match, formula) => {
            try {
                return katex.renderToString(formula, {
                    displayMode: true,
                    throwOnError: false,
                });
            } catch (e) {
                return match;
            }
        });
        
        // Handle \(...\) (inline math)
        processedText = processedText.replace(/\\\((.+?)\\\)/g, (match, formula) => {
            try {
                return katex.renderToString(formula, {
                    displayMode: false,
                    throwOnError: false,
                });
            } catch (e) {
                return match;
            }
        });
        
        // Handle \[...\] (display math)
        processedText = processedText.replace(/\\\[(.+?)\\\]/g, (match, formula) => {
            try {
                return katex.renderToString(formula, {
                    displayMode: true,
                    throwOnError: false,
                });
            } catch (e) {
                return match;
            }
        });

        // 1. Decode HTML-escaped image tags jika ada (misal &lt;img ...&gt;)
        if (processedText.includes('&lt;img')) {
            processedText = processedText.replace(/&lt;img\s+([^&>]+)&gt;/gi, '<img $1>');
        }

        // 2. Dukung format gambar Markdown: ![alt](url)
        processedText = processedText.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, (match, alt, url) => {
            let cleanUrl = (url || '').trim();
            if (!cleanUrl.startsWith('http://') && !cleanUrl.startsWith('https://') && !cleanUrl.startsWith('data:')) {
                const clean = cleanUrl.replace(/^\/+/, '');
                cleanUrl = clean.startsWith('storage/') ? '/' + clean : '/storage/' + clean;
            }
            return `<img src="${cleanUrl}" alt="${alt || 'Gambar'}" class="max-h-80 w-auto max-w-full rounded-2xl border border-border/80 object-contain my-3 shadow-sm block" loading="lazy" />`;
        });

        // 3. Normalisasi path src tag <img> agar selalu mengarah ke /storage/ jika relatif
        processedText = processedText.replace(/<img([^>]+)src=["']([^"']+)["']([^>]*)>/gi, (match, before, src, after) => {
            let newSrc = (src || '').trim();
            if (!newSrc.startsWith('http://') && !newSrc.startsWith('https://') && !newSrc.startsWith('data:')) {
                const clean = newSrc.replace(/^\/+/, '');
                newSrc = clean.startsWith('storage/') ? '/' + clean : '/storage/' + clean;
            }
            // Pastikan class styling rapi dan responsif
            return `<img${before}src="${newSrc}" class="max-h-80 w-auto max-w-full rounded-2xl border border-border/80 object-contain my-3 shadow-sm block"${after}>`;
        });
        
        return processedText;
    } catch (e) {
        return props.text;
    }
});
</script>

<template>
    <span v-html="renderedHtml" class="math-renderer"></span>
</template>

<style scoped>
.math-renderer {
    display: inline;
}

.math-renderer :deep(.katex) {
    font-size: 1.1em;
}

.math-renderer :deep(.katex-display) {
    margin: 0.5em 0;
    text-align: center;
}
</style>
