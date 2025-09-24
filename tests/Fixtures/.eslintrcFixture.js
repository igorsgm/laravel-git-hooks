module.exports = [
  {
    files: ['**/*.{js,jsx,mjs,cjs,vue}'],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: {
        browser: true
      }
    },
    rules: {
      // Auto-fixable rules to catch issues in fixable-js-file.js
      'brace-style': ['error', '1tbs', { allowSingleLine: true }],
      'no-multiple-empty-lines': ['error', { max: 1 }],
      'padded-blocks': ['error', 'never'],
      'space-before-blocks': ['error', 'always'],
      'space-before-function-paren': ['error', 'never'],

      // Non-fixable rules to not fix in not-fully-fixable-js-file.js
      'no-unused-vars': 'error',
      'complexity': ['error', 1],
      'no-empty-function': 'error'
    }
  }
];
