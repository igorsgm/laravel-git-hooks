module.exports = {
  env: {
    browser: true,
    es2021: true, // Supports ECMAScript 2021
  },
  extends: [
    'plugin:vue/vue3-essential', // Essential linting rules for Vue 3
    'eslint:recommended', // Recommended ESLint rules
  ],
  overrides: [
    {
      files: ['*.vue'],
      parser: 'vue-eslint-parser', // Ensure proper parsing of Vue files
      parserOptions: {
        parser: '@babel/eslint-parser', // Use Babel for JavaScript within Vue templates
        ecmaVersion: 'latest', // Latest ECMAScript features
        sourceType: 'module',
      },
    },
  ],
  parserOptions: {
    ecmaVersion: 'latest', // Latest ECMAScript features
    sourceType: 'module', // Support ES modules
  },
  plugins: [
    'vue', // Vue-specific linting rules
  ],
  rules: {
    // Example rules
    'vue/multi-word-component-names': 'off', // Disable enforcing multi-word names
    'no-unused-vars': 'warn', // Warn about unused variables
    'vue/no-mutating-props': 'error', // Disallow mutating props directly
    'vue/no-v-html': 'warn', // Warn about potential XSS via v-html
  },
};
