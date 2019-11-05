module.exports = {
  env: {
    amd: true,
  },
  extends: ['plugin:vue/essential', 'eslint:recommended', 'prettier'],
  plugins: ['prettier', 'vue'],
  rules: {
    'prettier/prettier': [
      'error',
      {
        semi: false,
        singleQuote: true,
        trailingComma: 'all',
      },
    ],
    'vue/require-v-for-key': 'off',
    'vue/script-indent': 'off',
    eqeqeq: ['error', 'always'],
    'no-console': process.env.NODE_ENV === 'production' ? 'error' : 'off',
    'no-debugger': process.env.NODE_ENV === 'production' ? 'error' : 'off',
  },
  parserOptions: {
    parser: 'babel-eslint',
    ecmaVersion: 2017,
    sourceType: 'module',
  },
  globals: {
    IS_DEV: true,
    Config: true,
    process: true,
    module: true,
    __dirname: true,
    APP_ENV: true,
  },
}
