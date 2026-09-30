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
