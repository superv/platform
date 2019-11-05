const purgecss = require('@fullhuman/postcss-purgecss')({

  content: [
    './src/**/*.html',
    './src/**/*.vue',
    './node_modules/@superv/ui/dist/superv-ui.umd.js'
  ],

  defaultExtractor: content => content.match(/[\w-/:]+(?<!:)/g) || []
})

module.exports = {
  plugins: [
    require('tailwindcss'),
    require('autoprefixer'),
    require('postcss-nested'),
    ...process.env.NODE_ENV === 'production'
      ? [purgecss]
      : []
  ]
}
