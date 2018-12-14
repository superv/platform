const path = require('path')

module.exports = {
  baseUrl: '/superv/',

  outputDir: '../assets/',

  indexPath: process.env.NODE_ENV === 'production'
    ? '../views/spa.php'
    : 'index.html',

  chainWebpack: config => {
    config
    .plugin('html')
    .tap(args => {
      if (process.env.NODE_ENV === 'development') {
        args[0].template = './public/development.html'
      }
      return args
    })
  }
}