import js from '@eslint/js';
import pluginVue from 'eslint-plugin-vue';

export default [
    js.configs.recommended,
    ...pluginVue.configs['flat/recommended'],
    {
        files: ['resources/**/*.{js,vue}'],
        languageOptions: {
            globals: {
                window: 'readonly',
                document: 'readonly',
                console: 'readonly',
                alert: 'readonly',
                confirm: 'readonly',
                navigator: 'readonly',
                FormData: 'readonly',
                URLSearchParams: 'readonly',
                setTimeout: 'readonly',
                clearTimeout: 'readonly',
                setInterval: 'readonly',
                clearInterval: 'readonly',
                fetch: 'readonly',
                URL: 'readonly',
                route: 'readonly',
                google: 'readonly',
            },
        },
        rules: {
            'vue/multi-word-component-names': 'off',
            'vue/max-attributes-per-line': 'off',
            'vue/singleline-html-element-content-newline': 'off',
            'vue/html-self-closing': 'off',
            'vue/html-indent': 'off',
            'vue/attributes-order': 'off',
            'vue/require-default-prop': 'off',
            'vue/multiline-html-element-content-newline': 'off',
            'vue/order-in-components': 'off',
            'vue/one-component-per-file': 'off',
            'vue/component-definition-name-casing': 'off',
            'vue/html-closing-bracket-newline': 'off',
            'vue/html-closing-bracket-spacing': 'off',
            'vue/attribute-hyphenation': 'off',
            'vue/v-on-event-hyphenation': 'off',
            'vue/first-attribute-linebreak': 'off',
        },
    },
    {
        ignores: ['vendor/**', 'public/**', 'node_modules/**'],
    },
];
