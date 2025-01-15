export default [
  {
    files: ["**/*.js"], // Target only JavaScript files
    languageOptions: {
      ecmaVersion: "latest", // Use the latest ECMAScript features
      sourceType: "module", // Enable ES Modules
      globals: {
        window: true,
        document: true,
        console: true,
        navigator: true,
        process: true,
        module: true,
      },
    },
    rules: {
      // General JavaScript rules
      "no-unused-vars": "warn", // Warn about unused variables
      "no-console": "warn", // Warn about console statements
      "semi": ["error", "always"], // Enforce semicolons
      "indent": ["error", 2], // Enforce 2-space indentation
      "quotes": ["error", "double"], // Enforce double quotes
    },
  },
];
